$(document).ready(function () {
    bascket();

    /////Subscribers
    $('#subscribe_form').live('submit', function () {

        var name = $('#subscribe-name').val();
        var email = $('#subscribe-email').val();
        var dataString = 'name=' + name + '&email=' + email;
        $.ajax({type: "POST", url: "/ajax/mailer/subscribers", dataType: 'json', data: dataString, cache: false, success: function (data) {
            if (data.err) {
                $.stickr({note: data.err, className: 'prev', position: {right: '42%', bottom: '60%'}, time: 2000, speed: 300});
            }
            else {
                $.stickr({note: data.message, className: 'next', position: {right: '42%', bottom: '60%'}, time: 2000, speed: 300});
            }
        }});
        return false;
    });

    /////Add to delivery prices
    $('#delivery').live('change', function () {
        Z
        var id = $(this).val();
        var dataString = 'id=' + id;
        $.ajax({type: "POST", url: "/ajax/delivery/deliveryprice", data: dataString, cache: false, success: function (html) {
            $('#deliver_price').html(html);
        }});
    });

    x = '';
    $("#live-search-input").live('keyup', function (e) {
        clearTimeout(x);
        if ($(this).val().length > 1) {
            var word = $(this).val();
            x = setTimeout(function () {
                autosearch(word);
            }, 300);
        }
        else $('#results_search').css('display', 'none');
    });

    $("#results_search li").live('click', function () {
        $('#results_search').hide(200);
        $("#search_query_top").val($(this).html());
        $("#searchbox").submit();
    });

    $("body").live('click', function () {
        $('#results_search').hide(200);
    });

    //Выбор цвета, размера в карточке товара
    $('.param-sel').on('click', function () {
        $(this).parent().find('div').each(function () {
            $(this).removeClass('selected');
        })
        $(this).addClass('selected');

        var type = 'color';
        if ($(this).hasClass("size_select"))type = 'size';
        else if ($(this).hasClass("power_select"))type = 'power';


        var id = $(this).attr('id');
        var product_id = $('#product_id').val();
        var dataString = 'id=' + id + '&product_id=' + product_id;
        $.ajax({type: "POST", url: "/ajax/product/loadparam", data: dataString, dataType: "json", cache: false, success: function (data) {
            $('.param-sel').addClass('noactive');
            for (var i = 0; i < data.option.length; i++) {
                $('#param-' + data.option[i]).removeClass('noactive');
            }
            $('#' + id).removeClass('noactive');
        }});

    })
});

function autosearch(word) {
    var dataString = 'search=' + word;
    $.ajax({type: "POST", url: "/ajax/livesearch", data: dataString, cache: false, success: function (data) {
        $('#results_search').html(data);
        $('#results_search').show(200);
    }});
}

/////Add to shop cart
$('.buy').live('click', function () {

    var id = $(this).attr('name');
    var amount = $('#cnt').val();
    var color = $('.color_select.selected').attr('id');
    var size = $('.size_select.selected').attr('id');

    if ($('.color_select').length > 0 && color == undefined) {
        $.stickr({note: 'Выберите цвет!', className: 'err', position: {right: '42%', bottom: '60%'}, time: 1000, speed: 300});
    }
    else if ($('.size_select').length > 0 && size == undefined) {
        $.stickr({note: 'Выберите размер!', className: 'err', position: {right: '42%', bottom: '60%'}, time: 1000, speed: 300});
    }
    else {
        var dataString = 'id=' + id + '&amount=' + amount + '&color=' + color + '&size=' + size;
        $.ajax({type: "POST", url: "/ajax/orders/incart", data: dataString, dataType: "json", cache: false, success: function (data) {
            $('#count_bascket').html(data.count);
            $('#sum_bascket').html(data.total);
            $.stickr({note: 'Товар добавлен!', className: 'next', position: {right: '42%', bottom: '60%'}, time: 1000, speed: 300});
        }});
    }
});

///Bascket
function bascket() {
    $.ajax
    ({
        type: "POST",
        url: "/ajax/orders/bascket",
        cache: false,
        success: function (html) {
            $("#bascket").html(html);
        }
    });
}

////Add comments
$('#form_comment').live('submit', function () {
    var id = $("#id_comment").val();
    var type = $("#type_comment").val();
    var name = $("#name_comment_form").val();
    var message = $("#text_comment_form").val();
    var photo = $("#avatar").val();
    var err = false;
    if (name == '') {
        $("#name_comment_form").css('border', '1px solid red');
        err = true;
    }
    else $("#name_comment_form").attr('style', '');
    if (message == '') {
        $("#text_comment_form").css('border', '1px solid red');
        err = true;
    }
    else $("#text_comment_form").attr('style', '');

    if (!err) {
        var dataString = 'id=' + id + '&type=' + type + '&name=' + name + '&message=' + message + '&photo=' + photo;
        $.ajax({type: "POST", url: "/ajax/comments/addcomment", data: dataString, dataType: 'json', cache: false, success: function (data) {
            if (data.list != null) {
                $("#comment_block").replaceWith(data.list);
            }
            $("#input").html(data.message);
            $("#text_comment_form").val('');
        }});
    }
    return false;
});


