<?php

$options = (array) $options + array(
  'header' => TRUE,
  'footer' => TRUE,
  'table'  => TRUE,
);

?>
<?php if ($options['header']): ?>
<div id="st-invoice" class="st-invoice invoice">
  <div id="invoice-header">
    <table>
      <tr>
        <td class="liberfor-logo"><img src="/images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="/images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="invoice-title">Proforma Stumpage Invoice</div>
    <div class="invoice-subtitle">Request for Payment to the Government of Liberia</div>
  </div>
<?php endif; ?>

<?php if ($options['table']): ?>
  <div id="invoice-data">
    <table border="1">
      <tr class="head">
        <td>Quantity<br />(m<sup>3</sup>)</td>
        <td>Species</td>
        <td>Species Class</td>
        <td>Fee Description</td>
        <td>Tax Code</td>
        <td>FOB Price<br />per m<sup>3</sup></td>
        <td>Total<br />(USD)</td>
      </tr>
      <?php foreach ($data as $record): ?>
      <tr>
        <td rowspan="2"><?php echo round($record['volume'], 3); ?></td>
        <td rowspan="2"><?php echo $record['species_code']; ?></td>
        <td rowspan="2"><?php echo $record['species_class']; ?></td>
        <td>
          Stumpage Fee (GoL share)<br />
          <em>FDA Regulation 107-7 section 22(b)</em>
        </td>
        <td>1415-12</td>
        <td rowspan="2"><?php echo $record['fob_price']; ?></td>
        <td><?php echo round($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_GOL_RATE, 3); ?></td>
      </tr>
      <tr>
        <td>
          Chain of Custody Stumpage Share<br />
          <em>GoL-SGS contract</em>
        </td>
        <td>1415-01</td>
        <td><?php echo round($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_SGS_RATE, 3); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
<?php endif; ?>

<?php if ($options['footer']): ?>
  <div id="invoice-footer">
    <table>
      <tr>
        <td class="date"><?php echo $date; ?></td>
        <td class="info">
          <div><img src="/images/invoice/st_liberfor_bw.jpg" /> is operated by <img src="/images/invoice/st_sgs.jpg" /> Liberia on the behalf of <img src="/images/invoice/st_fda_small.jpg" /></div>
          <div>LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia</div>
        </td>
      </tr>
    </table>
  </div>
</div>
<?php endif; ?>


