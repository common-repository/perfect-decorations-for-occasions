<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */

namespace Perfect\DecorationsForOccasions;

//No direct access
use Perfect\DecorationsForOccasions\Helpers\HTML;
use Perfect\DecorationsForOccasions\Models\Effects;
use Perfect\DecorationsForOccasions\Models\Events;

function_exists('add_action') or die;

class Site extends \PerfectCoreApplication_Site
{

    public function run($event)
    {
        //Get this event's effects and insert the info into the object
        $effect_model = new Effects();
        $effects = $effect_model->getEventEffects($this->event->id);
        $this->event->effects = $effects;
        //Quickly check if any of the files that need to be loaded are missing:
        $missing_files = false;
        foreach ($effects as $effect) {
            if (!file_exists($this->getPluginPath(true) . '/media/feeds/' . $effect->file_path)) {
                $missing_files = true;
                $missing_id = $effect->decoration_id;
                Logger::recordF('Missing file: %s', $this->getPluginPath() . '/media/feeds/' . $effect->file_path);
                break;
            }
        }
        //If some files *are* missing, perform emergency sync:
        if ($missing_files) {
            if (!Sync::emergencySyncShown($missing_id)) {
                //Failed to do the emergency sync, so just... give up already
                return;
            }
        }

        $reg = PFactory::getRegistry();
        //Insert info about the path
        $misc = new \stdClass();
        $misc->path = plugins_url('media/feeds/', dirname(__FILE__));
        $misc->read_more = HTML::getTriviaLink($this->event->name);
        $misc->auto_off_delay = $reg->get('auto_off.delay', 0);
	    $misc->display_limiter = $reg->get('display_limiter', 1);
        //Doing it the WP way instead of using PerfectCoreMedia, because we want to pass some data to the script...
		wp_register_script('velocity', $this->getPluginPath() . '/media/js/velocity.min.js', array('jquery'));
        wp_register_script('modernizr', $this->getPluginPath() . '/media/js/modernizr.custom.js');
        wp_register_script('dfo-effect', $this->getPluginPath() . '/media/js/effect.dfo.js', array(
            'jquery',
            'modernizr',
            'velocity'
        ));
        wp_register_script('decorations-for-occasions', $this->getPluginPath() . '/media/js/decorations-for-occasions-site.js', array(
            'jquery',
            'velocity',
            'dfo-effect'
        ));

        wp_localize_script('decorations-for-occasions', 'dfo_event_info', array(
            'event' => $this->event,
            'misc' => $misc
        ));

        wp_enqueue_script('dfo-effect');
        wp_enqueue_script('decorations-for-occasions');
        //Since we're doing things the WP way, we'll include the script without PerfectCoreMedia as well, just to be consistent
        wp_register_style('decorations-for-occasions', $this->getPluginPath() . '/media/css/decorations-for-occasions-site.css');
        wp_enqueue_style('decorations-for-occasions');


        //Screw the former
        \PerfectCoreMedia::registerCore();
        \PerfectCoreMedia::load( 'uikit' );

    }

    public function __construct($file = null)
    {
        //TODO: Split this into multiple methods...
        parent::__construct($file ? $file : dirname(__DIR__) . '/perfect-decorations-for-occasions.php');
        $hide_effects = isset($_GET['hide_effects']) ? $_GET['hide_effects'] : false;
        $display_event = isset($_GET['display_event']) ? $_GET['display_event'] : false;

        if (is_admin() || $hide_effects === 'true') {
            //Do not continue in WP-Admin or if we're fetching the image
            return;
        }
        //Determine the subscription status:
        $is_trial = PFactory::getSub()->isTrial();
        //Get the necessary model
        $event_model = new Events();
        if (is_numeric($display_event)) {
            //Force a certain event
	        $e = $event_model->getEvent($display_event);
	        if(!empty($e) && $e instanceof \stdClass){
		        $e->forced = 'true';
	        }
            $active_events = array($e);
        } else {
            $active_events = $event_model->getActiveEvents(!$is_trial); //do we need to limit the events to owned ones?
        }
        if (!empty($active_events)) {
            //Grab a random event from the active ones -> we display only one at a time!
            $event = $active_events[array_rand($active_events)];
            if (is_null($event)) {
                return; //Invalid event
            }
            $this->event = $event;
            add_action('wp_loaded', array($this, 'run'));
        }
    }
}