//login pop-up
function check_auth() {
    var email = $('#email_auth').val();
    var pass = $('#pass_auth').val();
    var dataString = 'email=' + email + '&pass=' + pass;
    $.ajax({type: "POST", url: "/ajax/users/checkauth", data: dataString, dataType: "json", cache: false, success: function (data) {
        if (data.auth == 1) {
            window.setTimeout('location.reload()', 3000);
        }
        $('#auth_message').html(data.message);
    }});
    return false;
}


///Send form feedback
function sendFeedback() {
    //$("#loader").css('display', 'block');
    var name = $("#f_name").val();
    var email = $("#f_email").val();//alert(email);
    var phone = $("#f_phone").val();//alert(email);
    var message = $("#f_message").val();

    var dataString = 'name=' + name + '&email=' + email + '&phone=' + phone + '&message=' + message;
    $.ajax({type: "POST", url: "/ajax/feedback/feedback", data: dataString, dataType: 'json', cache: false, success: function (html) {
        $("#message").html(html[1]);

        if (html[0] == 1) {
            $("#f_name").val('');
            $("#f_email").val('');//alert(email);
            $("#f_phone").val('');
            $("#f_message").val('');
            //closeFeedback(5000);
        }

    }});
    //$("#message").html(html);
    $("#loader").css('display', 'none');
    return false;
}

////Add Mailto
function mail_to() {
    var email = $("input[name=mailer]").val();
    var dataString = 'email=' + email;
    $.ajax({type: "POST", url: "/ajax/mailer/mailto", data: dataString, cache: false, success: function (html) {
        $("#message_mailer").html(html);
    }});
    return false;
}


/*Filters*/
function get_product() {
    show_loading();
    var items = [];
    $('.filter .checked').each(function () {
        var option = $(this).attr('data-id');
        option += '|' + $(this).attr('data-group');
        if (option != 0) {
            items.push(option);
        }
    });
    var cat_id = $('#curr_cat').attr('title');
    var url = $('#curr_cat').attr('url');
    var dataString = 'items=' + items + '&cat_id=' + cat_id;

    $.ajax({type: "POST", url: "/ajax/catalog/getfilter", data: dataString, dataType: 'json', cache: false, success: function (data) {
        $('#load_filter').html(data.filters);
        $('#load_product').html(data.product);
        hide_loading();
    }});
}

function show_loading() {
    $('#load_product').css('opacity', 0.2);
    $('#load_product').append('<img src="/tpl/default/images/loading.gif" id="loading" />');
}

function hide_loading() {
    $('#loading').remove();
    $('#load_product').css('opacity', 1);
}

$('.set_params').live('click', function () {
    if (!($(this).hasClass('uncheck'))) {
        if ($(this).hasClass("checked"))$(this).removeClass('checked');
        else $(this).addClass('checked');
        get_product();
        get_params_url();
    }
    else return false;
});

$('.clear_param').live('click', function () {

    var id = $(this).attr('data-id');

    var cat_id = $('#curr_cat').attr('title');
    var dataString = 'clear_id=' + id + '&cat_id=' + cat_id;

    $.ajax({type: "POST", url: "/ajax/catalog/getfilter", data: dataString, dataType: 'json', cache: false, success: function (data) {
        $('#load_filter').html(data.filters);
        $('#load_product').html(data.product);
        get_params_url();
    }});
});

function get_params_url() {
    var url = $('#curr_cat').attr('url');
    var params = '';
    var sub_params = '';

    $('.checked').each(function () {
        if (sub_params != '') {
            sub_params += ';';
        }
        sub_params += $(this).attr('data-id') + '-' + $(this).attr('url');
    });

    if (sub_params != '') {
        params = '/params/' + sub_params;
    }

    history.pushState(null, null, '/catalog/' + url + params);
}


$('#close_btn').live('click', function () {
    $('#registration_form').remove();
    $('#login_form').remove();
    $('#overlay').fadeOut('slow', function () {
        $('#overlay').css('display', 'none');
    });
});

function showList(id) {
    if ($(id).css('display') == 'none') {
        $(id).show(200);
    }
    else {
        $(id).hide(200);
    }
    return false;
}

function showList2(id) {
    if ($('.' + id).css('display') == 'none') {
        $('.sub_ul').hide(200);
        $('.' + id).show(200);
    }
    else {
        $('.' + id).hide(200);
    }
    return false;
}
