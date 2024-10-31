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
 * Class DataModel
 * @package Perfect\DecorationsForOccasions
 */
class Effects {
	public function getEventEffects( $id ) {
		global $wpdb;
		$query = 'SELECT type, file_path, options, e.decoration_id FROM #__dfo_effects e ';
		$query .= 'INNER JOIN #__dfo_eventsdecorations ed ON ed.decoration_id = e.decoration_id ';
		$query .= 'WHERE ed.event_id = ' . $id;

		return $wpdb->get_results( SQL::fixPrefix( $query ) );
	}

	public function getAvailableFiles($owned_only = true) {
		global $wpdb;

		$today = date( 'Y-m-d' ); //Not using NOW(), because it returns a datetime and for some reason I don't want casts in this overly complicated query

		$query = 'SELECT e.file_path, ev.id, ed.decoration_id, ';
		$query .= 'EXISTS(SELECT 1 FROM #__dfo_feeds AS f INNER JOIN #__dfo_feedsevents AS fe ON f.id = fe.feed_id WHERE f.owned=1 AND fe.event_id = ev.id) AS owned ';
		$query .= 'FROM #__dfo_effects AS e ';
		$query .= 'INNER JOIN #__dfo_eventsdecorations AS ed ON e.decoration_id = ed.decoration_id ';
		$query .= 'INNER JOIN #__dfo_events AS ev ON ed.event_id=ev.id ';
		$query .= "WHERE '{$today}' BETWEEN ev.available_start AND ev.available_end ";
		if($owned_only){
			$query .= 'HAVING owned=1';
		}
		return $wpdb->get_results( SQL::fixPrefix( $query ) );

	}
}