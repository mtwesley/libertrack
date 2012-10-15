editableOptions = {
  cssclass: 'eip-form',
  event: 'dblclick',
  id: 'id',
  name: 'data',
  placeholder: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
  callback: function(value, settings) {
    update_csv($(this).attr('id').match(/csv-(\d+)/)[1]);
  }
};

$(function() {

  $("body.import .toggle-details").live('click', function() {
    $(this).parent().parent("tr").next("tr.details").toggle();
    $(this).parent().parent("tr").next("tr.details").children("td.loading").load(
      '/ajax/details',
      {id: $(this).attr('id').match(/csv-(\d+)-details/)[1]},
      function() {
        $(".details-suggestions-link").live('click', function() {
          $("#popup").addClass('popup-suggestions');
          $("#popup .popup-text").load('/ajax/suggestions', {id: $(this).attr('id')}, function() {
            $("#popup").bPopup({
              opacity: 0.8,
              closeClass: 'popup-close',
              modalColor: '#fff',
              onClose: function() {
                $("#popup").removeClass('popup-suggestions');
              }
            });
          });
        });
        $(this).removeClass('loading')
      }
    );
  });

  $("body.analysis .toggle-details").live('click', function() {
    $(this).parent().parent("tr").next("tr.details").toggle();
  });

  $(".toggle-download-form").live('click', function() {
    $(this).parent().parent("tr").next("tr.download-form").toggle();
  });

  $(".toggle-form").click(function() {
    $(this).next("form").toggle();
    $(this).css("display", "none");
  });

  $(".details-suggestion .suggest li").click(function() {
    $("input[name="+$(this).attr("class")+"]").val($(this).text());
  });

  $("body.import .csv-eip").editable("/ajax/csv", editableOptions);

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
  $("#csv-"+id).addClass('loading');
  $("#csv-"+id).addClass('loading-small');
  $("#csv-"+id).attr("id", "csv-"+id+"-deleted");
  $.post(
    "/ajax/update",
    {id: id},
    function(data) {
      $("#csv-"+id+"-deleted").replaceWith(data);
      $("#csv-"+id+" .csv-eip").editable("/ajax/csv", editableOptions);
    },
    "html"
  );
}