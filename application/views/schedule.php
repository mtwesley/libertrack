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

  .schedule {
    padding: 20px 25px;
  }

  .schedule-page-break {
    page-break-before: always;
  }

  .schedule-header {}

  .schedule-header,
  .schedule-info,
  .schedule-summary {
    width: 100%;
  }

  .schedule-summary-table,
  .schedule-details-table,
  .schedule-signature-table,
  .schedule-info-table,
  .schedule-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .schedule-summary-table tr td,
  .schedule-details-table tr td,
  .schedule-info-table tr td {
    padding: 8px 5px;
    border: 1px solid #000;
  }

  .schedule-summary-table tr.head td,
  .schedule-details-table tr.head td {
    padding: 8px 5px;
    background-color: #cfcfcf;
    font-weight: bold;
    text-align: center;
  }

  .schedule-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .schedule-summary-table tr td {
    width: 30%;
    text-align: center;
  }

  .schedule-summary-table tr td.fee_desc,
  .schedule-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .schedule-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .schedule-details-table tr td {
    white-space: nowrap;
  }

  .schedule-details-table tr td.barcode {
    text-align: left;
  }

  .schedule-details-table tr td.scan_date,
  .schedule-details-table tr td.volume,
  .schedule-details-table tr td.species_code,
  .schedule-details-table tr td.species_class,
  .schedule-details-table tr td.diameter,
  .schedule-details-table tr td.bottom,
  .schedule-details-table tr td.top,
  .schedule-details-table tr td.bottom_min,
  .schedule-details-table tr td.bottom_max,
  .schedule-details-table tr td.top_min,
  .schedule-details-table tr td.top_max,
  .schedule-details-table tr td.length,
  .schedule-details-table tr td.grade,
  .schedule-details-table tr td.total {
    text-align: center;
  }

  .schedule-details-table tr td.number {
    text-align: right;
  }

  .schedule-details-table tr td.diameter,
  .schedule-details-table tr td.bottom,
  .schedule-details-table tr td.top,
  .schedule-details-table tr td.bottom_min,
  .schedule-details-table tr td.bottom_max,
  .schedule-details-table tr td.top_min,
  .schedule-details-table tr td.top_max {
    padding: 2px 5px;
  }

  .schedule-details-table tr td.total_volume {
    border-left: none;
    text-align: center;
  }

  .schedule-details-table tr td.total_label {
    border-right: none;
  }

  .schedule-details-table tr.even {
    background-color: #fafafa;
  }

  .schedule-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .schedule-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .schedule-signature-table tr td.half-signature {
    height: 75px;
  }

  .schedule-info-table {
    margin-bottom: 8px;
    border: none;
  }

  .schedule-info-table tr td {
    padding: 4px 5px;
    vertical-align: top;
    border: none;
  }

  .schedule-info-table tr td.label {
    font-weight: bold;
    width: 1px;
    white-space: nowrap;
  }

  .schedule-titles {}

  .schedule-title {
    margin: 2px 0 5px;
    font-size: 18px;
    text-align: center;
  }

  .schedule-subtitle {
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
    text-align: center;
    vertical-align: middle;
  }

  .qr_image img {
    height: 75px;
  }


</style>
<?php endif; ?>

<div class="schedule <?php if ($options['break']) echo 'schedule-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="schedule-header">
    <table class="schedule-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="schedule-title">Block Map /<br/> Stock Survey Inspection Schedule</div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="schedule-info">
    <table class="schedule-info-table">
      <tr>
        <td class="label">Operator:</td>
        <td><?php echo $block->site->operator->name; ?></td>
        <td class="label">TIN:</td>
        <td><?php echo $block->site->operator->tin; ?></td>
      </tr>
      <tr>
        <td class="label">Site:</td>
        <td><?php echo $block->site->name; ?></td>
        <td class="label">Block:</td>
        <td><?php echo $block->name; ?></td>
      </tr>
      <tr>
        <td class="label">Date:</td>
        <td><?php echo SGS::date('now', SGS::US_DATE_FORMAT); ?></td>
        <td></td>
        <td></td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['summary']): ?>
  <div class="schedule-summary">
    <table class="schedule-summary-table">
      <?php if ($schedule): ?>
      <tr class="head">
        <td class="cell_reference" colspan="2">Cell Reference</td>
        <td class="verified" rowspan="2">Verified</td>
      </tr>
      <tr class="head">
        <td class="survey_line">Survey Line</td>
        <td class="cell_number">Cell Number</td>
      </tr>
      <?php foreach ($schedule as $cell): ?>
      <tr class="<?php print SGS::odd_even($odd); ?>">
        <td class="survey_line"><?php echo $cell['survey_line']; ?></td>
        <td class="cell_number"><?php echo $cell['cell_number']; ?></td>
        <td class="verified"></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
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

    .schedule-footer {
      margin: 0 25px;
    }

    .schedule-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .schedule-footer-table tr td.date,
    .schedule-footer-table tr td.info,
    .schedule-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .schedule-footer-table tr td.date,
    .schedule-footer-table tr td.pageinfo {
      width: 120px;
    }

    .schedule-footer-table tr td.date {
      text-align: left;
    }

    .schedule-footer-table tr td.info {
      text-align: center;
    }

    .schedule-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .schedule-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="schedule-footer">
    <table class="schedule-footer-table">
      <tr>
        <td class="date"></td>
        <td class="info">
          <img class="liberfor-bw" src="<?php echo DOCROOT; ?>images/invoice/st_liberfor_bw.jpg" /> &nbsp; is operated by &nbsp; <img class="sgs-bw" src="<?php echo DOCROOT; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img class="fda-bw" src="<?php echo DOCROOT; ?>images/invoice/st_fda_small.jpg" /><br />
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
