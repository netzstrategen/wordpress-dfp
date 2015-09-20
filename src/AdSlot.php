<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\AdSlot.
 */

namespace Netzstrategen\Dfp;

/**
 * An ad slot.
 */
class AdSlot {

  private $provider;
  private $format;
  private $marker;

  private static $counter = [];

  public static function createFromWidget(array $instance) {
    return new static(Provider::getById($instance['provider']), $instance['format'], $instance['format']);
  }

  public function __construct(Provider $provider, $format, $marker = FALSE) {
    $this->provider = $provider;
    $this->format = $format;
    $this->marker = (bool) $marker;
  }

  public function render() {
    if (!isset(static::$counter[$this->format])) {
      static::$counter[$this->format] = 0;
    }
    $variables = [
      'id_suffix' => ++static::$counter[$this->format],
      'format' => $this->format,
      'marker' => $this->marker,
    ];
    Plugin::renderTemplate(['templates/banner.php'], $variables);
  }

}
