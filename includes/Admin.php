<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */

namespace Perfect\DecorationsForOccasions;

//Imports
use Perfect\DecorationsForOccasions\Helpers\Auth;
use Perfect\DecorationsForOccasions\Helpers\Date;
use Perfect\DecorationsForOccasions\Helpers\EventClasses;
use Perfect\DecorationsForOccasions\Helpers\HTML;
use Perfect\DecorationsForOccasions\Models\Decorations;
use Perfect\DecorationsForOccasions\Models\Events;
use Perfect\DecorationsForOccasions\Models\Feeds;
use \stdClass;

use \PerfectCoreMedia;
use \PerfectCoreForm;
use \PerfectCoreApplication_Admin;

// No direct access
function_exists( 'add_action' ) or die;

class Admin extends PerfectCoreApplication_Admin {
	//TODO: Actual service integration!
	protected $ajax_tasks = array(
		'startTrial',
		'feedDetails',
		'eventsTimeline',
		'eventDetails',
		'forceServiceSync',
		'eventOverride',
		'eventReset',
		'updateVotes'
	);

	protected static $instance = null;

	protected $sub = null;

	public $cart_url = null;

	const DEFAULT_CACHE_AGE = 604800; //7 days (60*60*24*7)

	public function __construct( $file = null ) {

		parent::__construct( $file ? $file : dirname( __DIR__ ) . '/perfect-decorations-for-occasions.php' );

		$this->views['perfect-decorations-for-occasions'] = array(
			'name'      => 'Perfect Decorations For Occasions',
			'menu_name' => 'Decorations For Occasions',
			'menu_slug' => 'perfect-decorations-for-occasions',
		);
		$this->views['main']                              = array(
			'name'        => 'Timeline',
			'menu_name'   => 'Decorations For Occasions',
			'menu_slug'   => 'perfect-decorations-for-occasions',
			'parent_view' => 'perfect-decorations-for-occasions'
		);
		$this->views['feeds']                             = array(
			'name'        => 'Channels',
			'menu_name'   => 'Decorations For Occasions',
			'menu_slug'   => 'perfect-decorations-for-occasions',
			'parent_view' => 'perfect-decorations-for-occasions'
		);
		$this->views['settings']                          = array(
			'name'        => 'Options',
			'menu_name'   => 'Decorations For Occasions',
			'menu_slug'   => 'perfect-decorations-for-occasions',
			'parent_view' => 'perfect-decorations-for-occasions'
		);
		$this->views['suggest']                           = array(
			'name'      => 'Suggest new Occasions',
			'menu_slug' => 'perfect-decorations-for-occasions',
		);
		$this->views['extend']                            = array(
			'name'      => 'Extend your Free Trial',
			'menu_slug' => 'perfect-decorations-for-occasions',
		);

		add_filter( 'perfect_plugins_groups', array( $this, 'loadPerfectPluginsGroups' ) );
		add_filter( 'perfect_decorations_for_occasions_plugins', array(
			$this,
			'loadPerfectDecorationsForOccasionsPlugins'
		) );
		PerfectCoreForm::registerPath( $this->slug, dirname( __DIR__ ) . '/forms' );
	}

	public static function getInstance( $slug = 'perfect-decorations-for-occasions', $file = null ) {
		return parent::getInstance( $slug, $file );
	}

