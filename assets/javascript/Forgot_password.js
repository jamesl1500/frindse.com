$(function(){
    var busy = false;
    var folder = '/frindse.com';

    $(document).on('submit', '#fpForm', function(){
        if(busy == false)
        {
            busy = true;

            // Make vars
            var email = $("#email");

            if(email != "")
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
});