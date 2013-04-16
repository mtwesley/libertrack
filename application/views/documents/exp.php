<?php

$options = (array) $options + array(
  'header'    => TRUE,
  'footer'    => FALSE,
  'break'     => TRUE,
  'styles'    => FALSE,
  'info'      => FALSE,
  'summary'   => FALSE,
  'total'     => FALSE,
  'format'    => 'pdf'
);

?>
<?php if ($options['styles']): ?>
<style type="text/css">

  * {
    font-family: "Arial";
    font-size: 10px;
  }

  body, html {
    margin: 0;
  }

  table {
    border-collapse: collapse;
  }

  table.blank {
    width: 100% !important;
  }

  table.blank,
  table.blank tr,
  table.blank td,
  table.blank th {
    margin: 0 !important;
    padding: 0 !important;
    border: none !important;
  }

  img.floater {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0.5;
  }

  table tr td.blank {
    border: none;
  }

  .liberfor-logo {
    text-align: right;
  }

  .liberfor-logo img {
    height: 32px;
  }

  .sgs-logo {
    text-align: left;
  }

  .sgs-logo img {
    height: 35px;
  }

  .fda-logo img {
    height: 32px;
  }

  .exp {
    padding: 20px 25px;
  }

  .exp-header {}

  .exp-header,
  .exp-info,
  .exp-summary {
    width: 100%;
  }

  .exp-summary-table,
  .exp-details-table,
  .exp-signature-table,
  .exp-info-table,
  .exp-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .exp-summary-table tr td,
  .exp-details-table tr td,
  .exp-info-table tr td {
    width: 50%;
    padding: 2px 5px;
    border: 1px solid #000;
  }

  .exp-summary-table tr.head td,
  .exp-details-table tr.head td {
    padding: 6px 5px;
    background-color: #cfcfcf;
    font-weight: bold;
  }

  .exp-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .exp-summary-table tr td.volume,
  .exp-summary-table tr td.species_code,
  .exp-summary-table tr td.species_class,
  .exp-summary-table tr td.fob_price,
  .exp-summary-table tr td.tax_code,
  .exp-summary-table tr td.total {
    text-align: center;
  }

  .exp-summary-table tr td.fee_desc,
  .exp-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .exp-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .exp-details-table tr td {
    white-space: nowrap;
  }

  .exp-details-table tr td.barcode {
    text-align: left;
  }

  .exp-details-table tr td.scan_date,
  .exp-details-table tr td.volume,
  .exp-details-table tr td.species_code,
  .exp-details-table tr td.species_class,
  .exp-details-table tr td.diameter,
  .exp-details-table tr td.bottom,
  .exp-details-table tr td.top,
  .exp-details-table tr td.bottom_min,
  .exp-details-table tr td.bottom_max,
  .exp-details-table tr td.top_min,
  .exp-details-table tr td.top_max,
  .exp-details-table tr td.length,
  .exp-details-table tr td.grade,
  .exp-details-table tr td.total {
    text-align: center;
  }

  .exp-details-table tr td.number {
    text-align: right;
  }

  .exp-details-table tr td.diameter,
  .exp-details-table tr td.bottom,
  .exp-details-table tr td.top,
  .exp-details-table tr td.bottom_min,
  .exp-details-table tr td.bottom_max,
  .exp-details-table tr td.top_min,
  .exp-details-table tr td.top_max {
    padding: 2px 5px;
  }

  .exp-details-table tr td.total_volume {
    border-left: none;
    text-align: center;
  }

  .exp-details-table tr td.total_label {
    border-right: none;
  }

  .exp-details-table tr.even {
    background-color: #fafafa;
  }

  .exp-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .exp-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .exp-signature-table tr td.half-signature {
    height: 75px;
  }

  .exp-info-table {
    margin-bottom: 8px;
  }

  .exp-info-table tr td {
    padding: 4px 5px;
    vertical-align: top;
    width: 50%;
  }

  .exp-info-table tr td.label {
    font-weight: bold;
    background-color: #cfcfcf;
    width: 26%;
    white-space: nowrap;
  }

  .exp-info-table tr td.from,
  .exp-info-table tr td.to {
     text-align: right;
  }

  .exp-titles {}

  .exp-title {
    margin: 0 0 5px;
    text-align: center;
    font-weight: bold;
    font-size: 16px;
  }

  .exp-subtitle {
    margin-bottom: 8px;
    text-align: center;
    text-transform: uppercase;
  }

  .payment-date {
    margin-bottom: 15px;
  }

  .payment-message {
    margin: -5px auto 15px;
    padding: 5px 10px;
    width: 75%;
    font-style: italic;
    text-align: center;
    border: 1px solid #000;
  }

  .strong {
    margin-bottom: 3px;
    font-weight: bold;
  }

  .stronger {
    padding-top: 6px !important;
    font-weight: bold;
    font-size: 11px;
  }

  tr.info-bar,
  tr.info-bar td {
    padding-top: 4px !important;
    padding-bottom: 4px !important;
    background-color: #cfcfcf;
  }

  .label {
    font-weight: bold;
  }

  sup {
    font-size: 75%;
  }

  .verification {
    height: 60px;
  }

  .qr_image {
    float: right;
  }

  .qr_image img {
    height: 80px;
  }

</style>
<?php endif; ?>

