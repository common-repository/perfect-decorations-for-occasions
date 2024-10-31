<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions\Helpers;

use \DateTime;

/**
 * Class Date
 * @package Perfect\DecorationsForOccasions\Helpers
 * Helper related to handling dates
 */
class Date {
	/**
	 * @param string $format The format this date should be in
	 * @param string $date The string representation of date
	 *
	 * @return bool True if $date is a valid representation, false otherwise
	 */
	static public function isValid( $format, $date ) {
		$dt = DateTime::createFromFormat( $format, $date );

		return $dt && $dt->format( $format ) == $date;
	}

	/**
	 * Calculates the days between today and a given date.
	 *
	 * @param string $date The date in question
	 *
	 * @return bool|int Number of days until given date or false on failure. Can be negative.
	 */
	static public function daysLeft( $date ) {
		return ( isset( $date ) ) ? floor( ( strtotime( $date ) - time() ) / 60 / 60 / 24 ) : false;
	}
}