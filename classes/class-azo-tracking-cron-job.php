<?php

defined('ABSPATH') || exit;

class AZO_Tracking_Cron_Job
{


	private static function download()
	{
		$azo_tracking_settings_instance = $GLOBALS['AZO_TRACKING_SETTINGS'];
		$ga_id = $azo_tracking_settings_instance->get_ga_code();
		if (!$ga_id) {
			AZO_Tracking_Debug_Logging::debug('Ga code is not set.', 'azo-tracking');
			return;
		}

		$remote_file     = 'https://www.googletagmanager.com/gtag/js?id=' . $ga_id;
		$downloaded_file = AZO_Tracking_Hosting_Locally::download_file($remote_file, 'gtag');
		$downloaded_files['gtag.js'] = AZO_Tracking_Hosting_Locally::get_file_alias();

		/**
		 * Writes all currently stored file aliases to the database.
		 */
		AZO_Tracking_Hosting_Locally::set_file_aliases(AZO_Tracking_Hosting_Locally::get_file_aliases(), true);

		return $downloaded_files;
	}

	public static function update_file_host_locally()
	{

		$create_dir = AZO_Tracking_Hosting_Locally::create_dir_r(AZO_Tracking_Hosting_Locally::get_local_dir());

		if ($create_dir) {
			// translators: %s refers to the local directory path created.
			AZO_Tracking_Debug_Logging::debug(sprintf(__('%s created successfully.', 'azo-tracking'), AZO_Tracking_Hosting_Locally::get_local_dir()));
		} else {
			// translators: %s refers to the local directory path that already exists.
			AZO_Tracking_Debug_Logging::debug(sprintf(__('%s already exists.', 'azo-tracking'), AZO_Tracking_Hosting_Locally::get_local_dir()));
		}

		$downloaded_files = self::download();

		if (!empty($downloaded_files)) {
			AZO_Tracking_Debug_Logging::debug('Gtag.js is downloaded successfully and updated accordingly.', 'azo-tracking');
		}
	}

	public static function update_profile_ga()
	{
		$ga_instances = $GLOBALS['AZO_TRACKING_GA'];

		delete_option('azo_profiles_list_summary');
		$ga_properties = $ga_instances->fetch_ga_properties();

		delete_option('azo_ga4_streams');
		$ga_streams = $ga_instances->fetch_all_ga_streams();
	}
}
