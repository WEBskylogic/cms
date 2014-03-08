$(document).ready(function(){
	
	$('#content_basic textarea').autoResize();

	if($('.cm-calendar').length>0)
	{
		$('.cm-calendar').appendDtpicker({
				"dateFormat": "YYYY-MM-DD hh:mm",
				"locale": "ru"
			});
	}
	
	
	///display active tab
	var tab=$.cookie('active_tab');
	if($('#'+tab).length>0)
	{
		$(".cm-js").removeClass('cm-active');	
		$('#'+tab).addClass('cm-active');	
		$('.cm-tabs').addClass('hidden');
		$('#content_'+tab).removeClass('hidden');	
	}
	
	
	$(".cm-check-changes").delegate(":input", "change", function(){
		$(".cm-check-changes").addClass('tb_was_change');
	});
	
	$(".extra-tools .cm-confirm").live('click', function(){
		$(".cm-check-changes").removeClass('tb_was_change');
		window.onbeforeunload =false;
	});
	$(".cm-check-changes").live('submit', function(){
		$(".cm-check-changes").removeClass('tb_was_change');
		window.onbeforeunload =false;
	});
	$(".extra-tools .cm-confirm").live('click', function(){
		$(".cm-check-changes").removeClass('tb_was_change');
		window.onbeforeunload =false;
	});
	
	$(".tb_was_change :input").live("change", function(){
		window.onbeforeunload = function(){
		  return "Are you sure you wish to leave this delightful page?";
		}
	});
	
	if($('.cm-active.hidden_li').length>0)$(".hidden_li").show();

	$(".see-all").live('click', function(){
		if($(".hidden_li").css('display')=='none')
		{
			$(".hidden_li").show();
			$(this).html('Скрыть ...');
		}
		else{
			$(".hidden_li").hide();
			$(this).html('Показать все ...');
		}
	});
	
	/////////
	$('.pos_block').live('click', function(){
        $('.pos_block').removeClass('hov_position');
		$(this).addClass('hov_position');
		$('input[name=watermark_position]').val($(this).attr('id'));
    });
	
	$('input[name=watermark_type]').live('click', function(){
        $('.type_watermark_div').hide();
		if($(this).val()==0)$('#text_image_watermark').show();
		else $('#image_watermark').show();
		
		$('input[name=watermark_position]').val($(this).attr('id'));
    });
	////
	

	$("div").parent(".lBlock");
	//autoHide();

	 $('#read_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".read_chmod").attr('checked', 'checked');
        else $(".read_chmod").attr('checked', false);
    });

    $('#add_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".add_chmod").attr('checked', 'checked');
        else $(".add_chmod").attr('checked', false);
    });

    $('#edit_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".edit_chmod").attr('checked', 'checked');
        else $(".edit_chmod").attr('checked', false);
    });
	
	 $('#del_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".del_chmod").attr('checked', 'checked');
        else $(".del_chmod").attr('checked', false);
    });
	
	//////subsystem
	$('#config_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".config_chmod").attr('checked', 'checked');
        else $(".config_chmod").attr('checked', false);
    });

    $('#help_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".help_chmod").attr('checked', 'checked');
        else $(".help_chmod").attr('checked', false);
    });

    $('#translate_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".translate_chmod").attr('checked', 'checked');
        else $(".translate_chmod").attr('checked', false);
    });
	
	 $('#chmod_all').live('click', function(){
        if($(this).attr('checked')=='checked')$(".chmod_chmod").attr('checked', 'checked');
        else $(".chmod_chmod").attr('checked', false);
    });
	

	$('.del_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')=='checked')$("#read"+id).attr('checked', 'checked');
	});
    $('.add_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')=='checked')$("#read"+id).attr('checked', 'checked');
    });
	$('.edit_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')=='checked')$("#read"+id).attr('checked', 'checked');
    });

    $('.read_chmod').live('click', function(){
        var id=$(this).val();
        if($(this).attr('checked')!='checked')
        {
            $("#add"+id).attr('checked', false);
            $("#edit"+id).attr('checked', false);
			$("#del"+id).attr('checked', false);
        }
    });

    $('.cm-notification-close').live('click', function(){
        $(".notification-e").parent(this).empty();
		$(".notification-n").parent(this).empty();
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
		var action = $('.back-link').attr('href');
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
	
	$('input[type=checkbox]').on('click', function(){//alert($('.check').attr('checked'));
		var id=$(this).attr('id');
		var cl=$(this).attr('class');
		
		if(id!='')
		{
			if($(this).attr('checked')=='checked')$('.'+id).attr('checked', true);
			else $('.'+id).attr('checked', false);
		}

		if(cl!='')
		{
			if($(this).attr('checked')=='checked')$('#'+cl).attr('checked', true);
		}
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
		var id2=$(this).attr('id');
		var id='content_'+id2;
		
		$('.cm-js').removeClass('cm-active');
		$(this).addClass('cm-active');
		
		
		$('.cm-tabs').addClass('hidden');
		$('#'+id).removeClass('hidden');
		$.cookie('active_tab', id2);
	});
	
	$('#sw_select_RU_wrap_').click(function(){
		if($('#select_RU_wrap_').css('display')=='block')$('#select_RU_wrap_').css('display', 'none');
		else $('#select_RU_wrap_').css('display', 'block');
	});	
	
	$(".notification-content").live('click', function(){
		var id = $(this).attr('id').split('_')[1];
		closeNotification(id, false);
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

function showFormPass()
{
	if($('#main_column_login').css('display')=='none')
	{
		$('#main_column_login').fadeIn(200);
		$('#main_column_pass').fadeOut(200);	
	}
	else{
		$('#main_column_login').fadeOut(200);
		$('#main_column_pass').fadeIn(200);
	}
}