<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
//if uninstall not called from WordPress exit
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}
//We need to do it here as Setup.php might already be deleted...
global $wpdb;
//Delete option
delete_option('perfect-decorations-for-occasions');
//Delete tables:
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_decorations`");
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_effects`");
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_events`");
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_eventsdecorations`");
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_feeds`");
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_feedsevents`");
$wpdb->query("DROP TABLE IF EXISTS `{$wpdb->prefix}dfo_overrides`");