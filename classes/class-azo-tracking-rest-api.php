<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class AZO_Tracking_Rest_API
{
    public static $instance;

    public $start_date;

    public $end_date;
    public $date_differ;

    public $compare_start_date = null;
    public $compare_end_date = null;

    public $compare_days = null;

    public static function get_instance()
    {
        if (empty(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Class constructor to set up AJAX actions for Google Analytics functionalities.
     *
     * This constructor registers several AJAX actions with WordPress, allowing the class methods 
     * to be called via AJAX requests. Each action corresponds to a specific Google Analytics 
     * functionality, such as logging out, creating GA streams, and fetching various statistics.
     */
    public function __construct()
    {
        add_action('wp_ajax_google_logout', array($this, 'google_logout'));
        add_action('wp_ajax_create_ga_streams', array($this, 'create_ga_streams'));
        add_action('wp_ajax_general_stats', array($this, 'general_stats'));
        add_action('wp_ajax_visitor_devices_stats', array($this, 'visitor_devices_stats'));
        add_action('wp_ajax_new_vs_returning_visitors_stats', array($this, 'new_vs_returning_visitors_stats'));
        add_action('wp_ajax_browser_stats', array($this, 'browser_stats'));
        add_action('wp_ajax_operating_systems_stats', array($this, 'operating_systems_stats'));
        add_action('wp_ajax_referer_stats', array($this, 'referer_stats'));
        add_action('wp_ajax_top_pages_stats', array($this, 'top_pages_stats'));
        add_action('wp_ajax_what_is_happening_stats', array($this, 'what_is_happening_stats'));
        add_action('wp_ajax_geographic_stats', array($this, 'geographic_stats'));
        add_action('wp_ajax_daily_visitors_stats', array($this, 'daily_visitors_stats'));
    }

    /**
     * Handle Google logout process for users with appropriate permissions.
     *
     * This function verifies the user's permissions and nonce for security. If verification is 
     * successful, it deletes various options related to Google Analytics authentication and 
     * data from the WordPress database. After successfully logging out, it triggers an action 
     * hook for additional custom processes and returns a success message. If the user does not 
     * have the required permissions or nonce verification fails, it returns an error message.
     */
    public function google_logout()
    {
        if (current_user_can('manage_options')) {
            $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

            if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-authentication')) {
                wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
                return;
            }

            delete_option('azo_profiles_list_summary');
            delete_option('post_azotracking_token');
            delete_option('azo_google_token');
            delete_option('azo_ga4_streams');
            delete_option('azo_ga4_exceptions');

            //action hook for after logout success
            do_action('after_logout_success');


            wp_send_json_success(array('message' => 'You have successfully logged out.'));
        }

        wp_send_json_error(array('message' => 'Sorry, you do not have permission to access this page.'));
    }

    /**
     * Handle the creation of Google Analytics streams.
     *
     * This function checks for user permissions and nonce verification for security. It then validates 
     * the presence of a property ID and uses it to create Google Analytics streams. If the creation 
     * process is successful, it returns a success message; otherwise, it returns an error message.
     */
    public function create_ga_streams()
    {
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Sorry, you do not have permission to access this page.'));
            return;
        }

        $_wpnonce = isset($_POST['_wpnonce']) ? sanitize_text_field(wp_unslash($_POST['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-profile-options')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }

        $property_id = isset($_POST['property_id']) ? sanitize_text_field($_POST['property_id']) : '';


        // Check if property ID is set
        if ($property_id == '') {
            wp_send_json_error(array('message' => 'Sorry, your property id is missing.'));
            return;
        }

        $response_create_streams_by_id = $GLOBALS['AZO_TRACKING_GA']->create_ga_stream($property_id);

        if (!$response_create_streams_by_id) {
            wp_send_json_error(array('message' => 'Sorry, your property id not exist or not valid.'));
            return;
        }
        wp_send_json_success(array('message' => $response_create_streams_by_id));
    }


    /**
     * Handle the request to set start date, end date, and date difference.
     *
     * This function sets the start date, end date, and date difference based on the provided 
     * parameters. If no start date or end date is provided, it defaults to the last 30 days 
     * and the current date, respectively. It also updates the date difference option if provided.
     *
     * @param string $start_date The start date for the request.
     * @param string $end_date The end date for the request.
     * @param int $date_differ The difference in dates to be updated as an option.
     */
    public function handle_request($start_date, $end_date, $date_differ)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->date_differ = $date_differ;
        if (!$this->start_date) {
            $this->start_date = wp_date('Y-m-d', strtotime('-30 days', current_time('timestamp')));
        }
        if (!$this->end_date) {
            $this->end_date = wp_date('Y-m-d', current_time('timestamp'));
        }
        if ($this->date_differ) {
            update_option('azo_date_differ', $this->date_differ);
        }
    }


    /**
     * Set the comparison dates based on the date difference.
     *
     * This function calculates the date difference between the start and end dates, and sets 
     * the comparison start date, end date, and the number of comparison days. If the date 
     * difference is not valid, the function returns without setting the comparison dates.
     */
    public function set_compare_dates()
    {
        $date_diff = AZO_Utils::calculate_date_diff($this->start_date, $this->end_date);
        if (!$date_diff) {
            return;
        }

        $this->compare_start_date = $date_diff['start_date'];
        $this->compare_end_date = $date_diff['end_date'];
        $this->compare_days = $date_diff['diff_days'];
    }

    /**
     * Handle the AJAX request to retrieve general statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * general statistics for the specified date range. It fetches the session, visitor, 
     * pageview, and bounce rate statistics from Google Analytics, formats the data, and 
     * returns it in a JSON response. If comparison dates are available, it also compares 
     * the current statistics with the previous period.
     */
    public function general_stats()
    {
        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }


        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        // Container all the text information about the stats in boxes.
        $boxes_description = array(
            'sessions' => array(
                'title' => esc_html__('Sessions', 'azo-tracking'),
                'description' => esc_html__('A session is a time period in which a user is actively engaged with your website.', 'azo-tracking'),
                'bottom' => false,
                'number' => 0,
            ),
            'visitors' => array(
                'title' => esc_html__('Visitors', 'azo-tracking'),
                'description' => esc_html__('Users who complete a minimum of one session on your website.', 'azo-tracking'),
                'bottom' => false,
                'number' => 0,
            ),
            'pageviews' => array(
                'title' => esc_html__('Page Views', 'azo-tracking'),
                'description' => esc_html__('Page Views are the total number of Pageviews, these include repeated views.', 'azo-tracking'),
                'bottom' => false,
                'number' => 0,
            ),
            'bounce_rate' => array(
                'title' => esc_html__('Bounce Rate', 'azo-tracking'),
                'description' => esc_html__('Percentage of single page visits (i.e number of visits in which a visitor leaves your website from the landing page without browsing your website).', 'azo-tracking'),
                'append' => '<span class="azo_xl_f">%</span>',
                'bottom' => false,
                'number' => 0,
            )
        );



        $footer_description = false;

        // Container numbers (or string) for the different stats.
        $boxes_stats = array();

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        $general_stats_raw = $azo_tracking_ga->get_reports('show-default-overall-dashboard', array(
            'sessions',
            'totalUsers',
            'screenPageViews',
            'bounceRate',
            'userEngagementDuration',
        ), $this->get_dates());


        $general_stats = $general_stats_raw['aggregations'];
        // code added by jawad during debug added isset checks on number field.
        $boxes_stats = array(
            'sessions' => array(
                'raw' => isset($general_stats['sessions']) ? $general_stats['sessions'] : 0,
                'number' => isset($general_stats['sessions']) ? AZO_Utils::pretty_numbers($general_stats['sessions']) : 0,
            ),

            'visitors' => array(
                'raw' => isset($general_stats['totalUsers']) ? $general_stats['totalUsers'] : 0,
                'number' => isset($general_stats['totalUsers']) ? AZO_Utils::pretty_numbers($general_stats['totalUsers']) : 0,
            ),

            'pageviews' => array(
                'raw' => isset($general_stats['screenPageViews']) ? $general_stats['screenPageViews'] : 0,
                'number' => isset($general_stats['screenPageViews']) ? AZO_Utils::pretty_numbers($general_stats['screenPageViews']) : 0,
            ),

            'bounce_rate' => array(
                'raw' => isset($general_stats['bounceRate']) ? $general_stats['bounceRate'] : 0,
                'number' => isset($general_stats['bounceRate']) ? AZO_Utils::fraction_to_percentage($general_stats['bounceRate']) : 0,
            ),

        );

        if ($this->compare_start_date && $this->compare_end_date) {
            $compare_stats_raw = $azo_tracking_ga->get_reports(
                'show-default-overall-dashboard-compare',
                array(
                    'sessions',
                    'totalUsers',
                    'screenPageViews',
                    'bounceRate',
                ),
                array(
                    'start' => $this->compare_start_date,
                    'end' => $this->compare_end_date,
                )
            );
        }

        if (isset($compare_stats_raw['aggregations'])) {
            $compare_stats = array(
                'sessions' => isset($compare_stats_raw['aggregations']['sessions']) ? $compare_stats_raw['aggregations']['sessions'] : 0,
                'visitors' => isset($compare_stats_raw['aggregations']['totalUsers']) ? $compare_stats_raw['aggregations']['totalUsers'] : 0,
                'pageviews' => isset($compare_stats_raw['aggregations']['screenPageViews']) ? $compare_stats_raw['aggregations']['screenPageViews'] : 0,
                'bounce_rate' => isset($compare_stats_raw['aggregations']['bounceRate']) ? $compare_stats_raw['aggregations']['bounceRate'] : 0,
            );
        }


        if (isset($general_stats['userEngagementDuration'])) {
            $footer_description = apply_filters('azo_general_stats_footer', $general_stats['userEngagementDuration'], array($this->start_date, $this->end_date));
        }


        foreach ($boxes_description as $key => $box) {
            if (isset($boxes_stats[$key])) {
                $boxes_description[$key]['number'] = (string) $boxes_stats[$key]['number'];
                if (isset($compare_stats[$key])) {
                    $boxes_description[$key]['bottom'] = $this->compare_stat($boxes_stats[$key]['raw'], $compare_stats[$key], $key);
                }
            }
        }

        return wp_send_json_success(
            array(
                'success' => true,
                'boxes' => apply_filters('azo_general_stats_boxes', $boxes_description, array($this->start_date, $this->end_date)),
                'footer' => $footer_description,
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve browser statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * browser statistics for the specified date range. It fetches the session data grouped by 
     * browser and operating system from Google Analytics, formats the data, and returns it in 
     * a JSON response. It also includes a footer message and supports filtering and customization.
     */
    public function browser_stats()
    {

        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        // API limit.
        $browser_stats_limit = apply_filters('azo_api_limit_browser_stats', 5, 'dashboard');


        $browser_stats = array();


        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        //GA-4
        $browser_stats_raw = $azo_tracking_ga->get_reports('show-default-browser-dashboard', array(
            'sessions',
        ), $this->get_dates(), array(
            'browser',
            'operatingSystem',
        ), array(
            'type' => 'metric',
            'name' => 'sessions',
            'order' => 'desc',
        ), array(
            'logic' => 'AND',
            'filters' => array(
                array(
                    'type' => 'dimension',
                    'name' => 'operatingSystem',
                    'match_type' => 4,
                    'value' => '(not set)',
                    'not_expression' => true,
                ),
            ),
        ), $browser_stats_limit);


        if (isset($browser_stats_raw['rows']) && $browser_stats_raw['rows']) {
            foreach ($browser_stats_raw['rows'] as $row) {
                $browser_stats[] = array(
                    'browser' => '<span role="img" aria-label="' . $row['browser'] . '" class="' . azo_pretty_class($row['browser']) . ' azo_social_icons"></span>' . '<span class="' . azo_pretty_class($row['operatingSystem']) . ' azo_social_icons"></span>' . $row['browser'] . ' ' . $row['operatingSystem'],
                    'sessions' => $row['sessions'],
                );
            }
        }
        /**
         * For Pro legacy support.
         * CSV export button in generated by this action.
         */

        $after_top_browser_text = '';

        ob_start();
        do_action('azo_after_top_browser_text');
        $after_top_browser_text .= ob_get_clean();


        $browser = array(
            'headers' => array(
                'browser' => array(
                    'label' => esc_html__('Browsers statistics', 'azo-tracking') . $after_top_browser_text,
                    'th_class' => 'azo_txt_left azo_top_geographic_details_wrapper',
                    'td_class' => '',
                ),
                'sessions' => array(
                    'label' => esc_html__('Visits', 'azo-tracking'),
                    'th_class' => 'azo_value_row',
                    'td_class' => 'azo_txt_center',
                ),
            ),
            'stats' => $browser_stats,
        );


        wp_send_json_success(
            array(
                'success' => true,
                'browser' => $browser,
                'footer' => apply_filters('azo_system_stats_footer', __('Top browsers and operating systems.', 'azo-tracking'), array($this->start_date, $this->end_date)),
            )
        );

    }

    /**
     * Handle the AJAX request to retrieve operating system statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * operating system statistics for the specified date range. It fetches the session data 
     * grouped by operating system and version from Google Analytics, formats the data, and 
     * returns it in a JSON response. It also includes a footer message and supports filtering 
     * and customization.
     */
    public function operating_systems_stats()
    {

        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        // API limit.
        $os_stats_limit = apply_filters('azo_api_limit_os_stats', 5, 'dashboard');

        $os_stats = array();

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        //GA-4
        $os_stats_raw = $azo_tracking_ga->get_reports('show-default-os-dashboard', array(
            'sessions',
        ), $this->get_dates(), array(
            'operatingSystem',
            'operatingSystemVersion',
        ), array(
            'type' => 'metric',
            'name' => 'sessions',
            'order' => 'desc',
        ), array(
            'logic' => 'AND',
            'filters' => array(
                array(
                    'type' => 'dimension',
                    'name' => 'operatingSystemVersion',
                    'match_type' => 4,
                    'value' => '(not set)',
                    'not_expression' => true,
                ),
            ),
        ), $os_stats_limit);


        if (isset($os_stats_raw['rows']) && $os_stats_raw['rows']) {
            foreach ($os_stats_raw['rows'] as $row) {
                $os_stats[] = array(
                    'os' => '<span  role="img" aria-label="' . $row['operatingSystem'] . '" class="' . azo_pretty_class($row['operatingSystem']) . ' azo_social_icons"></span> ' . $row['operatingSystem'] . ' ' . $row['operatingSystemVersion'],
                    'sessions' => $row['sessions'],
                );
            }
        }

        /**
         * For Pro legacy support.
         * CSV export button in generated by this action.
         */

        $after_top_operating_system_text = '';


        ob_start();
        do_action('azo_after_top_operating_system_text');
        $after_top_operating_system_text .= ob_get_clean();

        $os = array(
            'headers' => array(
                'os' => array(
                    'label' => esc_html__('Operating system statistics', 'azo-tracking') . $after_top_operating_system_text,
                    'th_class' => 'azo_txt_left azo_top_geographic_details_wrapper azo_brd_lft',
                    'td_class' => 'azo_border_left',
                ),
                'sessions' => array(
                    'label' => esc_html__('Visits', 'azo-tracking'),
                    'th_class' => 'azo_value_row',
                    'td_class' => 'azo_txt_center',
                ),
            ),
            'stats' => $os_stats,
        );

        wp_send_json_success(
            array(
                'success' => true,
                'os' => $os,
                'footer' => apply_filters('azo_system_stats_footer', __('Top browsers and operating systems.', 'azo-tracking'), array($this->start_date, $this->end_date)),
            )
        );

    }

    /**
     * Handle the AJAX request to retrieve new vs returning visitors statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * statistics comparing new and returning visitors for the specified date range. It fetches 
     * the relevant data from Google Analytics, formats it for a pie chart, and returns it in 
     * a JSON response. The chart colors can be customized through filters.
     */
    public function new_vs_returning_visitors_stats()
    {

        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        $chart_description = array(
            'new_vs_returning_visitors' => array(
                'title' => esc_html__('New vs Returning Visitors', 'azo-tracking'),
                'type' => 'PIE',
                'stats' => array(
                    'new' => array(
                        'label' => esc_html__('New', 'azo-tracking'),
                        'number' => 0,
                    ),
                    'returning' => array(
                        'label' => esc_html__('Returning', 'azo-tracking'),
                        'number' => 0,
                    ),
                ),
                'colors' => apply_filters('azo_new_vs_returning_visitors_chart_colors', array('#03a1f8', '#00c853')),
            ),
        );

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        $general_stats_raw = $azo_tracking_ga->get_reports('show-default-overall-visitors-dashboard', array(
            'newUsers',
            'activeUsers',
        ), $this->get_dates());

        $general_stats = $general_stats_raw['aggregations'];


        if (isset($general_stats['newUsers'])) {
            $chart_description['new_vs_returning_visitors']['stats']['new']['number'] = $general_stats['newUsers'];
        }

        if (isset($general_stats['activeUsers'])) {
            $chart_description['new_vs_returning_visitors']['stats']['returning']['number'] = $general_stats['activeUsers'];
        }

        return wp_send_json_success(
            array(
                'success' => true,
                'charts' => apply_filters('azo_general_stats_charts', $chart_description, array($this->start_date, $this->end_date)),
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve visitor device statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * statistics about the devices used by visitors for the specified date range. It fetches 
     * the relevant data from Google Analytics, formats it for a pie chart, and returns it in 
     * a JSON response. The chart shows the number of sessions for mobile, tablet, and desktop devices.
     */
    public function visitor_devices_stats()
    {

        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        $chart_description = array(
            'visitor_devices' => array(
                'title' => esc_html__('Devices of Visitors', 'azo-tracking'),
                'type' => 'PIE',
                'stats' => array(
                    'mobile' => array(
                        'label' => esc_html__('Mobile', 'azo-tracking'),
                        'number' => 0,
                    ),
                    'tablet' => array(
                        'label' => esc_html__('Tablet', 'azo-tracking'),
                        'number' => 0,
                    ),
                    'desktop' => array(
                        'label' => esc_html__('Desktop', 'azo-tracking'),
                        'number' => 0,
                    ),
                ),
            ),
        );

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        $device_category_stats = $azo_tracking_ga->get_reports(
            'show-default-overall-device-dashboard',
            array(
                'sessions',
            ),
            $this->get_dates(),
            array(
                'deviceCategory',
            ),
            array(
                'type' => 'dimension',
                'name' => 'deviceCategory',
            )
        );

        if ($device_category_stats['rows']) {
            foreach ($device_category_stats['rows'] as $device) {
                $chart_description['visitor_devices']['stats'][$device['deviceCategory']]['number'] = $device['sessions'];
            }
        }


        return wp_send_json_success(
            array(
                'success' => true,
                'charts' => apply_filters('azo_general_stats_charts', $chart_description, array($this->start_date, $this->end_date)),
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve referer statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * referer statistics for the specified date range. It fetches the relevant data from 
     * Google Analytics, formats it to show the number of sessions from different sources 
     * and mediums, and returns it in a JSON response. The function also supports pagination 
     * and includes a footer message.
     */
    public function referer_stats()
    {


        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        // API limit.
        $api_stats_limit = apply_filters('azo_api_limit_referer_stats', 30, 'dashboard');

        $referer_stats = array();
        $total_sessions = false;


        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        //GA4
        $referer_stats_raw = $azo_tracking_ga->get_reports(
            'show-default-refers-dashboard',
            array(
                'sessions',
            ),
            $this->get_dates(),
            array(
                'sessionSource',
                'sessionMedium',
            ),
            array(
                'type' => 'metric',
                'name' => 'sessions',
                'order' => 'desc',
            ),
            array(),
            $api_stats_limit
        );

        if (isset($referer_stats_raw['aggregations']['sessions'])) {
            $total_sessions = $referer_stats_raw['aggregations']['sessions'];
        }

        if ($referer_stats_raw['rows']) {
            foreach ($referer_stats_raw['rows'] as $row) {
                $bar = '';
                if ($total_sessions && $total_sessions > 0) {
                    $bar = ' <span class="azo_bar_graph"><span style="width:' . ($row['sessions'] / $total_sessions) * 100 . '%"></span></span>';
                }
                $referer_stats[] = array(
                    'referer' => $row['sessionSource'] . '/' . $row['sessionMedium'] . $bar,
                    'sessions' => $row['sessions'],
                );
            }
        }

        wp_send_json_success(
            array(
                'success' => true,
                'headers' => array(
                    'referer' => array(
                        'label' => false,
                        'th_class' => '',
                        'td_class' => '',
                    ),
                    'sessions' => array(
                        'label' => false,
                        'th_class' => '',
                        'td_class' => 'azo_txt_center azo_value_row',
                    ),
                ),
                'stats' => $referer_stats,
                'pagination' => true,
                'title_stats' => $total_sessions ? '<span class="azo_medium_f">' . esc_html__('Total Visits', 'azo-tracking') . '</span> ' . $total_sessions : false,
                'footer' => apply_filters('azo_referer_footer', __('Top referrers to your website.', 'azo-tracking'), array($this->start_date, $this->end_date)),
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve top pages statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * statistics for the top pages based on various metrics like page views, average session 
     * duration, and bounce rate for the specified date range. It fetches the relevant data 
     * from Google Analytics, formats it, and returns it in a JSON response. The function 
     * supports pagination and includes a footer message.
     */
    public function top_pages_stats()
    {

        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();


        // API limit for pages.
        $api_limit = apply_filters('azo_api_limit_top_pages_stats', 50, 'dashboard');

        // Site URL.
        $site_url = $this->get_profile_info('website_url');

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        $stats = array();

        $stats_raw = $azo_tracking_ga->get_reports('show-default-top-pages-dashboard', array(
            'screenPageViews',
            'averageSessionDuration',
            'bounceRate',
        ), $this->get_dates(), array(
            'pageTitle',
            'pagePath',
        ), array(
            'type' => 'metric',
            'name' => 'screenPageViews',
            'order' => 'desc',
        ), array(
            'logic' => 'AND',
            'filters' => array(
                array(
                    'type' => 'dimension',
                    'name' => 'pageTitle',
                    'match_type' => 4,
                    'value' => '(not set)',
                    'not_expression' => true,
                ),
                array(
                    'type' => 'dimension',
                    'name' => 'pagePath',
                    'match_type' => 4,
                    'value' => '(not set)',
                    'not_expression' => true,
                ),
            ),
        ), $api_limit);
        if ($stats_raw['rows']) {
            $no = 1;
            foreach ($stats_raw['rows'] as $row) {
                $views = $row['screenPageViews'] ? AZO_Utils::pretty_numbers($row['screenPageViews']) : 0;
                if ($views < 1) {
                    continue;
                }
                $stats[] = array(
                    'no' => null,
                    'pageTitle' => '<span>' . $row['pageTitle'] . '</span>',
                    'screenPageViews' => $views,
                    'userEngagementDuration' => $row['averageSessionDuration'] ? AZO_Utils::pretty_time($row['averageSessionDuration']) : 0,
                    'bounceRate' => $row['bounceRate'] ? AZO_Utils::fraction_to_percentage($row['bounceRate']) . '%' : 0,
                );
            }
        }

        wp_send_json_success(
            array(
                'success' => true,
                'headers' => array(
                    'no' => array(
                        'label' => esc_html__('#', 'azo-tracking'),
                        'type' => 'counter',
                        'th_class' => 'azo_num_row',
                        'td_class' => 'azo_txt_center',
                    ),
                    'pageTitle' => array(
                        'label' => esc_html__('Title', 'azo-tracking'),
                        'th_class' => 'azo_txt_left',
                        'td_class' => '',
                    ),
                    'screenPageViews' => array(
                        'label' => esc_html__('Views', 'azo-tracking'),
                        'th_class' => 'azo_value_row',
                        'td_class' => 'azo_txt_center azo_value_row',
                    ),
                    'userEngagementDuration' => array(
                        'label' => esc_html__('Avg. Time', 'azo-tracking'),
                        'th_class' => 'azo_value_row',
                        'td_class' => 'azo_txt_center azo_value_row',
                    ),
                    'bounceRate' => array(
                        'label' => esc_html__('Bounce Rate', 'azo-tracking'),
                        'th_class' => 'azo_value_row',
                        'td_class' => 'azo_txt_center azo_value_row',
                    ),
                ),
                'stats' => $stats,
                'pagination' => true,
                'footer' => apply_filters('azo_top_pages_footer', __('Top pages and posts.', 'azo-tracking'), array($this->start_date, $this->end_date)),
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve "what is happening" statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * statistics about user engagement for the specified date range. It fetches the relevant 
     * data from Google Analytics, formats it to show engaged sessions, engagement rate, and 
     * user engagement duration, and returns it in a JSON response. The function supports 
     * filtering and customization through hooks.
     */
    public function what_is_happening_stats()
    {


        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        // API limit.
        $api_stats_limit = apply_filters('azo_api_limit_what_happen_stats', 5, 'dashboard');

        $what_happen_stats = array();
        $headers = false;
        $footer = false;

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        $page_stats_raw = $azo_tracking_ga->get_reports(
            'show-default-what-happen',
            array(
                'engagedSessions',
                'engagementRate',
                'userEngagementDuration',
            ),
            $this->get_dates(),
            array(
                'landingPage',
                'pageTitle',
            ),
            array(
                'type' => 'metric',
                'name' => 'engagedSessions',
                'order' => 'desc',
            ),
            array(),
            $api_stats_limit
        );

        if ($page_stats_raw['rows']) {
            $num = 1;
            foreach ($page_stats_raw['rows'] as $row) {
                $what_happen_stats[] = array(
                    'title_link' => '<span class="azo_page_name azo_bullet_' . $num . '">' . $row['pageTitle'] . '</span>',
                    'userEngagementDuration' => AZO_Utils::pretty_time($row['userEngagementDuration']),
                    'engagedSessions' => AZO_Utils::pretty_numbers($row['engagedSessions']),
                    'engagementRate' => '<div class="azo_enter_exit_bars">' . round(AZO_Utils::fraction_to_percentage($row['engagementRate']), 2) . '<span class="azo_persantage_sign">%</span><span class="azo_bar_graph"><span style="width:' . round(AZO_Utils::fraction_to_percentage($row['engagementRate']), 2) . '%"></span></span></div>',
                );
                $num++;
            }

            $headers = array(
                'title_link' => array(
                    'label' => esc_html__('Title / Link', 'azo-tracking'),
                    'th_class' => 'azo_txt_left azo_link_title',
                    'td_class' => 'azo_page_url_details',
                ),
                'userEngagementDuration' => array(
                    'label' => esc_html__('User Engagement Duration', 'azo-tracking'),
                    'th_class' => 'azo_compair_value_row',
                    'td_class' => 'azo_txt_center azo_w_300 azo_l_f',
                ),
                'engagedSessions' => array(
                    'label' => esc_html__('Engaged Sessions', 'azo-tracking'),
                    'th_class' => 'azo_compair_value_row',
                    'td_class' => 'azo_txt_center azo_w_300 azo_l_f',
                ),
                'engagementRate' => array(
                    'label' => esc_html__('Engagement Rate', 'azo-tracking'),
                    'th_class' => 'azo_compair_row',
                    'td_class' => 'azo_txt_center azo_w_300 azo_l_f',
                ),
            );
        }


        wp_send_json_success(
            array(
                'success' => true,
                'headers' => $headers,
                'stats' => $what_happen_stats,
                'footer' => $footer,
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve geographic statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * geographic statistics for the specified date range. It fetches the relevant data from 
     * Google Analytics, formats it to show top countries and cities by the number of sessions, 
     * and returns it in a JSON response. The function supports customization through hooks.
     */
    public function geographic_stats()
    {


        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        // Limit for the table data.
        $country_limit = apply_filters('azo_api_limit_country_stats', 5, 'dashboard');

        // API limit for cities.
        $cities_limit = apply_filters('azo_api_limit_city_stats', 5, 'dashboard');

        $geo_map_data = array();
        $country_stats = array();
        $city_stats = array();

        $after_top_country_text = '';
        $after_top_city_text = '';

        /**
         * For Pro legacy support.
         * CSV export button in generated by this action.
         */
        ob_start();
        do_action('azo_after_top_country_text');
        $after_top_country_text .= ob_get_clean();

        ob_start();
        do_action('azo_after_top_city_text');
        $after_top_city_text .= ob_get_clean();



        $dashboard_profile_id = AZO_Utils::get_reporting_property();
        $report_url = azo_get_ga_report_url($dashboard_profile_id);

        $after_top_country_text .= ' <a href="javascript: return false;" data-ga-dashboard-link="' . AZO_Utils::get_all_stats_link($report_url, 'top_countries') . '" target="_blank" class="azo_tooltip"><span class="azo_tooltiptext">' . __('View All Top Countries', 'azo-tracking') . '</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>';

        $after_top_city_text .= ' <a href="javascript: return false;" data-ga-dashboard-link="' . AZO_Utils::get_all_stats_link($report_url, 'top_cities') . '" target="_blank" class="azo_tooltip"><span class="azo_tooltiptext">' . __('View All Top Cities', 'azo-tracking') . '</span><span aria-hidden="true" class="dashicons dashicons-external"></span></a>';

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        $country_stats_raw = $azo_tracking_ga->get_reports(
            'show-geographic-countries-dashboard',
            array(
                'sessions',
            ),
            $this->get_dates(),
            array(
                'country',
            ),
            array(
                'type' => 'dimension',
                'name' => 'sessions',
                'order' => 'desc',
            ),
            array(
                'logic' => 'AND',
                'filters' => array(
                    array(
                        'type' => 'dimension',
                        'name' => 'country',
                        'match_type' => 4,
                        'value' => '(not set)',
                        'not_expression' => true,
                    ),
                ),
            )
        );

        $city_stats_raw = $azo_tracking_ga->get_reports(
            'show-geographic-cities-dashboard',
            array(
                'sessions',
            ),
            $this->get_dates(),
            array(
                'city',
                'country',
            ),
            array(
                'type' => 'metric',
                'name' => 'sessions',
                'order' => 'desc',
            ),
            array(
                'logic' => 'AND',
                'filters' => array(
                    array(
                        'type' => 'dimension',
                        'name' => 'city',
                        'match_type' => 4,
                        'value' => '(not set)',
                        'not_expression' => true,
                    ),
                    array(
                        'type' => 'dimension',
                        'name' => 'country',
                        'match_type' => 4,
                        'value' => '(not set)',
                        'not_expression' => true,
                    ),
                ),
            ),
            $cities_limit
        );

        if ($country_stats_raw['rows']) {
            $country_count = 0;
            foreach ($country_stats_raw['rows'] as $row) {
                if ($country_count < $country_limit) {
                    $country_stats[] = array(
                        'country' => '<span role="img" aria-label="' . $row['country'] . '" class="azo_' . str_replace(' ', '_', strtolower($row['country'])) . ' azo_flages"></span> ' . $row['country'],
                        'sessions' => $row['sessions'],
                    );
                }
                if ('United States' === $row['country']) {
                    $row['country'] = 'United States of America';
                }
                $geo_map_data[] = $row;
                $country_count++;
            }
        }

        if ($city_stats_raw['rows']) {
            foreach ($city_stats_raw['rows'] as $row) {
                $city_stats[] = array(
                    'city' => '<span  role="img" aria-label="' . $row['country'] . '" class="azo_' . str_replace(' ', '_', strtolower($row['country'])) . ' azo_flages"></span> ' . $row['city'],
                    'sessions' => $row['sessions'],
                );
            }
        }


        $country = array(
            'headers' => array(
                'country' => array(
                    'label' => esc_html__('Top Countries', 'azo-tracking') . $after_top_country_text,
                    'th_class' => 'azo_txt_left azo_vt_middle azo_top_geographic_detials_wraper',
                    'td_class' => '',
                ),
                'sessions' => array(
                    'label' => esc_html__('Visitors', 'azo-tracking'),
                    'th_class' => 'azo_value_row',
                    'td_class' => 'azo_txt_center',
                ),
            ),
            'stats' => $country_stats,
        );

        $city = array(
            'headers' => array(
                'city' => array(
                    'label' => esc_html__('Top Cities', 'azo-tracking') . $after_top_city_text,
                    'th_class' => 'azo_txt_left azo_vt_middle azo_top_geographic_detials_wraper azo_brd_lft',
                    'td_class' => 'azo_boder_left',
                ),
                'sessions' => array(
                    'label' => esc_html__('Visitors', 'azo-tracking'),
                    'th_class' => 'azo_value_row',
                    'td_class' => 'azo_txt_center',
                ),
            ),
            'stats' => $city_stats,
        );

        wp_send_json_success(
            array(
                'success' => true,
                'map' => array(
                    'title' => esc_html__('Geographic Stats', 'azo-tracking'),
                    'label' => array(
                        'high' => esc_html__('High', 'azo-tracking'),
                        'low' => esc_html__('Low', 'azo-tracking'),
                    ),
                    'stats' => $geo_map_data,
                    'highest' => !empty($geo_map_data) ? max(array_column($geo_map_data, 'sessions')) + 1 : 1,
                    'colors' => apply_filters('azo_world_map_colors', array('#ff5252', '#ffbc00', '#448aff')),
                ),
                'country' => $country,
                'city' => $city,
                'footer' => apply_filters('azo_top_country_city_footer', __('Top countries and cities.', 'azo-tracking'), array($this->start_date, $this->end_date)),
            )
        );
    }

    /**
     * Handle the AJAX request to retrieve daily visitors statistics.
     *
     * This function verifies the nonce for security and processes the request to retrieve 
     * statistics about daily visitors for the specified date range. It fetches the relevant 
     * data from Google Analytics, formats it to show the number of active users per day, and 
     * returns it in a JSON response. The function supports customization through hooks.
     */
    public function daily_visitors_stats()
    {
        $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo-tracking-dashboard')) {
            wp_send_json_error(array('message' => 'Sorry, your nonce did not verify.'));
            return;
        }



        $this->handle_request(
            sanitize_text_field(wp_unslash($_GET['start_date'])),
            sanitize_text_field(wp_unslash($_GET['end_date'])),
            sanitize_text_field(empty($_GET['date_differ']) ? '' : sanitize_text_field(wp_unslash($_GET['date_differ'])))
        );
        $this->set_compare_dates();

        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
        $azo_tracking_ga = $GLOBALS['AZO_TRACKING_GA'];

        // Get reports from Google Analytics
        $daily_visitors_stats = $azo_tracking_ga->get_reports(
            'daily-visitors-stats',
            array('activeUsers'), // Metric to get active users/visitors
            $this->get_dates(),
            array('date'), // Dimension to get data per day
            array(
                'type' => 'dimension',
                'name' => 'date',
                'order' => 'asc',
            ),
            array()
        );

        // Prepare response
        $response = array(
            'success' => true,
            'data' => array(),
        );

        if (!empty($daily_visitors_stats['rows'])) {
            foreach ($daily_visitors_stats['rows'] as $row) {
                $response['data'][] = array(
                    'date' => $row['date'],
                    'activeUsers' => $row['activeUsers'],
                );
            }
        } else {
            $response['data'][] = array(
                'date' => sanitize_text_field($_GET['start_date']) . ' - ' . sanitize_text_field($_GET['end_date']),
                'activeUsers' => 0,
            );
        }

        wp_send_json_success($response);
    }

    /**
     * Get profile related data based on the key (option) provided.
     *
     * @param string $key Option name.
     * @return string|null
     */
    private function get_profile_info($key)
    {
        $dashboard_profile_id = AZO_Utils::get_reporting_property();
        switch ($key) {
            case 'profile_id':
                return $dashboard_profile_id;
            case 'website_url':
                return azo_search_profile_info($dashboard_profile_id, 'websiteUrl');
            default:
                return null;
        }
    }

    /**
     * Returns start and end date as an array to be used for GA4's get_reports()
     *
     * @return array
     */
    public function get_dates()
    {
        return array(
            'start' => $this->start_date,
            'end' => $this->end_date,
        );
    }

    /**
     * Compares current stat with the previous one and returns the formatted difference.
     *
     * @param int    $current_stat Current stat.
     * @param int    $old_stat     Old stat to compare with.
     * @param string $type         Type of stat (key).
     *
     * @return array | false
     */
    public function compare_stat($current_stat, $old_stat, $type)
    {

        // Check for compare dates.
        if (is_null($this->compare_start_date) || is_null($this->compare_end_date) || is_null($this->compare_days)) {
            return false;
        }

        // So we don't divide by zero.
        if (!$old_stat || 0 == $old_stat) {
            return false;
        }
        $number = number_format((($current_stat - $old_stat) / $old_stat) * 100, 2);

        if ('bounce_rate' === $type) {
            $arrow_type = ($number < 0) ? 'azo_green_inverted' : 'azo_red_inverted';
        } else {
            $arrow_type = ($number > 0) ? 'azo_green' : 'azo_red';
        }

        return array(
            'arrow_type' => $arrow_type,
            'main_text' => $number . esc_html__('%', 'azo-tracking'),
            // translators: %s is the number of days
            'sub_text' => sprintf(esc_html__('%s days ago', 'azo-tracking'), $this->compare_days),
        );
    }

}

/**
 * Create instance of azo_tracking_ga class.
 */
function azo_tracking_rest_api_instance()
{
    $GLOBALS['AZO_Tracking_Rest_API'] = AZO_Tracking_Rest_API::get_instance();
}
add_action('plugins_loaded', 'azo_tracking_rest_api_instance', 20);