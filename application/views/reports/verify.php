<?php

$options = (array) $options + array(
  'header'    => TRUE,
  'footer'    => FALSE,
  'break'     => TRUE,
  'styles'    => FALSE,
  'info'      => FALSE,
  'summary'   => FALSE,
  'summary_total' => FALSE,
  'details'   => FALSE,
  'format'    => 'pdf'
);

if ($site and !$operator) $operator = $site->operator;
if ($block and !$site) $site = $block->site;

$num = $cntr;

?>
<?php if ($options['styles']): ?>
<style type="text/css">

  * {
    font-family: "Arial";
    font-size: 10px;
  }

  body, html {
    margin: 0;
  }

  table {
    border-collapse: collapse;
  }

  img.floater {
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0.5;
  }

  table tr td.blank {
    border: none;
  }

  .liberfor-logo {
    text-align: left;
  }

  .liberfor-logo img {
    height: 32px;
  }

  .fda-logo {
    text-align: right;
  }

  .fda-logo img {
    height: 32px;
  }

  .verify {
    padding: 20px 25px;
  }

  .verify-page-break {
    page-break-before: always;
  }

  .verify-header {}

  .verify-header,
  .verify-info,
  .verify-summary {
    width: 100%;
  }

  .verify-summary-table {
    margin-bottom: 10px;
  }

  .verify-summary-table,
  .verify-details-table,
  .verify-signature-table,
  .verify-info-table,
  .verify-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .verify-summary-table tr td {
    padding: 3px 5px;
    border: 1px solid #000;
  }

  .verify-details-table tr td {
    padding: 1px 3px;
    border: 1px solid #000;
  }

  .verify-summary-table tr.head td,
  .verify-details-table tr.head td {
    padding: 6px 5px;
    background-color: #cfcfcf;
    font-weight: bold;
  }

  .verify-details-table tr.head td {
    padding: 4px 3px;
  }

  .verify-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .verify-summary-table tr td.volume,
  .verify-summary-table tr td.species_code,
  .verify-summary-table tr td.species_class,
  .verify-summary-table tr td.fob_price,
  .verify-summary-table tr td.tax_code,
  .verify-summary-table tr td.total {
    text-align: center;
  }

  .verify-summary-table tr td.fee_desc,
  .verify-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .verify-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .verify-summary-table tr td.check-info {
    width: 10%;
  }

  .verify-summary-table tr td.check-desc {
    border-left: none;
  }

  .verify-summary-table tr td.status {
    width: 1px;
    padding-right: 0;
    padding-left: 3px;
    text-align: center;
    border-right: none;
  }

  .verify-details-table tr td.status {
    padding: 1px;
  }

  .verify-summary-table tr td.status .error,
  .verify-summary-table tr td.status .warning,
  .verify-summary-table tr td.status .success,
  .verify-details-table tr td.status .error,
  .verify-details-table tr td.status .warning,
  .verify-details-table tr td.status .success {
    margin: 0;
    padding: 2px 4px;
    width: 50px;
    display: inline-block;
    /* border: 1px solid; */
    text-align: center;
    color: #fff;
    font-weight: bold;
  }

  .verify-details-table tr td.status .error,
  .verify-details-table tr td.status .warning,
  .verify-details-table tr td.status .success {
    padding: 2px;
    width: 10px;
  }

  .verify-summary-table tr td.status .error,
  .verify-details-table tr td.status .error {
    /* background-color: #fcdfe0; */
    background-color: #ea2528;
    /* color: #ae3636; */
  }

  .verify-summary-table tr td.status .warning,
  .verify-details-table tr td.status .warning {
    /* background-color: #f8f5e1; */
    background-color: #dccf64;
    /* color: #a5890b; */
  }

  .verify-summary-table tr td.status .success,
  .verify-details-table tr td.status .success {
    /* background-color: #dde5c5; */
    background-color: #4caa41;
    /* color: #63922d; */
  }

  .verify-details-table {}

  .verify-details-table tr td.check-type {
    padding: 5px 3px;
    text-align: center;
  }

  .verify-details-table tr td.inspection {
    padding: 5px 3px;
    text-align: left;
  }

  .verify-details-table tr td.title {
    text-align: left;
    font-size: 16px;
  }

  .verify-details-table tr td.value,
  .verify-details-table tr td.comparison,
  .verify-details-table tr td.status {
    text-align: center;
  }

  .verify-details-table tr td {
    white-space: nowrap;
  }

  .verify-details-table tr td.barcode {
    text-align: left;
  }

  .verify-details-table tr.even {
    background-color: #fafafa;
  }

  .verify-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .verify-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .verify-signature-table tr td.half-signature {
    height: 75px;
  }

  .verify-info-table {
    margin-bottom: 8px;
  }

  .verify-info-table tr td {
    padding: 4px 5px;
    vertical-align: top;
  }

  .verify-info-table tr td.label {
    font-weight: bold;
    width: 1px;
    white-space: nowrap;
  }

  .verify-titles {}

  .verify-title {
    margin: 2px 0 5px;
    font-size: 18px;
    text-align: center;
  }

  .verify-subtitle {
    margin-bottom: 8px;
    text-align: center;
    text-transform: uppercase;
  }

  .payment-date {
    margin-bottom: 15px;
  }

  .payment-message {
    margin: -5px auto 15px;
    padding: 5px 10px;
    width: 75%;
    font-style: italic;
    text-align: center;
    border: 1px solid #000;
  }

