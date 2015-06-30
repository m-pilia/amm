<?php
/*!
 * \brief This class represents an event booked in the calendar.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-25
 */
class Event {

    private $id;       /*!< int      Id of the event in the database. */
    private $owner;    /*!< string   Username of the user who owns the event. */
    private $day;      /*!< int      Day for the event. */
    private $month;    /*!< int      Month for the event. */
    private $year;     /*!< int      Year of the event. */
    private $start;    /*!< int      Start (in half hours from midnight). */
    private $end;      /*!< int      End (in half hours from midnight). */
    private $resource; /*!< Resource Resource name. */
    private $type;     /*!< string   Type of the event (i.e. author class), */
    private $notes;    /*!< string   Notes for the event. */

    /*!
     * \brief Construct an event object.
     * @param $id        int      Id of the event in the database.
     * @param $owner     string   Username of the creator of the event.
     * @param $day       int      Day for the event.
     * @param $month     int      Month for the event.
     * @param $year      int      Year of the event.
     * @param $start     int      Start (in half hours from midnight).
     * @param $end       int      End (in half hours from midnight).
     * @param $resource  Resource Resource name.
     * @param $type      string   Type of the event (i.e. author class).
     * @param $notes     string   Notes for the event.
     */
    public function __construct($id, $owner, $day, $month, $year, $start, $end,
            Resource $resource, $type, $notes) {
        if ($end <= $start)
            throw new InvalidArgumentException("Event->__construct(): " .
                    "\$end must be bigger than \$start.");
        $this->id = $id;
        $this->owner = $owner;
        $this->day = $day;
        $this->month = $month;
        $this->year = $year;
        $this->start = $start;
        $this->end = $end;
        $this->resource = $resource;
        $this->type = $type;
        $this->notes = $notes;
    }

    /*!
     * \brief Getter for id.
     * @return string The id of the event in the database.
     */
    public function getId() {
        return $this->id;
    }

    /*!
     * \brief Getter for owner.
     * @return string The creator of the event.
     */
    public function getOwner() {
        return $this->owner;
    }

    /*!
     * \brief Getter for day.
     * @return int Day for the event.
     */
    public function getDay() {
        return $this->day;
    }

    /*!
     * \brief Getter for month.
     * @return int Month for the event.
     */
    public function getMonth() {
        return $this->month;
    }

    /*!
     * \brief Getter for year.
     * @return int Year for the event.
     */
    public function getYear() {
        return $this->year;
    }

    /*!
     * \brief Getter for start.
     * @return int Starting hour (in half hours from midnight).
     */
    public function getStart() {
        return $this->start;
    }

    /*!
     * \brief Getter for end.
     * @return int Ending hour (in half hours from midnight).
     */
    public function getEnd() {
        return $this->end;
    }

    /*!
     * \brief Getter for end.
     * @return int Ending hour (in half hours from midnight).
     */
    public function getLength() {
        return $this->end - $this->start;
    }

    /*!
     * \brief Getter for resource.
     * @return Resource Object for the booked resource.
     */
    public function getResource() {
        return $this->resource;
    }

    /*!
     * \brief Getter for type.
     * @return string Type of the event (e.g. the class of the creator).
     */
    public function getType() {
        return $this->type;
    }

    /*!
     * \brief Getter for notes.
     * @return string Notes for the event.
     */
    public function getNotes() {
        return $this->notes;
    }

    /*!
     * \brief Compare two Event objects according to their chronological order.
     * @param  Event  $first  First event to be compared.
     * @param  Event  $second Second event to be compared.
     * @return int    A value lesser than zero if $first < $second, equal to
     *                zero when equals and greater than zero when
     *                $first > $second.
     */
    public static function compare(Event $first, Event $second) {
        /* three-way comparator */
        $res = ($first->year > $second->year)
             - ($first->year < $second->year);
        if ($res)
            return $res;

        $res = ($first->month > $second->month)
             - ($first->month < $second->month);
        if ($res)
            return $res;

        $res = ($first->day > $second->day)
             - ($first->day < $second->day);
        if ($res)
            return $res;

        return ($first->start > $second->start)
            - ($first->start < $second->start);
    }

