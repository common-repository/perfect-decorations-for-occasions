<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */
namespace Perfect\DecorationsForOccasions;

/**
 * Class Subscription
 * @package Perfect\DecorationsForOccasions
 */
class Subscription
{

    protected static $instance;

    protected $registry;

    protected $data;


    public static function getInstance()
    {
        if (self::$instance == false) {
            self::$instance = new Subscription();
        }

        return self::$instance;
    }

    private function __construct() //This class is a singleton
    {
        $this->registry = new Registry();
        $this->data = $this->registry->get('subscription', false);
    }

    public function isValid()
    {
        if ($this->isTrial()) {
            return true; //Simpler case, trial gives access to everything
        }
        return $this->registry->get('subscription.activated', false);
    }

    public function isTrial()
    {
        if (!isset($this->data->trial_until)) {
            return false;
        }
        $today = date('Y-m-d');

        return ($today <= $this->data->trial_until);

    }

    public function setTrial($expiration)
    {
        $this->registry->set('subscription.trial_until', $expiration);
        $this->registry->set('subscription.activated', true);
        $this->registry->save();
    }

    public function trialInform()
    {
        if ($this->data->trial_inform) {
            $this->registry->set('subscription.trial_inform', false);
            $this->registry->save();
            return true;
        }

        return false;
    }

    public function getTrialDate()
    {
        return $this->data->trial_until;
    }

    public function isTrialExpired()
    {
	    if (!isset($this->data->trial_until)) {
		    return null;
	    }
	    $today = date('Y-m-d');

	    return ($today > $this->data->trial_until);
    }


}