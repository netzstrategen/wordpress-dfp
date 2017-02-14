<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\CurrentPage.
 */

namespace Netzstrategen\Dfp;

/**
 * Resolves ad units, and generates and tracks ad slots for the currently requested page.
 *
 * Ad units are called "zones" in other ad servers.
 */
class CurrentPage {

  /**
   * @var string|FALSE
   */
  private static $zoneName;

  /**
   * @var \Netzstrategen\Dfp\AdSlot[]
   */
  private static $renderedAdSlots = [];

  /**
   * @var array
   */
  private static $targeting = [];

  /**
   * @implements wp_head
   * @see \Netzstrategen\Dfp\Plugin::wp_footer()
   */
  public static function wp_head() {
    ob_start();
    echo '<!--DFP-PLACEHOLDER-SCRIPT-->', "\n";
  }

  /**
   * @implements wp_footer
   * @see \Netzstrategen\Dfp\Plugin::wp_head()
   */
  public static function wp_footer() {
    $output = ob_get_clean();
    $script = '<script type="text/javascript" src="//www.googletagservices.com/tag/js/gpt.js"></script>
';
    $script .= static::renderJS();
    $output = str_replace('<!--DFP-PLACEHOLDER-SCRIPT-->', $script, $output);
    echo $output;
  }

  /**
   * @implements get_header
   */
  public static function get_header() {
    OutOfPageAdSlot::showOutOfPage();
  }

  public static function renderJS() {
    $unit = static::getZoneName();
    if (!$providers = Provider::getAll()) {
      // No providers.
      return '';
    }
    $default_provider = reset($providers);
    // @todo Support multiple providers.
    // @todo Decide: Same units for every provider? Provider-specific units must
    //   be negotiated in determineZoneName() already & will conflict with filter(s)
    //   for default/fallback units.
    //$unit = $unit[$default_provider->getId()];

    $slots = static::getRenderedAdSlots();
    $script = '<script type="text/javascript">
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
googletag.cmd.push(function () {
';
    foreach ($slots as $slot) {
      $defineMethod = $slot instanceof OutOfPageAdSlot ? 'defineOutOfPageSlot' : 'defineSlot';
      $provider = $slot->getProvider() ?: $default_provider;
      $slot_unit = $provider->getAdUnitPrefix() . $unit;
      if (!$provider->isPremium()) {
        $slot_unit = preg_replace('@/+@', '//', $slot_unit);
      }
      $slot_unit = '/' . $provider->getId() . '/' . $slot_unit;
      $sizes = $slot->getFormat()->getSizes();
      $sizes = $sizes ? json_encode($sizes) . ', ' : '';
      $html_id = $slot->getId();
      $script .= "  googletag.$defineMethod('$slot_unit', " . $sizes . "'$html_id')\n";
      if ($slot->getFormat()->hasSizeMappings()) {
        $script .= "    .defineSizeMapping(" . json_encode($slot->getFormat()->getSizeMappings()) . ")\n";
      }
      if ($customTargeting = $slot->getCustomTargeting()) {
        $script .= "    .setTargeting('position', '" . $customTargeting . "')\n";
      }
      $script .= "    .addService(googletag.pubads());\n";
    }
    $targeting = static::getTargeting();
    $script .= "  googletag.pubads()";
    foreach ($targeting as $key => $value) {
      $script .= "\n    .setTargeting('$key', " . json_encode($value) . ")";
    }
    $script .= ";\n";
    $script .= '  googletag.pubads().enableSingleRequest();
  googletag.pubads().enableSyncRendering();
  googletag.pubads().collapseEmptyDivs();
';
    // Allow plugins and theme to append further commands.
    ob_start();
    do_action('dfp/script_append', $unit, $slots, $script);
    $script .= ob_get_clean();

    $script .= '  googletag.enableServices();
});
</script>
';
    return $script;
  }

  /**
   * Adds a rendered ad slot to the stack.
   *
   * @param \Netzstrategen\Dfp\AdSlot $instance
   *   The ad slot that has been rendered.
   */
  public static function addRenderedAdSlot(AdSlot $instance) {
    static::$renderedAdSlots[$instance->getId()] = $instance;
  }

  /**
   * Returns the stack of rendered ad slot instances.
   *
   * @return \Netzstrategen\Dfp\AdSlot[]
   *   All rendered ad slots of the current PHP process, in the order they were
   *   output, keyed by HTML ID.
   */
  public static function getRenderedAdSlots() {
    return static::$renderedAdSlots;
  }

  public static function getZoneName() {
    if (!isset(static::$zoneName)) {
      static::$zoneName = static::determineZoneName();
      static::$zoneName = apply_filters('dfp/zone', static::$zoneName);
    }
    if (static::$zoneName !== FALSE) {
      return static::$zoneName;
    }
  }

  public static function determineZoneName() {
    global $wp_query;

    static::setTargeting('lang', 'fr');

    if (is_admin() || is_network_admin()) {
      return FALSE;
    }
    if (is_front_page()) {
      return 'homepage';
    }
    elseif (is_home()) {
      // Special case in WordPress when the front page shows a static page, but
      // the user visits the "blog" page (chronological listing of all posts).
      return 'news'; // @todo Valid name?
    }
    // If the current URL ends with /test in any way, let's test.
    if (preg_match('@[/\?]test/?$@', $_SERVER['REQUEST_URI'])) {
      static::setTargeting('entity_type', 'test');
      static::setTargeting('entity_slug', 'test');
      static::setTargeting('post_type', 'test');
      return 'test';
    }
    if (is_category() || is_tag()) {
      $term = $wp_query->get_queried_object();
      static::setTargeting('entity_type', 'term');
      static::setTargeting('entity_slug', $term->slug);
      static::setTargeting('entity_id', $term->term_id);
      if ($ad_unit = Term::getAdUnit($term)) {
        return $ad_unit;
      }
      // @todo Traverse through parent categories.
    }
    if (is_single()) {
      static::setTargeting('entity_type', 'post');
      static::setTargeting('entity_slug', $GLOBALS['post']->post_name);
      static::setTargeting('entity_id', $GLOBALS['post']->ID);
      static::setTargeting('post_type', $GLOBALS['post']->post_type);
      $categories = wp_get_post_categories($GLOBALS['post']->ID);
      foreach ($categories as $term_id) {
        $term = get_term($term_id, 'category');
        if ($ad_unit = Term::getAdUnit($term)) {
          break;
        }
        // @todo Traverse through parent categories.
      }
      if (!$ad_unit) {
        $ad_unit = 'other';
      }
      return apply_filters('dfp/zone/single', $ad_unit, $GLOBALS['post'], $categories);
    }
    // @todo Events?
    // @todo Galleries?
    elseif (is_page()) {
      static::setTargeting('entity_type', 'post');
      static::setTargeting('entity_slug', $GLOBALS['post']->post_name);
      static::setTargeting('entity_id', $GLOBALS['post']->ID);
      static::setTargeting('post_type', $GLOBALS['post']->post_type);
      // @todo Traverse through parent pages.
    }
    return apply_filters('dfp/zone_default', 'other');
  }

  /**
   * Sets a key/value pair for page-level targeting.
   *
   * @param string $key
   *   The targeting key to set. Must not contain spaces.
   * @param string $value
   *   The value to set for the key.
   */
  public static function setTargeting($key, $value) {
    static::$targeting[$key] = $value;
  }

  /**
   * Returns all key/value pairs that have been set for page-level targeting.
   *
   * @return array
   */
  public static function getTargeting() {
    return static::$targeting;
  }

}
