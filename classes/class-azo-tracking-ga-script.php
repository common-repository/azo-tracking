<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists(' AZO_Tracking_Ga_Script')) {

    class AZO_Tracking_Ga_Script
    {
        /**
         * @var         AZOTRACKING_Ga_Script 
         * @since       1.0.0
         */
        private static $instance = null;

        protected $azo_tracking_settings_instance;

        /**
         * Constructor.
         */
        public function __construct()
        {
            $this->azo_tracking_settings_instance = $GLOBALS['AZO_TRACKING_SETTINGS'];
            $this->hooks();
        }

        private function hooks()
        {
            add_action('wp_enqueue_scripts', array($this, 'azo_enqueue_ga_script'));
            add_action('wp_enqueue_scripts', array($this, 'azo_tracking_error_script'));
            add_action('wp_enqueue_scripts', array($this, 'scroll_depth_script'));
            add_filter('script_loader_tag', array($this, 'add_async_attribute_to_ga_script'), 10, 2);
        }

        function azo_enqueue_ga_script()
        {
            if ('on' !== $this->azo_tracking_settings_instance->get_option('add_ga_script', 'azo-tracking-profile', 'on')) {
                return;
            }

            $ga_code = $this->azo_tracking_settings_instance->get_ga_code();
            if (!$ga_code) {
                AZO_Tracking_Debug_Logging::debug('Ga code is not set.', 'azo-tracking');
                return;
            }

            global $current_user;
            $roles = $current_user->roles;
            $exclude_roles = $this->azo_tracking_settings_instance->get_option('exclude_users_roles_tracking', 'azo-tracking-configuration', array());
            if (isset($roles[0]) and in_array($roles[0], $exclude_roles)) {
                return;
            }

            //register ga4 script
            $local_analytics_file = (new AZO_Tracking_Hosting_Locally)->get_local_file_url();
            $analytics_url = $local_analytics_file ? esc_url($local_analytics_file) : esc_url('https://www.googletagmanager.com/gtag/js?id=' . $ga_code);
            wp_enqueue_script('azo-google-analytics', $analytics_url, array(), AZOTRACKING_VERSION, false);

            // define configuration for ga4
            $allow_display_features = ('on' === $this->azo_tracking_settings_instance->get_option('demographic_tracking', 'azo-tracking-configuration')) ? 'true' : 'false';

            $linker_cross_domain_tracking = ('on' === $this->azo_tracking_settings_instance->get_option('linker_cross_domain_tracking', 'azo-tracking-configuration')) ? true : false;

            $linked_domains = $this->azo_tracking_settings_instance->get_all_linked_domain();
            if (!$linked_domains) $linker_cross_domain_tracking = false;

            $configuration = array(
                'allow_display_features' => $allow_display_features,
            );
            if ($linker_cross_domain_tracking) {
                $configuration['linker'] = array(
                    'domains' => $linked_domains,
                );
            }
            $debug_mode = apply_filters('azo_tracking_debug_mode', true);

            if ($debug_mode) {
                $configuration['debug_mode'] = true;
            }

            if ('on' === $this->azo_tracking_settings_instance->get_option('track_user_id', 'azo-tracking-configuration') && is_user_logged_in()) {
                $configuration['user_id'] = esc_html(get_current_user_id());
            }

            $configuration = apply_filters('azo_tracking_gtag_configuration', $configuration);

            //add inline ga4 script 
            $inline_script = "
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                const gaConfig = " . wp_json_encode($configuration) . ";
                const gaCode = '" . esc_html($ga_code) . "';

                gtag('config', gaCode, gaConfig);
                ";
            wp_add_inline_script('azo-google-analytics', $inline_script);
        }

        function add_async_attribute_to_ga_script($tag, $handle)
        {
            if ('azo-google-analytics' === $handle) {
                return str_replace(' src', ' async src', $tag);
            }
            return $tag;
        }

        /**
         * Enqueue script for error tracking.
         */
        public function azo_tracking_error_script()
        {
            $is_tracking_404 = $this->azo_tracking_settings_instance->get_option('404_page_track', 'azo-tracking-configuration');
            $is_tracking_js_error = $this->azo_tracking_settings_instance->get_option('js_error_track', 'azo-tracking-configuration');
            $is_tracking_ajax_error = $this->azo_tracking_settings_instance->get_option('ajax_error_track', 'azo-tracking-configuration');
            if ($is_tracking_404 !== 'on' && $is_tracking_ajax_error !== 'on' && $is_tracking_js_error !== 'on') {
                return;
            }

            wp_enqueue_script('azo_tracking_error', AZOTRACKING_BASE_URL . 'assets/js/tracking-script/error-tracking.js', array('jquery'), AZOTRACKING_VERSION, true);

            $error_tracking_options = array(
                'track_404_page'   => array(
                    'is_tracking'  => $is_tracking_404,
                    'is_404' => is_404(),
                    'current_url'   => esc_url_raw(home_url(add_query_arg(null, null))),
                ),
                'track_js_error'   => $is_tracking_js_error,
                'track_ajax_error' => $is_tracking_ajax_error,
            );
            wp_localize_script('azo_tracking_error', 'error_tracking_options', $error_tracking_options);
        }

        /**
         * Add scripts for scroll depth.
         */
        public function scroll_depth_script()
        {
            global $post;

            if (!is_object($post)) {
                return;
            }
            if ('off' == $this->azo_tracking_settings_instance->get_option('scroll_depth', 'azo-tracking-configuration')) {
                return;
            }

            if ('on' == $this->azo_tracking_settings_instance->get_option('scroll_depth', 'azo-tracking-configuration')) {
                $permalink     = get_the_permalink($post->ID);
                $permalink     = str_replace(array('http://', 'https://'), '', $permalink);
                $localize_data = array(
                    'permalink'     => $permalink,
                );

                wp_enqueue_script('azo-scroll-depth', AZOTRACKING_BASE_URL . 'assets/js/tracking-script/scroll-depth.js', array('jquery'), AZOTRACKING_VERSION, true);
                wp_localize_script('azo-scroll-depth', 'scroll_data', $localize_data);
            }
        }


        public static function get_instance()
        {
            if (!self::$instance) {
                self::$instance = new AZO_Tracking_Ga_Script();
            }

            return self::$instance;
        }
    }
}

/**
 * Create instance of azo_tracking_ga_script class.
 */
function azo_tracking_ga_script_instance()
{
    $GLOBALS['AZO_TRACKING_GA_SCRIPT'] = AZO_Tracking_Ga_Script::get_instance();
}
add_action('plugins_loaded', 'azo_tracking_ga_script_instance', 50);
