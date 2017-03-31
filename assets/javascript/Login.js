$(function() {
    var busy = false;
    var folder = '/frindse.com';

    $(document).on('submit', '#loginForm', function () {
        if (busy == false) {
            busy = true;

            // Make vars
            var email = $("#email");
            var password = $("#password");

            if (email != "" && password != "") {
                $.post(folder + '/api/auth/login', {
                    email: email.val(),
                    password: password.val()
                }, function (data) {
                    var obj = jQuery.parseJSON(data);

                    if (obj.code == 1) {
                        // Send session data
                        socket.send('{"type":"addNewlyLoggedUser", "sid":"'+obj.sid+'", "sid_s":"'+obj.sid_s+'"}');
                        
                        $(".responseHold").html("<div class='response success'>" + obj.status + "</div><br /><br />");
                        busy = false;
                    } else {
                        $(".responseHold").html("<div class='response error'>" + obj.status + "</div><br /><br />");
                        busy = false;
                    }
                });
            } else {
                $(".responseHold").html("<div class='response error'>Please enter all of the fields</div><br /><br />");
                busy = false;
            }
        }
    });
});