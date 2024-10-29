<?php
defined('ABSPATH') || exit;

if (!class_exists('AZO_Tracking_Schedule_Event')) {
    class AZO_Tracking_Schedule_Event
    {
        protected $settings_instance;
        private $is_host_locally;
        public function __construct()
        {
            add_action('update_option_azo-tracking-profile', array($this, 'azo_tracking_host_file_maybe_update'), 20, 2);
            add_action('update_option_azo-tracking-profile', array($this, 'azo_tracking_ga_profile_maybe_update'), 10, 2);
            add_action('update_option_azo-tracking-configuration', array($this, 'azo_tracking_host_file_maybe_update'), 10, 2);
            add_action('azo_tracking_update_file_event', array($this, 'trigger_cron_update_file'));
            add_action('azo_tracking_update_profile_event', array($this, 'trigger_cron_update_profile'));
            $this->settings_instance = $GLOBALS['AZO_TRACKING_SETTINGS'];
            $this->is_host_locally = $this->settings_instance->get_option('locally_host_analytics', 'azo-tracking-configuration', false);
            $this->is_host_locally = $this->is_host_locally === false || $this->is_host_locally === 'off' ? false : $this->is_host_locally;
        }

        function azo_tracking_host_file_maybe_update($old_value, $new_value)
        {
            if ($this->settings_instance->get_option('add_ga_script', 'azo-tracking-profile') !== 'on') {
                wp_clear_scheduled_hook('azo_tracking_update_file_event');
                return;
            }
            if ($this->settings_instance->get_option('locally_host_analytics', 'azo-tracking-configuration') !== 'on') {
                wp_clear_scheduled_hook('azo_tracking_update_file_event');
                return;
            }
            if (!wp_next_scheduled('azo_tracking_update_file_event')) {
                wp_schedule_event(time(), 'daily', 'azo_tracking_update_file_event');
            }
        }

        function trigger_cron_update_file()
        {
            AZO_Tracking_Cron_Job::update_file_host_locally();
        }

        function azo_tracking_ga_profile_maybe_update($old_value, $new_value)
        {
            if (!get_option('azo_google_token')) {
                wp_clear_scheduled_hook('azo_tracking_update_profile_event');
                return;
            }
            if (!wp_next_scheduled('azo_tracking_update_profile_event')) {
                wp_schedule_event(time(), 'daily', 'azo_tracking_update_profile_event');
            }
        }
        function trigger_cron_update_profile()
        {
            AZO_Tracking_Cron_Job::update_profile_ga();
        }
    }
}
function azo_tracking_schedule_event()
{
    new AZO_Tracking_Schedule_Event();
}
add_action('plugins_loaded', 'azo_tracking_schedule_event', 40);
