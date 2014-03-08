$(document).ready( function() {

    $('#test-url').val(window.location.href);

    $('#testing-main').on('click', function() {
        $('#send-report').slideToggle();
    });


    $('#testing-send').on('click', function() {

        var error = 0;

        $('#test-subject').removeClass('error');
        $('#test-text').removeClass('error');

        if($('#test-subject').val()=='') {
            $('#test-subject').addClass('error');
            error +=1;
        }
        if($('#test-text').val()=='') {
            $('#test-text').addClass('error');
            error +=1;
        }

        if(error==0) {

            $('#testing').hide();

            html2canvas($('body'), {
                onrendered: function(canvas) {
                    var img = canvas.toDataURL('image/jpeg');
                    img = img.replace(/^data:image\/(png|jpeg);base64,/, "");
                    $("#test-img").val(img);

                    var url = $('#test-url').val();
                    var subject = $('#test-subject').val();
                    var text = $('#test-text').val();
                    var image = $('#test-img').val();

                    $.ajax({
                        type: "POST",
                        url: "/ajax/testingsend",
                        cache: false,
                        data: { URL: url,
                            SUBJECT: subject,
                            TEXT: text,
                            IMAGE: image} ,
                        success: function (text) {
                            $('#testing').show();
                            $('#send-report').html('<p class="done">Сообщение отправлено!</p>');
                        }
                    });

                }
            });
        }

    });
});