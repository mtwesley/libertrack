<?php

$options = (array) $options + array(
  'header'    => TRUE,
  'footer'    => FALSE,
  'break'     => TRUE,
  'styles'    => FALSE,
  'info'      => FALSE,
  'summary'   => TRUE,
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
    text-align: left;
  }

  .liberfor-logo img {
    height: 32px;
  }

  .fda-logo {
    text-align: right;
  }

  .fda-logo img {
    height: 32px;
  }

  .certificate {
    padding: 20px 25px;
  }

  .certificate-page-break {
    page-break-before: always;
  }

  .certificate-header {}

  .certificate-header,
  .certificate-info,
  .certificate-summary {
    width: 100%;
  }

  .certificate-summary-table,
  .certificate-details-table,
  .certificate-signature-table,
  .certificate-info-table,
  .certificate-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .certificate-summary-table tr td,
  .certificate-details-table tr td,
  .certificate-info-table tr td {
    padding: 8px 5px;
    border: 1px solid #000;
  }

  .certificate-summary-table tr.head td,
  .certificate-details-table tr.head td {
    padding: 8px 5px;
    background-color: #cfcfcf;
    font-weight: bold;
    text-align: center;
  }

  .certificate-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .certificate-summary-table tr td {
    width: 30%;
    text-align: center;
  }

  .certificate-summary-table tr td.fee_desc,
  .certificate-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .certificate-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .certificate-details-table tr td {
    white-space: nowrap;
  }

  .certificate-details-table tr td.barcode {
    text-align: left;
  }

  .certificate-details-table tr td.scan_date,
  .certificate-details-table tr td.volume,
  .certificate-details-table tr td.species_code,
  .certificate-details-table tr td.species_class,
  .certificate-details-table tr td.diameter,
  .certificate-details-table tr td.bottom,
  .certificate-details-table tr td.top,
  .certificate-details-table tr td.bottom_min,
  .certificate-details-table tr td.bottom_max,
  .certificate-details-table tr td.top_min,
  .certificate-details-table tr td.top_max,
  .certificate-details-table tr td.length,
  .certificate-details-table tr td.grade,
  .certificate-details-table tr td.total {
    text-align: center;
  }

  .certificate-details-table tr td.number {
    text-align: right;
  }

  .certificate-details-table tr td.diameter,
  .certificate-details-table tr td.bottom,
  .certificate-details-table tr td.top,
  .certificate-details-table tr td.bottom_min,
  .certificate-details-table tr td.bottom_max,
  .certificate-details-table tr td.top_min,
  .certificate-details-table tr td.top_max {
    padding: 2px 5px;
  }

  .certificate-details-table tr td.total_volume {
    border-left: none;
    text-align: center;
  }

  .certificate-details-table tr td.total_label {
    border-right: none;
  }

  .certificate-details-table tr.even {
    background-color: #fafafa;
  }

  .certificate-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .certificate-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .certificate-signature-table tr td.half-signature {
    height: 75px;
  }

  .certificate-info-table {
    width: 100%;
    margin-bottom: 8px;
    border: none;
  }

  .certificate-info-table tr td {
    padding: 4px 10px 4px 5px;
    vertical-align: top;
    border: 1px solid;
  }

  .certificate-info-table tr td.label {
    font-weight: bold;
    width: 1px;
    white-space: nowrap;
    background-color: #cfcfcf;
  }

  .certificate-titles {}

  .certificate-title {
    margin: 2px 0 5px;
    font-size: 18px;
    text-align: center;
  }

  .certificate-subtitle {
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

  sup {
    font-size: 75%;
  }

  .qr_image {
    margin-left: 10px;
    margin-bottom: 10px;
    padding: 5px;
    float: right;
    text-align: center;
    vertical-align: middle;
  }

  .qr_image img {
    height: 75px;
  }


</style>
<?php endif; ?>

<div class="certificate <?php if ($options['break']) echo 'certificate-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="certificate-header">
    <table class="certificate-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_libertrace.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="certificate-title"><?php if ($document->is_draft) echo 'Draft '; ?>Certificate of Origin</div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <br /><br />
  <div class="qr_image"><img src="<?php echo $qr_image; ?>" /></div>
  <strong>
  Statement # SGS - LiberFor - <?php echo $document->is_draft ? 'DRAFT' : $document->values['statement_number']; ?><br />
  Date: <?php echo SGS::date('now', SGS::CERTIFICATE_DATE_FORMAT); ?><br />
  </strong>
  <br /><br />
  Further to the inspection carried out by SGS Liberia, Inc. at
  <?php echo $document->values['inspection_location']; ?> - Liberia, the company:<br />
  <br />
  <strong>
  <?php echo $document->operator->name; ?>
  <?php echo nl2br($document->operator->address); ?>
  </strong>
  <br /><br />
  Has been successfully evaluated on the origin of the following consignment(s):<br />
  <br />
  <table class="certificate-info-table">
    <tr>
      <td class="label" style="width: 33%;">Round Logs</td>
      <td class="label" style="width: 33%;">Vessel Name</td>
      <td class="label" style="width: 33%;">Buyer</td>
    </tr>
    <tr>
      <td><?php echo SGS::quantitify($loaded_volume); ?> m3</td>
      <td><?php echo $document->values['vessel']; ?></td>
      <td><?php echo $document->values['buyer']; ?></td>
    </tr>
  </table>
  <br /><br />
  References of the consignment:<br />
  <br />
  <table class="certificate-info-table" style="width: 70%;">
    <tr>
      <td class="label" style="padding-right: 20px;">Export Permit Number</td>
      <td>EP <?php echo SGS::numberify($document->values['exp_number']); ?></td>
    </tr>
    <tr>
      <td class="label">Shipment Specification Number</td>
      <td>SPEC <?php echo SGS::implodify((array)$exp_document->values['specs_number']); ?></td>
    </tr>
    <tr>
      <td class="label">Shipment Specification Volume</td>
      <td><?php echo SGS::quantitify($specs_volume); ?> m3 <em>(<?php echo SGS::quantitify($short_shipped_volume); ?> m3 short-shipped)</em></td>
    </tr>
    <tr>
      <td class="label">Forest Permit of Origin</td>
      <td><?php echo $document->values['site_reference']; ?></td>
    </tr>
  </table>
  <br /><br />
  This Certificate of Origin from the Republic of Liberia for the above wood
  products is established according to the Standard Operating Procedures of
  LiberFor, the National Chain of Custody System under development by
  SGS Liberia Inc.<br />
  <br /><br /><br /><br /><br /><br />
  <div style="text-align: right;">
  Dr. Shiv S. Panse<br />
  Government and Institutions (GIS)<br />
  Forestry Project Coordinator - LAS Team Leader<br />
  <a href="mailto:shiv.panse@sgs.com">shiv.panse@sgs.com</a><br />
  +231 (0) 886 785 992
  </div>
  <br /><br />
  This statement is issued on behalf of SGS Liberia Inc. The findings recorded
  hereon are based upon assessment performed by SGS Liberia Inc., the results
  of which are valid for the scope and the time of the intervention only as
  referenced above. This statement does not relieve the company from compliance
  with any bylaws, federal, national or regional acts and regulations.
  Stipulations to the contrary are not binding on SGS Liberia Inc. and
  SGS Liberia Inc. shall have no responsibility vis-a-vis parties other than
  the company.
  <br /><br />
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
      font-size: 10px;
    }

    img.liberfor-bw {
      height: 20px;
    }

    img.sgs-bw {
      height: 20px;
    }

    img.fda-bw {
      height: 20px;
    }

    .certificate-footer {
      margin: 0 25px;
    }

    .certificate-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .certificate-footer-table tr td.date,
    .certificate-footer-table tr td.info,
    .certificate-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .certificate-footer-table tr td.date,
    .certificate-footer-table tr td.pageinfo {
      width: 120px;
    }

    .certificate-footer-table tr td.date {
      text-align: left;
    }

    .certificate-footer-table tr td.info {
      text-align: center;
    }

    .certificate-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .certificate-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="certificate-footer">
    <table class="certificate-footer-table">
      <tr>
        <td class="date"></td>
        <td class="info">
          <img class="liberfor-bw" src="<?php echo DOCROOT; ?>images/invoice/st_libertrace_bw.jpg" /> &nbsp; is operated by &nbsp; <img class="sgs-bw" src="<?php echo DOCROOT; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img class="fda-bw" src="<?php echo DOCROOT; ?>images/invoice/st_fda_small.jpg" /><br />
          LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia
        </td>
        <td class="pageinfo">
          <div class="ref"></div>
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>
