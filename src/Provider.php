<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\Provider.
 */

namespace Netzstrategen\Dfp;

/**
 * Represents a DFP account providing ads for this site.
 */
class Provider {

  /**
   * The Google DFP account ID.
   *
   * @var int
   */
  private $id;

  /**
   * Administrative Google DFP account label.
   *
   * @var string
   */
  private $label;

  /**
   * Common prefix for all ad units; e.g., 'example.com/'.
   *
   * @var string
   */
  private $adUnitPrefix;

  /**
   * @var bool
   */
  private $isPremium;

  public function __construct($id, $label, $adunit_prefix = '', $is_premium = FALSE) {
    $this->id = (int) $id;
    $this->label = $label;
    $this->adUnitPrefix = $adunit_prefix;
    $this->isPremium = (bool) $is_premium;
  }

  public function getId() {
    return $this->id;
  }

  public function getLabel() {
    return $this->label;
  }

  public function getAdUnitPrefix() {
    return $this->adUnitPrefix;
  }

  /**
   * Returns whether this provider is a premium DFP account.
   *
   * Denotes e.g. whether hierarchical ad slots are supported (delimited by
   * forward slashes) - if not, slashes in ad slot IDs need to be escaped
   * (with a slash).
   *
   * @return bool
   */
  public function isPremium() {
    return $this->isPremium;
  }

  public static function getAll() {
    $options = get_option('dfp_providers', []);
    $providers = [];
    foreach ($options as $option) {
      $providers[$option['id']] = new static($option['id'], $option['label'], $option['adunit_prefix'], $option['is_premium']);
    }
    return $providers;
  }

  public static function getById($provider_id) {
    $providers = get_option('dfp_providers', []);
    foreach ($providers as $provider) {
      if ($provider['id'] == $provider_id) {
        return new static($provider['id'], $provider['label'], $provider['adunit_prefix'], $provider['is_premium']);
      }
    }
    return FALSE;
  }

}
