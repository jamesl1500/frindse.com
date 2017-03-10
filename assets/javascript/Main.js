/*
 * Main Javascript File
 * ----
 * This is the main javascript file that will initiate everything
 */

// Lets first figure out if there is a logged user
function checkLoginStatus()
{
    
}

// Start main socket
var socket = io.connect("http://localhost:8082");

/* NAVBAR & HEADER */
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
