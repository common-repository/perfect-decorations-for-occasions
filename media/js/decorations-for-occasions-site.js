var DFO = DFO || {};
(function ($) {
    $(document).ready(function () {
        if (typeof Modernizr !== 'object') {
            return;
        }
        /* if (Modernizr.mq('only all and (max-width: 480px)') || screen.width < 480) {
         return; //Disable on small screens
         }*/
        if (typeof dfo_event_info === 'undefined' || typeof dfo_event_info.event !== 'object') {
            return; //Guard case
        }
        if (typeof dfo_event_info.misc !== 'object') {
            return; //another guard case
        }
        var isDisabled = localStorage.getItem('dfo-disable') === 'true';
        //Display limiting
        switch (dfo_event_info.misc.display_limiter) {
            case '0':
                isDisabled = isDisabled || (localStorage.getItem('dfo-displayed') === 'true');
                try {
                    localStorage.setItem('dfo-displayed', 'true');
                }
                catch (e) {
                    //Lol, this is bad
                }

                break;
            case '1':
                var d = new Date(),
                    now = d.getTime(),
                    last_visit = localStorage.getItem('dfo-last-visit-start') || 0;
                if (now - last_visit < 1800000) { //30 * 60 * 1000
                    isDisabled = true;
                }
                else {
                    try {
                        localStorage.setItem('dfo-last-visit-start', now);
                    }
                    catch (e) {
                        //Ignore, probably incognito mode
                    }
                }

                break;
            //Case 2 is no case ;)
        }
        DFO.Event = dfo_event_info.event;
        //Fix for forced evens
        if (DFO.Event.forced === 'true') {
            isDisabled = false;
        }
        var basePath = dfo_event_info.misc.path;
        //Yay for valid data and multiple alias-like instances
        var event = DFO.Event;
        for (var i = 0; i < event.effects.length; i++) {
            var options = {
                'source': basePath + event.effects[i].file_path,
                'type': event.effects[i].type,
                'sizer': window
            };
            //Ensure options are an object
            if (typeof event.effects[i].options === 'string') {
                event.effects[i].options = JSON.parse(event.effects[i].options);
            }
            //Merge the two objects together. We don't care about overwriting here, since it shouldn't occur
            for (var prop in event.effects[i].options) {
                options[prop] = event.effects[i].options[prop];
            }
            event.effects[i] = new DFO.Effect($('body'), options); //Init the effect
        }
        //This is quite important!
        event.running = !isDisabled;
        //-----------------------------------------------------------------
        // Main bubbles
        //-----------------------------------------------------------------
        var $wrap = $('<div />', {class: 'perfect-decorations-glued-box'}),
            $toggler = $('<div />', {class: 'dfo-bubble dfo-toggler'}).text('ON'),
            $info_bubble = $('<div class="dfo-bubble dfo-info"><i class="uk-icon uk-icon-info"></i></div>'),
            $badge = $('<div />', {class: 'dfo-badge'});

        $toggler.appendTo($wrap);
        $info_bubble.appendTo($wrap);
        $badge.appendTo($wrap);

        function toggleDisplay() {
            if (event.running) {
                for (var i = 0; i < event.effects.length; i++) {
                    event.effects[i].stop()
                }
                $toggler.text('ON');
                //Hide info
                $info.hide();
                $bubble1.hide();
                $bubble2.hide();
                $info.removeClass('opened');
            }
            else {
                for (var i = 0; i < event.effects.length; i++) {
                    event.effects[i].start()
                }
                $toggler.text('OFF');
            }
            $wrap.velocity({scale: [0, 1.0]},
                {
                    duration: 500,
                    easing: [450, 20], //spring physics
                    display: 'block',
                    complete: function () {
                        $wrap.toggleClass('dfo-closed');
                        $wrap.velocity('reverse');
                    }
                }
            );
            event.running = !event.running;
        }

        $toggler.click(function () {
            toggleDisplay();
            localStorage.setItem('dfo-disable', !event.running);
        });
        //-----------------------------------------------------------------
        // WTF box
        //-----------------------------------------------------------------
        var $info = $('<div />', {class: 'dfo-info-box'}),
            $bubble1 = $('<div />', {class: 'dfo-small-bubble'}),
            $bubble2 = $('<div />', {class: 'dfo-bigger-bubble'}),
            $header = $('<p />', {class: 'dfo-header'}).html('This is how I celebrate <a href="' + dfo_event_info.misc.read_more + '" target="_blank">' + event.name + '</a> on my website'),
            $read_more = $('<a />').text('Join me and let\'s celebrate together').attr('href', dfo_event_info.misc.read_more).attr('target', '_blank'),
            $close = $('<a href="#" class="dfo-close"><i class="uk-icon uk-icon-close"></i></a>');

        $header.appendTo($info);
        $read_more.appendTo($info);
        $close.appendTo($info);
        //Add the WTF box to the wrap
        $bubble1.appendTo($wrap);
        $bubble2.appendTo($wrap);
        $info.appendTo($wrap);
        //-----------------------------------------------------------------
        // WTF box events
        //-----------------------------------------------------------------
        function openInfoBubble() {
            var isOpen = $info.hasClass('opened');
            //Temp array for the loop
            var bubbles = [$bubble1, $bubble2, $info];
            for (var i = 0; i < 3; i++) {
                bubbles[(isOpen ? 2 - i : i)].velocity('stop');
                bubbles[(isOpen ? 2 - i : i)].velocity(
                    {
                        scale: (isOpen ? [0.0, 1.0] : [1.0, 0.0])
                    },
                    {
                        duration: 500,
                        easing: [450, 20], //sprind physics
                        display: 'block',
                        delay: 300 * i,
                        queue: false,
                        complete: function () {
                            if (i === 3) {
                                $info.toggleClass('opened');
                            }
                        }
                    }
                );
            }
        }

        $info_bubble.click(function (e) {
            e.preventDefault();
            $info_bubble.data('dfo-clicked', 'true');
            openInfoBubble();
        });
        $close.click(function (e) {
            e.preventDefault();
            $info_bubble.click();
        });
        $('body').append($wrap);
        //Should we run the effects?
        if (event.running) {
            for (var i = 0; i < event.effects.length; i++) {
                event.effects[i].start()
            }
            $toggler.text('OFF');
        }
        else {
            $wrap.addClass('dfo-closed');
            return;
        }
        if(DFO.Event.forced !== 'true'){
            setTimeout(function () {
                if ($info_bubble.data('dfo-clicked') === 'true') {
                    return;
                }
                openInfoBubble();
                setTimeout(function () {
                    if (!$wrap.hasClass('dfo-closed') && !$info.is(':hover') && $info_bubble.data('dfo-clicked') !== 'true') {
                        $info_bubble.click();
                    }
                }, 5000);
            }, 3000);
        }
        //Auto-off
        if (!!dfo_event_info.misc.auto_off_delay) {
            //Eh..
            var timeout = parseInt(dfo_event_info.misc.auto_off_delay);
            if (timeout > 0) {
                setTimeout(function () {
                    toggleDisplay();
                }, timeout * 1000); //timeout is in seconds
            }
        }
    });
})(jQuery);
