//Make global variables for selected image for further usage
var selectImgWidth,selectImgHeight,jcrop_api, boundx, boundy,isError=false;
$(document).ready(function(){
	
	$('#display_image_type input').live('click', function(){
		var id=$(this).val();
		display_image_type(id, '');
    });
	
	$('#display_image_type_extra input').live('click', function(){
		var id=$(this).val();
		display_image_type(id, '_extra');
    });
	
	
	$(".load_crop").live('click',function(){
		var type_upload = $('input[name=image_upload_type_extra]:checked').val();

		var id = $(this).children('img').attr('id');
		var src = $(this).children('img').attr('src');
		var width2=$('#width_image2').val();
		var height2=$('#height_image2').val();
		var dataString = 'id='+id+'&src='+src+'&width2='+width2+'&height2='+height2+'&type_upload='+type_upload;
		$.ajax({type:"POST",url:"/admin/ajax/getcrop", data:dataString, dataType:'json',cache:false,success:
		function(data)
		{
			createCrop2(data.path, data.fileName, data.image_id, true);
			display_image_type(type_upload, '_extra');
		}});
	});
	
	$(".close_extra").live('click',function(){
		$('.qq-upload-list').html('');
   		$(".jcrop-holder").remove();
		var src = $(".bord_img_extra").attr('src');
		var id=$('#resize_photo_id_extra').val();
		var path_image=$('#path_image_extra').val();
		if(src!='')
		{
			$('#sort'+id+' .load_crop img').attr('src', src);
		}
		
		var dataString = 'path='+path_image+'&image_id='+id;
		$.ajax({type:"POST",url:"/admin/ajax/delextra", data:dataString,cache:false});
	});
	
	$("#load_url_image_extra_submit").live('click',function(){
		var url = $("#load_url_image_extra").val();
		$("#load_url_image_extra").val('');
		var ext = url.substr(url.lastIndexOf(".") + 1);
	    if(checkExt(ext))
		{
			var image_id=$('#resize_photo_id_extra').val();
			var type_upload = $('input[name=image_upload_type_extra]:checked').val();
			var width_image=$('#width_image_extra').val();
			var height_image=$('#height_image_extra').val();
			var width2=$('#width_image2_extra').val();
			var height2=$('#height_image2_extra').val();
			var path_image=$('#path_image_extra').val();
			var dataString = 'url='+url+'&path='+path_image+image_id+'&type_upload='+type_upload+'&width='+width_image+'&height='+height_image+'&width2='+width2+'&height2='+height2
			$.ajax({type:"POST",url:"/admin/ajax/curlupload", data:dataString, dataType:'json',cache:false,success:
			function(data)
			{
				if(data.err!='')
				{
					$('.error').html(data.err).fadeIn(500);
				}
				else{
					var fileName=$("#resize_photo_id_extra").val()+'_b'+'.'+data.ext;
					if(type_upload==1)
					{
						var img = new Image();
						img.src = "/"+path_image+fileName;//alert(img.src);
						$(img).ready(function(){
							createCrop2(path_image, fileName, image_id);
						});
					}
					else{
						var time=new Date();
						var img=$(".bord_img_extra");
						var src=img.attr('src')+'?'+time;
						img.hide();
						img.attr('src', src);
						img.fadeIn('slow');
						$('#create_t_extra').hide();
						
						$('#bord_img_del_extra').attr('href', img.attr('src'));
						$('#bord_img_del_extra').show();
					}
					$('#cut_avatar_extra .jcrop-holder').remove();
				}
			}});
		}
	});
	
	$("#image_file_extra").live('change', function(){
	    var thumbId = document.getElementById('thumb');
	    var flag = 0;

	    // Get selected file parameters
	    var selectedImg = $(this)[0].files[0];
	    //Check the select file is JPG,PNG or GIF image
		var ext = selectedImg.type.substr(selectedImg.type.lastIndexOf("/") + 1);
	    if (!checkExt(ext)) {
	        $('.error').html('Please select a valid image file (jpg, png, gif, bmp are allowed)').fadeIn(500);
	        flag++;
	        isError = true;
	    }
	    
	    // Check the size of selected image if it is greater than 250 kb or not
	  	/*  else if (selectedImg.size > 250 * 1024) {
	        $('.error').html('The file you selected is too big. Max file size limit is 250 KB').fadeIn(500);
	        flag++;
	        isError = true;
	    }
	    */
	    if(flag==0){
	    isError=false;
	    $('.error').hide(); //if file is correct then hide the error message
	    
	    // Preview the selected image with object of HTML5 FileReader class
	    // Make the HTML5 FileReader Object
	    var oReader = new FileReader();
	        oReader.onload = function(e) {
	            // display the image with fading effect
				var type_upload = $('input[name=image_upload_type_extra]:checked').val();
				var width_image=$('#width_image_extra').val();
				var height_image=$('#height_image_extra').val();
				var width2=$('#width_image2').val();
				var height2=$('#height_image2').val();
				var image_id=$('#resize_photo_id_extra').val();
				var path_image=$('#path_image_extra').val();
				var action=$('#action2').val();
				
				var img = new Image();

				var dataString = 'action='+action+'&image='+e.target.result+'&path='+path_image+'&image_id='+image_id+'&width='+width_image+'&height='+height_image+'&width2='+width2+'&height2='+height2+'&type_upload='+type_upload;
				$.ajax({type:"POST",url:"/admin/ajax/includephoto", data:dataString, dataType:'json',cache:false,success:
				function(data)
				{
					afterSave(data, path_image, type_upload, '_extra');
				}});
	    };
	    // read selected file as DataURL
	    oReader.readAsDataURL(selectedImg);
	}
	})
	
	
	
	$("#load_url_image_submit").live('click',function(){
		
		var type_upload = $('input[name=image_upload_type]:checked').val();
		var url = $("#load_url_image").val();
		$("#load_url_image").val('');
		var ext = url.substr(url.lastIndexOf(".") + 1);
		var time=new Date();
	    if(checkExt(ext))
		{
			var width_image=$('#width_image').val();
			var height_image=$('#height_image').val();
			var width2=$('#width_image2').val();
			var height2=$('#height_image2').val();
			var path_image=$('#path_image').val();
			var dataString = 'url='+url+'&path='+path_image+$("#resize_photo_id").val()+'&type_upload='+type_upload+'&width='+width_image+'&height='+height_image+'&width2='+width2+'&height2='+height2;
			$.ajax({type:"POST",url:"/admin/ajax/curlupload", data:dataString, dataType:'json',cache:false,success:
			function(data)
			{
				if(data.err!='')
				{
					$('.error').html(data.err).fadeIn(500);
				}
				else{
					var fileName=$("#resize_photo_id").val()+'_b'+'.'+data.ext;
					if(type_upload==1)
					{
						var img = new Image();
						img.src = "/"+path_image+fileName+'?'+time;
						$(img).ready(function(){
							createCrop(path_image, fileName);
						});
					}
					else{
						
						var img=$(".bord_img");
						var src=img.attr('src')+'?'+time;
						img.hide();
						img.attr('src', src);
						img.fadeIn('slow');
						$('#create_t').hide();
						
						$('#bord_img_del').attr('href', img.attr('src'));
						$('#bord_img_del').show();
						
					}
					$('#cut_avatar .jcrop-holder').remove();
					
				}
			}});
		}
	});
	
	
	$("#image_file").live('change',function(){
	    var thumbId = document.getElementById('thumb');
	    var flag = 0;

	    // Get selected file parameters
	    var selectedImg = $(this)[0].files[0];
	    //Check the select file is JPG,PNG or GIF image
		var ext = selectedImg.type.substr(selectedImg.type.lastIndexOf("/") + 1);
	    if (!checkExt(ext)) {
	        $('.error').html('Please select a valid image file (jpg, png, gif, bmp are allowed)').fadeIn(500);
	        flag++;
	        isError = true;
	    }
	    
	    // Check the size of selected image if it is greater than 250 kb or not
	  	/*  else if (selectedImg.size > 250 * 1024) {
	        $('.error').html('The file you selected is too big. Max file size limit is 250 KB').fadeIn(500);
	        flag++;
	        isError = true;
	    }
	    */
	    if(flag==0){
	    isError=false;
	    $('.error').hide(); //if file is correct then hide the error message
	    
	    // Preview the selected image with object of HTML5 FileReader class
	    // Make the HTML5 FileReader Object
	    var oReader = new FileReader();
	        oReader.onload = function(e) {
	            // display the image with fading effect
				var type_upload = $('input[name=image_upload_type]:checked').val();
				var width_image=$('#width_image').val();
				var height_image=$('#height_image').val();
				var width2=$('#width_image2').val();
				var height2=$('#height_image2').val();
				var path_image=$('#path_image').val();
				var img = new Image();
				var action=$('#action').val();

				var dataString = 'action='+action+'&image='+e.target.result+'&path='+path_image+'&image_id='+$("#resize_photo_id").val()+'&width='+width_image+'&height='+height_image+'&width2='+width2+'&height2='+height2+'&type_upload='+type_upload;
				$.ajax({type:"POST",url:"/admin/ajax/includephoto", data:dataString, dataType:'json',cache:false,success:
				function(data)
				{
					afterSave(data, path_image, type_upload, '');
				}});
	    };
	    // read selected file as DataURL
	    oReader.readAsDataURL(selectedImg);
	}
	})
	
	$("#width_image").live('change',function(){
		if($('#cut_avatar .jcrop-holder').length > 0)
		{
			var width=$(this).val();
			if(width>5)
			{
				var path_image=$('#path_image').val();
				var fileName=$("#resize_photo_id").val()+'_b'+'.jpg';
				createCrop(path_image, fileName);
			}
		}
	});
	
	$("#height_image .jcrop-holder").live('change',function(){
		if($('#cut_avatar').length > 0)
		{
			var height=$(this).val();
			if(height>5)
			{
				var path_image=$('#path_image').val();
				var fileName=$("#resize_photo_id").val()+'_b'+'.jpg';
				createCrop(path_image, fileName);
			}
		}
	});
	
	$("#width_image_extra .jcrop-holder").live('change',function(){
		if($('#cut_avatar_extra').length > 0)
		{
			var width=$(this).val();
			if(width>5)
			{
				var image_id=$('#resize_photo_id_extra').val();
				var path_image=$('#path_image_extra').val();
				var fileName=$("#resize_photo_id_extra").val()+'_b'+'.jpg';
				createCrop2(path_image, fileName, image_id);
			}
		}
	});
	
	$("#height_image_extra .jcrop-holder").live('change',function(){
		if($('#cut_avatar_extra').length > 0)
		{
			var height=$(this).val();
			if(height>5)
			{
				var image_id=$('#resize_photo_id_extra').val();
				var path_image=$('#path_image_extra').val();
				var fileName=$("#resize_photo_id_extra").val()+'_b'+'.jpg';
				createCrop2(path_image, fileName, image_id);
			}
		}
	});
})

