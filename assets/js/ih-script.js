
jQuery(document).ready(function($){
	
	var dialogBoxWrapper = $("#rs-ih-dialogBoxWrapper");
	
	$("a#ih_close").click(function(){
		dialogBoxWrapper.css({'display': 'none'});
	});
	
	// hide dialog box when clicking outside of it
	$(document).on('click', function (e) {
		if ($(e.target).closest("#rs-ih-popup").length === 0) {
			dialogBoxWrapper.hide();
		}
	});
	
});