<div class="exp <?php if ($options['break']) echo 'exp-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="exp-header">
    <table class="exp-header-table">
      <tr>
        <td class="sgs-logo"><img src="<?php echo DOCROOT; ?>images/invoice/sgs_logo.jpg" /></td>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
      </tr>
    </table>
    <div class="exp-title">Wood Products Export Permit</div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="exp-info">
    <table class="exp-info-table">
      <tr class="info-bar">
        <td style="border-right: none !important;"><span class="label">EP Number:</span> <?php echo $document->number ? 'EP ' . $document->number : 'DRAFT'; ?></td>
        <td style="border-left: none !important;"><span class="label">Date of Issue:</span> <?php echo SGS::date($document->created_date, SGS::US_DATE_FORMAT); ?></td>
      </tr>
      <tr>
        <td colspan="2" class="blank stronger">Exporter</td>
      </tr>
      <tr>
        <td>
          <div class="strong">Name and Address</div>
          <div><?php echo $document->operator->name; ?></div>
          <?php if ($document->operator->address): ?>
          <div><?php echo nl2br($document->operator->address); ?></div>
          <?php endif; ?>
        </td>
        <td>
          <div class="strong">Contact Details</div>
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
      <tr>
        <td colspan="2" class="blank stronger">Shipping Reference</td>
      </tr>
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
          <?php echo SGS::date($document->values['eta_date'], SGS::US_DATE_FORMAT); ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="blank stronger">Overall Shipment Description</td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="strong">Type</div>
          <?php echo $document->values['product_type']; ?>
        </td>
      </tr>
      <tr>
        <td>
          <div class="strong">Description</div>
          <?php echo $document->values['product_description']; ?>
        </td>
        <td>
          <div class="strong">Quantity</div>
          <?php echo SGS::quantitify($total_quantity); ?> m<sup>3</sup>
        </td>
      </tr>
      <tr>
        <td colspan="2">
          <div class="strong">Total FOB Value (USD)</div>
          $<?php echo SGS::amountify($total_fob); ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="blank stronger">Buyer</td>
      </tr>
      <tr>
        <td>
          <div class="strong">Name and Address</div>
          <div><?php echo $document->values['buyer']; ?></div>
          <?php if ($document->values['buyer_address']): ?>
          <div><?php echo nl2br($document->values['buyer_address']); ?></div>
          <?php endif; ?>
        </td>
        <td>
          <div class="strong">Contact Details</div>
          <?php if ($document->values['buyer_contact']): ?>
          <div><?php echo '<em>Contact:</em> '.$document->values['buyer_contact']; ?></div>
          <?php endif; ?>
          <?php if ($document->values['buyer_phone']): ?>
          <div><?php echo '<em>Phone:</em> '.$document->values['buyer_phone']; ?></div>
          <?php endif; ?>
          <?php if ($document->values['buyer_email']): ?>
          <div><?php echo '<em>E-mail:</em> '.$document->values['buyer_email']; ?></div>
          <?php endif; ?>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="blank stronger">SGS Verification</td>
      </tr>
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
        <td>
          <div class="strong">SGS Approval</div>
        </td>
      </tr>
      <tr class="verification">
        <td>
          <div class="strong">FOB Price Verification</div>
          <?php echo $document->values['fob_price_notes']; ?>
        </td>
        <td>
          <div class="strong">FDA Approval</div>
        </td>
      </tr>
      <tr>
        <td colspan="2" class="blank stronger">For Administration Use Only</td>
      </tr>
      <tr class="verification">
        <td colspan="2">
          <div class="qr_image"><img src="<?php echo $qr_image; ?>" /></div>
          <div class="strong">Notes</div>
          <?php echo $document->values['notes']; ?>
          <div class="clear clearfix"></div>
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['footer']): ?>
  <script>
  window.onload = function() {
    var vars = {};
    var x = document.location.search.substring(1).split('&');
    for (var i in x) {
      var z = x[i].split('=',2);
      vars[z[0]] = unescape(z[1]);
    }

    var x = ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection'];
    for (var i in x) {
      var y = document.getElementsByClassName(x[i]);
      for (var j = 0; j<y.length; ++j) y[j].textContent = vars[x[i]];
    }
  }
  </script>
  <style>
    * {
      font-family: "Arial";
      font-size: 13px;
    }

    img.liberfor-bw {
      height: 22px;
    }

    img.sgs-bw {
      height: 22px;
    }

    img.fda-bw {
      height: 22px;
    }

    .exp-footer {
      margin: 0 25px;
    }

    .exp-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .exp-footer-table tr td.date,
    .exp-footer-table tr td.info,
    .exp-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .exp-footer-table tr td.date,
    .exp-footer-table tr td.pageinfo {
      width: 120px;
    }

    .exp-footer-table tr td.date {
      text-align: left;
    }

    .exp-footer-table tr td.info {
      text-align: center;
    }

    .exp-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .exp-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="exp-footer">
    <table class="exp-footer-table">
      <tr>
        <td class="date"></td>
        <td class="info">
          <img class="liberfor-bw" src="<?php echo DOCROOT; ?>images/invoice/st_liberfor_bw.jpg" /> &nbsp; is operated by &nbsp; <img class="sgs-bw" src="<?php echo DOCROOT; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img class="fda-bw" src="<?php echo DOCROOT; ?>images/invoice/st_fda_small.jpg" /><br />
          LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia
        </td>
        <td class="pageinfo">
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($document->is_draft): ?>
<!-- <img class="floater" src="<?php // echo DOCROOT; ?>images/invoice/draft_copy.png" /> -->
<?php endif; ?>