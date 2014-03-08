$(document).ready(function() {		
	$(window).bind('resize', function(){
		var height = $('#cabinet-new').height();
		if(height!=500)
		{
			$('#cabinet-new .overview').height(windowHeight()-125+'px');
		}
    });

$('.cabinet-submit').live('click', function () {
	var id=$(this).attr('id');
    $('.cabinet-submit').removeClass('active');
    $(this).addClass('active');
	
	loadCabinet('');
});

$('#close-cabinet').live('click', function(){
    if ($('#cabinet-new').hasClass('show')){
        $('#overlay').hide();
        $('#cabinet-new').animate({
            top: '-700'
        }, 500, function(){
            $('#cabinet-new').remove();
			
        });}
});

$('#overlay').live('click', function () {
    $('#overlay').hide();
    $('#registration_form').remove();
    $('#login_form').remove();
    $('#cabinet-new').animate({
        top: '-700'
    }, 500, function () {
        $('#cabinet-new').remove();
		
    });
});

$('.item_del').live('click',function () {
    var id = $(this).attr('dir');
	removefrombasket(id);
});

$('.cnt1').live('click', function (){
    var id = $(this).attr('id');
    var title = $(this).attr('title');
    var cnt = $('#cnt' + id).val();
    if (title == '-' && parseInt(cnt) > 0) {
        $('#cnt' + id).val(parseInt(cnt) - 1);
    }
    else if (title == '+') {
        $('#cnt' + id).val(parseInt(cnt) + 1);
    }
	var pid = $(this).parent().parent().parent().parent().attr('id');
	change_amount('cnt' + id, pid);
});

	
///////Change amount in orders
$('.amount').live('change', function(){
	var id = $(this).attr('id');
	var pid = $(this).parent().parent().parent().attr('id');
	change_amount(id, pid);
});

});	
//////////////////////////////////////////////
function change_view(id)
{
	$('.overview').attr('id', id);
	$('#gridview_but').html('<img src="/images/gridview.png" alt="" />');
    $('#tableview_but').html('<img src="/images/tableview.png" alt="" />');
	$('#'+id+'_but').html('<img src="/images/'+id+'2.png" alt="" />');
	
	var dataString='id='+id+'&type=1';
	$.ajax({type: "POST", url: "/ajax/orders/changeview", data: dataString, cache: false});	
}

function expand()
{
	var height = $('#cabinet-new').height();
	if(height==500)
	{
		var id=2;
		$('#eview').html('<img src="/images/expand2.png" alt="" />');
		$('#cabinet-new').addClass('expandview');
		$('#cabinet-new.expandview .overview').height(windowHeight()-115+'px');
	}
	else{
		var id=1;
		$('#eview').html('<img src="/images/expand.png" alt="" />');
		$('#cabinet-new').removeClass('expandview');
		$('#cabinet-new .overview').css('height', '');
	}
	
	var dataString='id='+id+'&type=2';
	$.ajax({type: "POST", url: "/ajax/orders/changeview", data: dataString, cache: false});	
}

function close_cabinet()
{
	if ($('#cabinet-new').hasClass('show'))
	{
        $('#overlay').hide();
        $('#cabinet-new').animate({
            top: '-700'
        }, 500, function(){
            $('#cabinet-new').remove();
			
        });
		
	}
}

function loadCabinet(type)
{
	$(".basket").css('opacity', '0.2');
	$('#bottom_bar .holder').append('<div class="loading_im"><img src="/images/load.gif" /></div>');
	if(type=='')type = $('.cabinet-submit.active').attr('id');

	var dataString = 'type='+type;
	$.ajax({type: "POST", url: "/ajax/orders/loadcabinet", data: dataString, dataType:'json', cache: false, success: function (data){
		$(".basket").html(data.content);
		$(".basket").css('opacity', '1');
		$('.loading_im').remove();
		
		if(data.total)
		{
			$('#sum_p').html(data.total);	
			$('#count_p').html(data.count);	
		}
		
		var height = $('#cabinet-new').height();
		if(height!=500)
		{
			$('#cabinet-new .overview').height(windowHeight()-135+'px');
		}
		
		$('#tableview_but, #gridview_but, #sum, .bot_link_basket').hide();
		if(type=='cabinet-show')
		{
			$('.save').show();
		}
		else if(type=='basket-show')
		{
			$('#sum, .go, #tableview_but, #gridview_but').show();
		}
		else if(type=='ordering-show')
		{
			$('#sum, .send').show();
		}
    }});
}