function afterSave(data, path_image, type_upload, suffix)
{
	if(data.err!='')
	{
		$('.error').html(data.err).fadeIn(500);
	}
	else{
		var image_id=$("#resize_photo_id"+suffix).val();
		if(type_upload==1)
		{
			var fileName=image_id+'_b'+'.'+data.ext;
			var img = new Image();
			img.src = "/"+path_image+fileName;//alert(img.src);
			$(img).ready(function(){
				if(suffix=='')createCrop(path_image, fileName);
				else createCrop2(path_image, fileName, image_id);
			});
		}
		else{
			var fileName=image_id+'_s'+'.'+data.ext;
			var time=new Date();
			var img=$(".bord_img"+suffix);
			var src=path_image+fileName;
			img.hide();
			img.attr('src', "/"+src+'?'+time);
			img.fadeIn('slow');
			$('#create_t'+suffix).hide();
			
			$('#bord_img_del'+suffix).attr('href', "/"+src);
			$('#bord_img_del'+suffix).show();
		}
		$('#cut_avatar'+suffix+' .jcrop-holder').remove();		
		$('#current_photo'+suffix+'').val(data.path);		
	}	
}

function display_image_type(id, suffix)
{
	if(id==1)
	{
		$('#cut_avatar'+suffix).show();
		if($('#cut_avatar'+suffix+' .jcrop-holder').length > 0)
		{
			$('#create_t'+suffix).show();
		}
		$('#tb_size'+suffix).show();
		$("#cut_avatar"+suffix).css({'visibility':'visible', 'height':'auto'});
	}
	else if(id==2)
	{
		$('#cut_avatar'+suffix).hide();
		$('#create_t'+suffix).hide();
		$('#tb_size'+suffix).show();
	}
	else if(id==3)
	{
		$('#cut_avatar'+suffix).hide();
		$('#create_t'+suffix).hide();
		$('#tb_size'+suffix).hide();
	}	
}

