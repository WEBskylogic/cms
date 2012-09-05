$(document).ready(function()
{
	///Lightbox
	$("a[rel=lightbox]").fancybox({
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'titlePosition' 	: 'over',
			'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
				return '<span id="fancybox-title-over">Image ' + (currentIndex + 1) + ' / ' + currentArray.length + (title.length ? ' &nbsp; ' + title : '') + '</span>';
			}
	});
	
	//////Change product photo in card product
	$('#more_photo a').live('click', function(e){
		var dir=$(this).attr('href');
		$("#img_load img").attr('src', dir);
		$("#more_photo a").removeClass('cur_more');
		$(this).addClass('cur_more');
		return false;
	});
	
	///////Clear input search
	$('input[name=search]').live('click', function(){
		if($(this).val()=='Search'||$(this).val()=='Поиск')$(this).val('');
	});
	
	///////Clear input mailer
	$('input[name=mailer]').live('click', function(){
		if($(this).val()=='E-mail')$(this).val('');
	});
	
	///////Change amount in orders
	$('.amount').live('change', function(){
		$('#update_order').submit();
	});
	
	///////Clear input search
	$('#text_form').live('click', function(e){
		if($(this).val()=='Сообщение...')$(this).val('');
	});
});