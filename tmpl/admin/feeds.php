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
<script>
    dfo.id_list = JSON.parse('<?php echo json_encode($id_list, JSON_HEX_APOS); ?>');
</script>
<div class="uk-grid uk-width-1-1">
    <div class="uk-width-2-3 dfo-event-details">
        <p class="dfo-spinner-text">
            <i class="uk-icon-spinner uk-icon-spin"></i> <?php _e('Loading event details, please wait...', $this->slug); ?>
        </p>
        <?php
        if (!\Perfect\DecorationsForOccasions\PFactory::getSub()->isValid()) {
            //Cover page - get the top rated one even though we don't have the db yet
            printf('<iframe
                src="https://decorationsforoccasions.com/?url=%s" scrolling="no" class="dfo-demo-frame dfo-empty-db"></iframe>', rawurlencode(home_url() . '?hide_effects=true'));
        } else {
            //Load the event :3
            echo '<div class="dfo-event" data-dfo-autoload-event-id="'.$autoload_id.'"></div>';
        }
        ?>
    </div>
    <div class="uk-width-1-3">
        <div class="uk-search dfo-search uk-width-1-1 uk-margin-bottom">
            <input class="uk-search-field dfo-search-field" type="search" placeholder="Search...">
        </div>
        <a href="<?php echo $this->route('feeds', array('context' => 'top')); ?>"
           class="uk-button uk-width-1-1 uk-button-success uk-margin-bottom">Top Rated Decorations</a>
        <a href="<?php echo $this->route('feeds', array('context' => 'featured')); ?>"
           class="uk-button uk-width-1-1 uk-button-success uk-margin-bottom">Featured Decorations</a>

        <div id="side-nav" class="dl-menuwrapper uk-width-1-1">
            <?php
            \Perfect\DecorationsForOccasions\Helpers\HTML::printTreeMenu($tree, 'dl-menu dl-menuopen', $this->route('feeds', array('context' => 'all')));
            ?>
        </div>
        <!-- /dl-menuwrapper -->
        <a href="<?php echo $this->route('feeds', array('context' => 'all')); ?>"
           class="uk-button uk-width-1-1 uk-button-success">All Decorations</a>
    </div>
</div>
<?php $this->loadTmpl('footer'); ?>
