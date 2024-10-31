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
 * Class Auth
 * @package Perfect\DecorationsForOccasions\Helpers
 * Helper related to user auth
 */
class SQL {
	static public function fixPrefix( $sql ) {
		global $wpdb;

		return str_replace( '#__', $wpdb->prefix, $sql );
	}

	static public function splitQueries( $sql ) {
		//Since mysql_query/mysqli_query doesn't support multiple queries per execution we need to split them
		//Overwriting WPDb would not really be sensible
		//Lets just pray that nothing craps itself, since transactions are not available either (WP uses MyISAM, I think)
		if ( strpos( $sql, '/*STATEMENT END*/' ) === false ) {
			return false;
		}
		$sql_array = explode( '/*STATEMENT END*/', $sql );

		return $sql_array;
	}
}