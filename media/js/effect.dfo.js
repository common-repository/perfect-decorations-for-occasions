var DFO = DFO || {};
(function ($) {
    "use strict";
    //Effects lib
    DFO.Effect = function ($container, options) {
        var effect = this;
        //Internal private function
        function initImage() {
            effect.img = document.createElement('img');
            effect.img.onload = function () {
                //It's important to wait for the image to be loaded before trying to determine its size
                effect.imgHeight = this.naturalHeight;
                effect.imgWidth = this.naturalWidth;
                if (effect.options.autoScale) {
                    reScale();
                    $(window).bind('resize', function () {
                        refreshScale();
                        reScale();
                    });
                }
            };
            effect.img.className = 'perfect-decoration';
            if (effect.options.sourceIsBase64) {
                //I was somewhat surprised that this worked. Huh.
                effect.img.src = 'data:image/' + effect.options.sourceBase64MIME + ';base64,' + effect.options.source;
            }
            else {
                effect.img.src = effect.options.source;
            }
            effect.$img = $(effect.img); //Cause someone's lazy
        }

        function reScale() {
            effect.$img.css({
                'height': (effect.imgHeight * effect.scale) + 'px',
                'width': (effect.imgWidth * effect.scale) + 'px'
            });
        }

        function refreshScale() {
            effect.scale = (!!effect.options.autoScale ? Math.min(effect.$sizer.width() / 1600, 1.0) : 1.0);
        }

        //Init internal stuff
        this.$container = $container;
        var tmpOptions = {};
        if (typeof options === "string") {
            tmpOptions = JSON.parse(options);
        }
        else if (typeof options === "object") {
            tmpOptions = options;
        }
        this.uid = 'dfo-' + Math.floor(1 + Math.random() * 0x10000).toString(16); //To identify different effects across the page
        //Default options
        var defaults = {
            'source': '', //Either an URI or a base64-encoded string
            'sourceIsBase64': false,
            'sourceBase64MIME': 'png', //MIME-type for the base64-encoded string
            'autoScale': true, //Should decorations resize with the window and scale with regards to "designed" size?
            'type': '',
            'position': 'right-bottom',
            'flake_position': 'fixed',
            'sizer': '', //Element to get the bounding box dimensions from. Defaults to the container
            'ignoreTouch': false //Whether to show cursor effects on touch-enabled devices anyway
        };
        this.options = $.extend({}, defaults, tmpOptions);
        //For scaling
        if (!!this.options.sizer) {
            this.$sizer = $(this.options.sizer);
        }
        else {
            this.$sizer = this.$container;
        }
        refreshScale();
        //Type aliases, so our lives are easier
        switch (this.options.type) {
            case 'cursor':
            case 'mouse-follower':
            case '1':
            case 1:
                this.options.type = 'mouse-follower';
                break;
            case 'glued':
            case 'repeated':
            case 'fixed-image':
            case '2':
            case 2:
                this.options.type = 'fixed-image';
                break;
            case 'falling-images':
            case 'falling':
            case 'flakes':
            case '3':
            case 3:
                this.options.type = 'falling-images';
                break;
            case 'custom-js':
            case '4':
            case 4:
                this.options.type = 'custom-js';
                break;
            case 'custom-css':
            case '5':
            case 5:
                this.options.type = 'custom-css';
                break;
            default:
                this.options.type = '';
                break;
        }
        if (!this.options.type) {
            //Uh-oh, someone fucked up.
            return;
        }
        //Don't init image for Custom JS/CSS, because they won't use it anyway
        if (this.options.type !== 'custom-js' && this.options.type !== 'custom-css') {
            initImage();
        }
    };
    //---------------------------------------------------
    // Mouse-follower/Cursor-companion
    //---------------------------------------------------
    DFO.Effect.prototype.renderMouseFollower = function () {
        var effect = this;
        if (Modernizr.touch && !effect.options.ignoreTouch) {
            return; //Guard case
        }
        $(document).on('mousemove.dfo.' + effect.uid, function (e) {
                var x, y = 0,
                    maxX = $(document).width() - effect.imgWidth * effect.scale,
                    maxY = $(document).height() - effect.imgHeight * effect.scale;

                switch (effect.options.position) {
                    case 'left-top':
                        x = e.pageX - (effect.imgWidth * effect.scale) - 8;
                        y = e.pageY - (effect.imgHeight * effect.scale) - 8;
                        break;
                    case 'left-bottom':
                        x = e.pageX - (effect.imgWidth * effect.scale) - 8;
                        y = e.pageY + 8;
                        break;
                    case 'right-top':
                        x = e.pageX + 8;
                        y = e.pageY - (effect.imgHeight * effect.scale) - 8;
                        break;
                    case 'right-bottom':
                    default:
                        x = e.pageX + 8;
                        y = e.pageY + 8;
                        break;
                }
                effect.$img.offset({
                    left: Math.min(x, maxX),
                    top: Math.min(y, maxY)
                });
            }
        );
        effect.$img.addClass('perfect-decorations-cursor');
        effect.$container.append(effect.$img);
    };
    DFO.Effect.prototype.hideMouseFollower = function () {
        $(document).off('mousemove.dfo.' + this.uid);
        this.$img.remove();
    };
    //---------------------------------------------------
    // "Glued"/fixed position images
    //---------------------------------------------------
    DFO.Effect.prototype.renderFixedImage = function () {
        var effect = this;
        //TODO: Since repeated images get special treatment, consider splitting them into distinct types
        if (!!effect.options.repeated) {
            //Yes, we need to wait
            effect.$div = $('<div />', {class: 'perfect-decorations-glued perfect-decoration repeat'});
            effect.$div.css({
                'background-image': 'url(' + effect.img.src + ')',
                'height': Math.round(effect.imgHeight * effect.scale) + 'px'
            });
            effect.$div.addClass(effect.options.position);
            effect.$container.append(effect.$div);
            //Special resize function
            $(window).on('resize', function () {
                effect.$div.css({'height': Math.round(effect.imgHeight * effect.scale) + 'px'});
            });
            //Yay for duplicate code
            effect.$img.on('load', function () {
                effect.$div.css({'height': Math.round(effect.imgHeight * effect.scale) + 'px'});
            });
        }
        else {
            if (effect.options.position === "left-middle" || effect.options.position === "right-middle") {
                //Center the image vertically, because CSS is hard and a meanie
                effect.$img.css({
                    'margin-top': -(effect.imgHeight * effect.scale) / 2
                });
            }
            effect.$img.addClass(effect.options.position);
            effect.$img.addClass('perfect-decorations-glued');
            effect.$container.append(effect.$img);
        }
    };
    DFO.Effect.prototype.hideFixedImage = function () {
        if (this.options.repeated) {
            this.$div.remove();
        }
        else {
            this.$img.remove();
        }
    };
    //---------------------------------------------------
    // Falling images (e.g. snow, leaves)
    //---------------------------------------------------
    DFO.Effect.prototype.renderFallingImages = function(){

        var animationSupport    = false,
            animationString     = 'animation',
            vendorPrefix        = '',
            prefix              = '',
            domPrefixes         = ['Webkit', 'Moz', 'O', 'ms', 'Khtml'],
            style               = document.body.style,
            effect              = this;

        effect.options.accelerationRandomizer = parseInt(effect.options.accelerationRandomizer) || 10;
        effect.options.wander = parseInt(effect.options.wander) || 100;
        effect.options.spawnInterval = parseInt(effect.options.spawnInterval) || 750;

        for( var i = 0; i < domPrefixes.length; i++ ) {
            if( style[ domPrefixes[i] + 'AnimationName' ] !== undefined ) {
                prefix = domPrefixes[ i ];
                animationString = prefix + 'Animation';
                vendorPrefix = '-' + prefix.toLowerCase() + '-';
                animationSupport = true;
                break;
            }
        }

        //no CSS3
        if( animationSupport === false )
            return;

        function init()
        {

            var holder = document.createElement( 'div' );
            holder.setAttribute('class', 'perfect-decoration-flake-holder');
            document.body.appendChild(holder);

            var count =  7000 / effect.options.spawnInterval;

            for (var i = 0; i < count; i++)
            {
                holder.appendChild(createALeaf());
            }
        }

        function randomInteger(low, high)
        {
            return low + Math.floor(Math.random() * (high - low));
        }

        function randomFloat(low, high)
        {
            return low + Math.random() * (high - low);
        }

        function pixelValue(value)
        {
            return value + 'px';
        }

        function precentValue(value) {
            return value + '%';
        }

        function durationValue(value)
        {
            return value + 's';
        }

        /* MAGIC! */
        function createALeaf()
        {
            var PwebFallingDiv = document.createElement('div');
            var image = document.createElement('img');
            image.src = effect.$img.attr('src');

            effect.scale = (!!effect.options.autoScale ? Math.min(effect.$sizer.width() / 1600, 1.0) : 1.0);

            $(image).load(function(){
                if( effect.scale >= 1 )
                    image.style.width = image.naturalWidth + 'px';
                else
                    image.style.width = image.naturalWidth * effect.scale * 1.4 + 'px';
            });

            $(window).resize(function(){
                effect.scale = (!!effect.options.autoScale ? Math.min(effect.$sizer.width() / 1600, 1.0) : 1.0);

                if( effect.scale >= 1 )
                    image.style.width = image.naturalWidth + 'px';
                else
                    image.style.width = image.naturalWidth * effect.scale * 1.4 + 'px';
            });



            PwebFallingDiv.setAttribute('class', 'perfect-decorations-flake-2');
            PwebFallingDiv.style.top = "-10%";
            PwebFallingDiv.style.left = precentValue(randomInteger(0, 100));
            var spinAnimationName = (Math.random() < 0.5) ? 'clockwiseSpin' : 'counterclockwiseSpinAndFlip';
            PwebFallingDiv.style.webkitAnimationName = 'fade, drop';
            PwebFallingDiv.style.mozAnimationName = 'fade, drop';
            PwebFallingDiv.style.oAnimationName = 'fade, drop';
            PwebFallingDiv.style.msAnimationName = 'fade, drop';
            PwebFallingDiv.style.animationName = 'fade, drop';
            image.style.webkitAnimationName = spinAnimationName;
            image.style.mozAnimationName = spinAnimationName;
            image.style.oAnimationName = spinAnimationName;
            image.style.msAnimationName = spinAnimationName;
            image.style.animationName = spinAnimationName;
            var fadeAndDropDuration = durationValue(randomFloat(5, 11));
            var spinDuration = durationValue(randomFloat(4, 8));
            PwebFallingDiv.style.webkitAnimationDuration = fadeAndDropDuration + ', ' + fadeAndDropDuration;
            PwebFallingDiv.style.mozAnimationDuration = fadeAndDropDuration + ', ' + fadeAndDropDuration;
            PwebFallingDiv.style.oAnimationDuration = fadeAndDropDuration + ', ' + fadeAndDropDuration;
            PwebFallingDiv.style.msAnimationDuration = fadeAndDropDuration + ', ' + fadeAndDropDuration;
            PwebFallingDiv.style.animationDuration = fadeAndDropDuration + ', ' + fadeAndDropDuration;
            var PwebFallingDelay = durationValue(randomFloat(0, 5));
            PwebFallingDiv.style.webkitAnimationDelay = PwebFallingDelay + ', ' + PwebFallingDelay;
            PwebFallingDiv.style.mozAnimationDelay = PwebFallingDelay + ', ' + PwebFallingDelay;
            PwebFallingDiv.style.oAnimationDelay = PwebFallingDelay + ', ' + PwebFallingDelay;
            PwebFallingDiv.style.msAnimationDelay = PwebFallingDelay + ', ' + PwebFallingDelay;
            PwebFallingDiv.style.animationDelay = PwebFallingDelay + ', ' + PwebFallingDelay;
            image.style.webkitAnimationDuration = spinDuration;
            image.style.mozAnimationDuration = spinDuration;
            image.style.oAnimationDuration = spinDuration;
            image.style.msAnimationDuration = spinDuration;
            image.style.animationDuration = spinDuration;
            PwebFallingDiv.appendChild(image);
            return PwebFallingDiv;
        }

        jQuery(document).ready(function(){

            init();
        });
    };

    DFO.Effect.prototype.hideFallingImages = function () {
        $('.perfect-decoration-flake-holder').remove();
    };
    //---------------------------------------------------
    // Custom JS
    //---------------------------------------------------
    DFO.Effect.prototype.renderCustomJS = function () {
        //TODO: Make this bit not shit.
        //TODO: Error checking
        //TODO: Multiple CustomJS instances within a decoration
        $.ajax({
            url: this.options.source,
            dataType: 'script',
            success: function () {
                window.CustomEffectRun();
            },
            async: true
        });
    };
    DFO.Effect.prototype.hideCustomJS = function () {
        window.CustomEffectStop(); //yep... I know...
    };
    //---------------------------------------------------
    // Custom CSS
    //---------------------------------------------------
    DFO.Effect.prototype.renderCustomCSS = function () {
        var effect = this;
        $.ajax({
            url: effect.options.source,
            dataType: 'text',
            success: function (data) {
                $('<style type="text/css" data-id="' + effect.uid + '">\n' + data + '</style>').appendTo("head"); //nasty but works
            }
        });
    };
    DFO.Effect.prototype.hideCustomCSS = function () {
        $('[data-id="' + this.uid + '"]').remove();
    };
    //Comfort method if the effect type is not known at design-time. Shouldn't create *too* much overhead
    //Aliases are handled in the constructor
    DFO.Effect.prototype.start = function () {
        var effect = this;
        switch (effect.options.type) {
            case 'mouse-follower':
                effect.renderMouseFollower();
                break;
            case 'fixed-image':
                effect.renderFixedImage();
                break;
            case 'falling-images':
                effect.renderFallingImages();
                break;
            case 'custom-js':
                effect.renderCustomJS();
                break;
            case 'custom-css':
                effect.renderCustomCSS();
                break;
        }
    };

    DFO.Effect.prototype.stop = function () {
        var effect = this;
        switch (effect.options.type) {
            case 'mouse-follower':
                effect.hideMouseFollower();
                break;
            case 'fixed-image':
                effect.hideFixedImage();
                break;
            case 'falling-images':
                effect.hideFallingImages();
                break;
            case 'custom-js':
                effect.hideCustomJS();
                break;
            case 'custom-css':
                effect.hideCustomCSS();
                break;
        }
    }
})(jQuery);