<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

/**
 * Class Logger
 * @package Perfect\DecorationsForOccasions
 */
class Logger {
//TODO: Make this shitty class usable outside our dev enviroment. Oh well.
	public static function record( $message ) {
		if ( ! defined( 'DEBUG_MODE' ) ) {
			return; //Yay for fucking function call overhead, dumbass.
		}
        $file_path = dirname( dirname( __FILE__ ) ) . '/log.txt';
		$handle    = fopen( $file_path, 'a+' );
		if ( $handle ) {
			fwrite( $handle, '[' . date( 'd-m-Y H:i:s:u' ) . ']> ' . $message . "\r\n" );
			fclose( $handle );
		}
	}

	public static function recordF( $message, $arguments ) {
		self::record( sprintf( $message, $arguments ) );
	}

	public static function recordDump( $var ) {
		self::record( var_export( $var, true ) );
	}
}