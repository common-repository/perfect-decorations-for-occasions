<?php
/**
 * @version 1.0.0
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza
 */
namespace Perfect\DecorationsForOccasions\Helpers;

/**
 * Class Input
 * @package Perfect\DecorationsForOccasions\Helpers
 * Helper related to handling input
 */
class Input {
	static public function isMD5($string){
		return preg_match('/^[a-f0-9]{32}$/i', $string);
	}
}