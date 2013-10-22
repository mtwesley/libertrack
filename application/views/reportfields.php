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
    width: 90%;
  }

  th.order,
  th.remove {
    width: 1px;
  }
</style>
<form method="post">
  <table class="<?php echo SGS::render_classes($classes); ?>">
    <tr class="head">
      <?php if ($enabled): ?>
      <th class="order"></th>
      <th class="remove"></th>
      <?php endif; ?>
      <th class="type"></th>
      <th>Model</th>
      <th>Field</th>
      <th>Name</th>
      <th>Value</th>
    </tr>
    <?php if ($fields) foreach ($fields as $index => $field): ?>
    <tr id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field']; ?>" class="<?php echo 'field-'.$index.'-'.$field['field']; ?> <?php echo SGS::odd_even($odd); ?>">
      <?php if ($enabled): ?>
      <td>
        <select id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-index'; ?>" class="field-value" name="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-index'; ?>">
          <?php for ($i = 0; $i <= count($fields); $i++): ?>
          <option value="<?php echo $i; ?>" <?php if ($i == $index) echo 'selected="selected"'; ?>><?php echo ($i + 1); ?></option>
          <?php endfor; ?>
        </select>
      </td>
      <td class="remove"><input name="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-remove'; ?>" type="submit" value="Remove" /></td>
      <?php endif; ?>
      <td class="data-row-details type"><span class="data-type"><?php echo strtoupper($field['model']); ?></span></td>
      <td><?php echo $report::$models[$field['model']]['name']; ?></td>
      <td><?php echo $report::$models[$field['model']]['fields'][$field['field']]['name'] ?></td>
      <td>
        <?php if ($enabled): ?>
        <input id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-name'; ?>" class="field-name" name="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-name'; ?>" type="text" value="<?php echo $field['name']; ?>" />
        <?php else: ?>
        <?php echo $field['name']; ?>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($enabled): ?>
        <select id="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-value'; ?>" class="field-value" name="<?php echo 'field-'.$index.'-'.$field['model'].'-'.$field['field'].'-value'; ?>">
          <?php foreach ($report::field_values($model, $field['field'], $field['model']) as $val): ?>
          <option value="<?php echo $val; ?>" <?php if ($field['value'] == $val) echo 'selected="selected"' ?>><?php echo $report::$aggregates[$val] ?: strtoupper($val); ?></option>
          <?php endforeach; ?>
        </select>
        <?php else: ?>
        <?php echo $report::$aggregates[$field['value']] ?: strtoupper($field['value']); ?>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php if ($enabled): ?>
  <div class="submit">
    <input name="save" type="submit" value="Save Changes" />
  </div>
  <?php endif; ?>
</form>