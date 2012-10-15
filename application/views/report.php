<?php

$classes[] = 'data';

?>
<table class="<?php echo SGS::render_classes($classes); ?> report-summary">
  <tr class="head">
    <th>Checks and Queries</th>
    <th>Passed</th>
    <th>Failed</th>
  </tr>
  <?php foreach ($data['errors'] as $error => $array): ?>
  <?php foreach ($array as $field => $record): ?>
  <tr>
    <td><?php echo SGS::decode_error($field, $error, array(':field' => $fields[$field]), $messages); ?></td>
    <td><span class="accepted"><?php echo $data['passed']; ?> Passed (<?php echo $data['total'] ? floor($data['passed'] * 100 / $data['total']) : 0; ?>%)</span></td>
    <td><span class="rejected"><?php echo $data['failed']; ?> Failed (<?php echo $data['total'] ? floor($data['failed'] * 100 / $data['total']) : 0; ?>%)</span></td>
  </tr>
  <?php endforeach; ?>
  <?php endforeach; ?>
</table>
