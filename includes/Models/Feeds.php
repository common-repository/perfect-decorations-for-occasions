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
class Feeds {

	public function getEventFeeds( $id ) {
		global $wpdb;
		$query = 'SELECT id, name, price, owned FROM #__dfo_feeds AS f INNER JOIN #__dfo_feedsevents AS fe ON fe.feed_id = f.id WHERE fe.event_id = ' . $id;

		return $wpdb->get_results( SQL::fixPrefix( $query ) );
	}

	public function getFeedsList() {
		global $wpdb;
		$query = 'SELECT id, name, price, owned, category FROM #__dfo_feeds ORDER BY name';
		$feeds = $wpdb->get_results( SQL::fixPrefix( $query ) );

		return $feeds;
	}

	public function getFeed( $id ) {
		global $wpdb;

		$query = 'SELECT id, name, description FROM #__dfo_feeds WHERE id =' . $id;
		$feed  = $wpdb->get_row( SQL::fixPrefix( $query ) );

		return $feed;
	}

	public function getCartFeeds() {
		global $wpdb;
		$query = 'SELECT id, name, price FROM #__dfo_feeds WHERE owned = 0 ORDER BY name';

		return $wpdb->get_results( SQL::fixPrefix( $query ) );
	}

	public function getOwnedCount() {
		//Only truly owned
		global $wpdb;
		$query = 'SELECT COUNT(id) AS count FROM #__dfo_feeds WHERE owned = 1 AND price > 0.00';

		return $wpdb->get_row( SQL::fixPrefix( $query ) )->count;
	}

	public function getTotalCount(){
		global $wpdb;
		$query = 'SELECT COUNT(id) AS count FROM #__dfo_feeds';

		return $wpdb->get_row( SQL::fixPrefix( $query ) )->count;
	}

	public function getFreeCount() {

		global $wpdb;
		$query = 'SELECT COUNT(id) AS count FROM #__dfo_feeds WHERE owned = 1 AND price = 0.00';

		return $wpdb->get_row( SQL::fixPrefix( $query ) )->count;
	}
}