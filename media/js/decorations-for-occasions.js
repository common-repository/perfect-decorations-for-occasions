/*!
 * @version 1.0.0
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza
 */
var dfo = dfo || {};
dfo.getSpinner = function (text) {
    return jQuery('<p class="dfo-spinner-text"><i class="uk-icon-spinner uk-icon-spin"></i> ' + text + ' </p>');
};
dfo.spinner = function (className) {
    //Naaaasty :c
    return jQuery('<div class="dfo-spinner-m ' + className + '"><svg class="circular"><circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="2" stroke-miterlimit="10"/></svg></div>');
};

dfo.serviceAPICall = function (query, data, callback_done, callback_fail, type) {
    var that = this;
    jQuery.ajax({
        url: 'https://www.perfect-web.co/index.php?option=com_dfo_service&task=api.' + ( typeof query !== "undefined" ? query : "" ),
        type: type || "POST",
        crossDomain: true,
        jsonpCallback: "dfo_callback",
        cache: false,
        data: ( typeof data !== "undefined" && data ) ? data : {},
        dataType: "jsonp"
    }).done(function (response, textStatus, jqXHR) {
        if (response && typeof response.success !== "undefined") {
            if (response.success === true) {
                if (typeof callback_done === "function") {
                    callback_done.call(this, response);
                }
            }
            else {
                // server error message
                if (typeof callback_fail === "function") {
                    callback_fail.call(this, response);
                }
            }
        }
        else {
            // unrecognized server response
            if (typeof callback_fail === "function") {
                callback_fail.call(this, response);
            }
            pwebCore.message("Connection with remote server has failed. Please try again.", "danger", true);
        }

    }).fail(function (jqXHR, textStatus, errorThrown) {

        if (typeof callback_fail === "function") {
            callback_fail.call(this);
        }
        // handle error
        pwebCore.message("Connection with remote server has failed. Please try again.", "danger", true);
    });
};

