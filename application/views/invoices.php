<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="image"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'reference_number')), 'Reference Number'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th>Operator</th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'from_date')), 'From'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'to_date')), 'To'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'created_date')), 'Created'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'due_date')), 'Due'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($invoices as $invoice): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type"><?php echo $invoice->type; ?></span></td>
    <td class="image"><?php echo HTML::image('images/layout.png'); ?></td>
    <td><?php echo $invoice->is_draft ? 'DRAFT' : $invoice->type.'-'.$invoice->reference_number; ?></td>
    <td><?php echo $invoice->site->operator->name; ?></td>
    <td><?php echo $invoice->site->name; ?></td>
    <td><?php if ($invoice->from_date) echo SGS::date($invoice->from_date); ?></td>
    <td><?php if ($invoice->to_date) echo SGS::date($invoice->to_date); ?></td>
    <td><?php echo SGS::date($invoice->created_date); ?></td>
    <td><?php echo SGS::date($invoice->due_date); ?></td>
    <td class="links">
      <?php echo HTML::anchor('invoices/'.$invoice->id, 'View', array('class' => 'link')); ?>

        <?php if ($invoice->is_draft): ?>
      <?php echo HTML::anchor('invoices/'.$invoice->id.'/finalize', 'Finalize', array('class' => 'link')); ?>
      <?php echo HTML::anchor('invoices/'.$invoice->id.'/delete', 'Delete', array('class' => 'link')); ?>
      <?php endif; ?>

      <?php echo HTML::anchor($invoice->file->path, 'Download', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
