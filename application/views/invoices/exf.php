<?php

$options = (array) $options + array(
  'header'    => TRUE,
  'footer'    => FALSE,
  'break'     => TRUE,
  'styles'    => FALSE,
  'info'      => FALSE,
  'summary'   => FALSE,
  'signature' => FALSE,
  'fee'       => FALSE,
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

  .exf-invoice {}

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

  .invoice-summary-table tr td {
    padding: 2px 5px;
    border: 1px solid #000;
  }

  .invoice-details-table tr td {
    padding: 1px 5px;
    border: 1px solid #000;
  }

  .invoice-summary-table tr.fat td,
  .invoice-details-table tr.fat td {
    padding: 3px 5px;
  }

  .invoice-summary-table tr.head td,
  .invoice-details-table tr.head td {
    padding: 6px 5px;
    background-color: #cfcfcf;
    font-weight: bold;
  }

  .invoice-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .invoice-summary-table tr td.quantity,
  .invoice-summary-table tr td.volume,
  .invoice-summary-table tr td.items,
  .invoice-summary-table tr td.species_code,
  .invoice-summary-table tr td.species_class,
  .invoice-summary-table tr td.fob_price,
  .invoice-summary-table tr td.tax_code,
  .invoice-summary-table tr td.total {
    text-align: center;
  }

  .invoice-summary-table tr td.fee_desc,
  .invoice-summary-table tr td.fee_desc em {
    font-size: 9px;
    white-space: nowrap;
  }

  .invoice-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .invoice-details-table tr td {
    white-space: nowrap;
  }

  .invoice-details-table tr td.barcode {
    text-align: left;
  }

  .invoice-details-table tr td.scan_date,
  .invoice-details-table tr td.volume,
  .invoice-details-table tr td.species_code,
  .invoice-details-table tr td.species_class,
  .invoice-details-table tr td.diameter,
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
    background-color: #fafafa;
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
    margin-top: -3px;
    margin-bottom: 5px;
  }

  .invoice-info-table tr td {
    width: 15%;
    padding: 4px 5px;
    vertical-align: top;
    white-space: nowrap;
    overflow: hidden;
  }

  .invoice-info-table tr td.label {
    width: 25%;
    font-weight: bold;
    white-space: nowrap;
    overflow: visible;
  }

  .invoice-info-table tr td.label-left {
    white-space: normal;
    width: 15%;
  }

  .invoice-info-table tr td.desc-left {
    width: 25%;
  }

  .invoice-info-table tr td.from,
  .invoice-info-table tr td.to {
     /* text-align: right; */
  }

  .invoice-info-table tr td.address {
    white-space: normal;
  }

  .invoice-titles {}

  .invoice-title {
    margin: 2px 0 5px;
    font-size: 18px;
    text-align: center;
  }

  .invoice-subtitle {
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
    clear: both;
  }

  sup {
    font-size: 75%;
  }

</style>
<?php endif; ?>

