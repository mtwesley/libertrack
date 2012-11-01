  type d_invoice_type not null,
  site_id d_id not null,
  reference_number d_positive_int unique,
  is_draft d_bool default true not null,
  from_date d_date not null,
  to_date d_date not null,
  created_date d_date not null,
  due_date d_date not null,

<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th></th>
    <th></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'reference_number')), 'Reference Number'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'from_date')), 'From'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'to_date')), 'To'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'created_date')), 'Date Created'); ?></th>
    <th><?php echo HTML::anchor(Request::$current->url().URL::query(array('sort' => 'due_date')), 'Date Due'); ?></th>
    <th>Total Logins</th>
    <th>Currently Logged In</th>
    <th>Last Activity</th>
    <th>Privileges</th>
    <th class="links"></th>
  </tr>
  <?php foreach ($users as $user): ?>
  <?php
    $total_logins = DB::select()
      ->from('sessions')
      ->where('user_id', '=', $user->id)
      ->execute()
      ->count();

    $activity = DB::select('cookie', 'from_timestamp', 'to_timestamp')
      ->from('sessions')
      ->where('user_id', '=', $user->id)
      ->order_by('to_timestamp', 'DESC')
      ->limit(1)
      ->execute()
      ->current();

    $currently_logged_in = $activity['cookie'] ? 'YES' : 'NO';

    $last_activity = SGS::datetime($activity['to_timestamp']);
  ?>
  <tr class="<?php print SGS::odd_even($odd); ?>">
    <td><?php echo $user->username; ?></td>
    <td><?php echo $user->name; ?></td>
    <td><?php echo $user->email; ?></td>
    <td><?php echo SGS::datetime($user->last_timestamp); ?></td>
    <td><?php echo $total_logins; ?></td>
    <td><?php echo $currently_logged_in; ?></td>
    <td><?php echo $last_activity; ?></td>
    <td class="wrap-normal">
      <?php if ($roles = $user->roles->find_all()->as_array(NULL, 'description')): ?>
      <ul class="roles">
        <?php foreach ($roles as $role): ?>
        <li><?php echo $role; ?></li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </td>
    <td class="links">
      <?php echo HTML::anchor('users/'.$user->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php echo HTML::anchor('users/'.$user->id.'/password', 'Change Password', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
