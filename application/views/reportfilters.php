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

  input.filter-values {
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
      <th class="remove"></th>
      <?php endif; ?>
      <th class="type"></th>
      <th>Model</th>
      <th>Field</th>
      <th>Filter</th>
      <th>Values</th>
    </tr>
    <?php if ($filters) foreach ($filters as $index => $filter): ?>
    <tr id="<?php echo 'filter-'.$index.'-'.$filter['model'].'-'.$filter['field'].'-'.$filter['filter']; ?>" class="<?php echo 'filter-'.$index.'-'.$filter['field'].'-'.$filter['filter']; ?> <?php echo SGS::odd_even($odd); ?>">
      <?php if ($enabled): ?>
      <td class="remove"><input name="<?php echo 'filter-'.$index.'-'.$filter['model'].'-'.$filter['field'].'-'.$filter['filter'].'-remove'; ?>" type="submit" value="Remove" /></td>
      <?php endif; ?>
      <td class="data-row-details type"><span class="data-type"><?php echo strtoupper($filter['model']); ?></span></td>
      <td><?php echo $report::$models[$filter['model']]['name']; ?></td>
      <td><?php echo $report::$models[$filter['model']]['fields'][$filter['field']]['name'] ?></td>
      <td>
        <?php if ($enabled): ?>
        <select id="<?php echo 'filter-'.$index.'-'.$filter['model'].'-'.$filter['field'].'-'.$filter['filter'].'-filter'; ?>" class="filter-filter" name="<?php echo 'filter-'.$index.'-'.$filter['model'].'-'.$filter['field'].'-'.$filter['filter'].'-filter'; ?>">
          <?php foreach ($report::$filters as $k => $v): ?>
          <option value="<?php echo $k; ?>" <?php if ($filter['filter'] == $k) echo 'selected="selected"' ?>><?php echo $v; ?></option>
          <?php endforeach; ?>
        </select>
        <?php else: ?>
        <?php echo $report::$filters[$filter['filter']]; ?>
        <?php endif; ?>
      </td>
      <td>
        <?php if ($enabled): ?>
        <input id="<?php echo 'filter-'.$index.'-'.$filter['model'].'-'.$filter['field'].'-'.$filter['filter'].'-values'; ?>" class="filter-values" name="<?php echo 'filter-'.$index.'-'.$filter['model'].'-'.$filter['field'].'-'.$filter['filter'].'-values'; ?>" type="text" value="<?php echo implode(',', $filter['values']); ?>" />
        <?php else: ?>
        <?php echo implode(',', $filter['values']); ?>
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