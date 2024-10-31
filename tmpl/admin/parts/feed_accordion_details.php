<p>
	<?php
	if ( empty( $feed->description ) ) {
		_e( 'Sorry, no description is available at this moment.', $this->slug );
	} else {
		echo $feed->description;
	}
	?>
</p>
<?php
if ( is_array( $events ) && ! empty( $events ) ) {
	echo '<h4>' . __( 'Events in this channel', $this->slug ) . '</h4>';
	echo '<ul>';
	foreach ( $events as $event ) {
		printf( '<li>%s</li>', $event->name );
	}
	echo '</ul>';
}