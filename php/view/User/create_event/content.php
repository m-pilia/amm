<?php
require __DIR__ . "/../../../settings.php";

if (!isset($wrongDate))
    $wrongDate = Null;
if (!isset($wrongResource))
    $wrongResource = Null;
if (!isset($wrongStart))
    $wrongStart = Null;
if (!isset($wrongEnd))
    $wrongEnd = Null;

if (!isset($notes))
    $notes = Null;

/* address to return to the calendar page for the event date */
$returnDate = explode("/", $date);
$returnAddress = "calendar" .
                 "?day=" . $returnDate[0] .
                 "&month=" . $returnDate[1] .
                 "&year=" . $returnDate[2];

/* action page for the form */
if ($vd->getTitle() == "Create event")
    $action = "createEvent?cmd=save";
else
    $action = "editEvent?cmd=save&id=$id";

?>

<h2><?php echo $vd->getTitle(); ?></h2>
<script language="javascript"
    src="js/calendar.js"
    type="text/javascript">
</script>
<form   name="event-creation-form"
        id="event-creation-form"
        class="input-form"
        action="<?= $action ?>"
        method="POST"
        enctype="multipart/form-data">
<div class="form-contour">
    <div class="view-table">
        <div class="view-row">
            <label class="prop-name" for="date">Date: </label>
            <div class="prop-value">
                <input type="text"
                       id="date-picker"
                       name="date"
                       class="<?php if ($wrongDate) echo "input-error"; ?>"
                       placeholder="dd/mm/yyyy"
                       value="<?= $date ?>"/>
                <br />
                <?php
                    $errorId = "date-error";
                    $errorMessage = $wrongDate;
                    include __DIR__ . "/../../default/registration/error.php";
                ?>
            </div>
        </div>

        <div class="view-row">
            <label class="prop-name" for="resource">Resource: </label>
            <div class="prop-value">
                <select name="resource"
                        id="resource"
                        class="<?php if ($wrongResource) echo "input-error";?>">
                    <?php
                    foreach ($resources as $r) {
                        $id = $r->getId();
                        $name = $r->getName();
                        /* print the options, and mark the requested
                         * resource as default selection */
                        echo "<option value=\"$id\" " .
                             ($id == $resource ? "selected=\"selected\"" : "") .
                             ">$name</option>";
                    }
                    ?>
                </select>
                <br />
                <?php
                    $errorId = "resource-error";
                    $errorMessage = $wrongResource;
                    include __DIR__ . "/../../default/registration/error.php";
                ?>
            </div>
        </div>

        <div class="view-row">
            <label class="prop-name" for="start">Start hour: </label>
            <div class="prop-value">
                <select name="start"
                        id="start-hour"
                        class="<?php if ($wrongStart) echo "input-error"; ?>">
                    <?php
                    /* from beginHour:00 to (endHour-1):30 */
                    for ($hour = $beginHour; $hour <= $endHour; $hour++) {
                        /* convert hours:minutes in half hours from midnight */
                        $time = $hour * 2;
                        echo "<option value=\"$time\" " .
                             ($time == $start ? "selected=\"selected\"" : "") .
                             ">$hour:00</option>";
                        $time = $hour * 2 + 1;
                        echo "<option value=\"$time\" " .
                             ($time == $start ? "selected=\"selected\"" : "") .
                             ">$hour:30</option>";
                    }
                    ?>
                </select>
                <br />
                <?php
                    $errorId = "start-error";
                    $errorMessage = $wrongStart;
                    include __DIR__ . "/../../default/registration/error.php";
                ?>
            </div>
        </div>

        <div class="view-row">
            <label class="prop-name" for="end">End hour: </label>
            <div class="prop-value">
                <select name="end"
                        id="end-hour"
                        class="<?php if ($wrongEnd) echo "input-error"; ?>">
                    <?php
                    /* from beginHour:30 to (endHour + 1):00 */
                    for ($hour = $beginHour; $hour <= $endHour; Null) {
                        /* convert hours:minutes in half hours from midnight */
                        $time = $hour * 2 + 1;
                        echo "<option value=\"$time\" " .
                             ($time == $end ? "selected=\"selected\"" : "") .
                             ">$hour:30</option>";
                        $hour++;
                        $time = $hour * 2;
                        echo "<option value=\"$time\" " .
                             ($time == $end ? "selected=\"selected\"" : "") .
                             ">$hour:00</option>";
                    }
                    ?>
                </select>
                <br />
                <?php
                    $errorId = "end-error";
                    $errorMessage = $wrongEnd;
                    include __DIR__ . "/../../default/registration/error.php";
                ?>
            </div>
        </div>

        <div class="view-row">
            <label class="prop-name" for="notes">Notes: </label>
            <div class="prop-value">
                <textarea name="notes"
                          id="notes"
                          rows="5"
                          cols="50"
                          maxlength="<?= $notesLen ?>"><?= $notes ?></textarea>
                <br />
            </div>
        </div>
    </div>
    <div>
        <input class="rc-button" id="create-event-button"
            type="submit" name="create-event"
            value="<?php echo explode(' ', $vd->getTitle())[0]; ?>" />
        <br />

        <a role="button"
           class="return-link"
           onclick="openCalendarDate()">
            Return to the calendar
        </a>
    </div>
</div>
</form>
