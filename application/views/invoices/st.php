<?php

$options = (array) $options + array(
  'header'    => TRUE,
  'footer'    => TRUE,
  'break'     => TRUE,
  'styles'    => FALSE,
  'info'      => FALSE,
  'summary'   => FALSE,
  'signature' => FALSE,
  'total'     => FALSE,
  'format'    => 'pdf'
);

?>
<?php if ($options['styles']): ?>
<style type="text/css">

  @page {
    margin: 0;
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

  .fda-logo {
    text-align: right;
  }

  .invoice {
    padding: 20px 25px;
  }

  .st-invoice {
    font-family: Arial, sans-serif;
    font-size: 9.5pt;
  }

  .invoice-page-break {
    page-break-before: always;
  }

  .invoice-header {}

  .invoice-footer {
    position: absolute;
    bottom: 0;
    left: 35px;
  }

  .invoice-header,
  .invoice-info,
  .invoice-summary {
    width: 100%;
  }

  .invoice-summary-table,
  .invoice-details-table,
  .invoice-signature-table,
  .invoice-info-table,
  .invoice-header-table,
  .invoice-footer-table {
    width: 100%;
    border-collapse: collapse;
  }

  .invoice-summary-table tr td,
  .invoice-details-table tr td {
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

  .invoice-summary-table tr td.fee_desc {
    font-size: 8pt;
    white-space: nowrap;
  }

  .invoice-summary-table tr.head td.fee_desc {
    font-size: 10pt;
  }

  .invoice-details-table tr td.barcode,
  .invoice-details-table tr td.scan_date,
  .invoice-details-table tr td.volume,
  .invoice-details-table tr td.species_code,
  .invoice-details-table tr td.species_class,
  .invoice-details-table tr td.diameter,
  .invoice-details-table tr td.length,
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
    padding: 5px;
    vertical-align: top;
  }

  .invoice-info-table tr td.label {
    width: 127px;
    font-weight: bold;
  }

  .invoice-info-table tr td.from,
  .invoice-info-table tr td.to {
    text-align: right;
  }

  .invoice-footer-table {
    margin-top: 15px;
  }

  .invoice-footer-table tr td.date,
  .invoice-footer-table tr td.info,
  .invoice-footer-table tr td.page {
    vertical-align: bottom;
  }

  .invoice-footer-table tr td.date,
  .invoice-footer-table tr td.page {
    width: 135px;
  }

  .invoice-footer-table tr td.date {
    text-align: left;
  }

  .invoice-footer-table tr td.info {
    text-align: center;
  }

  .invoice-footer-table tr td.page {
    text-align: right;
    position: relative;
  }

  .invoice-footer-table tr td.page .ref {
    margin-bottom: 12px;
    font-size: 8pt;
  }

  .invoice-titles {}

  .invoice-title {
    margin: 7px 0 15px;
    font-size: 24pt;
    text-align: center;
  }

  .invoice-subtitle {
    margin-bottom: 25px;
    text-align: center;
    text-transform: uppercase;
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
        <td class="liberfor-logo"><img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="invoice-title">Proforma Stumpage Invoice</div>
    <div class="invoice-subtitle">Request for Payment to the Government of Liberia</div>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="invoice-info">
    <table class="invoice-info-table">
      <tr>
        <td class="label">Owner:</td>
        <td><?php echo $invoice->site->operator->contact; ?></td>
        <td class="label">Reference No:</td>
        <td><?php echo $invoice->is_draft ? 'DRAFT' : 'ST-'.$invoice->reference_number; ?></td>
      </tr>
      <tr>
        <td class="label">Company:</td>
        <td><?php echo $invoice->site->operator->name; ?></td>
        <td class="label">Date Created:</td>
        <td><?php echo SGS::date($invoice->created_date, SGS::PRETTY_DATE_FORMAT); ?></td>
      </tr>
      <tr>
        <td rowspan="3" class="label">Address:</td>
        <td rowspan="3"><?php echo $invoice->site->operator->address; ?></td>
        <td class="label">Date Due:</td>
        <td><?php echo SGS::date($invoice->due_date, SGS::PRETTY_DATE_FORMAT); ?></td>
      </tr>
      <tr>
        <td class="label from">Logs Declared From:</td>
        <td><?php if ($invoice->from_date) echo SGS::date($invoice->from_date, SGS::PRETTY_DATE_FORMAT); ?></td>
      </tr>
      <tr>
        <td class="label to">To:</td>
        <td><?php if ($invoice->to_date) echo SGS::date($invoice->to_date, SGS::PRETTY_DATE_FORMAT); ?></td>
      </tr>
      <tr>
        <td class="label">Telephone:</td>
        <td><?php echo $operator->phone; ?></td>
        <td class="label">Site Reference:</td>
        <td><?php echo $site->type.'/'.$site->name; ?></td>
      </tr>
      <tr>
        <td class="label">E-mail:</td>
        <td><?php echo $operator->email; ?></td>
        <td class="label">Payee TIN:</td>
        <td><?php echo $operator->tin; ?></td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['summary']): ?>
  <div class="invoice-summary">
    <table class="invoice-summary-table">
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
        <td class="volume" rowspan="2"><?php echo round($record['volume'], 3); ?></td>
        <td class="species_code" rowspan="2"><?php echo $record['species_code']; ?></td>
        <td class="species_class" rowspan="2"><?php echo $record['species_class']; ?></td>
        <td class="fee_desc">
          Stumpage Fee (GoL share)<br />
          <em>FDA Regulation 107-7 section 22(b)</em>
        </td>
        <td class="tax_code">1415-12</td>
        <td class="fob_price" rowspan="2"><?php echo $record['fob_price']; ?></td>
        <td class="total"><?php echo round($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_GOL_RATE, 3); ?></td>
      </tr>
      <tr>
        <td class="fee_desc">
          Chain of Custody Stumpage Share<br />
          <em>GoL-SGS contract</em>
        </td>
        <td class="tax_code">1415-01</td>
        <td class="total"><?php echo round($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_SGS_RATE, 3); ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if ($options['total']): ?>
      <tr>
        <td colspan="7" class="blank">&nbsp;</td>
      </tr>
      <tr class="total head">
        <td class="volume">Volume<br />(m<sup>3</sup>)</td>
        <td class="desc" colspan="2">Description</td>
        <td class="fee_desc">Fee Description</td>
        <td class="tax_code">Tax Code</td>
        <td class="total" colspan="2">Total<br />(USD)</td>
      </tr>
      <tr class="total">
        <td class="volume" rowspan="2"><?php echo $total['summary']['volume']; ?></td>
        <td class="desc" colspan="2" rowspan="2">Total</td>
        <td class="fee_desc">
          Stumpage Fee (GoL share)<br />
          <em>FDA Regulation 107-7 section 22(b)</em>
        </td>
        <td class="tax_code">1415-12</td>
        <td class="total" colspan="2"><?php echo round($total['summary']['total'] * SGS::FEE_GOL_RATE, 3); ?></td>
      </tr>
      <tr class="total">
        <td class="fee_desc">
          Chain of Custody Stumpage Share<br />
          <em>GoL-SGS contract</em>
        </td>
        <td class="tax_code">1415-01</td>
        <td class="total" colspan="2"><?php echo round($total['summary']['total'] * SGS::FEE_SGS_RATE, 3); ?></td>
      </tr>
      <tr>
        <td colspan="7" class="blank blank-slim">&nbsp;</td>
      </tr>
      <tr>
        <td colspan="5" class="blank">&nbsp;</td>
        <td class="total" colspan="2"><?php echo $total['summary']['total']; ?></td>
      </tr>
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
        <td class="volume">Volume<br />(m<sup>3</sup>)</td>
      </tr>
      <?php foreach ($data as $record): ?>
      <tr class="<?php print SGS::odd_even($odd); ?>">
        <td class="barcode"><?php echo $record['barcode']; ?></td>
        <td class="scan_date"><?php echo $record['scan_date']; ?></td>
        <td class="species_code"><?php echo $record['species_code']; ?></td>
        <td class="species_class"><?php echo $record['species_class']; ?></td>
        <td class="diameter"><?php echo $record['diameter']; ?></td>
        <td class="length"><?php echo $record['length']; ?></td>
        <td class="volume"><?php echo $record['volume']; ?></td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <?php if ($options['total']): ?>
      <tr>
        <td class="total_species" colspan="6"><strong>Total:</strong> <?php echo $record['species_botanic_name']; ?></td>
        <td class="total_volume"><?php echo $total['details'][$record['species_code']]['volume']; ?></td>
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
      <td class="signature half-signature">Authorized Signature</td>
      <td class="blank" rowspan="2"></td>
      <td class="signature" rowspan="2">Invoice Registered</td>
      <td class="signature" rowspan="2">Payment Done</td>
      <td class="signature" rowspan="2">Payment Registered</td>
    </tr>
    <tr>
      <td class="signature half-signature">Reviewed Signature</td>
    </tr>
    <div class="payment-date">
      <br />Date: _______________________________________
    </div>
  </table>
  <?php endif; ?>

  <?php if ($options['footer']): ?>
  <div class="invoice-footer">
    <table class="invoice-footer-table">
      <tr>
        <td class="date"><?php echo SGS::date($invoice->created_date, SGS::PRETTY_DATE_FORMAT); ?></td>
        <td class="info">
          <img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_liberfor_bw.jpg" /> &nbsp; is operated by &nbsp; <img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_fda_small.jpg" /><br />
          LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia
        </td>
        <td class="page">
          <div class="ref"><?php echo $invoice->is_draft ? 'DRAFT' : 'Ref No: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; ST-'.$invoice->reference_number; ?></div>
          Page <?php echo $page; ?> of <?php echo $page_count; ?>
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($invoice->is_draft): ?>
<!-- <img class="floater" src="<?php // echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/draft_copy.png" /> -->
<?php endif; ?>