<?php

/**
 * Admin AZO Tracking Class
 *
 * @package   AZO_Tracking
 * @author    AZO Team <support@azonow.com>
 * @license   GPL-2.0+
 * @link      https://azonow.com
 * @copyright since 2024 AZO Team
 *
 */
class AZO_Tracking_Admin
{

	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Instance of admin notice class.
	 *
	 * @var      object $notices
	 */
	protected $notices = null;

	/**
	 * Slug of the settings page
	 *
	 * @var      string $plugin_screen_hook_suffix
	 */
	public $plugin_screen_hook_suffix = null;

	/**
	 * General plugin slug
	 *
	 * @var     string
	 */
	protected $plugin_slug = 'azo-tracking';

	/**
	 * Admin settings.
	 *
	 * @var      array
	 */
	protected static $admin_settings = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 */
	private function __construct()
	{
		if (wp_doing_ajax()) {
		} else {
			add_action('plugins_loaded', [$this, 'wp_plugins_loaded']);
			add_filter('admin_footer_text', [$this, 'admin_footer_text'], 100);
			add_filter('admin_body_class', [$this, 'add_custom_admin_body_class'], 100);
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @return	object	A single instance of this class.
	 */
	public static function get_instance()
	{
		// If the single instance hasn't been set, set it now.
		if (null === self::$instance) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Actions and filter available after all plugins are initialized.
	 */
	public function wp_plugins_loaded()
	{
		// load admin style sheet and javascript.
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_styles']);
		add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 9);

		AZO_Tracking_Admin_Menu::get_instance();
	}

	/**
	 * Enqueue admin stylesheet.
	 */
	public function enqueue_admin_styles()
	{
		$screen = get_current_screen();

		if (strpos($screen->id, $this->plugin_slug) !== FALSE) {

			// Include Date Range Picker CSS
			wp_enqueue_style($this->plugin_slug . '-admin-daterangepicker-style', AZOTRACKING_BASE_URL . 'assets/css/daterangepicker.css', [], AZOTRACKING_VERSION);
			// skeleton
			wp_enqueue_style($this->plugin_slug . '-admin-skeleton-style', AZOTRACKING_BASE_URL . 'assets/css/skeleton.min.css', [], AZOTRACKING_VERSION);
			// Include apexchart
			wp_enqueue_style($this->plugin_slug . '-admin-apps-style', AZOTRACKING_BASE_URL . 'assets/css/apps.min.css', [], AZOTRACKING_VERSION);

			wp_enqueue_style($this->plugin_slug . '-admin-style', AZOTRACKING_BASE_URL . 'assets/css/admin.css', [], AZOTRACKING_VERSION);
		}
	}

	/**
	 * Enqueue admin javaScript.
	 */
	public function enqueue_admin_scripts()
	{
		$screen = get_current_screen();


		if (strpos($screen->id, $this->plugin_slug) !== FALSE) {

			// Enqueue utils
			wp_enqueue_script($this->plugin_slug . '-admin-utils-js', AZOTRACKING_BASE_URL . 'assets/js/admin/utils.js', array('jquery'), AZOTRACKING_VERSION, true);
			wp_localize_script($this->plugin_slug . '-admin-utils-js', 'AZO', array('AJAX_URL' => admin_url('admin-ajax.php')));

			// Include Moment.js from WordPress Core
			wp_enqueue_script('moment');

			// Include Date Range Picker JS
			wp_enqueue_script($this->plugin_slug . '-admin-daterangepicker-js', AZOTRACKING_BASE_URL . 'assets/js/admin/daterangepicker.js', [], AZOTRACKING_VERSION, true);

			// Include apexchart js
			wp_enqueue_script($this->plugin_slug . '-admin-apps-js', AZOTRACKING_BASE_URL . 'assets/js/admin/apps.min.js', [], AZOTRACKING_VERSION, false);

			if (strpos($screen->id, 'dashboard') !== FALSE) {
				$azo_tracking_options = get_option('azo-tracking-dashboard-page');
				if ($azo_tracking_options && is_string($azo_tracking_options)) {
					$azo_tracking_options = unserialize($azo_tracking_options);
				}
				$show_dashboard_stats = isset($azo_tracking_options['show_dashboard_stats']) ? $azo_tracking_options['show_dashboard_stats'] : array();
				wp_enqueue_script($this->plugin_slug . '-dashboard-js', AZOTRACKING_BASE_URL . 'assets/js/admin/dashboard.js', array('jquery'), AZOTRACKING_VERSION, true);
				wp_localize_script(
					$this->plugin_slug . '-dashboard-js',
					'azotracking_dashboard',
					array(
						'AZOTRACKING_BASE_URL' => AZOTRACKING_BASE_URL,
						'show_dashboard_stats' => $show_dashboard_stats,
						'settings_page_url' => admin_url('admin.php?page=azo-tracking-settings')
					)
				);
			}

			// azo tracking ajax init
			wp_enqueue_script($this->plugin_slug . '-admin-js', AZOTRACKING_BASE_URL . 'assets/js/admin/main.js', array('jquery'), AZOTRACKING_VERSION, true);
			wp_localize_script($this->plugin_slug . '-admin-main-js', 'azoads_ajax', array('ajaxurl' => admin_url('admin-ajax.php')));

			// azo tracking settings js
			wp_enqueue_script($this->plugin_slug . '-settings-js', AZOTRACKING_BASE_URL . 'assets/js/admin/settings-page.js', array('jquery'), AZOTRACKING_VERSION, true);
			wp_localize_script($this->plugin_slug . '-settings-js', 'settings_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
		}
	}

	/**
	 * Rewrite WordPress text in Footer
	 *
	 * @param String $default_text The default footer text.
	 *
	 * @return string
	 */
	public function screen_belongs_to_AZO_tracking()
	{

		if (!function_exists('get_current_screen'))
			return false;

		$screen = get_current_screen();
		if (!isset($screen->id))
			return false;

		return in_array(
			$screen->id,
			array(
				'toplevel_page_azo-tracking',
				'azo-tracking_page_azo-tracking-settings',
			)
		);
	}

	/**
	 * Rewrite WordPress text in Footer
	 *
	 * @param String $default_text The default footer text.
	 *
	 * @return string
	 */
	public function admin_footer_text($default_text)
	{
		if ($this->screen_belongs_to_AZO_tracking()) {
			return 'Please consider leaving us &#9733;&#9733;&#9733;&#9733;&#9733; review on <a href="https://wordpress.org/support/plugin/azo-tracking/reviews/" target="_blank">wordpress.org</a>. Thank you.';
		}

		return $default_text;
	}

	/**
	 * Adds a custom class to the body tag in the WordPress admin area.
	 * 
	 * @param string $classes The existing classes for the admin body tag.
	 *
	 * @return string The modified list of classes with the custom class appended.
	 */

	function add_custom_admin_body_class($classes)
	{
		$classes .= 'azo-tracking_page';
		return $classes;
	}
}
