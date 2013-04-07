<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'code')), 'Code'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'trade_name')), 'Trade Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'botanic_name')), 'Botanic Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'class')), 'Class'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'fob_price')), 'FOB Price'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($species as $spcs): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $spcs->code; ?></td>
    <td><?php echo $spcs->trade_name; ?></td>
    <td><?php echo $spcs->botanic_name; ?></td>
    <td><?php echo $spcs->class; ?></td>
    <td><?php echo $spcs->fob_price; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('admin/species/'.$spcs->id.'/edit', 'Edit', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
