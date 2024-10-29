<?php

/**
 * AZO Tracking
 *
 * @package   AZO_Ads
 * @author    AZO Team <support@azonow.com>
 * @license   GPL-2.0+
 * @link      https://azonow.com
 * @copyright since 2023 AZO Team
 *
 * @wordpress-plugin
 * Plugin Name:       AZO Tracking
 * Plugin URI:        https://ads.azonow.com
 * Description:       AZO Tracking delivers real-time insights and powerful reports from Google Analytics 4 to supercharge your website and app performance.
 * Version:           1.0.1
 * Author:            AZO Team
 * Author URI:        https://azonow.com
 * Text Domain:       azo-tracking
 * Domain Path:       /languages
 * License:           GPL-2.0+
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.txt
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// define importantly constant
define('AZOTRACKING_BASE', plugin_basename(__FILE__));
define('AZOTRACKING_BASE_PATH', plugin_dir_path(__FILE__));
define('AZOTRACKING_BASE_URL', plugin_dir_url(__FILE__));
define('AZOTRACKING_BASE_DIR', dirname(AZOTRACKING_BASE));
define('AZOTRACKING_NAME', 'AZO Tracking');
define('AZOTRACKING_SLUG', 'azo-tracking');
define('AZOTRACKING_URL', 'https://azonow.com/');
define('AZOTRACKING_VERSION', '1.0.1');
define('AZOTRACKING_HOST_FILE_ALIAS', 'azo-gtag.js');
define('AZOTRACKING_LOCALLY_HOST_DIR', WP_CONTENT_DIR . apply_filters('azo_tracking_dir_to_host_analytics', '/uploads/azo-tracking/'));

// INIT plugin
// on plugin activation
register_activation_hook(__FILE__, "activate_azotracking");

// activate plugin
function activate_azotracking()
{
    do_action('azo_tracking_active');
}

// on plugin de-activation
register_deactivation_hook(__FILE__, "deactivate_azotracking");
// de-activate plugin
function deactivate_azotracking()
{
    do_action('azo_tracking_deactive');
}




// load main functions
require_once AZOTRACKING_BASE_PATH . 'includes/functions.php';
foreach (glob(AZOTRACKING_BASE_PATH . '/classes/*.php') as $filename) {
    require_once $filename;
}

// // load admin functionality
if (is_admin()) {
    AZO_Tracking_Admin::get_instance();
}
