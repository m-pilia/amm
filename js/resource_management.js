/* enable the input text field to rename an existing resource */
function enableResourceInput(id) {
    /* hide edit anchor */
    $("#a-" + id).css('visibility', 'hidden');
    /* enable edit in the input box */
    var input = $("input[name='" + id + "']");
    input.prop('disabled', false);
    input.focus();
}

var resourceCounter = 0; /* count added resources */

/* add a new input text field to the form, to add a new resource */
function newResource() {
    ++resourceCounter;
    var newChild =
            "<div class=\"resource-box view-row\">" +
                "<a><!-- placeholder --></a>" +
                "<span>" +
                "    <input type=\"text\"" +
                "           name=\"new-" + resourceCounter + "\"/>" +
                "</span>" +
                "<span><!-- placeholder --></span>" +
            "</div>";
    $('#resources').append(newChild);
    
    /* focus on the new child */
    $('input[name="new-' + resourceCounter + '"]').focus();
}
