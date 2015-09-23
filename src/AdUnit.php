<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\AdUnit.
 */

namespace Netzstrategen\Dfp;

/**
 * An ad unit.
 */
class AdUnit {

  /**
   * @var string
   */
  private $name;

  public static function getDefaults() {
    // 'homepage' is hard-coded.
    // 'other' ('sonstiges') is the global fallback.
    $units = [
      'automotive',
      'business',
      'computer',
      'ecommerce',
      'entertainment',
      'family',
      'football',
      'formula1',
      'health',
      'jobs',
      'localnews',
//      'nationalnews',
      'politics', // OMS: 'politik',
      'property',
      'science',
      'social',
      'sports',
      'travel',
      'weather',
      'webradio',
      'test',
    ];
    return $units;
  }

  public static function getAll() {
    $units = static::getDefaults();
    apply_filters('dfp/units', $units);
    return $units;
  }

  public static function createFromName($name) {
    return new static($name);
  }

  /**
   * Constructs a new ad slot.
   *
   * @param string $name
   *   The name of the ad unit.
   */
  public function __construct($name) {
    $this->name = $name;
  }

  /**
   * Returns the name of this ad unit.
   *
   * @return string
   */
  public function getName() {
    return $this->name;
  }

}
