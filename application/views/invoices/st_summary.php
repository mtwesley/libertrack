<?php
$classes[] = 'data';
?>
<style>
  .invoice-st-summary tr.blank {
    background-color: #fff !important;
  }
  .invoice-st-summary tr.blank td {
    height: 1px !important;
    max-height: 1px !important;
  }
</style>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-st-summary">
  <?php if ($data): ?>
  <tr class="head">
    <th>Species</th>
    <th>Species Class</th>
    <th>Volume</th>
    <th>Fee Description</th>
    <th>Tax Code</th>
    <th>FOB Price</th>
    <th>Price</th>
  </tr>
  <?php foreach ($data as $record): ?>
  <tr>
    <td rowspan="2"><?php echo $record['species_code']; ?></td>
    <td rowspan="2"><?php echo $record['species_class']; ?></td>
    <td rowspan="2"><?php echo SGS::quantitify($record['volume']); ?></td>
    <td>Stumpage Fee <em>(FDA Regulation 107-7 section 22b)</em></td>
    <td>1415-12</td>
    <td rowspan="2"><?php echo $record['fob_price']; ?></td>
    <td><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_GOL_RATE); ?></td>
  </tr>
  <tr>
    <td class="split-row">Chain of Custody Stumpage Share <em>(GoL-SGS contract)</em></td>
    <td class="split-row">1415-01</td>
    <td class="split-row"><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_SGS_RATE); ?></td>
  </tr>
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
    <td>Stumpage Fee <em>(FDA Regulation 107-7 section 22b)</em></td>
    <td>1415-12</td>
    <td></td>
    <td><?php echo SGS::amountify($total['summary']['total'] * SGS::FEE_GOL_RATE); ?></td>
  </tr>
  <tr>
    <td class="split-row">Chain of Custody Stumpage Share <em>(GoL-SGS contract)</em></td>
    <td class="split-row">1415-01</td>
    <td class="split-row"></td>
    <td class="split-row"><?php echo SGS::amountify($total['summary']['total'] * SGS::FEE_SGS_RATE); ?></td>
  </tr>
</table>
