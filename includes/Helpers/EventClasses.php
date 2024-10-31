<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions\Helpers;

/**
 * Class EventClasses
 * @package Perfect\DecorationsForOccasions\Helpers
 * Helper used to retrieve a CSS class for an event to be displayed in FullCalendar
 */
class EventClasses {
	static public function getClass( $is_trial = false, $event ) {
		$today = date( 'Y-m-d' );
		if ( $event->date_end < $today ) {
			return 'event-past';
		}
		if ( $is_trial === false && ! $event->owned ) { //all events are "owned" in the trial
			return 'event-not-owned';
		}
		if ( $event->enabled ) {
			if ( $event->date_start <= $today && $event->date_end >= $today ) {
				return 'event-displayed';
			} else {
				return 'event-enabled';
			}
		} else {
			return 'event-disabled';
		}
	}
}