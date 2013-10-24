<style>
  #content .report-wrapper {
    overflow-x: auto;
    white-space: nowrap;
  }

  #content table.report {
    margin: 5px 0;
    width: 100%;
    table-layout: auto;
    border: 1px solid #eee;
    overflow-x: auto;
    white-space: nowrap;
  }

  #content table.report tr {}

  #content table.report tr {}

  #content table.report tr.odd {
    background-color: #f8f8f8;
  }

  #content table.report tr.even {
    background-color: #f4f4f4;
  }

  #content table.report tr.head {
    background-color: #efefef;
  }

  #content table.report tr.head a,
  #content table.report tr.head a:visited {
    color: #333;
    text-decoration: none;
  }

  #content table.report tr.head a:hover {
    text-decoration: underline;
  }

  #content table.report tr:hover,
  #content table.report tr.odd:hover,
  #content table.report tr.even:hover {
    background-color: #fefff0;
    cursor: pointer;
  }

  #content table.report tr.head:hover {
    background-color: #eee;
  }

  #content table.report tr.details:hover,
  #content table.report tr.odd.details:hover,
  #content table.report tr.even.details:hover,
  #content table.report tr.odd.download-form:hover,
  #content table.report tr.even.download-form:hover {
    background-color: #fefff0;
    cursor: pointer;
  }

  #content table.report td:hover {
    background-color: #feffef !important;
  }

  #content table.report tr.details,
  #content table.report tr.download-form {
    display: none;
  }

  #content table.report th,
  #content table.report td {
    padding: 2px 6px;
    text-align: left;
    height: 18px;
  }

  #content table.report td.links {
    text-align: right;
    position: relative;
  }

  #content table.report th {
    padding: 3px 6px;
    vertical-align: bottom;
    border-bottom: 2px solid #e8e8e8;
  }

  #content table.report td {
    white-space: nowrap;
  }

  #content table.report td.wrap-normal {
    white-space: normal;
  }

  .subnav {
    margin: 5px 0 0;
    border: 1px solid #bcc6aa;
    background-color: #ecefe7;
    padding: 4px 6px;
    color: #333;
    text-align: center;
  }

  .subnav a,
  .subnav a:visited {
    color: #333;
    text-decoration: none;
  }

  .subnav a:hover {
    text-decoration: underline;
  }

  .subnav a.active {
    font-weight: bold;
  }
</style>
<div class="subnav">
  <span><strong>Download Report: </strong></span>
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/csv'; ?>">CSV</a> |
  <a href="<?php echo '/reports/create/'.strtolower($report_type).'/xls'; ?>">Excel</a>
</div>
<div class="report-wrapper">
  <table class="report">
    <tr class="head">
      <?php foreach ($headers as $name): ?>
      <th class="<?php print SGS::odd_even($odd2); ?>"><?php echo $name; ?></th>
      <?php endforeach; ?>
    </tr>
    <?php foreach ($results as $result): $odd2 = NULL; ?>
    <tr class="<?php print SGS::odd_even($odd); ?>">
      <?php foreach ($result as $data): ?>
      <td class="<?php print SGS::odd_even($odd2); ?>"><?php echo $data; ?></td>
      <?php endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </table>
</div>