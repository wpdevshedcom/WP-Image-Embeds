/*
 * This contain plugin JS scripts
 */

jQuery(document).ready(function($){
	
	var dialogBoxWrapper 	= $("#wp-image-embeds-dialogBoxWrapper"),
		dialogBox 			= $("#wp-image-embeds-dialogBox"),
		dialogPopupBox 		= $("#wp-image-embeds-popup"),
		dialogHTML 			= '';

	var menu = {
		context_menu: function( img ){
			var offset 	= $(img).offset(),
				img_src = $(img).attr('src'),
				img_alt = $(img).attr('alt'),
				window_height = $( window ).height();

			// display dialog box wrapper
			$(dialogBoxWrapper).css({'display': 'block'});
			
			// add dialog a margin from top
			$(dialogPopupBox).css({'top': ( window_height / 2 ) - parseInt(100) });
			
			dialogHTML = '<textarea onclick="this.select()" cols="100" rows="4">&lt;a href="'+ wpie_ajax.page_permalink +'"&gt;&lt;img src="'+ img_src +'" alt="'+ wpie_ajax.page_title +'" /&gt;&lt;/a&gt;</textarea>';
			
			// add dialog box content
			dialogBox.html( dialogHTML );
		}
	}

	if( 'yes' == wpie_ajax.is_page_enable || 'yes' == wpie_ajax.is_mata_enable ) {
		$('.page img, .single img').bind('contextmenu', function() {
			menu.context_menu( this );
			
	        return false;
	    });
	}

	if( 'yes' == wpie_ajax.is_post_enable || 'yes' == wpie_ajax.is_mata_enable ) {
		$('.single img').bind('contextmenu', function() {
			menu.context_menu( this );
			
	        return false;
	    });
	}
	
	
	/*
	 * Display or hide dialog Box
	 */
	var dialogBoxWrapper = $("#wp-image-embeds-dialogBoxWrapper");
	
	$("a#wp-image-embeds-close").click(function(){
		dialogBoxWrapper.css({'display': 'none'});
	});
	
	// hide dialog box when clicking outside of it
	$(document).on('click', function (e) {
		if ($(e.target).closest("#wp-image-embeds-popup").length === 0) {
			dialogBoxWrapper.hide();
		}
	});

});