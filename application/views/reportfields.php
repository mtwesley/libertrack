<?php
$classes[] = 'form';
?>
<style>
  div.submit {
    padding: 6px 0 5px;
    text-align: center;
  }

  th.type {
    padding-right: 10px !important;
  }

  input.field-name {
    width: 200px;
  }
</style>
<form method="post" action="<?php echo '/reports/create/'.strtolower($report_type).'/preview'; ?>">
  <table class="<?php echo SGS::render_classes($classes); ?>">
    <tr class="head">
      <th class="type"></th>
      <th>Model</th>
      <th>Field</th>
      <th>Name</th>
      <th>Value</th>
    </tr>
    <?php if ($fields) foreach ($fields as $index => $field): ?>
    <tr id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field']; ?>" class="<?php echo 'field-'.$index.'-'.$field['field']; ?> <?php echo SGS::odd_even($odd); ?>">
      <td class="data-row-details type"><span class="data-type"><?php echo strtoupper($field['model']); ?></span></td>
      <td><?php echo $report::$models[$field['model']]['name']; ?></td>
      <td><?php echo $report::$models[$field['model']]['fields'][$field['field']]['name'] ?></td>
      <td><input id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-name'; ?>" class="field-name" name="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-name'; ?>" type="text" value="<?php echo $field['name']; ?>" /></td>
      <td>
        <select id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-value'; ?>" class="field-value" name="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-value'; ?>">
          <?php if (is_array($field['values'])) foreach ($field['values'] as $val): ?>
          <option value="<?php echo $val; ?>"><?php echo strtoupper($val); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="submit">
    <input type="submit" value="Preview Report" />
  </div>
</form>