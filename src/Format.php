<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\Format.
 */

namespace Netzstrategen\Dfp;

/**
 * Represents an ad format; i.e., suitable size(s), responsive breakpoints, etc.
 */
class Format {

  /**
   * The internal name of this format.
   *
   * @var string
   */
  private $name;

  /**
   * Administrative label of this format.
   *
   * @var string
   */
  private $label;

  /**
   * List of allowed/suitable ad creative sizes/dimensions.
   *
   * Each size is itself a simple, indexed array whose two elements represent
   * width and height; e.g.: [300,250]. A width or height of 0 means "any".
   *
   * @var array
   */
  private $sizes;

  /**
   * List of Responsive Size Mappings (allowed ad sizes/dimensions), keyed by breakpoint.
   *
   * Each breakpoint represents a minimum screen size/dimension that allows the
   * respective specified ad sizes/dimensions.
   *
   * Breakpoints MUST be ordered from largest to smallest.
   *
   * Each size is itself a simple, indexed array whose two elements represent
   * width and height; e.g.: [300,250]. A width or height of 0 means "any".
   *
   * @see https://support.google.com/dfp_premium/answer/3423562
   * @see https://support.google.com/dfp_premium/answer/4578089
   *
   * @var array
   */
  private $sizeMappings = [];

  public static function getDefaults() {
    $container_width = 960;
    $formats = [
      'superbanner' => [
        'label' => __('Superbanner/Leaderboard', Plugin::L10N),
        'sizes' => [[728,90]],
        'size_mappings' => [
          [[728 + 2*10,90], [728,90]],
        ],
      ],
      'skyscraper' => [
        'label' => __('Skyscraper', Plugin::L10N),
        'sizes' => [[120,600], [160,600], [200,600]],
        'size_mappings' => [
          // The skyscraper appears to the right of the main content area, which is
          // centered horizontally, so the same amount of space is required to its left.
          // Additionally, there is a vertical browser scrollbar of approx. 16px.
          [[$container_width + 16 + 2*200,200], [[200,600], [160,600], [120,600]]],
          [[$container_width + 16 + 2*160,200], [[160,600], [120,600]]],
          [[$container_width + 16 + 2*120,200], [[120,600]]],
        ],
      ],
      'billboard' => [
        'label' => __('Megabanner/Billboard', Plugin::L10N),
        'sizes' => [[800,250]],
        'size_mappings' => [
          [[800 + 2*10,250], [[800,250]]],
        ],
      ],
      'rectangle' => [
        'label' => __('Any Rectangle', Plugin::L10N),
        'sizes' => [[300,100], [300,250], [300,600]],
        'size_mappings' => [
          [[300 + 2*10,100], [[300,600],[300,250],[300,100]]],
        ],
      ],
      'mediumrectangle' => [
        'label' => __('Medium Rectangle', Plugin::L10N),
        'sizes' => [[300,250]],
        'size_mappings' => [
          [[300 + 2*10,250], [[300,600],[300,250]]],
        ],
      ],
      'bottom' => [
        'label' => __('Bottom', Plugin::L10N),
        'sizes' => [[300,300]],
        'size_mappings' => [
          [[300 + 2*10,300], [[300,300]]],
        ],
      ],
    ];
    foreach ($formats as &$format) {
      if (!empty($format['size_mappings'])) {
        // The last size mapping has the lowest priority and thus applies if the
        // available screen dimensions are smaller than the earlier defined
        // mappings with higher priority. By defining no suitable sizes for "any"
        // dimension ([0,0]), no ad will be suitable/rendered.
        $format['size_mappings'][] = [[0,0], []];
      }
    }
    return $formats;
  }

  public function __construct($name, $label, array $sizes, array $size_mappings = NULL) {
    $this->name = $name;
    $this->label = $label;
    $this->sizes = $sizes;
    $this->sizeMappings = $size_mappings;
  }

  public function getName() {
    return $this->name;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getSizes() {
    return $this->sizes;
  }

  public function hasSizeMappings() {
    return !empty($this->sizeMappings);
  }

  public function getSizeMappings() {
    return $this->sizeMappings;
  }

  /**
   * Returns all known ad formats.
   *
   * @return \Netzstrategen\Dfp\Format[]
   */
  public static function getAll() {
    $formats = static::getDefaults();
    foreach ($formats as $name => &$format) {
      $format = new static($name, $format['label'], $format['sizes'], $format['size_mappings']);
    }
    return $formats;
  }

  public static function createFromDefault($name) {
    $formats = static::getDefaults();
    if (isset($formats[$name])) {
      $format = $formats[$name];
      return new static($name, $format['label'], $format['sizes'], $format['size_mappings']);
    }
    throw new \InvalidArgumentException("Unknown format '$name'");
  }

}
