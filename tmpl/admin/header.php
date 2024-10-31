<?php
/**
 * @version 1.1.5
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
use Perfect\DecorationsForOccasions\Helpers\Auth;

// No direct access
function_exists( 'add_action' ) or die;
//Well... fuck
$reg = \Perfect\DecorationsForOccasions\PFactory::getRegistry();
$clear = $reg->get('clear_cart', false) ? 'true' : 'false'; //yay for PHP casting false to empty string...
//We need to export some information for the JS libraries to use. Not the best idea I've ever had, but I've also had 7 coffees already
?>
<script>
	var dfo = dfo || {};
	dfo.cart_url = '<?php echo $this->cart_url; ?>';
	dfo.home_url = '<?php echo home_url(); ?>';
	dfo.uid = '<?php echo Auth::getUID(); ?>';
	dfo.token = '<?php echo Auth::getCustomerSecret(); ?>';
	dfo.clear_cart = '<?php echo $clear; ?>';
</script>
<?php
//Cover :D
if ( ! $this->sub->isValid() ) {
	?>
	<div class="dfo-cover">
		<div class="dfo-fix-center">
			<div class="dfo-content">
				<a href="#" id="start_trial" class="uk-button uk-button-success dfo-trial-btn uk-width-1-2">Start a free
					14 day trial</a>
				<a href="#" id="referer_toggle">Someone recommended Decorations For Occasions to you? Click here!</a>

				<div id="referer" class="uk-form uk-margin-top">
					<input class="uk-width-1-4" type="text" placeholder="Enter their e-mail address here"/>
					<button class="uk-button uk-button-primary fake-btn">Save</button>
					<span class="uk-form-help-inline dfo-info"></span>
				</div>
			</div>
			<div class="dfo-spinner dfo-activating">
				<i class="uk-icon-circle-o-notch uk-icon-spin uk-icon-large"></i> Activating your trial, please wait
			</div>
		</div>
	</div>
<?php
}
?>
<div class="uk-margin-top">
	<div class="uk-clearfix">
		<div class="uk-float-left">
			<h2>Perfect Decorations For Occasions</h2>
		</div>
		<div class="uk-float-right">
			<p class="uk-float-left uk-margin-right">
				<?php
				if ( $this->sub->isValid() ) {
					//Yay for doing controller-stuff in the view, butt fuck it ;)
					$dec_model  = new \Perfect\DecorationsForOccasions\Models\Decorations();
					$feed_model = new \Perfect\DecorationsForOccasions\Models\Feeds();
					//Ownership - feeds
					$owned_feeds = $feed_model->getOwnedCount();
					$free_feeds  = $feed_model->getFreeCount();
					//Ownership - decorations
					$owned_dec = $dec_model->getOwnedCount();
					$free_dec  = $dec_model->getFreeCount();
					//Now for events that don't have decorations bound, but will (presumably) in the future
					$missing_free = $dec_model->getMissingFreeCount();
					$missing_owned = $dec_model->getMissingOwnedCount();
					//Summy, sum
					$owned_dec += $missing_owned;
					$free_dec += $missing_free;
					$dec_count  = $dec_model->getTotalCount() + $dec_model->getMissingCount();
					$feed_count = $feed_model->getTotalCount();
					if ( $this->sub->isTrial() ) {
						printf(
							'Trial active. You have access to all %d decorations and %d channels until <strong>%s</strong>.<br />',
							$dec_count,
							$feed_count,
							$this->sub->getTrialDate()
						);
						echo 'After this date you will have access to ';
						if ( $owned_feeds > 0 ) {
							printf( '%d decoration%s in %d channel%s you purchased and ', $owned_dec, ($owned_dec>1?'s':''), $owned_feeds, ($owned_dec>1?'s':'') );
						}
						printf( '%d decoration%s in %d free channel%s.', $free_dec, ($free_dec > 1 ? 's':''), $free_feeds, ($free_feeds > 1 ? 's':'') );
					} else {
						//Expired
						if ( $owned_feeds == 0 ) {
							echo 'Trial expired. ';
						}
						echo 'You have access to ';
						if ( $owned_feeds > 0 ) {
							printf( '%d decoration%s in %d channel%s you purchased and ', $owned_dec, ($owned_dec>1?'s':''), $owned_feeds, ($owned_feeds>1?'s':'') );
						}
						printf( '%d decoration%s in %d free channel%s.<br/>Browse decorations and purchase more channels.', $free_dec, ($free_dec>1?'s':''), $free_feeds , ($free_feeds?'s':''));
					}
				}
				?></p>

			<a href="https://www.perfect-web.co/my-subscriptions" class="dfo-my-account" target="_blank">My account settings</a>
		</div>
		<div class="uk-float-right dfo-cart">
			<div class="cart-toggler">
				<i class="uk-icon-shopping-cart icon uk-icon-medium"></i>

				<div class="uk-badge uk-badge-success cart-badge uk-badge-notification">0</div>
			</div>
			<div class="cart-items">
				<ul>

				</ul>
				<button class="checkout-button" id="checkout">Proceed to checkout</button>
			</div>
		</div>
	</div>
	<div class="uk-grid dfo-tabs">
		<?php
		$tabs = array(
			'Occasions List'         => 'feeds',
			'Occasions Timeline'     => 'main',
			'Suggest new Occasions'  => 'suggest',
			'Extend your Free Trial' => 'extend',
			'Options'                => 'settings'
		);
		if ( $this->view === 'perfect-decorations-for-occasions' ) {
			$this->view = 'feeds'; //Default to feeds view. Hacky, but it works :p
		}
		foreach ( $tabs as $name => $view ) {
			if ( $this->view === $view ) {
				$class = 'active';
			} else {
				$class = '';
			}
			printf( '<div class="uk-width-small-1-5 %s"><a href="%s">%s</a></div>', $class, $this->route( $view ), $name );
		}

		?>
	</div>
	<div id="perfect_main" class="uk-grid">
		<div id="perfect_messages"
		     class="uk-margin-top uk-margin-bottom uk-width-small-1-1"><?php $this->loadTmpl( 'messages' ); ?></div>
