<?php
if (!defined('ABSPATH')) {
    exit;
}

class AZO_Utils
{
    /**
     * Add an exception to the GA4 exceptions list.
     *
     * This static function records an exception by storing its type, reason, and message 
     * in the WordPress options. The exceptions are saved under the `azo_ga4_exceptions` 
     * option, allowing for tracking and debugging of GA4-related issues.
     *
     * @param string $type The type of exception.
     * @param string $reason The reason for the exception.
     * @param string $message The exception message.
     */
    public static function add_ga4_exception($type, $reason, $message)
    {
        $azo_ga4_exceptions = (array) get_option('azo_ga4_exceptions');
        $azo_ga4_exceptions[$type]['reason'] = $reason;
        $azo_ga4_exceptions[$type]['message'] = $message;
        update_option('azo_ga4_exceptions', $azo_ga4_exceptions);
    }

    /**
     * Remove an exception from the GA4 exceptions list.
     *
     * This static function removes an exception of a specified type from the WordPress options.
     * The exceptions are stored under the `azo_ga4_exceptions` option, and the function ensures 
     * that the specified type of exception is deleted from this list.
     *
     * @param string $type The type of exception to remove.
     */

    public static function remove_ga4_exception($type)
    {
        $azo_ga4_exceptions = (array) get_option('azo_ga4_exceptions');
        unset($azo_ga4_exceptions[$type]);
        update_option('azo_ga4_exceptions', $azo_ga4_exceptions);
    }

    /**
     * Calculate the date difference and generate comparison dates.
     *
     * This static function calculates the difference in days between the provided start date and end date.
     * It then generates a comparison start date and end date based on this difference. The function returns 
     * an array containing the comparison start date, comparison end date, and the number of days in the difference.
     *
     * @param string $start_date The start date in 'Y-m-d' format.
     * @param string $end_date The end date in 'Y-m-d' format.
     * @return array An array containing the comparison start date, comparison end date, and the number of days.
     */
    public static function calculate_date_diff($start_date, $end_date)
    {
        $diff = date_diff(date_create($end_date), date_create($start_date));
        $compare_start_date = gmdate('Y-m-d', strtotime($start_date . $diff->format(' %R%a days')));
        $compare_end_date = $start_date;
        $diff_days = $diff->format('%a');

        return array(
            'start_date' => $compare_start_date,
            'end_date' => $compare_end_date,
            'diff_days' => (string) $diff_days,
        );
    }

    /**
     * Retrieve the Google Analytics reporting property ID.
     *
     * This static function gets the reporting property ID from the global settings. If the 
     * property ID includes the 'ga4:' prefix, it removes the prefix to extract the actual 
     * property ID. The function then returns the cleaned property ID.
     *
     * @return string The Google Analytics reporting property ID.
     */
    public static function get_reporting_property()
    {

        $property_id = $GLOBALS['AZO_TRACKING_SETTINGS']->get_option('dashboard_profile', 'azo-tracking-profile');
        if (false !== strpos($property_id, 'ga4:')) {
            $property_id = explode(':', $property_id)[1];
        }

        return $property_id;
    }

    /**
     * Pretty numbers to display.
     *
     * @param int $time time.
     *
     * @since  1.0
     */
    public static function pretty_numbers($num)
    {

        if (!is_numeric($num)) {
            return $num;
        }

        return ($num > 10000) ? round(($num / 1000), 2) . 'k' : number_format($num);
    }


    /**
     * Pretty time to display.
     *
     * @param int $time time.
     *
     * @since  1.0
     */
    public static function pretty_time($time)
    {

        // Check if numeric.
        if (is_numeric($time)) {

            $value = array(
                'years' => '00',
                'days' => '00',
                'hours' => '',
                'minutes' => '',
                'seconds' => '',
            );

            $attach_hours = '';
            $attach_min = '';
            $attach_sec = '';

            $time = floor($time);

            if ($time >= 31556926) {
                $value['years'] = floor($time / 31556926);
                $time = ($time % 31556926);
            } //$time >= 31556926

            if ($time >= 86400) {
                $value['days'] = floor($time / 86400);
                $time = ($time % 86400);
            } //$time >= 86400
            if ($time >= 3600) {
                $value['hours'] = str_pad(floor($time / 3600), 1, 0, STR_PAD_LEFT);
                $time = ($time % 3600);
            } //$time >= 3600
            if ($time >= 60) {
                $value['minutes'] = str_pad(floor($time / 60), 1, 0, STR_PAD_LEFT);
                $time = ($time % 60);
            } //$time >= 60
            $value['seconds'] = str_pad(floor($time), 1, 0, STR_PAD_LEFT);
            // Get the hour:minute:second version.
            if ('' != $value['hours']) {
                $attach_hours = '<span class="azo_xl_f">' . _x('h', 'Hour Time', 'azo-tracking') . ' </span> ';
            }
            if ('' != $value['minutes']) {
                $attach_min = '<span class="azo_xl_f">' . _x('m', 'Minute Time', 'azo-tracking') . ' </span>';
            }
            if ('' != $value['seconds']) {
                $attach_sec = '<span class="azo_xl_f">' . _x('s', 'Second Time', 'azo-tracking') . '</span>';
            }
            return $value['hours'] . $attach_hours . $value['minutes'] . $attach_min . $value['seconds'] . $attach_sec;
        } //is_numeric($time)
        else {
            return false;
        }
    }

    /**
     * Convert a fraction to a percentage.
     *
     * This static function takes a fractional number, multiplies it by 100 to convert it to a 
     * percentage, and then formats it using the `AZO_Utils::pretty_numbers` method.
     *
     * @param float $number The fractional number to be converted to a percentage.
     * @return string The formatted percentage.
     */
    public static function fraction_to_percentage($number)
    {
        return AZO_Utils::pretty_numbers($number * 100);
    }

    /**
     * Create all stats external link takes to Google Analytics.
     *
     * @param string $report_url
     * @param string $report
     * @param string $date_range
     *
     * @return string
     */
    public static function get_all_stats_link($report_url, $report, $date_range = false)
    {

        switch ($report) {
            case 'top_pages':
                $link = 'top_pages';
                break;
            case 'top_countries':
                $link = 'top_countries';
                break;
            case 'top_cities':
                $link = 'top_cities';
                break;
            case 'referer':
                $link = 'referer';
                break;
            case 'top_products':
                $link = 'top_products';
                break;
            case 'source_medium':
                $link = 'source_medium';
                break;
            case 'top_countries_sales':
                $link = 'top_countries_sales';
                break;
            default:
                $link = '';
                break;
        }

        return $link;
    }
}
