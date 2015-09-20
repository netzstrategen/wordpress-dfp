<div id="ad-<?= esc_attr($format) ?>-<?= $id_suffix ?>" class="ad ad--<?= esc_attr($format) ?>">
<?php if ($marker): ?>
<div class="ad__marker"><?= __('Advertisement', \Netzstrategen\Dfp\Plugin::L10N) ?></div>
<?php endif; ?>
<script type="text/javascript">
googletag.cmd.push(function () { googletag.display('ad-<?= esc_attr($format) ?>-<?= $id_suffix ?>'); });
</script>
</div>
