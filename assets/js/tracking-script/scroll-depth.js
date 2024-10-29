(function ($) {
    // Default options
    const defaults = {
        percentage: true,
        elements: []  // Initialize elements array for tracking
    };

    let cache = [];
    let scrollEventBound = false;

    // Function to send scroll depth event
    function sendScrollDepthEvent(pageLink, percentage, timing) {
        gtag('event', 'scroll_depth', {
            'azo_category': 'Azo Scroll Depth',
            'azo_percentage': percentage,
            'non_interaction': true
        });

        if (timing !== undefined) {
            gtag('event', 'timing_complete', {
                'event_category': 'Azo Scroll Depth',
                'event_label': pageLink,
                'value': timing,
                'non_interaction': true
            });
        }
    }

    // Function to calculate scroll marks
    function calculateScrollMarks(docHeight) {
        return {
            '25': Math.floor(docHeight * 0.25),
            '50': Math.floor(docHeight * 0.50),
            '75': Math.floor(docHeight * 0.75),
            '100': docHeight - 5
        };
    }

    // Function to check scroll marks
    function checkScrollMarks(marks, scrollDistance, timing, cache) {
        $.each(marks, function (key, val) {
            if ($.inArray(key, cache) === -1 && scrollDistance >= val) {
                sendScrollDepthEvent(scroll_data.permalink, key, timing);
                cache.push(key);
            }
        });
    }

    // Throttle function to limit the rate at which a function can fire
    function throttle(func, wait) {
        let timeout = null;
        let previous = 0;
        return function () {
            let now = new Date();
            let remaining = wait - (now - previous);
            let context = this;
            let args = arguments;

            if (remaining <= 0) {
                clearTimeout(timeout);
                timeout = null;
                previous = now;
                func.apply(context, args);
            } else if (!timeout) {
                timeout = setTimeout(() => {
                    previous = new Date();
                    timeout = null;
                    func.apply(context, args);
                }, remaining);
            }
        };
    }

    // Function to bind the scroll event
    function bindScrollDepthEvent(startTime, cache, options) {
        const $window = $(window);

        $window.on('scroll.scrollDepth', throttle(function () {
            let docHeight = $(document).height();
            let winHeight = window.innerHeight || $window.height();
            let scrollDistance = $window.scrollTop() + winHeight;
            let marks = calculateScrollMarks(docHeight);
            let timing = +new Date() - startTime;

            checkScrollMarks(marks, scrollDistance, timing, cache);
        }, 500));
    }

    // Function to initialize scroll depth tracking
    function initScrollDepth(options) {
        let startTime = +new Date();
        cache = [];
        bindScrollDepthEvent(startTime, cache, options);
    }

    // Public API
    const scrollDepth = {
        init: function (options) {
            options = $.extend({}, defaults, options);
            initScrollDepth(options);
        },
        reset: function () {
            cache = [];
            $(window).off('scroll.scrollDepth');
            initScrollDepth(defaults);  // Ensure options are passed
        },
        addElements: function (elems) {
            if (!Array.isArray(elems)) return;
            $.merge(defaults.elements, elems);
            if (!scrollEventBound) initScrollDepth(defaults);  // Ensure elements are tracked
        },
        removeElements: function (elems) {
            if (!Array.isArray(elems)) return;
            $.each(elems, function (index, elem) {
                let inElementsArray = $.inArray(elem, defaults.elements);
                let inCacheArray = $.inArray(elem, cache);
                if (inElementsArray != -1) defaults.elements.splice(inElementsArray, 1);
                if (inCacheArray != -1) cache.splice(inCacheArray, 1);
            });
        }
    };

    // Initializing scroll depth tracking on document ready
    $(document).ready(function () {
        scrollDepth.init();
    });

    // Expose the API to the global scope if needed
    window.scrollDepth = scrollDepth;

})(jQuery);