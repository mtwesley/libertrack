$(function() {

  $(".toggle-details").click(function() {
    $(this).parent().parent("tr").next("tr.edit").next("tr.details").toggle();
  });

  $(".toggle-edit").click(function() {
    $(this).parent().parent("tr").next("tr.edit").toggle();
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

});