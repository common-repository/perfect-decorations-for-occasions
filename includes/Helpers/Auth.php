<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions\Helpers;
use Perfect\DecorationsForOccasions\PFactory;

/**
 * Class Auth
 * @package Perfect\DecorationsForOccasions\Helpers
 * Helper related to user auth
 */
class Auth {
	static public function getCredentials() {
		return array(
			'email'  => get_bloginfo( 'admin_email' ),
			'domain' => get_bloginfo( 'url' ),
			'uid'    => self::getUID(),
		);
	}

	static public function getUID() {
		return md5( get_bloginfo( 'url' ) );
	}

	static public function getCustomerSecret() {
        //Not really a secret, just a token
		$reg = PFactory::getRegistry();

		return $reg->get( 'customer_secret' );
	}

	static public function clearCustomerSecret() {
		self::setCustomerSecret( null );
	}

	static public function setCustomerSecret( $secret ) {
		$reg = PFactory::getRegistry();
		$reg->set( 'customer_secret', $secret );
		$reg->save();
	}
}