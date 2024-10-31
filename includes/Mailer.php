<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

use Perfect\DecorationsForOccasions\Models\Events;
use Perfect\DecorationsForOccasions\Helpers\HTML;

/**
 * Class Mailer
 * @package Perfect\DecorationsForOccasions
 */
class Mailer {
	private static function sendMail( $subject, $body ) {
		$to      = get_bloginfo( 'admin_email' );
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );
		//Actually send the message
		wp_mail( $to, $subject, $body, $headers );
	}

	public static function dailyDigest() {
		Logger::record( 'Running daily mail digest...' );
		if ( ! PFactory::getSub()->isValid() ) {
			Logger::record( 'Aborting daily digest - no sub info or invalid sub' );

			return;
		}
		$registry     = PFactory::getRegistry();
		$send_enabled = $registry->get( 'mail.daily_enabled', 1 );
		//If both are off, there's no point in doing anything more
		if ( ! $send_enabled ) {
			Logger::record( 'Daily digest exiting, user preferences' );

			return;
		}
		$model = new Events();
		//Get all events that *would* be active now
		$is_trial = ! PFactory::getSub()->isTrial();
		$events = $model->getTodayEvents( $is_trial); //Check the sub info but force disabled events to show
		if ( ! is_array( $events ) || empty( $events ) ) {
			return; //Empty list - nothing starts today
		}
		$upcoming = $model->getThisWeekEvents($is_trial);
		//Set up the available text variations
		$subjects     = array(
			'Celebration is coming. Join us!',
			'Decorate your website & let\'s celebrate',
			'Celebration time!'
		);
		$greetings    = array(
			'Hi there,',
			'Howdy,',
			'What\'s up?'
		);
		$text_enabled = array(
			array(
				'copy' => 'We\'re happy that you decided to join <strong>%1$s</strong> celebrations. That\'s how your website %2$s will look from %3$s.',
				'link' => 'Change display settings'
			),
			array(
				'copy' => '<strong>%1$s</strong> is coming and we\'re celebrating. From %3$s your website %2$s will look like this. Take a glimpse.',
				'link' => 'Change display settings'
			),
			array(
				'copy' => '<strong>%1$s</strong> is just around the corner - enjoy your website\'s new look:',
				'link' => 'Click here to change that'
			)
		);
		$text_disabled = array(
			array(
				'copy' => 'So, we\'ve heard that you decided not to celebrate <strong>%1$s</strong>. Are you sure you don\'t want %2$s to look like this?',
				'link' => 'Click here to start celebrating'
			),
			array(
				'copy' => 'We are preparing to celebrate <strong>%1$s</strong> soon. Join us! That’s how your website %2$s could look.',
				'link' => 'Click here to decorate it and join celebrations'
			),
			array(
				'copy' => 'Sadly, it looks like you\'re not going to celebrate <strong>%1$s</strong> with us :( That’s a pity your website %2$s won’t look like this. Maybe you can reconsider joining us?',
				'link' => 'Click here to join us & decorate your website'
			)
		);
		//Randomize the array
		shuffle($text_enabled);
		shuffle($text_disabled);
		//Build the message
		$data   = array(
			'events'  => $events,
			'greeting' => $greetings[ mt_rand( 0, 1 ) ],
			'enabled_copy' => $text_enabled,
			'disabled_copy' => $text_disabled,
			'upcoming' => $upcoming,
			'home_url' => preg_replace( "~^https?://[^/]+$~", "$0/", home_url() )
		);
		$markup = HTML::getIncludeOutput( 'mail/daily.php', $data );
		//Send the email with a random subject
		self::sendMail( $subjects[ mt_rand( 0, 2 ) ], $markup );
	}
}