<div class="exf-invoice invoice <?php if ($options['break']) echo 'invoice-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="invoice-header">
    <table class="invoice-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="invoice-title"><?php if ($invoice->is_draft) echo 'Draft '; ?>Export Fee Invoice</div>
    <div class="invoice-subtitle"><?php if (!$invoice->is_draft): ?>Request for Payment to the Government of Liberia<?php endif; ?></div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="invoice-info">
    <table class="invoice-info-table">
      <tr>
        <td class="label label-left">Contact:</td>
        <td class="desc-left"><?php echo $invoice->operator->contact; ?></td>
        <td class="label">Invoice No:</td>
        <td><?php echo $invoice->invnumber; ?></td>
      </tr>
      <tr>
        <td class="label label-left">Company:</td>
        <td class="desc-left"><?php echo $invoice->operator->name; ?></td>
        <td class="label">Reference No:</td>
        <td><?php echo $invoice->is_draft ? 'DRAFT' : 'EXF-'.$invoice->number; ?></td>
      </tr>
      <tr>
        <td rowspan="3" class="label label-left">Address:</td>
        <td class="desc-left address" rowspan="3"><?php echo SGS::breakify($invoice->operator->address); ?></td>
        <td class="label">Payee TIN:</td>
        <td><?php echo $operator->tin; ?></td>
      </tr>
      <tr>
        <td class="label specs_number">SPEC Number:</td>
        <td><?php if ($specs_number) echo $specs_number; ?></td>
      </tr>
      <tr>
        <td class="label specs_barcode">SPEC Barcode:</td>
        <td><?php if ($specs_barcode) echo $specs_barcode; ?></td>
      </tr>
      <tr>
        <td class="label label-left">Telephone:</td>
        <td class="desc-left"><?php echo $operator->phone; ?></td>
        <td class="label">Date Created:</td>
        <td><?php echo SGS::date($invoice->created_date, SGS::PRETTY_DATE_FORMAT); ?></td>
      </tr>
      <tr>
        <td class="label label-left">E-mail:</td>
        <td class="desc-left"><?php echo $operator->email; ?></td>
        <td class="label">Date Due:</td>
        <td><?php echo SGS::date($invoice->due_date, SGS::PRETTY_DATE_FORMAT); ?></td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['summary']): ?>
  <div class="invoice-summary">
    <table class="invoice-summary-table">
      <?php if ($options['fee']): ?>
      <tr class="head">
        <td class="quantity">Quantity</td>
        <td colspan="2"></td>
        <td class="fee_desc">Fee Description</td>
        <td class="tax_code">Tax Code</td>
        <td class="fob_price">Price</td>
        <td class="total">Total<br />(USD)</td>
      </tr>
      <tr class="fat">
        <td class="quantity">1</td>
        <td colspan="2"></td>
        <td class="fee_desc">
          Timber Export License Fee<br />
          <em>FDA Regulation 107-7, Section 42(c)</em>
        </td>
        <td class="tax_code">1415-16</td>
        <td class="fob_price"><?php echo SGS::amountify(100); ?></td>
        <td class="total"><?php echo SGS::amountify(100); ?></td>
      </tr>
      <tr>
        <td colspan="7" class="blank">&nbsp;</td>
      </tr>
      <?php endif; ?>
      <?php if ($data): ?>
      <tr class="head">
        <td class="volume">Volume<br />(m<sup>3</sup>)</td>
        <td class="species_code">Species</td>
        <td class="species_class">Species<br />Class</td>
        <td class="fee_desc">Fee Description</td>
        <td class="tax_code">Tax Code</td>
        <td class="fob_price">FOB Price<br />per m<sup>3</sup></td>
        <td class="total">Total<br />(USD)</td>
      </tr>
      <?php foreach ($data as $record): ?>
      <tr>
        <td class="volume"><?php echo SGS::quantitify($record['volume']); ?></td>
        <td class="species_code"><?php echo $record['species_code']; ?></td>
        <td class="species_class"><?php echo $record['species_class']; ?></td>
        <td class="fee_desc">
          Log and Wood Product Export Fee<br />
          <em>FDA Regulation 107-7, Section 44-45</em>
        </td>
        <td class="tax_code">1415-17</td>
        <td class="fob_price"><?php echo SGS::amountify($record['fob_price']); ?></td>
        <td class="total"><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']]); ?></td>
      </tr>
      <?php /*
      <tr>
        <td class="fee_desc">
          Chain of Custody Management Fee<br />
          <em>GoL-SGS Contract (1.4% of FOB Value)</em>
        </td>
        <td class="tax_code">1415-18</td>
        <td class="total"><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::FEE_SGS_CONTRACT_RATE); ?></td>
      </tr>
      */ ?>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if ($options['total']): ?>
      <tr>
        <td colspan="7" class="blank">&nbsp;</td>
      </tr>
      <tr class="total head">
        <td class="volume">Volume<br />(m<sup>3</sup>)</td>
        <td class="items" colspan="2">Logs</td>
        <td class="fee_desc">Fee Description</td>
        <td class="tax_code">Tax Code</td>
        <td class="total" colspan="2">Total<br />(USD)</td>
      </tr>
      <tr class="total">
        <td class="volume" rowspan="2"><?php echo SGS::quantitify($total['summary']['volume']); ?></td>
        <td class="items" colspan="2" rowspan="2"><?php echo $total['summary']['count']; ?></td>
        <td class="fee_desc">
          Timber Export License Fee<br />
          <em>FDA Regulation 107-7, Section 42(c)</em>
        </td>
        <td class="tax_code">1415-16</td>
        <td class="total" colspan="2"><?php echo SGS::amountify($fee_total = 100); ?></td>
      </tr>
      <tr class="total">
        <td class="fee_desc">
          Log and Wood Product Export Fee<br />
          <em>FDA Regulation 107-7, Section 44-45</em>
        </td>
        <td class="tax_code">1415-17</td>
        <td class="total" colspan="2"><?php echo SGS::amountify($gol_total = $total['summary']['total']); ?></td>
      </tr>
      <?php /*
      <tr class="total">
        <td class="fee_desc">
          Chain of Custody Management Fee<br />
          <em>GoL-SGS Contract (1.4% of FOB Value)</em>
        </td>
        <td class="tax_code">1415-18</td>
        <td class="total" colspan="2"><?php echo SGS::amountify($sgs_total = $total['summary']['fob_total'] * SGS::FEE_SGS_CONTRACT_RATE); ?></td>
      </tr>
      */ ?>
      <tr>
        <td colspan="7" class="blank blank-slim">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="5" class="blank">&nbsp;</td>
        <td class="total" colspan="2"><?php echo SGS::amountify($fee_total + $gol_total); ?></td>
      </tr>
      <?php if ($options['signature']): ?>
      <tr>
        <td colspan="7" class="blank">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="7" class="blank">&nbsp;</td>
      </tr>
      <?php endif; ?>
      <?php endif; ?>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['details']): ?>
  <div class="invoice-details">
    <table class="invoice-details-table">
      <?php if ($data): ?>
      <tr class="head">
        <td class="barcode">Barcode</td>
        <td class="scan_date">Scan Date</td>
        <td class="species_code">Species</td>
        <td class="species_class">Species<br />Class</td>
        <td class="diameter">Average<br />Diameter<br />(cm)</td>
        <td class="length">Length<br />(m)</td>
        <td class="grade">ATIBT<br />Grade</td>
        <td class="volume">Volume<br />(m<sup>3</sup>)</td>
      </tr>
      <?php foreach ($data as $record): ?>
      <tr class="<?php print SGS::odd_even($odd); ?>">
        <td class="barcode"><?php echo $record['barcode']; ?></td>
        <td class="scan_date"><?php echo $record['scan_date']; ?></td>
        <td class="species_code"><?php echo $record['species_code']; ?></td>
        <td class="species_class"><?php echo $record['species_class']; ?></td>
        <td class="diameter"><?php echo $record['diameter']; ?></td>
        <td class="length"><?php echo SGS::quantitify($record['length'], 1); ?></td>
        <td class="grade"><?php echo $record['grade']; ?>
        <td class="volume"><?php echo SGS::quantitify($record['volume']); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if ($options['total']): ?>
      <tr>
        <td class="total_species" colspan="7"><strong>Total:</strong> <?php echo $record['species_botanic_name']; ?></td>
        <td class="total_volume"><?php echo SGS::quantitify($total['details'][$record['species_code']]['volume']); ?></td>
      </tr>
      <?php endif; ?>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['signature']): ?>
  <div class="payment-message">
    Payments shall be made by Manager's Check to the order of the<br />
    'General Revenue Account' delivered to SGS Liberia Inc. before the due date
  </div>
  <table class="invoice-signature-table">
    <tr>
      <td class="blank">&nbsp;</td>
      <td class="blank">&nbsp;</td>
      <td class="blank" colspan="3">For SGS internal use only</td>
    </tr>
    <tr>
      <td class="signature half-signature">Reviewed Signature</td>
      <td class="blank" rowspan="2"></td>
      <td class="signature" rowspan="2">Invoice Registered</td>
      <td class="signature" rowspan="2">Payment Done</td>
      <td class="signature" rowspan="2">Payment Registered</td>
    </tr>
    <tr>
      <td class="signature half-signature">Authorized Signature</td>
    </tr>
    <div class="payment-date">
      <br />Date: _______________________________________
    </div>
  </table>
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

    .invoice-footer {
      margin: 0 25px;
    }

    .invoice-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .invoice-footer-table tr td.date,
    .invoice-footer-table tr td.info,
    .invoice-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .invoice-footer-table tr td.date,
    .invoice-footer-table tr td.pageinfo {
      width: 120px;
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
      white-space: nowrap;
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
          <div class="ref"><?php echo $invoice->is_draft ? 'DRAFT' : 'Ref No: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; EXF-'.$invoice->number; ?></div>
          Page <span class="page"></span> of <span class="topage"></span>
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($invoice->is_draft): ?>
<!-- <img class="floater" src="<?php // echo DOCROOT; ?>images/invoice/draft_copy.png" /> -->
<?php endif; ?>