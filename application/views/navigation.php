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
    <?php $user = Auth::instance()->get_user(); ?>
    <?php if (Auth::instance()->logged_in()): ?>
    <?php if ($user->name): ?>
    <li class="right">
      <span>(<?php echo $user->name; ?>)</span>
    </li>
    <?php endif; ?>
    <li class="<?php if ($secondary == 'logout')  echo 'active'; ?> right"><?php echo HTML::anchor('logout', SGS::title('logout')); ?></li>
    <li class="<?php if ($primary == 'index')     echo 'active'; ?>"><?php echo HTML::anchor('', SGS::title('index')); ?></li>

    <?php if (Auth::instance()->logged_in('data')): ?>
    <li class="<?php if ($primary == 'declaration') echo 'active'; ?>"><?php echo HTML::anchor('declaration', SGS::title('declaration')); ?></li>
    <li class="<?php if ($primary == 'verification') echo 'active'; ?>"><?php echo HTML::anchor('verification', SGS::title('verification')); ?></li>
    <!-- <li class="<?php if ($primary == 'documents') echo 'active'; ?>"><?php echo HTML::anchor('documents', SGS::title('documents')); ?></li> -->
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('analysis')): ?>
    <li class="<?php if ($primary == 'analysis')  echo 'active'; ?>"><?php echo HTML::anchor('analysis', SGS::title('analysis')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('invoices')): ?>
    <li class="<?php if ($primary == 'invoices')  echo 'active'; ?>"><?php echo HTML::anchor('invoices', SGS::title('invoices')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('exports')): ?>
    <li class="<?php if ($primary == 'exports')  echo 'active'; ?>"><?php echo HTML::anchor('exports', SGS::title('exports')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('reports')): ?>
    <li class="<?php if ($primary == 'reports')   echo 'active'; ?>"><?php echo HTML::anchor('reports', SGS::title('reports')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('admin')): ?>
    <li class="<?php if ($primary == 'config')     echo 'active'; ?>"><?php echo HTML::anchor('config', SGS::title('config')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('admin')): ?>
    <li class="<?php if ($primary == 'manage')     echo 'active'; ?>"><?php echo HTML::anchor('manage', SGS::title('manage')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('admin')): ?>
    <li class="<?php if ($primary == 'settings')     echo 'active'; ?>"><?php echo HTML::anchor('settings', SGS::title('settings')); ?></li>
    <?php endif; ?>

    <?php if (Auth::instance()->logged_in('users')): ?>
    <li class="<?php if ($primary == 'users')     echo 'active'; ?> right"><?php echo HTML::anchor('users', SGS::title('users')); ?></li>
    <?php elseif ($user->id): ?>
    <li class="<?php if ($primary == 'account')   echo 'active'; ?> right"><?php echo HTML::anchor('users/'.$user->id, 'Account'); ?></li>
    <?php endif; ?>

    <?php else: ?>
    <li class="<?php if ($secondary == 'login')   echo 'active'; ?>"><?php echo HTML::anchor('login', SGS::title('login')); ?></li>
    <?php endif; ?>
  </ul>

  <?php if ($primary and $primary != 'index'): ?>
  <ul class="nav secondary">
    <?php if ($primary == 'config'): ?>
    <li class="<?php if ($secondary == 'operators')  echo 'active'; ?>"><?php echo HTML::anchor('config/operators', SGS::title('config/operators')); ?></li>
    <li class="<?php if ($secondary == 'buyers')     echo 'active'; ?>"><?php echo HTML::anchor('config/buyers', SGS::title('config/buyers')); ?></li>
    <li class="<?php if ($secondary == 'sites')      echo 'active'; ?>"><?php echo HTML::anchor('config/sites', SGS::title('config/sites')); ?></li>
    <li class="<?php if ($secondary == 'blocks')     echo 'active'; ?>"><?php echo HTML::anchor('config/blocks', SGS::title('config/blocks')); ?></li>
    <li class="<?php if ($secondary == 'species')    echo 'active'; ?>"><?php echo HTML::anchor('config/species', SGS::title('config/species')); ?></li>

    <?php elseif ($primary == 'settings'): ?>
    <li class="<?php if ($secondary == 'tolerances') echo 'active'; ?>"><?php echo HTML::anchor('settings/tolerances', SGS::title('settings/tolerances')); ?></li>

    <?php elseif ($primary == 'manage'): ?>
    <li class="<?php if ($secondary == 'printjobs')  echo 'active'; ?>"><?php echo HTML::anchor('manage/printjobs', SGS::title('manage/printjobs')); ?>
      <?php if ($secondary == 'printjobs'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'list')     echo 'active'; ?>"><?php echo HTML::anchor('manage/printjobs/list', SGS::title('manage/printjobs/list')); ?></li>
        <li class="<?php if ($command == 'upload')   echo 'active'; ?>"><?php echo HTML::anchor('manage/printjobs/upload', SGS::title('manage/printjobs/upload')); ?></li>
        <li class="<?php if ($command == 'download') echo 'active'; ?>"><?php echo HTML::anchor('manage/printjobs/download', SGS::title('manage/printjobs/download')); ?></li>
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'barcodes')   echo 'active'; ?>"><?php echo HTML::anchor('manage/barcodes', SGS::title('manage/barcodes')); ?>
      <?php if ($secondary == 'barcodes'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'list')   echo 'active'; ?>"><?php echo HTML::anchor('manage/barcodes/list', SGS::title('manage/barcodes/list')); ?></li>
        <li class="<?php if ($command == 'query')  echo 'active'; ?>"><?php echo HTML::anchor('manage/barcodes/query', SGS::title('manage/barcodes/query')); ?></li>
      </ul>
      <?php endif; ?>
    </li>

    <?php elseif ($primary == 'declaration'): ?>
    <li class="<?php if ($secondary == 'upload')   echo 'active'; ?>"><?php echo HTML::anchor('declaration/upload', SGS::title('declaration/upload')); ?></li>
    <li class="<?php if ($secondary == 'files')  echo 'active'; ?>"><?php echo HTML::anchor('declaration/files', SGS::title('declaration/files')); ?></li>
    <li class="<?php if ($secondary == 'data')   echo 'active'; ?>"><?php echo HTML::anchor('declaration/data', SGS::title('declaration/data')); ?>
      <?php if ($secondary == 'data'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssf') echo 'active'; ?>"><?php echo HTML::anchor('declaration/data/ssf', SGS::title('declaration/data/ssf')); ?></li>
        <li class="<?php if ($command == 'tdf') echo 'active'; ?>"><?php echo HTML::anchor('declaration/data/tdf', SGS::title('declaration/data/tdf')); ?></li>
        <li class="<?php if ($command == 'ldf') echo 'active'; ?>"><?php echo HTML::anchor('declaration/data/ldf', SGS::title('declaration/data/ldf')); ?></li>
        <li class="<?php if ($command == 'specs') echo 'active'; ?>"><?php echo HTML::anchor('declaration/data/specs', SGS::title('declaration/data/specs')); ?></li>
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'search') echo 'active'; ?>"><?php echo HTML::anchor('declaration/search', SGS::title('declaration/search')); ?></li>

    <?php elseif ($primary == 'verification'): ?>
    <li class="<?php if ($secondary == 'upload')   echo 'active'; ?>"><?php echo HTML::anchor('verification/upload', SGS::title('verification/upload')); ?></li>
    <li class="<?php if ($secondary == 'files')  echo 'active'; ?>"><?php echo HTML::anchor('verification/files', SGS::title('verification/files')); ?></li>
    <li class="<?php if ($secondary == 'data')   echo 'active'; ?>"><?php echo HTML::anchor('verification/data', SGS::title('verification/data')); ?>
      <?php if ($secondary == 'data'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssfv') echo 'active'; ?>"><?php echo HTML::anchor('verification/data/ssfv', SGS::title('verification/data/ssfv')); ?></li>
        <li class="<?php if ($command == 'tdfv') echo 'active'; ?>"><?php echo HTML::anchor('verification/data/tdfv', SGS::title('verification/data/tdfv')); ?></li>
        <li class="<?php if ($command == 'ldfv') echo 'active'; ?>"><?php echo HTML::anchor('verification/data/ldfv', SGS::title('verification/data/ldfv')); ?></li>
        <!-- <li class="<?php if ($command == 'specsv') echo 'active'; ?>"><?php echo HTML::anchor('verification/data/specsv', SGS::title('verification/data/specsv')); ?></li> -->
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'search') echo 'active'; ?>"><?php echo HTML::anchor('verification/search', SGS::title('verification/search')); ?></li>
    <?php elseif ($primary == 'analysis'): ?>
    <li class="<?php if ($secondary == 'review') echo 'active'; ?>"><?php echo HTML::anchor('analysis/review', SGS::title('analysis/review')); ?>
      <?php if ($secondary == 'review'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssf' or $id == 'ssf')     echo 'active'; ?>"><?php echo HTML::anchor('analysis/review/ssf', SGS::title('analysis/review/ssf')); ?></li>
        <li class="<?php if ($command == 'tdf' or $id == 'tdf')     echo 'active'; ?>"><?php echo HTML::anchor('analysis/review/tdf', SGS::title('analysis/review/tdf')); ?></li>
        <li class="<?php if ($command == 'ldf' or $id == 'ldf')     echo 'active'; ?>"><?php echo HTML::anchor('analysis/review/ldf', SGS::title('analysis/review/ldf')); ?></li>
        <li class="<?php if ($command == 'specs' or $id == 'specs') echo 'active'; ?>"><?php echo HTML::anchor('analysis/review/specs', SGS::title('analysis/review/specs')); ?></li>
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'checks') echo 'active'; ?>"><?php echo HTML::anchor('analysis/checks', SGS::title('analysis/checks')); ?>
      <?php if ($secondary == 'checks'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssf')   echo 'active'; ?>"><?php echo HTML::anchor('analysis/checks/ssf', SGS::title('analysis/checks/ssf')); ?></li>
        <li class="<?php if ($command == 'tdf')   echo 'active'; ?>"><?php echo HTML::anchor('analysis/checks/tdf', SGS::title('analysis/checks/tdf')); ?></li>
        <li class="<?php if ($command == 'ldf')   echo 'active'; ?>"><?php echo HTML::anchor('analysis/checks/ldf', SGS::title('analysis/checks/ldf')); ?></li>
        <li class="<?php if ($command == 'specs') echo 'active'; ?>"><?php echo HTML::anchor('analysis/checks/specs', SGS::title('analysis/checks/specs')); ?></li>
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'verify') echo 'active'; ?>"><?php echo HTML::anchor('analysis/verify', SGS::title('analysis/verify')); ?>
      <?php if ($secondary == 'verify'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssfv')   echo 'active'; ?>"><?php echo HTML::anchor('analysis/verify/ssfv', SGS::title('analysis/verify/ssfv')); ?></li>
        <li class="<?php if ($command == 'tdfv')   echo 'active'; ?>"><?php echo HTML::anchor('analysis/verify/tdfv', SGS::title('analysis/verify/tdfv')); ?></li>
        <li class="<?php if ($command == 'ldfv')   echo 'active'; ?>"><?php echo HTML::anchor('analysis/verify/ldfv', SGS::title('analysis/verify/ldfv')); ?></li>
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'download') echo 'active'; ?>"><?php echo HTML::anchor('analysis/download', SGS::title('analysis/download')); ?>
      <?php if ($secondary == 'download'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'ssf') echo 'active'; ?>"><?php echo HTML::anchor('analysis/download/ssf', SGS::title('analysis/download/ssf')); ?></li>
        <li class="<?php if ($command == 'tdf') echo 'active'; ?>"><?php echo HTML::anchor('analysis/download/tdf', SGS::title('analysis/download/tdf')); ?></li>
        <li class="<?php if ($command == 'ldf') echo 'active'; ?>"><?php echo HTML::anchor('analysis/download/ldf', SGS::title('analysis/download/ldf')); ?></li>
        <li class="<?php if ($command == 'specs') echo 'active'; ?>"><?php echo HTML::anchor('analysis/download/specs', SGS::title('analysis/download/specs')); ?></li>
      </ul>
      <?php endif; ?>
    </li>

    <?php elseif ($primary == 'invoices'): ?>
    <li class="<?php if ($secondary == 'list')     echo 'active'; ?>"><?php echo HTML::anchor('invoices/list', SGS::title('invoices/list')); ?></li>
    <li class="<?php if ($secondary == 'create')   echo 'active'; ?>"><?php echo HTML::anchor('invoices/create', SGS::title('invoices/create')); ?>
      <?php if ($secondary == 'create'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'st')  echo 'active'; ?>"><?php echo HTML::anchor('invoices/create/st', SGS::title('invoices/create/st')); ?></li>
        <li class="<?php if ($command == 'exf') echo 'active'; ?>"><?php echo HTML::anchor('invoices/create/exf', SGS::title('invoices/create/exf')); ?></li>
      </ul>
      <?php endif; ?>
    </li>

    <?php elseif ($primary == 'exports'): ?>
    <li class="<?php if ($secondary == 'documents' and $command == 'list')   echo 'active'; ?>"><?php echo HTML::anchor('exports/documents/list', SGS::title('exports/documents/list')); ?></li>
    <li class="<?php if ($secondary == 'documents' and ($command == 'create' or $id == 'create')) echo 'active'; ?>"><?php echo HTML::anchor('exports/documents/create', SGS::title('exports/documents/create')); ?>
      <?php if ($secondary == 'documents' and ($command == 'create' or $id == 'create')): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'specs') echo 'active'; ?>"><?php echo HTML::anchor('exports/documents/create/specs', SGS::title('exports/documents/create/specs')); ?></li>
        <li class="<?php if ($command == 'exp')   echo 'active'; ?>"><?php echo HTML::anchor('exports/documents/create/exp', SGS::title('exports/documents/create/exp')); ?></li>
        <li class="<?php if ($command == 'cert')  echo 'active'; ?>"><?php echo HTML::anchor('exports/documents/create/cert', SGS::title('exports/documents/create/cert')); ?></li>
      </ul>
      <?php endif; ?>
    </li>
    <li class="<?php if ($secondary == 'documents' and $command == 'validate') echo 'active'; ?>"><?php echo HTML::anchor('exports/documents/validate', SGS::title('exports/documents/validate')); ?></li>

    <?php elseif ($primary == 'reports'): ?>
    <li class="<?php if ($secondary == 'list')   echo 'active'; ?>"><?php echo HTML::anchor('reports/list', SGS::title('reports/list')); ?></li>
    <li class="<?php if ($secondary == 'create') echo 'active'; ?>"><?php echo HTML::anchor('reports/create', SGS::title('reports/create')); ?>
      <?php if ($secondary == 'create'): ?>
      <ul class="nav commands">
        <li class="<?php if ($command == 'summary') echo 'active'; ?>"><?php echo HTML::anchor('reports/create/summary', SGS::title('reports/create/summary')); ?></li>
        <!-- <li class="<?php if ($command == 'csv')     echo 'active'; ?>"><?php echo HTML::anchor('reports/create/csv', SGS::title('reports/create/csv')); ?></li> -->
        <li class="<?php if ($command == 'data')    echo 'active'; ?>"><?php echo HTML::anchor('reports/create/data', SGS::title('reports/create/data')); ?></li>
      </ul>
      <?php endif; ?>
    </li>

    <?php elseif ($primary == 'users'): ?>
    <li class="<?php if ($secondary == 'list')     echo 'active'; ?>"><?php echo HTML::anchor('users/list', SGS::title('users/list')); ?></li>
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