	private function registerAndLoadScript( $handle, $file_name, $deps = array( 'jquery' ), $ver = '1.0.0', $in_footer = true ) {
		PerfectCoreMedia::registerScript( $handle, $this->getPluginPath() . '/media/js/' . $file_name, $deps, $ver, $in_footer );
		PerfectCoreMedia::load( $handle );
	}
	/**
	 * Build admin menu
	 */
	public function addAdminMenu() {

		foreach ($this->views as $data) {

			if (isset($data['menu_name']) AND isset($data['menu_slug']) AND $data['menu_name'] AND $data['menu_slug'] AND !isset(self::$menu[ $data['menu_slug'] ])) {

				if (isset($data['menu_parent_slug']) AND $data['menu_parent_slug']) {

					if (isset(self::$menu[ $data['menu_parent_slug'] ])) {

						add_submenu_page( $data['menu_parent_slug'],
							__($data['name'], $this->slug), __($data['menu_name'], $this->slug),
							(isset($data['menu_capability']) AND $data['menu_capability']) ? $data['menu_capability'] : 'manage_options',
							$data['menu_slug'],
							array($this, 'displayView')
						);
					}
					else continue;
				}
				else {
					add_menu_page( __($data['name'], $this->slug), __($data['menu_name'], $this->slug),
						(isset($data['menu_capability']) AND $data['menu_capability']) ? $data['menu_capability'] : 'manage_options',
						$data['menu_slug'],
						array($this, 'displayView'),
						plugins_url('media/img/menu-icon.png', dirname(__FILE__))
					);
				}

				self::$menu[ $data['menu_slug'] ] = true;
			}
		}
	}
	public function init() {
		if ( parent::init() === false ) {
			return false;
		}

		PerfectCoreMedia::registerScript( 'decorations-for-occasions', $this->getPluginPath() . '/media/js/decorations-for-occasions.min.js', array( 'jquery' ), '1.0.0', true );
		PerfectCoreMedia::registerStyle( 'decorations-for-occasions', $this->getPluginPath() . '/media/css/decorations-for-occasions.min.css', array(), '1.0.0' );
		//Velocity
		PerfectCoreMedia::registerScript( 'velocity', $this->getPluginPath() . '/media/js/velocity.min.js', array( 'jquery' ), '1.0.0', true );
		//Calendar
		PerfectCoreMedia::registerScript( 'moment', $this->getPluginPath() . '/media/js/moment.min.js', array( 'jquery' ), '2.3.1', true );
		PerfectCoreMedia::registerScript( 'fullcalendar', $this->getPluginPath() . '/media/js/fullcalendar.min.js', array( 'jquery' ), '2.3.1', true );
		PerfectCoreMedia::registerStyle( 'fullcalendar', $this->getPluginPath() . '/media/css/fullcalendar.min.css', array(), '2.3.1' );
		//Chosen
		PerfectCoreMedia::registerScript( 'chosen', $this->getPluginPath() . '/media/js/chosen.jquery.min.js', array( 'jquery' ), '1.4.2', true );
		PerfectCoreMedia::registerStyle( 'chosen', $this->getPluginPath() . '/media/css/chosen.min.css', array(), '1.4.2' );
		//DLMenu
		PerfectCoreMedia::registerScript( 'dlmenu', $this->getPluginPath() . '/media/js/jquery.dlmenu.min.js', array( 'jquery' ), '1.4.2', true );
		PerfectCoreMedia::registerScript( 'modernizr', $this->getPluginPath() . '/media/js/modernizr.custom.js', array( 'jquery' ), '1.4.2', true );
		PerfectCoreMedia::registerStyle( 'dlmenu', $this->getPluginPath() . '/media/css/dlmenu.min.css', array(), '1.4.2' );

		//Load that shit
		PerfectCoreMedia::load( 'modernizr' );
		PerfectCoreMedia::load( 'uikit' );
		PerfectCoreMedia::load( 'uikit-addons' );
		PerfectCoreMedia::load( 'uikit-accordion' );
		PerfectCoreMedia::load( 'uikit-datepicker' );
		PerfectCoreMedia::load( 'velocity' );
		PerfectCoreMedia::load( 'glyphicon' );
		PerfectCoreMedia::load( 'uikit-slideshow' );
		PerfectCoreMedia::load( 'uikit-slideshow-fx' );
		PerfectCoreMedia::load( 'moment' );
		PerfectCoreMedia::load( 'fullcalendar' );
		PerfectCoreMedia::load( 'chosen' );
		PerfectCoreMedia::load( 'dlmenu' );

		PerfectCoreMedia::load( 'decorations-for-occasions' );

		$this->sub = PFactory::getSub();

		$this->cart_url = 'https://www.perfect-web.co/index.php?option=com_dfo_service&task=cart.process&uid=' . Auth::getUID();

		return true;
	}

