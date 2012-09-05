$(document).ready(function(){
	///////////////Sort
	$(".tb_sort").tableDnD({
		onDragClass:"hover"
	});
	$(".move").live('mouseup',function(){
		 sortA();
	});
	
	///////Active
	$('.active_status').live('click',function()
	{
		var tb=$("#action").val();
		var id=$(this).attr('id');
		var dataString = 'id='+id+'&tb='+tb;
		$.ajax({type:"POST",url:"/ajaxadmin/active",dataType:'json', data:dataString,cache:false,success:
		function(data)
		{
			if(!data.access)$('#'+id).html(data.active);//alert('asd');
			$('#message').html(data.message);	
			autoHide();
		}
	});});

    $('#add_module').live('change',function()
    {
        var id=$(this).val();
        var dataString = 'id='+id;
        $.ajax({type:"POST",url:"/ajaxadmin/addmodule",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
                if(data)
                {
                    $('#name_module').val(data.name);
                    $('#comment_module').val(data.comment);
                    $('#tables_module').val(data.tables);
                }
                else{
                    $('#name_module').val('');
                    $('#comment_module').val('');
                    $('#tables_module').val('');
                }
            }
        });
    });
	
	$('#catalog_add').live('change',function()
    {
        var id=$(this).val();
        var dataString = 'id='+id;
        $.ajax({type:"POST",url:"/ajaxadmin/orderproduct",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
            	$('#product_add').html(data.content);
            }
        });
    });
	
	$('#product_add').live('change',function()
    {
        var id=$(this).val();
		var order_id=$(this).attr('name');
        var dataString = 'id='+id+'&order_id='+order_id;
        $.ajax({type:"POST",url:"/ajaxadmin/orderproductview",dataType:'json', data:dataString,cache:false,success:
            function(data)
            {
            	$('#order_product').html(data.content);
				$('#total').html(data.total);
            }
        });
    });
});

function sortA()
{
	var tb=$("#action").val();
	var arr=$(".tb_sort").tableDnDSerialize();
	var dataString = 'arr='+arr+'&tb='+tb;//alert(dataString);
	$.ajax({type: "POST",url: "/ajaxadmin/sort",dataType:'json',data: dataString,cache: false,success:function(data){$('#message').html(data.message);autoHide();}});
}

////Add comments
function addComment(id)
{
    var name = $("#name_form").val();
    var message = $("#text_form").val();
	var photo = '';
	var subb = $("input[name=id]").val();
    var dataString = 'id='+id+'&name='+name+'&message='+message+'&photo='+photo+'&sub='+subb;
    $.ajax({type: "POST",url: "/ajaxadmin/addcomment",dataType:'json',data: dataString,cache: false,success: 
	function(data){
		$("#input").html(data.message);//alert(data.message+'asda');
		$("#answers").html(data.content);
		$("#name_form").val('');
		$("#text_form").val('');
	}});
    return false;
}