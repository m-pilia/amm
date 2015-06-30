<h2><?php echo $vd->getTitle(); ?></h2>

<div class="container-wrapper">
    <div id="next-events-title">Your events for the next week</div>
    <table id="next-events">
        <tr>
            <th>Date</th>
            <th>Hour</th>
            <th>Resource</th>
        </tr>
        <?php
        /* get events for the next week */
        $days = $_SESSION[self::USER]->getNextWeekEvents();

        /* count rows in the table, to determine odd and even ones and
         * apply a different CSS background color*/
        $lineCounter = 1;

        foreach ($days as $key => $events) {
            /* number of events for the date */
            $number = $events->getSize();

            if (!$number)
                continue; /* no events for this date */

            /* the $key is a date string in the format yyyy/mm/dd */
            $date = date("D jS F Y", strtotime($key)); /* change date format */

            /* address of the calendar page for the date */
            list($year, $month, $day) = explode("/", $key);
            $dateAddress = "calendar" .
                           "?day="   . $day .
                           "&month=" . $month .
                           "&year="  . $year;

            $class = $lineCounter % 2 ? "class=\"odd-table-line\"" : "";
            ++$lineCounter;

            echo "<tr $class>";
            echo "<td rowspan=\"$number\" class=\"event-date\">" .
                     "<a href=\"$dateAddress\"" .
                     "   title=\"Open in the calendar\">" .
                         $date .
                     "</a>" .
                 "</td>";

            $first = True; /* true at first iteration of the inner loop */
            foreach ($events as $e) {
                $start = $e->getStart();
                $end = $e->getEnd();
                $hour =   floor($start / 2) . ":"
                        . ($start % 2 == 0 ? "00" : "30") . " &mdash; "
                        . floor($end / 2) . ":"
                        . ($end % 2 == 0 ? "00" : "30");
                $resource = $e->getResource()->getName();

                /* address for the event page */
                $eventAddress = "displayEvent?id=" . $e->getId();

                /* do not open tr in the first iteration, it is open yet */
                if ($first) {
                    $first = False;
                }
                /* open it in the next iterations */
                else {
                    /* mark only odd lines with the class */
                    $class = $lineCounter % 2 ? "class=\"odd-table-line\"" : "";
                    ++$lineCounter;
                    echo "<tr $class>";
                }

                /* print event */
                echo <<<EOF
                        <td>
                            <a href="$eventAddress"
                               title="Event details">
                                $hour
                            </a>
                        </td>
                        <td>
                            $resource
                        </td>
                    </tr>
EOF;
            }
        }

        /* if the user has no events for the next week, show a message */
        if ($lineCounter == 1) {
            echo <<<EOF
                <tr>
                    <td colspan="3">
                            You have no events for the next week
                    </td>
                </tr>
EOF;
        }
        ?>
    </table>
</div>
