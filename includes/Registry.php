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
class Registry extends \PerfectCoreRegistry {
	/*
	 * I cannot make this class a singleton because it inherits from something that is not a singleton...
	 */
	public function __construct() {
		parent::__construct( null, 'perfect-decorations-for-occasions' );
	}

	/**
	 * Save plugin's data to WP options
	 *
	 * @return void
	 */
	public function save($name = null) { //Parameter just so PHP won't throw a notice...
		update_option( 'perfect-decorations-for-occasions', $this->data );
	}

	/**
	 * Get option from wordpress
	 *
	 * @param string $name
	 */
	protected function getOptions( $name ) {

		$this->name = $name;
		$settings_a = get_option( $name );

		if ( $settings_a === false ) {
			add_option( $name, array() );

			return;
		}

		array_walk_recursive( $settings_a, function ( &$value, $key ) {
			if ( ! is_object( $value ) AND ! is_array( $value ) ) {
				$value = stripslashes( $value );
			}
		} );

		$this->bindData( $this->data, $settings_a );
	}
}