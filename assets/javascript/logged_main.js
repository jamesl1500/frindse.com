/*
 * Main Javascript File
 * ----
 * This is the main javascript file that will initiate everything
 */
var folder = '/frindse.com';
var busy = false;

// Lets first figure out if there is a logged user
function checkLoginStatus()
{

}

// Start main socket
var socket = new WebSocket("ws://localhost:8083");

socket.onopen = function(e) {
    console.log("Connection established!");
};

socket.onmessage = function(e) {
    console.log(e.data);

    var obj = jQuery.parseJSON(e.data);

    if (obj.type != "")
    {
        switch (obj.type)
        {
            // New friends online
            case  'newOnlineFriend':
                $(".a_holder").notifications("Your friend " + obj.friendFirstname + " " + obj.last_name + " just logged in!", obj);
                break;

            // Post liking
            case  'newPostLike':
                $(".a_holder").notifications("" + obj.fromFirstname + " " + obj.fromLastname + " liked your post!", obj);
                break;
        }
    }
};

/* WEBSITE MAIN CONTAINER */
var website = $(".website-main");
var documentHeight = $(document).height();

website.css('height', documentHeight);

/* NAVBAR & HEADER & sidebar */
$(document).on('click', '.sidebarOpen', function(){
    var sidebar = $(".sidebar");
    var website = $(".website-main");
    var website_overlay = $(".website-overlay");

    var sidebarWidth = sidebar.width();

    if(sidebar.hasClass("sidebar-closed"))
    {
        // Means the sidebar is closed
        $("html").css('overflow', 'hidden');

        website.animate({left: sidebarWidth}, 200);
        website_overlay.fadeIn('fast');

        sidebar.removeClass("sidebar-closed");
        sidebar.addClass("sidebar-opened");
    }else{
        // Means the sidebar is closed
        website.animate({left: 0}, 200);
        website_overlay.fadeOut('fast');

        $("html").css('overflow', 'scroll');

        sidebar.removeClass("sidebar-opened");
        sidebar.addClass("sidebar-closed");
    }
});

$(document).on('click', '.navOpen', function(){
    var t = $(this);
    var open = t.data('open');

    if(open != "")
    {
        var openEle = $("." + open);

        if(openEle.hasClass('hidden'))
        {
            // Means its already closed, but we gotta close the others
            $(".navBox").each(function(){
                $(this).addClass("hidden");
            });

            // Now remove the hidden class from this one
            openEle.removeClass('hidden');
        }else{
            // Means its already open, just add the class
            openEle.addClass('hidden');
        }
    }
});

var page = $("#page").val();
$("#" + page).children('li').addClass('active');

/* HEADER SEARCH */
$(document).on('click', '.searchTabbing', function(){
    var open = $(this).data('open');
    var close = $(this).data('close');

    if(open != "")
    {
        $(".searchTabbing").removeClass('filterResultsNavActive');
        $("." + close).addClass("hidden");
        $(this).addClass("filterResultsNavActive");
        $("." + open).removeClass("hidden");
    }
    return false;
});

