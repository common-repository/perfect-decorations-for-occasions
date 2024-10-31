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
 * Class Registry
 * @package Perfect\DecorationsForOccasions
 */
class Form extends \PerfectCoreForm {
	/**
	 * Get HTML code with fieldset containing all fields
	 *
	 * @param string $group  Fieldset name
	 * @return string
	 */
	public function displayFieldset($group) {

		$fieldset = $this->getFieldset($group);

		$html = '<fieldset name="' . $group . '" class="'.$group.'">';
		foreach ($fieldset as $field) {
			$html .= $field->displayWrapped();
		}
		$html .= '</fieldset>';

		return $html;
	}
}