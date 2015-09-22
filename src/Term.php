<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\Term.
 */

namespace Netzstrategen\Dfp;

/**
 * Handles metadata/settings for a taxonomy term.
 */
class Term {

  private static $all_units;

  public static function getAdUnit($term, Provider $provider = NULL) {
    $term_units = static::getAdUnits($term);
    if ($provider) {
      return isset($term_units[$provider->getId()]) ? $term_units[$provider->getId()] : NULL;
    }
    return $term_units;
  }

  public static function setAdUnit($term, Provider $provider, $unit) {
    static::getAllAdUnits();
    static::$all_units[$term->taxonomy][$term->term_id][$provider->getId()] = $unit;
    update_option('dfp_term_units', static::$all_units);
  }

  public static function removeAdUnit($term, Provider $provider = NULL) {
    static::getAllAdUnits();
    if (isset($provider)) {
      unset(static::$all_units[$term->taxonomy][$term->term_id][$provider->getId()]);
    }
    else {
      unset(static::$all_units[$term->taxonomy][$term->term_id]);
    }
    update_option('dfp_term_units', static::$all_units);
  }

  private static function getAdUnits($term) {
    $all_units = static::getAllAdUnits();
    if (isset($all_units[$term->taxonomy][$term->term_id])) {
      return $all_units[$term->taxonomy][$term->term_id];
    }
    return [];
  }

  private static function getAllAdUnits() {
    if (!isset(static::$all_units)) {
      static::$all_units = get_option('dfp_term_units', []);
    }
    return static::$all_units;
  }

  /**
   * @implements {$taxonomy_name}_add_form_fields
   */
  public static function add_form_fields($taxonomy_name) {
?>
<div class="form-field term-dfp-adunit-wrap">
  <label for="term-dfp-adunit"><?= __('Associated DFP Ad Unit', Plugin::L10N) ?></label>
  <?php static::outputFormSelectElement($taxonomy_name) ?>
</div>
<?php
  }

  /**
   * @implements {$taxonomy_name}_edit_form
   */
  public static function edit_form($term, $taxonomy_name) {
?>
<tr class="form-field term-dfp-adunit-wrap">
  <th scope="row"><label for="term-dfp-adunit"><?= __('Associated DFP Ad Unit', Plugin::L10N) ?></label></th>
  <td><?php static::outputFormSelectElement($taxonomy_name, $term) ?></td>
</tr>
<?php
  }

  /**
   * Outputs a select dropdown form element for a new or a given term.
   *
   * @param string $taxonomy_name
   *   The name/slug of the taxonomy the term belongs to.
   * @param string $term
   *   (optional) The term object being edited.
   */
  public static function outputFormSelectElement($taxonomy_name, $term = NULL) {
    $options = [
      'homepage',
      'sonstiges',
      'test',
    ];
    foreach (Provider::getAll() as $provider):
      $selected = NULL;
      if ($term) {
        $selected = static::getAdUnit($term, $provider);
      }
?>
  <div>
    <?= esc_html($provider->getLabel()) ?>:
    <select name="dfp[adunit][<?= $provider->getId() ?>]" data-dfp-provider="<?= $provider->getId() ?>">
      <option value=""><?= __('- None -', Plugin::L10N) ?></option>
<?php foreach ($options as $value): ?>
      <option value="<?= esc_attr($value) ?>"<?= $value === $selected ? ' selected' : '' ?>><?= esc_html($value) ?></option>
<?php endforeach; ?>
    </select>
  </div>
<?php
    endforeach;
  }

  /**
   * @implements created_term
   * @implements edited_term
   */
  public static function onSave($term_id, $taxonomy_id, $taxonomy_name) {
    if (isset($_POST['dfp']['adunit']) && is_array($_POST['dfp']['adunit'])) {
      $term = get_term($term_id, $taxonomy_name);
      foreach ($_POST['dfp']['adunit'] as $provider_id => $unit) {
        if ($provider = Provider::getById($provider_id)) {
          if ($unit !== '') {
            static::setAdUnit($term, $provider, $unit);
          }
          else {
            static::removeAdUnit($term, $provider);
          }
        }
      }
    }
  }

  /**
   * @implements delete_term
   */
  public static function onDelete($term_id, $taxonomy_id, $taxonomy_name, $term_or_error) {
    if (!$term_or_error instanceof \WP_Error) {
      static::removeAdUnit($term_or_error);
    }
  }

  /**
   * @implements manage_edit-{$taxonomy_name}_columns
   */
  public static function tableColumns(array $columns) {
    $columns['dfp-adunit'] = __('DFP Ad Unit', Plugin::L10N);
    return $columns;
  }

  /**
   * @implements manage_{$taxonomy_name}_custom_column
   */
  public static function tableColumn($content, $column_name, $term_id) {
    if ($column_name === 'dfp-adunit') {
      $term = get_term($term_id, $_REQUEST['taxonomy']);
      // @todo Inherit ad unit of parent term.
      $providers = Provider::getAll();
      $provider_count = count($providers);
      foreach ($providers as $provider) {
        if ($unit = static::getAdUnit($term, $provider)) {
          $content .= '<div data-dfp-provider="' . $provider->getId() . '" data-dfp-unit="' . esc_attr($unit) . '">';
          if ($provider_count > 1) {
            $content .= esc_html($provider->getLabel()) . ': ';
          }
          $content .= esc_html($unit) . '</div>';
        }
      }
    }
    return $content;
  }

  /**
   * @implements quick_edit_custom_box
   */
  public static function quick_edit_custom_box($column_name, $screen_name, $taxonomy_name) {
    if ($column_name !== 'dfp-adunit') {
      return FALSE;
    }
?>
  <fieldset>
    <div class="inline-edit-col">
      <label>
        <span class="title" style="width: 10em;"><?= __('DFP Ad Unit', Plugin::L10N) ?></span>
        <span class="input-text-wrap" style="overflow: hidden;">
          <?php static::outputFormSelectElement($taxonomy_name); ?>
        </span>
      </label>
    </div>
  </fieldset>
<?php
  }

}
