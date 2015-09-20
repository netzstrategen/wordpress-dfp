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

  /**
   * @var string
   */
  private $id;

  /**
   * @var \Netzstrategen\Dfp\Format
   */
  private $format;

  /**
   * @var bool
   */
  private $marker;

  /**
   * @var \Netzstrategen\Dfp\Provider
   */
  private $provider;

  /**
   * @var int[]
   */
  private static $formatCounter = [];

  /**
   * Renders an ad slot.
   *
   * @param string $format
   *   The name of the format to render in the ad slot.
   * @param bool $marker
   *   Whether to include an "Advertisement" text marker.
   * @param int $provider
   *   (optional) The ID of a provider to explicitly use for the ad slot.
   *
   * @return \Netzstrategen\Dfp\AdSlot
   *   The ad slot instance, after it has been output.
   */
  public static function show($format, $marker = FALSE, $provider = NULL) {
    $format = Format::createFromDefault($format);
    if (isset($provider)) {
      $provider = Provider::getById($provider) ?: NULL;
    }
    $instance = new static($format, $marker, $provider);
    $instance->render();
    return $instance;
  }

  /**
   * Constructs a new ad slot.
   *
   * @param \Netzstrategen\Dfp\Format $format
   *   The format to render in the ad slot.
   * @param bool $marker
   *   Whether to include an "Advertisement" text marker.
   * @param \Netzstrategen\Dfp\Provider $provider
   *   (optional) The provider to explicitly use for the ad slot.
   */
  public function __construct(Format $format, $marker = FALSE, Provider $provider = NULL) {
    $this->format = $format;
    $this->marker = (bool) $marker;
    $this->provider = $provider;
  }

  /**
   * Returns the HTML ID of this ad slot.
   *
   * Only available after this ad slot has been rendered.
   *
   * @return string
   */
  public function getId() {
    return $this->id;
  }

  public function getFormat() {
    return $this->format;
  }

  public function getProvider() {
    return $this->provider;
  }

  /**
   * Renders this ad slot.
   */
  public function render() {
    $format_name = $this->format->getName();
    if (!isset(static::$formatCounter[$format_name])) {
      static::$formatCounter[$format_name] = 0;
    }
    $this->id = preg_replace('@[^a-zA-Z0-9_-]+@', '', 'dfp-ad-' . $format_name . '-' . ++static::$formatCounter[$format_name]);
    CurrentPage::addRenderedAdSlot($this);

    $variables = [
      'id' => $this->id,
      'format' => $format_name,
      'marker' => $this->marker,
    ];
    Plugin::renderTemplate(['templates/dfp-adslot.php'], $variables);
  }

}
