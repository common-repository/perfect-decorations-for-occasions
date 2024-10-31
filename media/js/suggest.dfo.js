/**
 * @version 1.0.0
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza
 */
(function ($) {
    //Extend the array class to add a custom method
    dfo.suggestionList = function ($container) {
        this.$container = $container;
    };
    dfo.suggestionList.prototype = [];
    dfo.suggestionList.prototype.renderAll = function () {
        if (this.length === 0) {
            //Empty list :c
            var text = document.createElement('p');
            text.className = 'dfo-no-results';
            text.innerHTML = 'No suggestions present - add your own!';
            this.$container.html(text);
            return;
        }
        //Create the wrapper
        var list = document.createElement('ul');
        list.className = 'suggest-list';
        $.each(this, function (i, val) {
            list.appendChild(val.getHTML());
        });
        this.$container.html(list);
    };
    //Declare a class for use later:
    dfo.suggestion = function (data) {
        if (!data) {
            console.log('Empty data, exiting...');
            return;
        }
        this.id = data.id || 0;
        this.name = data.name || '';
        this.description = data.description || '';
        this.votes = parseInt(data.votes);
    };
    dfo.suggestion.prototype.isValid = function () {
        var isDefined = (!!this.id && !!this.name && !!this.description),
            isEmpty = (this.id != 0 && this.name != '' && this.description != '');
        return (isDefined && isEmpty);
    };
    dfo.suggestion.prototype.getHTML = function () {
        //This will generate the markup with bound events :) Mixing jQuery and vanillaJS because... fuck it.
        if (!this.isValid()) {
            return; //Oops, shouldn't event be on the list!
        }
        var span = document.createElement('span'),
            votecount = document.createElement('span'),
            anchor = document.createElement('a'),
            item = document.createElement('li'),
            icon = document.createElement('i'),
            desc = document.createElement('p');
        span.innerText = this.name;
        item.appendChild(span);
        icon.className = 'uk-icon-thumbs-o-up';
        anchor.href = '#';
        anchor.className = 'dfo-suggestion-vote';
        //Don't want to deal with IE<11 dataset malarky, so... yeah:
        anchor.setAttribute('data-dfo-id', this.id);
        //Add the count
        votecount.innerText = this.votes;
        anchor.appendChild(votecount);
        //Add the icon
        anchor.appendChild(icon);
        //Scooooope!
        var that = this;
        //Bind the click
        anchor.addEventListener('click', function (e) {
            var $a = $(this);
            dfo.serviceAPICall('toggleSuggestionVote', {
                'id': that.id, 'uid': dfo.uid, 'token': dfo.token
            }, function (r) {
                //Success
                $('span', $a).text(r.data.votes);
            }, function (r) {
                //Fail...
                if (!!r && typeof r.message !== "undefined") {
                    dfo.message(r.message, 'danger');
                }
            });
        });
        item.appendChild(anchor);

        $(desc).html('&nbsp;'+this.description);
        desc.className = 'dfo-suggestion-desc folded';
        $(desc).toggle(function () {
            $(this).removeClass('folded');
        }, function () {
            $(this).addClass('folded');
        });
        item.appendChild(desc);

        return item;
    };
    //Bind modal form to buttons
    $('.add-feed, .add-event').click(function (e) {
        var m = UIkit.modal('#add');
        m.show();
        //Add type info to the box
        $('#add').data('dfo-type', $(this).data('dfo-type'));
    });
    //Start of by inserting spinners into both boxes
    var $suggestEvent = $('.suggestions-event'),
        $suggestFeed = $('.suggestions-feed');
    dfo.getSpinner('Please wait while we grab suggested occasions...').appendTo($suggestEvent);
    dfo.getSpinner('Please wait while we grab suggested channels...').appendTo($suggestFeed);
    //Then create arrays to hold the suggestions
    dfo.EventSuggestions = new dfo.suggestionList($suggestEvent);
    dfo.FeedSuggestions = new dfo.suggestionList($suggestFeed);
    //Then just load the suggestions:
    dfo.serviceAPICall('getSuggestions', {'type': 0, 'offset': 0}, function (r) {
        //TODO: Refactor to avoid code doubling
        //Success, process the lists
        if (!!r.data[1]) {
            //Event suggestions
            $.each(r.data[1], function (i, val) {
                var item = new dfo.suggestion(val);
                if (item.isValid()) {
                    dfo.EventSuggestions.push(item);
                }
            })
        }
        if (!!r.data[2]) {
            $.each(r.data[2], function (i, val) {
                var item = new dfo.suggestion(val);
                if (item.isValid()) {
                    dfo.FeedSuggestions.push(item);
                }
            })
        }
        $('.dfo-spinner').hide();
        //Render both
        dfo.EventSuggestions.renderAll();
        dfo.FeedSuggestions.renderAll();
    }, function () {
        //Fail - the serviceApiCall method will show an error message so let's just... fuck it.
        //Well, actually - delete the spinners
        $('.dfo-spinner').hide();
    });
    //Bind visuals on the form
    $('#name').on('keyup keypress blur change', function () {
        var charsLeft = 100 - $(this).val().length;
        if (charsLeft >= 0) {
            $('.chars-left-name').text(charsLeft + ' characters left');
            $('.btn-send').attr('disabled', false);
        }
        else {
            $(this).val($(this).val().substr(0, 100));
        }
    });
    $('#description').on('keyup keypress blur change', function () {
        var charsLeft = 1000 - $(this).val().length;
        if (charsLeft >= 0) {
            $('.chars-left-desc').text(charsLeft + ' characters left');
            $('.btn-send').attr('disabled', false);
        }
        else {
            $(this).val($(this).val().substr(0, 1000));
        }
    });
    //Cancel
    $('.uk-modal-close').click(function (e) {
        $('#name').val('');
        $('#description').val('');
        $('.chars-left-name').text('100 characters left');
        $('.chars-left-desc').text('1000 characters left');
    });
    //Send the form
    $('.btn-send').click(function (e) {
        var name = $('#name').val(),
            desc = $('#description').val(),
            type = $('#add').data('dfo-type');
        //Disable fields & this button
        $('#name, #description, .btn-send').attr('disabled', true);
        //Call the service:
        dfo.serviceAPICall('suggest', {
            'name': name,
            'desc': desc,
            'type': type,
            'uid': dfo.uid,
            'token': dfo.token
        }, function (r) {
            //Success
            console.log(r);
            //Re-enable fields & this button
            $('#name, #description, .btn-send').attr('disabled', false);
            $('.chars-left-name').text('100 characters left');
            $('.chars-left-desc').text('1000 characters left');
            //Also, clear them:
            $('#name, #description').val('');
            //And close the modal
            var m = UIkit.modal('#add');
            m.hide();
            //Inform the user :)
            pwebCore.message(r.message, 'success', true);
        }, function (r) {
            //Fail
            //Re-enable fields & this button
            $('#name, #description, .btn-send').attr('disabled', false);
            //Notify about the error
            if (typeof r.message !== "undefined") {
                dfo.message(r.message, 'danger', '.add-form');
            }
        });
    });
    //Disallow submits
    $('.add-form').submit(function (e) {
        e.preventDefault();
        return false;
    })
})
(jQuery);