$("#searchMainField").keyup(function () {
    $(".navBox").addClass('hidden');
    var data2 = $.trim($("#searchMainField").val());
    var len = data2.length;

    if (len > 20) {
        $("#name").text(data2.substr(0, 20) + '...');
    } else {
        $("#name").text(data2);
    }
    if (data2 != "") {
        $(".searchAllResultsHrefMain").attr('href', folder + '/api/search/main' + data2);
        $(".dropdown-mod").addClass('hidden');
        $(".search_results").fadeIn('fast');

        $(".user_search_list").html("<center><span style='font-size: 24px;padding: 10px;color: #000;'><i class='fa fa-spinner fa-pulse'></i></span></center>");
        $.post(folder + "/api/search/main", {searchUser: data2}, function (data) {
            var obj = jQuery.parseJSON(data);
            if (obj.code == 1) {
                var users_payload = obj['users_payload'];
                var cliques_payload = obj['cliques_payload'];

                var user_count = users_payload['count'];
                var cliques_count = cliques_payload['count'];

                // Render users
                if (user_count > 0) {
                    $('.users_results').fadeIn('fast');

                    if (user_count >= 10) {
                        $(".searchAllUsersResults").fadeIn('fast');
                        $(".searchAllResultsHrefUsers").text('See all ' + user_count + ' results');
                        $(".searchAllResultsHrefUsers").attr('href', folder + '/search/for/users/' + data2);
                    } else {
                        $(".searchAllUsersResults").fadeOut('fast');
                    }
                    // Render actual user divs
                    var temp = "";

                    $.each(users_payload['payload'], function (idx, object) {
                        temp += "<li class='searchUserObject clearfix' style='background-image: url(" + folder + "/users/data/" + object['salt'] + "/banner);background-size: 100% auto;background-position: center;'>";
                        temp += "<div class='inner_search_object clearfix' style='padding: 5px;background: rgba(0,0,0,.3);'>";
                        temp += "<div class='profilePictureSearch'>";
                        temp += "<img src='" + folder + "/users/data/" + object['salt'] + "/profile_picture' />";
                        temp += "</div>";
                        temp += "<div class='rightInformation'>";
                        temp += "<h3 style='color: #fff;'><a style='color: #fff;' href='" + folder + "/profile/" + object['username'] + "'>" + object['first_name'] + " " + object['last_name'] + "</a></h3>";
                        temp += "<h4 style='color: #fff;'>@" + object['username'] + "</h4>";
                        temp += "</div>";
                        temp += "</div>";
                        temp += "</li>";
                    });

                    // Append
                    $(".user_search_list").html(temp);
                } else if (user_count == 0) {
                    $('.users_results').fadeOut('fast');
                    $(".users_found_counter").html(user_count);
                }

                if (cliques_count > 0) {

                    if (cliques_count >= 10) {
                        $(".searchAllCliqueResults").fadeIn('fast');
                        $(".searchAllResultsHrefCliques").text('See all ' + cliques_count + ' results');
                        $(".searchAllResultsHrefCliques").attr('href', folder + 'search/for/cliques/' + data2);
                    } else {
                        $(".searchAllCliqueResults").fadeOut('fast');
                    }

                    // Render the template
                    var template = "";

                    $.each(cliques_payload['payload'], function (idx, object) {
                        template += "<li class='searchUserObject clearfix' style='padding: 0px;background-image: url(" + folder + "clique_data/" + object['c_x'] + "/clique_banners/" + object['clique_banner_pic'] + ");background-size: 100% auto;background-position: center;'>";
                        template += "<div class='inner_search_object searchBackground clearfix' style='padding: 5px;'>";
                        template += "<div class='profilePictureSearch'>";
                        template += "<img src='" + folder + "clique_data/" + object['c_x'] + "/clique_profile_pic/" + object['clique_profile_pic'] + "' />";
                        template += "</div>";
                        template += "<div class='rightInformation'>";
                        template += "<h3><a  style='color: #fff;' href='" + folder + "clique/" + object['clique_username'] + "'>" + object['clique_name'] + "</a></h3>";
                        template += "<h4 style='color: #fff;'>" + object['clique_privacy'] + " &middot; " + object['count_members'] + " members</h4>";
                        template += "</div>";
                        template += "</div>";
                        template += "</li>";
                    });

                    // Append
                    $(".cliques_search_list").html(template);
                } else if (cliques_count == 0) {
                    $('.cliques_results').fadeOut('fast');
                    $(".cliques_found_counter").html(cliques_count);
                }
            } else {

            }
        });
    } else {
        $(".user_search_list").html('');
        $(".search_results").fadeOut('fast');
        $('.cliques_results').addClass('hidden');
        $('.users_results').removeClass('hidden');

        $(".searchTabbing").removeClass("filterResultsNavActive");
        $(".searchTabbing:first").addClass("filterResultsNavActive");
        $("#name").html('');
    }
});

