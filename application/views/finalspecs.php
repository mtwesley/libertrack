<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="image"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'number')), 'Shipment Specification Number'); ?></th>
    <th>Shipment Specification Barcode</th>
    <th>Export Permit Number</th>
    <th>Export Permit Barcode</th>
    <th>Operator</th>
    <th>Port of Origin</th>
    <th>Port of Destination</th>
    <th>Date Surveyed</th>
    <th>Date Created</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($specs as $spec): ?>
  <?php
        $item = ORM::factory('SPECS')
          ->where('specs_id', '=', $spec['id'])
          ->find();

        $file = ORM::factory('file', $spec['file_id']);
  ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">SPECS</span></td>
    <td class="image"><?php echo HTML::image('images/layout.png'); ?></td>
    <td><?php echo $spec['number'] ? 'SPEC '.$spec['number'] : 'DRAFT'; ?></td>
    <td><?php echo $item->specs_barcode->barcode; ?></td>
    <td><?php echo $item->exp_number; ?></td>
    <td><?php echo $item->exp_barcode->barcode; ?></td>
    <td><?php echo $item->operator->name; ?></td>
    <td><?php echo SGS::locationify($item->origin); ?></td>
    <td><?php echo SGS::locationify($item->destination); ?></td>
    <td><?php echo SGS::date($item->create_date); ?></td>
    <td><?php echo SGS::date($spec['timestamp']); ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('exporting/specs/'.$spec->id, 'View', array('class' => 'link')); ?>

          <?php if (SGS::booleanify($spec['is_draft'])): ?>
          <?php echo HTML::anchor('exporting/specs/'.$spec->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php echo HTML::anchor('exporting/specs/'.$spec->id.'/finalize', 'Finalize', array('class' => 'link')); ?>
          <?php endif; ?>

          <?php echo HTML::anchor($file->path, 'Download', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
