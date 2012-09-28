<?php

if (!$primary)   $primary   = Request::$current->controller();
if (!$secondary) $secondary = Request::$current->action();
if (!$command)   $command   = Request::$current->param('command');
if (!$id)        $id        = Request::$current->param('id');

// may not be a good idea to do this, if routes change in the future
if (!$command && !is_numeric($id)) {
  $command = $id;
  $id      = NULL;
}

?>
<div id="navigation">
  <ul class="nav primary">
    <li class="<?php if ($secondary == 'logout')  echo 'active'; ?> right"><?php echo HTML::anchor('logout', SGS::title('logout')); ?></li>

    <?php if (Auth::instance()->logged_in()): ?>
    <li class="<?php if ($primary == 'index')     echo 'active'; ?>"><?php echo HTML::anchor('', SGS::title('index')); ?></li>

    <?php if (Auth::instance()->logged_in('data')): ?>
    <li class="<?php if ($primary == 'import')    echo 'active'; ?>"><?php echo HTML::anchor('import', SGS::title('import')); ?></li>
    <li class="<?php if ($primary == 'export')    echo 'active'; ?>"><?php echo HTML::anchor('export', SGS::title('export')); ?></li>
    <li class="<?php if ($primary == 'documents') echo 'active'; ?>"><?php echo HTML::anchor('documents', SGS::title('documents')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('analysis')): ?>
    <li class="<?php if ($primary == 'analysis')  echo 'active'; ?>"><?php echo HTML::anchor('analysis', SGS::title('analysis')); ?></li>
    <li class="<?php if ($primary == 'checks')  echo 'active'; ?>"><?php echo HTML::anchor('checks', SGS::title('checks')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('reports')): ?>
    <li class="<?php if ($primary == 'reports')   echo 'active'; ?>"><?php echo HTML::anchor('', SGS::title('reports')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('admin')): ?>
    <li class="<?php if ($primary == 'admin')     echo 'active'; ?>"><?php echo HTML::anchor('admin', SGS::title('admin')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('barcodes')): ?>
    <li class="<?php if ($primary == 'barcodes')     echo 'active'; ?>"><?php echo HTML::anchor('barcodes', SGS::title('barcodes')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('users')): ?>
    <li class="<?php if ($primary == 'users')     echo 'active'; ?>"><?php echo HTML::anchor('users', SGS::title('users')); ?></li>
    <?php endif; ?>

    <?php else: ?>
    <li class="<?php if ($secondary == 'login')   echo 'active'; ?>"><?php echo HTML::anchor('login', SGS::title('login')); ?></li>
    <?php endif; ?>
  </ul>


  <?php if ($primary and $primary != 'index'): ?>
  <ul class="nav secondary">
    <?php if ($primary == 'admin'): ?>
    <li class="<?php if ($secondary == 'operators') echo 'active'; ?>"><?php echo HTML::anchor('admin/operators', SGS::title('admin/operators')); ?></li>
    <li class="<?php if ($secondary == 'sites')     echo 'active'; ?>"><?php echo HTML::anchor('admin/sites', SGS::title('admin/sites')); ?></li>
    <li class="<?php if ($secondary == 'blocks')    echo 'active'; ?>"><?php echo HTML::anchor('admin/blocks', SGS::title('admin/blocks')); ?></li>
    <li class="<?php if ($secondary == 'species')   echo 'active'; ?>"><?php echo HTML::anchor('admin/species', SGS::title('admin/species')); ?></li>

    <?php elseif ($primary == 'import'): ?>
    <li class="<?php if ($secondary == 'upload') echo 'active'; ?>"><?php echo HTML::anchor('import/upload', SGS::title('import/upload')); ?></li>
    <li class="<?php if ($secondary == 'files')  echo 'active'; ?>"><?php echo HTML::anchor('import/files', SGS::title('import/files')); ?></li>
    <li class="<?php if ($secondary == 'data')   echo 'active'; ?>"><?php echo HTML::anchor('import/data', SGS::title('import/data')); ?>
      <?php if ($secondary == 'data'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssf') echo 'active'; ?>"><?php echo HTML::anchor('import/data/ssf', SGS::title('import/data/ssf')); ?></li>
        <li class="<?php if ($command == 'tdf') echo 'active'; ?>"><?php echo HTML::anchor('import/data/tdf', SGS::title('import/data/tdf')); ?></li>
        <li class="<?php if ($command == 'ldf') echo 'active'; ?>"><?php echo HTML::anchor('import/data/ldf', SGS::title('import/data/ldf')); ?></li>
      </ul>
      <?php endif; ?>
    </li>

    <?php elseif ($primary == 'export'): ?>
    <li class="<?php if ($secondary == 'download') echo 'active'; ?>"><?php echo HTML::anchor('export/download', SGS::title('export/download')); ?>
      <?php if ($secondary == 'download'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssf') echo 'active'; ?>"><?php echo HTML::anchor('export/download/ssf', SGS::title('export/download/ssf')); ?></li>
        <li class="<?php if ($command == 'tdf') echo 'active'; ?>"><?php echo HTML::anchor('export/download/tdf', SGS::title('export/download/tdf')); ?></li>
        <li class="<?php if ($command == 'ldf') echo 'active'; ?>"><?php echo HTML::anchor('export/download/ldf', SGS::title('export/download/ldf')); ?></li>
      </ul>
      <?php endif; ?>
    </li>

    <li class="<?php if ($secondary == 'files')    echo 'active'; ?>"><?php echo HTML::anchor('export/files', SGS::title('export/files')); ?></li>
    <li class="<?php if ($secondary == 'data')     echo 'active'; ?>"><?php echo HTML::anchor('export/data', SGS::title('export/data')); ?></li>

    <?php elseif ($primary == 'analysis'): ?>
    <li class="<?php if ($secondary == 'ssf') echo 'active'; ?>"><?php echo HTML::anchor('analysis/ssf', SGS::title('analysis/ssf')); ?></li>
    <li class="<?php if ($secondary == 'tdf') echo 'active'; ?>"><?php echo HTML::anchor('analysis/tdf', SGS::title('analysis/tdf')); ?></li>
    <li class="<?php if ($secondary == 'ldf') echo 'active'; ?>"><?php echo HTML::anchor('analysis/ldf', SGS::title('analysis/ldf')); ?></li>

    <?php elseif ($primary == 'barcodes'): ?>
    <li class="<?php if ($secondary == 'upload') echo 'active'; ?>"><?php echo HTML::anchor('barcodes/upload', SGS::title('barcodes/upload')); ?></li>
    <li class="<?php if ($secondary == 'download') echo 'active'; ?>"><?php echo HTML::anchor('barcodes/download', SGS::title('barcodes/download')); ?></li>

    <?php endif; ?>
  </ul>

  <?php if ($links): ?>
  <ul class="nav commands">
    <?php foreach ((array) $links as $key => $value): ?>
    <?php
      $link  = is_array($value) ? $value['link']  : $key;
      $title = is_array($value) ? $value['title'] : $value;
    ?>
    <li class="<?php if ($active_link == $link) echo 'active'; ?>"><?php echo HTML::anchor($link, $title); ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php endif; ?>
</div>
