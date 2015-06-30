<?php
/*
 * The calendar is displayed with one row for each half-hour of the day
 * (inside the interval between $beginHour and $endHour) and one column
 * for each resource. Bookings are shown in their respective places.
 *
 * The resources are contained in an associative array...
 *
 * The bookings are contained into an EventSet object.
 *
 * When the length of an event is greater than half an hour, the event spans
 * across many lines. The array $skip contains an index for each resource, whose
 * value specifies the number of rows to skip when printing the html source
 * of the table, due to a rowspan in a previous row.
 */
require_once __DIR__ . "/../../../model/Event.php";
require_once __DIR__ . "/../../../model/EventSet.php";
require __DIR__ . "/../../../settings.php";

/**
 * \brief Print a row in the calendar table
 * @param  array $resources The resources array.
 * @param  array $bookings  The bookings array for the current day.
 * @param  array $skip      The skip counter array.
 * @param  array $date      The date (day, month, year).
 * @param  int   $hour      Hour corresponding to the row.
 * @param  int   $minute    Minute corresponding to the row.
 */
function printTableRow(&$resources, &$events, &$skip, $date, $hour, $minute) {
    /* the row has one cell for each resource */
    foreach ($resources as $r) {
        $resourceName = $r->getName();
        $resourceId = $r->getId();

        if (sizeof($date) != 3)
            throw new InvalidArgumentException(
                    "printTableRow: invalid date argument");

        /* if there is a rowspan in some preceeding row, do not print */
        if (isset($skip["$resourceId"]) && $skip["$resourceId"] > 0) {
            $skip["$resourceId"]--;
            continue;
        }

        /* event for the current cell */
        $e = $events->getEventByStart($hour, $minute, $r);

        /* variables for date, start and end (time in half hours) */
        list ($day, $month, $year) = $date;
        $start = $hour * 2 + ($minute == 30 ? 1 : 0);
        $end = $start + 1;

        /* text to be displayed inside the cell */
        $content = Null;

        /* length of the event (in half hours) for the rowspan */
        $len = Null;

        /* type of the event */
        $type = Null;

        /* check if there is an event for the current cell */
        if ($e != Null) {
            $content = $e->getOwner();
            $len = $e->getLength();
            $type = $e->getType();
            $id = $e->getId();

            /* code for the onClick action */
            $address = "'displayEvent?id=$id'";
            $onClick = "window.open($address, '_self')";

            /* this will cause the cell to change the cursor but not
             * highlight on mouse hover, thanks to CSS rules */
            $dataStatus = "busy";

            /* tip title for the cell */
            $tipTitle = "busy";

            /* set the counter for the rows to span after this */
            $skip["$resourceId"] = $len - 1;
        }
        else {

            /* Code for the onClick action. */
            $onClick = "";

            /* this will cause the cell to appear not clickable,
             * thanks to CSS rules */
            $dataStatus = "past";

            /* tip title for the cell */
            $tipTitle = "past";

            /* The link for the event creation is inserted only if the
             * date and hour are not past.
             * Date and hour are compared by lexicographic order in the
             * yyyymmdd and hhmm formats. */
            $dateString = $year . sprintf('%02d',$month) . sprintf('%02d',$day);
            $hourString = sprintf('%02d', floor($start / 2))
                    . ($start % 2 == 0 ? "00" : "30");
            /* check if the date is greater than today, or if it is equal
             * check if the hour is later than now */
            if (strcmp($dateString, date("Ymd")) > 0 || /* greater date */
                    (strcmp($dateString, date("Ymd")) == 0 && /* equal date */
                     strcmp($hourString, date('Hi')) > 0)) { /* later hour */
                /* the timeslot not past, so it is bookable */
                $address = "'createEvent" .
                           "?date=$day%2F$month%2F$year" .
                           "&start=$start" .
                           "&end=$end" .
                           "&resource=$resourceId'";
                $onClick = "window.open($address, '_self')";

                /* this will cause the cell to change the cursor and
                 * highlight on mouse hover, thanks to CSS rules */
                $dataStatus = "bookable";

                /* tip title for the cell */
                $tipTitle = "available";
            }
        }

        /* print the html code of the cell */
        echo <<<EOF
                <td class="calendar-cell"
                    title="$tipTitle"
                    onclick="$onClick"
                    data-event-type="$type"
                    data-status="$dataStatus"
                    rowspan="$len">
                    $content
                </td>

EOF;
    }
}
?>

