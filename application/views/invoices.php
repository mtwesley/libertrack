<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="image"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'number')), 'Reference Number'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'invnumber')), 'Invoice Number'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'operator_id')), 'Operator'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'from_date')), 'From'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'to_date')), 'To'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'created_date')), 'Created'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'due_date')), 'Due'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'is_paid')), 'Paid'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($invoices as $invoice): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type"><?php echo $invoice->type; ?></span></td>
    <td class="image"><?php echo HTML::image('images/layout_content.png'); ?></td>
    <td><?php echo $invoice->is_draft ? 'DRAFT' : $invoice->type.'-'.$invoice->number; ?></td>
    <td><?php echo $invoice->invnumber; ?></td>
    <td><?php echo $invoice->operator->name; ?></td>
    <td><?php echo $invoice->site->name; ?></td>
    <td><?php if ($invoice->from_date) echo SGS::date($invoice->from_date); ?></td>
    <td><?php if ($invoice->to_date) echo SGS::date($invoice->to_date); ?></td>
    <td><?php echo SGS::date($invoice->created_date); ?></td>
    <td><?php echo SGS::date($invoice->due_date); ?></td>
    <td><?php echo $invoice->is_paid ? 'YES' : 'NO'; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('invoices/'.$invoice->id, 'View', array('class' => 'link')); ?>

          <?php if ($invoice->is_draft): ?>
          <?php echo HTML::anchor('invoices/'.$invoice->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php echo HTML::anchor('invoices/'.$invoice->id.'/finalize', 'Finalize', array('class' => 'link')); ?>
          <?php else: ?>
          <?php if (!$invoice->is_paid): ?>
          <?php echo HTML::anchor('invoices/'.$invoice->id.'/payment', 'Payments', array('class' => 'link')); ?>
          <?php echo HTML::anchor('invoices/'.$invoice->id.'/check', 'Check', array('class' => 'link')); ?>
          <?php endif; // is paid ?>
          <?php endif; ?>

          <?php echo HTML::anchor($invoice->file->path, 'Download', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
