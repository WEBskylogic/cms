$(document).ready(function()
{
	$('.amount').live('change', function(){
		$('#update_order').submit();
	});
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

    // Изменение количества в карточке товара
    $('.countchange').live('click', function() {

        var min = 1;
        var max = parseInt( $('#cnt').attr('max'));
        var current = parseInt( $('#cnt').val());
        var cnt = current;

        var price = parseInt($('#fixprice').val());

        if($(this).hasClass('cnt-up') && (current < max)) {
                var cnt = current + 1;
        }

        if($(this).hasClass('cnt-down') && (current > min)) {
                var cnt = current - 1;
        }

        var newprice = cnt*price;
        $('#cnt').val(cnt);
        $('#total').html(numToPrice(newprice));

    });

    function numToPrice(x) {
        return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
    }

	///////Clear input search
	$('input[name=search]').live('click', function(){
		if($(this).val()=='Search'||$(this).val()=='Поиск')$(this).val('');
	});
	
	///////Clear input mailer
	$('input[name=mailer]').live('click', function(){
		if($(this).val()=='E-mail')$(this).val('');
	});
});

function windowHeight()
{
	var de = document.documentElement;
	return self.innerHeight || ( de && de.clientHeight ) || document.body.clientHeight;
}
function windowWidth()
{
	var de = document.documentElement;
	return self.innerWidth || ( de && de.clientWidth ) || document.body.clientWidth;
}

function IsValidateEmail(email)
{
      var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,6})$/;
      return reg.test(email);
}