function createCrop(path_image, fileName)
{
	var width_image=$('#width_image').val();
	var height_image=$('#height_image').val();
	
	if(width_image>1&&height_image>1)
	{
		var time=new Date(); //alert(fileName);
		var position = [ 10, 10, 110, 110 ];
		var type_upload = $('input[name=image_upload_type]:checked').val();
		$("#crop, .jcrop-holder").remove();
		$("#cut_avatar").append("<img src='/"+path_image+fileName+"?"+time+"' id='crop' />");						
		$('#cut_avatar').fadeIn(500);                  
		$("#src").val(fileName);

	
		$("#crop").Jcrop({
			setSelect: position,
			onChange: checkCoord,
			onSelect: checkCoord,
			aspectRatio: width_image/height_image
		});
		
		if(type_upload!=1)$("#cut_avatar").css({'visibility':'hidden', 'height':'1px'});
		else{
			$("#create").css("display", "block");
			$("#create_t").css("display", "block");	
			$("#cut_avatar").css({'visibility':'visible', 'height':'auto'});
		}
	}
}

function createCrop2(path_image, fileName, image_id, flag)
{
	var width_image=$('#width_image_extra').val();
	var height_image=$('#height_image_extra').val();
	
	if(width_image>1&&height_image>1)
	{
		var time=new Date(); //alert(fileName);
		$('#resize_photo_id_extra').val(image_id);
		if(flag)
		{
			$('.bord_img_extra').attr('src', "/"+path_image+fileName+"?"+time);
			
			$('#bord_img_del_extra').attr('href', "/"+path_image+fileName);
			$('#bord_img_del_extra').show();
		}
		
		var position = [ 10, 10, 110, 110 ];
		var type_upload = $('input[name=image_upload_type_extra]:checked').val();
		$("#crop_extra, .jcrop-holder").remove();
		fileName = fileName.replace("_s", "_b");
		$("#cut_avatar_extra").append("<img src='/"+path_image+fileName+"?"+time+"' id='crop_extra' />");						
		$('#cut_avatar_extra').fadeIn(500);

		$("#crop_extra").Jcrop({
			setSelect: position,
			onChange: checkCoord,
			onSelect: checkCoord,
			aspectRatio: width_image/height_image
		});
		
		//if(type_upload==1)$("#cut_avatar_extra").css('display', 'block');
		if(type_upload!=1)
		{
			$("#cut_avatar_extra").css({'visibility':'hidden', 'height':'1px'});
		}
		else{
			$("#create_extra").css("display", "block");
			$("#create_t_extra").css("display", "block");	
			$("#cut_avatar_extra").css({'visibility':'visible', 'height':'auto'});
		}
		$("#src_extra").val(fileName);
		
		var id=$('#resize_photo_id_extra').val();//alert(id);
		
	}
}

