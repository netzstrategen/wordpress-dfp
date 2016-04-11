<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\OutOfPageAdSlot.
 */

namespace Netzstrategen\Dfp;

/**
 * An out-of-page ad slot.
 */
class OutOfPageAdSlot extends AdSlot {

  /**
   * Renders an out-of-page ad slot.
   *
   * @param int $provider
   *   (optional) The ID of a provider to explicitly use for the ad slot.
   *
   * @return \Netzstrategen\Dfp\AdSlot
   *   The ad slot instance, after it has been output.
   */
  public static function showOutOfPage($provider = NULL) {
    $format = Format::createFromDefault('outofpage');
    if (isset($provider)) {
      $provider = Provider::getById($provider) ?: NULL;
    }
    $instance = new static($format, FALSE, $provider);
    $instance->render();
    return $instance;
  }

}
