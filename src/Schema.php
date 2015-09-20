<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\Schema.
 */

namespace Netzstrategen\Dfp;

/**
 * Generic plugin lifetime and maintenance functionality.
 */
class Schema {

  /**
   * register_activation_hook() callback.
   */
  public static function activate() {
    add_option('dfp_providers', []);
    add_option('dfp_term_units', []);
  }

  /**
   * register_deactivation_hook() callback.
   */
  public static function deactivate() {
  }

  /**
   * register_uninstall_hook() callback.
   */
  public static function uninstall() {
    delete_option('dfp_providers');
    delete_option('dfp_term_units');
  }

}
