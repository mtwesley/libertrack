<?php
$classes[] = 'form';
?>
<style>
  .subnav {
    margin: 5px 0;
    border: 1px solid #bcc6aa;
    background-color: #ecefe7;
    padding: 4px 6px;
    color: #333;
    text-align: center;
  }

  .subnav a,
  .subnav a:visited {
    color: #333;
    text-decoration: none;
  }

  .subnav a:hover {
    text-decoration: underline;
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
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/fields'; ?>" <?php if ($step == 'fields'):?>class="active"<?php endif; ?>>Setup Fields</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/filters'; ?>" <?php if ($step == 'filters'):?>class="active"<?php endif; ?>>Setup Filters</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/limits'; ?>" <?php if ($step == 'limits'):?>class="active"<?php endif; ?>>Setup Sorting Order and Row Limit</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/preview'; ?>" <?php if ($step == 'preview'):?>class="active"<?php endif; ?>>Preview and Download</a>
</div>
<?php endif; ?>