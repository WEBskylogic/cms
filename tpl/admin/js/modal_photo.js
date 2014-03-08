// JavaScript Document
$(document).ready(function()
{ 
	$('.Logo').click(function()
	{
		var height=140;
		var width=140;
		var path='files/colors';
		var ID = $(this).attr('id');   
		var addhtml =  "<img src='/tpl/images/admin/image_16.gif'  id='"+ID+"'  class='Logo'  border=0 />";	
		//$("#"+ID).replaceWith(addhtml) ;
		
		
		var mas_id=ID.split('_');
		var id = mas_id[1]; 
		//alert(id);
		//--------------------------------
		var slot='<div id="DownloadIframe_'+id+'"><iframe src="/incmodules/photo_frame.php?id='+id+'&width='+width+'&height='+height+'&path='+path+'" width="100%" style="border:none;min-width:650px;min-height:560px;" frameborder="0"></iframe></div>';
		$('#DownloadFotoContent').html(slot);
		//-------------------------------
		$("#DownloadFoto").dialog({
	    position: ["center","center"],
	  	modal :true, 
		height:700,
		dialogClass:'DownloadFoto', 
		width:750,
		closeOnEscape:false, 
	    resizable :true,
		buttons: {	       
	      "Закрыть": function() { 
		   $("#DownloadFoto").dialog("close"); 
	      }
	    },
		close:function() { 	
			$('#DownloadIframe_'+id).remove();
			var URL_img='/files/colors/'+id+'_s.jpg';//alert("#logo"+id);
			if(file_exists(URL_img)==true)$("#logo"+id).attr('src',URL_img) ;
		} 	 
	  });
	}); 
});

function file_exists(url)
{
	// Returns true if filename exists
	//
	// version: 909.322
	// discuss at: http://phpjs.org/functions/file_exists // + original by: Enrique Gonzalez
	// + input by: Jani Hartikainen
	// + improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// % note 1: This function uses XmlHttpRequest and cannot retrieve resource from different domain.
	// % note 1: Synchronous so may lock up browser, mainly here for study purposes. // * example 1: file_exists('http://kevin.vanzonneveld.net/pj_test_supportfile_1.htm');
	// * returns 1: '123'
	
	var req = this.window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
	if (!req) {throw new Error('XMLHttpRequest not supported');}
	// HEAD Results are usually shorter (faster) than GET
	req.open('HEAD', url, false);
	req.send(null);
	if (req.status == 200){ return true;
	}	
	return false;
}