/* POSTS ACTIONS SYSTEMS */
// Likes
$(document).on('click', '.postLikeListener', function(){
   if(busy != true)
   {
       var t = $(this);
       var type = t.data('type');
       var pid = t.data('pid');

       busy = true;

       if(pid != "")
       {
           if (type == "like")
           {
               t.html('<i class="fa fa-circle-o-notch fa-spin"></i>');
               $.post(folder + "/api/posts/likePost", {pid: pid, tag: type}, function(data){
                   var obj = jQuery.parseJSON(data);

                   if(obj.code == 1)
                   {
                       t.addClass('hidden');
                       t.siblings().removeClass('hidden');
                       t.html('<i class="fa fa-heart"></i> Like');
                       $(".countHolder" + pid).html('<font color="#e74c3c">' + obj.status + '</font>');

                       if (obj.isThereAchievement == 1) {
                           //var ach = obj['achievements'];
                           //$(".a_holder").achievements(ach['points'], ach['text'], ach['icon']);
                       }
                       socket.send('{"type":"newPostLike", "sendTo":"'+obj.sendTo+'", "fromSalt":"'+obj.fromSalt+'", "fromFirstname":"'+obj.fromFirstname+'", "fromLastname":"'+obj.fromLastname+'", "fromUsername":"'+obj.fromUsername+'"}');
                       busy = false;
                   }else{
                       $(".global_response_holder").Alert(obj.status);
                       busy = false;
                   }
               });
           } else if (type == "unlike")
           {
               t.html('<i class="fa fa-circle-o-notch fa-spin"></i>');
               $.post(folder + "/api/posts/unlikePost", {pid: pid, tag: type}, function(data){
                   var obj = jQuery.parseJSON(data);

                   if(obj.code == 1)
                   {
                       t.addClass('hidden');
                       t.siblings().removeClass('hidden');
                       t.html('<i class="fa fa-heart"></i> Unlike');
                       $(".countHolder" + pid).html('<font color="#e74c3c">' + obj.status + '</font>');

                       if (obj.isThereAchievement == 1) {
                           var ach = obj['achievements'];
                           $(".a_holder").achievements(ach['points'], ach['text'], ach['icon']);
                       }
                       busy = false;
                   }else{
                       $(".global_response_holder").Alert(obj.status);
                       busy = false;
                   }
               });
           }
       }else{
           busy = false;
       }
   }
});
// Comments
$(document).on('click', '.commentMaker', function () {
    var id = $(this).data('id');

    if (id != "") {
        var form = $("#commentFormHolder" + id);

        if (form.hasClass('hidden')) {
            $("#commentTextArea" + id).focus();
            form.removeClass('hidden');
        } else {
            form.addClass('hidden');
        }
    }
});

$(document).on('submit', '.commentForm', function () {
    if (busy != true) {
        var text = $(this).children('.commentMakerAssets').children().val();
        var id = $(this).children('.commentMakerAssets').children().data('id');
        var num = $("#commentNumber" + id);
        busy = true;

        if (text != "" && id != "") {
            $(this).children('.comemntTextArea').val('');
            $.post(site_url + 'api/posts/comments/new', {id: id, body: text}, function (data) {

                var obj = jQuery.parseJSON(data);
                var response = obj['code'];
                var status = obj['status'];
                var user_data = obj['user_data'];
                var comment_data = obj['comment_data'];
                var timeline_data = obj['timeline_data'];

                if (response == 1) {
                    // make the template
                    var comment = "<div class='comment clearfix' id='commentCont" + comment_data['comment_id'] + "' style=''>";
                    comment += "<div class='profilePicLeft' style='float: left;'>";
                    comment += "<div class='' style='background-image: url(" + site_url + "user_data/" + user_data['salt'] + "/profile_pictures/" + user_data['profile_pic'] + ");background-size: cover;'></div>";
                    comment += "</div>";
                    comment += "<div class='rightPerson' style='font-size: 14px;'>";
                    comment += "<h3 style='font-size: 14px;'><a href='" + site_url + "profile/" + user_data['username'] + "'>" + user_data['first_name'] + " " + user_data['last_name'] + "</a> <span style='float: right;font-weight: 400;color: #aaa'>Now</span></h3>";
                    comment += "<p style=''>" + comment_data['comment_body'] + "</p>";
                    comment += "<div class='actions'>";
                    comment += "<a href='#' style='color: #e74c3c;' id='cmtliker" + comment_data['comment_id'] + "' class='cliker tooltips unlikeCommentMaker hidden' data-type='unlikeComment' data-cid='" + comment_data['comment_id'] + "' data-userby='" + comment_data['user_id'] + "'><i class='fa fa-heart'></i> <span style='color: #e74c3c;' class='counter" + comment_data['comment_id'] + "'>0</span></a>";
                    comment += "<a href='#' style='' id='cmtliker" + comment_data['comment_id'] + "' class='cliker tooltips likecommentMaker' data-type='likeComment' data-cid='" + comment_data['comment_id'] + "' data-userby='" + comment_data['user_id'] + "'><i class='fa fa-heart'></i> <span class='counter" + comment_data['comment_id'] + "'>0</span></a> &middot; ";
                    comment += "<a style='font-weight: 400;' href='#' class='commentRemover' data-cid='" + comment_data['comment_id'] + "'><i class='fa fa-trash-o'></i></a>";
                    comment += "</div>";
                    comment += "</div>";
                    comment += "</div>";
                    if (num.val() > 5) {
                        var t = $(".showAllComments" + id);
                        t.text("Loading Comments...");
                        $.post(site_url + "api/posts/comments/viewAllComments", {pid: id}, function (data) {
                            t.fadeOut('slow');
                            $(".commentHolder" + id).html(data);
                        });
                    }else if(num.val() == 0){

                    }
                    $(".commentHolder" + id).css('display', 'block');
                    $(".commentHolder" + id).append(comment);

                    if (obj.isThereAchievement == 1) {
                        var ach = obj['achievements'];
                        $(".a_holder").achievements(ach['points'], ach['text'], ach['icon']);
                    }
                    busy = false;
                } else if (response == 0) {
                    alert(status);
                }
            });
        }
    }
});

