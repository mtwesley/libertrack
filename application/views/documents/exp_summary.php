<?php
$classes[] = 'data';
?>
<style>
  .document-exp-summary tr.blank {
    background-color: #fff !important;
  }
  .document-exp-summary tr.blank td {
    height: 1px !important;
    max-height: 1px !important;
  }
  .document-exp-summary td {
    padding: 2px 10px !important;
    width: 50%;
    white-space: normal !important;
  }
  .document-exp-summary td.label {
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
<table class="<?php echo SGS::render_classes($classes); ?> document-exp-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <span class="label">EP Number: </span>&nbsp;
      <?php echo $document->number ? 'EP '.$document->number : 'DRAFT'; ?>
    </td>
    <td>
      <span class="label">Date of Issue:</span>&nbsp;
      <?php echo SGS::date($document->created_date, SGS::US_DATE_FORMAT); ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-exp-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <div class="strong">Exporter Name and Address</div>
      <div><?php echo $document->operator->name; ?></div>
      <?php if ($document->operator->address): ?>
      <div><?php echo nl2br($document->operator->address); ?></div>
      <?php endif; ?>
    </td>
    <td>
      <div class="strong">Exporter Contact Details</div>
      <div><em>TIN:</em> <?php echo $document->operator->tin; ?></div>
      <?php if ($document->operator->contact): ?>
      <div><?php echo '<em>Name:</em> '.$document->operator->contact; ?></div>
      <?php endif; ?>
      <?php if ($document->operator->phone): ?>
      <div><?php echo '<em>Phone:</em> '.$document->operator->phone; ?></div>
      <?php endif; ?>
      <?php if ($document->operator->email): ?>
      <div><?php echo '<em>E-mail:</em> '.$document->operator->email; ?></div>
      <?php endif; ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-exp-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <div class="strong">Port of Loading</div>
      <?php echo SGS::locationify($document->values['origin']); ?>
    </td>
    <td>
      <div class="strong">Name of Vessel</div>
      <?php echo $document->values['vessel']; ?>
    </td>
  </tr>
  <tr>
    <td>
      <div class="strong">Port of Destination</div>
      <?php echo SGS::locationify($document->values['destination']); ?>
    </td>
    <td>
      <div class="strong">ETA</div>
      <?php echo $document->values['eta_date']; ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-exp-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <div class="strong">Site Reference</div>
      <?php echo $document->values['site_reference']; ?>
    </td>
    <td>
      <div class="strong">Quantity</div>
      <?php echo SGS::quantitify($total_quantity); ?> m<sup>3</sup>
    </td>
  </tr>
  <tr>
    <td>
      <div class="strong">Type</div>
      <?php echo $document->values['product_type']; ?>
    </td>
    <td>
      <div class="strong">Total FOB Value (USD)</div>
      $<?php echo SGS::amountify($total_fob); ?>
    </td>
  </tr>
  <tr>
    <td colspan="2">
      <div class="strong">Description</div>
      <?php echo preg_replace('/(\w+): (\d+\.\d+)m3/i', '<em>$1:</em> $2m<sup>3</sup>', $document->values['product_description']); ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-exp-summary" style="border: 1px solid #D9C7AD;">
  <tr>
    <td>
      <div class="strong">Buyer Name and Address</div>
      <div><?php echo $document->values['buyer']; ?></div>
      <?php if ($document->values['buyer_address']): ?>
      <div><?php echo nl2br($document->values['buyer_address']); ?></div>
      <?php endif; ?>
    </td>
    <td>
      <div class="strong">Buyer Contact Details</div>
      <?php if ($document->values['buyer_contact']): ?>
      <div><?php echo '<em>Name:</em> '.$document->values['buyer_contact']; ?></div>
      <?php endif; ?>
      <?php if ($document->values['buyer_phone']): ?>
      <div><?php echo '<em>Phone:</em> '.$document->values['buyer_phone']; ?></div>
      <?php endif; ?>
      <?php if ($document->values['buyer_email']): ?>
      <div><?php echo '<em>E-mail:</em> '.$document->values['buyer_email']; ?></div>
      <?php endif; ?>
    </td>
  </tr>
</table>

<table class="<?php echo SGS::render_classes($classes); ?> document-exp-summary" style="border: 1px solid #D9C7AD;">
  <tr class="verification">
    <td>
      <div class="strong">Physical Inspection</div>
      <table class="blank">
        <tr>
          <td><div class="strong">Date</div></td>
          <td><div class="strong">Location</div></td>
        </tr>
        <tr>
          <td><?php echo SGS::date($document->values['inspection_date'], SGS::US_DATE_FORMAT); ?></td>
          <td><?php echo $document->values['inspection_location']; ?></td>
        </tr>
      </table>
    </td>
  </tr>
</table>
