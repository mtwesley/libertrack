<?php

$classes[] = 'form';

$tolerances = DB::select()
  ->from('tolerances')
  ->order_by('form_type')
  ->order_by('check')
  ->execute()
  ->as_array();

?>
<style>
  table tr td.form-type {
    width: 225px;
    white-space: nowrap;
  }
  table tr td.measurement {
    width: 100px;
  }
  table tr td.measurement input {
    width: 100px;
  }
  div.submit {
    padding: 6px 0 5px;
    text-align: center;
  }
</style>
<form method="post">
  <table class="<?php echo SGS::render_classes($classes); ?>">
    <tr class="head">
      <th>Form</th>
      <th>Check</th>
      <th>Accuracy Range</th>
      <th>Tolerance Range</th>
    </tr>
    <?php foreach ($tolerances as $tolerance): ?>
    <?php $model = ORM::factory($tolerance['form_type']); ?>
    <tr class="<?php print SGS::odd_even($odd); ?>">
      <td class="form-type"><?php echo SGS::$operation_type[$tolerance['form_type']]; ?></td>
      <td><?php echo $model::$checks['tolerance']['checks'][$tolerance['check']]['title']; ?></td>
      <td class="measurement"><input name="<?php echo "{$tolerance['form_type']}-{$tolerance['check']}-accuracy_range"; ?>" type="text" value="<?php echo $tolerance['accuracy_range']; ?>" /></td>
      <td class="measurement"><input name="<?php echo "{$tolerance['form_type']}-{$tolerance['check']}-tolerance_range"; ?>" type="text" value="<?php echo $tolerance['tolerance_range']; ?>" /></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="submit">
    <input type="submit" value="Update Tolerances" />
  </div>
</form>