<?php
require_once __DIR__ . "/Event.php";

/*!
 * \brief This class is a container for Event objects, providing some
 * 		  facilities for event search.
 * @author Martino Pilia <martino.pilia@gmail.com>
 * @date 2015-06-26
 *
 * This class provides a container with some search features under limited
 * circumstances. The container is iterable, and it is transversed following
 * the chronological order of the events.
 */
class EventSet implements Iterator {

    private $events; /*!< array Array of Event objects. */

    /*!
     * \brief Construct an empty set.
     */
    public function __construct() {
        $this->events = array();
    }

    /**!
     * \brief Get the number of events contained in the set.
     * @return int The number of contained events.
     */
    public function getSize() {
        return count($this->events);
    }

    /*!
     * \brief Add an event to the set.
     * @param  Event $e Event object to be added.
     * @return True when added successfully, False otherwise.
     * @throws InvalidArgumentException when $e is not an Event object.
     */
    public function addEvent($e) {
        if (!isset($e) || !is_a($e, 'Event'))
            throw new InvalidArgumentException("addEvent method accept Event " .
                    "objects only");

        if ($e == Null || in_array($e, $this->events))
            return False;

        $this->events[] = $e;
        return True;
    }

    /*!
     * \brief Return the first matching event with a certain start and resource.
     * @param  int      $hour     Start hour for the event.
     * @param  int      $min      Start minutes for the event (accepted: 0 or 30).
     * @param  Resource $resource Resource for the event.
     * @return Event    The first matching event.
     * @throws InvalidArgumentException when a parameter is missing or the $min
     *         value is different than 0 and 30.
     */
    public function getEventByStart($hour, $min, Resource $resource) {
        if (!isset($hour) || !isset($min) || !isset($resource))
            throw new InvalidArgumentException("getEventByStart method " .
                    "requires three parameters.");
        if ($min != 0 && $min != 30)
            throw new InvalidArgumentException("getEventByStart: accepted ".
                    "values for \$min are 0 and 30 only.");

        /* convert hour:minutes into the number of half hours from midnight */
        $start = $hour * 2 + ($min == 30 ? 1 : 0);

        /* linear search */
        foreach ($this->events as $e) {
            if ($e->getStart() == $start
                && $e->getResource()->getId() == $resource->getId())
                return $e;
        }

        return Null;
    }

    /**
     * \brief Rewind the container to its first element.
     */
    public function rewind() {
        reset($this->events);

        /* sort $events in chronological order */
        usort($this->events, 'Event::compare');
    }

    /**
     * \brief Return the current element in the container iteration.
     * @return Event object for the current iteration.
     */
    public function current() {
        return current($this->events);
    }

    /**
     * \brief Return the key of the current element.
     * @return int Key of the current element.
     */
    public function key() {
        return key($this->events);
    }

    /**
     * \brief Move to the next element in the container.
     */
    public function next() {
        return next($this->events);
    }

    /**
     * \brief Check if the current position in the container iteration
     * 		  is valid.
     * @return bool True if the position is valid, False otherwise.
     */
    public function valid() {
        $key = key($this->events);
        return $key !== Null && $key !== False;
    }
}
?>
