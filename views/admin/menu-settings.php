<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div id="azo-tracking-settings-page">
    <?php
        $azo_tracking_settings_instances=$GLOBALS['AZO_TRACKING_SETTINGS'];
        $azo_tracking_settings_instances->show_tabs();
    ?>