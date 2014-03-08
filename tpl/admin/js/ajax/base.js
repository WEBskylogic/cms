$.router(/^admin=(.+)$/, function(m, user){ 
		 
		//$("#ajax_loading_box").show(); 
		$.ajax({type: "POST",url: "/admin/"+user,  
			cache: false, 
			data: {},
			success: function(html){
				 
				$('#Router-ContentMy').html(html);	
				//$("#ajax_loading_box").hide();
				
			}});
}) 

$(document).ready(function(){
	$("div").parent(".lBlock")
	autoHide();

    $('#read_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".read_chmod").attr('checked', 'checked');
        else $(".read_chmod").attr('checked', false);
    });

    $('#add_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".add_chmod").attr('checked', 'checked');
        else $(".add_chmod").attr('checked', false);
    });

    $('#del_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".del_chmod").attr('checked', 'checked');
        else $(".del_chmod").attr('checked', false);
    });

	$('.del_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')=='checked')$("#read"+id).attr('checked', 'checked');
	});
    $('.add_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')=='checked')$("#read"+id).attr('checked', 'checked');
    });

    $('.read_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')!='checked')
        {
            $("#add"+id).attr('checked', false);
            $("#del"+id).attr('checked', false);
        }
    });

    $('.cm-notification-close').live('click', function(){
        $(".notification-e").parent(this).empty();
    });

	$('.cm-external-focus').live('click', function(){
		var id = $(this).attr('rev');
		$('#'+id).focus();
	});
	
	
	$('.cm-save-and-close').click(function(){
		var action = '/admin/'+$('#action').val();
		$('form[name=page_update_form]').attr('action', action);
	});
	
	$('.cm-save-and-close-product').click(function(){
		var action = '/admin/product';
		$('form[name=page_update_form]').attr('action', action);
	});
	
	$('.check_all').click(function(){
		$('.check-item').attr('checked', true);
		$('.check_all2').attr('checked', true);
	});
	
	$('.check_all2').click(function(){//alert($('.check').attr('checked'));
		if($('.check-item').attr('checked')=='checked')$('.check-item').attr('checked', false);
		else $('.check-item').attr('checked', true);
	});
	
	$('.uncheck_all').click(function(){
		$('.check-item').attr('checked', false);
		$('.check_all2').attr('checked', false);
	});
	
	$('.cm-save-and-close').click(function(){
		var action = '/admin/'+$('#action').val();
		$('form[name=page_update_form]').attr('action', action);
	});
	
	$("#ajax_loading_box").ajaxStart(function(){
		 $(this).show();
	});
			
	$("#ajax_loading_box").ajaxStop(function(){
		$(this).hide();
	});
	
	$(".cm-js").live('click', function(){
		var id='content_'+$(this).attr('id');
		
		$('.cm-js').removeClass('cm-active');
		$(this).addClass('cm-active');
		
		
		$('.cm-tabs').addClass('hidden');
		$('#'+id).removeClass('hidden');
	});
	
});		


$(".cm-confirm").live('click', function(){
	return  confirm('Вы уверены что хотите удалить данную запись?');
});

function info_box(typeM, message){ //error information warning success
	
	showNotification({
		message: message,
		type: typeM
		/*
		
		'duration': 0, // display duration
		
		'autoClose' : false,
		
		*/
	}); 		
}


function fn_switch_default_box(holder, prefix, default_id)
{
	var default_box = $('#' + prefix + '_' + default_id);
	var checked_items = $('input[id^=' + prefix + '_].checkbox:checked').not(default_box).length + holder.checked ? 1 : 0;
	if (checked_items == 0) {
		default_box.attr('disabled', 'disabled');
		default_box.attr('checked', 'checked');
	} else {
		default_box.removeAttr('disabled');
	}
}

function autoHide()
{
	$('.cm-auto-hide').each(function() {
		var id = str_replace('notification_', '', $(this).attr('id')); // FIXME: not good
		if (($(this).hasClass('product-notification-container') || $(this).hasClass('notification-content')) && typeof(notice_displaying_time) != 'undefined') {
			closeNotification(id, true, false, notice_displaying_time * 1000);
		} else {
			closeNotification(id, true);
		}
	});	
}

function str_replace(search, replace, subject)
{
	return subject.split(search).join(replace);
}
function closeNotification(key, delayed, no_fade, delay)
{
	var DELAY = typeof(delay) == 'undefined' ? 5000 : delay;
	if (delayed == true) {
		if (DELAY != 0) {
			var timeout_key = setTimeout(function(){
				closeNotification(key);
			}, DELAY);
			
			return timeout_key;
		}
		return true;
	}

	var notification = parent.window != window ? $('#notification_' + key, parent.document) : $('#notification_' + key);
	if (notification.hasClass('cm-ajax-close-notification')) {
		var id = key.indexOf('__') != -1 ? key.substr(0, key.indexOf('__')) : key;
		jQuery.ajaxRequest(fn_url(index_script + '?close_notification=' + id), {hidden: true});
	}

	if (no_fade || jQuery.browser.msie && jQuery.ua.version == '6.0') {
		notification.remove();
	} else {
		notification.fadeOut('slow', function() {notification.remove();});
	}
}
