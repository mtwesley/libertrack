var editableOptions = {
  cssclass: 'eip-form',
  event: 'dblclick',
  id: 'id',
  name: 'data',
  placeholder: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
  callback: function(value, settings) {
    update_csv($(this).attr('id').match(/csv-(\d+)/)[1]);
  }
};

var bPopupOptions = {
  opacity: 0.8,
  closeClass: 'popup-close',
  modalColor: '#fff',
  onClose: function() {
    $("#popup .popup-text").text("");
  }
}

$(function() {

  $("body.import .toggle-details").live('click', function() {
    $(this).parent().parent("tr").next("tr.details").toggle();
    $(this).parent().parent("tr").next("tr.details").children("td.loading").load(
      '/ajax/details',
      {id: $(this).attr('id').match(/csv-(\d+)-details/)[1]},
      function() {
        $(".details-suggestions-link").live('click', function() {
          $("#popup").addClass("popup-loading").bPopup(bPopupOptions);
          $("#popup .popup-text").load('/ajax/suggestions', {id: $(this).attr('id')}, function() {
            $("#popup").removeClass("popup-loading").bPopup(bPopupOptions);
          });

          $("ul.suggest li").live('click', function() {
            var csv_id = $(this).parent("ul.suggest").attr('id').match(/csv-(\d+)/)[1];
            $.post(
              '/ajax/csv',
              {id: $(this).parent("ul.suggest").attr('id'), data: $(this).text(), process: 1},
              function() {
                $("#popup").bPopup().close();
                update_csv(csv_id);
              }
            );
          });
        });
        $(this).removeClass('loading');
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
      $("#csv-"+id).next("tr.details").hide();
      $("#csv-"+id).next("tr.details").children("td").text("").addClass('loading');
    },
    "html"
  );
}