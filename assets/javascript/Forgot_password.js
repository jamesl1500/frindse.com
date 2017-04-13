$(function(){
    var busy = false;
    var folder = '/frindse.com';

    $(document).on('submit', '#fpForm', function(){
        if(busy == false)
        {
            busy = true;

            // Make vars
            var email = $("#email");

            if(email.val() != "")
            {
                $.post(folder + '/api/users/initiatePasswordRecovery',{email: email.val()}, function(data){
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

    $(document).on('submit', '#cpForm', function(){
        if(busy == false)
        {
            busy = true;

            // Make vars
            var email = $("#feh");
            var code = $("#fch");
            var password1 = $("#password1");
            var password2 = $("#password2");

            if(email.val() != "" && code.val() != "" && password1.val() != "" && password2.val() != "")
            {
                $.post(folder + '/api/users/recoverPassword',{code: code.val(), email: email.val(), password1: password1.val(), password2: password2.val()}, function(data){
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
});