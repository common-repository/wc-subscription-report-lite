var dashboard_summary_data = new Array();
var title_dashboard_summary = 'Today';

jQuery(document).ready(function($) {
    $('#slt_dashboard_summary').change(function(){
		//alert(jQuery(this).val());
		var last_days = $("#slt_dashboard_summary").val();
		if(dashboard_summary_data[last_days]){
			data = dashboard_summary_data[last_days];
			$.each(data,function(key, value){				
				$('#'+key).html(value);
			});
		}else{
			$('form#frm_dashboard_summary').submit();
		}
		if(last_days == "today"){
			title_dashboard_summary = "Today";
		}else if(last_days == "yesterday"){
			title_dashboard_summary = "Yesterday";
		}else if(last_days == "dayofyesterday"){
			title_dashboard_summary = "Day of Yesterday";
		}else{
			title_dashboard_summary = "Last " + last_days +  " Days";
		}
		$("#title_dashboard_summary").find('span').html(title_dashboard_summary);
		
	});
	
	$('.loading_summary').css({"opacity":"0.5"});
	
	$('form#frm_dashboard_summary').submit(function(e) {
		
		var last_days = $("#slt_dashboard_summary").val();
		
		$('.loading_summary').fadeIn();
		
		//alert('fdasfd');
		$("#slt_dashboard_summary").attr('disabled',false).addClass('disabled');
		$.ajax({
			type: "POST",
			url: ic_ajax_object.ajaxurl,
			data:  $( "form#frm_dashboard_summary" ).serialize(),
			dataType: "json",
			success:function(data) {
				//alert(JSON.stringify(data))
				
				dashboard_summary_data[last_days] = data;
				
				$.each(data,function(key, value){
					//alert(key + " - " + value)
					$('#'+key).html(value);
				});
				
				$('.dashboard_summary_postbox .block').fadeIn('slow');
				
				$("#slt_dashboard_summary").attr('disabled',false).removeClass('disabled');
				
				$('.loading_summary').fadeOut();
			},
			error: function(jqxhr, textStatus, error ){
				
				$(".ajax_error").html(jqxhr.responseText);				
				//alert("responseText" + jqxhr.responseText);				
				submitClicked = false;								
				//window.location = window.location;
				$("#slt_dashboard_summary").attr('disabled',false).removeClass('disabled');				
				$('.loading_summary').fadeOut();
				
			},
			beforeSend: function () {
				$("#slt_dashboard_summary").attr('disabled',true).addClass('disabled');
			}
		});
		
        return false;
    });
	
	//$('.dashboard_summary_postbox .block').fadeIn('slow');
	
	if($('#slt_dashboard_summary').length > 0){
		$('input#btn_dashboard_summary').trigger('click');	
	}
});