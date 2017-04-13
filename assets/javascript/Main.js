/*
 * Main Javascript File
 * ----
 * This is the main javascript file that will initiate everything
 */
var folder = '/frindse.com';

// Lets first figure out if there is a logged user
function checkLoginStatus()
{
    
}

// Start main socket
var socket = new WebSocket("ws://localhost:8083");

socket.onopen = function(e) {
    console.log("Connection established!");
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