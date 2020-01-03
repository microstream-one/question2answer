(function () {
    "use strict";

    var out_url_layout = $('#out_url_layout').val(),
        qa_user_is_logged = parseInt($('#qa_user_is_logged').val(), 10),
        out_user_is_logged = (out_user_id !== false) ? 1 : 0,
        qa_host = $('#qa_host').val();

    $.get(out_url_layout, {h:qa_host, u:out_user_is_logged}, function (data) {
        // console.log(data);
        $('#out_header').html(data.header);
        $('#out_footer').html(data.footer);
        $('#out_user_box').html(data.user_message);
    }, 'JSON');

    $('#out_header .navbar-toggle').click(function () {
        var target = $(this).data('target');
        $(target).slideToggle(100);
    });

    if ((out_user_id !== false) && (qa_user_is_logged === 0)) {
        $.post('/?qa=check-user', {uid:out_user_id}, function (data) {
            // console.info(data.user);
            if (data.user === true) {
                location.href = out_identity_url;
            } else if (data.user === false) {
                $('#out_user_box').slideDown(500);
            }
        }, 'json');
    }
})();