/*     Basket    */
function showBasket()
{
    dataString = '';
    $.ajax({type: "POST", url: "/ajax/orders/showbasket", data: dataString, cache: false, success: 
	function (html)
	{
        $('#cabinet-new').remove();
		$("body").append(html);
        $('#overlay').show();
        $('#cabinet-new').animate({
            top: '0'
        }, 500, function(){
			
			loadCabinet('');
		});
        $('#cabinet-new').addClass('show');
		
    }});
}

function change_amount(id, pid)
{
	var cnt2 = $('#'+id).val();
	var arr=pid.split('_');
	pid = arr[1];
	if(cnt2>0)
	{
		var dataString = 'count=' + cnt2 + '&pid=' + pid;
		$.ajax({type: "POST", url: "/ajax/orders/refreshbasket", data: dataString, dataType:"json", cache: false, success: 
		function (data)
		{
			$('#count_p').html(data.count);
			$('#sum_p').html(data.total);
			$('#sum_'+pid).html(data.sum);
			$('#count_bascket').html(data.count);
			$('#sum_bascket').html(data.total);			
		}});
	}
	else{
		removefrombasket(pid);	
	}
}

function removefrombasket(id)
{
	var dataString = 'id=' + id;
    $.ajax({type: "POST", url: "/ajax/orders/removefrombasket", data: dataString, dataType:"json", cache: false, success: 
	function (data)
	{
        $('#item_'+id).fadeOut(200, function(){$(this).remove();});
		$('#count_p').html(data.count);
		$('#sum_p').html(data.total);
		$('#count_bascket').html(data.count);
		$('#sum_bascket').html(data.total);
    }});	
}


/*     Ordering    */
function makeorder()
{
	if($('.item_basket').length>0)
	{
		loadCabinet('ordering-show');
	}
}

function send_order()
{
	var fio = $('#o_name').val();
	var email = $('#o_email').val();
	var phone = $('#o_phone').val();
	var err='';
	$("#o_email, #o_name, #o_phone").removeAttr("style");
	
	if(!IsValidateEmail(email))
	{
		err='Неверный E-mail';
		$('#o_email').css('border', '1px solid #ff7a7a');
	}
	
	if($('#o_name').val().length<2)
	{
		err='Поля * являются обязательными';
		$('#o_name').css('border', '1px solid #ff7a7a');
	}
	if($('#o_phone').val().length<5)
	{
		err='Поля * являются обязательными';
		$('#o_phone').css('border', '1px solid #ff7a7a');
	}
	
	if(err=='')$('#order_form').submit();
	else $('.message_order').html(err);
}


/*     Cabinet    */
function saveprofile()
{
    var dataString = '';
    var name = $('#n_name').val();
    var new_pass = $('#n_pass').val();
	var old_pass = $('#o_pass').val();
    var city = $('#n_city').val();
    var phone = $('#n_phone').val();
	var address = $('#n_address').val();
	var post_index = $('#n_post_index').val();

    var dataString = 'name='+name+'&new_pass='+new_pass+'&old_pass='+old_pass+'&city='+city+'&phone='+phone+'&address='+address+'&post_index='+post_index;
    $.ajax({type: "POST", url: "/ajax/orders/saveprofile", data: dataString, cache: false, success: function (data) {
        $('#cabinet .message_basket').html(data);
    }});
}

/*     Orders    */
function showorder(id)
{
    var dataString = 'id=' + id;
    $.ajax({type: "POST", url: "/ajax/orders/getorder", data: dataString, cache: false, success: function (data) {
        $('#cabinet_order').html(data);
		$('#tb_orders').hide();
    }});
}

function allorders()
{
    $.ajax({type: "POST", url: "/ajax/orders/allorders", cache: false, success: function (data) {
        $('.orders').find('.overview').html(data);
    }});
}

function back_orders()
{
	$('#tb_orders').show();	
	$('#cabinet_order').html('');
}