<?php
$classes[] = 'data';
?>
<style>
  .document-cert-summary tr.blank {
    background-color: #fff !important;
  }
  .document-cert-summary tr.blank td {
    height: 1px !important;
    max-height: 1px !important;
  }
  .document-cert-summary td {
    padding: 2px 10px !important;
    width: 33%;
    white-space: normal !important;
    vertical-align: top;
  }
  .document-cert-summary td.label {
    width: 100px;
    font-weight: bold;
    text-align: right !important;
  }
  .strong {
    margin-bottom: 3px;
    font-weight: bold;
  }

  .stronger {
    padding-top: 8px !important;
    font-weight: bold;
    font-size: 11px;
  }

  table.blank,
  table.blank tr,
  table.blank td,
  table.blank th {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
  }

  .label {
    font-weight: bold;
  }

  sup {
    font-size: 75%;
  }

  .verification {
    height: 75px;
  }
</style>
<table class="<?php echo SGS::render_classes($classes); ?> document-cert-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <span class="label">Statement Number: </span>&nbsp;
      <?php echo $document->number ? $document->number : 'DRAFT'; ?>
    </td>
    <td>
      <span class="label">Date of Issue:</span>&nbsp;
      <?php echo SGS::date($document->created_date, SGS::US_DATE_FORMAT); ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-cert-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <div class="strong">Exporter Name and Address</div>
      <div><?php echo $document->operator->name; ?></div>
      <?php if ($document->operator->address): ?>
      <div><?php echo nl2br($document->operator->address); ?></div>
      <?php endif; ?>
    </td>
    <td>
      <div class="strong">Buyer</div>
      <div><?php echo $document->values['buyer']; ?></div>
      <div class="strong">Vessel</div>
      <div><?php echo $document->values['vessel']; ?></div>
    </td>
    <td>
      <div class="strong">Site Reference</div>
      <?php echo $document->values['site_reference']; ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-cert-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <div class="strong">Shipment Specification Volume</div>
      <?php echo SGS::quantitify($specs_volume); ?> m<sup>3</sup>
    </td>
    <td>
      <div class="strong">Loaded Volume</div>
      <?php echo SGS::quantitify($loaded_volume); ?> m<sup>3</sup>
    </td>
    <td>
      <div class="strong">Short-shipped Volume</div>
      <?php echo SGS::quantitify($short_shipped_volume); ?> m<sup>3</sup>
    </td>
  </tr>
</table>
