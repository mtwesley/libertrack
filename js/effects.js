$(function() {
  $(".toggle-details").click(function() {
    $(this).parent().parent("tr").next("tr.details").toggle();
		return false;
  });
});