/*
 * This contain plugin JS scripts
 */

jQuery(document).ready(function($){
	
	var dialogBoxWrapper = $("#rs-ih-dialogBoxWrapper"),
		dialogBox = $("#rs-ih-dialogBox"),
		dialogPopupBox = $("#rs-ih-popup"),
		dialogHTML = '';

	var menu = {
		context_menu: function( img ){
			var offset = $(img).offset(),
				img_src = $(img).attr('src'),
				img_alt = $(img).attr('alt'),
				window_height = $( window ).height();

			// display dialog box wrapper
			$(dialogBoxWrapper).css({'display': 'block'});
			
			// add dialog a margin from top
			$(dialogPopupBox).css({'top': ( window_height / 2 ) - parseInt(100) });
			
			dialogHTML = '<textarea onclick="this.select()" cols="100" rows="4">&lt;a href="'+ ih_ajax.page_permalink +'"&gt;&lt;img src="'+ img_src +'" alt="'+ ih_ajax.page_title +'" /&gt;&lt;/a&gt;</textarea>';
			
			// add dialog box content
			dialogBox.html( dialogHTML );
		}
	}

	if( 'yes' == ih_ajax.is_page_enable || 'yes' == ih_ajax.is_mata_enable ) {
		$('.page img').bind('contextmenu', function() {
			menu.context_menu( this );
			
	        return false;
	    });
	}

	if( 'yes' == ih_ajax.is_post_enable || 'yes' == ih_ajax.is_mata_enable ) {
		$('.single img').bind('contextmenu', function() {
			menu.context_menu( this );
			
	        return false;
	    });
	}

});