	public function loadPerfectPluginsGroups( $items = array() ) {
		//TODO: Either remove it or make it actually function
		$slug = 'perfect-decorations-for-occasions';
		if ( ! isset( $items[ $slug ] ) OR $items[ $slug ]->installed !== true ) {
			$item            = new stdClass();
			$item->priority  = 0;
			$item->name      = __( 'Decorations For Occasions', $this->slug );
			$item->icon      = 'birthday-cake';
			$item->content   = __( 'Description of the group', $this->slug );
			$item->url       = self_admin_url( 'admin.php?page=' . $slug );
			$item->installed = true;
			$items[ $slug ]  = $item;
		}

		return $items;
	}

	public function loadPerfectDecorationsForOccasionsPlugins( $items = array() ) {
		//TODO: Either remove it or make it actually function
		if ( ! isset( $items[ $this->slug ] ) OR $items[ $this->slug ]->installed !== true ) {
			$item                 = new stdClass();
			$item->priority       = 100;
			$item->name           = __( 'Decorations For Occasions', $this->slug );
			$item->icon           = 'birthday-cake';
			$item->content        = __( 'Display decorative elements for various occasions.', $this->slug );
			$item->url            = $this->route( 'main' );
			$item->installed      = true;
			$items[ $this->slug ] = $item;
		}

		return $items;
	}

	public function processAjaxTaskEventsTimeline() {
		//NOT called thorugh pwebCore.ajax, so we don't use the usual wrapper
		//This is called by fullcalendar
		//See: http://fullcalendar.io/docs/event_data/events_json_feed/
		$start = isset($_POST['start']) ? $_POST['start'] : ''; //ISO 8601 (Y-m-d for dates only)
		$end   = isset($_POST['end']) ? $_POST['end'] : '';
		$feeds = isset($_POST['filter']) ? $_POST['filter'] : '';
		//Validate input
		if ( ! Date::isValid( 'Y-m-d', $start ) || ! Date::isValid( 'Y-m-d', $end ) ) {
			return; //Guard case to avoid unnecessary nesting (readability)
		}
		if ( is_array( $feeds ) ) {
			array_filter( $feeds, function ( $var ) {
				return ctype_digit( $var ); //filter the array so it contains only ints
			} );
		}
		$model = new Events();
		/*
		 * FullCalendar expects the end date to be a moment JUST after the actual end of an event.
		 * Hence, we need to SUBTRACT one day from the end date so that we don't fetch events that are not displayed anyway
		 */
		$end        = date( 'Y-m-d', strtotime( '-1 day', strtotime( $end ) ) );
		$events     = $model->getInterval( $start, $end, $feeds );
		$events_out = array();
		if ( is_array( $events ) && ! empty( $events ) ) {
			foreach ( $events as $event ) {
				$info         = new stdClass();
				$info->id     = $event->id;
				$info->title  = $event->name;
				$info->allDay = true; //no hour-based events, at least for now
				$info->start  = $event->date_start;
				/*
				 * Again, FullCalendar needs the end date to be a moment just after the actual end - so let's add a day.
				 */
				$info->end = date( 'Y-m-d', strtotime( '+1 day', strtotime( $event->date_end ) ) );
				$is_trial  = PFactory::getSub()->isTrial();
				//For setting the colors
				$info->className = EventClasses::getClass( $is_trial, $event );
				array_push( $events_out, $info );
			}
			//Output the info for FullCalendar
			echo json_encode( $events_out );
			die();
		}
	}

