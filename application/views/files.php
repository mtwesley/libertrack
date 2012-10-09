<?php
$classes[] = 'data';
?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th></th>
    <th>Type</th>
    <th>Name</th>
    <th>Size</th>
    <th>Uploaded</th>
    <th>Operator</th>
    <th>Site</th>
    <th>Block</th>

    <?php if ($mode == 'import'): ?>
    <th>Statistics</th>
    <?php endif; ?>

    <th class="links"></th>
  </tr>
  <?php foreach ($files as $file): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><span class="data-type"><?php echo $file->operation_type; ?></span></td>
    <td>
      <?php
        $info = pathinfo($file->name);
        switch ($info['extension']):
          case 'xls':
          case 'xlsx':
            echo HTML::image('images/xls.png', array('class' => 'xls', 'title' => SGS::$file_type['xls'])); break;
          case 'csv':
            echo HTML::image('images/csv.png', array('class' => 'csv', 'title' => SGS::$file_type['csv'])); break;
        endswitch;
      ?>
    </td>
    <td>
      <?php
        if ($file->path) echo HTML::anchor($file->path, $file->name, array('title' => 'Download', 'class' => 'download-link'));
        else echo $file->name;
      ?>
    </td>
    <td><?php echo Num::unbytes($file->size, 0); ?></td>
    <td><?php echo SGS::date($file->timestamp); ?></td>
    <td><?php echo $file->operator->name; ?></td>
    <td><?php echo $file->site->name; ?></td>
    <td><?php echo $file->block->name; ?></td>

    <?php if ($mode == 'import'): ?>
    <?php
      $_p = (int) $file->csv->where('status', '=', 'P')->find_all()->count();
      $_a = (int) $file->csv->where('status', '=', 'A')->find_all()->count();
      $_r = (int) $file->csv->where('status', '=', 'R')->find_all()->count();
      $_u = (int) $file->csv->where('status', '=', 'U')->find_all()->count();

      $_total = $_p + $_a + $_r + $_u;

      $_pp = $_total ? floor($_p * 100 / $_total) : 0;
      $_ap = $_total ? floor($_a * 100 / $_total) : 0;
      $_rp = $_total ? floor($_r * 100 / $_total) : 0;
      $_up = $_total ? floor($_u * 100 / $_total) : 0;
    ?>
    <td>
      <span class="pending"><?php echo $_p; ?> Pending (<?php echo $_pp; ?>%)</span> |
      <span class="accepted"><?php echo $_a; ?> Accepted (<?php echo $_ap; ?>%)</span> |
      <span class="rejected"><?php echo $_r; ?> Rejected (<?php echo $_rp; ?>%)</span> |
      <span class="duplicated"><?php echo $_u; ?> Duplicated (<?php echo $_up; ?>%)</span>
    </td>
    <?php endif; ?>

    <td class="links">
      <?php if (in_array($file->operation, array('I','E'))) echo HTML::anchor(strtolower(SGS::value($file->operation, 'operation', 'U')).'/files/'.$file->id.'/review', 'Review', array('class' => 'link')); ?>

      <?php if ($mode == 'import'): ?>
      <?php echo HTML::anchor('import/files/'.$file->id.'/process', 'Process', array('class' => 'link')); ?>
      <?php echo HTML::anchor('import/files/'.$file->id.'/delete', 'Delete', array('class' => 'link')); ?>
      <span class="link toggle-download-form">Download</span>
      <?php endif; ?>
    </td>
  </tr>
  <?php if ($mode == 'import'): ?>
  <tr class="form download-form <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="<?php echo ($mode == 'import') ? 11 : 10; ?>">
      <?php
        echo Formo::form(array('attr' => array('action' => '/import/files/'.$file->id.'/download')))
          ->add_group('status', 'checkboxes', SGS::$csv_status, array_keys(SGS::$csv_status), array(
            'label'    => 'Status',
            'required' => TRUE,
          ))
          ->add('name', 'input', substr($file->name, 0, strrpos($file->name, '.')), array('label' => 'Name'))
          ->add('type', 'radios', array(
            'options' => array(
              'xls' => SGS::$file_type['xls'],
              'csv' => SGS::$file_type['csv']
            ),
            'label'    => 'Format',
            'required' => TRUE,
            'value'    => 'xls'
          ))
          ->add('download', 'submit', array('label' => 'Download'));
      ?>
    </td>
  </tr>
  <?php endif; ?>
  <?php endforeach; ?>
</table>