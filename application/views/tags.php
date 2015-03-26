<style type="text/css">

@font-face {
  font-family: 'SGS';
  src: url('<?php echo DOCROOT; ?>fonts/sgs_font_bold.ttf');
}

.page-break {
  page-break-before: always;
}

.barcode-text,
.company-text {
  font-family: 'SGS';
  text-transform: uppercase;
}

.barcode-text {
  font-size: 16px;
}

.company-text {
  font-size: 10px;
}

.barcode-image {
  padding: 5px 0;
  height: 35px;
  width: 245px;
  position: relative;
  left: -13px;
}

.fda-logo {
  height: 30px;
}

.libertrace-logo {
  height: 27px;
}

.right-float {
  float: right;
}

.left-float {
  float: left;
}

.clear-all {
  clear: both;
}

.orange {
  color: #ff6600;
}

.rotate {
  -webkit-transform:rotate(-180deg);
  -moz-transform:rotate(-180deg); 
  -o-transform:rotate(-180deg); 
  transform:rotate(-180deg);
  ms-filter:"progid:DXImageTransform.Microsoft.BasicImage(rotation=2)";
  filter:progid:DXImageTransform.Microsoft.BasicImage(rotation=2);
}
</style>

<?php $first = TRUE; ?>
<?php foreach ($barcodes as $barcode): ?>

<?php if (!$first): ?>
<div class="clear-all <?php if ($options['break']) echo 'page-break'; ?>"></div>
<?php endif; ?>

<?php
$first = FALSE;
$tempname = tempnam(sys_get_temp_dir(), 'br_');
$barcode->image($tempname);
?>

<div class="right-float">
  <img class="fda-logo" src="<?php echo DOCROOT; ?>images/tags/fda_logo.png">
</div>

<div class="left-float">
  <div class="barcode-text">
    <span class="orange"><?php echo $printjob->type ? SGS::$printjob_type[$printjob->type] : 'LOG'; ?>:</span> 
    <?php echo $barcode->pretty(); ?>
  </div>
  <div class="company-text">
    <?php echo $printjob->site->operator->short_name ? $printjob->site->operator->short_name : $printjob->site->operator->name; ?> - 
    <?php echo $printjob->site->name; ?>
  </div>
</div>

<div class="clear-all">
  <img class="barcode-image" src="<?php echo $tempname; ?>">
</div>

<div class="left-float">
  <img class="libertrace-logo" src="<?php echo DOCROOT; ?>images/tags/libertrace_logo.png">
</div>
<div class="right-float">
  <div class="company-text rotate">
    <?php echo $printjob->site->operator->short_name ? $printjob->site->operator->short_name : $printjob->site->operator->name; ?> - 
    <?php echo $printjob->site->name; ?>
  </div>
  <div class="barcode-text rotate">
    <span class="orange"><?php echo $printjob->type ? SGS::$printjob_type[$printjob->type] : 'LOG'; ?>:</span> 
    <?php echo $barcode->pretty(); ?>
  </div>
</div>
<?php endforeach; ?>