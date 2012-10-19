<?php

$options = (array) $options + array(
  'styles' => TRUE,
  'header' => TRUE,
  'footer' => TRUE,
  'info'   => TRUE,
  'table'  => TRUE,
  'format' => 'pdf'
);

?>
<?php if ($options['styles']): ?>
<style type="text/css">

  @page {
    margin-top: 20px;
    margin-left: 25px;

  }

  .liberfor-logo {
    text-align: left;
  }

  .fda-logo {
    text-align: right;
  }

  .st-invoice {
    font-family: Arial, sans-serif;
    font-size: 9.5pt;
  }

  .invoice-page {
    page-break-before:  always;
/*    padding-top: 150px;
    padding-bottom: 50px;*/
/*    position: relative;*/
  }

  .invoice-header {}

  .invoice-footer {
    position: absolute;
    bottom: 0;
    left: 0;
  }

  .invoice-header,
  .invoice-footer,
  .invoice-info,
  .invoice-data {
    width: 100%;
  }

  .invoice-data-table,
  .invoice-info-table,
  .invoice-header-table,
  .invoice-footer-table {
    width: 100%;
    border-collapse: collapse;
  }

  .invoice-data-table {
    border-collapse: collapse;
  }

  .invoice-data-table tr td {
    padding: 2px 5px;
    border: 1px solid #000;
  }

  .invoice-data-table tr.head td {
    padding: 6px 5px;
    background-color: #bfbfbf;
    font-weight: bold;
  }

  .invoice-data-table tr td.quantity,
  .invoice-data-table tr td.species_code,
  .invoice-data-table tr td.species_class,
  .invoice-data-table tr td.fob_price,
  .invoice-data-table tr td.tax_code,
  .invoice-data-table tr td.total {
    text-align: center;
  }

  .invoice-data-table tr td.fee_desc {
    font-size: 8pt;
    white-space: nowrap;
  }

  .invoice-data-table tr.head td.fee_desc {
    font-size: 10pt;
  }

  .invoice-info-table {
    margin-bottom: 5px;
  }

  .invoice-info-table tr td {
    padding: 7px 5px;
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

  .invoice-titles {

  }

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

</style>
<?php endif; ?>

<div class="st-invoice invoice">
  <div class="invoice-page">
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
          <td><?php echo $operator->contact; ?></td>
          <td class="label">Reference No:</td>
          <td><?php // echo ($invoice->is_draft ? 'DT-' : 'ST-').$invoice->number; ?></td>
        </tr>
        <tr>
          <td class="label">Company:</td>
          <td><?php echo $operator->name; ?></td>
          <td class="label">Created Date:</td>
          <td><?php // echo $invoice->date; ?></td>
        </tr>
        <tr>
          <td rowspan="3" class="label">Address:</td>
          <td rowspan="3"><?php echo $operator->address; ?></td>
          <td class="label">Due Date:</td>
          <td><?php echo $due_date; ?></td>
        </tr>
        <tr>
          <td class="label from">Logs Declared From:</td>
          <td><?php if ($from) echo SGS::date($from, SGS::PRETTY_DATE_FORMAT); ?></td>
        </tr>
        <tr>
          <td class="label to">To:</td>
          <td><?php if ($to) echo SGS::date($to, SGS::PRETTY_DATE_FORMAT); ?></td>
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

    <?php if ($options['table']): ?>
    <div class="invoice-data">
      <table class="invoice-data-table">
        <tr class="head">
          <td class="quantity">Quantity<br />(m<sup>3</sup>)</td>
          <td class="species_code">Species</td>
          <td class="species_class">Species<br />Class</td>
          <td class="fee_desc">Fee Description</td>
          <td class="tax_code">Tax Code</td>
          <td class="fob_price">FOB Price<br />per m<sup>3</sup></td>
          <td class="total">Total<br />(USD)</td>
        </tr>
        <?php foreach ($data as $record): ?>
        <tr>
          <td class="quantity" rowspan="2"><?php echo round($record['volume'], 3); ?></td>
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
      </table>
    </div>
    <?php endif; ?>

    <?php if ($options['footer']): ?>
    <div class="invoice-footer">
      <table class="invoice-footer-table">
        <tr>
          <td class="date"><?php echo SGS::date('now', SGS::PRETTY_DATE_FORMAT); ?></td>
          <td class="info">
            <img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_liberfor_bw.jpg" /> &nbsp; is operated by &nbsp; <img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img src="<?php echo $options['format'] == 'pdf' ? DOCROOT : '/'; ?>images/invoice/st_fda_small.jpg" /><br />
            LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia
          </td>
          <td class="page">
            <div class="ref">Ref No: &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; <?php // echo ($invoice->is_draft ? 'DT-' : 'ST-').$invoice->number; ?></div>
            Page <?php echo $page; ?> of <?php echo $page_count; ?>
          </td>
        </tr>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

