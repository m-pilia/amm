/*!
 * This function checks the form fields before the submission. If a field is
 * not valid, the input field class is changed (to highlight it), the error
 * div class is changed (to unhide the error message box below the input field)
 * and the content of the latter is set with a message.
 *
 * @param element The input element to be validated. The whole form is
 * validated when absent.
 */
function ajaxValidation(element) {
    $.ajax({
        url: 'registration',
        type: 'POST',
        data: {
            'cmd': 'regValidation',
            'username': $("#reg-username-field").val().trim(),
            'password': $("#reg-password-field").val(),
            'password-rep': $("#reg-password-rep-field").val(),
            'email': $("#reg-email-field").val().trim(),
            'first': $("#reg-first-field").val().trim(),
            'last': $("#reg-last-field").val().trim(),
        },
        dataType: 'json',
        success: function (data, state) {
            /* single input element: set or remove the error message */
            if (element !== undefined)
                setErrorMessage(element, data[element]);

            /* check the whole form */
            else {
                if (data['valid']) /* submit a valid form */
                    document.getElementById('registration-form').submit();
                else { /* set error messages in a non valid form */
                    delete data['valid']; /* not needed anymore */
                    avatarValidation(); /* check avatar */
                    for (var el in data) { /* set remaining messages */
                        setErrorMessage(el, data[el]);
                    }
                }
            }
        },
        error: function (data, state) {
            /* TODO remove when stable */
            //alert("AJAX error\n" + JSON.stringify(data));
        }
    });

    return false; /* the submission is managed by the ajax return function */
}

/* set or clean an error message */
function setErrorMessage(field, message) {
    if (message !== undefined && message !== null) {
        document.getElementById("reg-" + field + "-field").className =
            "input-error";
        document.getElementById(field + "-error").className = "error-message";
        document.getElementById(field + "-error").innerHTML = message;
        return false;
    } else {
        document.getElementById("reg-" + field + "-field").className = "";
        document.getElementById(field + "-error").className = "error-hidden";
        document.getElementById(field + "-error").innerHTML = "";
        return true;
    }
}

/* validates the avatar file type
 * this must be done with js on client side because the file has not been
 * sent to the server yet; a second and deeper check is done on the server
 * side after the form submission */
function avatarValidation() {
    /* This regex extracts the extension from a filename */
    var extPat = /\.([A-Za-z0-9]+)$/;

    /* check avatar image (file extension only) */
    var avatar = $("#reg-avatar-field").val();
    if (avatar != null && avatar != "") {
        var imageExt = extPat.exec(avatar)[1]; /* get file extension */
        if ( /* unaccepted extension */
                imageExt != "png" &&
                imageExt != "gif" &&
                imageExt != "jpg" &&
                imageExt != "jpeg") {
            setErrorMessage('avatar',
                    "Supported formats are .jpg/.jpeg, .png, .gif");
            return false;
        }
    }
    /* valid extension, or no file submitted */
    setErrorMessage('avatar');
    return true;
}
