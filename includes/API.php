<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

use Perfect\DecorationsForOccasions\Helpers\Auth;

/**
 * Class API
 * @package Perfect\DecorationsForOccasions
 * Handles the calls from service to WP :)
 */
class API
{
    public static function process()
    {
        if (!isset($_GET['dfo_api_task'])) {
            return; //nothing to do here
        }
        Logger::record('Api called!');
        switch ($_GET['dfo_api_task']) {
            case 'subinfo':
                self::subInfo();
                break;
            case 'force_sync':
                self::forceDBSync();
                break;
            default:
                die('400'); //so that the service knows something went wrong... also, no http_response_code()
                break;
        }
        exit;
    }

    protected static function subInfo()
    {
        //Check if we're waiting for this info
        $reg = PFactory::getRegistry();
        $allowed = $reg->get('api.expecting_contact', false);
        if (!$allowed) {
            Logger::record('API call failed - not expecting contact');
            die('403');
        }
        $trial_expiration = isset($_POST['trial_expiration']) ? $_POST['trial_expiration'] : null;
        $secret = isset($_POST['secret']) ? $_POST['secret'] : null;
        if ($secret === null || $trial_expiration === null) {
            die('400');
        }
        Logger::record('Setting secret and trial expiration');
        Auth::setCustomerSecret($secret);
        PFactory::getSub()->setTrial($trial_expiration);
        //Check for queued sync:
        $reg = PFactory::getRegistry();
        if ($reg->get('queued_sync', false)) {
            Sync::performAssetSync();
        }
        die('200');
    }

    protected static function forceDBSync()
    {
        $auth = isset($_POST['auth']) ? $_POST['auth'] : null;
        if (Auth::getCustomerSecret() !== $auth) {
            //If someone know the secret of this WP instance, we're fucked anyway
            Logger::record('Force sync failed because of invalid auth token');
            die('403');
        }
	    //We assume it's time to clear the cart. It's not, but shhh!
	    $reg = PFactory::getRegistry();
	    $reg->set('clear_cart', true);
	    $reg->save();
	    //Download data
        Sync::execute(true);
        Logger::record('Forced to sync by the service');
        die('200');
    }

}