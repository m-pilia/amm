<?php
$user = $_SESSION['user'];
$date = $event->getDay() . "/" . $event->getMonth() . "/" . $event->getYear();

/* conversion from half hours to hours:minutes */
$start = floor($event->getStart() / 2)
         . ":"
         . ($event->getStart() % 2 == 0 ? "00" : "30");
$end = floor($event->getEnd() / 2)
         . ":"
         . ($event->getEnd() % 2 == 0 ? "00" : "30");
$hour = " from " . $start . " to " . $end;
$resource = $event->getResource()->getName();
$owner = $event->getOwner() . " (" . $event->getType() . ")";
$notes = ($event->getNotes() == "" ? "None" : $event->getNotes());

/* address to the calendar page containing the event */
$returnAddress = "calendar" .
                 "?day=" . $event->getDay() .
                 "&month=" . $event->getMonth() .
                 "&year=" . $event->getYear();

/* address to edit this event */
$editAddress = "editEvent?id=" . $event->getId();

/* event date in the format yyyymmdd */
$dateStr = $event->getYear()
        . sprintf('%02d', $event->getMonth())
        . sprintf('%02d', $event->getDay());
/* event start hour in the format hhmm */
$startStr = sprintf('%02d', floor($event->getStart() / 2))
        . ($event->getStart() % 2 == 0 ? "00" : "30");
/* determine if the event has already started */
$started = /* the event has already started if */
        (strcmp($dateStr, date("Ymd")) < 0) || /* the date is past or */
        ((strcmp($dateStr, date("Ymd")) == 0)  /* the date is today but */
            && (strcmp($startStr, date("Hi"))) <= 0); /* the hour is past */
?>

<h2>Event details</h2>

<div id="event-display-container">
    <div id="event-display" class="view-table">
        <div class="view-row">
            <span class="prop-name">Date: </span>
            <span class="prop-value"><?= $date ?></span>
        </div>
        <div class="view-row">
            <span class="prop-name">Hour: </span>
            <span class="prop-value"><?= $hour ?></span>
        </div>
        <div class="view-row">
            <span class="prop-name">Resource:</span>
            <span class="prop-value"><?= $resource ?></span>
        </div>
        <div class="view-row">
            <span class="prop-name">Owner:</span>
            <span class="prop-value"><?= $owner ?></span>
        </div>
        <div class="view-row">
            <span class="prop-name">Notes:</span>
            <span class="prop-value prop-notes"><?= $notes ?></span>
        </div>
    </div>
<?php
/* only an admin or the owner can edit the event, and started event
 * cannot be edited */
if (!$started &&
        (  $user->getRole() == "Admin"
        || $event->getOwner() == $user->getUsername()))
    echo <<<EOF
    <p>
        <a href="$editAddress">Edit the event</a>
    </p>
EOF
?>
    <p>
        <a href="<?= $returnAddress ?>">Return to the calendar</a>
    </p>
</div>