    /**
     * \brief Validate the data for an event.
     * @param $date      string   Date for the event, in the format dd/mm/yyyy.
     * @param $start     int      Start (in half hours from midnight).
     * @param $end       int      End (in half hours from midnight).
     * @param $resource  Resource Resource name.
     * @param $notes     string   Notes for the event.
     * @param $selfId    int  The id of the event itself, when validating the
     *                        edit of a pre-existing event (set to Null
     *                        otherwise).
     * @return An associative array containing the validated data or the error
     *         messages related to invalid values.
     */
    public static function validateEventData($date, $resource, $start, $end,
            $notes, $selfId) {
        require __DIR__ . "/../settings.php";

        /* variables for data */
        $day = Null;
        $month = Null;
        $year = Null;

        /* variables for error messages */
        $wrongDate = Null;
        $wrongResource = Null;
        $wrongStart = Null;
        $wrongEnd = Null;

        /* date ($day, $month, $year) validation */
        if (!$date)
            $wrongDate = "Date required";
        else {
            /* extract date fields (day/month/year) */
            $fields = explode('/', $date);

            /* validate */
            if (sizeof($fields) != 3 ||
                    !checkdate($fields[1], $fields[0], $fields[2]))
                $wrongDate = "The date seems invalid";
            else
                list($day, $month, $year) = $fields;

            /* ensure the date is not past */
            $dateString = $year
                    . sprintf('%02d', $month)
                    . sprintf('%02d', $day);
            if (strcmp($dateString, date("Ymd")) < 0)
                $wrongDate = "The date is past";
        }

        /* resource validation */
        if (!$resource)
            $wrongResource = "Resource required";
        else {
            $found = 0; /* variable for search result */

            /* search resource in the database */
            $mysqli = Database::getInstance()->connect(); /* db connection */
            $query = "SELECT COUNT(Name) FROM Resources WHERE Id= ? ";
            $stmt = $mysqli->stmt_init();
            $stmt->prepare($query);
            if (!$stmt)
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt prepare");

            $bind = $stmt->bind_param("s", $resource);
            if (!$bind)
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt bind param");

            if (!$stmt->execute())
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt execute");

            $bind = $stmt->bind_result($found);
            if (!$bind)
                throw new DbQueryException($stmt->errno,
                        $stmt->error, "Stmt bind result");

            $stmt->fetch();

            $stmt->close();
            $mysqli->close(); /* close db connection */

            if ($found < 1)
                $wrongResource = "The requested resource does not exist";
        }

        /* start validation */
        if (!is_numeric($start))
            $wrongStart = "Invalid start hour";
        else {
            /* note: conversion of begin and end hours into half hours */
            if ((int) $start < $beginHour * 2
                    || (int) $start > $endHour * 2 + 1)
                $wrongStart = "Invalid start hour";
            else {
                /* If the date is today, ensure the hour is not past.
                 * Date and hour are compared by lexicographic order in the
                 * yyyymmdd and hhmm formats. */
                $dateString = $year
                        . sprintf('%02d', $month)
                        . sprintf('%02d', $day);
                $startHour = sprintf('%02d', floor($start / 2))
                        . ($start % 2 == 0 ? "00" : "30");
                if (strcmp($dateString, date("Ymd")) == 0
                        && strcmp($startHour, date('Hi')) <= 0)
                    $wrongStart = "The start hour is past";
            }
        }

        /* end validation */
        if (!is_numeric($end))
            $wrongEnd = "Invalid end hour";
        else {
            /* note: conversion of begin and end hours into half hours */
            if ((int) $end < $beginHour * 2 + 1
                    || (int) $end > ($endHour + 1) * 2)
                $wrongEnd = "Invalid end hour";
            else if ((int) $end <= (int) $start)
                $wrongEnd = "Must be later than the start hour";
            else {
                /* If the date is today, ensure the hour is not past.
                 * Date and hour are compared by lexicographic order in the
                 * yyyymmdd and hhmm formats. */
                $dateString = $year
                        . sprintf('%02d', $month)
                        . sprintf('%02d', $day);
                $endHour = sprintf('%02d', floor($end / 2))
                        . ($end % 2 == 0 ? "00" : "30");
                if (strcmp($dateString, date("Ymd")) == 0
                        && strcmp($endHour, date('Hi')) <= 0)
                    $wrongEnd = "The end hour is past";
            }
        }

        /* if the date and hour of the event is valid, ensure the event does not
         * overlap with any other event scheduled in the same date for the same
         * resource */
        if (!$wrongDate && !$wrongStart && !$wrongEnd && !$wrongResource) {
            $eventsForDate =
                    Event::getEventsByDate($day, $month, $year);

            /* iterate on the events scheduled for the same date */
            foreach ($eventsForDate as $e) {

                /* Two events do not overlap if one of them starts before and
                 * ends before or equal the begin hour of the other, or if
                 * one starts after or equal and ends after the other.
                 * Negating this logical expression with De Morgan's theorem,
                 * the following condition is obtained.
                 * The event $e must be different from the event itself, when
                 * validating an event edit instead of an event creation. */
                if (($resource == $e->getResource()->getId())
                        &&  ($selfId != $e->getId())
                        && !($start < $e->getStart() && $end <= $e->getStart())
                        && !($start >= $e->getEnd() && $end > $e->getEnd())) {
                    $wrongStart = "The selected time interval overlaps with " .
                            "another event.";
                    $wrongEnd = $wrongStart;
                    break;
                }
            }
        }

        /* notes validation */
        if (!$notes || !is_string($notes))
            $notes = Null;
        else {
            /* trim when too long */
            $notes = mb_strimwidth($notes, 0, $notesLen - 1, "");
        }

        /* return an associative array, containing the value of the validated
         * fields or the error messages for invalid data */
        return array(
                'wrongDate' => $wrongDate,
                'wrongResource' => $wrongResource,
                'wrongStart' => $wrongStart,
                'wrongEnd' => $wrongEnd,
                'day' => $day,
                'month' => $month,
                'year' => $year,
                'resource' => $resource,
                'start' => $start,
                'end' => $end,
                'notes' => $notes,
                'allValid' => !($wrongDate ||
                                $wrongResource ||
                                $wrongStart ||
                                $wrongEnd));
    }