	public
	function processAjaxTaskEventReset() {
		$ajax          = new stdClass();
		$ajax->message = null;
		$ajax->data    = null;
		$ajax->success = false;

		$id = ctype_digit( $_POST['id'] ) ? $_POST['id'] : null;
		if ( is_null( $id ) ) {
			die( json_encode( $ajax ) ); //guard case - this should never happen
		}
		//Get this event details
		$model = new Events();
		$event = $model->getEvent( $id );
		if ( ! is_object( $event ) ) {
			//Not a valid event id
			$ajax->message = 'Cannot change properties for a non-existent event!';
			die( json_encode( $ajax ) );
		}
		$model->deleteOverrides( $event->id );
		//Reload the event and output new dates
		$new_event  = $model->getEvent( $id );
		$ajax->data = array(
			'date_start' => $new_event->date_start,
			'date_end'   => $new_event->date_end,
		);

		$ajax->success = true;
		die( json_encode( $ajax ) );
	}

	public
	function processAjaxTaskEventOverride() {
		$ajax          = new stdClass();
		$ajax->message = null;
		$ajax->data    = null;
		$ajax->success = false;

		$id = ctype_digit( $_POST['id'] ) ? $_POST['id'] : null;
		if ( is_null( $id ) ) {
			die( json_encode( $ajax ) ); //guard case - this should never happen
		}

		//Get this event details
		$model = new Events();
		$event = $model->getEvent( $id );
		if ( ! is_object( $event ) ) {
			//Not a valid event id
			$ajax->message = 'Cannot change properties for a non-existent event!';
			die( json_encode( $ajax ) );
		}

		$enabled = isset($_POST['toggle']) ? ! $event->enabled : 'null'; //yes, NULL as string

		$date_start = Date::isValid( 'Y-m-d', @$_POST['date_start'] ) ? $_POST['date_start'] : $event->date_start; //Yes, surpress warnings since we do the validation anyway.
		$date_end   = Date::isValid( 'Y-m-d', @$_POST['date_end'] ) ? $_POST['date_end'] : $event->date_end;

		//Check if the event can be set to occur at the specified dates:
		if ( $date_start > $date_end ) {
			$ajax->message = 'Event cannot end before it starts!';
			die( json_encode( $ajax ) );
		}
		if ( $date_end > $event->available_end || $date_start < $event->available_start ) {
			$ajax->message = 'Cannot reschedule an event past its availability!';
			die( json_encode( $ajax ) );
		}
		//All seems to be in order! :)
		$model->setOverrides( $event->id, $date_start, $date_end, $enabled );
		$ajax->success = true;
		die( json_encode( $ajax ) );
	}

	public
	function processAjaxTaskForceServiceSync() {
		$ajax          = new stdClass();
		$ajax->message = null;
		$ajax->data    = null;
		$ajax->success = false;

		if ( Sync::execute( true ) ) {
			$ajax->success = true;
		} else {
			$ajax->message = "Couldn't perform data sync at this moment, please try again later.";
		}

		die( json_encode( $ajax ) );
	}

	public
	function processAjaxTaskStartTrial() {
		$ajax          = new stdClass();
		$ajax->message = null;
		$ajax->data    = null;
		$ajax->success = false;

		$referer = filter_var( $_POST['referer'], FILTER_VALIDATE_EMAIL );

		$request_data            = Auth::getCredentials();
		$request_data['referer'] = $referer;

		$request = PFactory::getRequest();
		$request->APICall( 'trial', $request_data );
		//Check if we managed to succeed
		if ( $request->is_json && $request->json_data->trial_expiration ) {
			$ajax->success = true;
			//Save subscription info into the db
			PFactory::getSub()->setTrial( $request->json_data->trial_expiration );
			//And also request the secret
			Sync::requestSubInfo();
		} else {
			$ajax->message = $request->json_data->message;
		}

		die( json_encode( $ajax ) );
	}

