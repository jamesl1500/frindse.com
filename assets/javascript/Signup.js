$(function() {
    var busy = false;
    var folder = '/frindse.com';

    $(document).on('submit', '#signupForm', function(){
        if(busy == false)
        {
            busy = true;

            // Make vars
            var firstname = $("#firstname");
            var lastname  = $("#lastname");
            var username  = $("#username");
            var email     = $("#email");
            var password  = $("#password");

            if(firstname.val() != "" && lastname.val() != "" && username != "" && email != "" && password != "")
            {
                $.post(folder + '/api/auth/register',{firstname: firstname.val(), lastname: lastname.val(), username: username.val(), email: email.val(), password: password.val()}, function(data){
                    var obj = jQuery.parseJSON(data);

                    if(obj.code == 1)
                    {
                        $(".responseHold").html("<div class='response success'>"+obj.status+"</div><br /><br />");
                        busy = false;
                    }else{
                        $(".responseHold").html("<div class='response error'>"+obj.status+"</div><br /><br />");
                        busy = false;
                    }
                });
            }else{
                $(".responseHold").html("<div class='response error'>Please enter all of the fields</div><br /><br />");
                busy = false;
            }
        }
    });

    $(document).on('click', '#actAcc', function(){
        if(busy == false)
        {
            busy = true;

            // Make vars
            var code = $(this).data('c');
            var email  = $(this).data('e');

            if(code.val() != "" && email.val() != "")
            {
                $.post(folder + '/api/auth/activate',{code: code, email: email}, function(data){
                    var obj = jQuery.parseJSON(data);

                    if(obj.code == 1)
                    {
                        window.location.assign(folder + "/timeline");
                    }else{
                        $(".responseHold").html("<div class='response error'>"+obj.status+"</div><br /><br />");
                        busy = false;
                    }
                });
            }else{
                $(".responseHold").html("<div class='response error'>Please enter all of the fields</div><br /><br />");
                busy = false;
            }
        }
    });
});