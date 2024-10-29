<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

class AZO_Tracking_Admin_Menu
{
	/**
	 * Instance of this class.
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * AZO_Ads_Admin_Menu constructor.
	 */
	private function __construct()
	{
		// add menu items.
		add_action('admin_menu', [$this, 'add_plugin_admin_menu']);
		add_action('admin_head', [$this, 'hide_submenu_pages'], 10);

	}

	/**
	 * Return an instance of this class.
	 *
	 * @return    object    A single instance of this class.
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
	 * Register the administration menu for this plugin into the WordPress dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu()
	{


		add_menu_page(
			__('AZO Tracking', 'azo-tracking'),
			'AZO Tracking',
			'hidden',
			AZOTRACKING_SLUG,
			[$this, 'redirect_to_dashboard_page'],
			'dashicons-google',
			'59.74'
		);

		// add dashboard page.
		add_submenu_page(
			AZOTRACKING_SLUG,
			__('Dashboard', 'azo-tracking'),
			__('Dashboard', 'azo-tracking'),
			'manage_options',
			AZOTRACKING_SLUG . '-dashboard',
			[$this, 'display_dashboard_page']
		);
		// add support page.
		add_submenu_page(
			AZOTRACKING_SLUG,
			__('Support', 'azo-tracking'),
			__('Support', 'azo-tracking'),
			'manage_options',
			AZOTRACKING_SLUG . '-support',
			[$this, 'display_support_page']
		);
		// add settings page.
		add_submenu_page(
			AZOTRACKING_SLUG,
			__('AZO Tracking Settings', 'azo-tracking'),
			__('Settings', 'azo-tracking'),
			'manage_options',
			AZOTRACKING_SLUG . '-settings',
			[$this, 'display_plugin_settings_page']
		);


		// if (!defined('AZOADS_PRO_VERSION')) {
		// 	// add support page.
		// 	add_submenu_page(
		// 		AZOTRACKING_SLUG,
		// 		__('AZO Ads Upgrade', 'azo-ads'),
		// 		__('Upgrade to Pro', 'azo-ads'),
		// 		'manage_options',
		// 		AZOTRACKING_SLUG . '-upgrade',
		// 		[$this, 'display_plugin_upgrade_page']
		// 	);
		// }

	}


	/**
	 * Hide sepecific submenus for plugin
	 *
	 * @since    1.0.0
	 */
	public function hide_submenu_pages()
	{

		remove_submenu_page(AZOTRACKING_SLUG, AZOTRACKING_SLUG . '-support');
		remove_submenu_page(AZOTRACKING_SLUG, AZOTRACKING_SLUG . '-authentication');
	}

	/**
	 * Redirect to dashboard page
	 *
	 * @since    1.0.0
	 */
	public function redirect_to_dashboard_page()
	{
		wp_redirect(admin_url('admin.php?page=' . AZOTRACKING_SLUG . '-dashboard'));
		exit;
	}
	/**
	 * Render the dashboard page
	 *
	 * @since    1.0.0
	 */
	public function display_dashboard_page()
	{

		include AZOTRACKING_BASE_PATH . 'views/admin/dashboard.php';
	}

	/**
	 * Render the support page
	 *
	 * @since    1.0.0
	 */
	public function display_support_page()
	{

		include AZOTRACKING_BASE_PATH . 'views/admin/support.php';
	}

	/**
	 * Render the settings page
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_settings_page()
	{
		include AZOTRACKING_BASE_PATH . 'views/admin/settings.php';
	}

	/**
	 * Render the authentication page
	 *
	 * @since    1.0.0
	 */
	public function display_authentication_page()
	{
		include AZOTRACKING_BASE_PATH . 'views/admin/authentication.php';
	}
}