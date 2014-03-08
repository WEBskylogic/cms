function createUploader()
{
	var uploader = new qq.FileUploader({
	element: document.getElementById('file-uploader'),
	action: '/admin/ajax/importexel/uploadfile',
	onComplete: function(id, fileName, responseJSON){
		load_file();
	}});           
}

function load_file()
{
	//alert('asd');
	var id =1;//alert(amount);
	var dataString = 'id='+id;
	$.ajax({type: "POST",url: "/admin/ajax/importexel/insertproducts",data: dataString,cache: false,success: function(data)
	{
		$('#load_cols').html(data);
	}});
}