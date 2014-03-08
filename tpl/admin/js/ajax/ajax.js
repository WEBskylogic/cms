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
		$.ajax({type:"POST",url:"/ajax/active",dataType:'json', data:dataString,cache:false,success:
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
        $.ajax({type:"POST",url:"/ajax/addmodule",dataType:'json', data:dataString,cache:false,success:
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
});

function sortA()
{
	var tb=$("#action").val();
	var arr=$(".tb_sort").tableDnDSerialize();
	var dataString = 'arr='+arr+'&tb='+tb;//alert(dataString);
	$.ajax({type: "POST",url: "/ajax/sort",dataType:'json',data: dataString,cache: false,success:function(data){$('#message').html(data.message);autoHide();}});
}