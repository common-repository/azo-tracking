jQuery(document).ready(function ($) {
    // Track 404 page errors.
    if (
        "on" === error_tracking_options.track_404_page.is_tracking &&
        error_tracking_options.track_404_page.is_404 &&
        typeof gtag !== "undefined"
    ) {
        gtag("event", "404_error", {
            azo_category: "404 Error",
            azo_label: error_tracking_options.track_404_page.current_url,
        });
    }

    // Track JavaScript errors.
    if (
        "on" === error_tracking_options.track_js_error &&
        typeof gtag !== "undefined"
    ) {
        function trackJavaScriptError(e) {
            const errMsg = e.message;
            const errSrc = e.filename + ": " + e.lineno;
            gtag("event", "js_error", {
                azo_category: "JavaScript Error",
                azo_action: errMsg,
                azo_label: errSrc,
                non_interaction: true,
            });
        }

        window.addEventListener("error", trackJavaScriptError, false);
    }

    // Track AJAX errors.
    if (
        "on" === error_tracking_options.track_ajax_error &&
        typeof gtag !== "undefined"
    ) {
        jQuery(document).ajaxError(function (e, request, settings) {
            gtag("event", "ajax_error", {
                azo_category: "Ajax Error",
                azo_action: request.statusText,
                azo_label: settings.url,
                non_interaction: true,
            });
        });
    }
});