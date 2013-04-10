<?php
$classes[] = 'data';
?>
<style>
  .invoice-specs-summary tr.blank {
    background-color: #fff !important;
  }
  .invoice-specs-summary tr.blank td {
    height: 1px !important;
    max-height: 1px !important;
  }
  .invoice-specs-summary td {
    padding: 2px 10px !important;
    width: 50%;
  }
  .invoice-specs-summary td.label {
    width: 100px;
    font-weight: bold;
    text-align: right !important;
  }
</style>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-specs-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td class="label">SPEC Barcode:</td>
    <td><?php echo $info['specs_barcode']; ?></td>
    <td class="label">EP Barcode:</td>
    <td><?php echo $info['exp_barcode']; ?></td>
  </tr>
  <tr>
    <td class="label">Exporter TIN:</td>
    <td><?php echo $info['operator_tin']; ?></td>
    <td class="label">Exporter Company Name:</td>
    <td><?php echo $info['operator_name']; ?></td>
  </tr>
  <tr>
    <td class="label">Port of Origin:</td>
    <td><?php echo SGS::locationify($info['origin']); ?></td>
    <td class="label">Expected Loading Date:</td>
    <td><?php echo $info['loading_date']; ?></td>
  </tr>
  <tr>
    <td class="label">Port of Destination:</td>
    <td><?php echo SGS::locationify($info['destination']); ?></td>
    <td class="label">Buyer:</td>
    <td><?php echo $info['buyer']; ?></td>
  </tr>
  <tr>
    <td class="label">Submitted By:</td>
    <td><?php echo $info['submitted_by']; ?></td>
    <td class="label">Date:</td>
    <td><?php echo SGS::date($info['create_date'], SGS::US_DATE_FORMAT); ?></td>
  </tr>
</table>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-specs-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td class="label" style="text-align: left !important;">Total Volume:</td>
    <td colspan="3" style="text-align: right;"><?php echo SGS::quantitify($total); ?></td>
  </tr>
</table>
