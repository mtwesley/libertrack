<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'type')), 'Type'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'operator_id')), 'Operator'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($sites as $site): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $site->type; ?></td>
    <td><?php echo $site->name; ?></td>
    <td><?php echo $site->operator->name; ?></td>
    <td class="links">
      <?php echo HTML::anchor('admin/sites/'.$site->id.'/edit', 'Edit', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
