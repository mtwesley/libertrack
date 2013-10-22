<?php
$classes[] = 'form';
?>
<style>
  .subnav {
    border: 1px solid #e6dfd4;
    background-color: #f0ede8;
    padding: 4px 6px;
    color: #5a5a5a;
    text-align: center;
  }

  .subnav a,
  .subnav a:visited {
    color: #8a8a8a;
    text-decoration: none;
  }

  .subnav a:hover,
  .subnav a.active {
    color: #222;
  }

  .subnav a.active {
    font-weight: bold;
  }
</style>
<?php if ($report_type or $model): ?>
<table class="data-header">
  <?php if ($report_type): ?>
  <tr>
    <td class="label">Report Type:</td>
    <td><?php echo $report::$types[$report_type]['name']; ?></td>
  </tr>
  <?php endif; ?>
  <?php if ($model): ?>
  <tr>
    <td class="label">Model:</td>
    <td><?php echo $report::$models[$model]['name']; ?></td>
  </tr>
  <?php endif; ?>
</table>
<?php endif; ?>
<?php if ($report_type and $model): ?>
<div class="subnav">
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/fields'; ?>" <?php if ($step == 'fields'):?>class="active"<?php endif; ?>>Fields</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/filters'; ?>" <?php if ($step == 'filters'):?>class="active"<?php endif; ?>>Filters</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/limits'; ?>" <?php if ($step == 'limits'):?>class="active"<?php endif; ?>>Order and Limit</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/preview'; ?>" <?php if ($step == 'preview'):?>class="active"<?php endif; ?>>Preview</a>
</div>
<?php endif; ?>