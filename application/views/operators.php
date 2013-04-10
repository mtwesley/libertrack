<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'tin')), 'TIN'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'name')), 'Name'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'contact')), 'Contact'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'address')), 'Address'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'email')), 'E-mail'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'phone')), 'Phone Number'); ?></th>
    <th class="links"></th>
  </tr>
  <?php foreach ($operators as $operator): ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $operator->tin; ?></td>
    <td><?php echo $operator->name; ?></td>
    <td><?php echo $operator->contact; ?></td>
    <td><?php echo nl2br($operator->address); ?></td>
    <td><?php echo $operator->email; ?></td>
    <td><?php echo $operator->phone; ?></td>
    <td class="links">
      <div class="links-container">
        <span class="link link-title">+</span>
        <div class="links-links">
          <?php echo HTML::anchor('admin/operators/'.$operator->id.'/edit', 'Edit', array('class' => 'link')); ?>
        </div>
      </div>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
