<?php

$form = Model::factory($form_type);

$options = (array) $options + array(
  'header'    => TRUE,
  'footer'    => FALSE,
  'break'     => TRUE,
  'styles'    => FALSE,
  'info'      => FALSE,
  'summary'   => FALSE,
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

  .checks {
    padding: 20px 25px;
  }

  .checks-page-break {
    page-break-before: always;
  }

  .checks-header {}

  .checks-header,
  .checks-info,
  .checks-summary {
    width: 100%;
  }

  .checks-summary-table {
    margin-bottom: 10px;
  }

  .checks-summary-table,
  .checks-details-table,
  .checks-signature-table,
  .checks-info-table,
  .checks-header-table {
    width: 100%;
    border-collapse: collapse;
  }

  .checks-summary-table tr td {
    padding: 3px 5px;
    border: 1px solid #000;
  }

  .checks-details-table tr td {
    padding: 1px 3px;
    border: 1px solid #000;
  }

  .checks-summary-table tr.head td,
  .checks-details-table tr.head td {
    padding: 6px 5px;
    background-color: #cfcfcf;
    font-weight: bold;
  }

  .checks-details-table tr.head td {
    padding: 4px 3px;
  }

  .checks-summary-table tr td.blank-slim {
    border: none;
    font-size: 6px;
  }

  .checks-summary-table tr td.volume,
  .checks-summary-table tr td.species_code,
  .checks-summary-table tr td.species_class,
  .checks-summary-table tr td.fob_price,
  .checks-summary-table tr td.tax_code,
  .checks-summary-table tr td.total {
    text-align: center;
  }

  .checks-summary-table tr td.fee_desc,
  .checks-summary-table tr td.fee_desc em {
    font-size: 8px;
    white-space: nowrap;
  }

  .checks-summary-table tr.head td.fee_desc {
    font-size: 10px;
  }

  .checks-summary-table tr td.check-info {
    width: 10%;
  }

  .checks-summary-table tr td.check-desc {
    border-left: none;
  }

  .checks-summary-table tr td.status {
    width: 1px;
    padding-right: 0;
    padding-left: 3px;
    text-align: center;
    border-right: none;
  }

  .checks-details-table tr td.status {
    padding: 1px;
    height: 100%;
  }

  .checks-summary-table tr td.status .error,
  .checks-summary-table tr td.status .warning,
  .checks-summary-table tr td.status .success,
  .checks-details-table tr td.status .error,
  .checks-details-table tr td.status .warning,
  .checks-details-table tr td.status .success {
    margin: 0;
    padding: 2px 4px;
    width: 50px; 
    display: inline-block;
    /* border: 1px solid; */
    text-align: center;
    color: #fff;
    font-weight: bold;
    vertical-align: top;
  }

  .checks-details-table tr td.status .error,
  .checks-details-table tr td.status .warning,
  .checks-details-table tr td.status .success {
    padding: 2px;
    width: 10px;
    vertical-align: top;
  }

  .checks-summary-table tr td.status .error,
  .checks-details-table tr td.status .error {
    /* background-color: #fcdfe0; */
    background-color: #ea2528;
    /* color: #ae3636; */
  }

  .checks-summary-table tr td.status .warning,
  .checks-details-table tr td.status .warning {
    /* background-color: #f8f5e1; */
    background-color: #dccf64;
    /* color: #a5890b; */
  }

  .checks-summary-table tr td.status .success,
  .checks-details-table tr td.status .success {
    /* background-color: #dde5c5; */
    background-color: #4caa41;
    /* color: #63922d; */
  }

  .checks-details-table {}

  .checks-details-table tr td.check-type {
    padding: 5px 3px;
    text-align: center;
  }

  .checks-details-table tr td.traceability {
    padding: 5px 3px;
    text-align: left;
  }

  .checks-details-table tr td.title {
    text-align: left;
    font-size: 16px;
  }

  .checks-details-table tr td.value,
  .checks-details-table tr td.comparison,
  .checks-details-table tr td.status {
    text-align: center;
  }

  .checks-details-table tr td {
    white-space: nowrap;
  }

  .checks-details-table tr td.barcode {
    text-align: left;
  }

  .checks-details-table tr.even {
    background-color: #fafafa;
  }

  .checks-signature-table tr td {
    padding: 5px;
    border: 1px solid #000;
  }

  .checks-signature-table tr td.signature {
    width: 24%;
    height: 150px;
    vertical-align: top;
  }

  .checks-signature-table tr td.half-signature {
    height: 75px;
  }

  .checks-info-table {
    margin-bottom: 8px;
  }

  .checks-info-table tr td {
    padding: 4px 5px;
    vertical-align: top;
  }

  .checks-info-table tr td.label {
    font-weight: bold;
    width: 1px;
    white-space: nowrap;
  }

  .checks-titles {}

  .checks-title {
    margin: 2px 0 5px;
    font-size: 18px;
    text-align: center;
  }

  .checks-subtitle {
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

<div class="checks <?php if ($options['break']) echo 'checks-page-break'; ?>">
  <?php if ($options['header']): ?>
  <div class="checks-header">
    <table class="checks-header-table">
      <tr>
        <td class="liberfor-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_liberfor.jpg" /></td>
        <td class="fda-logo"><img src="<?php echo DOCROOT; ?>images/invoice/st_fda.jpg" /></td>
      </tr>
    </table>
    <div class="checks-title"><?php echo SGS::$form_data_type[$form_type]; ?> Check Report</div>
    <?php if ($options['subtitle']): ?>
    <div class="checks-subtitle"><?php echo $options['subtitle']; ?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <?php if ($options['info']): ?>
  <div class="checks-info">
    <table class="checks-info-table">
      <tr>
        <td class="label">Operator:</td>
        <td><?php if ($operator) echo $operator->name; ?></td>
        <td class="label">
          <?php
            if ($site) echo 'Site:';
            else if ($specs_info) echo 'Shipment Specification Barcode:';
            else if ($wb_info) echo 'Waybill Barcode:';
          ?>
        </td>
        <td>
          <?php
            if ($site) echo $site->name;
            else if ($specs_info) echo $specs_info['barcode'];
            else if ($wb_info) echo $wb_info['barcode'];
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
  <div class="checks-summary">
    <table class="<?php echo SGS::render_classes($classes); ?> checks-summary-table">
      <?php if ($checks): ?>
      <?php foreach ($checks as $type => $info): ?>
      <tr class="head">
        <td colspan="2"><?php print $info['title']; ?></td>
        <td class="check-info">Records</td>
        <td class="check-info">Checked</td>
        <td class="check-info">Passed</td>
        <td class="check-info">Warnings</td>
        <td class="check-info">Failed</td>
        <td class="check-info">Pass<br />Rate</td>
      </tr>
      <?php foreach ($info['checks'] as $check => $array): ?>
      <?php
        $records = $report['total']['records'] ?: 0;
        $checked = $report['checks'][$type][$check]['checked'] ?: 0;
        $passed  = $report['checks'][$type][$check]['passed'] ?: 0;
        $failed  = $report['checks'][$type][$check]['failed'] ?: 0;
        $warned  = $report['checks'][$type][$check]['warned'] ?: 0;
        $percentage = $checked ? floor($passed * 100 / $checked) : 100;
        
        $records_volume = $report['total_volume']['records'] ?: 0;
        $checked_volume = $report['volume'][$type][$check]['checked'] ?: 0;
        $passed_volume  = $report['volume'][$type][$check]['passed'] ?: 0;
        $failed_volume  = $report['volume'][$type][$check]['failed'] ?: 0;
        $warned_volume  = $report['volume'][$type][$check]['warned'] ?: 0;
        $percentage_volume = $checked_volume ? floor($passed_volume * 100 / $checked_volume) : 100;
      ?>
      <tr>
        <td class="status">
          <?php if ($percentage < 100): ?>
          <div class="error">Failed</div>

          <?php elseif ($warned): ?>
          <div class="warning">Warned</div>

          <?php else: ?>
          <div class="success">Passed</div>
          <?php endif; ?>
        </td>
        <td class="check-desc"><?php print $array['title']; ?></td>
        <td class="check-info"><?php print $records . " ($records_volume m<sup>3</sup>)"; ?></td>
        <td class="check-info"><?php print $checked . " ($checked_volume m<sup>3</sup>)"; ?></td>
        <td class="check-info"><span class="accepted"><?php print $passed . " ($passed_volume m<sup>3</sup>)"; ?></span></td>
        <td class="check-info"><span class="pending"><?php print $warned . " ($warned_volume m<sup>3</sup>)"; ?></span></td>
        <td class="check-info"><span class="rejected"><?php print $failed . " ($failed_volume m<sup>3</sup>)"; ?></span></td>
        <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
      </tr>
      <?php endforeach; ?>
      <tr>
        <td colspan="8" class="blank-slim">&nbsp;</td>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
      <tr class="head">
        <td colspan="2">Summary</td>
        <td class="check-info">Records</td>
        <td class="check-info">Checked</td>
        <td class="check-info">Passed</td>
        <td class="check-info">Warnings</td>
        <td class="check-info">Failed</td>
        <td class="check-info">Pass<br />Rate</td>
      </tr>
      <?php
        $records = $report['total']['records'] ?: 0;
        $checked = $report['total']['checked'] ?: 0;
        $passed  = $report['total']['passed'] ?: 0;
        $failed  = $report['total']['failed'] ?: 0;
        $warned  = $report['total']['warned'] ?: 0;
        $percentage = $checked ? floor($passed * 100 / $checked) : 100;

        $records_volume = $report['total_volume']['records'] ?: 0;
        $checked_volume = $report['total_volume']['checked'] ?: 0;
        $passed_volume  = $report['total_volume']['passed'] ?: 0;
        $failed_volume  = $report['total_volume']['failed'] ?: 0;
        $warned_volume  = $report['total_volume']['warned'] ?: 0;
        $percentage_volume = $checked_volume ? floor($passed_volume * 100 / $checked_volume) : 100;
      ?>
      <tr>
        <td class="status">
          <?php if ($percentage < 100): ?>
          <div class="error">Failed</div>

          <?php elseif ($warned): ?>
          <div class="warning">Warned</div>

          <?php else: ?>
          <div class="success">Passed</div>
          <?php endif; ?>
        </td>
        <td class="check-desc">Total</td>
        <td class="check-info"><?php print $records . " ($records_volume m<sup>3</sup>)"; ?></td>
        <td class="check-info"><?php print $checked . " ($checked_volume m<sup>3</sup>)"; ?></td>
        <td class="check-info"><span class="accepted"><?php print $passed . " ($passed_volume m<sup>3</sup>)"; ?></span></td>
        <td class="check-info"><span class="pending"><?php print $warned . " ($warned_volume m<sup>3</sup>)"; ?></span></td>
        <td class="check-info"><span class="rejected"><?php print $failed . " ($failed_volume m<sup>3</sup>)"; ?></span></td>
        <td class="check-info"><span class="<?php print $percentage > 50 ? 'accepted' : 'rejected'; ?>"><?php print $percentage; ?>%</span></td>
      </tr>
    </table>
  </div>
  <?php endif; ?>

  <?php if ($options['details']): ?>
  <div class="checks-details">
    <table class="checks-details-table">
      <?php if ($data): ?>
      <?php
        $chks = $checks;
        unset($chks['traceability']);
      ?>
      <tr class="head">
        <td class="check-type traceability" colspan="3">Traceability</td>
        <?php if ($chks) foreach ($chks as $chk) foreach ($chk['checks'] as $ck): ?>
        <td class="check-type" colspan="3"><?php echo $ck['name']; ?></td>
        <?php endforeach; ?>
        <?php if ($form_type == 'LDF'): ?>
        <td class="value" rowspan="2">Stumpage Paid</td>
        <td class="value" rowspan="2">Export Permit</td>
        <?php endif; ?>
      </tr>
      <tr class="head">
        <?php if ($form_type == 'SSF'): ?>
        <td class="barcode" colspan="3">Tree Barcode</td>
        <?php elseif ($form_type == 'TDF'): ?>
        <td class="barcode" colspan="2">Felled Tree Barcode</td>
        <td class="barcode">Tree Barcode</td>
        <?php elseif ($form_type == 'LDF'): ?>
        <td class="barcode" colspan="2">Barcode</td>
        <td class="barcode">Parent Barcode</td>
        <?php elseif ($form_type == 'SPECS'): ?>
        <td class="barcode" colspan="2">Barcode</td>
        <td class="barcode">Log Barcode</td>
        <?php endif; ?>
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
      <tr class="<?php print $odd_even = SGS::odd_even($odd); ?>">
        <td class="status" rowspan="2">
          <?php if (in_array('is_valid_parent', array_keys($errors))): ?>
          <div class="error">F</div>

          <?php elseif (in_array('is_valid_parent', array_keys($warnings))): ?>
          <div class="warning">W</div>

          <?php else: ?>
          <div class="success">P</div>
          <?php endif; ?>
        </td>
        <?php if ($form_type == 'SSF'): ?>
        <td class="barcode value" colspan="2"><?php echo $record->barcode->barcode; ?></td>
        <?php else: ?>
        <td class="barcode value"><?php echo $record->barcode->barcode; ?></td>
        <td class="barcode comparison">
          <?php
            if ($form_type == 'SSF') echo 'N/A';
            else if ($form_type == 'TDF') echo $record->tree_barcode->barcode;
            else if ($form_type == 'LDF') echo $record->parent_barcode->barcode;
            else if ($form_type == 'SPECS') echo $record->barcode->barcode;
          ?>
        </td>
        <?php endif; ?>
        <?php if ($chks) foreach ($chks as $chk) foreach ($chk['checks'] as $kck => $ck): ?>
        <td class="status" rowspan="2">
          <?php if (!in_array($kck, array_keys($errors + $warnings + $successes))): $sts = 'U'; ?>
          <div></div>

          <?php elseif (in_array($kck, array_keys($errors))): $sts = 'E'; ?>
          <div class="error">F</div>

          <?php elseif (in_array($kck, array_keys($warnings))): $sts = 'W'; ?>
          <div class="warning">W</div>

          <?php else: $sts = 'S'; ?>
          <div class="success">P</div>
          <?php endif; ?>
        </td>
        <td class="value"><?php echo $errors[$kck]['value'] ?: $warnings[$kck]['value'] ?: $successes[$kck]['value'] ?: ''; ?> </td>
        <td class="comparison"><?php echo $errors[$kck]['comparison'] ?: $warnings[$kck]['comparison'] ?: $successes[$kck]['comparison'] ?: ''; ?> </td>
        <?php endforeach; ?>
        <?php if ($form_type == 'LDF'): ?>
        <td class="value" rowspan="2">
          <?php if ($record->is_invoiced('ST', TRUE)): ?>
          Paid
          <?php elseif ($record->is_invoiced('ST')): ?>
          Not paid
          <?php else: ?>
          Not invoiced
          <?php endif; ?>
        </td>
        <td class="value" rowspan="2">
          <?php if ($record->barcode->get_activity('E') and 
              $number = DB::select('documents.number')
                ->from('specs_data')
                ->join('document_data')
                ->on('specs_data.id', '=', 'document_data.form_data_id')
                ->on('document_data.form_type', '=', DB::expr("'SPECS'"))
                ->join('documents')
                ->on('document_data.document_id', '=', 'documents.id')
                ->where('documents.type', '=', 'EXP')
                ->execute()
                ->get('number')): ?>
          <?php print 'EP ' . SGS::numberify($number); ?>
          <?php else: ?>
          Not exported
          <?php endif; ?>
        </td>
        <?php endif; ?>
      </tr>
      <tr class="<?php print $odd_even; ?>">
        <td style="white-space: normal;" colspan="2">
          <?php if (in_array('is_existing_parent', array_keys($errors))): ?>
          <div><?php echo $form::$checks['traceability']['checks']['is_existing_parent']['error'] ?></div>

          <?php elseif (in_array('is_valid_parent', array_keys($errors))): ?>
          <div><?php echo $form::$checks['traceability']['checks']['is_valid_parent']['error'] ?></div>

          <?php elseif (in_array('is_valid_parent', array_keys($warnings))): ?>
          <div><?php echo $form::$checks['traceability']['checks']['is_valid_parent']['warning'] ?></div>

          <?php else: ?>
          <div><?php echo $form::$checks['traceability']['checks']['is_existing_parent']['title'] ?></div>
          <?php endif; ?>
        </td>
        <?php if ($chks) foreach ($chks as $chk) foreach ($chk['checks'] as $kck => $ck): ?>
        <td style="white-space: normal;" colspan="2">
          <?php if (!in_array($kck, array_keys($errors + $warnings + $successes))): ?>
          <div></div>

          <?php elseif (in_array($kck, array_keys($errors))): ?>
          <div class="error">
            <?php 
              foreach ($form::$checks as $_type => $_array) 
                foreach ($_array['checks'] as $_check => $_check_array) 
                  if ($_check == $kck) echo $_check_array['error'] 
            ?>
          </div>

          <?php elseif (in_array($kck, array_keys($warnings))): ?>
          <div class="warning">
            <?php 
              foreach ($form::$checks as $_type => $_array) 
                foreach ($_array['checks'] as $_check => $_check_array) 
                  if ($_check == $kck) echo $_check_array['warning'] 
            ?>
          </div>

          <?php else: ?>
          <div class="success">
            <?php 
              foreach ($form::$checks as $_type => $_array) 
                foreach ($_array['checks'] as $_check => $_check_array) 
                  if ($_check == $kck) echo $_check_array['title'] 
            ?>
          </div>
          <?php endif; ?>
        </td>
        <?php endforeach; ?>
      </tr>
      <?php endforeach; ?>
      <?php endif; ?>
    </table>
  </div>
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

    .checks-footer {
      margin: 0 25px;
    }

    .checks-footer-table {
      margin-top: 0;
      width: 100%;
      border-collapse: collapse;
    }

    .checks-footer-table tr td.date,
    .checks-footer-table tr td.info,
    .checks-footer-table tr td.pageinfo {
      vertical-align: bottom;
      white-space: nowrap;
    }

    .checks-footer-table tr td.date,
    .checks-footer-table tr td.pageinfo {
      width: 120px;
    }

    .checks-footer-table tr td.date {
      text-align: left;
    }

    .checks-footer-table tr td.info {
      text-align: center;
    }

    .checks-footer-table tr td.pageinfo {
      text-align: right;
      position: relative;
    }

    .checks-footer-table tr td.pageinfo .ref {
      margin-bottom: 12px;
    }
  </style>
  <div class="checks-footer">
    <table class="checks-footer-table">
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