    /**
     * \brief Get the event with a specified id.
     * @param  int $id Id for the event.
     * @return Event   Object for the event, or Null if not found.
     * @throws InvalidArgumentException when a parameter is missing or invalid.
     * @throws DbQueryException if an operation on the database failed.
     */
    public static function getEventById($id) {
        if (!isset($id))
            throw new InvalidArgumentException(
                    "getEventById: missing parameter");
        if (!filter_var($id, FILTER_VALIDATE_INT))
            throw new InvalidArgumentException(
                    "getEventById: invalid parameter");

        /* variable for result */
        $event = Null;

        $query = "SELECT Username, Day, Month, Year, Start, End, " .
                 "Resources.Name, Resources.Id, Role, Notes " .
                 "FROM Events " .
                 "JOIN Users ON Events.Owner_id = Users.Id " .
                 "JOIN Resources ON Events.Resource_id = Resources.Id " .
                 "WHERE Events.Id = ?";

        $mysqli = Database::getInstance()->connect(); /* db connection */
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param("d", $id);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result($owner, $day, $month, $year, $start, $end,
                $resName, $resId, $type, $notes);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        if ($stmt->fetch()) {
            $resource = new Resource($resId, $resName);
            $event = new Event($id, $owner, $day, $month, $year, $start, $end,
                    $resource, $type, $notes);
        }

        $stmt->close();
        $mysqli->close(); /* close db connection */

        return $event;
    }

    /**
     * \brief Get all the events scheduled on a certain date.
     * @param  int $day   Day of the date (0-28/31).
     * @param  int $month Month of the date (1-12).
     * @param  int $year  Year of the date.
     * @return EventSet   A set containing the events for the date.
     * @throws InvalidArgumentException when a parameter is missing or invalid.
     * @throws DbQueryException if an operation on the database failed.
     */
    public static function getEventsByDate($day, $month, $year) {
        if (!isset($day) || !isset($month) || !isset($year) ||
            !is_numeric($day) || !is_numeric($month) || !is_numeric($year))
            throw new InvalidArgumentException(
                    "getEventsByDate: missing or invalid parameter");
        if (!checkdate($month, $day, $year))
            throw new InvalidArgumentException(
                    "getEventsByDate: the parameters does not form " .
                    "a valid date");

        /* container for the events */
        $events = new EventSet();

        $query = "SELECT Events.Id, Username, Start, End, Resources.Name, " .
                 "Resources.Id, Role, Notes " .
                 "FROM Events " .
                 "JOIN Users ON Events.Owner_id = Users.Id " .
                 "JOIN Resources ON Events.Resource_id = Resources.Id " .
                 "WHERE Day = ? AND Month = ? AND Year = ?";

        $mysqli = Database::getInstance()->connect(); /* db connection */
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param(
            "ddd",
            $day,
            $month,
            $year);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $bind = $stmt->bind_result($id, $owner, $start, $end, $resName, $resId,
                $type, $notes);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind result");

        while ($stmt->fetch()) {
            $resource = new Resource($resId, $resName);
            $e = new Event($id, $owner, $day, $month, $year, $start, $end,
                    $resource, $type, $notes);
            $events->addEvent($e);
        }

        $stmt->close();
        $mysqli->close(); /* close db connection */

        return $events;
    }