<h2>Calendar</h2>

<script src="js/calendar.js"
    type="text/javascript">
</script>
<div id="date-picker-div">
    <label for="date">Select a date: </label>
    <input type="text"
           id="date-picker"
           name="date"
           placeholder="dd/mm/yyyy"
           value="<?= "$day/$month/$year" ?>"/>
    <input type="button"
           id="go-button"
           value="Go"
           onclick="openCalendarDate()"/>
</div>

<?php
/* date for h3 title, with full month name */
$dateTitle = $day . " " . date('F', mktime(0, 0, 0, $month)) . " " . $year;

/* address for the next day in the calendar */
$nextDate = explode("/", date('d/m/Y', strtotime("$day.$month.$year + 1 day")));
$nextAddress = "calendar" .
               "?day=" . $nextDate[0] .
               "&month=" . $nextDate[1] .
               "&year=" . $nextDate[2];

/* address for the previous day in the calendar */
$prevDate = explode("/", date('d/m/Y', strtotime("$day.$month.$year - 1 day")));
$prevAddress = "calendar" .
               "?day=" . $prevDate[0] .
               "&month=" . $prevDate[1] .
               "&year=" . $prevDate[2];
?>

<!-- day title -->
<h3>
    <a href="<?= $prevAddress ?>">&larr;</a> <!-- goto next day -->
    <?= $dateTitle ?>
    <a href="<?= $nextAddress ?>">&rarr;</a> <!-- goto previous day -->
</h3>

<!-- color legend -->
<div style="text-align: center;">
    <div id="color-legend">
        <div class="color-square" id="color-square-past"></div>
        <span class="color-title">Past</span>

        <div class="color-square">
            <div class="color-square" id="color-square-available-1"></div>
        </div>
        <span class="color-title">Available</span>

        <div class="color-square" id="color-square-user"></div>
        <span class="color-title">Busy (user)</span>

        <div class="color-square" id="color-square-admin"></div>
        <span class="color-title">Busy (admin)</span>
    </div>
</div>

<!-- calendar -->
<table id="calendar-table">
<?php
    /* open heading row */
    echo "\t\t\t<tr>\n\t\t\t\t<th><!-- blank above the hour column --></th>\n";

    /* one heading for each resource */
    foreach ($resources as $r)  {
        /* trim resource name to not exceed a fixed length (see settings.php);
         * the whole name is displayed in the title tooltip */
        $resName = $r->getName();
        $resNameTrim = mb_strimwidth($resName, 0, $trimLen, "...");
        echo "\t\t\t\t<th title=\"$resName\">$resNameTrim</th>\n";
    }

    /* close heading row */
    echo "\t\t\t</tr>\n";

    /* one double row for each hour of the day (see settings.php) */
    for ($hour = $beginHour; $hour <= $endHour; $hour++){

        /* first column, containing the hour */
        echo <<<EOF
            <tr class="odd-table-line">
                <td rowspan="2"
                    class="calendar-hour-cell">
                    $hour:00
                </td>

EOF;

        $date = array($day, $month, $year);

        /* first sub-row (hour o' clock) */
        printTableRow($resources, $events, $skip, $date, $hour, 0);

        /* division between the two sub-rows */
        echo <<<EOF
            </tr>
            <tr class="even-table-line">

EOF;

        /* second sub-row (half past hour) */
        printTableRow($resources, $events, $skip, $date, $hour, 30);

        /* close row */
        echo "\t\t\t</tr>\n";
    }
?>
</table>
