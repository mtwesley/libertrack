<?php

$options = (array) $options + array(
  'table'   => TRUE,
  'links'   => TRUE,
);

$classes[] = 'data';
?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="image"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'size')), 'Size'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'timestamp')), 'Uploaded'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'operator_id')), 'Operator'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'site_id')), 'Site'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'block_id')), 'Block'); ?></th>
    <th colspan="5">Statistics</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($files as $file): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type"><?php echo $file->operation_type; ?></span></td>
    <td class="image">
      <?php
        $info = pathinfo($file->name);
        switch ($info['extension']):
          case 'xls':
          case 'xlsx':
            echo HTML::image('images/xls.png', array('class' => 'xls', 'title' => SGS::$file_type['xls'])); break;
          case 'csv':
            echo HTML::image('images/csv.png', array('class' => 'csv', 'title' => SGS::$file_type['csv'])); break;
          case 'pdf':
            echo HTML::image('images/csv.png', array('class' => 'csv', 'title' => SGS::$file_type['pdf'])); break;
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
    <?php
      $_p = (int) DB::select('count("id")')->from('csv')->where('file_id', '=', $file->id)->and_where('status', '=', 'P')->execute()->get('count');
      $_a = (int) DB::select('count("id")')->from('csv')->where('file_id', '=', $file->id)->and_where('status', '=', 'A')->execute()->get('count');
      $_r = (int) DB::select('count("id")')->from('csv')->where('file_id', '=', $file->id)->and_where('status', '=', 'R')->execute()->get('count');
      $_u = (int) DB::select('count("id")')->from('csv')->where('file_id', '=', $file->id)->and_where('status', '=', 'U')->execute()->get('count');
      $_d = (int) DB::select('count("id")')->from('csv')->where('file_id', '=', $file->id)->and_where('status', '=', 'D')->execute()->get('count');

      $_total = $_p + $_a + $_r + $_u + $_d;

      $_pp = $_total ? floor($_p * 100 / $_total) : 0;
      $_ap = $_total ? floor($_a * 100 / $_total) : 0;
      $_rp = $_total ? floor($_r * 100 / $_total) : 0;
      $_up = $_total ? floor($_u * 100 / $_total) : 0;
      $_dp = $_total ? floor($_d * 100 / $_total) : 0;
    ?>
    <td><span class="pending"><?php echo $_p; ?> <?php print HTML::anchor($file->data_type.'/files/'.$file->id.'/review?status=P', 'Pending'); ?> (<?php echo $_pp; ?>%)</span></td>
    <td><span class="accepted"><?php echo $_a; ?> <?php print HTML::anchor($file->data_type.'/files/'.$file->id.'/review?status=A', 'Accepted'); ?> (<?php echo $_ap; ?>%)</span></td>
    <td><span class="rejected"><?php echo $_r; ?> <?php print HTML::anchor($file->data_type.'/files/'.$file->id.'/review?status=R', 'Rejected'); ?> (<?php echo $_rp; ?>%)</span></td>
    <td><span class="duplicated"><?php echo $_u; ?> <?php print HTML::anchor($file->data_type.'/files/'.$file->id.'/review?status=U', 'Duplicated'); ?> (<?php echo $_up; ?>%)</span></td>
    <td><span class="deleted"><?php echo $_d; ?> <?php print HTML::anchor($file->data_type.'/files/'.$file->id.'/review?status=D', 'Deleted'); ?> (<?php echo $_dp; ?>%)</span></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php if ($options['links']): ?>
          <?php echo HTML::anchor($file->data_type.'/files/'.$file->id, 'View', array('class' => 'link')); ?>
          <?php if (Auth::instance()->logged_in('management')) echo HTML::anchor($file->data_type.'/files/'.$file->id.'/delete', 'Delete', array('class' => 'link')); ?>
          <?php echo HTML::anchor($file->data_type.'/files/'.$file->id.'/process', 'Process', array('class' => 'link')); ?>
          <?php echo HTML::anchor($file->data_type.'/files/'.$file->id.'/review', 'Review', array('class' => 'link')); ?>
          <span class="link toggle-download-form">Download</span>
          <?php endif; ?>
        </div>
      </div>
    </td>
  </tr>
  <?php if ($has_csv): ?>
  <tr class="form download-form <?php echo $odd ? 'odd' : 'even'; ?>">
    <td colspan="11">
      <?php
        echo Formo::form(array('attr' => array('action' => '/'.$file->data_type.'/files/'.$file->id.'/download')))
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