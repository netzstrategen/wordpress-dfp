<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\Admin.
 */

namespace Netzstrategen\Dfp;

/**
 * Administrative back-end functionality.
 */
class Admin {

  /**
   * @implements admin_init
   */
  public static function init() {
    add_action('created_term', __NAMESPACE__ . '\Term::onSave', 10, 3);
    add_action('edited_term', __NAMESPACE__ . '\Term::onSave', 10, 3);
    add_action('delete_term', __NAMESPACE__ . '\Term::onDelete', 10, 3);

    $taxonomies = get_taxonomies('', 'names');
    foreach ($taxonomies as $taxonomy_name) {
      add_action("{$taxonomy_name}_add_form_fields", __NAMESPACE__ . '\Term::add_form_fields');
      add_action("{$taxonomy_name}_edit_form_fields", __NAMESPACE__ . '\Term::edit_form', 10, 2);
      add_filter("manage_edit-{$taxonomy_name}_columns", __NAMESPACE__ . '\Term::tableColumns');
      add_filter("manage_{$taxonomy_name}_custom_column", __NAMESPACE__ . '\Term::tableColumn', 10, 3);
    }
  }

}
