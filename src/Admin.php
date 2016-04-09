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
    static::registerSettings();

    add_action('created_term', __NAMESPACE__ . '\Term::onSave', 10, 3);
    add_action('edited_term', __NAMESPACE__ . '\Term::onSave', 10, 3);
    add_action('delete_term', __NAMESPACE__ . '\Term::onDelete', 10, 4);

    $taxonomies = get_taxonomies('', 'names');
    foreach ($taxonomies as $taxonomy_name) {
      add_action("{$taxonomy_name}_add_form_fields", __NAMESPACE__ . '\Term::add_form_fields');
      add_action("{$taxonomy_name}_edit_form_fields", __NAMESPACE__ . '\Term::edit_form', 10, 2);
      add_filter("manage_edit-{$taxonomy_name}_columns", __NAMESPACE__ . '\Term::tableColumns');
      add_filter("manage_{$taxonomy_name}_custom_column", __NAMESPACE__ . '\Term::tableColumn', 10, 3);
    }
    add_action('admin_enqueue_scripts', __CLASS__ . '::admin_enqueue_scripts');
    add_action('quick_edit_custom_box', __NAMESPACE__ . '\Term::quick_edit_custom_box', 10, 3);
  }

  /**
   * @implements admin_menu
   */
  public static function menu() {
    add_options_page(sprintf(__('%s › DoubleClick For Publishers (DFP) Advertisements', Plugin::L10N), __('Settings')), __('Advertisements (DFP)', Plugin::L10N), 'manage_options', 'dfp', __CLASS__ . '::renderSettingsPage');
  }

  /**
   * Registers plugin options.
   */
  public static function registerSettings() {
    register_setting('dfp', 'dfp_providers', __CLASS__ . '::submitSettingProviders');
    add_settings_section('default', __('Providers', Plugin::L10N), '__return_false', 'dfp');
    add_settings_field('dfp_providers', __('Google DFP accounts', Plugin::L10N), __CLASS__ . '::renderSettingProviders', 'dfp', 'default');
  }

  /**
   * Page callback; Presents the DFP options page.
   */
  public static function renderSettingsPage() {
    echo '<div class="wrap">';
    echo '<h2>' . $GLOBALS['title'] . '</h2>';
    echo '<form action="options.php" method="post">';
    settings_fields('dfp');
    do_settings_sections('dfp');
    submit_button();
    echo '</form>';
    echo '</div>';
  }

  /**
   * Setting field callback; Presents form elements for the 'dfp_providers' option.
   */
  public static function renderSettingProviders() {
    $label_placeholder = get_option('blogname');
    $adunit_prefix_placeholder = parse_url(get_option('siteurl'))['host'] . '/';
    $providers = Provider::getAll();
    $providers[] = new Provider(0, '', '', FALSE);
    foreach ($providers as $i => $provider) {
      $id_prefix = 'dfp-provider-' . $provider->getId();
      if ($provider->getId() !== 0) {
        $name_prefix = 'dfp_providers[' . $i . ']';
      }
      else {
        $name_prefix = 'dfp_providers[new]';
      }
      echo '<div>';
      echo '<label for="' . $id_prefix . '-id">' . __('ID', Plugin::L10N) . '</label>', "\n";
      if ($provider->getId() !== 0) {
        echo '<input id="' . $id_prefix . '-id" type="text" name="' . $name_prefix . '[id]" pattern="[0-9]+" required value="' . $provider->getId() . '" readonly>', "\n";
      }
      else {
        echo '<input id="' . $id_prefix . '-id" type="text" name="' . $name_prefix . '[id]" pattern="[0-9]+">', "\n";
      }
      echo '<label for="' . $id_prefix . '-label">' . __('Label', Plugin::L10N) . '</label>', "\n";
      echo '<input id="' . $id_prefix . '-label" type="text" name="' . $name_prefix . '[label]" placeholder="' . $label_placeholder . '" value="' . $provider->getLabel() . '">', "\n";

      echo '<label for="' . $id_prefix . '-adunit-prefix">' . __('Ad unit prefix', Plugin::L10N) . '</label>', "\n";
      echo '<input id="' . $id_prefix . '-adunit-prefix" type="text" name="' . $name_prefix . '[adunit_prefix]" placeholder="' . $adunit_prefix_placeholder . '" value="' . $provider->getAdUnitPrefix() . '">', "\n";

      echo '<input id="' . $id_prefix . '-is-premium" type="checkbox" name="' . $name_prefix . '[is_premium]" value="1"' . ($provider->isPremium() ? ' checked' : '') . '>', "\n";
      echo '<label for="' . $id_prefix . '-is-premium">' . __('Premium account', Plugin::L10N) . '</label>', "\n";
      echo '</div>';
    }
  }

  /**
   * Setting "sanitization" callback; Adjusts 'dfp_providers' option prior to saving.
   */
  public static function submitSettingProviders(array $input) {
    if ($input['new']['id'] === '') {
      unset($input['new']);
    }
    else {
      $new = $input['new'];
      unset($input['new']);
      $input[] = $new;
    }
    foreach ($input as &$provider) {
      $provider['id'] = (int) $provider['id'];
      // A checkbox value is not sent if it was not checked.
      $provider['is_premium'] = !empty($provider['is_premium']);
    }
    return $input;
  }

  /**
   * @implements admin_enqueue_scripts
   */
  public static function admin_enqueue_scripts() {
    global $pagenow;

    if ($pagenow === 'edit-tags.php' && isset($_GET['taxonomy']) && !isset($_REQUEST['action'])) {
      wp_enqueue_script('dfp.admin', Plugin::getBaseUrl() . '/js/dfp.admin.js', ['jquery-core', 'inline-edit-tax']);
    }
  }

}
