/**
 * @file dynamic_view_set.js
 * @author Martino Pilia <martino.pilia@gmail.com>
 *
 * This file contains the function used to provide dynamic changes to the
 * view of the master page.
 */

var toBeClosed = false; /* condition to close the sidebar after a wait time */

/* Force the content section to have a mininum height sufficient to push the
 * footer at the bottom of the viewport. */
function dynamicContent() {

    /* reset content minimum height
     * (needed for resize, otherwise the wrapped section results
     * artificially higher) */
    $("#content").css('min-height', "0px");
    $("#sidebar").css('min-height', "0px");

    /* get content margin + padding */
    var content = document.getElementById('content'),
        style = window.getComputedStyle(content),
        contentMargin =
            parseInt(style.marginTop)
            + parseInt(style.marginBottom)
            + parseInt(style.paddingTop)
            + parseInt(style.paddingBottom);

    /* header and footer offset height (height + border + margin + padding) */
    var footerOffsetHeight = $("#footer-wrapper").outerHeight(true);
    var headerOffsetHeight = $("header").outerHeight(true);

    /* compute the content minimum height to fill the viewport */
    var contentMinHeight =
            $(window).height()
            - footerOffsetHeight
            - headerOffsetHeight
            - contentMargin;

    /* set the property */
    $("#content").css('min-height', contentMinHeight + "px");
}

/* Handler for window events */
window.addEventListener('load', dynamicContent);
window.addEventListener('resize', dynamicContent);

/* Resize the header when scrolling */
window.addEventListener("scroll", function(event) {
    var minScroll = 50; /* minimum scroll to reduce the header */
    var heightDiff = 60; /* difference in height when reducing the header */
    /* get vertical scroll position */
    var top  = window.pageYOffset || document.documentElement.scrollTop;
    /* get viewport height */
    var viewHeight = window.innerHeight;
    /* get document height */
    var docHeight = document.body.scrollHeight
            || document.body.offsetHeight
            || html.clientHeight
            || html.scrollHeight
            || html.offsetHeight;

    /* ensure the difference between viewport and document height is high
     * enough to avoid that the header reduction brings the scroll below the
     * `minScroll` value, causing an immediate opposite resize. */
    if (top > minScroll && docHeight - viewHeight > minScroll + heightDiff) {
        /* reduce header size */
        var logoImg = $("#logo-image");
        logoImg.width("50px");
        logoImg.height("50px");
        $("header h1").css("line-height", "50px");
        var header = $("header");
        header.css("height", "60px");
        header.css("margin-top", "0px");
        header.css("-moz-border-top-right-radius", "0px");
        header.css("-webkit-border-top-right", "0px");
        header.css("border-top-right-radius", "0px");
        $("#sliding").css("padding-top", "60"); /* header offset */
        $("#trigger").css("top", "73px"); /* header offset + 10 px margin */
        $("#sidebar").css("top", "60px");
        $("#login-button, #logout-button").css("margin-top", "10px");
    } else {
        /* enlarge header size */
        var logoImg = $("#logo-image");
        logoImg.width("100px");
        logoImg.height("100px");
        $("header h1").css("line-height", "100px");
        var header = $("header");
        header.css("height", "110px");
        header.css("margin-top", "10px");
        header.css("-moz-border-top-right-radius", "20px");
        header.css("-webkit-border-top-right", "20px");
        header.css("border-top-right-radius", "20px");
        $("#sliding").css("padding-top", "123"); /* header offset */
        $("#trigger").css("top", "133px"); /* header offset + 10 px margin */
        $("#sidebar").css("top", "123px");
        $("#login-button, #logout-button").css("margin-top", "34px");
    }
});

/* Preview an image before the upload */
function imagePreview(event) {
    var preview = document.getElementById('preview-image');
    preview.src = URL.createObjectURL(event.target.files[0]);
};

/* Goes back to the previous browser history page */
function goBack() {
    window.history.back();
}

/* toggle the sidebar */
function sidebarTrigger(event) {
    event.stopPropagation(); /* stop bubbling */

    /* 200 is the offset width of the sidebar, due to the static css values. */
    if ($("#sliding").css("margin-left") == "200px")
        closeSidebar(); /* sidebar is shown, hide it */
    else
        openSidebar(); /* sidebar is hidden, show it */
}

/* close the sidebar */
function closeSidebar() {
    $("#sliding").css("margin-left", "0px");
    $("#trigger").css("left", "10px"); /* 10 px margin */
    $("#trigger").attr("title", "Show the sidebar");

    /* remove the ?sidebar=open query from the address bar url */
    if (document.location.href.search('sidebar=open') != -1)
        history.replaceState({}, '',
                document.location.href.replace('sidebar=open', ''));
}

/* open the sidebar */
function openSidebar() {
    $("#sliding").css("margin-left", "200px");
    $("#trigger").css("left", "210px"); /* sidebar + 10 px margin */
    $("#trigger").attr("title", "Hide the sidebar");
    /* automatically close the sidebar when the mouse is over the
     * content for a certain time */
    $("#content").attr("onmouseover", "closeSidebarAfterTime(700)");
}

/* close the sidebar if the condition is still valid after a timeout
 * @param timeout Timeout in milliseconds */
function closeSidebarAfterTime(timeout) {
    $("#content").attr("onmouseover", "");
    toBeClosed = true;

    /* launch a timer: if the mouse has not left the content section at
     * its end, close the sidebar */
    window.setTimeout(function () {
        if (toBeClosed)
            closeSidebar();
    }, timeout);
}

/* abort the timed sidebar close action */
function closeSidebarAfterTimeCancel() {
    toBeClosed = false;
    /* automatically close the sidebar when the mouse is over the
     * content for a certain time */
    $("#content").attr("onmouseover", "closeSidebarAfterTime(700)");
}

/* if the caps lock is active, unhide a warning message */
function capsLockAlert(e){
    /* check if a keypress is an uppercase or lowercase letter */
    var isUpper = /^[A-Z]{1}$/.test(e.key);
    var isLower = /^[a-z]{1}$/.test(e.key);

    /* if it is an uppercase and shift is not hold, or if it is a lowercase
     * and shift is hold, the CapsLock must be active */
    if ((isUpper && !e.shiftKey) || (isLower && e.shiftKey))
        $("#caps-warning").removeClass("error-hidden");

    /* hide the box otherwise (this and the above test are not complementar) */
    if ((isUpper && e.shiftKey) || (isLower && !e.shiftKey))
        $("#caps-warning").addClass("error-hidden");
}

/* this listener removes the CapsLock warning message when CapsLock is disabled
 * without need to wait the next input inside the form */
document.addEventListener("keydown", function (e) {
    if (e.key == "CapsLock" && !$("#caps-warning").hasClass("error-hidden")) {
        $("#caps-warning").addClass("error-hidden");
    }
});
