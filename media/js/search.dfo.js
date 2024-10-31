/**
 * @version 1.0.0
 * @package Perfect Decorations For Occasions
 * @copyright © 2015 Perfect Web sp. z o.o., All rights reserved. http://www.perfect-web.co
 * @license GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 * @author Mateusz Podraza
 */
(function ($) {
    dfo.search_data = [];

    $('#side-nav li a[data-dfo-id]').each(function (i, e) {
        var name = $(e).text();
        dfo.search_data.push({
            value: name,
            data: $(e).data('dfo-id')
        });
    });
    //Filter the array
    var u = {}, a = [];
    for(var i = 0, l = dfo.search_data.length; i < l; ++i){
        if(u.hasOwnProperty(dfo.search_data[i].value)) {
            continue;
        }
        a.push(dfo.search_data[i]);
        u[dfo.search_data[i].value] = 1;
    }
    dfo.search_data = a;

    $('.dfo-search-field').autocomplete({
        lookup: dfo.search_data,
        onSelect: function (suggestion) {
            //Hacky-clicky, but worky :p
            $('[data-dfo-id="'+suggestion.data+'"]')[0].click();
        }
    });
})
(jQuery);
