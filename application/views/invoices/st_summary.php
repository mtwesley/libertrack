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
    <td><?php echo $record['species_code']; ?></td>
    <td><?php echo $record['species_class']; ?></td>
    <td><?php echo SGS::quantitify($record['volume']); ?></td>
    <td>Stumpage Fee <em>(FDA Regulation 107-7, Section 22(b))</em></td>
    <td>1415-14</td>
    <td><?php echo SGS::amountify($record['fob_price']); ?></td>
    <td><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_GOL_RATE); ?></td>
  </tr>
  <?php /*
  <tr>
    <td class="split-row">Chain of Custody Stumpage Share <em>(GoL-SGS Contract)</em></td>
    <td class="split-row">1415-15</td>
    <td class="split-row"><?php echo SGS::amountify($record['volume'] * $record['fob_price'] * SGS::$species_fee_rate[$record['species_class']] * SGS::FEE_SGS_RATE); ?></td>
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
    <td colspan="2">Total</td>
    <td><?php echo SGS::quantitify($total['summary']['volume']); ?></td>
    <td>Stumpage Fee <em>(FDA Regulation 107-7, Section 22(b))</em></td>
    <td>1415-14</td>
    <td></td>
    <td><?php echo SGS::amountify($gol_total = $total['summary']['total'] * SGS::FEE_GOL_RATE); ?></td>
  </tr>
  <?php /*
  <tr>
    <td class="split-row">Chain of Custody Stumpage Share <em>(GoL-SGS Contract)</em></td>
    <td class="split-row">1415-15</td>
    <td class="split-row"></td>
    <td class="split-row"><?php echo SGS::amountify($sgs_total = $total['summary']['total'] * SGS::FEE_SGS_RATE); ?></td>
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
    <td><?php echo SGS::amountify($gol_total); ?></td>
  </tr>
</table>
