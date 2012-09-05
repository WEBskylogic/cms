// JavaScript Document

function sortList(){

    var ids = [];

    $("#tabl_trannz tr").each(function(){ ids[ids.length] = $(this).attr('id'); });
	 
    $.ajax({

        type: 'POST',

        dataType: 'text',

        url: '/incmodules/MenuEdetInclude.inc.php',

        data: ({ serialized: ids.join(),Table:Table,TB_identif:TB_identif })

    }); 

}

	
$(document).ready(function(){
// Подключаем сортировку
$( "#tabl_trannz" ).sortable({placeholder: "ui-state-highlight", items: "tbody > tr",  axis: 'y',  cursor: 'move',  stop: function(event,ui){ sortList(); }});
 
// Получаем ширину столбцов таблицы
var colWidth = [];
$("#tabl_trannz").find("th").each(function(index, element) {  
    colWidth[index] = $(element).width();
});
 /*
// Устанавливаем ширину ячеек для каждой строки
var colIndex = 0;
var colCount = colWidth.length; 
$("#tabl_trannz").find("tbody > tr > td").each(function(index, element) { 
    element.width(colWidth[colIndex++]);
    if (colIndex == colCount) colIndex = 0;
});			  
	*/	
		 
			$(".edit_category"). blur(function(){
				categoryId = $(this).parent("td").parent("tr").attr("id"); 
				category_name = $(this).attr("value"); 
				$.ajax({type: "post", url: "/incmodules/MenuEdetInclude.inc.php", data: {id: categoryId,catalog:category_name ,Table:Table,TB_identif:TB_identif}});
			return false;
			});	
			
			$(".edet_link").blur(function(){
				categoryId = $(this).parent("td").parent("tr").attr("id");
				category_name = $(this).attr("value");
				ind = $(".edet_link").index($(this));  
				category_title =$(".edit_category:eq("+ind+")").attr("value");
				$.ajax({type: "post", url: "/incmodules/MenuEdetInclude.inc.php", 
				data: {id: categoryId, value:category_name,titl_cat:category_title,Table:Table,TB_identif:TB_identif},
				dataType: "html",
				success: function(text){ 
					// alert(text); 
					$("#edet_url_"+categoryId).val(text);   //alert($("#edet_url_"+categoryId).val());
					$('#message2').show(); 
				} 
			});
			return false;
			});	
			
			 

			$("#loading").ajaxStart(function(){
			   $(this).show();
			   $("#message").hide();
			});
		  	$("#loading").ajaxStop(function(){
		   		$(this).hide();
		  	});
		  	$("#message").ajaxComplete(function(){
		   		$(this).show();
		 	});  
 
});