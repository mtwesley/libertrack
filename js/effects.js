var csvEditableOptions = {
  cssclass: 'eip-form',
  event: 'dblclick',
  id: 'id',
  name: 'data',
  placeholder: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
  callback: function(value, settings) {
    update_csv($(this).attr('id').match(/csv-(\d+)/)[1]);
  }
};

var dataEditableOptions = {
  cssclass: 'eip-form',
  event: 'dblclick',
  id: 'id',
  name: 'data',
  placeholder: '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;',
  callback: function(value, settings) {
    var match = $(this).attr('id').match(/(\w+)-(\d+)/);
    update_data(match[1], match[2]);
  }
};

var bPopupOptions = {
  opacity: 0.9,
  closeClass: 'popup-close',
  modalColor: '#fff',
  fadeSpeed: 100,
  follow: [true, false],
  amsl: 150,
  onClose: function() {
    $("#popup .popup-text").text("");
  }
}

$(function() {
  $(".toggle-details").live('click', function() {
    $(this).parent().parent().parent().parent("tr").next("tr.details").toggle();
  });

  $(".details-tips-link").live('click', function() {
    $("#popup").addClass("popup-loading").bPopup(bPopupOptions);
    $("#popup .popup-text").load('/ajax/tips', {id: $(this).attr('id')}, function() {
      $("#popup").removeClass("popup-loading").bPopup(bPopupOptions);
    });
  });

  $(".details-suggestions-link").live('click', function() {
    $("#popup").addClass("popup-loading").bPopup(bPopupOptions);
    $("#popup .popup-text").load('/ajax/suggestions', {id: $(this).attr('id')}, function() {
      $("#popup").removeClass("popup-loading").bPopup(bPopupOptions);
      $("ul.suggest li").click(function() {
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
  });

  $(".details-resolutions-link").live('click', function() {
    $("#popup").addClass("popup-loading").bPopup(bPopupOptions);
    $("#popup .popup-text").load('/ajax/resolutions', {id: $(this).attr('id')}, function() {
      $("#popup").removeClass("popup-loading").bPopup(bPopupOptions);
      $(".details-resolution-select span").click(function() {
        var csv_id = $(this).attr('id').match(/csv-(\d+)/)[1];
        $.post(
          '/ajax/resolve',
          {id: $(this).attr('id')},
          function() {
            $("#popup").bPopup().close();
            update_csv(csv_id);
          }
        );
      });
    });
  });

  $(".csv-process").live('click', function() {
    $(this).parent().parent().parent("td").addClass("loading");
    var csv_id = $(this).attr('id').match(/csv-(\d+)/)[1];
    $.post(
      '/ajax/process',
      {id: $(this).attr('id')},
      function() {
        $(this).parent().parent().parent("td").removeClass("loading");
        update_csv(csv_id);
      }
    );
  });

  $(".links-container").live('click', function() {
    $(this).children(".links-links").show();
  });

  $(".links-links span").live('click', function() {
    $(".links-links").hide();
  });

  $("body").click(function() {
    $(".links-links").hide();
  });

  $(".data-check").live('click', function() {
    $(this).parent().parent().parent("td").addClass("loading");
    var match = $(this).attr('id').match(/(\w+)-(\d+)/);
    $.post(
      '/ajax/check',
      {id: $(this).attr('id')},
      function() {
        $(this).parent().parent().parent("td").removeClass("loading");
        update_data(match[1], match[2]);
      }
    );
  });

  $(".toggle-download-form").live('click', function() {
    $(this).parent().parent().parent().parent("tr").next("tr.download-form").toggle();
  });

  $(".toggle-form").click(function() {
    $(this).next("form").toggle();
    $(this).css("display", "none");
  });

  $(".details-suggestion .suggest li").click(function() {
    $("input[name="+$(this).attr("class")+"]").val($(this).text());
  });

  $(".csv-eip").editable("/ajax/csv", csvEditableOptions);

  $(".data-eip").editable("/ajax/data", dataEditableOptions);

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

  $("select.siteopts").change(function() {
    $("select.blockopts").load(
      '/ajax/blockopts',
      {site_id: $(this).val()}
    );
  });

  $("select.blockopts").load(
    '/ajax/blockopts',
    {site_id: $("select.siteopts").val()}
  );

  $("select.specs_operatoropts").change(function() {
    $("select.specsopts").load(
      '/ajax/specsopts',
      {
        operator_id: $(this).val(),
        specs_number: $(this).hasClass('specs_number') ? 1 : 0,
        specs_barcode: $(this).hasClass('specs_barcode') ? 1 : 0
      }
    );
  });

  $("select.specsopts").load(
    '/ajax/specsopts',
    {
      operator_id: $("select.specs_operatoropts").val(),
      specs_number: $("select.specs_operatoropts").hasClass('specs_number') ? 1 : 0,
      specs_barcode: $("select.specs_operatoropts").hasClass('specs_barcode') ? 1 : 0
    }
  );

  $("select.exp_operatoropts").change(function() {
    $("select.expopts").load(
      '/ajax/expopts',
      {
        operator_id: $(this).val(),
        exp_number: $(this).hasClass('exp_number') ? 1 : 0,
        exp_barcode: $(this).hasClass('exp_barcode') ? 1 : 0
      }
    );
  });

  $("select.expopts").load(
    '/ajax/expopts',
    {
      operator_id: $("select.exp_operatoropts").val(),
      exp_number: $("select.exp_operatoropts").hasClass('exp_number') ? 1 : 0,
      exp_barcode: $("select.exp_operatoropts").hasClass('exp_barcode') ? 1 : 0
    }
  );

  $("select.site_operatoropts").change(function() {
    $("select.siteopts").load(
      '/ajax/siteopts',
      {
        operator_id: $(this).val(),
        hide_all: $(this).hasClass('hide-all') ? 1 : 0
      }
    );
  });

  $("select.siteopts").load(
    '/ajax/siteopts',
    {
      operator_id: $("select.site_operatoropts").val(),
      hide_all: $("select.site_operatoropts").hasClass('hide-all') ? 1 : 0
    }
  );

  $("select.specs_specsinputs").change(function() {
    $.post(
      '/ajax/specsarray',
      {
        type: 'specs_barcode',
        value: $(this).val()
      },
      function(data) {
        var values = $.parseJSON(data);
        for (var key in values) $('.'+key+'input').val(values[key]);
      }
    );
  });

  $("select.exp_specsinputs").change(function() {
    $.post(
      '/ajax/specsarray',
      {
        type: 'exp_number',
        value: $(this).val()
      },
      function(data) {
        var values = $.parseJSON(data);
        for (var key in values) $('.'+key+'input').val(values[key]);
      }
    );
  });

  $(".autocomplete-barcode-barcode").autocomplete({
    source: '/ajax/autocompletebarcode',
    minLength: 3,
    delay: 300,
    appendTo: $(".autocomplete-barcode-barcode").parent()
  });

//  .next(".field.select").children("label").children(".field").children("select.blockopts").load(
//    '/ajax/blockopts',
//    {site_id: $(this).val()}
//  );

});

function update_csv(id, new_id) {
  new_id = new_id || id;

  $("#csv-"+id).addClass('loading');
//  $("#csv-"+id).addClass('loading-small');
  $("#csv-"+id).attr("id", "csv-"+id+"-deleted");
  $.post(
    "/ajax/update",
    {
      id: new_id,
      actions: $("#csv-"+id+"-deleted").parents("table.data").hasClass('has-actions') ? 1 : 0,
      details: $("#csv-"+id+"-deleted").parents("table.data").hasClass('has-details') ? 1 : 0,
      header: $("#csv-"+id+"-deleted").parents("table.data").hasClass('has-header') ? 1 : 0,
      hide_header_info: $("#csv-"+id+"-deleted").parents("table.data").hasClass('has-hide-header') ? 1 : 0
    },
    function(data) {
      $("#csv-"+id+"-deleted").next("tr.details").remove();
      $("#csv-"+id+"-deleted").replaceWith(data);
      $("#csv-"+new_id+" .csv-eip").editable("/ajax/csv", csvEditableOptions);
//      $("#csv-"+new_id).next("tr.details").hide();
//      $("#csv-"+new_id).next("tr.details").children("td").text("").addClass('loading');
    },
    "html"
  );
}

function update_data(type, id, new_id) {
  new_id = new_id || id;

  $("#"+type+"-"+id).addClass('loading');
//  $("#"+type+"-"+id).addClass('loading-small');
  $("#"+type+"-"+id).attr("id", type+"-"+id+"-deleted");
  $.post(
    "/ajax/updatedata",
    {
      id: new_id,
      type: type,
      actions: $("#"+type+"-"+id+"-deleted").parents("table.data").hasClass('has-actions') ? 1 : 0,
      details: $("#"+type+"-"+id+"-deleted").parents("table.data").hasClass('has-details') ? 1 : 0,
      header: $("#"+type+"-"+id+"-deleted").parents("table.data").hasClass('has-header') ? 1 : 0,
      hide_header_info: $("#"+type+"-"+id+"-deleted").parents("table.data").hasClass('has-hide-header') ? 1 : 0
    },
    function(data) {
      $("#"+type+"-"+id+"-deleted").next("tr.details").remove();
      $("#"+type+"-"+id+"-deleted").replaceWith(data);
      $("#"+type+"-"+new_id+" .data-eip").editable("/ajax/data", dataEditableOptions);
//      $("#"+type+"-"+new_id).next("tr.details").hide();
//      $("#"+type+"-"+new_id).next("tr.details").children("td").text("").addClass('loading');
    },
    "html"
  );
}