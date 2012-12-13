<?php
$classes[] = 'data';
?>
<?php foreach ($checks as $type => $info): ?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php print $info['title']; ?></th>
    <th class="check-info">Total Checked</th>
    <th class="check-info">Total Passed</th>
    <th class="check-info">Total Warnings</th>
    <th class="check-info">Total Failed</th>
    <th class="check-info">Pass Rate</th>
  </tr>
  <?php foreach ($info['checks'] as $check => $array): ?>
  <?php
    $checked = $report['checks'][$type][$check]['checked'] ?: 0;
    $passed  = $report['checks'][$type][$check]['passed'] ?: 0;
    $failed  = $report['checks'][$type][$check]['failed'] ?: 0;
    $warned  = $report['checks'][$type][$check]['warned'] ?: 0;
    $percentage = $checked ? floor($passed * 100 / $checked) : 100;
  ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status">
      <?php
        if ($failed) print HTML::image('images/cross.png', array('class' => 'status'));
        else if ($warned) print HTML::image('images/asterisk_yellow.png', array('class' => 'status'));
        else print HTML::image('images/check.png', array('class' => 'status'));
      ?>
    </td>
    <td class="check-desc"><?php print $array['title']; ?></td>
    <td class="check-info"><?php print $checked; ?></td>
    <td class="check-info"><span class="accepted"><?php print $passed; ?></span></td>
    <td class="check-info"><span class="pending"><?php print $warned; ?></span></td>
    <td class="check-info"><span class="rejected"><?php print $failed; ?></span></td>
    <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endforeach; ?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th>Summary</th>
    <th class="check-info">Total Checked</th>
    <th class="check-info">Total Passed</th>
    <th class="check-info">Total Warnings</th>
    <th class="check-info">Total Failed</th>
    <th class="check-info">Pass Rate</th>
  </tr>
  <?php
    $checked = $report['total']['checked'] ?: 0;
    $passed  = $report['total']['passed'] ?: 0;
    $failed  = $report['total']['failed'] ?: 0;
    $warned  = $report['total']['warned'] ?: 0;
    $percentage = $checked ? floor($passed * 100 / $checked) : 100;
  ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status"><?php print HTML::image('images/calculator.png', array('class' => 'status')); ?></td>
    <td class="check-desc">Total</td>
    <td class="check-info"><?php print $checked; ?></td>
    <td class="check-info"><span class="accepted"><?php print $passed; ?></span></td>
    <td class="check-info"><span class="pending"><?php print $warned; ?></span></td>
    <td class="check-info"><span class="rejected"><?php print $failed; ?></span></td>
    <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
  </tr>
</table>
