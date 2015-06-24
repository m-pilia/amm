/**
 * @file dynamic_view_set.js
 * @author Martino Pilia <martino.pilia@gmail.com>
 *
 * This file contains the function used to provide dynamic changes to the
 * view of the master page.
 */

/* Force the content section to have a mininum height sufficient to put the
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

    /* get header and footer height + border + margin + padding */
    var footerOffsetHeight = $("#footer-wrapper").outerHeight(true);

    /* compute the content minimum height to fill the viewport */
    var contentMinHeight =
            $(window).height()
            - footerOffsetHeight
            - contentMargin;

    /* set the property */
    $("#content").css('min-height', contentMinHeight + "px");
}

/* Handler for window events */
window.addEventListener('load', dynamicContent);
window.addEventListener('resize', dynamicContent);

/* Resize the header when scrolling */
window.addEventListener("scroll", function(event) {
    var minScroll = 100; /* minimum scroll to reduce the header */
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
     * `minScroll` value, causing the header resize. */
    if (top > minScroll && docHeight - viewHeight > minScroll + heightDiff) {
        $("#logo img").width("50px");
        $("#logo img").height("50px");
        $("header > div#head").css("line-height", "50px");
        $("header").css("margin-top", "0px");
        $("header").css("-moz-border-top-right-radius", "0px");
        $("header").css("-webkit-border-top-right", "0px");
        $("header").css("border-top-right-radius", "0px");
        $("#sidebar").css("top", "60px");
    } else {
        $("#logo img").width("100px");
        $("#logo img").height("100px");
        $("header > div#head").css("line-height", "100px");
        $("header").css("margin-top", "10px");
        $("header").css("-moz-border-top-right-radius", "20px");
        $("header").css("-webkit-border-top-right", "20px");
        $("header").css("border-top-right-radius", "20px");
        $("#sidebar").css("top", "123px");
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

/* Close the sidebar */
function closeSidebar() {
    document.getElementById('nav-trigger').checked = false;
}
