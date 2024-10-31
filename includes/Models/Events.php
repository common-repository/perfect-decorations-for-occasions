<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions\Models;

use Perfect\DecorationsForOccasions\Helpers\SQL;
use Perfect\DecorationsForOccasions\Logger;
use Perfect\DecorationsForOccasions\Registry;

/**
 * Class DataModel
 * @package Perfect\DecorationsForOccasions
 */
class Events
{

    /**
     * An internal comfort method to retrieve the display mode of events. Defaults to 'all'
     *
     * @return int 1 if the mode is set to display all unless explicitly disabled, 0 otherwise
     */
    private function getDisplayMode()
    {
        $registry = new Registry();

        return ($registry->get('display.mode', 'all') === 'all') ? 1 : 0;
    }

    /**
     * Internal helper function that returns the basic SQL query string for getting events, since it's quite long...
     * @return string The basic event query
     */
    private function getBasicQuery()
    {
        //Get the default event mode:
        $mode = $this->getDisplayMode();

        $query = 'SELECT DISTINCT e.id, e.name, e.description, IFNULL(o.start, e.date_start) AS date_start, IFNULL(o.end, e.date_end) AS date_end, ';
        $query .= 'IFNULL(o.enabled, ' . $mode . ') AS enabled, available_start, available_end ';
        $query .= 'FROM #__dfo_events e LEFT JOIN #__dfo_overrides o ON e.id = o.event_id ';

        return $query;
    }

    /**
     * Similar to getBasicQuery, but also includes information about event ownership
     * @return string The extended event query
     */
    private function getOwnershipQuery()
    {
        //TODO: Consider revising somehow to avoid duplicate SQL statements
        $mode = $this->getDisplayMode();

        $query = 'SELECT DISTINCT e.id, e.name, e.description, IFNULL(o.start, e.date_start) AS date_start, IFNULL(o.end, e.date_end) AS date_end, ';
        $query .= 'IFNULL(o.enabled, ' . $mode . ') AS enabled, available_start, available_end, ';
        $query .= 'EXISTS(SELECT 1 FROM #__dfo_feeds AS f INNER JOIN #__dfo_feedsevents AS fe ON f.id = fe.feed_id WHERE f.owned=1 AND fe.event_id = e.id) AS owned ';
        $query .= 'FROM #__dfo_events e LEFT JOIN #__dfo_overrides o ON e.id = o.event_id ';

        return $query;

    }
	/**
	 * Grabs events that start this week, but not today
	 *
	 * @param bool $limit_feeds Should we filter by owned events?
	 *
	 * @return mixed An array with events
	 */
	public function getThisWeekEvents($limit_feeds = true)
	{
		global $wpdb;
		$tomorrow = date('Y-m-d',strtotime('tomorrow'));
		$next_week = date('Y-m-d', strtotime('+7 days'));
		if ($limit_feeds === false) {
			//Trial mode
			$query = $this->getBasicQuery();
		} else {
			//Normal mode
			$query = $this->getOwnershipQuery();
		}

		//Common for both...
		$query .= " HAVING date_start BETWEEN '$tomorrow' AND '$next_week'";


		if ($limit_feeds !== false) {
			//Aaaand just for normal mode:
			$query .= ' AND owned = 1';
		}

		return $wpdb->get_results(SQL::fixPrefix($query));
	}
	/**
	 * Grabs events that start today
	 *
	 * @param bool $limit_feeds Should we filter by owned events?
	 *
	 * @return mixed An array with events
	 */
	public function getTodayEvents($limit_feeds = true)
    {
        global $wpdb;
        $today = date('Y-m-d');
        if ($limit_feeds === false) {
            //Trial mode
            $query = $this->getBasicQuery();
        } else {
            //Normal mode
            $query = $this->getOwnershipQuery();
        }

        //Common for both...
        $query .= " HAVING date_start = '$today'";


        if ($limit_feeds !== false) {
            //Aaaand just for normal mode:
            $query .= ' AND owned = 1';
        }

        return $wpdb->get_results(SQL::fixPrefix($query));
    }

    /**
     * Grabs events that should be shown at this point in time
     *
     * @param bool $limit_feeds Whether to limit returned events by ownership. False if the user has a trial active
     * @return mixed Active events
     */
    public function getActiveEvents($limit_feeds = true)
    {
        global $wpdb;
        $today = date('Y-m-d'); //Not really using NOW() because it returns DATETIME
        if ($limit_feeds === false) {
            //Trial mode
            $query = $this->getBasicQuery();
        } else {
            //Normal mode
            $query = $this->getOwnershipQuery();
        }

        //Common for both...
        $query .= " HAVING '$today' BETWEEN date_start AND date_end AND enabled = 1";


        if ($limit_feeds !== false) {
            //Aaaand just for normal mode:
            $query .= ' AND owned = 1';
        }

        return $wpdb->get_results(SQL::fixPrefix($query));
    }