</style>
<?php endif; ?>

<div class="verify <?php if ($options['break']) echo 'verify-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="verify-header">
    <table class="verify-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="verify-title"><?php echo SGS::$form_verification_type[$form_type]; ?> Check Report</div>
    <?php if ($options['subtitle']): ?>
    <div class="verify-subtitle"><?php echo $options['subtitle']; ?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="verify-info">
    <table class="verify-info-table">
      <tr>
        <td class="label">Operator:</td>
        <td><?php if ($operator) echo $operator->name; ?></td>
        <td class="label">
          <?php
            if ($site) echo 'Site:';
            else if ($specs_info) echo 'Shipment Specification Barcode:';
          ?>
        </td>
        <td>
          <?php
            if ($site) echo $site->name;
            else if ($specs_info) echo $specs_info['barcode'];
          ?>
        </td>
      </tr>
      <tr>
        <td class="label">TIN:</td>
        <td><?php if ($operator) echo $operator->tin; ?></td>
        <td class="label"><?php if ($block) echo 'Block:'; ?></td>
        <td><?php if ($block) echo $block->name; ?></td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['summary']): ?>
  <div class="verify-summary">
    <?php
      $model = ORM::factory($form_type);

      $total_records    = $report['total']['records'];
      $total_declared   = $report['total']['declared'];
      $total_inspected  = $report['total']['inspected'];
      $total_verified   = $report['total']['verified'];
      $total_unverified = $report['total']['unverified'];
      $total_accurate   = $report['total']['accurate'];
      $total_inaccurate = $report['total']['inaccurate'];

      $declaration_percentage  = $total_accurate ? floor($total_accurate * 100 / $total_inspected) : 100;
      $verification_percentage = $total_declared ? floor($total_records * 100 / ($total_declared + $total_inspected - $total_records)) : 100;
    ?>

    <?php if ($info = $checks[$type = 'declaration']): ?>
    <table class="<?php echo SGS::render_classes($classes); ?> verify-summary-table">
      <tr class="head">
        <td colspan="2">Declaration Performance</td>
        <td class="check-info">Inspected</td>
        <td class="check-info">Found</td>
        <td class="check-info">Verified</td>
        <td class="check-info">Accurate</td>
        <td class="check-info">Accuracy Rate</td>
      </tr>
      <?php foreach ($info['checks'] as $check => $array): ?>
      <tr>
        <td class="status">
          <?php if ($declaration_percentage < 80): ?>
          <div class="error">Inaccurate</div>
          <?php else: ?>
          <div class="success">Accurate</div>
          <?php endif; ?>
        </td>
        <td class="check-desc"><?php print $array['title']; ?></td>
        <td class="check-info"><?php print $total_inspected; ?></td>
        <td class="check-info"><?php print $total_records; ?></td>
        <td class="check-info"><?php print $total_verified; ?></td>
        <td class="check-info"><?php print $total_accurate; ?></td>
        <td class="check-info"><span class="<?php print $declaration_percentage >= 80 ? 'accepted' : 'rejected'; ?>"><?php print $declaration_percentage; ?>%</span></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($info = $checks[$type = 'verification']): ?>
    <table class="<?php echo SGS::render_classes($classes); ?> verify-summary-table">
      <tr class="head">
        <td colspan="2">Verification Performance</td>
        <td class="check-info">Declared</td>
        <td class="check-info">Inspected</td>
        <td class="check-info">Found</td>
        <td class="check-info">Not Found</td>
        <td class="check-info">Inspection Rate</td>
      </tr>
      <?php foreach ($info['checks'] as $check => $array): ?>
      <tr>
        <td class="status">
          <?php if ($verification_percentage < $model::$target_percentage): ?>
          <div class="error">Inaccurate</div>
          <?php else: ?>
          <div class="success">Accurate</div>
          <?php endif; ?>
        </td>
        <td class="check-desc"><?php print $array['title']; ?></td>
        <td class="check-info"><?php print $total_declared; ?></td>
        <td class="check-info"><?php print $total_inspected; ?></td>
        <td class="check-info"><?php print $total_records; ?></td>
        <td class="check-info"><?php print $total_inspected - $total_records; ?></td>
        <td class="check-info"><span class="<?php print $verification_percentage >= $model::$target_percentage ? 'accepted' : 'rejected'; ?>"><?php print $verification_percentage; ?>%</span></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($info = $checks[$type = 'variance']): ?>
    <table class="<?php echo SGS::render_classes($classes); ?> verify-summary-table">
      <tr class="head">
        <td colspan="4">Variance</td>
        <td class="check-info">Declared</td>
        <td class="check-info">Inspected</td>
        <td class="check-info">Difference</td>
      </tr>
      <?php foreach ($info['checks'] as $check => $array): ?>
      <?php
        $field = str_replace('is_valid_', '', $check);

        $dtotal = $report['variance'][$field]['data']['total'];
        $dcount = $report['variance'][$field]['data']['count'];
        $itotal = $report['variance'][$field]['verification']['total'];
        $icount = $report['variance'][$field]['verification']['count'];

        switch ($field) {
          case 'diameter':
            $declared  = SGS::floatify($dcount ? ($dtotal / $dcount) : 0);
            $inspected = SGS::floatify($icount ? ($itotal / $icount) : 0);
            $difference = SGS::floatify(abs($declared - $inspected));
            break;

          case 'length':
            $declared  = SGS::amountify($dcount ? ($dtotal / $dcount) : 0);
            $inspected = SGS::amountify($icount ? ($itotal / $icount) : 0);
            $difference = SGS::amountify(abs($declared - $inspected));
            break;

          case 'volume':
            $declared  = SGS::quantitify($dtotal);
            $inspected = SGS::quantitify($itotal);
            $difference = SGS::quantitify(abs($declared - $inspected));
            break;
        }
      ?>
      <tr>
        <td class="status">
          <?php if (($inspected ? ($difference / $inspected) : 0) > 0.1): ?>
          <div class="warning">Warned</div>

          <?php else: ?>
          <div class="success">Accurate</div>
          <?php endif; ?>
        </td>
        <td class="check-desc" colspan="3"><?php print $array['title']; ?></td>
        <td class="check-info"><?php print $declared; ?></td>
        <td class="check-info"><?php print $inspected; ?></td>
        <td class="check-info"><span class="<?php print ($inspected ? ($difference / $inspected) : 0) <= 0.1 ? 'accepted' : 'rejected'; ?>"><?php print $difference; ?></span></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endif; ?>

    <?php if ($checks) foreach ($checks as $type => $info): ?>
    <?php if (in_array($type, array('verification', 'declaration', 'variance'))) continue; ?>
    <table class="<?php echo SGS::render_classes($classes); ?> verify-summary-table">
      <tr class="head">
        <td colspan="2"><?php print $info['title']; ?></td>
        <td class="check-info">Inspected</td>
        <td class="check-info">Verified</td>
        <td class="check-info">Correct</td>
        <td class="check-info">Incorrect</td>
        <td class="check-info">Correctness Rate</td>
      </tr>
      <?php foreach ($info['checks'] as $check => $array): ?>
      <?php
        $records    = $report['checks'][$type][$check]['records'] ?: 0;
        $accurate   = $report['checks'][$type][$check]['accurate'] ?: 0;
        $inaccurate = $report['checks'][$type][$check]['inaccurate'] ?: 0;
        $percentage = $total_inspected ? floor($accurate * 100 / $total_inspected) : 100;
      ?>
      <tr>
        <td class="status">
          <?php if ($percentage < 100): ?>
          <div class="error">Inaccurate</div>
          <?php else: ?>
          <div class="success">Accurate</div>
          <?php endif; ?>
        </td>
        <td class="check-desc"><?php print $array['title']; ?></td>
        <td class="check-info"><?php print $total_inspected; ?></td>
        <td class="check-info"><?php print $records; ?></td>
        <td class="check-info"><span class="accepted"><?php print $accurate; ?></span></td>
        <td class="check-info"><span class="rejected"><?php print $inaccurate; ?></span></td>
        <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <?php endforeach; ?>

    <?php if ($options['summary_total']): ?>
    <table class="<?php echo SGS::render_classes($classes); ?> verify-summary-table">
      <tr class="head">
        <td colspan="2">Total</td>
        <td class="check-info">Inspected</td>
        <td class="check-info">Verified</td>
        <td class="check-info">Correct</td>
        <td class="check-info">Incorrect</td>
        <td class="check-info">Correctness Rate</td>
      </tr>
      <?php
        $records = $report['total']['records'] ?: 0;
        $accurate  = $report['total']['accurate'] ?: 0;
        $inaccurate  = $report['total']['inaccurate'] ?: 0;
        $percentage = $verified ? floor($accurate * 100 / $records) : 100;
      ?>
      <tr>
        <td class="status">
          <?php if ($percentage < 5): ?>
          <div class="error">Inaccurate</div>
          <?php else: ?>
          <div class="success">Accurate</div>
          <?php endif; ?>
        </td>
        <td class="check-desc">Total</td>
        <td class="check-info"><?php print $total_inspected; ?></td>
        <td class="check-info"><?php print $records; ?></td>
        <td class="check-info"><span class="accepted"><?php print $accurate; ?></span></td>
        <td class="check-info"><span class="rejected"><?php print $inaccurate; ?></span></td>
        <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
      </tr>
    </table>
    <?php endif; ?>

  </div>
  <?php endif; ?>

  <?php if ($options['details']): ?>
  <div class="verify-details">
    <table class="verify-details-table">
      <?php if ($data): ?>
      <?php
        $chks = $checks;
        unset($chks['verification']);
        unset($chks['declaration']);
        unset($chks['variance']);
      ?>
      <tr class="head">
        <td class="check-type inspection" colspan="3">Inspection</td>
        <?php if ($chks) foreach ($chks as $chk) foreach ($chk['checks'] as $ck): ?>
        <td class="check-type" colspan="3"><?php echo $ck['name']; ?></td>
        <?php endforeach; ?>
      </tr>
      <tr class="head">
        <td class="barcode" colspan="2">Verification Barcode</td>
        <td class="barcode">Declaration Barcode</td>
        <?php if ($chks) foreach ($chks as $chk) foreach ($chk['checks'] as $kck => $ck): ?>
        <td class="value" colspan="2">Value</td>
        <td class="comparison">Comp</td>
        <?php endforeach; ?>
      </tr>
      <?php foreach ($data as $record): ?>
      <?php
        $errors    = $record->get_errors(TRUE, FALSE, FALSE);
        $warnings  = $record->get_warnings(TRUE, FALSE, FALSE);
        $successes = $record->get_successes(TRUE, FALSE, FALSE);
      ?>
      <tr class="<?php print SGS::odd_even($odd); ?>">
        <td class="status">
          <?php if (in_array('is_existing_data', array_keys($errors)) or in_array('is_existing_verification', array_keys($errors))): ?>
          <div class="error">F</div>

          <?php elseif (in_array('is_existing_data', array_keys($warnings)) or in_array('is_existing_verification', array_keys($warnings))): ?>
          <div class="warning">W</div>

          <?php else: ?>
          <div class="success">P</div>
          <?php endif; ?>
        </td>
        <td class="barcode value"><?php echo $record->verification()->barcode->barcode; ?></td>
        <td class="barcode comparison"><?php echo $record->data()->barcode->barcode; ?></td>
        <?php if ($chks) foreach ($chks as $chk) foreach ($chk['checks'] as $kck => $ck): ?>
        <td class="status">
          <?php if (in_array($kck, array_keys($errors))): ?>
          <div class="error">F</div>

          <?php elseif (in_array($kck, array_keys($warnings))): ?>
          <div class="warning">W</div>

          <?php else: ?>
          <div class="success">P</div>
          <?php endif; ?>
        </td>
        <td class="value"><?php echo $errors[$kck]['value'] ?: $warnings[$kck]['value'] ?: $successes[$kck]['value'] ?: ' - '; ?> </td>
        <td class="comparison"><?php echo $errors[$kck]['comparison'] ?: $warnings[$kck]['comparison'] ?: $successes[$kck]['comparison'] ?: ' - '; ?> </td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
    </table>
  </div>
  <?php endif; ?>
  <?php endif; ?>

  <?php if ($options['footer']): ?>
  <script>
  window.onload = function() {
    var vars = {};
    var x = document.location.search.substring(1).split('&');
    for (var i in x) {
      var z = x[i].split('=',2);
      vars[z[0]] = unescape(z[1]);
    }

    var x = ['frompage', 'topage', 'page', 'webpage', 'section', 'subsection', 'subsubsection'];
    for (var i in x) {
      var y = document.getElementsByClassName(x[i]);
      for (var j = 0; j<y.length; ++j) y[j].textContent = vars[x[i]];
    }
  }
  </script>
  <style>
    * {
      font-family: "Arial";
      font-size: 13px;
    }

    img.liberfor-bw {
      height: 22px;
    }

    img.sgs-bw {
      height: 22px;
    }

    img.fda-bw {
      height: 22px;
    }

    .verify-footer {
      margin: 0 25px;
    }

    .verify-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .verify-footer-table tr td.date,
    .verify-footer-table tr td.info,
    .verify-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .verify-footer-table tr td.date,
    .verify-footer-table tr td.pageinfo {
      width: 120px;
    }

    .verify-footer-table tr td.date {
      text-align: left;
    }

    .verify-footer-table tr td.info {
      text-align: center;
    }

    .verify-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .verify-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="verify-footer">
    <table class="verify-footer-table">
      <tr>
        <td class="date"><?php echo SGS::date('now', SGS::PRETTY_DATE_FORMAT); ?></td>
        <td class="info">
          <img class="liberfor-bw" src="<?php echo DOCROOT; ?>images/invoice/st_liberfor_bw.jpg" /> &nbsp; is operated by &nbsp; <img class="sgs-bw" src="<?php echo DOCROOT; ?>images/invoice/st_sgs.jpg" /> &nbsp; Liberia on the behalf of &nbsp; <img class="fda-bw" src="<?php echo DOCROOT; ?>images/invoice/st_fda_small.jpg" /><br />
          LiberFor, SGS Compound, Old Road, Sinkor, Monrovia, Liberia
        </td>
        <td class="pageinfo">
          <div class="ref"></div>
          Page <span class="page"><?php echo $page; ?></span> of <span class="topage"><?php echo $page_count; ?></span>
        </td>
      </tr>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($info['is_draft']): ?>
<!-- <img class="floater" src="<?php // echo DOCROOT; ?>images/invoice/draft_copy.png" /> -->
<?php endif; ?>