	public
	function processAjaxTaskFeedDetails() {
		$ajax          = new stdClass();
		$ajax->message = null;
		$ajax->data    = null;
		$ajax->success = false;
		$id            = ctype_digit( $_POST['id'] ) ? $_POST['id'] : 0;
		if ( $id !== 0 ) {
			$feed_model = new Feeds();
			$feed       = $feed_model->getFeed( $id );
			if ( is_object( $feed ) ) {
				$event_model   = new Events();
				$events        = $event_model->getFeedEvents( $feed->id );
				$ajax->success = true;
				$ajax->data    = HTML::getViewOutput( $this, 'parts/feed_accordion_details', array(
					'feed'   => $feed,
					'events' => $events
				) );
			}
		}

		die( json_encode( $ajax ) );
	}

	public
	function processAjaxTaskEventDetails() {
		$ajax          = new stdClass();
		$ajax->message = null;
		$ajax->data    = null;
		$ajax->success = false;
		$id            = ctype_digit( $_POST['id'] ) ? $_POST['id'] : 0;
		if ( $id !== 0 ) {
			$events_model      = new Events();
			$event             = $events_model->getEvent( $id );
			$feeds_model       = new Feeds();
			$feeds             = $feeds_model->getEventFeeds( $id );
			$decorations_model = new Decorations();
			$decorations       = $decorations_model->getEventDecorations( $id );
			if ( is_object( $event ) ) {
				$ajax->success = true;
				$ajax->data    = HTML::getViewOutput( $this, 'parts/event_details', array(
					'event'            => $event,
					'feeds'            => $feeds,
					'decorations'      => $decorations,
					'is_trial'         => PFactory::getSub()->isTrial(),
					'is_trial_expired' => PFactory::getSub()->isTrialExpired()
				) );
			}
		}

		die( json_encode( $ajax ) );
	}

	public function processAjaxTaskUpdateVotes() {
		$response          = new stdClass();
		$response->message = '';
		$response->data    = null;
		$response->success = false;
		//Get the input
		$id        = ctype_digit( $_POST['id'] ) ? $_POST['id'] : null;
		$upvotes   = ctype_digit( $_POST['up'] ) ? $_POST['up'] : null;
		$downvotes = ctype_digit( $_POST['down'] ) ? $_POST['down'] : null;
		$my_vote   = in_array( (int) $_POST['vote'], array( 1, 2 ) ) ? $_POST['vote'] : null;
		//Validate it
		if ( is_null( $id ) || is_null( $upvotes ) || is_null( $downvotes ) || is_null( $my_vote ) ) {
			$response->message = 'Invalid input';
			die( json_encode( $response ) );
		}
		$model = new Decorations();
		$model->updateVotes( $id, $upvotes, $downvotes, $my_vote );
		$response->success = true;
		die( json_encode( $response ) );
	}

	protected function displayViewSuggest() {
		//Load additional JS code :)
		$this->registerAndLoadScript( 'suggest', 'suggest.dfo.js' );
		$data = array();
		$this->loadTmpl( 'suggest', $data );
	}

	protected function displayViewExtend() {
		//Empty data
		$data = array();
		$this->loadTmpl( 'extend', $data );
	}

	protected
	function displayViewPerfectDecorationsForOccasions() {
		//This should really have a different name, but PwebCore forces that:
		$this->displayViewFeeds(); //Default to feeds
	}


