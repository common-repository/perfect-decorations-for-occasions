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


/**
 * Class Decorations
 * @package Perfect\DecorationsForOccasions\Models
 */
class Decorations
{
    public function getEventDecorations($id)
    {
        global $wpdb;
        $query = 'SELECT id, name, featured, upvotes, downvotes, local_vote FROM #__dfo_decorations d ';
        $query .= 'INNER JOIN #__dfo_eventsdecorations ed ON ed.decoration_id = d.id ';
        $query .= 'WHERE ed.event_id = ' . $id;

        return $wpdb->get_results(SQL::fixPrefix($query));
    }

    public function updateVotes($id, $up, $down, $my_vote)
    {
        global $wpdb;
        $query = 'UPDATE #__dfo_decorations SET ';
        $query .= "upvotes = {$up}, downvotes = {$down}, local_vote = {$my_vote} ";
        $query .= 'WHERE id = ' . $id;

        return $wpdb->query(SQL::fixPrefix($query));
    }

	public function getTotalCount(){
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT id) as total FROM #__dfo_decorations';
		return $wpdb->get_row(SQL::fixPrefix($query))->total;
	}

	public function getMissingCount(){
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT e.id) as total FROM #__dfo_events e ';
		$query .= 'LEFT JOIN #__dfo_eventsdecorations ed ON ed.event_id = e.id ';
		$query .= 'WHERE ed.decoration_id IS NULL';


		return $wpdb->get_row(SQL::fixPrefix($query))->total;

	}

	public function getOwnedCount(){
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT d.id) as total FROM #__dfo_decorations d ';
		$query .= 'INNER JOIN #__dfo_eventsdecorations ed ON ed.decoration_id = d.id ';
		$query .= 'INNER JOIN #__dfo_feedsevents fe ON fe.event_id = ed.event_id ';
		$query .= 'INNER JOIN #__dfo_feeds f ON f.id = fe.feed_id ';
		$query .= 'WHERE f.price > 0.00 AND f.owned = 1';


		return $wpdb->get_row(SQL::fixPrefix($query))->total;
	}

	public function getFreeCount(){
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT d.id) as total FROM #__dfo_decorations d ';
		$query .= 'INNER JOIN #__dfo_eventsdecorations ed ON ed.decoration_id = d.id ';
		$query .= 'INNER JOIN #__dfo_feedsevents fe ON fe.event_id = ed.event_id ';
		$query .= 'INNER JOIN #__dfo_feeds f ON f.id = fe.feed_id ';
		$query .= 'WHERE f.price = 0.00 AND f.owned = 1';


		return $wpdb->get_row(SQL::fixPrefix($query))->total;
	}

	public function getMissingOwnedCount(){
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT e.id) as total FROM #__dfo_events e ';
		$query .= 'LEFT JOIN #__dfo_eventsdecorations ed ON ed.event_id = e.id ';
		$query .= 'INNER JOIN #__dfo_feedsevents fe ON fe.event_id = e.id ';
		$query .= 'INNER JOIN #__dfo_feeds f ON f.id = fe.feed_id ';
		$query .= 'WHERE f.price > 0.00 AND f.owned = 1 AND ed.decoration_id IS NULL';


		return $wpdb->get_row(SQL::fixPrefix($query))->total;
	}

	public function getMissingFreeCount(){
		global $wpdb;
		$query = 'SELECT COUNT(DISTINCT e.id) as total FROM #__dfo_events e ';
		$query .= 'LEFT JOIN #__dfo_eventsdecorations ed ON ed.event_id = e.id ';
		$query .= 'INNER JOIN #__dfo_feedsevents fe ON fe.event_id = e.id ';
		$query .= 'INNER JOIN #__dfo_feeds f ON f.id = fe.feed_id ';
		$query .= 'WHERE f.price = 0.00 AND f.owned = 1 AND ed.decoration_id IS NULL';


		return $wpdb->get_row(SQL::fixPrefix($query))->total;
	}
}