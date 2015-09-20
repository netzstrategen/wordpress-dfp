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
   */
  public static function init() {
    register_widget(__CLASS__);
  }

  public function __construct() {
    parent::__construct('dfp', __('DFP Ad', Plugin::L10N), [
      'description' => __('An ad slot for DFP.', Plugin::L10N),
    ]);
  }

  /**
   * Front-end display of widget.
   */
  public function widget($args, $instance) {
    echo $args['before_widget'];
    AdSlot::createFromWidget($instance)->render();
    echo $args['after_widget'];
  }

  /**
   * Outputs the administrative widget form.
   */
  public function form($instance) {
    $instance += [
      'provider' => NULL,
      'size' => '',
      'marker' => TRUE,
      'lazyload' => FALSE,
    ];
    $formats = [
      'superbanner',
      'skyscraper',
      'billboard',
      'mediumrectangle',
      'bottom',
    ];
    ?>

<p>
  <label for="<?= $this->get_field_id('provider') ?>"><?= __('Provider:', Plugin::L10N) ?></label>
  <select name="<?= $this->get_field_name('provider') ?>" id="<?= $this->get_field_id('provider') ?>">
<?php foreach (Provider::getAll() as $provider): ?>
    <option value="<?= esc_attr($provider->getId()) ?>"><?= $provider->getLabel() ?></option>
<?php endforeach; ?>
  </select>
</p>

<p>
  <label for="<?= $this->get_field_id('format') ?>"><?= __('Format:', Plugin::L10N) ?></label>
  <select name="<?= $this->get_field_name('format') ?>" id="<?= $this->get_field_id('format') ?>">
<?php foreach ($formats as $value): ?>
    <option value="<?= esc_attr($value) ?>"><?= esc_html($value) ?></option>
<?php endforeach; ?>
  </select>
</p>

<p>
  <input type="checkbox" name="<?= $this->get_field_name('marker') ?>" value="1"<?= $instance['marker'] ? ' checked' : '' ?> id="<?= $this->get_field_id('marker') ?>">
  <label for="<?= $this->get_field_id('marker') ?>"><?= sprintf(__('Output "%s" marker', Plugin::L10N), __('Advertisement', Plugin::L10N)) ?></label>
</p>

<!--p>
  <input type="checkbox" name="<?= $this->get_field_name('lazyload') ?>" value="1"<?= $instance['lazyload'] ? ' checked' : '' ?>>
  <label><?= __('Only load when visible', Plugin::L10N) ?></label>
</p-->
<?php
  }

  /**
   * Sanitizes widget form values as they are saved.
   */
  public function update($new_instance, $old_instance) {
    $instance = array();
    $instance['provider'] = $new_instance['provider'];
    $instance['format'] = $new_instance['format'];
    $instance['lazyload'] = (bool) $new_instance['lazyload'];
    return $instance;
  }

}
