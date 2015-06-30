/**
 * @file calendar.js
 * @author Martino Pilia <martino.pilia@gmail.com>
 *
 * This file provides the functions used in the calendar page.
 */

/* assign the date picker selector to the input field */
$(function () {
    $("#date-picker").datepicker({
        dateFormat: 'dd/mm/yy',
        onSelect: function() {
            /* hide the picker */
            $("#date-picker").datepicker("hide");

            /* go to the calendar page for the selected date, activating
             * the submission button, when such button is present */
            document.getElementById("go-button").click();
        }
    });
});

/* open a date in the calendar page */
function openCalendarDate() {
    var date = $("#date-picker").val();
    if (date) {
        var datePattern = /([0-9]{1,2})\/([0-9]{1,2})\/([0-9]{1,4})/;
        var match = datePattern.exec(date);
        
        /* the input date is invalid, use current date */
        if (!match) {
            var now = new Date();
            match = [now.getDate(), now.getMonth() + 1, now.getFullYear()];
        }

        var address = 'calendar?day=' + match[1]
                    + '&month=' + match[2]
                    + '&year=' + match[3];
        window.open(address, '_self');
    }
}
