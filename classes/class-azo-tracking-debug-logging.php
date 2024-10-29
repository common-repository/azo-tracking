<?php
defined('ABSPATH') || exit;

class AZO_Tracking_Debug_Logging
{


	/**
	 * Global debug logging function.
	 *
	 * @param mixed $message
	 *
	 * @return void
	 */
	public static function debug($message)
	{
		error_log(current_time('Y-m-d H:i:s') . ": $message\n", 3, trailingslashit(WP_CONTENT_DIR) . 'azo-debug.log');
	}
}
