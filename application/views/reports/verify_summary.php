<?php
$model = ORM::factory($form_type);

$classes[] = 'data';

$total_records    = $report['total']['records'];
$total_declared   = $report['total']['declared'];
$total_inspected  = $report['total']['inspected'];
$total_verified   = $report['total']['verified'];
$total_unverified = $report['total']['unverified'];
$total_accurate   = $report['total']['accurate'];
$total_inaccurate = $report['total']['inaccurate'];

$declaration_percentage  = $total_accurate ? floor($total_accurate * 100 / $total_inspected) : 100;
$verification_percentage = $total_declared ? floor($total_records * 100 / ($total_declared + $total_inspected - $total_records)) : 100;
?>
<?php if ($info = $checks[$type = 'declaration']): ?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th>Declaration Performance</th>
    <th class="check-info">Inspected</th>
    <th class="check-info">Found</th>
    <th class="check-info">Verified</th>
    <th class="check-info">Accurate</th>
    <th class="check-info">Accuracy Rate</th>
  </tr>
  <?php foreach ($info['checks'] as $check => $array): ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status">
      <?php
        if ($declaration_percentage < 80) print HTML::image('images/cross.png', array('class' => 'status'));
        else print HTML::image('images/check.png', array('class' => 'status'));
      ?>
    </td>
    <td class="check-desc"><?php print $array['title']; ?></td>
    <td class="check-info"><?php print $total_inspected; ?></td>
    <td class="check-info"><?php print $total_records; ?></td>
    <td class="check-info"><?php print $total_verified; ?></td>
    <td class="check-info"><?php print $total_accurate; ?></td>
    <td class="check-info"><span class="<?php print $declaration_percentage >= 80 ? 'accepted' : 'rejected'; ?>"><?php print $declaration_percentage; ?>%</span></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($info = $checks[$type = 'verification']): ?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th>Verification Performance</th>
    <th class="check-info">Declared</th>
    <th class="check-info">Inspected</th>
    <th class="check-info">Found</th>
    <th class="check-info">Not Found</th>
    <th class="check-info">Inspection Rate</th>
  </tr>
  <?php foreach ($info['checks'] as $check => $array): ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status">
      <?php
        if ($verification_percentage < $model::$target_percentage) print HTML::image('images/cross.png', array('class' => 'status'));
        else print HTML::image('images/check.png', array('class' => 'status'));
      ?>
    </td>
    <td class="check-desc"><?php print $array['title']; ?></td>
    <td class="check-info"><?php print $total_declared; ?></td>
    <td class="check-info"><?php print $total_inspected; ?></td>
    <td class="check-info"><?php print $total_records; ?></td>
    <td class="check-info"><?php print $total_inspected - $total_records; ?></td>
    <td class="check-info"><span class="<?php print $verification_percentage >= $model::$target_percentage ? 'accepted' : 'rejected'; ?>"><?php print $verification_percentage; ?>%</span></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($info = $checks[$type = 'deviation']): ?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th>Deviation</th>
    <th></th>
    <th></th>
    <th class="check-info">Declared</th>
    <th class="check-info">Inspected</th>
    <th class="check-info">Difference</th>
  </tr>
  <?php foreach ($info['checks'] as $check => $array): ?>
  <?php
    $field = str_replace('is_valid_', '', $check);

    $dtotal = $report['deviation'][$field]['data']['total'];
    $dcount = $report['deviation'][$field]['data']['count'];
    $itotal = $report['deviation'][$field]['verification']['total'];
    $icount = $report['deviation'][$field]['verification']['count'];

    switch ($field) {
      case 'diameter':
        $declared  = SGS::floatify($dcount ? ($dtotal / $dcount) : 0);
        $inspected = SGS::floatify($icount ? ($itotal / $icount) : 0);
        $difference = SGS::floatify(abs($declared - $inspected));
        break;

      case 'length':
        $declared  = SGS::amountify($dcount ? ($dtotal / $dcount) : 0);
        $inspected = SGS::amountify($icount ? ($itotal / $icount) : 0);
        $difference = SGS::amountify(abs($declared - $inspected));
        break;

      case 'volume':
        $declared  = SGS::quantitify($dtotal);
        $inspected = SGS::quantitify($itotal);
        $difference = SGS::quantitify(abs($declared - $inspected));
        break;
    }
  ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status">
      <?php
        if (($inspected ? ($difference / $inspected) : 0) > 0.1) print HTML::image('images/asterisk_yellow.png', array('class' => 'status'));
        else print HTML::image('images/check.png', array('class' => 'status'));
      ?>
    </td>
    <td class="check-desc"><?php print $array['title']; ?></td>
    <td></td>
    <td></td>
    <td class="check-info"><?php print $declared; ?></td>
    <td class="check-info"><?php print $inspected; ?></td>
    <td class="check-info"><span class="<?php print ($inspected ? ($difference / $inspected) : 0) <= 0.1 ? 'accepted' : 'rejected'; ?>"><?php print $difference; ?></span></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>

<?php if ($checks) foreach ($checks as $type => $info): ?>
<?php if (in_array($type, array('verification', 'declaration', 'deviation'))) continue; ?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php print $info['title']; ?></th>
    <th class="check-info">Inspected</th>
    <th class="check-info">Verified</th>
    <th class="check-info">Correct</th>
    <th class="check-info">Incorrect</th>
    <th class="check-info">Correctness Rate</th>
  </tr>
  <?php foreach ($info['checks'] as $check => $array): ?>
  <?php
    $records    = $report['checks'][$type][$check]['records'] ?: 0;
    $accurate   = $report['checks'][$type][$check]['accurate'] ?: 0;
    $inaccurate = $report['checks'][$type][$check]['inaccurate'] ?: 0;
    $percentage = $records ? floor($accurate * 100 / $records) : 100;
  ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status">
      <?php
        if ($percentage < 100) print HTML::image('images/cross.png', array('class' => 'status'));
        // else if ($warned) print HTML::image('images/asterisk_yellow.png', array('class' => 'status'));
        else print HTML::image('images/check.png', array('class' => 'status'));
      ?>
    </td>
    <td class="check-desc"><?php print $array['title']; ?></td>
    <td class="check-info"><?php print $total_inspected; ?></td>
    <td class="check-info"><?php print $records; ?></td>
    <td class="check-info"><span class="accepted"><?php print $accurate; ?></span></td>
    <td class="check-info"><span class="rejected"><?php print $inaccurate; ?></span></td>
    <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endforeach; ?>

<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th>Total</th>
    <th class="check-info">Inspected</th>
    <th class="check-info">Verified</th>
    <th class="check-info">Correct</th>
    <th class="check-info">Incorrect</th>
    <th class="check-info">Correctness Rate</th>
  </tr>
  <?php
    $records = $report['total']['records'] ?: 0;
    $accurate  = $report['total']['accurate'] ?: 0;
    $inaccurate  = $report['total']['inaccurate'] ?: 0;
    $percentage = $verified ? floor($accurate * 100 / $records) : 100;
  ?>
  <tr>
    <td class="type"><span class="data-type"><?php print $form_type; ?></span></td>
    <td class="status"><?php print HTML::image('images/calculator.png', array('class' => 'status')); ?></td>
    <td class="check-desc">Total</td>
    <td class="check-info"><?php print $total_inspected; ?></td>
    <td class="check-info"><?php print $records; ?></td>
    <td class="check-info"><span class="accepted"><?php print $accurate; ?></span></td>
    <td class="check-info"><span class="rejected"><?php print $inaccurate; ?></span></td>
    <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
  </tr>
</table>

