<?php

defined('ABSPATH') || exit;

class AZO_Tracking_Host_File_Manager
{

	/**
	 * @var $file
	 */
	private $file_contents;

	/**
	 * Downloads the remote file, checks if the local file exists and deletes it if it does, then writes the remote file to the local file.
	 *
	 * @param $local_file
	 * @param $remote_file
	 * @param $file string
	 *
	 * @return void|string
	 */
	public function download_file($remote_file, $file = '')
	{
		do_action('azo_tracking_download_gtag_before');

		$this->file_contents = wp_remote_get($remote_file);

		if (is_wp_error($this->file_contents)) {
			// translators: %1$s refers to the error code and %2$s refers to the error message.
			AZO_Tracking_Debug_Logging::debug(sprintf(__('An error occurred: %1$s - %2$s', 'azo-tracking'), $this->file_contents->get_error_code(), $this->file_contents->get_error_message()));
			return $this->file_contents->get_error_code() . ': ' . $this->file_contents->get_error_message();
		}


		$file         = $file ? $file : pathinfo($remote_file)['filename'];
		$file_alias = AZO_Tracking_Hosting_Locally::get_file_alias();
		if (empty($file_alias) || $file_alias === 'azo-gtag.js') {
			$file_alias = bin2hex(random_bytes(4)) . '.js';
		}
		$local_dir = AZOTRACKING_LOCALLY_HOST_DIR;
		// translators: %s refers to the directory path where the data is being saved.
		AZO_Tracking_Debug_Logging::debug(sprintf(__('Saving to %s.', 'azo-tracking'), $local_dir));


		/**
		 * Some servers don't do a full overwrite if file already exists, so we delete it first.
		 */
		if ($file_alias && file_exists($local_dir . $file_alias)) {
			$deleted = wp_delete_file($local_dir . $file_alias);

			if ($deleted) {
				// translators: %s refers to the alias of the file that was successfully deleted.
				AZO_Tracking_Debug_Logging::debug(sprintf(__('File %s successfully deleted.', 'azo-tracking'), $file_alias));
			} else {
				if ($error = error_get_last()) {
					// translators: %1$s refers to the alias of the file that could not be deleted and %2$s refers to the error message.
					AZO_Tracking_Debug_Logging::debug(sprintf(__('File %1$s could not be deleted. Something went wrong: %2$s', 'azo-tracking'), $file_alias, $error['message']));
				} else {
					// translators: %s refers to the alias of the file that could not be deleted.
					AZO_Tracking_Debug_Logging::debug(sprintf(__('File %s could not be deleted. An unknown error occurred.', 'azo-tracking'), $file_alias));
				}
			}
		}
		$write = file_put_contents($local_dir . $file_alias, $this->file_contents['body']);

		if ($write) {
			// translators: %s refers to the alias of the file that was successfully saved.
			AZO_Tracking_Debug_Logging::debug(sprintf(__('File %s successfully saved.', 'azo-tracking'), $file_alias));
		} else {
			if ($error = error_get_last()) {
				// translators: %1$s refers to the alias of the file that could not be saved and %2$s refers to the error message.
				AZO_Tracking_Debug_Logging::debug(sprintf(__('File %1$s could not be saved. Something went wrong: %2$s', 'azo-tracking'), $file_alias, $error['message']));
			} else {
				// translators: %s refers to the alias of the file that could not be saved.
				AZO_Tracking_Debug_Logging::debug(sprintf(__('File %s could not be saved. An unknown error occurred.', 'azo-tracking'), $file_alias));
			}
		}

		/**
		 * Update the file alias in the global variable AND database.
		 */
		AZO_Tracking_Hosting_Locally::set_file_alias($file_alias, true);

		do_action('azo_tracking_download_gtag_after');

		return $local_dir . $file_alias;
	}

	/**
	 * Returns false if path already exists.
	 *
	 * @param mixed $path
	 * @return bool
	 */
	public function create_dir_recursive($path)
	{
		if (!file_exists($path)) {
			return wp_mkdir_p($path);
		}

		return false;
	}

	/**
	 * Find $find in $file and replace with $replace.
	 *
	 * @param $file string Absolute Path|URL
	 * @param $find array|string
	 * @param $replace array|string
	 */
	public function find_replace_in($file, $find, $replace)
	{
		// translators: %1$s refers to the text being replaced, %2$s refers to the replacement text, and %3$s refers to the file in which the replacement is being made.
		AZO_Tracking_Debug_Logging::debug(sprintf(__('Replacing %1$s with %2$s in %3$s.', 'azo-tracking'), print_r($find, true), print_r($replace, true), $file));
		return file_put_contents($file, str_replace($find, $replace, file_get_contents($file)));
	}
}
