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
<div class="uk-width-small-1-1 dfo-cart">
	<h2><?php _e( 'Select channels you wish to purchase', $this->slug ); ?></h2>

	<p>All channels billed annually</p>
	<?php
	if ( is_array( $feeds ) && ! empty( $feeds ) ) {
		foreach ( $feeds as $feed ) {
			$checked = '';
			if ( $selected_id == $feed->id ) {
				$checked = 'checked';
				$total   = $feed->price * 12;
			}
			printf( '<label for="%1$s"><input name="%1$s" id="%1$s" value="1" type="checkbox" data-dfo-price="%4$s" %3$s>%2$s - %4$s &euro; per month</label>', 'feed_' . $feed->id, $feed->name, $checked, $feed->price );
		}
	} else {
		echo '<p class="uk-alert uk-alert-danger">Sorry, no feeds found. Try manually refreshing the data</p>';
	}
	?>
	<h3>Total: <span data-dfo-cart-total><?php printf('%0.2f', $total); ?></span> &euro;
		<small>(exl. VAT)</small>
	</h3>
	<button class="uk-button uk-button-primary uk-button-large uk-margin-bottom" data-dfo-cart-proceed>Proceed to
		checkout
	</button>
</div>
<?php $this->loadTmpl( 'footer' ); ?>
