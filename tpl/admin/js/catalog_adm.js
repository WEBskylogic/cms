// JavaScript Document
$(document).ready(function(){
			
			simpleTreeCollection = $('.simpleTree').simpleTree({
				autoclose: false,
				afterClick:function(node){
				},
				afterMove:function(){	
					var serialStr = "";
					var order = "";
					//alert ($("ul.simpleTree li span").length)
					$("ul.simpleTree li span").each(function(){			
						parentId = $(this).parent("li").parent("ul").parent("li").attr("id");
						if (parentId == undefined) parentId = "root";
						order = (($(this).parent("li").prevAll("li").size()+1))/2; 
						if ( parentId != "root") serialStr += ""+parentId+":"+$(this).parent("li").attr("id")+":"+order+"|";
					});
					 
					
					$.ajax({
					   type: "POST",
					   url: "/incmodules/CatalogEdetInclude.inc.php",
					   data: { serialized:serialStr,Table:Table,TB_identif:TB_identif},
				success: function(text){  
					info_box('success','Изменения Сохранены.')
				} 
					   
					 });
			
					return false;
					
				},
				docToFolderConvert: true,
				afterAjax:function()
				{
					//alert('Loaded');
				},
				animate:true
			});	
	 
		 
			$(".edit_category"). blur(function(){
				categoryId = $(this).parent("li").attr("id");
				category = $(this).attr("value");
				//alert("edit  "+categoryId + category_name);
				$.ajax({type: "post", url: "/incmodules/CatalogEdetInclude.inc.php", data: {id: categoryId,category:category_name ,Table:Table,TB_identif:TB_identif},
				success: function(text){  
					info_box('success','Изменения Сохранены.')
				} });
			return false;
			});
			$(".edet_code"). blur(function(){
				categoryId = $(this).parent("li").attr("id");
				code = $(this).attr("value"); 
				$.ajax({type: "post", url: "/incmodules/CatalogEdetInclude.inc.php", 
				data: {id: categoryId,code:code,Table:Table,TB_identif:TB_identif},
				success: function(text){  
					info_box('success','Изменения Сохранены.')
				} 
				});
			return false;
			});
				
			
			$(".edet_link").blur(function(){
				categoryId = $(this).parent("li").attr("id");
				category_name = $(this).attr("value");
				ind = $(".edet_link").index($(this));  
				category_title =$(".edit_category:eq("+ind+")").attr("value");
				$.ajax({type: "post", url: "/incmodules/CatalogEdetInclude.inc.php", 
				data: {id: categoryId, value:category_name,titl_cat:category_title,Table:Table,TB_identif:TB_identif},
				dataType: "html",
				success: function(text){ 
					// alert(text); 
					$("#edet_url_"+categoryId).val(text);   //alert($("#edet_url_"+categoryId).val());
					info_box('success','Изменения Сохранены.')
				} 
			});
			return false;
			});	
			
			 /*

			$("#loading").ajaxStart(function(){
			   $(this).show();
			   $("#message").hide();
			});
		  	$("#loading").ajaxStop(function(){
		   		$(this).hide();
		  	});
		  	$("#message").ajaxComplete(function(){
		   		$(this).show();
		 	});  */
			
$("ul.simpleTree li span").each(function(){	
	var parentClass = $(this).parent("li").parent("ul").css("display"); 
	if( parentClass == 'none' ) 
		{ //alert (parentClass);
			$(this).parent("li").parent("ul").parent("li").attr("class",'folder-open');
			$(this).parent("li").parent("ul").attr("style",'display: block;');
		}
	
});

		});