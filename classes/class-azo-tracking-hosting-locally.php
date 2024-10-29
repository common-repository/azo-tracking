<?php
defined('ABSPATH') || exit;

class AZO_Tracking_Hosting_Locally
{

	protected $settings_instance;

	private $is_host_locally;

	public function __construct()
	{
		$this->settings_instance = $GLOBALS['AZO_TRACKING_SETTINGS'];
		$this->is_host_locally = $this->settings_instance->get_option('locally_host_analytics', 'azo-tracking-configuration', false);
		$this->is_host_locally = $this->is_host_locally === false || $this->is_host_locally === 'off' ? false : $this->is_host_locally;
	}


	/**
	 * @param string $alias
	 * @param bool   $write
	 *
	 * @return bool
	 */
	public static function set_file_alias($alias, $write = false)
	{
		$file_aliases = self::get_file_aliases();

		$file_aliases['gtag'] = $alias;

		return self::set_file_aliases($file_aliases, $write);
	}

	/**
	 * @return false|array Global variable containing all saved file aliases.
	 */
	public static function get_file_aliases()
	{
		return get_option('azo_tracking_host_file_aliases');
	}

	/**
	 * @param array $file_aliases
	 * @param bool  $write
	 *
	 * @return bool
	 */
	public static function set_file_aliases($file_aliases, $write = false)
	{

		if ($write) {
			return update_option('azo_tracking_host_file_aliases', $file_aliases);
		}

		return true;
	}

	/**
	 * @return string|void
	 */
	public static function get_file_alias_path()
	{
		$file_path = self::get_local_dir() . 'gtag.js';

		// Backwards compatibility
		if (!self::get_file_aliases()) {
			return $file_path;
		}

		$file_alias = self::get_file_alias() ?? '';

		// Backwards compatibility
		if (!$file_alias) {
			return $file_path;
		}

		return self::get_local_dir() . $file_alias;
	}

	/**
	 * @since v1.0.0
	 * @return string Absolute path to cache directory.
	 */
	public static function get_local_dir()
	{
		return apply_filters('azo_tracking_local_dir', AZOTRACKING_LOCALLY_HOST_DIR);
	}


	/**
	 * Get alias of JS library.
	 * @return string
	 */
	public static function get_file_alias()
	{
		$file_aliases = self::get_file_aliases();

		if (!$file_aliases) {
			return '';
		}

		return $file_aliases['gtag'] ?? '';
	}

	/**
	 * Check if the local gtag library file exists.
	 *
	 * @return boolean
	 */
	public function file_already_exist()
	{

		if (file_exists($this->get_local_dir() . 'azo-gtag.js') || file_exists($this->get_local_dir() . $this->get_file_alias())) {
			return true;
		}

		return false;
	}

	public function get_local_file_url()
	{

		if (!$this->is_host_locally || !$this->file_already_exist()) {
			return null;
		}

		$url = content_url() .  '/uploads/azo-tracking/' . 'gtag.js';

		/**
		 * is_ssl() fails when behind a load balancer or reverse proxy. That's why we double check here if
		 * SSL is enabled and rewrite accordingly.
		 */
		if (strpos(home_url(), 'https://') !== false && !is_ssl()) {
			$url = str_replace('http://', 'https://', $url);
		}


		$file_alias = self::get_file_alias();

		if (!$file_alias) {
			return $url;
		}

		$url = str_replace('gtag.js', $file_alias, $url);

		return apply_filters('azo_tracking_local_file_url', $url);
	}


	/**
	 * File downloader
	 *
	 * @param mixed  $local_file
	 * @param mixed  $remote_file
	 * @param string $file
	 *
	 * @return string
	 */
	public static function download_file($remote_file, $file = '')
	{
		$download = new AZO_Tracking_Host_File_Manager();

		return $download->download_file($remote_file, $file);
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function create_dir_r($path)
	{
		$file_manager = new AZO_Tracking_Host_File_Manager();

		return $file_manager->create_dir_recursive($path);
	}

	/**
	 * @param string $file
	 * @param string $find
	 * @param string $replace
	 *
	 * @return int|false
	 */
	public static function find_replace_in($file, $find, $replace)
	{
		$file_manager = new AZO_Tracking_Host_File_Manager();

		return $file_manager->find_replace_in($file, $find, $replace);
	}
}

function azo_tracking_hosting_locally_init()
{
	new AZO_Tracking_Hosting_Locally();
}
add_action('plugins_loaded', 'azo_tracking_hosting_locally_init', 30);
