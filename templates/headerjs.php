<?php if (isset($omssite) && isset($omszone)): ?>

<script type="text/javascript">
var oms_site = "<?php echo $omssite; ?>";
var oms_zone = "<?php echo $omszone; ?>";
var omsv_centered = true;<?php // Indicate horizontally centered layout to Superbanner/Wallpaper ?>

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
<script type="text/javascript" src="<?php echo get_stylesheet_directory_uri(); ?>/js/omsvjs14_1.js"></script>
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

<?php endif; ?>

<script async type="text/javascript" src="//www.googletagservices.com/tag/js/gpt.js"></script>
<script type="text/javascript">
var googletag = googletag || {};
googletag.cmd = googletag.cmd || [];
googletag.cmd.push(function () {
<?php foreach ($adSpaces as $adSpace):
  // Responsive Size Mappings
  // @see https://support.google.com/dfp_premium/answer/3423562?hl=en
  // @see https://support.google.com/dfp_premium/answer/4578089
  $size_mappings = [];
  // Inject additional alternative ad slot sizes.
  if ($adSpace['adSpace']->width == 728 || FALSE !== stripos($adSpace['adSpace']->idName, 'super')) {
    $sizes = [728,90];
    $size_mappings[] = [[835,400], [728,90]];
  }
  elseif ($adSpace['adSpace']->width == 120 || $adSpace['adSpace']->width == 160 || FALSE !== stripos($adSpace['adSpace']->idName, 'sky')) {
    $sizes = [[120,600], [160,600], [200,600]];
    // The skyscraper appears to the right of the main content area, which is
    // centered horizontally, so the same amount of space is required to its left.
    // Additionally, there is a vertical browser scrollbar of approx. 16px.
    $size_mappings[] = [[960 + 16 + 2*200, 200], [[200,600], [160,600], [120,600]]];
    $size_mappings[] = [[960 + 16 + 2*160, 200], [[160,600], [120,600]]];
    $size_mappings[] = [[960 + 16 + 2*120, 200], [[120,600]]];
  }
  elseif ($adSpace['adSpace']->width == 800 || FALSE !== stripos($adSpace['adSpace']->idName, 'billboard')) {
    $sizes = [800,250];
    $size_mappings[] = [[835,400], [800,250]];
  }
  elseif (FALSE !== stripos($adSpace['adSpace']->idName, 'rect')) {
    if ($adSpace['adSpace']->height != 251) {
      $sizes = [[300,250], [300,600]];
      $size_mappings[] = [[835,320], [[300,250], [300,600]]];
    }
    else {
      $sizes = [300,251];
      $size_mappings[] = [[835,320], [300,251]];
    }
  }
  elseif (FALSE !== stripos($adSpace['adSpace']->idName, 'bottom') || $adSpace['adSpace']->height == 300) {
    $sizes = [[300,300]];
    $size_mappings[] = [[320,320], [300,300]];
  }
  else {
    $sizes = [(int) $adSpace['adSpace']->width, (int) $adSpace['adSpace']->height];
  }
  // The last size mapping has the lowest priority and thus applies if the
  // available screen dimensions are smaller than the earlier defined mappings
  // with higher priority. By defining no suitable sizes for "any" dimension
  // ([0,0]), no ad is rendered.
  if ($size_mappings) {
    $size_mappings[] = [[0,0], []];
  }
  $size_mappings = $size_mappings ? "\n    .defineSizeMapping(" . json_encode($size_mappings) . ")\n" : '';

  // Determine the DFP provider namespace.
  // @todo Verify whether OMS namespace can also be used for own DFP server now
  //   (sans "oms." prefix).
  if ($adSpace['dfp']->state == DFP_Id_MappingData::STATE_OMS) {
    $namespace = "/5766/$omssite/$omszone";
  }
  else {
    // Hierarchical Ad Units are supported by DFP Premium only. In regular DFP,
    // slashes have to be escaped (using a slash).
    $namespace = '/20204716/' . preg_replace('@/+@', '//', $adSpace['dfpId']);
  }
  ?>
  googletag.defineSlot('<?= $namespace ?>', <?= json_encode($sizes) ?>, 'div-gpt-ad-<?= $adSpace['adSpace']->getHtmlId() ?>')<?= $size_mappings ?>
    .addService(googletag.pubads());
<?php endforeach; ?>
  googletag.pubads().enableSingleRequest();
<?php //googletag.pubads().enableSyncRendering(); // document.write() breaks due to async. ?>
  googletag.pubads().collapseEmptyDivs();
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
  googletag.enableServices();
});
</script>
