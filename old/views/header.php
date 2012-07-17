<style>
  body {
    font-size: 12px;
    font-family: sans-serif;
  }
  fieldset {
    border: 2px solid #333;
  }
  fieldset legend {
    font-weight: bold;
  }
  fieldset, form {
    margin: 0;
  }
  div.submit {
    margin: 10px 0;
    padding: 5px;
    border: 2px solid #333;
  }
  .csv_results,
  .barcode_results {
    border: 2px solid #333;
    font-size: 12px;
    width: 100%;
  }
  .csv_results td,
  .barcode_results td {
    border: none;
    padding: 5px;
  }
  .barcode_results .found {
    border-top: 1px solid #9473c0;
    background-color: #e6d8ed;
  }
  .csv_results .accepted {
    border-top: 1px solid #99c494;
    background-color: #dde5c5;
  }
  .csv_results .accepted input {
    width: 100%;
  }
  .csv_results .rejected {
    border-top: 1px solid #f38284;
    background-color: #fcdfe0;
  }
  .csv_results .rejected input {
    width: 100%;
  }
  strong.accepted {
    margin: 10px 0 0;
    padding: 5px;
    display: block;
    border: 1px dotted #99c494;
    background-color: #dde5c5;
    color: #63922d;
  }
  strong.rejected {
    margin: 10px 0 0;
    padding: 5px;
    display: block;
    border: 1px dotted #f38284;
    background-color: #fcdfe0;
    color: #ae3636;
  }
  strong.found {
    margin: 10px 0 0;
    padding: 5px;
    display: block;
    border: 1px dotted #9473c0;
    background-color: #e6d8ed;
    color: #8c4faa;
  }
</style>
<h2>SGS Database</h2>
