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
  sup {
    font-size: 75%;
  }
</style>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-specs-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td class="label">SH-SH Number:</td>
    <td><?php echo $document->number ? 'SPEC '.$document->number : 'DRAFT'; ?></td>
    <td class="label">EP Number:</td>
    <td><?php echo $document->values['exp_number']; ?></td>
  </tr>
  <tr>
    <td class="label">Exporter TIN:</td>
    <td><?php echo $document->operator->tin; ?></td>
    <td class="label">Exporter Company Name:</td>
    <td><?php echo $document->operator->name; ?></td>
  </tr>
  <tr>
    <td class="label">Site Reference:</td>
    <td><?php echo $document->values['site_reference']; ?></td>
    <td class="label">Loading Date:</td>
    <td><?php echo $document->values['loading_date']; ?></td>
  </tr>
  <tr>
    <td class="label">Port of Origin:</td>
    <td><?php echo SGS::locationify($document->values['origin']); ?></td>
    <td class="label">Buyer:</td>
    <td><?php echo $document->values['buyer']; ?></td>
  </tr>
  <tr>
    <td class="label">Port of Destination:</td>
    <td><?php echo SGS::locationify($document->values['destination']); ?></td>
    <td class="label">Date:</td>
    <td><?php echo SGS::date($document->created_date, SGS::US_DATE_FORMAT); ?></td>
  </tr>
  <tr>
    <td class="label">Inspected By:</td>
    <td><?php echo $document->values['inspected_by']; ?></td>
    <td class="label"></td>
    <td></td>
  </tr>
</table>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-specs-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td class="label" style="text-align: left !important; padding-left: 45px !important; width: 75px !important;">Total Volume:</td>
    <td colspan="3" style="width: inherit;"><?php echo SGS::quantitify($total); ?> m<sup>3</sup></td>
  </tr>
</table>
