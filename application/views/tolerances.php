<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'form_type')), 'Form'); ?></th>
    <th>Fields</th>
    <th>Accuracy Rance</th>
    <th>Tolerance Range</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($tolerances as $tolerance): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type"><?php echo $tolerance->form_type; ?></span></td>
    <td><?php echo SGS::$form_type[$tolerance->form_type]; ?></td>
    <td>
      <?php
        $form_fields = array();
        $labels = ORM::factory($tolerance->form_type)->labels();
        foreach ($tolerance->form_fields as $field) $form_fields[] = $labels[$field];
        echo SGS::implodify($form_fields);
      ?>
    </td>
    <td><?php echo $tolerance->accuracy_range; ?></td>
    <td><?php echo $tolerance->tolerance_range; ?></td>
    <td class="links"></td>
  </tr>
  <?php endforeach; ?>
</table>
