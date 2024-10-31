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
 * Class Pfactory
 * @package Perfect\DecorationsForOccasions\Helpers
 * A basic factory for classes in this namespace
 */
class PFactory {

	static public function getSubscription() {
		return Subscription::getInstance();
	}

	static public function getSub() {
		//Alias
		return self::getSubscription();
	}

	static public function getRequest() {
		return new Request();
	}

	static public function getRegistry(){
		return new Registry();
	}
}