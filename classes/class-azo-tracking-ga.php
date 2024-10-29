<?php
if (!defined('ABSPATH')) {
    exit;
}
/*If Use Test, Enable below code */
// define('AZOTRACKING_CLIENTID', '869487743288-p9v1d0gsd9htpj36d4fdiq22u7ea2ing.apps.googleusercontent.com');
// define('AZOTRACKING_CLIENTSECRET', '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');
// define('AZOTRACKING_REDIRECTURI', 'https://nongnghiepdongnai.azdigi.shop/google-login-callback');

define('AZOTRACKING_CLIENTID', '622956391917-mg6cht1p4f9sqq5ah1ru1gig3mj8j92u.apps.googleusercontent.com');
define('AZOTRACKING_CLIENTSECRET', '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');
define('AZOTRACKING_REDIRECTURI', 'https://my2.azonow.com/google-login-callback');
define('AZOTRACKING_SCOPE', 'https://www.googleapis.com/auth/analytics.readonly https://www.googleapis.com/auth/analytics https://www.googleapis.com/auth/analytics.edit https://www.googleapis.com/auth/webmasters');


/**
 * Class AZO_Tracking_GA
 * 
 * This class handles Google Analytics integration for AZO Tracking plugin.
 */
class AZO_Tracking_GA
{
    private static $instance = null;
    public $service;
    public $client;
    public $token;

    private $ga4_exception;

    protected $transient_timeout;
    protected $plugin_base;
    protected $plugin_settings_base;

    private $cache_timeout = 60 * 60 * 10;

    /**
     * Constructor method for AZO_Tracking_GA class.
     */
    public function __construct()
    {
        // Initialize plugin base URLs.
        $this->plugin_base = 'admin.php?page=azo-tracking-dashboard';
        $this->plugin_settings_base = 'admin.php?page=azo-tracking-settings';
        $this->ga4_exception = get_option('azo_ga4_exceptions');

        // Attempt to connect to Google Analytics.
        try {
            $this->azo_connect_ga();
        } catch (Exception $e) {
            error_log($e->getMessage());
        }

        // Add action hook to check authentication status.
        add_action('admin_init', array($this, 'azo_check_authentication'));
    }

    /**
     * Get singleton instance of AZO_Tracking_GA class.
     *
     * @return AZO_Tracking_GA|null Singleton instance of AZO_Tracking_GA class.
     */
    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Method to connect to Google Analytics.
     */
    public function azo_connect_ga()
    {
        $ga_google_authtoken = get_option('azo_google_token');

        if (!empty($ga_google_authtoken)) {
            $ga_google_authtoken = is_array($ga_google_authtoken) ? wp_json_encode($ga_google_authtoken) : $ga_google_authtoken;
            $this->token = json_decode($ga_google_authtoken);
        } else {
            $auth_code = get_option('post_azotracking_token');

            if (empty($auth_code)) {
                return false;
            }

            try {
                $access_token = $this->authenticate($auth_code);
            } catch (\Throwable $th) {
                error_log($th->getMessage());
                $response = $th->getMessage();
                $response = json_decode($response);
                AZO_Utils::add_ga4_exception('authenticate_exception', $response->reason, $response->message);
                return false;
            }

            if ($access_token) {
                update_option('azo_google_token', wp_json_encode($access_token));
                $this->token = $access_token;
                return true;
            } else {
                return false;
            }
        }

        return true;
    }

