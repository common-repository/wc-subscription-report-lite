var ic_commerce_vars = Array();
ic_commerce_vars['country_dropdown']	=	5;
var total_shop_day = "-365D";
jQuery(document).ready(function($) {
	$(".numberonly").keydown(function(event) {
		// Allow: backspace, delete, tab, escape, enter and .
		if ( $.inArray(event.keyCode,[46,8,9,27,13,190]) !== -1 ||
			 // Allow: Ctrl+A
			(event.keyCode == 65 && event.ctrlKey === true) || 
			 // Allow: home, end, left, right
			(event.keyCode >= 35 && event.keyCode <= 39)) {
				 // let it happen, don't do anything
				 return;
		}
		else {
			// Ensure that it is a number and stop the keypress
			if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105 )) {
				event.preventDefault(); 
			}   
		}
	});
	
	 	var custom_uploader;
		var upload_this = null;
		
		$('a.ic_upload_button').click(function(e) {
			upload_this = $(this);
			e.preventDefault();
			//If the uploader object has already been created, reopen the dialog
			if (custom_uploader) {
				custom_uploader.open();
				return;
			}
			//Extend the wp.media object
			custom_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose File',
				//frame: 'post',
				button: {
					text: 'Choose File'
				},
				multiple: false
			});
			//When a file is selected, grab the URL and set it as the text field's value
			custom_uploader.on('select', function() {
				attachment = custom_uploader.state().get('selection').first().toJSON();
				upload_this.parent().find('input[type=text].upload_field').val(attachment.url);
			});
			//Open the uploader dialog
			custom_uploader.open();
		});
		
		$('.clear_textbox').click(function(){
			$(this).parent().find('input[type=text]').val('');
		});
	
	
});





