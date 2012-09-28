<?php $classes[] = 'data'; ?>
<table class="<?php echo SGS::render_classes($classes); ?>">
  <tr class="head">
    <th>Username</th>
    <th>Name</th>
    <th>E-mail</th>
    <th>Last Login</th>
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
      ->order_by('to_timestamp', 'desc')
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
    <td class="wrap-normal"><?php echo SGS::implodify($user->roles->find_all()->as_array(NULL, 'description')); ?></td>
    <td class="links">
      <?php echo HTML::anchor('users/'.$user->id.'/edit', 'Edit', array('class' => 'link')); ?>
      <?php echo HTML::anchor('users/'.$user->id.'/password', 'Change Password', array('class' => 'link')); ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
