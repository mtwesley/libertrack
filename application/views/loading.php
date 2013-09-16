<?php
$classes[] = 'form';
?>
<style>
  div.submit {
    padding: 6px 0 5px;
    text-align: right;
  }
</style>
<form method="post" class="has-section">
  <table class="<?php echo SGS::render_classes($classes); ?>">
    <tr class="head">
      <th class="type"></th>
      <th class="status"></th>
      <th>Date</th>
      <th>Species</th>
      <th>Log Barcode</th>
      <th>Origin</th>
      <th>Destination</th>
      <th>Grade</th>
      <th>Volume</th>
      <th>Current Status</th>
      <th>Loading Status</th>
    </tr>
    <?php foreach ($data as $record): ?>
    <tr id="<?php echo $record::$type.'-'.$record->id; ?>" class="<?php echo $record::$type.'-'.$record->id; ?> <?php echo SGS::odd_even($odd); ?>">
      <td class="data-row-details type"><span class="data-type"><?php echo $form_type; ?></span></td>
      <td class="status">
        <?php
          if (!$record->is_verification()) switch ($record->status):
            case 'P': echo HTML::image('images/bullet_yellow.png', array('class' => 'status pending', 'title' => 'Unchecked')); break;
            case 'A': echo HTML::image('images/bullet_green.png', array('class' => 'status accepted', 'title' => 'Passed')); break;
            case 'R': echo HTML::image('images/bullet_red.png', array('class' => 'status rejected', 'title' => 'Failed')); break;
          endswitch;
          if (($verification = $record->verification()) and $verification->loaded()) switch ($verification->status):
            case 'P': echo HTML::image('images/bullet_check_yellow.png', array('class' => 'status pending', 'title' => 'Unverified')); break;
            case 'A': echo HTML::image('images/bullet_check.png', array('class' => 'status accepted', 'title' => 'Accurate')); break;
            case 'R': echo HTML::image('images/bullet_check_red.png', array('class' => 'status rejected', 'title' => 'Inaccurate')); break;
          endswitch;
          if ($record->is_locked()) echo HTML::image('images/bullet_locked.png', array('class' => 'locked', 'title' => 'Locked'));
          if ($record->has_problem()) echo HTML::image('images/bullet_exclamation.png', array('class' => 'problem', 'title' => 'Problems'));
        ?>
      </td>
      <td><?php echo SGS::date($record->create_date); ?></td>
      <td><?php echo $record->species->code; ?></td>
      <td><?php echo $record->barcode->barcode; ?></td>
      <td><?php echo SGS::locationify($record->origin); ?></td>
      <td><?php echo SGS::locationify($record->destination); ?></td>
      <td><?php echo $record->grade; ?></td>
      <td><?php echo $record->volume; ?></td>
      <td><?php echo SGS::$barcode_activity[$record->barcode->get_activity()]; ?></td>
      <td>
        <input name="<?php echo "SPECS-".$record->id."-loading"; ?>" type="radio" value="O" <?php if ($record->barcode->get_activity(array('S','O')) != 'S') echo 'checked="checked"' ?> /> Loaded on Vessel &nbsp; &nbsp;
        <input name="<?php echo "SPECS-".$record->id."-loading"; ?>" type="radio" value="S" /> Short-shipped
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  <div class="submit">
    <input type="submit" value="Update Loading Status" />
  </div>
</form>