function createAvatar(suffix)
{
	//alert(type);
	var id=$('#resize_photo_id'+suffix).val();
	var path=$('#path_image'+suffix).val();
	
	var width=$('#width_image'+suffix).val();
	var height=$('#height_image'+suffix).val();
	
	var width2=$('#width_image2').val();
	var height2=$('#height_image2').val();
	if(suffix=='')var action=$('#action').val();
	else var action=$('#action2').val();
	
    $("#create"+suffix).css("display", "none");
	$("#create_t"+suffix).css("display", "none");
	$('.qq-upload-list').html('');
    $(".jcrop-holder").remove();
    var w, h, x, y, src;
    w = $("#w").val();
    h = $("#h").val();
    x = $("#x").val();
    y = $("#y").val();
    src = $("#src"+suffix).val();

	var image_code=$('#load_img').attr('src');
	//alert(id);
    $.ajax({ url: '/admin/ajax/createphoto', type: 'post', data: {x: x, y: y, w: w, h: h, src: src, image_id: id, width: width, height: height, width2: width2, height2: height2, path:path, image_code:image_code, action:action}, dataType:"json", success: function(data){
        //$("#file-uploader").remove();
       // $("#thumb img").attr("src", "/files/partners/" + data+'?'+time).css("float", "none").parent().css({"display": "block"});
		$(".bord_img"+suffix).attr("src", "/"+path+"/" + data.file+'?'+new Date()).css("float", "none").parent();
		
		$('#bord_img_del'+suffix).attr('href', "/"+path+"/" + data.file);
		$('#bord_img_del'+suffix).show();
    }});
}

function checkCoord(c)
{
	$("#x").val(c.x);
	$("#y").val(c.y);
	$("#w").val(c.w);
	$("#h").val(c.h);
}

function checkExt(ext)
{
	//alert(ext);
	var regex = /^(JPG|jpg|jpeg|png|PNG|bmp|BMP|gif|GIF)$/i;
	if (!regex.test(ext))
	{
		$('.error').html('Please select a valid image file (jpg, png, gif, bmp are allowed)').fadeIn(500);
		return false;
	}	
	else{
		$('.error').html('').fadeOut(500);
		return true;
	}
}

function validateForm()
{
	if ($('#image_file').val()=='') {
        $('.error').html('Please select an image').fadeIn(500);
        return false;
    }else if(isError){
    	return false;
    }else {
    	return true;
    }
}


function showThumbnail(e)
{
	var rx = 155 / e.w; //155 is the width of outer div of your profile pic
	var ry = 190 / e.h; //190 is the height of outer div of your profile pic
	var image_id = $('#usr').val();
	$('#w_'+image_id).val(e.w);
    $('#h_'+image_id).val(e.h);
    $('#x_'+image_id).val(e.x);
    $('#y_'+image_id).val(e.y);

	$('#thumb').css({
		width: Math.round(rx * selectImgWidth) + 'px',
		height: Math.round(ry * selectImgHeight) + 'px',
		marginLeft: '-' + Math.round(rx * e.x) + 'px',
		marginTop: '-' + Math.round(ry * e.y) + 'px'
	});
}