    /**
     * Authenticate using OAuth2 and return the token data.
     *
     * This function sends a POST request to the Google OAuth2 token URL with the provided 
     * authorization code and client credentials. If the request is successful and returns 
     * a valid response, the token data is returned. If there is an error, it logs the error details.
     *
     * @param string $auth_code The authorization code obtained from the OAuth2 flow.
     * @return array|null The token data array if successful, or null if there is an error.
     */
    private function authenticate($auth_code)
    {
        if (!empty($auth_code)) {
            $token_url = 'https://oauth2.googleapis.com/token';

            $response = wp_remote_post($token_url, [
                'body' => [
                    'code' => $auth_code,
                    'client_id' => AZOTRACKING_CLIENTID,
                    'client_secret' => AZOTRACKING_CLIENTSECRET,
                    'redirect_uri' => AZOTRACKING_REDIRECTURI,
                    'grant_type' => 'authorization_code',
                ],
            ]);

            if (is_wp_error($response)) {
                error_log(print_r($response, true));
            }

            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);
            if (!isset($data['error'])) {
                return $data;
            }
            error_log(print_r($data, true));
            return;
        }
    }


    /**
     * Get URL for Google login.
     *
     * @return string Google login URL.
     */
    public function azo_google_login_url()
    {


        $auth_url = 'https://accounts.google.com/o/oauth2/auth?' . http_build_query([
            'client_id' => AZOTRACKING_CLIENTID,
            'redirect_uri' => AZOTRACKING_REDIRECTURI,
            'response_type' => 'code',
            'scope' => AZOTRACKING_SCOPE,
            'access_type' => 'offline',
            'approval_prompt' => 'force',
            'state' => urlencode(azo_get_current_url() . '&_wpnonce=' . wp_create_nonce('azo_tracking_ga_auth')),
        ]);

        return $auth_url;
    }

    /**
     * Check authentication status.
     */
    public function azo_check_authentication()
    {
        $uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
        if (strpos($uri, 'azo-tracking-callback-login') !== false) {
            $_wpnonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';
            if ($_wpnonce == '' || !wp_verify_nonce($_wpnonce, 'azo_tracking_ga_auth')) {
                $plugin_page_url = admin_url('plugins.php');
                wp_die(
                    sprintf(
                        // translators: %1$s: start link tag, %2$s: end link tag
                        esc_html__('Sorry, you are not allowed as nonce verification failed. %1$sClick here to return to the Dashboard%2$s.', 'azo-tracking'),
                        '<a href="' . esc_url($plugin_page_url) . '">',
                        '</a>'
                    )
                );
            }
            $code_google = sanitize_text_field(wp_unslash($_GET['code']));
            self::azo_save_data($code_google);
            wp_redirect(admin_url('admin.php?page=azo-tracking-settings'));
        }
    }

    /**
     * Save authentication data.
     *
     * @param string $code_google Authentication code from Google.
     * @return bool|null True if authentication data saved successfully, false otherwise.
     */
    public function azo_save_data($code_google)
    {
        try {
            update_option('post_azotracking_token', $code_google);

            if ($this->azo_connect_ga()) {
                return true;
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
        }
    }

    /**
     * Get Google Analytics authentication details.
     *
     * @return array|null Google Analytics authentication details.
     */
    private function get_ga_auth_details()
    {

        $azo_google_token = get_option('azo_google_token');
        $google_token = is_array($azo_google_token) ? $azo_google_token : json_decode($azo_google_token, TRUE);

        if (!empty($google_token)) {
            return array(
                'credentials' => Google\ApiCore\CredentialsWrapper::build(
                    array(
                        'scopes' => explode(' ', AZOTRACKING_SCOPE),
                        'keyFile' => array(
                            'type' => 'authorized_user',
                            'client_id' => AZOTRACKING_CLIENTID,
                            'client_secret' => AZOTRACKING_CLIENTSECRET,
                            'refresh_token' => $google_token['refresh_token'],
                        )
                    )
                ),
            );
        }
    }

    /**
     * Connect with Google Analytics admin API.
     * 
     * @return BetaAnalyticsDataClient
     */

    private function connect_data_api()
    {

        $client = new Google\Analytics\Data\V1beta\BetaAnalyticsDataClient($this->get_ga_auth_details());

        return $client;
    }

    /**
     * Connect with Google Analytics admin API.
     * 
     * @return AnalyticsAdminServiceClient
     */
    private function connect_admin_api()
    {

        $client = new Google\Analytics\Admin\V1alpha\AnalyticsAdminServiceClient($this->get_ga_auth_details());

        return $client;
    }

    /**
     * Get Google Analytics properties.
     *
     * @return array|null  Google Analytics properties, or miss azo_google_token.
     */
    public function get_ga_properties()
    {
        $admin_client = $this->connect_admin_api();
        $accounts = array();
        $ga_properties = array();

        try {
            $admin_client = $this->connect_admin_api();

            if (get_option('azo_google_token') != '') {
                $accounts = $admin_client->listAccounts();
            } else {
                throw new \Exception('{"reason": "authorization_error", "message": "No Google token available."}');
            }

            foreach ($accounts as $account) {
                $formatted_account_name = 'parent:' . $account->getName();
                $properties = $admin_client->listProperties($formatted_account_name);
                $property_data = array();

                foreach ($properties as $property) {
                    // Extract property id since there is no direct method to get it (API is in alpha).
                    $id = explode('/', $property->getName());
                    $id = isset($id[1]) ? $id[1] : $property->getName();

                    $property_data[] = array(
                        'id' => $id,
                        'name' => $property->getName(),
                        'display_name' => $property->getDisplayName(),
                    );
                }

                if ($property_data) {
                    $ga_properties[$account->getDisplayName()] = $property_data;
                }
            }
        } catch (\Throwable $th) {
            $response = $th->getMessage();
            $response = json_decode($response);
            AZO_Utils::add_ga4_exception('get_properties_exception', $response->reason, $response->message);

            return $ga_properties;
        }
        AZO_Utils::remove_ga4_exception('get_properties_exception');
        return $ga_properties;
    }

    /**
     * Returns the property list that was saved after fetching from Google.
     * If DB does not contains the list, get using Google's method for GA4.
     *
     * @return array
     */

    public function fetch_ga_properties()
    {
        $properties = get_option('azo_profiles_list_summary');

        if (empty($properties) && get_option('azo_google_token')) {
            $properties = array();
            $ga4_profiles_raw = $this->get_ga_properties();
            if (!empty($ga4_profiles_raw)) {
                foreach ($ga4_profiles_raw as $parent_account_name => $account_properties) {
                    foreach ($account_properties as $property_item) {
                        // Push into an array with the property name as key and profile ID as child key.
                        $properties[$parent_account_name][$property_item['id']] = array(
                            'name' => $property_item['display_name'],
                            'code' => $property_item['id'],
                            'property_id' => '',
                            'website_url' => '',
                            'web_property_id' => '',
                            'view_id' => '',
                        );
                    }
                }
            }
            update_option('azo_profiles_list_summary', $properties);
        }
        return $properties;
    }

    /**
     * Fetches all the Google Analytics 4 data streams for a given property.
     *
     * @param string $property_id The ID of the property for which to fetch the data streams.
     *
     * @return array|false|null Array of data stream objects if found, null if something Error, otherwise false or empty array, null Error.
     */
    public function get_ga_streams($property_id)
    {
        // If no property ID specified, return false.
        if (empty($property_id)) {
            return false;
        }

        // Format the property ID for the request.
        $formatted_parent = 'properties/' . $property_id;

        // Get all the streams saved in the database.
        $ga4_streams = get_option('azo_ga4_streams', array());
        // Check if there are any streams for the current property.
        $streams = $ga4_streams[$property_id] ?? array();

        // If streams exist for this property, return them.
        if (!empty($streams)) {
            return $streams;
        }

        // Connect to the Google Analytics Admin API.
        $admin_client = $this->connect_admin_api();

        // Call the API and save the streams.
        try {
            $response = $admin_client->listDataStreams($formatted_parent);

            // Array to store the streams.
            $all_streams = array();

            foreach ($response as $element) {
                $serialize = $element->serializeToJsonString();
                // Deserialize the response to a stdClass object.
                $stream_obj = json_decode($serialize);

                // We only need web streams.
                if ($stream_obj->type === 'WEB_DATA_STREAM') {
                    // Store the current stream data.
                    $stream_data = array(
                        'full_name' => $stream_obj->name,
                        'property_id' => $property_id,
                        'stream_name' => $stream_obj->displayName,
                        'measurement_id' => $stream_obj->webStreamData->measurementId,
                        'url' => $stream_obj->webStreamData->defaultUri,
                    );
                    // Add the current stream to the array of all streams.
                    $all_streams[$stream_obj->webStreamData->measurementId] = $stream_data;
                }
            }
            // Save the streams to the database.
            $ga4_streams[$property_id] = $all_streams;
            update_option('azo_ga4_streams', $ga4_streams);
            return $all_streams;
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            // Optionally, you could return an error message or a specific response
            return null;
        }
    }


    /**
     * Fetch all Google Analytics streams if not already cached.
     *
     * This function retrieves all Google Analytics 4 (GA4) streams associated with the accounts 
     * stored in the WordPress options. If the streams are not already cached, it fetches them 
     * from the Google Analytics API and caches the results. The function also logs the fetching 
     * process for debugging purposes.
     *
     * @return array|null The array of all GA4 streams if available, or null if not found.
     */
    public function fetch_all_ga_streams()
    {
        $all_streams = get_option('azo_ga4_streams');
        $accounts = get_option('azo_profiles_list_summary');
        if (!empty($accounts) && get_option('azo_google_token') && empty($all_streams)) {
            error_log('Fetching all streams');
            foreach ($accounts as $account) {
                foreach ($account as $key => $data) {
                    if (!empty($key) && is_numeric($key)) {
                        $this->get_ga_streams($key);
                    }
                }
            }
            $all_streams = get_option('azo_ga4_streams');
        }
        return $all_streams;
    }

    /**
     * Create web stream for AZO tracking in Google Analytics.
     * Stream types: Google\Analytics\Admin\V1alpha\DataStream\DataStreamType
     *
     * @param string $property_id
     * 
     * @return array|false|null Measurement data, false if not found propertyID, null if something Error.
     * 
     * @since 1.0.0
     */
    public function create_ga_stream($property_id)
    {
        $azo_ga4_streams = get_option('azo_ga4_streams');

        if (isset($azo_ga4_streams) && isset($azo_ga4_streams[$property_id]) && is_array($azo_ga4_streams[$property_id]) && !empty($azo_ga4_streams[$property_id])) {
            return $azo_ga4_streams[$property_id];
        }

        // Return if there is no property id given.
        if (!isset($azo_ga4_streams) || empty($property_id) || !is_array($azo_ga4_streams[$property_id])) {
            return false;
        }

        $admin_client = $this->connect_admin_api();
        $measurement_data = array();
        try {
            $formatted_property_name = $admin_client->propertyName($property_id);
            $stream_name = 'AZO - ' . get_site_url(); // AZO defined stream name.

            $data_stream = new Google\Analytics\Admin\V1alpha\DataStream(
                array(
                    'type' => 1,
                    'display_name' => $stream_name
                )
            );
            $web_data_stream = new Google\Analytics\Admin\V1alpha\DataStream\WebStreamData(
                array(
                    'default_uri' => get_site_url(),
                )
            );

            // Set data stream to web.
            $data_stream->setWebStreamData($web_data_stream);

            try { // Try to create new stream.
                $web_stream = $admin_client->createDataStream($formatted_property_name, $data_stream);
            } catch (\Throwable $th) { // Check if AZO stream already exists.
                $response = $th->getMessage();
                $response = json_decode($response);

                // Error code 6: ALREADY_EXISTS.
                if (isset($response->code) && 6 === $response->code) {
                    AZO_Utils::remove_ga4_exception('create_stream_exception');
                    $paged_response = $admin_client->listDataStreams($formatted_property_name);

                    // Get pre created stream.
                    foreach ($paged_response->iterateAllElements() as $element) {
                        if ($stream_name === $element->getDisplayName()) {
                            $web_stream = $element;
                            break;
                        }
                    }
                } else {
                    AZO_Utils::add_ga4_exception('create_stream_exception', $response->reason, $response->message);
                }
            }

            if ($web_stream) {
                AZO_Utils::remove_ga4_exception('create_stream_exception');
                $measurement_data = array(
                    'full_name' => $web_stream->getName(),
                    'property_id' => $property_id,
                    'stream_name' => $web_stream->getDisplayName(),
                    'measurement_id' => $web_stream->getWebStreamData()->getMeasurementId(),
                    'url' => $web_stream->getWebStreamData()->getDefaultUri(),
                );

                if (empty($azo_ga4_streams)) {
                    $azo_ga4_streams = array();
                }
                $azo_ga4_streams[$property_id][$web_stream->getWebStreamData()->getMeasurementId()] = $measurement_data;
                update_option('azo_ga4_streams', $azo_ga4_streams);
            }
        } catch (\Throwable $th) {
            AZO_Utils::add_ga4_exception('create_stream_exception', $response->reason, $response->message);
            return false;
        } finally {
            $admin_client->close();
        }
        return $azo_ga4_streams[$property_id];
    }

    /**
     * Fetch reports from Google Analytics Data API.
     * @param string $name 'test-report-name' Its the key used to store reports in transient as cache.
     * @param array $metrics {
     *     'screenPageViews',
     *	   'userEngagementDuration',
     *	   'bounceRate',
     * }
     * @param array $date_range {
     *     'start' => '30daysAgo', Format should be either YYYY-MM-DD, yesterday, today, or NdaysAgo where N is a positive integer
     *     'end'   => 'yesterday', 
     * }
     * @param array $dimensions {
     *     'pageTitle',
     *     'pagePath'
     * }
     * @param array $order_by {
     *     'type' => 'metric', Should be either 'metric' or 'dimension'.
     *     'name' => 'screenPageViews', Name of the metric or dimension.
     * }
     * @param array $filters {
     *     {
     *          'type' => 'dimension', Should be either 'metric' or 'dimension'.
     *          'name' => 'sourcePlatform', Name of the metric or dimension.
     *          'match_type' => 5, (EXACT = 1; BEGINS_WITH = 2; ENDS_WITH = 3; CONTAINS = 4; FULL_REGEXP = 5; PARTIAL_REGEXP = 6;)
     *          'value' => 'Linux', Value depending on match type.
     *          'not_expression' => true, If a not expression i.e !=
     *     },
     *     {
     *         ...
     *     }
     *     ...
     * }
     * @param integer array $limit Positive integer to limit report rows.
     * 
     * @return array {
     * 	   'headers' => {
     *         ...
     * 	   },
     * 	   'rows' => {
     *         ...
     * 	   }
     * }
     */
    public function get_reports($name, $metrics, $date_range, $dimensions = array(), $order_by = array(), $filters = array(), $limit = 0, $cached = true)
    {
        $property_id = AZO_Utils::get_reporting_property();
        // To override the caching.
        $cached = apply_filters('azo_set_caching_to', $cached);

        if ($cached) {
            $cache_key = 'azo_transient_' . md5($name . $property_id . $date_range['start'] . $date_range['end']);
            $report_cache = get_transient($cache_key);

            if ($report_cache) {
                return $report_cache;
            }
        }

        $reports = array();

        // Default response array.
        $default_response = array(
            'headers' => array(),
            'rows' => array(),
            'error' => array(),
            'aggregations' => array(),
        );


        try {
            $data_client = $this->connect_data_api();
            // Main request body for the report.
            $request_body = array(
                'property' => 'properties/' . $property_id,
                'dateRanges' => array(
                    new Google\Analytics\Data\V1beta\DateRange(
                        array(
                            'start_date' => isset($date_range['start']) ? $date_range['start'] : 'today',
                            'end_date' => isset($date_range['end']) ? $date_range['end'] : 'today',
                        )
                    ),
                ),
                'metricAggregations' => array(1) // TOTAL = 1; COUNT = 4; MINIMUM = 5; MAXIMUM = 6;
            );

            // Set metrics.
            if ($metrics) {
                $send_metrics = array();

                foreach ($metrics as $value) {
                    $send_metrics[] = new Google\Analytics\Data\V1beta\Metric(array('name' => $value));
                }

                $request_body['metrics'] = $send_metrics;
            }

            // Add dimensions.
            if ($dimensions) {
                $send_dimensions = array();

                foreach ($dimensions as $value) {
                    $send_dimensions[] = new Google\Analytics\Data\V1beta\Dimension(array('name' => $value));
                }

                $request_body['dimensions'] = $send_dimensions;
            }

            // Order report by metric or dimension.
            if ($order_by) {
                $order_by_request = array();
                $is_desc = (empty($order_by['order']) || 'desc' !== $order_by['order']) ? false : true;

                if ('metric' === $order_by['type']) {
                    $order_by_request = array(
                        'metric' => new Google\Analytics\Data\V1beta\OrderBy\MetricOrderBy(
                            array(
                                'metric_name' => $order_by['name']
                            )
                        ),
                        'desc' => $is_desc,
                    );
                } else if ('dimension' === $order_by['type']) {
                    $order_by_request = array(
                        'dimension' => new Google\Analytics\Data\V1beta\OrderBy\DimensionOrderBy(
                            array(
                                'dimension_name' => $order_by['name']
                            )
                        ),
                        'desc' => $is_desc,
                    );
                }

                $request_body['orderBys'] = [new Google\Analytics\Data\V1beta\OrderBy($order_by_request)];
            }

            // Filters for the report.
            if ($filters) {

                foreach ($filters['filters'] as $filter_data) {
                    if ('dimension' === $filter_data['type']) {
                        if (isset($filter_data['not_expression']) && $filter_data['not_expression']) {
                            $dimension_filters[] = new Google\Analytics\Data\V1beta\FilterExpression(
                                array(
                                    'not_expression' => new Google\Analytics\Data\V1beta\FilterExpression(
                                        array(
                                            'filter' =>
                                            new \Google\Analytics\Data\V1beta\Filter(
                                                array(
                                                    'field_name' => $filter_data['name'],
                                                    'string_filter' => new \Google\Analytics\Data\V1beta\Filter\StringFilter(
                                                        array(
                                                            'match_type' => $filter_data['match_type'],
                                                            'value' => $filter_data['value'],
                                                            'case_sensitive' => true
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            );
                        } else {
                            $dimension_filters[] = new Google\Analytics\Data\V1beta\FilterExpression(
                                array(
                                    'filter' => new \Google\Analytics\Data\V1beta\Filter(
                                        array(
                                            'field_name' => $filter_data['name'],
                                            'string_filter' => new \Google\Analytics\Data\V1beta\Filter\StringFilter(
                                                array(
                                                    'match_type' => $filter_data['match_type'],
                                                    'value' => $filter_data['value'],
                                                    'case_sensitive' => true
                                                )
                                            )
                                        )
                                    )
                                )
                            );
                        }
                    } else if ('metric' === $filter_data['type']) {
                        // TODO: Add metric filter.
                    }
                }

                if ($dimension_filters) {
                    $group_type = (isset($filters['logic']) && 'OR' === $filters['logic']) ? 'or_group' : 'and_group';
                    $dimension_filter_construct = new Google\Analytics\Data\V1beta\FilterExpression(
                        array(
                            $group_type =>
                            new \Google\Analytics\Data\V1beta\FilterExpressionList(
                                array('expressions' => $dimension_filters)
                            )
                        )
                    );

                    $request_body['dimensionFilter'] = $dimension_filter_construct;
                }
            }

            // Set limit.
            if (0 < $limit) {
                $request_body['limit'] = $limit;
            }
            // Send reports request.
            $reports = $data_client->runReport($request_body);
        } catch (\Throwable $th) {
            error_log($th->getMessage());
            if (is_callable($th, 'getStatus') && is_callable($th, 'getBasicMessage')) {
                $default_response['error'] = array(
                    'status' => $th->getStatus(),
                    'message' => $th->getBasicMessage(),
                );
            } else if (method_exists($th, 'getMessage')) {
                $default_response['error'] = array(
                    'status' => 'Token Expired',
                    'message' => $th->getMessage(),
                    // 'basicmessage' => $th->getBasicMessage(),
                );
            }
            return $default_response;
        }
        if (!is_object($reports)) {
            $default_response['error'] = array(
                'status' => 'API Request Error',
                'message' => 'Invalid response from API, proper object not found.',
            );

            return $default_response;
        }
        if (0 === $reports->getRowCount()) {
            return $default_response;
        }

        $formatted_reports = $this->format_ga_reports($reports);

        if (empty($formatted_reports)) {
            return $default_response;
        }

        if ($cached) {
            set_transient($cache_key, $formatted_reports, $this->get_cache_time());
        }

        return $formatted_reports;
    }

    public function get_cache_time()
    {
        return $this->cache_timeout;
    }

    /**
     * Format reports data fetched from Google Analytics Data API.
     *
     * For references check folder for class definitions: lib\Google\vendor\google\analytics-data\src\V1beta
     *
     * @param $reports
     * @return array
     */
    public function format_ga_reports($reports)
    {

        $metric_header_data = array();
        $dimension_header_data = array();
        $aggregations = array();

        $data_rows = $reports->getRows();

        // Get metric headers.
        foreach ($reports->getMetricHeaders() as $metric_header) {
            $metric_header_data[] = $metric_header->getName();
        }
        // Get dimension headers.
        foreach ($reports->getDimensionHeaders() as $dimension_header) {
            $dimension_header_data[] = $dimension_header->getName();
        }

        $headers = array_merge($metric_header_data, $dimension_header_data);

        // Bind metrics and dimensions to rows.
        foreach ($data_rows as $row) {
            $metric_data = array();
            $dimension_data = array();

            $index_metric = 0;
            $index_dimension = 0;

            foreach ($row->getMetricValues() as $value) {
                $metric_data[$metric_header_data[$index_metric]] = $value->getValue();
                $index_metric++;
            }

            foreach ($row->getDimensionValues() as $value) {
                $dimension_data[$dimension_header_data[$index_dimension]] = $value->getValue();
                $index_dimension++;
            }

            $rows[] = array_merge($metric_data, $dimension_data);
        }

        // Get metric aggregations.
        foreach ($reports->getTotals() as $total) {
            $index_metric = 0;

            foreach ($total->getMetricValues() as $value) {
                $aggregations[$metric_header_data[$index_metric]] = $value->getValue();
                $index_metric++;
            }
        }

        $formatted_data = array(
            'headers' => $headers,
            'rows' => $rows,
            'aggregations' => $aggregations
        );

        return $formatted_data;
    }
}



/**
 * Create instance of azo_tracking_ga class.
 */
function azo_tracking_ga_instance()
{
    $uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
    if (strpos($uri, 'azo-tracking') !== false) {
        require_once __DIR__ . '/../vendor/autoload.php';
        $GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
    }
}
add_action('plugins_loaded', 'azo_tracking_ga_instance', 20);
