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
    $script = '<script async type="text/javascript" src="//www.googletagservices.com/tag/js/gpt.js"></script>
';
    $script .= static::renderJS();
    $output = str_replace('<!--DFP-PLACEHOLDER-SCRIPT-->', $script, $output);
    echo $output;
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
      $provider = $slot->getProvider() ?: $default_provider;
      $slot_unit = $provider->getAdUnitPrefix() . $unit;
      if (!$provider->isPremium()) {
        $slot_unit = preg_replace('@/+@', '//', $slot_unit);
      }
      $slot_unit = '/' . $provider->getId() . '/' . $slot_unit;
      $sizes = $slot->getFormat()->getSizes();
      $html_id = $slot->getId();
      $script .= "  googletag.defineSlot('$slot_unit', " . json_encode($sizes) . ", '$html_id')\n";
      if ($slot->getFormat()->hasSizeMappings()) {
        $script .= "    .defineSizeMapping(" . json_encode($slot->getFormat()->getSizeMappings()) . ")\n";
      }
      $script .= "    .addService(googletag.pubads());\n";
    }
    $script .= '  googletag.pubads().enableSingleRequest();
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
    }
    if (static::$zoneName !== FALSE) {
      return static::$zoneName;
    }
  }

  public static function determineZoneName() {
    global $wp_query;

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
      return 'test';
    }
    if (is_category() || is_tag()) {
      $term = $wp_query->get_queried_object();
      if ($ad_unit = Term::getAdUnit($term)) {
        return $ad_unit;
      }
      // @todo Traverse through parent categories.
    }
    if (is_single()) {
      $categories = wp_get_post_categories($GLOBALS['post']->ID);
      foreach ($categories as $term_id) {
        $term = get_term($term_id, 'category');
        if ($ad_unit = Term::getAdUnit($term)) {
          return $ad_unit;
        }
        // @todo Traverse through parent categories.
      }
    }
    // @todo Events?
    // @todo Galleries?
    elseif (is_page()) {
      // @todo Traverse through parent pages.
    }
    return apply_filters('dfp/zone_default', 'other');
  }

}
