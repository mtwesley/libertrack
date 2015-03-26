<?php

$tempname = tempnam(sys_get_temp_dir(), 'br_');
Barcode::png('430LAA999AAX', $tempname);

?>

<style type="text/css">

@font-face {
  font-family: 'SGS';
  src: url('sgs_font_bold.ttf');
}

.barcode-text,
.company-text {
  font-family: 'SGS';
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

<div class="clear-all" style="padding-top: 5px;"></div>

<div class="right-float">
  <img class="fda-logo" src="fda_logo.png">
</div>

<div class="left-float">
  <div class="barcode-text"><span class="orange">LOG:</span> AA-999-AA-X</div>
  <div class="company-text">ALPHA LOGGING - FMC A</div>
</div>

<div class="clear-all">
  <img class="barcode-image" src="barcode.php?format=jpeg&size=38&barcode=430LAA999AAX">
</div>

<div class="left-float">
  <img class="libertrace-logo" src="libertrace_logo.png">
</div>
<div class="right-float">
  <div class="company-text rotate">ALPHA LOGGING - FMC A</div>
  <div class="barcode-text rotate"><span class="orange">LOG:</span> AA-999-AA-X</div>
</div>
