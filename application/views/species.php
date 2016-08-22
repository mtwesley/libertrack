<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'code')), 'Code'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'trade_name')), 'Trade Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'botanic_name')), 'Botanic Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'class')), 'Class'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'fob_price_low')), 'FOB Low Price'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'fob_price_high')), 'FOB High Price'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($species as $spcs): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">SPECIES</span></td>
    <td class="status"><?php echo HTML::image('images/tree.png', array('class' => 'barcode', 'title' => 'Barcode')); ?></td>
    <td><?php echo $spcs->code; ?></td>
    <td><?php echo $spcs->trade_name; ?></td>
    <td><?php echo $spcs->botanic_name; ?></td>
    <td><?php echo $spcs->class; ?></td>
    <td><?php echo $spcs->fob_price_low; ?></td>
    <td><?php echo $spcs->fob_price_high; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('config/species/'.$spcs->id.'/edit', 'Edit', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