dfo.simpleAjaxCrossDomain = function (URI, method, data, callback_done, callback_fail, callback_name, cache) {
    var that = this;
    jQuery.ajax({
        url: URI,
        type: method || "POST",
        crossDomain: true,
        timeout: 5000,
        jsonpCallback: callback_name || "dfo_callback",
        cache: cache || false,
        data: ( typeof data !== "undefined" && data ) ? data : {},
        dataType: "jsonp"
    }).done(function (response, textStatus, jqXHR) {
        if (response) {
            //Success - in theory
            if (typeof callback_done === "function") {
                callback_done.call(this, response);
            }
        }
        else {
            //Wat?
            if (typeof callback_fail === "function") {
                callback_fail.call(this);
            }
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        if (typeof callback_fail === "function") {
            callback_fail.call(this);
        }
        // handle error
        that.message("Connection with remote server has failed. Please try again.", "danger", true);
    });
};

dfo.message = function (message, type, selector) {
    // define type of alert
    if (typeof type === "undefined") {
        type = "";
    }
    switch (type) {
        case "error":
        case "danger":
            type = "danger";
            break;
        case "warning":
            type = "warning";
            break;
        case "success":
            type = "success";
            break;
        default:
            type = ""; // info
    }
    // create alert and hide it
    var $alert = jQuery("<div>", {
        "class": "uk-alert" + (type ? " uk-alert-" + type : ""),
        "data-uk-alert": "",
        "html": '<a href="" class="uk-alert-close uk-close"></a><p>' + message + '</p>'
    }).css("display", "none");

    if (typeof selector === "undefined") {
        selector = '#perfect_messages';
    }

    // insert alert
    jQuery(selector)["prepend"]($alert);
    $alert.slideDown("slow", function () {
        setTimeout(function () {
            $alert.slideUp("slow", function () {
                jQuery(this).remove();
            });
        }, 5000);
    });
};

dfo.calendarFilter = [];
//CART
var simplecart = function (items) {
    this.items = items || {};
    this.$element = null;
    this.total = 0.00;
    //Check for previous cart
    var old = localStorage.getItem('dfo-cart');
    if (!!old) {
        old = JSON.parse(old);
        for (var id in old) {
            this.addItem(id, old[id].name, old[id].price, true);
        }
    }
};
simplecart.prototype.refresh = function (silent) {
    this.$element = jQuery('.dfo-cart');
    if (Object.keys(this.items).length > 0) {
        this.$element.fadeIn();
        if (!silent)
            jQuery('.cart-items', this.$element).slideDown();
    }
    else {
        if (!silent)
            jQuery('.cart-items', this.$element).slideUp();
        this.$element.fadeOut();
    }

    //Delete old entries
    jQuery('.cart-items li', this.$element).remove();
    this.total = 0.00;
    //Build again
    for (var index in this.items) {
        var feed = this.items[index];
        this.total = this.total + parseFloat(feed.price);
        jQuery('<li data-id="' + index + '">' + feed.name + ' - &euro; ' + feed.price + '/mo<a href="#"><i class="uk-icon-trash-o uk-icon-hover"></i></a></li>').appendTo('.cart-items ul', this.$element);
    }
    jQuery('<li class="total">Total: <span class="price">&euro;' + this.total.toFixed(2) + '</span>/mo <span class="info">(exl. VAT, billed annually)</span></li>').appendTo('.cart-items ul', this.$element);
    jQuery('.cart-badge', this.$element).text(Object.keys(this.items).length);
    //Save the data for restoring after page reload
    localStorage.setItem('dfo-cart', JSON.stringify(this.items));
};
simplecart.prototype.addItem = function (id, name, price, silent) {
    this.items[id] = {
        'name': name,
        'price': price
    };
    //Disable the button
    var el = jQuery('[data-dfo-feed-id="' + id + '"] [data-dfo-buy-feed]');
    el.attr('disabled', true);
    el.text('Already in your cart');
    this.refresh(silent);
};
simplecart.prototype.removeItem = function (id) {
    if (!!this.items[id]) {
        delete this.items[id];
        //Enable the button, if present
        jQuery('[data-dfo-feed-id="' + id + '"] [data-dfo-buy-feed]').attr('disabled', false);
        this.refresh();
    }
};

simplecart.prototype.clearItems = function () {
    for (var index in this.items) {
        this.removeItem(index);
    }
};

dfo.cart = new simplecart();

if(dfo.clear_cart === 'true'){
    dfo.cart.clearItems(); //yay.
}

jQuery(document).ready(function ($) {
    dfo.EventRender = function ($container, $spinner, id, callback_next, callback_prev, reSize) {
        //If there is a spinner, show it
        if ($spinner) {
            $spinner.show();
        }
        //Hide the container
        $container.css({
            visibility: 'hidden'
        });
        pwebCore.ajax("action=perfect-decorations-for-occasions_eventDetails", {'id': id}, function (data) {
            //Success
            $container.html(data);
            $container.css({
                visibility:'visible'
            });
            //Bind events to callbacks
            if (typeof callback_next === "function") {
                $('.next', $container).click(function (e) {
                    callback_next(e, id);
                });
            }
            if (typeof callback_prev === "function") {
                $('.prev', $container).click(function (e) {
                    callback_prev(e, id);
                });
            }
            //Block add to cart buttons for each item in cart
            for (var item in dfo.cart.items) {
                $('[data-dfo-feed-id="' + item + '"] [data-dfo-buy-feed]').attr('disabled', true);
            }
            //Resize the slider...
            $('.dfo-slider').css({
                'min-height': Math.round($('.dfo-demo-frame').width() * 0.5625)
            });
            //Resize the iframe (it's still invisible)
            function resizeFrame() {
                $('.dfo-demo-frame').css({
                    height: Math.round($('.dfo-demo-frame').width() * 0.5625)
                });
            }
            resizeFrame();
            //Add listener
            $(window).bind('resize', function () {
                resizeFrame();
            });
            if ($spinner) {
                $spinner.hide();
            }
            //Show iFrame spinner
            var $iframe_spinner = dfo.spinner('spinner-cover');
            $iframe_spinner.appendTo($('.dfo-slider'));
            $('.dfo-demo-frame').on('load', function () {
                $iframe_spinner.hide();
                $(this).velocity({opacity:[1.0,0]});
            });

        }, function (data) {
            //Fail
            pwebCore.message('Failed to fetch event details. Try again later.', 'error');
            if ($spinner) {
                $spinner.hide();
            }
        }, false, true, true);
    };
    dfo.EventOpenTimeline = function (id) {
        var modal = UIkit.modal(".dfo-event-details");
        modal.show();
        var $spinner = $('.dfo-spinner', $(modal.element));
        var $content = $('.dfo-event', $(modal.element));
        var nextID = 0, prevID = 0;
        //Get the next/prev IDs
        var allEvents = $('#calendar').fullCalendar('clientEvents');
        for (var i = 0; i < allEvents.length; i++) {
            if (allEvents[i].id === id) {
                //We found this event in the list
                if (i < (allEvents.length - 1))
                    nextID = allEvents[i + 1].id;
                if (i > 0)
                    prevID = allEvents[i - 1].id;
                break;
            }
        }
        dfo.EventRender($content, $spinner, id, function (e) {
            //Calback next
            if (nextID !== 0)
                dfo.EventOpenTimeline(nextID);
        }, function (e) {
            //Calback prev
            if (prevID !== 0)
                dfo.EventOpenTimeline(prevID);
        });
    };
    if ($('.dfo-empty-db').length) {
        $('.dfo-event-details').css({'min-height': $('.dfo-event-details').width() * 0.5625 + 'px'});
        $('.dfo-demo-frame').on('load', function () {
            $(this).fadeIn();
            $(this).css({height: $('.dfo-demo-frame').width() * 0.5625 + 'px'});
            $('.dfo-spinner-text').hide();
        })
    }
    //Feeds view
    if ($('[data-dfo-autoload-event-id]').length) {
        //Assume this is the container...
        var $container = $('[data-dfo-autoload-event-id]'),
            $spinner = $('.dfo-spinner-text'),
            id = $container.data('dfo-autoload-event-id');
        //TODO: Check that the id mangling acutally works...
        function next(e, id) {
            //Eh, get the next id
            var nextID = dfo.id_list[id].next;
            dfo.EventRender($container, $spinner, nextID, next, prev, true);
        }

        function prev(e, id) {
            var prevID = dfo.id_list[id].prev;
            dfo.EventRender($container, $spinner, prevID, next, prev, true);
        }

        dfo.EventRender($container, $spinner, id, next, prev, true);
    }
    //Menu
    $('#side-nav').dlmenu({
        animationClasses: {classin: 'dl-animate-in-1', classout: 'dl-animate-out-1'}
    });
    //Cart UI
    $('.cart-toggler').click(function (e) {
        $('.cart-items').slideToggle();
    });
    //Cart - delete
    $('.cart-items').on('click', 'li>a', function (e) {
        e.preventDefault(); //so we don't get the pound sign in browser address bar
        var id = $(this).parent().data('id'); //refers to the <li> element
        dfo.cart.removeItem(id);
    });
    //Cart - add
    $('#wpbody').on('click', '[data-dfo-buy-feed]', function (e) {
        e.stopImmediatePropagation();
        var $p = $(this).parent();
        dfo.cart.addItem($p.data('dfo-feed-id'), $p.data('dfo-feed-name'), $p.data('dfo-feed-price'));
        //Disabling the button is handled in simplecart
    });
    //Cart - add (visual cue)
    $('#wpbody').on('mouseenter', '[data-dfo-buy-feed]', function (e) {
        $(this).data('dfo-on-mouseleave-text', $(this).text());
        $(this).text('Add to cart');
    });
    $('#wpbody').on('mouseleave', '[data-dfo-buy-feed]', function (e) {
        $(this).text($(this).data('dfo-on-mouseleave-text'));
    });
    //Cart - checkout
    $('#checkout').click(function (e) {
        //So that the service will understand us... It's a hack-job for now, time's pressin' :C
        var ids = Object.keys(dfo.cart.items);
        var idQS = '';
        for (var i = 0; i < ids.length; i++) {
            idQS = idQS + '&feed[]=' + ids[i];
        }
        console.log(ids.feed);
        console.log(idQS);
        window.open(dfo.cart_url + '&' + idQS, '_blank');
        //Don't clear the cart now...
        //dfo.cart.clearItems();
    });
    //Subscription
    $('.fake-btn').click(function () {
        $(this).attr('disabled', true);

        $('.dfo-info').hide().text('Saved').fadeIn();
    });
    $('#start_trial').click(function (e) {
        e.preventDefault(); //To avoid page scrolling up
        var referer = $('#referer input').val();
        var validate = /^[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?$/i;
        if (!!referer && !validate.test(referer)) {
            $('#referer input').addClass('uk-form-danger');
            $('#referer .uk-form-help-inline').text('Please enter a valid e-mail address or leave blank').appendTo($('#referer'));
            return;
        }
        //Visual cue
        $('.dfo-content').velocity('fadeOut', {duration: 750});
        $('.dfo-activating').velocity('fadeIn', {duration: 750});
        pwebCore.ajax("action=perfect-decorations-for-occasions_startTrial", {'referer': referer}, function (data) {
            //Success
            $('.dfo-cover').remove();
            pwebCore.message('Your trial was activated. Have fun!', 'success');
        }, function (data) {
            //Fail
            pwebCore.message('Failed to enable the trial. Please contact support', 'error');

        }, false, true, true)
    });
    if ($('.dfo-cover').length) {
        $('.dfo-cover').css({
            bottom: '-' + $('#wpfooter').outerHeight() + 'px'
        });
    }
    $('#referer_toggle').toggle(function (e) {
        e.preventDefault();
        $('#referer').velocity('slideDown', {duration: 750}).velocity('fadeIn', {duration: 1000, queue: false});
    }, function (e) {
        e.preventDefault();
        $('#referer').velocity('slideUp', {duration: 750}).velocity('fadeOut', {duration: 1000, queue: false});
    });
    //Timeline handler
    $('#calendar').fullCalendar({
        eventSources: [{
            url: 'admin-ajax.php?action=perfect-decorations-for-occasions_eventsTimeline',
            type: 'POST',
            data: {
                filter: dfo.calendarFilter
            }
        }],
        buttonText: {
            today: 'Go to current month'
        },
        aspectRatio: 2,
        height: 'auto',
        eventClick: function (calEvent, jsEvent, view) {
            //EVENT DETAILS
            dfo.EventOpenTimeline(calEvent.id);
        }
    });
    //Feed filtering
    if ($('[data-dfo-feeds-filter]').length) {
        var $select = $('[data-dfo-feeds-filter]');
        $select.detach().prependTo('.fc-toolbar .fc-right');
        $select.show();
        $select.chosen({width: "400px"});
        $select.on('change', function (e, data) {
            if (!!data.selected) {
                dfo.calendarFilter.push(data.selected)
            }
            if (!!data.deselected) {
                var index = dfo.calendarFilter.indexOf(data.deselected);
                dfo.calendarFilter.splice(index, 1);
            }
            //Refetch the events:
            $('#calendar').fullCalendar('refetchEvents');
        });
    }
    //Force sync
    $('.dfo-force-sync').click(function (e) {
        var $spinner = dfo.getSpinner('Downloading data from the service, please wait...');
        //Visual cues for the user
        $(this).attr('disabled', true);
        $(this).after($spinner);

        var $that = $(this);
        //Request data download
        pwebCore.ajax("action=perfect-decorations-for-occasions_forceServiceSync", {}, function (data) {
            //Success
            pwebCore.message('Data was downloaded from the service', 'success');
            $that.attr('disabled', false);
            $spinner.remove();
        }, function (data) {
            //Fail
            pwebCore.message('There was a problem while fetching data from the service. Please try again later.', 'danger');
            $that.attr('disabled', false);
            $spinner.remove();
        }, false, true, true);
    });
    //Event details - slider hover
    $('.dfo-event-details').on('mouseenter', '.dfo-slider', function () {
        $('.dfo-slider-controls-cover', $(this)).stop().fadeIn();
    });
    $('.dfo-event-details').on('mouseleave', '.dfo-slider', function () {
        $('.dfo-slider-controls-cover', $(this)).stop().fadeOut();
    });
    //Event details - voting
    $('.dfo-event-details').on('click', '.vote', function (e) {
        e.preventDefault();
        var $this = $(this);
        var type = null;
        var id = $(this).parent().parent().data('dfo-decoration-id'); //haaaaacky
        if ($this.hasClass('upvote')) {
            type = 1;
        }
        else {
            type = 2;
        }
        $('.vote').removeClass('selected');
        $this.addClass('selected');
        //Call the API to vote
        dfo.serviceAPICall("vote", {'id': id, 'vote': type}, function (r) {
            //Success
            if (typeof r.data.upvotes !== "undefined") {
                $('.upvote span').text(r.data.upvotes);
                $('.downvote span').text(r.data.downvotes);
                //Update local info
                pwebCore.ajax('action=perfect-decorations-for-occasions_updateVotes', {
                    'id': id,
                    'up': r.data.upvotes,
                    'down': r.data.downvotes,
                    'vote': type
                }, false, false, false, true, true);
            }
        }, function (r) {
            //Fail - ignore for now
        });
    });
    //Overrides handler - toggle
    $('.dfo-event-details').on('click', '[data-dfo-publish-toggler]', function () {
        var $btn = $(this);
        var event_id = $btn.data('dfo-event-id');
        //Visual cue while we do the AJAX request
        $btn.attr('disabled', true);
        pwebCore.ajax("action=perfect-decorations-for-occasions_eventOverride", {
            id: event_id,
            toggle: 'true'
        }, function (data) {
            //Success
            dfo.message('Event status changed', 'success', '.dfo-event-messages');
            $btn.attr('disabled', false);
            //Change the button's text:
            var tmp = $btn.text();
            $btn.text($btn.data('dfo-ontoggle'));
            $btn.data('dfo-ontoggle', tmp);
            //Rerender the calendar
            $('#calendar').fullCalendar('refetchEvents');
        }, function (data) {
            //Fail
            dfo.message('Couldn\'t toggle the event. Please try again later.', 'danger', '.dfo-event-messages');
            $btn.attr('disabled', false);
        }, false, true, true);
    });
    //Overrides handler - reschedule
    $('.dfo-event-details').on('click', '[data-dfo-reschedule]', function () {
        var $btn = $(this);
        var event_id = $btn.data('dfo-event-id');
        var date_start = $('#dateStart').val();
        var date_end = $('#dateEnd').val();
        //Visual cue while we do the AJAX request
        $btn.attr('disabled', true);
        pwebCore.ajax("action=perfect-decorations-for-occasions_eventOverride", {
            id: event_id,
            'date_start': date_start,
            'date_end': date_end
        }, function (data) {
            //Success
            dfo.message('Event rescheduled', 'success', '.dfo-event-messages');
            $btn.attr('disabled', false);
            //Rerender the calendar
            $('#calendar').fullCalendar('refetchEvents');
        }, function (data) {
            //Fail
            dfo.message((data || 'Unknown error'), 'danger', '.dfo-event-messages');
            $btn.attr('disabled', false);
        }, false, true, true);
    });
    //Cache clear
    $('.dfo-event-details').on('click', '.dfo-clear-cache', function () {
        var $frame = $('.dfo-demo-frame'),
        src = $frame.attr('src') + '&force=1';
        $frame.velocity({opacity:[0, 1.0]}, {complete:function(){
            $('.dfo-spinner-m').show();
        }});

        $('.dfo-demo-frame').attr('src', src);
    });
    //Overrides handler - reset
    $('.dfo-event-details').on('click', '[data-dfo-reset]', function () {
        var $btn = $(this);
        var event_id = $btn.data('dfo-event-id');
        //Visual cue while we do the AJAX request
        $btn.attr('disabled', true);
        pwebCore.ajax("action=perfect-decorations-for-occasions_eventReset", {
            id: event_id
        }, function (r) {
            //Success
            dfo.message('Event has been reset to its defaults', 'success', '.dfo-event-messages');
            $btn.attr('disabled', false);
            //Rerender the calendar
            $('#calendar').fullCalendar('refetchEvents');
            //Replace the dates in input boxes
            $('#dateStart').val(r.date_start);
            $('#dateEnd').val(r.date_end);
        }, function (data) {
            //Fail
            $btn.attr('disabled', false);
        }, false, true, true);
    });
    if($('.advanced_text').length > 0){
        $('.advanced_text').detach().appendTo('fieldset.advanced');
    }
    $('.dfo-toggle-advanced').click(function (e) {
        e.preventDefault();
        $('fieldset.advanced').slideToggle();
    });
});