/* CUSTOM PLUGINS */
// Achievements
$.fn.achievements = function (points, message, icon) {
    var t = $(this);
    // Now make thee request
    var temp = "<div class='ach_mod clearfix'>";
    temp += "<div class='rightParts'>";
    temp += "<h2>+" + points + "</h2>";
    temp += "<p>" + message + "</p>";
    temp += "<span class='closeAchNode'>X</span>";
    temp += "</div>";
    temp += "<div class='leftParts'>";
    temp += "<h2>" + icon + "</h2>";
    temp += "</div>";
    temp += "</div>";
    t.html(temp);

    setTimeout(function () {
        $(".ach_mod").fadeOut('slow', function () {
            $(".ach_mod").remove();
        });
    }, 4000);
};

$(document).on('click', '.closeAchNode', function () {
    $(".ach_mod").fadeOut('slow', function () {
        $(".ach_mod").remove();
    });
});

// Notifications
$.fn.notifications = function (message, data) {
    var t = $(this);
    // Now make thee request
    if(data.type == "newOnlineFriend") {
        var temp = "<div class='n_mod clearfix'>";
        temp += "<div class='rightParts'>";
        temp += "<h2>" + data.friendFirstname + " " + data.last_name;
        temp += "<p>" + message + "</p>";
        temp += "</div>";
        temp += "<div class='leftParts'>";
        temp += "<img src='" + folder + "/users/data/" + data['friendSalt'] + "/profile_picture' style='height: 75px;width: 75px;'>";
        temp += "</div>";
        temp += "</div>";
        t.html(temp);
    }else if(data.type == "newPostLike")
    {
        var temp = "<div class='n_mod clearfix'>";
        temp += "<div class='rightParts'>";
        temp += "<h2>" + data.fromFirstname + " " + data.fromLastname;
        temp += "<p>" + message + "</p>";
        temp += "</div>";
        temp += "<div class='leftParts'>";
        temp += "<img src='" + folder + "/users/data/" + data['fromSalt'] + "/profile_picture' style='height: 75px;width: 75px;'>";
        temp += "</div>";
        temp += "</div>";
        t.html(temp);
    }

    setTimeout(function () {
        $(".n_mod").fadeOut('slow', function () {
            $(".n_mod").remove();
        });
    }, 4000);
};

$(document).on('click', '.closeAchNode', function () {
    $(".ach_mod").fadeOut('slow', function () {
        $(".ach_mod").remove();
    });
});

// Closing boxes
$.fn.outerHTML = function(){

    // IE, Chrome & Safari will comply with the non-standard outerHTML, all others (FF) will have a fall-back for cloning
    return (!this.length) ? this : (this[0].outerHTML || (
        function(el){
            var div = document.createElement('div');
            div.appendChild(el.cloneNode(true));
            var contents = div.innerHTML;
            div = null;
            return contents;
        })(this[0]));

};

// Alert box
$.fn.Alert = function (message, type) {
    var t = $(this);
    if (message != "" && type != "") {
        if (t.hasClass('hidden')) {
            t.removeClass('hidden');
            if (t.hasClass('slideOutUp')) {
                t.removeClass('slideOutUp');
            }
            t.addClass('slideInDown');
        }
        switch (type) {
            case 'error':
                t.addClass('global_error');
                t.html("<center><p><i class='fa fa-exclamation-circle'></i> " + message + "</p></center>");

                setTimeout(function () {
                    t.removeClass('global_error');
                    t.addClass('hidden');
                }, 4000);
                break;
            case 'warning':
                t.addClass('global_warning');
                t.html("<center><p><i class='fa fa-exclamation-triangle'></i> " + message + "</p></center>");

                setTimeout(function () {
                    t.removeClass('global_warning');
                    t.addClass('hidden');
                }, 4000);
                break;
            case 'success':
                t.addClass('global_success');
                t.html("<center><p><i class='fa fa-check-circle'></i> " + message + "</p></center>");

                setTimeout(function () {
                    t.removeClass('global_success');
                    if (t.hasClass('slideInDown')) {
                        t.addClass('slideOutUp');
                        t.addClass('hidden');
                    }
                }, 4000);
                break;
        }
    }
};

