<?php
$classes[] = 'data';
?>
<style>
  .invoice-exf-summary tr.blank {
    background-color: #fff !important;
  }
  .invoice-exf-summary tr.blank td {
    height: 1px !important;
    max-height: 1px !important;
  }
</style>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-exf-summary">
  <tr class="head">
    <th></th>
    <th></th>
    <th></th>
    <th>Fee Description</th>
    <th>Tax Code</th>
    <th>FOB Price</th>
    <th>Price</th>
  </tr>
  <tr>
    <td></td>
    <td></td>
    <td></td>
    <td>Timber Export License Fee <em>(FDA Regulation 107-7 section 42c)</em></td>
    <td>1415-16</td>
    <td><?php echo SGS::amountify(100); ?></td>
    <td><?php echo SGS::amountify(100); ?></td>
  </tr>
  <tr class="blank">
    <td colspan="7"></td>
  </tr>
  <tr class="head">
    <th>Species</th>
    <th>Species Class</th>
    <th>Volume</th>
    <th>Fee Description</th>
    <th>Tax Code</th>
    <th>FOB Price</th>
    <th>Price</th>
  </tr>
  <?php if ($data): ?>
  <?php foreach ($data as $record): ?>
  <tr>
    <td><?php echo $record['species_code']; ?></td>
    <td><?php echo $record['species_class']; ?></td>
    <td><?php echo SGS::quantitify($record['volume']); ?></td>
    <td>Log and Wood Product Export Fee <em>(FDA Regulation 107-7, Section 44-45</em></td>
    <td>1415-17</td>
    <td><?php echo SGS::amountify($record['fob_price']); ?></td>
    <td><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']]); ?></td>
  </tr>
  <?php /*
  <tr>
    <td class="split-row">Chain of Custody Management Fee <em>(GoL-SGS Contract, 1.4% of FOB Value)</em></td>
    <td class="split-row">1415-18</td>
    <td class="split-row"><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::FEE_SGS_CONTRACT_RATE); ?></td>
  </tr>
  */ ?>
  <?php endforeach; ?>
  <?php endif; ?>
  <tr class="blank">
    <td colspan="7"></td>
  </tr>
  <tr class="head" style="border-top: 2px solid #D9C7AD;">
    <th></th>
    <th></th>
    <th>Volume</th>
    <th>Fee Description</th>
    <th>Tax Code</th>
    <th></th>
    <th>Price</th>
  </tr>
  <tr>
    <td rowspan="2" colspan="2">Total</td>
    <td rowspan="2"><?php echo SGS::quantitify($total['summary']['volume']); ?></td>
    <td>Timber Export License Fee <em>(FDA Regulation 107-7 section 42c)</em></td>
    <td>1415-16</td>
    <td></td>
    <td><?php echo SGS::amountify($fee_total = 100); ?></td>
  </tr>
  <tr>
    <td class="split-row">Log and Wood Product Export Fee <em>(FDA Regulation 107-7, Section 44-45)</em></td>
    <td class="split-row">1415-17</td>
    <td class="split-row"></td>
    <td class="split-row"><?php echo SGS::amountify($gol_total = $total['summary']['total']); ?></td>
  </tr>
  <?php /*
  <tr>
    <td class="split-row">Chain of Custody Management Fee <em>(GoL-SGS Contract, 1.4% of FOB Value)</em></td>
    <td class="split-row">1415-18</td>
    <td class="split-row"></td>
    <td class="split-row"><?php echo SGS::amountify($sgs_total = $total['summary']['fob_total'] * SGS::FEE_SGS_CONTRACT_RATE); ?></td>
  </tr>
  */ ?>
  <tr class="blank">
    <td colspan="7"></td>
  </tr>
  <tr>
    <td colspan="2"></td>
    <td></td>
    <td></td>
    <td></td>
    <td></td>
    <td><?php echo SGS::amountify($fee_total + $gol_total); ?></td>
  </tr>
</table>
