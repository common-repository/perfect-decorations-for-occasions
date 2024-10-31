<?php
echo '<strong>' . __( 'Available in:', $this->slug ) . '</strong>';
if ( is_array( $feeds ) && ! empty( $feeds ) ) {
	echo '<ul class="event_feeds">';
	foreach ( $feeds as $feed ) {
		printf( '<li data-dfo-feed-id="%d" data-dfo-feed-name="%s" data-dfo-feed-price="%s">', $feed->id, $feed->name, $feed->price );
		printf( '%s <button data-dfo-buy-feed class="uk-button uk-button-small uk-button-success uk-margin-left">&euro; %s/mo</button>', $feed->name, $feed->price );
		echo '</li>';
	}
	echo '</ul>';
} else {
	_e( 'We\'re sorry, but it seems this event does not appear in any feeds. Please contact support', $this->slug );
}