	protected
	function displayViewFeeds() {
		//Load additional JS & CSS
		$this->registerAndLoadScript( 'autocomplete', 'jquery.autocomplete.min.js' );
		$this->registerAndLoadScript( 'search', 'search.dfo.js' );


		$f_model = new Feeds();
		$feeds   = $f_model->getFeedsList();
		$e_model = new Events();
		$events  = $e_model->getWithRelationship(); //don't limit those, cause menu...
		//Yay for hardcoding the categories and 1-indexed arrays... /s
		$trunk       = array(
			1 => array( 'name' => 'Occasions for Hobbyists' ),
			2 => array( 'name' => 'National Occasions' ),
			3 => array( 'name' => 'Religious Occasions' ),
			4 => array( 'name' => 'Seasons' ),
		);
		$feeds_keyed = array();
		//Key to feeds array
		foreach ( $feeds as $item ) {
			$feeds_keyed[ $item->id ] = array(
				'name'     => $item->name,
				'category' => $item->category,
				'children' => array()
			);
		}
		//Bind events to feeds & generate names array
		foreach ( $events as $item ) {
			$feeds_keyed[ $item->feed_id ]['children'][] = array( 'name' => $item->name, 'id' => $item->id );
		}
		//And finally bind feeds to categories
		foreach ( $feeds_keyed as $item ) {
			$trunk[ $item['category'] ]['children'][] = $item;
		}
		//Now for the ID list. Yes, another query. Yes, I know. No, there wasn't enough time to do it right.
        $context = isset($_GET['context']) ? $_GET['context'] : '';
		$featured_only = $context !== 'all' && $context !== 'top';
		$id_list       = $e_model->getIDList( $featured_only );
		$size          = count( $id_list );
		foreach ( $id_list as $i => $item ) {
			if ( $i === 0 ) {
				$item->prev = $id_list[ $size - 1 ]->id;
			} else {
				$item->prev = $id_list[ $i - 1 ]->id;
			}
			if ( $i === $size - 1 ) {
				$item->next = $id_list[0]->id;
			} else {
				$item->next = $id_list[ $i + 1 ]->id;
			}
			$list_keyed[ $item->id ] = $item;
		}
		$autoload_id = isset($id_list[0]->id) ? $id_list[0]->id: '';
		if ( isset($_GET['display']) && in_array( $_GET['display'], array_keys( $list_keyed ) ) ) {
			$autoload_id = $_GET['display'];
		}
		$data = array(
			'tree'        => $trunk,
			'id_list'     => $list_keyed,
			'autoload_id' => $autoload_id,
		);
		$this->loadTmpl( 'feeds', $data );
	}

	protected
	function displayViewSettings() {
		$registry = new Registry();
		//Get the current settings
		$sync_frequency = $registry->get( 'sync.frequency', Sync::DEFAULT_CACHE_AGE ); //Defaults to 7 days
		$mode           = $registry->get( 'display.mode', 'all' ); //defaults to show all unless explicitly disabled
		$last_update    = $registry->get( 'sync.last_update', 0 );
		//Email settings
		$mail_daily_enabled  = $registry->get( 'mail.daily_enabled', 1 );
		//Auto-off
		$auto_off       = $registry->get( 'auto_off.delay', 0 );
		$display_limiter = $registry->get('display_limiter', 1); //defaults to 1 which is per visit
		$form_data      = array(
			'mode'                => $mode,
			'sync_frequency'      => $sync_frequency,
			'send_daily'  => $mail_daily_enabled,
			'auto_off'            => $auto_off,
			'display_limiter' => $display_limiter
		);
		//Get the form itself
		$form = new Form( $this->slug, 'settings', $form_data );

		if ( $form->submitted && $form->isValid() ) {
			//Save the user input into registry
			$registry->set( 'sync.frequency', $form->data['sync_frequency'] );
			$registry->set( 'display.mode', $form->data['mode'] );
			$registry->set( 'mail.daily_enabled', (bool) $form->data['send_daily'] );
			$registry->set( 'auto_off.delay', (int) $form->data['auto_off'] );
			$registry->set('display_limiter', (int) $form->data['display_limiter']);
			$registry->save();
			$this->enqueueMessage( __( 'Your settings were updated', $this->slug ), 'success' );
		}
		$data = array(
			'form'        => $form->displayForm(),
			'last_update' => $last_update
		);
		$this->loadTmpl( 'settings', $data );
	}

	protected
	function displayViewMain() {
		//Get the feeds for filter select
		$feed_model = new Feeds();
		$feeds      = $feed_model->getFeedsList();
		$this->loadTmpl( 'timeline', array( 'feeds' => $feeds ) ); //Events will be pulled by FullCalendar via an AJAX request
	}
}
