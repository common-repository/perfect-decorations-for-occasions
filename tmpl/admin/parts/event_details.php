<div class="uk-grid uk-grid-collapse">
    <div class="uk-width-small-1-1">
        <h3><?php echo $event->name; ?></h3>

        <p>
            <?php
            if (!empty($event->description)) {
                echo $event->description;
                echo ' <a href="' . \Perfect\DecorationsForOccasions\Helpers\HTML::getTriviaLink($event->name) . '">' . __('Read more', $this->slug) . '</a>';
            }
            ?>
        </p>

        <div class="dfo-event-messages"></div>
        <?php
        if ($is_trial_expired && !$event->owned) {
            //Trial still going
            echo '<p class="uk-alert uk-alert-danger">Oh gosh! Your trial has ended. To display this event buy one of the occasions lists below.</p>';
            include_once('event_feed_info.php');
        }
        $top_dir = dirname(dirname(dirname(__FILE__)));
        ?>
        <div class="dfo-slider uk-margin-bottom">
            <iframe
                src="http://decorationsforoccasions.com/?url=<?php echo rawurlencode(home_url() . '/?hide_effects=true'); ?>&event_id=<?php echo $event->id; ?>"
                scrolling="no" class="dfo-demo-frame"></iframe>
            <div class="dfo-slider-controls-cover" data-dfo-decoration-id="<?php echo $decorations[0]->id; ?>">
                <div class="dfo-slider-controls">
                    <a href="#" title="Previous event" class="prev"><i class="uk-icon-arrow-left"></i></a>
                    <a href="#"
                       class="vote upvote<?php echo($decorations[0]->local_vote == 1 ? ' selected' : ''); ?>"><i
                            class="uk-icon-thumbs-o-up"></i><span><?php echo $decorations[0]->upvotes; ?></span>
                    </a>
                    <a href="#"
                       class="vote downvote<?php echo($decorations[0]->local_vote == 2 ? ' selected' : ''); ?>"><i
                            class="uk-icon-thumbs-o-down"></i><span><?php echo $decorations[0]->downvotes; ?></span></a>
                    <a href="#" title="Next event" class="next"><i class="uk-icon-arrow-right"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="uk-width-small-2-3">
        <p><?php _e('Display from', $this->slug); ?>
            <input type="text" class="date" id="dateStart" value="<?php echo $event->date_start; ?>"
                   data-uk-datepicker="{format:'YYYY-MM-DD', minDate: '<?php echo $event->available_start; ?>', maxDate: '<?php echo $event->available_end; ?>'}">
            <?php _e('to', $this->slug); ?>
            <input type="text" class="date" id="dateEnd" value="<?php echo $event->date_end; ?>"
                   data-uk-datepicker="{format:'YYYY-MM-DD', minDate: '<?php echo $event->available_start; ?>', maxDate: '<?php echo $event->available_end; ?>'}">
            <button class="uk-button uk-button-small uk-button-primary dfo-btn-resize"
                    data-dfo-event-id="<?php echo $event->id; ?>"
                    data-dfo-reschedule><?php _e('Set', $this->slug); ?></button>
            <button class="uk-button uk-button-small dfo-btn-resize" data-dfo-reset
                    data-dfo-event-id="<?php echo $event->id; ?>">
                <?php _e('Reset', $this->slug); ?>
            </button>
        </p>
        <p><?php
            printf(__('Available from <strong>%s</strong> to <strong>%s</strong>'), $event->available_start, $event->available_end);
            ?>
        </p>
        <?php
        if (!$event->owned) {
            //Trial still going
            include_once('event_feed_info.php');
        }
        ?>
    </div>
    <div class="uk-width-small-1-3">
        <?php
        $text = ($event->enabled) ? 'Disable this decoration' : 'Enable this decoration';
        $on_toggle = ($event->enabled) ? 'Enable this decoration' : 'Disable this decoration';
        $btn_class = ($event->enabled) ? '' : 'uk-button-primary';
        ?>
        <button
            class="uk-button <?php echo $btn_class; ?> uk-button-small uk-margin-small-bottom uk-width-1-1 dfo-btn-resize"
            data-dfo-ontoggle="<?php echo $on_toggle; ?>" data-dfo-event-id="<?php echo $event->id; ?>"
            data-dfo-publish-toggler>
            <?php echo $text; ?>
        </button>
	    <button class="uk-button uk-button-small uk-margin-small-bottom uk-width-1-1 dfo-btn-resize dfo-clear-cache">Refresh your website's image</button>
    </div>
</div>