<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */

// No direct access
function_exists( 'add_action' ) or die;
?>
<?php $this->loadTmpl( 'header' ); ?>
<div class="uk-width-small-1-1">
	<h2><?php _e( 'Options', $this->slug ); ?></h2>

	<form method="POST" action="<?php echo $this->route( 'settings' ); ?>" class="uk-form-stacked">
		<?php echo $form; ?>
	</form>
	<p class="advanced_text">
		<?php
		$update_str = $last_update > 0 ? date( 'Y-m-d H:i', $last_update ) : 'Never';
		echo __( 'Last time data was downloaded from the service: ', $this->slug ) . '<strong>' . $update_str . '</strong> (' . date_default_timezone_get() . ')';
		?>
	</p>

</div>
<?php $this->loadTmpl( 'footer' ); ?>
