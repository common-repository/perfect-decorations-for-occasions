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
<div class="uk-width-small-1-1">
    <h2>Extend your trial</h2>
    <p>Currently, you can extend your trial by referring other people to Decorations For Occasions.</p>
    <p>
        If they enter your e-mail address (<strong><?php echo get_bloginfo('admin_email');?></strong>) while creating their free trial,
        you will be granted additional 5 days of free trial!
    </p>
    <p>More ways to extend your free trial are coming soon.</p>
</div>
<?php $this->loadTmpl('footer'); ?>
