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
	<div class="dfo_batch" style="display:none;">
		<?php _e( 'With selected: ', $this->slug ); ?>
		<a href="#" data-dfo-batch="activate"
		   class="uk-button uk-button-primary"><?php _e( 'Activate', $this->slug ); ?></a>
		<a href="#" data-dfo-batch="deactivate" class="uk-button"><?php _e( 'Deactivate', $this->slug ); ?></a>
	</div>
	<div id='calendar'></div>
	<select data-dfo-feeds-filter class="feeds-filter" multiple data-placeholder="All channels">
		<?php
		if ( is_array( $feeds ) ) {
			foreach ( $feeds as $feed ) {
				printf( '<option value="%d">%s</option>', $feed->id, $feed->name );
			}
		}
		?>
	</select>

	<div class="legend">
		<h4><?php _e( 'Legend', $this->slug ); ?></h4>

		<p><i class="event event-disabled"></i><?php _e( 'Disabled event', $this->slug ); ?></p>

		<p><i class="event event-enabled"></i><?php _e( 'Enabled event', $this->slug ); ?></p>

		<p><i class="event event-displayed"></i><?php _e( 'Currently displayed event', $this->slug ); ?></p>

		<p><i class="event event-not-owned"></i><?php _e( 'Not in any owned channels', $this->slug ); ?></p>

		<p><i class="event event-past"></i><?php _e( 'Past event', $this->slug ); ?></p>
	</div>
	<div class="uk-modal dfo-event-details">
		<div class="uk-modal-dialog">
			<a class="uk-modal-close uk-close"></a>
			<p class="dfo-spinner">
				<i class="uk-icon-spinner uk-icon-spin"></i> <?php _e( 'Loading event details, please wait...', $this->slug ); ?>
			</p>

			<div class="dfo-event">
				<!-- PLACEHOLDER -->
			</div>
		</div>
	</div>
</div>
<?php $this->loadTmpl( 'footer' ); ?>
