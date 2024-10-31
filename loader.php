<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
// No direct access

namespace Perfect\DecorationsForOccasions;

//DEV:
//define( 'DEBUG_MODE', true );

function_exists( 'add_action' ) or die;
//Autoload our classes
spl_autoload_register( __NAMESPACE__ . '\\autoload' );

function autoload( $cls ) {
	$cls = ltrim( $cls, '\\' );
	if ( strpos( $cls, __NAMESPACE__ ) !== 0 ) {
		return;
	}

	$cls = str_replace( __NAMESPACE__, '', $cls );

	$path = 'includes' .
	        str_replace( '\\', DIRECTORY_SEPARATOR, $cls ) . '.php';

	require_once( $path );
}