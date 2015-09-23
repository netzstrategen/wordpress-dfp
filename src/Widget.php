<?php

/**
 * @file
 * Contains \Netzstrategen\Dfp\Widget.
 */

namespace Netzstrategen\Dfp;

/**
 * DFP widget.
 */
class Widget extends \WP_Widget {

  /**
   * @implements widgets_init
   *
   * register_widget() directly instantiates the given class, so this file will
   * be loaded on all pages anyway.
   */
  public static function init() {
    register_widget(__CLASS__);
  }

  public function __construct() {
    parent::__construct('dfp', __('Advertisement (DFP)', Plugin::L10N), [
      'description' => __('Placeholder for an advertisement delivered via DoubleClick For Publishers (DFP).', Plugin::L10N),
    ]);
  }

  /**
   * Front-end display of widget.
   */
  public function widget($args, $instance) {
    //echo $args['before_widget'];
    AdSlot::show($instance['format'], $instance['marker'], $instance['provider'] ?: NULL);
    //echo $args['after_widget'];
  }

  /**
   * Outputs the administrative widget form.
   */
  public function form($instance) {
    $instance += [
      'provider' => NULL,
      'format' => '',
      'marker' => TRUE,
      //'lazyload' => FALSE,
    ];
    $providers = Provider::getAll();
    $provider_disabled = count($providers) < 2 ? ' disabled' : '';
    $formats = Format::getAll();
    ?>

<p>
  <label for="<?= $this->get_field_id('provider') ?>"><?= __('Provider:', Plugin::L10N) ?></label>
  <select name="<?= $this->get_field_name('provider') ?>" id="<?= $this->get_field_id('provider') ?>"<?= $provider_disabled ?>>
    <option value=""><?= __('- Default -', Plugin::L10N) ?></option>
<?php foreach ($providers as $provider): ?>
    <option value="<?= esc_attr($provider->getId()) ?>"<?= $provider->getId() === $instance['provider'] ? ' selected' : '' ?>><?= $provider->getLabel() ?></option>
<?php endforeach; ?>
  </select>
</p>

<p>
  <label for="<?= $this->get_field_id('format') ?>"><?= __('Format:', Plugin::L10N) ?></label>
  <select name="<?= $this->get_field_name('format') ?>" id="<?= $this->get_field_id('format') ?>">
<?php foreach ($formats as $name => $format): ?>
    <option value="<?= esc_attr($name) ?>"<?= $name === $instance['format'] ? ' selected' : '' ?>><?= esc_html($format->getLabel()) ?></option>
<?php endforeach; ?>
  </select>
</p>

<p>
  <input type="checkbox" name="<?= $this->get_field_name('marker') ?>" value="1"<?= $instance['marker'] ? ' checked' : '' ?> id="<?= $this->get_field_id('marker') ?>">
  <label for="<?= $this->get_field_id('marker') ?>"><?= sprintf(__('Output "%s" marker', Plugin::L10N), __('Advertisement', Plugin::L10N)) ?></label>
</p>

<!--p>
  <input type="checkbox" name="<?= $this->get_field_name('lazyload') ?>" value="1"<?= !empty($instance['lazyload']) ? ' checked' : '' ?>>
  <label><?= __('Only load when visible', Plugin::L10N) ?></label>
</p-->
<?php
  }

  /**
   * Sanitizes widget form values as they are saved.
   */
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['provider'] = (int) $new_instance['provider'];
    $instance['format'] = $new_instance['format'];
    $instance['marker'] = (bool) $new_instance['marker'];
    //$instance['lazyload'] = (bool) $new_instance['lazyload'];
    return $instance;
  }

}
