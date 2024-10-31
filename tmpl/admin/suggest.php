<?php
/**
 * @version 1.1.4
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 */

// No direct access
function_exists('add_action') or die;
?>
<?php $this->loadTmpl('header'); ?>
<div class="uk-width-small-1-1 uk-grid">
    <div class="uk-width-1-2 suggestions">
        <p>Vote for new occasions. Each month we will create 5 new decorations for most popular occasions.</p>
        <div class="suggestions-event">

        </div>
        <button class="uk-button uk-button-primary uk-width-2-3 add-event" data-dfo-type="1">Add your own</button>
    </div>
    <div class="uk-width-1-2 suggestions">
        <p>Vote for new occasions list. We will create new occasions lists according to your suggestions once it reaches 100 votes</p>
        <div class="suggestions suggestions-feed">

        </div>
        <button class="uk-button uk-button-primary uk-width-2-3 add-feed" data-dfo-type="2">Add your own</button>
    </div>
</div>
<div id="add" class="uk-modal">
<div class="uk-modal-dialog">
    <div class="uk-modal-header">Add a new suggestion</div>
        <form class="uk-form uk-form-stacked add-form">
            <div class="uk-form-row">
                <label class="uk-form-label" for="name">Name</label>
                <div class="uk-form-controls">
                    <input type="text" class="uk-width-1-1" id="name" placeholder="Make it quite descriptive">
                </div>
                <p class="uk-form-help-block chars-left-name">100 characters left</p>
            </div>
            <div class="uk-form-row">
                <label class="uk-form-label" for="description">Description</label>
                <div class="uk-form-controls">
                    <textarea id="description" class="uk-width-1-1" cols="30" rows="5" placeholder="Type something here :)"></textarea>
                </div>
	            <p class="uk-form-help-block chars-left-desc">1000 characters left</p>
            </div>
        </form>
    <div class="uk-modal-footer uk-text-right">
        <button type="button" class="uk-button uk-modal-close">Cancel</button>
        <button type="button" class="uk-button uk-button-primary btn-send">Send your suggestion</button>
    </div>
</div>
</div>
<?php $this->loadTmpl('footer'); ?>
