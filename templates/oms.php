<!-- dfp_before_script -->

<script type="text/javascript">
var oms_site = "<?= $omssite; ?>";
var oms_zone = "<?= $omszone; ?>";
<?php // Indicate horizontally centered layout to Wallpaper ?>
var omsv_centered = true;

<?php // adlWallPaperLeft will be calculated once more for actual BODY width in footer.php. ?>
var docWidth = document.documentElement.clientWidth || jQuery(window).width();
var intPosLeft = ((docWidth / 2) + (960 / 2));
<?php if (is_admin_bar_showing()): ?>
var adlWallPaperTop = (intPosLeft < 782 ? 46 : 32);
<?php else: ?>
var adlWallPaperTop = 0;
<?php endif; ?>
var adlWallPaperLeft = parseInt(intPosLeft, 10);
var adlAutoScrolling = false;
</script>
<script type="text/javascript" src="<?= get_stylesheet_directory_uri(); ?>/js/omsvjs14_1.js"></script>
<script type="text/javascript">
(function (WLRCMD, yl, segQS, crtg_content) {
try {
  var ystr = '', y_adj = '', c, id;
  for (id in yl.YpResult.getAll()) {
    c = yl.YpResult.get(id);
    ystr += ';y_ad=' + c.id;
    if (c.format) {
      y_adj = ';y_adj=' + c.format;
    }
  }
  ystr += y_adj + ';';
  WLRCMD = WLRCMD + ystr + segQS + crtg_content;
}
catch (err) {}
})(WLRCMD, yl, segQS, crtg_content);
</script>

<!-- END dfp_before_script -->
<!-- dfp_script_append -->

<script type="text/javascript">
<?php //googletag.pubads().enableSyncRendering(); // document.write() breaks due to async. ?>
  googletag.pubads().setTargeting('bundesland', 'BW');

  if (typeof WLRCMD != 'undefined' && WLRCMD != '') {
    var values, id, id2, temp = WLRCMD.split(";"), temp2;
    for (id in temp) { 
      if (temp[id].indexOf('=') != -1) {
        values = temp[id].split('=')[1];
        for (id2 in temp) {
          if (temp[id2].indexOf('=') > -1 && (temp[id].split('=')[0] == temp[id2].split('=')[0]) && id < id2) {
            values += ';' + temp[id2].split('=')[1];
            delete temp[id2];
          }
        }
        temp2 = values.split(";");
        googletag.pubads().setTargeting(temp[id].split('=')[0], temp2);
      }
    }
  }
</script>

<!-- END dfp_script_append -->
