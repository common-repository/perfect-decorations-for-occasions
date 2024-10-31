<?php
/**
 * @version 1.0.0
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza
 */

// No direct access
function_exists( 'add_action' ) or die;
?>
<?php $this->loadTmpl( 'header' ); ?>
	<div class="uk-width-small-1-1">
		<div class="uk-grid">
			<div class="uk-width-small-1-2">
				<h1><?php _e( 'Decorations For Occasions', $this->slug ); ?></h1>

				<p><?php _e( 'Decorations For Occasions description', $this->slug ); ?></p>
			</div>

			<div class="uk-width-small-1-2">
				<a href="" id="connect" class="uk-button uk-button-primary uk-margin-bottom">Login</a>
				<a href="" id="start_trial" class="uk-button uk-button-success uk-button-large uk-width-small-1-1">Start
					a free 30
					day trial</a>
			</div>
		</div>
		<h2><?php _e( 'Decorations Examples', $this->slug ); ?></h2>

		<p>TODO: Slider</p>
	</div>
<?php $this->loadTmpl( 'footer' ); ?>