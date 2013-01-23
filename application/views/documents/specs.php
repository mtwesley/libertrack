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

  .invoice {
    padding: 20px 25px;
  }

  .st-invoice {}

  .invoice-page-break {
    page-break-before: always;
  }

  .invoice-header {}

  .invoice-header,
  .invoice-info,
  .invoice-summary {
    width: 100%;
  }

  .invoice-summary-table,
  .invoice-details-table,
  .invoice-signature-table,
  .invoice-info-table,
  .invoice-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .invoice-summary-table tr td,
  .invoice-details-table tr td,
  .invoice-info-table tr td {
    padding: 2px 5px;
    border: 1px solid #000;
  }

  .invoice-summary-table tr.head td,
  .invoice-details-table tr.head td {
    padding: 6px 5px;
    background-color: #bfbfbf;
    font-weight: bold;
  }

  .invoice-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .invoice-summary-table tr td.volume,
  .invoice-summary-table tr td.species_code,
  .invoice-summary-table tr td.species_class,
  .invoice-summary-table tr td.fob_price,
  .invoice-summary-table tr td.tax_code,
  .invoice-summary-table tr td.total {
    text-align: center;
  }

  .invoice-summary-table tr td.fee_desc,
  .invoice-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .invoice-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .invoice-details-table tr td.barcode,
  .invoice-details-table tr td.scan_date,
  .invoice-details-table tr td.volume,
  .invoice-details-table tr td.species_code,
  .invoice-details-table tr td.species_class,
  .invoice-details-table tr td.diameter,
  .invoice-details-table tr td.bottom,
  .invoice-details-table tr td.top,
  .invoice-details-table tr td.bottom_min,
  .invoice-details-table tr td.bottom_max,
  .invoice-details-table tr td.top_min,
  .invoice-details-table tr td.top_max,
  .invoice-details-table tr td.length,
  .invoice-details-table tr td.grade,
    .invoice-details-table tr td.total {
    text-align: center;
  }

  .invoice-details-table tr td.total_volume {
    border-left: none;
    text-align: center;
  }

  .invoice-details-table tr td.total_species {
    border-right: none;
  }

  .invoice-details-table tr.even {
    background-color: #f2f2f2;
  }

  .invoice-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .invoice-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .invoice-signature-table tr td.half-signature {
    height: 75px;
  }

  .invoice-info-table {
    margin-bottom: 5px;
  }

  .invoice-info-table tr td {
    padding: 10px 5px;
    vertical-align: top;
    width: 25%;
  }

  .invoice-info-table tr td.label {
    font-weight: bold;
    background-color: #bfbfbf;
  }

  .invoice-info-table tr td.from,
  .invoice-info-table tr td.to {
     text-align: right;
  }

  .invoice-titles {}

  .invoice-title {
    margin: 7px 0 15px;
    font-size: 20px;
    text-align: center;
  }

  .invoice-subtitle {
    margin-bottom: 25px;
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

<div class="st-invoice invoice <?php if ($options['break']) echo 'invoice-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="invoice-header">
    <table class="invoice-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="invoice-title">Export Shipment Specification - Logs</div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="invoice-info">
    <table class="invoice-info-table">
      <tr>
        <td class="label">Shipment Specification Barcode:</td>
        <td><?php echo $info['specs_barcode']; ?></td>
        <td class="label">Shipment Specification Number:</td>
        <td><?php echo $info['specs_number']; ?></td>
      </tr>
      <tr>
        <td class="label">Permit Request Barcode:</td>
        <td><?php echo $info['epr_barcode']; ?></td>
        <td class="label">Permit Request Number:</td>
        <td><?php echo $info['epr_number']; ?></td>
      </tr>
      <tr>
        <td class="label">Exporter TIN:</td>
        <td><?php echo $info['operator_tin']; ?></td>
        <td class="label">Exporter Company Name:</td>
        <td><?php echo $info['operator_name']; ?></td>
      </tr>
      <tr>
        <td class="label">Port of Origin:</td>
        <td><?php echo $info['origin']; ?></td>
        <td class="label">Expected Loading Date:</td>
        <td><?php echo $info['loading_date']; ?></td>
      </tr>
      <tr>
        <td class="label">Port of Destination:</td>
        <td><?php echo $info['destination']; ?></td>
        <td class="label">Buyer:</td>
        <td><?php echo $info['buyer']; ?></td>
      </tr>
      <tr>
        <td class="label">Submitted By:</td>
        <td><?php echo $info['submitted_by']; ?></td>
        <td class="label">Date:</td>
        <td><?php echo $info['create_date']; ?></td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['details']): ?>
  <div class="invoice-details">
    <table class="invoice-details-table">
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
        <td class="length"><?php echo $record->length; ?></td>
        <td class="grade"><?php echo $record->grade; ?></td>
        <td class="volume"><?php echo $record->volume; ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if ($options['total']): ?>
      <tr>
        <td class="total_label" colspan="6"><strong>Total volume (m<sup)3</sup>)</strong></td>
        <td class="total_volume"><?php echo $info['total']; ?></td>
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
      font-size: 12px;
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

    .invoice-footer {
      margin: 0 25px;
    }

    .invoice-footer-table {
      margin-top: 15px;
      width: 100%;
      border-collapse: collapse;
    }

    .invoice-footer-table tr td.date,
    .invoice-footer-table tr td.info,
    .invoice-footer-table tr td.pageinfo {
      vertical-align: bottom;
    }

    .invoice-footer-table tr td.date,
    .invoice-footer-table tr td.pageinfo {
      width: 110px;
    }

    .invoice-footer-table tr td.date {
      text-align: left;
    }

    .invoice-footer-table tr td.info {
      text-align: center;
    }

    .invoice-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .invoice-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="invoice-footer">
    <table class="invoice-footer-table">
      <tr>
        <td class="date"><?php echo SGS::date($invoice->created_date, SGS::PRETTY_DATE_FORMAT); ?></td>
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

<?php if ($invoice->is_draft): ?>
<!-- <img class="floater" src="<?php // echo DOCROOT; ?>images/invoice/draft_copy.png" /> -->
<?php endif; ?>