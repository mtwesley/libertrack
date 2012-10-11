$(function() {

  $("body.import .toggle-details").click(function() {
    $(this).parent().parent("tr").next("tr.details").toggle();
    $(this).parent().parent("tr").next("tr.details").children("td.loading").load(
      '/ajax/details',
      {id: $(this).attr('id').match(/csv-(\d+)-details/)[1]},
      function() {
        $(this).removeClass('loading')
      }
    );
  });

  $("body.analysis .toggle-details").click(function() {
    $(this).parent().parent("tr").next("tr.details").toggle();
  });

  $(".toggle-download-form").click(function() {
    $(this).parent().parent("tr").next("tr.download-form").toggle();
  });

  $(".toggle-form").click(function() {
    $(this).next("form").toggle();
    $(this).css("display", "none");
  });

  $(".details-suggestion .suggest li").click(function() {
    $("input[name="+$(this).attr("class")+"]").val($(this).text());
  });

  $("body.import .csv-eip").editable("/ajax/csv", {
    cssclass: 'eip-form',
    event: 'dblclick',
    id: 'id',
    name: 'data',
    placeholder: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
  });

  $("#messages .message .close").click(function() {
    $(this).parent(".message").hide();
  })

  $("input.dpicker").glDatePicker({
//    cssName: "default",
//    startDate: -1,
//    endDate: -1,
//    selectedDate: -1,
//    showPrevNext: true,
//    allowOld: true,
//    showAlways: true,
    position: "relative",
    onChange: function(target, newDate) {
      target.val((newDate.getMonth() + 1)+'/'+newDate.getDate()+'/'+ newDate.getFullYear())}
  });

});

function update_csv(id) {
  var row = $("csv-"+id);
  row.attr("id", "csv-"+id+"-deleted");
  row.after();
}