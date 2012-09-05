function checkCoord(c)
{
	var id=$('#resize_photo_id').val();
	$("#x_"+id).val(c.x);
	$("#y_"+id).val(c.y);
	$("#w_"+id).val(c.w);
	$("#h_"+id).val(c.h);
}

function createUploader(width, height, path)
{//alert(document.getElementById('file-uploader'));

	var uploader = new qq.FileUploader({
		element: document.getElementById('file-uploader'),
		action: '/ajaxadmin/includephoto',
		onComplete: function(id, fileName, responseJSON){
			
			var fileName=$("#resize_photo_id").val()+'_b'+'.jpg';
			var img = new Image();
			var position = [ 10, 10, 110, 110 ];
			img.src = "/"+path+"/" + fileName;
			$(img).ready(function(){//alert(id);

				//alert('widht:'+img.width+'; height:'+img.height+'');
				var time=new Date(); //alert(fileName);
				//$('#file-uploader-demo1').hide(1000); 
				$("#crop").remove();
				$("#cut_avatar").attr("style",'display: block;').append("<img src='"+path+fileName  +"?"+time+"' id='crop' />");						
				
				$('#cut_avatar').show(500);                  
				$("#src").val(fileName);
				
				var id=$('#resize_photo_id').val();//alert(id);
				$("#create").css("display", "block");
				$("#create_t").css("display", "block");
				
				$("#crop").Jcrop({
					setSelect: position,
					onChange: checkCoord,
					onSelect: checkCoord,
					aspectRatio: width/height
				});
			});
		}
	});           
}

function createAvatar(id, path, width, height)
{//alert(type);
    $("#create").css("display", "none");
	$("#create_t").css("display", "none");
	$('.qq-upload-list').html('');
    $(".jcrop-holder").remove();
    var w, h, x, y, src, usr;
    w = $("#w_"+id).val();
    h = $("#h_"+id).val();
    x = $("#x_"+id).val();
    y = $("#y_"+id).val();
    src = $("#src").val();
    usr = $("#usr").val();
	//alert(id);
    $.ajax({ url: '/ajaxadmin/createphoto', type: 'post', data: {x: x, y: y, w: w, h: h, src: src, usr: usr, width: width, height: height, path:path}, success: function(data){
    	data = eval('('+data+')');
        //$("#file-uploader").remove();
       // $("#thumb img").attr("src", "/files/partners/" + data+'?'+time).css("float", "none").parent().css({"display": "block"});
		$("#thumb1 img").attr("src", "/"+path+"/" + data+'?'+new Date()).css("float", "none").parent();
    }});
}