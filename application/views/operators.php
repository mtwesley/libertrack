<?php $classes[] = 'data'; ?>
<style>
  #content table.data td {

    white-space: normal !important;
    vertical-align: baseline;
  }
  #content table.data th,
  #content table.data td {
    padding-top: 0px !important;
  }
  #content table.data th.type img,
  #content table.data td.type img,
  #content table.data th.status img,
  #content table.data td.status img,
  #content table.data th.image img,
  #content table.data td.image {
    top: 3px;
  }
</style>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th class="type"></th>
    <th class="status"></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'tin')), 'TIN'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'short_name')), 'Short Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'contact')), 'Contact'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'address')), 'Address'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'email')), 'E-mail'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'phone')), 'Phone Number'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($operators as $operator): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td class="type"><span class="data-type">OPERATOR</span></td>
    <td class="status"><?php echo HTML::image('images/building.png', array('class' => 'barcode', 'title' => 'Barcode')); ?></td>
    <td><?php echo $operator->tin; ?></td>
    <td><?php echo $operator->name; ?></td>
    <td><?php echo $operator->short_name; ?></td>
    <td><?php echo $operator->contact; ?></td>
    <td><?php echo nl2br($operator->address); ?></td>
    <td><?php echo $operator->email; ?></td>
    <td><?php echo $operator->phone; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('config/operators/'.$operator->id.'/edit', 'Edit', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
