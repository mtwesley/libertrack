<?php

$classes[] = 'data';

?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th></th>
    <th>Total Records</th>
    <th>Total Checked</th>
    <th>Total Passed</th>
    <th>Total Warnings</th>
    <th>Total Failed</th>
    <th>Pass Rate</th>
  </tr>
  <?php foreach ($checks as $check => $description): ?>
  <?php
    $total   = $report['total'];
    $passed  = $total - count(array_filter(array_unique((array) $report['errors'][$check])));
    $failed  = $total - $passed;
    $checked = $total - $report['unchecked'];

    $percentage = $total ? floor($passed * 100 / $total) : 100;
    $warnings   = count(array_filter(array_unique((array) $report['warnings'][$check])));
  ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status">
      <?php
        if ($failed) print HTML::image('images/cross.png', array('class' => 'status'));
        else if ($warnings) print HTML::image('images/asterisk_yellow.png', array('class' => 'status'));
        else print HTML::image('images/check.png', array('class' => 'status'));
      ?>
    </td>
    <td><?php print $description; ?></td>
    <td><?php print $total; ?></td>
    <td><?php print $checked; ?></td>
    <td><span class="accepted"><?php print $passed ?></span></td>
    <td><span class="pending"><?php print $warnings; ?></span></td>
    <td><span class="rejected"><?php print $failed; ?></span></td>
    <td><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
  </tr>
  <?php endforeach; ?>
</table>
