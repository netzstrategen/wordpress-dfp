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
   * @var string
   */
  private $customTargeting;

  /**
   * @var int[]
   */
  protected static $formatCounter = [];

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
  public static function show($format, $marker = FALSE, $provider = NULL, $customTargeting = NULL) {
    $format = Format::createFromDefault($format);
    if (isset($provider)) {
      $provider = Provider::getById($provider) ?: NULL;
    }
    $instance = new static($format, $marker, $provider, $customTargeting);
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
   * @param string $customTargeting
   *   (optional) The custom target name for the ad slot.
   */
  public function __construct(Format $format, $marker = FALSE, Provider $provider = NULL, $customTargeting = NULL) {
    $this->format = $format;
    $this->marker = (bool) $marker;
    $this->provider = $provider;
    $this->customTargeting = $customTargeting;
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

  public function getCustomTargeting() {
    return $this->customTargeting;
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
      'customTargeting' => $this->customTargeting,
    ];
    Plugin::renderTemplate(['templates/dfp-adslot.php'], $variables);
  }

}