// Posting
$.fn.Post = function (selector, type, messagedata, appendType) {
    var t = $(this);

    if(type != "") {
        // Now switch between the different types
        switch (type) {
            case 'text':
                var post_data = messagedata['post_data'];
                var user_data = messagedata['user_data'];

                if (post_data['postHead'] == 1) {
                    var user_posted_to = obj['user_posted_to'];
                }

                var temp = "<div class='topPostAlways clearfix'>";
                temp += "<div class='topAuthorPortion'>";
                temp += "<div class='authorProfilePic' style='background-image: url("+user_data['user_by']['profile_picture']+");'></div>";
                temp += "<div class='rightAuthorInfo'>";
                if(post_data['postHead'] == 0) {
                    temp += "<a style='display: inline;' href='"+folder+"profile/" +user_data['user_by']['username'] + "'>" +user_data['user_by']['first_name']+ " " +user_data['user_by']['last_name']+ "</a>";
                }else{
                    temp += "<a style='' href='" +folder+ "profile/" +user_data['user_by']['username'] + "'>" +user_data['user_by']['first_name']+ " " +user_data['user_by']['last_name']+ "</a> » <a style='padding-top: 5px;' href='" +site_url+ "profile/" + user_posted_to['posted_to']['username'] + "'>" +user_posted_to['posted_to']['first_name']+ " " +user_posted_to['posted_to']['last_name']+ "</a>";
                }
                temp += "<h3>"+post_data['date_posted']+"</h3>";
                temp += "</div>";
                temp += "<div class='postTextBody'>";
                temp += "<p class='postTextBody' id='postBody"+post_data['unique_id']+"' style='padding-left: 5px;margin: 10px;margin-left: 0px;'>"+post_data['postBody']+"</p>";
                temp += "</div>";
                temp += "</div>";
                temp += "</div>";
                temp += "<div class='extrasHolder'>";
                $.each(post_data['postPhotos'], function (idx, obj) {
                    temp += "<video src='" + obj + "' style='width: 100%;height: auto;' controls></video>";
                });
                temp += "</div>";
                temp += "<div class='actionsHolder' style='padding-top: 20px;'>";
                temp += "<a style='color: #e74c3c;' class='likePostBtn"+post_data['unique_id']+" unlikeBTN postLikeListener hidden' data-type='unlike' data-pid='"+post_data['unique_id']+"'><i class='fa fa-heart'></i> Unlike</a>";
                temp += "<a style='color: #999;' class='likePostBtn"+post_data['unique_id']+" postLikeListener' data-type='like' data-pid='"+post_data['unique_id']+"'><i class='fa fa-heart'></i> Like</a>";
                temp += " &middot; <a class='commentMaker' data-id='"+post_data['unique_id']+"'>Comment</a> &middot; ";
                temp += "<a style='' data-pid='"+post_data['unique_id']+"'class='postRemove'>Delete</a>";
                temp += "</div>";
                temp += "<div class='bottomPostAssets'>";
                temp += "<div class='postStatsTop'>";
                temp += "<ul>";
                temp += "<li><span class='fspan likeCH'><i class='fa fa-heart'></i></span> <span class='sspan likeCH countHolder"+post_data['unique_id']+"' id=''><font color='#e74c3c'>0</font></span></li>";
                temp += "<li><span class='fspan'><i class='fa fa-commenting'></i></span> <span class='sspan' id=''>0</span></li>";
                temp += "</ul>";
                temp += "</div>";
                temp += "<div class='commentArea'>";
                temp += "<div class='commentHolder"+post_data['unique_id']+" cmod clearfix ' style='display: none;'>";
                temp += "</div>";
                temp += "<div class='comments commentFormHold hidden' id='commentFormHolder"+post_data['unique_id']+"'>";
                temp += "<form action='#' method='post' style='' id='commentForm"+post_data['unique_id']+"' class='clearfix commentForm' onSubmit='return false;'>";
                temp += "<div class='commentPicHolder' style='background-size: cover;background-image: url("+user_data['user_by']['profile_picture']+");'></div>";
                temp += "<div class='commentMakerAssets'>";
                temp += "<input type='text' class='comemntTextArea' id='commentTextArea"+post_data['unique_id']+"' data-id='"+post_data['unique_id']+"' placeholder='Write a comment..'/>";
                temp += "</div>";
                temp += "</div>";
                temp += "</div>";
                temp += "</div>";

                var grid = document.querySelector("#timelinePostsHold");
                var item = document.createElement('div');

                // Now change its attr
                item.className = 'timeline-item post clearfix';
                item.setAttribute('id', 'post-' + post_data['unique_id']);
                item.setAttribute('post-id', post_data['unique_id']);
                item.innerHTML = temp;

                salvattore.prependElements(grid, [item]);

                break;
        }
    }
};