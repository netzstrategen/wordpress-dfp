
<div id="<?= $id ?>" class="ad ad--<?= esc_attr($format) ?>">
<?php if ($marker): ?>
<div class="ad__marker"><?= __('Advertisement', \Netzstrategen\Dfp\Plugin::L10N) ?></div>
<?php endif; ?>
<script type="text/javascript">
googletag.cmd.push(function () { googletag.display('<?= $id ?>'); });
</script>
</div>