    /**
     * \brief Save the event object in the database.
     * @throws DbQueryException if an operation on the database failed.
     */
    public function insertEvent() {
        /* insert the event in the database
         * and update the event count for the user owning the event, with a
         * database transaction */
        $query1 = "INSERT INTO Events
                   (Owner_id, Resource_id, Day, Month, Year, Start, End, Notes)
                   VALUES
                   (?, ?, ?, ?, ?, ?, ?, ?)";
        $query2 = "UPDATE Users
                   SET Created_events = Created_events + 1
                   WHERE Id = ?";

        $mysqli = Database::getInstance()->connect();

        /* init, prepare and bind stmt1 */
        $stmt1 = $mysqli->stmt_init();
        $stmt1->prepare($query1);
        if (!$stmt1)
            throw new DbQueryException($stmt1->errno,
                    $stmt1->error, "Stmt prepare");
        $bind = $stmt1->bind_param(
            "ddddddds",
            $this->owner,
            $this->resource->getId(),
            $this->day,
            $this->month,
            $this->year,
            $this->start,
            $this->end,
            $this->notes);
        if (!$bind)
            throw new DbQueryException($stmt1->errno,
                    $stmt1->error, "Stmt bind param");

        /* init, prepare and bind stmt2 */
        $stmt2 = $mysqli->stmt_init();
        $stmt2->prepare($query2);
        if (!$stmt2)
            throw new DbQueryException($stmt2->errno,
                    $stmt2->error, "Stmt prepare");
        $bind = $stmt2->bind_param("d", $this->owner);
        if (!$bind)
            throw new DbQueryException($stmt2->errno,
                    $stmt2->error, "Stmt bind param");

        /* start transaction */
        $mysqli->autocommit(False);

        if (!$stmt1->execute()) {
            $mysqli->rollback();
            throw new DbQueryException($stmt1->errno,
                    $stmt1->error, "Stmt execute");
        }
        if (!$stmt2->execute()) {
            $mysqli->rollback();
            throw new DbQueryException($stmt2->errno,
                    $stmt2->error, "Stmt execute");
        }

        /* all seems ok, commit changes to the database */
        if (!$mysqli->commit()) {
            $mysqli->rollback();
            throw new DbQueryException($mysqli->errno,
                    $mysqli->error, "Commit");
        }

        /* reset autocommit and release resources */
        $stmt1->close();
        $stmt2->close();
        $mysqli->autocommit(True);
        $mysqli->close();
    }

    /**
     * \brief Edit an existing event in the database.
     * @throws DbQueryException if an operation on the database failed.
     */
    public function editEvent() {
        $query = "UPDATE Events
                  SET
                     Resource_id = ?,
                     Day = ?,
                     Month = ?,
                     Year = ?,
                     Start = ?,
                     End = ?,
                     Notes = ?
                  WHERE Id = ?";

        $mysqli = Database::getInstance()->connect();
        $stmt = $mysqli->stmt_init();
        $stmt->prepare($query);
        if (!$stmt)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt prepare");

        $bind = $stmt->bind_param(
                "ddddddsd",
                $this->resource->getId(),
                $this->day,
                $this->month,
                $this->year,
                $this->start,
                $this->end,
                $this->notes,
                $this->id);
        if (!$bind)
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt bind param");

        if (!$stmt->execute())
            throw new DbQueryException($stmt->errno,
                    $stmt->error, "Stmt execute");

        $stmt->close();
        $mysqli->close(); /* close db connection */
    }
}
?>