    /**
     * Returns events that are bound to a specific feed
     *
     * @param int $id The ID of the feed in question
     *
     * @return mixed Events bound to this feed
     */
    public function getFeedEvents($id)
    {
        global $wpdb;
        $query = 'SELECT id, name, description, date_start, date_end FROM #__dfo_events AS e INNER JOIN #__dfo_feedsevents AS fe ON fe.event_id = e.id WHERE fe.feed_id = ' . $id;

        return $wpdb->get_results(SQL::fixPrefix($query));
    }

    /**
     * Returns events that are set to happen between the two specified dates, but not necessarily enabled
     *
     * @param $start string Start date, in Y-m-d format
     * @param $end string End date, in Y-m-d format
     * @param $feeds array An array of feed ids to filter by
     *
     * @return mixed Events that meet the specified criteria
     */
    public function getInterval($start, $end, $feeds = array())
    {
        /*
         * Same as getActiveEvents - using an alias in the WHERE clause is a no-no so we use the HAVING clause
         */
        global $wpdb;

        $query = $this->getOwnershipQuery();

        if (!empty($feeds)) {
            $query .= 'LEFT JOIN #__dfo_feedsevents AS fe ON fe.event_id = e.id ';
            $query .= 'WHERE fe.feed_id IN (' . implode(',', $feeds) . ') ';
        }

        $query .= "HAVING (date_start BETWEEN '{$start}' AND '{$end}') OR ('{$start}' BETWEEN date_start AND date_end) ";
        $query .= 'ORDER BY date_start';
        $events = $wpdb->get_results(SQL::fixPrefix($query));

        return $events;

    }

    /**
     * Grabs details about a specified event
     *
     * @param $id int ID of the event in questoin
     *
     * @return mixed The specified event or empty array if none with this ID exists
     */
    public function getEvent($id)
    {
        global $wpdb;
        $query = $this->getOwnershipQuery() . 'WHERE id =' . $id;

        return $wpdb->get_row(SQL::fixPrefix($query));
    }

    public function getWithRelationship()
    {
        global $wpdb;
        $query = 'SELECT e.id, e.name, fe.feed_id, featured FROM #__dfo_events e INNER JOIN #__dfo_feedsevents fe ON fe.event_id = e.id ';
        $query .= 'INNER JOIN #__dfo_eventsdecorations ed ON ed.event_id = e.id ';
        $query .= 'INNER JOIN #__dfo_decorations d ON d.id = ed.decoration_id ';
        $query .= 'ORDER BY (d.upvotes - d.downvotes) DESC';

        return $wpdb->get_results(SQL::fixPrefix($query));
    }

    public function getIDList($featured_only)
    {
        global $wpdb;
        $query = 'SELECT e.id FROM #__dfo_events e ';
        $query .= 'INNER JOIN #__dfo_eventsdecorations ed ON ed.event_id = e.id ';
        $query .= 'INNER JOIN #__dfo_decorations d ON d.id = ed.decoration_id ';
        if ($featured_only) {
            $query .= 'WHERE featured = 1 ';
        }
        $query .= 'ORDER BY (d.upvotes - d.downvotes) DESC';

        return $wpdb->get_results(SQL::fixPrefix($query));
    }

    /**
     * Sets overrides for a specific event
     *
     * @param $id ID of the event in question
     * @param $date_start New start date
     * @param $date_end New end data
     * @param $enabled Whether the event is enabled. Can be 'null' (as a string) to not change the default feed behaviour
     */
    public function setOverrides($id, $date_start, $date_end, $enabled)
    {
        global $wpdb;
        if ($enabled !== 'null') {
            $enabled = ($enabled) ? 1 : 0;
        }
        //We assume all data passed to this function is already valid!
        $query = "INSERT INTO #__dfo_overrides (event_id, start, end, enabled) VALUES ($id, '$date_start', '$date_end', $enabled)"; //NULL is not a valid INT in PHP
        //The space at the start of this string is necessary!
        $query .= ' ON DUPLICATE KEY UPDATE start = VALUES(start), end = VALUES(end), enabled = IFNULL(VALUES(enabled), enabled)';

        $wpdb->query(SQL::fixPrefix($query));
    }

    /**
     * Deletes overrides for a specific event
     *
     * @param $id ID of the event in question
     */
    public function deleteOverrides($id)
    {
        global $wpdb;
        $query = "DELETE FROM #__dfo_overrides WHERE event_id = " . $id;
        $wpdb->query(SQL::fixPrefix($query));
    }
}