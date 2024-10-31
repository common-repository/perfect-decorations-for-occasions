<h3 class="uk-accordion-title"
    data-dfo-feed-id="<?php echo $feed->id; ?>" data-dfo-feed-name="<?php echo $feed->name; ?>"
    data-dfo-feed-price="<?php echo $feed->price; ?>">
	<?php echo $feed->name; ?>
	<?php
	if ( $feed->owned == 1 ) {
		//Owned/free feed
		echo '<small class="uk-float-right">You\'ve got it :)</small>';
	} else {
		//Paid feed
		printf( '<button data-dfo-buy-feed class="uk-button uk-button-small uk-button-success uk-float-right">&euro; %s/mo</button>', $feed->price );
		if ( $this->sub_info->is_trial ) {
			printf( '<small class="uk-float-right uk-margin-right">Free trial! %s days left to go</small>', \Perfect\DecorationsForOccasions\Helpers\Date::daysLeft( $this->sub_info->trial_until ) );
		}
	}
	?>
</h3>
<div class="uk-accordion-content">
	<p class="dfo-spinner"><i class="uk-icon-spinner uk-icon-spin"></i> Loading feed details...</p>
</div>