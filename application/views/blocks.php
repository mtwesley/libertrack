<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th>Operator</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($blocks as $block): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">BLOCK</span></td>
    <td class="status"><?php echo HTML::image('images/shape_square.png', array('class' => 'barcode', 'title' => 'Barcode')); ?></td>
    <td><?php echo $block->name; ?></td>
    <td><?php echo $block->site->name; ?></td>
    <td><?php echo $block->site->operator->name; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('config/blocks/'.$block->id.'/edit', 'Edit', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
