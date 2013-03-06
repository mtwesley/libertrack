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

$num = $cntr;

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

  .specs {
    padding: 20px 25px;
  }

  .specs-page-break {
    page-break-before: always;
  }

  .specs-header {}

  .specs-header,
  .specs-info,
  .specs-summary {
    width: 100%;
  }

  .specs-summary-table,
  .specs-details-table,
  .specs-signature-table,
  .specs-info-table,
  .specs-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .specs-summary-table tr td,
  .specs-details-table tr td,
  .specs-info-table tr td {
    padding: 2px 5px;
    border: 1px solid #000;
  }

  .specs-summary-table tr.head td,
  .specs-details-table tr.head td {
    padding: 6px 5px;
    background-color: #bfbfbf;
    font-weight: bold;
  }

  .specs-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .specs-summary-table tr td.volume,
  .specs-summary-table tr td.species_code,
  .specs-summary-table tr td.species_class,
  .specs-summary-table tr td.fob_price,
  .specs-summary-table tr td.tax_code,
  .specs-summary-table tr td.total {
    text-align: center;
  }

  .specs-summary-table tr td.fee_desc,
  .specs-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .specs-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .specs-details-table tr td {
    white-space: nowrap;
  }

  .specs-details-table tr td.barcode {
    text-align: left;
  }

  .specs-details-table tr td.scan_date,
  .specs-details-table tr td.volume,
  .specs-details-table tr td.species_code,
  .specs-details-table tr td.species_class,
  .specs-details-table tr td.diameter,
  .specs-details-table tr td.bottom,
  .specs-details-table tr td.top,
  .specs-details-table tr td.bottom_min,
  .specs-details-table tr td.bottom_max,
  .specs-details-table tr td.top_min,
  .specs-details-table tr td.top_max,
  .specs-details-table tr td.length,
  .specs-details-table tr td.grade,
  .specs-details-table tr td.total {
    text-align: center;
  }

  .specs-details-table tr td.number {
    text-align: right;
  }

  .specs-details-table tr td.diameter,
  .specs-details-table tr td.bottom,
  .specs-details-table tr td.top,
  .specs-details-table tr td.bottom_min,
  .specs-details-table tr td.bottom_max,
  .specs-details-table tr td.top_min,
  .specs-details-table tr td.top_max {
    padding: 2px 5px;
  }

  .specs-details-table tr td.total_volume {
    border-left: none;
    text-align: center;
  }

  .specs-details-table tr td.total_label {
    border-right: none;
  }

  .specs-details-table tr.even {
    background-color: #f2f2f2;
  }

  .specs-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .specs-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .specs-signature-table tr td.half-signature {
    height: 75px;
  }

  .specs-info-table {
    margin-bottom: 8px;
  }

  .specs-info-table tr td {
    padding: 4px 5px;
    vertical-align: top;
    width: 24%;
  }

  .specs-info-table tr td.label {
    font-weight: bold;
    background-color: #bfbfbf;
    width: 26%;
    white-space: nowrap;
  }

  .specs-info-table tr td.from,
  .specs-info-table tr td.to {
     text-align: right;
  }

  .specs-titles {}

  .specs-title {
    margin: 0 0 5px;
    font-size: 22px;
    text-align: center;
  }

  .specs-subtitle {
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

</style>
<?php endif; ?>

<div class="specs <?php if ($options['break']) echo 'specs-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="specs-header">
    <table class="specs-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="specs-title">Export Shipment Specification - Logs</div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="specs-info">
    <table class="specs-info-table">
      <tr>
        <td class="label">SPEC Barcode:</td>
        <td><?php echo $info['specs_barcode']; ?></td>
        <td class="label">SPEC Number:</td>
        <td><?php echo 'SPECS '.$info['specs_number']; ?></td>
      </tr>
      <tr>
        <td class="label">EP Barcode:</td>
        <td><?php echo $info['exp_barcode']; ?></td>
        <td class="label">EP Number:</td>
        <td><?php echo $info['exp_number']; ?></td>
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
  </div>
  <?php endif; ?>

  <?php if ($options['details']): ?>
  <div class="specs-details">
    <table class="specs-details-table">
      <?php if ($data): ?>
      <tr class="head">
        <td class="number" rowspan="3">No.</td>
        <td class="barcode" rowspan="3">Log Barcode</td>
        <td class="species_code" rowspan="3">Species Code</td>
        <td class="diameter" colspan="4">Diameter (underbark to nearest cm)</td>
        <td class="length" rowspan="3">Length (m) <br />to nearest 0.1m</td>
        <td class="grade" rowspan="3">ATIBT<br />Grade</td>
        <td class="volume" rowspan="3">Volume<br />(m<sup>3</sup>)</td>
      </tr>
      <tr class="head">
        <td class="bottom" colspan="2">Butt</td>
        <td class="top" colspan="2">Top</td>
      </tr>
      <tr class="head">
        <td class="bottom_max">D1</td>
        <td class="bottom_min">D2</td>
        <td class="top_max">D3</td>
        <td class="top_min">D4</td>
      </tr>
      <?php foreach ($data as $record): ?>
      <tr class="<?php print SGS::odd_even($odd); ?>">
        <td class="number"><?php echo ++$num; ?></td>
        <td class="barcode"><?php echo $record->barcode->barcode; ?></td>
        <td class="species_code"><?php echo $record->species->code; ?></td>
        <td class="bottom_max"><?php echo $record->bottom_max; ?></td>
        <td class="bottom_min"><?php echo $record->bottom_min; ?></td>
        <td class="top_max"><?php echo $record->top_max; ?></td>
        <td class="top_min"><?php echo $record->top_min; ?></td>
        <td class="length"><?php echo SGS::quantitify($record->length, 1); ?></td>
        <td class="grade"><?php echo $record->grade; ?></td>
        <td class="volume"><?php echo SGS::quantitify($record->volume); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if ($options['total']): ?>
      <tr>
        <td class="total_label" colspan="9"><strong>Total volume (m<sup)3</sup>)</strong></td>
        <td class="total_volume"><?php echo SGS::quantitify($info['total']); ?></td>
      </tr>
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
      font-size: 13px;
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

    .specs-footer {
      margin: 0 25px;
    }

    .specs-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .specs-footer-table tr td.date,
    .specs-footer-table tr td.info,
    .specs-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .specs-footer-table tr td.date,
    .specs-footer-table tr td.pageinfo {
      width: 120px;
    }

    .specs-footer-table tr td.date {
      text-align: left;
    }

    .specs-footer-table tr td.info {
      text-align: center;
    }

    .specs-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .specs-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="specs-footer">
    <table class="specs-footer-table">
      <tr>
        <td class="date"><?php echo SGS::date('now', SGS::PRETTY_DATE_FORMAT); ?></td>
        <td class="info">
          <img class="liberfor-bw" src="<?php echo DOCROOT; ?>images/invoice/st_liberfor_bw.jpg" /> &nbsp; is operated by &nbsp; <img class="sgs-bw" src="<?php echo DOCROOT; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img class="fda-bw" src="<?php echo DOCROOT; ?>images/invoice/st_fda_small.jpg" /><br />
          LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia
        </td>
        <td class="pageinfo">
          <div class="ref"></div>
          Page <span class="page"><?php echo $page; ?></span> of <span class="topage"><?php echo $page_count; ?></span>
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($info['is_draft']): ?>
<!-- <img class="floater" src="<?php // echo DOCROOT; ?>images/invoice/draft_copy.png" /> -->
<?php endif; ?>