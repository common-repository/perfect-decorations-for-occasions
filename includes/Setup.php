<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

use \stdClass;

/**
 * Class Setup
 * @package Perfect\DecorationsForOccasions
 */
class Setup {
	const OPTION_NAME = 'perfect-decorations-for-occasions';

	public static function Activate() {
		global $wpdb;
		//TODO: Consider some goddamn error-checking for once...
		//HERE BE DRAGONS
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_decorations` (
          `id` int(10) unsigned NOT NULL,
          `name` varchar(255) NOT NULL,
          `featured` tinyint(1) unsigned NOT NULL,
          `upvotes` int(10) NOT NULL,
          `downvotes` int(10) NOT NULL,
          `local_vote` tinyint(1) unsigned NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_events` (
          `id` int(10) unsigned NOT NULL,
          `name` varchar(255) NOT NULL,
          `description` text NOT NULL,
          `date_start` date NOT NULL,
          `date_end` date NOT NULL,
          `available_start` date NOT NULL,
          `available_end` date NOT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_eventsdecorations` (
          `event_id` int(11) NOT NULL,
          `decoration_id` int(11) NOT NULL,
          PRIMARY KEY (`event_id`,`decoration_id`)
        ) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_feeds` (
          `id` int(11) unsigned NOT NULL,
          `name` varchar(255) NOT NULL,
          `description` text NOT NULL,
          `price` DECIMAL(4,2) NOT NULL,
          `owned` tinyint(1) unsigned NOT NULL,
          `category` tinyint(1) unsigned NOT NULL,
          PRIMARY KEY (`id`)
        ) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_feedsevents` (
          `feed_id` int(10) unsigned NOT NULL,
          `event_id` int(10) unsigned NOT NULL,
          PRIMARY KEY (`feed_id`,`event_id`)
        ) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_effects` (
          `decoration_id` int(10) unsigned NOT NULL,
          `type` tinyint(1) unsigned NOT NULL,
          `file_path` varchar(255) NOT NULL,
          `options` text NOT NULL,
          KEY `decoration_id` (`decoration_id`),
          KEY `type_path` (`type`,`file_path`)
        ) DEFAULT CHARSET=utf8;';
		$sql[] = 'CREATE TABLE IF NOT EXISTS `#__dfo_overrides` (
          `event_id` int(11) NOT NULL,
          `start` date DEFAULT NULL,
          `end` date DEFAULT NULL,
          `enabled` tinyint(1) DEFAULT NULL,
          PRIMARY KEY (`event_id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
		foreach ( $sql as $query ) {
			$query = str_replace( '#__', $wpdb->prefix, $query );
			$wpdb->query( $query );
		}
		//Setup the options in DB
		$initial_info                  = new stdClass();
		$initial_info->setup_performed = true;
		update_option( 'perfect-decorations-for-occasions', $initial_info );
		//Register the `cron` task:
		wp_schedule_event( strtotime('tomorrow 09:00'), 'daily', 'dfo_daily_digest' );
		wp_schedule_event( strtotime('tomorrow 09:00'), 'daily', 'dfo_service_sync' );
	}

	public static function Deactivate() {
		wp_clear_scheduled_hook( 'dfo_service_sync' );
		wp_clear_scheduled_hook( 'dfo_daily_digest' );
	}
}