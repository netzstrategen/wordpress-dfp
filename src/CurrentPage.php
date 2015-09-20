<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\CurrentPage.
 */

namespace Netzstrategen\Dfp;

/**
 * Page related helper functions.
 */
class CurrentPage {

  /**
   * @var string|FALSE
   */
  private $zoneName;

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
    $placementId = FALSE;
    elseif (is_category() || is_single()) {
      $categoryId = $this->getCurrentCategoryId();
      $placementId = DFP_Model_Mapping::getInstance()->getPlacementIdForCategory($categoryId);
    }
    else {
      $pageId = url_to_postid($_SERVER['REQUEST_URI']);
      $placementId = DFP_Model_Mapping::getInstance()->getPlacementIdForPage(!empty($pageId) ? $pageId : 0);
    }
    if ($placementId == 0) {
      $placementId = FALSE;
    }
    return $placementId;
  }

  private function getCurrentCategoryId() {
    if (isset($this->currentCategoryId)) {
      return $this->currentCategoryId;
    }
    $catId = get_query_var('cat');
    if (!$catId) {
      $categories = wp_get_post_categories($GLOBALS['post']->ID);
      if (isset($categories[0])) {
        $catId = $categories[0];
      }
    }
    $this->currentCategoryId = $catId ? $catId : FALSE;
    return $this->currentCategoryId;
  }

}
