<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th>Payment Number</th>
    <th>Reference Number</th>
    <th>Invoice Number</th>
    <th>Amount</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($payments as $payment): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">PAYMENT</span></td>
    <td class="status"><?php echo HTML::image('images/money.png', array('class' => 'payment', 'title' => 'Payment')); ?></td>
    <td><?php echo $payment->number; ?></td>
    <td><?php echo $payment->invoice->type.'-'.$payment->invoice->number; ?></td>
    <td><?php echo $payment->invoice->invnumber; ?></td>
    <td><?php echo SGS::amountify($payment->amount); ?></td>
    <td class="links">
<!--      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
        </div>
      </div> -->
    </td>
  </tr>
  <?php endforeach; ?>
</table>
