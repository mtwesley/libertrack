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
<style type="text/css">
  #navigation .primary {
    margin: 0;
    padding: 0;
    height: 28px;
    width: 100%;
    background-color: #e4e4e4;
    list-style-type: none;
  }

  #navigation .primary li {
    margin: 0;
    padding: 0 0 0 25px;
    width: auto;
    float: left;
    display: block;
    text-transform: uppercase;
  }

  #navigation .primary li.right {
    float: right;
    margin-right: 25px;
  }

  #navigation .primary li a {
    padding: 7px 0 6px;
    color: #555;
    text-decoration: none;
    line-height: 26px;
  }

  #navigation .primary li a:hover {
    color: #333;
    border-bottom: 2px solid #aaa;
    cursor: pointer;
  }

  #navigation .primary li.active a {
    color: #333;
    cursor: default;
    font-weight: bold;
    border-bottom: 2px solid #000;
  }

  #navigation .primary li.active a:hover {
    border-bottom: 2px solid #f60;
  }


  #navigation .secondary {
    margin: 0;
    padding: 4px 0;
    list-style-type: none;
    text-align: left;
    background-color: #fafafa;
  }

  #navigation .secondary li {
    margin: 0;
    padding: 6px 10px 0;
  }

  #navigation .secondary li a {
    background: transparent url('/images/list_off.gif') left center no-repeat;
    padding-left: 15px;
    text-align: left;
    text-decoration: none;
    color: #555;
  }

  #navigation .secondary li a:hover {
    background: transparent url('/images/list_on.gif') left center no-repeat;
    color: #333;
  }

  #navigation .secondary li.active a {
    background: transparent url('/images/list_active.gif') left center no-repeat;
    color: #000;
  }

  #navigation .secondary li.active a:hover {
    background: transparent url('/images/list_orange.gif') left center no-repeat;
    cursor: default;
  }

  #navigation .secondary li .commands {
    margin: 0;
    padding: 0 16px;
    list-style-type: none;
  }
  #navigation .secondary li .commands li {}
  #navigation .secondary li .commands li a {
    background: transparent url('/images/list_off.gif') left center no-repeat;
    padding-left: 15px;
    text-align: left;
    text-decoration: none;
    color: #555;
    cursor: pointer;
  }
  #navigation .secondary li .commands li a:hover {
    background: transparent url('/images/list_on.gif') left center no-repeat;
    color: #333;
    cursor: pointer;
  }
  #navigation .secondary li .commands li.active a {
    background: transparent url('/images/list_active.gif') left center no-repeat;
    color: #000;
  }
  #navigation .secondary li .commands li.active a:hover {
    background: transparent url('/images/list_orange.gif') left center no-repeat;
    cursor: default;
  }

</style>
<div id="navigation">
  <ul class="nav primary">
    <?php if (Auth::instance()->logged_in()): ?>
    <li class="<?php if ($primary == 'index')     echo 'active'; ?>"><?php echo HTML::anchor('', SGS::title('index')); ?></li>

    <?php if (Auth::instance()->logged_in('data')): ?>
    <li class="<?php if ($primary == 'import')    echo 'active'; ?>"><?php echo HTML::anchor('import', SGS::title('import')); ?></li>
    <li class="<?php if ($primary == 'export')    echo 'active'; ?>"><?php echo HTML::anchor('export', SGS::title('export')); ?></li>
    <li class="<?php if ($primary == 'documents') echo 'active'; ?>"><?php echo HTML::anchor('documents', SGS::title('documents')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('analysis')): ?>
    <li class="<?php if ($primary == 'analysis')  echo 'active'; ?>"><?php echo HTML::anchor('', SGS::title('analysis')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('reports')): ?>
    <li class="<?php if ($primary == 'reports')   echo 'active'; ?>"><?php echo HTML::anchor('', SGS::title('reports')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('admin')): ?>
    <li class="<?php if ($primary == 'admin')     echo 'active'; ?>"><?php echo HTML::anchor('admin', SGS::title('admin')); ?></li>
    <?php endif; ?>

    <li class="<?php if ($secondary == 'logout')  echo 'active'; ?> right"><?php echo HTML::anchor('logout', SGS::title('logout')); ?></li>

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
    <li class="<?php if ($secondary == 'printjobs') echo 'active'; ?>"><?php echo HTML::anchor('admin/printjobs', SGS::title('admin/printjobs')); ?></li>
    <li class="<?php if ($secondary == 'users')     echo 'active'; ?>"><?php echo HTML::anchor('admin/users', SGS::title('admin/users')); ?></li>

    <?php elseif ($primary == 'import'): ?>
    <li class="<?php if ($secondary == 'upload') echo 'active'; ?>"><?php echo HTML::anchor('import/upload', SGS::title('import/upload')); ?></li>
    <li class="<?php if ($secondary == 'files')  echo 'active'; ?>"><?php echo HTML::anchor('import/files', SGS::title('import/files')); ?></li>
    <li class="<?php if ($secondary == 'data')   echo 'active'; ?>"><?php echo HTML::anchor('import/data', SGS::title('import/data')); ?></li>

    <?php elseif ($primary == 'export'): ?>
    <li class="<?php if ($secondary == 'download') echo 'active'; ?>">
      <?php echo HTML::anchor('export/download', SGS::title('export/download')); ?>
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
