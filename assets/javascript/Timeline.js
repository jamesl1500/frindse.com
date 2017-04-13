$(document).ready(function(){
    var folder = "/frindse.com";
    var type = 'text';
    var i = 0, img, reader, file, busyyo = false, b = false, postBusy = false, busy = false;

    function _(el) {
        return document.getElementById(el);
    }
    
    $(document).on('submit', '#postingForm', function(){
        if(postBusy == false)
        {
            postBusy = true;

            // Vars
            var body = $("#timelinePostingStationBody");
            var userTo = $("#upt");
            var privacy = $("#def-privacy");
            var formBtn = $("#timelineBtn");

            // Change button
            formBtn.addClass("busyBTN");
            formBtn.removeClass("primaryBTN");

            formBtn.html("<i class='fa fa-circle-o-notch fa-spin fa-fw'></i>");

            // Now do some validation and render where to send the post
            if(body.val() != "" && userTo.val() != "" && privacy.val() != "")
            {
                // Now lets just send the info to the server and get some info back and use our post plugin to display the post back
                if(type == "text") {
                    $.post(folder + "/api/posts/makeTextPost", {userTo: userTo.val(), postBody: body.val(), privacy: privacy.val()}, function (data) {
                        var obj = jQuery.parseJSON(data);

                        if(obj.code == 1)
                        {
                            $("#timelinePostsHold").Post("#timelinePostsHold", type, obj, 'sal');
                            $(".global_response_holder").Alert("Posted Successfully", "success");

                            // Fix button back
                            formBtn.addClass("primaryBTN");
                            formBtn.removeClass("busyBTN");

                            formBtn.html("Post");

                            postBusy = false;
                        }else{
                            $(".global_response_holder").Alert(obj.status, "error");

                            // Fix button back
                            formBtn.addClass("primaryBTN");
                            formBtn.removeClass("busyBTN");

                            formBtn.html("Post");

                            postBusy = false;
                        }
                    });
                }else if(type == "video"){
                    $.post("", {}, function (data) {

                    });
                }else if(type == "photo"){
                    $.post("", {}, function (data) {

                    });
                }else if(type == "original-video"){
                    $.post("", {}, function (data) {

                    });
                }
            }else{
                $(".global_response_holder").Alert("Please enter some text!", "warning");

                // Fix button back
                formBtn.addClass("primaryBTN");
                formBtn.removeClass("busyBTN");

                formBtn.html("Post");

                postBusy = false;
            }
        }
    });

    $(document).on('change', '#timelinePhotoSelect', function () {
        var filedata = _("timelinePhotoSelect"),
            formdata = false,
            len = filedata.files.length;

        if (window.FormData) {
            formdata = new FormData();
        }

        for (; i < len; i++) {
            file = filedata.files[i];

            if (window.FileReader) {
                reader = new FileReader();
                reader.onloadend = function (e) {
                    $(".allPhotos").append("<img src=" + e.target.result + " style='padding: 3px;' height='100' width='100'/>");
                };
                reader.readAsDataURL(file);
            }

            if (formdata) {
                formdata.append("photourl", file);
                type = "photo";
            }
        }
    });

    $(document).on('change', '#timelineVideoSelect', function () {
        var videoNode = _('videoPreviewCont');
        var url = window.URL || window.webkitURL;
        var filedata = _("timelineVideoSelect"),
            formdata = false,
            len = filedata.files.length;


        if (window.FormData) {
            formdata = new FormData();
        }

        file = filedata.files[0];
        var fileSize = file.size; //size in kb
        fileSize = fileSize / 1048576;

        if (fileSize <= 90) {
            videoNode.src = url.createObjectURL(file);
            $(".videoPreview").removeClass('hidden');

            if (formdata) {
                formdata.append("videourl", file);
                type = "original-video";
            }
        } else {
            $(".global_response_holder").Alert("Videos can only be under 90 MB's", 'error');
            _("timelineVideoSelect").val("");
        }
    });

    $(document).on('click', '#cancelPhotoUpload', function () {
        var photo = $("#timelinePhotoSelect");
        photo.val("");

        type = "text";
        $('#previewImg').attr('src', '');
        $('#previewImg').fadeOut('fast');
        $('#previewImg').addClass('hidden');
        return false;
    });

    $(document).on('click', '#open', function () {
        $(".videoUploader").fadeIn("fast");
        return false;
    });

    $(document).on('click', '#open2', function () {
        $(".videoUploader").fadeIn("fast");
        return false;
    });

    $(document).on('click', '.timelineFeedSelector', function () {
        var open = $(this).data('open');

        if (open != "") {
            $(".conts").addClass('hidden');
            $(".timelineFeed").children('.' + open).removeClass('hidden');

            $(".timelineFeedSelector").removeClass("a");
            $(this).addClass('a');
        }
    });

    $(document).on('click', '#cancel2', function () {
        $("#videoLink").val("");

        type = "text";
        $(".videoUploader").fadeOut("fast");
        $(".overlay").fadeOut("fade");
        return false;
    });

    $(document).on('click', '#cancel', function () {
        var photo = $("#timelinePhotoSelect");
        photo.val("");

        type = "text";
        $('#previewImg').attr('src', '');
        $('#previewImg').fadeOut('fast');
        $(".photoUploader").fadeOut("fast");
        $(".overlay").fadeOut("fade");
        return false;
    });

    $(document).on('click', '.openPrivTab', function(){
        var privDrop = $(".privacyDrop");

        if(privDrop.hasClass("hidden"))
        {
            privDrop.removeClass('hidden');
        }else{
            privDrop.addClass('hidden');
        }
    });

    $(document).on('click', '.privTabSetting', function(){
        var t = $(this);

        var currentPriv = $("#def-privacy").val();
        var thisPriv = t.data('priv');

        if(thisPriv != "")
        {
            $(".currentSetting").html(t.data('val'));
            $("#def-privacy").val(thisPriv);

            $(".privTabSetting").removeClass("privActive");
            t.addClass("privActive");
        }
    });
});