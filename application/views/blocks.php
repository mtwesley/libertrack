<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th>Operator</th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'status')), 'Status'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($blocks as $block): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">BLOCK</span></td>
    <td class="status">
      <?php
        // echo HTML::image('images/shape_square.png', array('class' => 'barcode', 'title' => 'Barcode'));
        switch ($block->status):
          case 'P': echo HTML::image('images/flag_orange.gif', array('class' => 'status pending', 'title' => 'Pending')); break;
          case 'A': echo HTML::image('images/flag_green.gif', array('class' => 'status accepted', 'title' => 'Approved')); break;
          case 'R': echo HTML::image('images/flag_red.gif', array('class' => 'status rejected', 'title' => 'Rejected')); break;
        endswitch;
      ?>
    </td>
    <td><?php echo $block->name; ?></td>
    <td><?php echo $block->site->name; ?></td>
    <td><?php echo $block->site->operator->name; ?></td>
    <td><?php echo SGS::$block_status[$block->status]; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('config/blocks/'.$block->id.'/edit', 'Edit', array('class' => 'link')); ?>
          <?php echo HTML::anchor('config/blocks/'.$block->id.'/inspection', 'Inspection', array('class' => 'link')); ?>
          <span id="<?php echo implode('-', array('block', $block->id, 'block-status-update')); ?>" class="link block-status-update-link">Status</span>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
