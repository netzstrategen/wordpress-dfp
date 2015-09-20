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
    $slots = static::getRenderedAdSlots();
    $script = '<script type="text/javascript">
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
googletag.cmd.push(function () {
';
    $unit = static::getZoneName();
    $default_provider = Provider::getAll()[0];
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
    do_action('dfp_script_append', $unit, $slots, $script);
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
    elseif (preg_match('@^/test/@', $_SERVER['REQUEST_URI'])) {
      return 'test';
    }
    elseif (is_category()) {
      $cat_id = get_query_var('cat');
      if ($ad_unit = Term::getAdUnit($cat_id)) {
        return $ad_unit;
      }
      // @todo Traverse through parent categories.
      return $ad_unit;
    }
    elseif (is_tag()) {
      
    }
    elseif (is_single()) {
      // @todo Traverse through parent pages.
      if (is_hierarchical()) {
        
      }
    }
//    $placementId = FALSE;
//    elseif (is_category() || is_single()) {
//      $categoryId = static::getCurrentCategoryId();
//      $placementId = DFP_Model_Mapping::getInstance()->getPlacementIdForCategory($categoryId);
//    }
//    else {
//      $pageId = url_to_postid($_SERVER['REQUEST_URI']);
//      $placementId = DFP_Model_Mapping::getInstance()->getPlacementIdForPage(!empty($pageId) ? $pageId : 0);
//    }
//    if ($placementId == 0) {
//      $placementId = FALSE;
//    }
//    return $placementId;
  }

  private static function getCurrentCategoryId() {
    if (isset(static::$currentCategoryId)) {
      return static::$currentCategoryId;
    }
    $catId = get_query_var('cat');
    if (!$catId) {
      $categories = wp_get_post_categories($GLOBALS['post']->ID);
      if (isset($categories[0])) {
        $catId = $categories[0];
      }
    }
    static::$currentCategoryId = $catId ? $catId : FALSE;
    return static::$currentCategoryId;
  }

}
