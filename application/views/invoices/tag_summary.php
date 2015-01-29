<?php
$classes[] = 'data';
?>
<table class="<?php echo SGS::render_classes($classes); ?> invoice-exf-summary">
  <tr class="head">
    <th>Quantity</th>
    <th></th>
    <th></th>
    <th>Fee Description</th>
    <th>Unit Price</th>
    <th>Price</th>
  </tr>
  <tr>
    <td><?php echo $invoice->values['tag_quantity']; ?></td>
    <th></th>
    <th></th>
    <td>Barcode Tag Fee</td>
    <td><?php echo SGS::amountify(SGS::TAG_PRICE); ?></td>
    <td><?php echo SGS::amountify(SGS::TAG_PRICE * $invoice->values['tag_quantity']); ?></td>
  </tr>
</table>
