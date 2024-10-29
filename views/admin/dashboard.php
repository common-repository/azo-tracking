<?php
include 'header.php';
include 'menu-dashboard.php';


$GLOBALS['AZO_TRACKING_GA'] = AZO_Tracking_GA::get_instance();
$isConnectGoogle = $GLOBALS['AZO_TRACKING_GA']->azo_connect_ga();
$property_id = AZO_Utils::get_reporting_property();

$allowed_roles_option = get_option('azo-tracking-dashboard-page');

if ($allowed_roles_option && is_string($allowed_roles_option)) {
    $allowed_roles_option = unserialize($allowed_roles_option);
}

$current_user = wp_get_current_user();
$current_user_roles = $current_user->roles;

$can_view_dashboard = false;
if (isset($allowed_roles_option['show_dashboard_for_roles']) && is_array($allowed_roles_option['show_dashboard_for_roles'])) {
    foreach ($current_user_roles as $role) {
        if (in_array($role, $allowed_roles_option['show_dashboard_for_roles'])) {
            $can_view_dashboard = true;
            break;
        }
    }
}

?>
<?php wp_nonce_field('azo-tracking-dashboard', '_wpnonce'); ?>

<div class="azo-tracking-content">
    <?php if (!$isConnectGoogle): ?>
        <div class="azo-tracking-notification">
            <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-danger.svg" />
            <span>AZO Tracking Dashboard can't be loaded until you sign in with your Google Analytics <a
                    class="redirect-to-authentication-tap"
                    href="<?php echo esc_url(admin_url('admin.php?page=azo-tracking-settings')); ?>">here</a>.</span>
        </div>
    <?php elseif ($isConnectGoogle && empty($property_id)): ?>
        <div class="azo-tracking-notification">
            <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-danger.svg" />
            <span>AZO Tracking Dashboard can't be loaded until you select your reporting property <a
                    class="redirect-to-profile-tap"
                    href="<?php echo esc_url(admin_url('admin.php?page=azo-tracking-settings')); ?>">here</a>.</span>
        </div>
    <?php elseif (!$can_view_dashboard && is_admin()): ?>
        <div class="azo-tracking-notification">
            <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-danger.svg" />
            <span>You do not have permission to view the dashboard.</span>
        </div>
    <?php else: ?>
        <div id="azo-tracking-dashboard">
            <div class="head-dashboard mt-2">
                <h3> Overview Report</h3>
                <form action="" id="dateForm">
                    <div id="reportrange">
                        <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-calendar.svg" />
                        <span></span>
                        <img class="date-dropdown"
                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-dropdown.svg" />
                    </div>
                </form>
            </div>

            <div class="body-dashboard mt-2">
                <div data-endpoint="general_stats" id="general_stats" class="body-dashboard-row at-rp-card-wrapper">
                    <div class="at-rp-card">
                        <div class="at-rp-card-top">
                            <h4>General Statistics</h4>
                        </div>
                        <div class="at-rp-card-mid at-rp-card-box-items at-rp-card-wrapper-4col">
                        </div>
                        <div class="at-skeleton-loading-container skeleton-box-container">
                            <div class="at-rp-card-item">
                                <div style="width:100%; --lines: 3" class="skeleton skeleton-line">
                                </div>
                            </div>
                            <div class="at-rp-card-item">
                                <div style="width:100%; --lines: 3" class="skeleton skeleton-line">
                                </div>
                            </div>
                            <div class="at-rp-card-item">
                                <div style="width:100%; --lines: 3" class="skeleton skeleton-line">
                                </div>
                            </div>
                            <div class="at-rp-card-item">
                                <div style="width:100%; --lines: 3" class="skeleton skeleton-line">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div data-endpoint="daily_visitors_stats" class="body-dashboard-row at-rp-card-wrapper">
                    <div class="at-rp-card">
                        <div class="at-rp-card-top">
                            <h4>Website Visitors</h4>
                            <div class="at-icon-description-container">
                                <span class="info-link">
                                    <img class="info-icon"
                                        data-description="Website Visitors chart represents the number and behavior of users visiting a website over a specified period."
                                        src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                </span>
                                <div class="at-description-tooltip"></div>
                            </div>
                        </div>
                        <div class="at-skeleton-loading-container">
                            <div style="width:100%" id="at-rp-chart-skeleton" class="skeleton skeleton-chart-columns">
                            </div>
                        </div>
                        <div id="at-rp-daily-visitor" class="at-rp-chart"></div>
                    </div>
                </div>
                <div class="donut-charts-container">
                    <div class="body-dashboard-row at-rp-card-wrapper-donut-chart" data-endpoint="visitor_devices_stats">
                        <div class="at-rp-card">
                            <div class="at-rp-card-top">
                                <h4>Devices of Visitors</h4>
                                <div class="at-icon-description-container">
                                    <span class="info-link">
                                        <img class="info-icon"
                                            data-description="Devices of Visitors chart shows the distribution of devices used by website visitors."
                                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                    </span>
                                    <div class="at-description-tooltip"></div>
                                </div>
                            </div>
                            <div class="at-skeleton-loading-container skeleton-circle-container" id="at-rp-device-skeleton">
                                <div class="skeleton skeleton-circle" style="--c-s: 280px;--c-w: 320px;"></div>
                            </div>
                            <div id="at-rp-device" class="at-rp-chart"></div>
                            <div class="no-data-message">
                            </div>
                        </div>
                    </div>
                    <div class="body-dashboard-row at-rp-card-wrapper-donut-chart"
                        data-endpoint="new_vs_returning_visitors_stats">
                        <div class="at-rp-card">
                            <div class="at-rp-card-top">
                                <h4>New vs Returning Visitors</h4>
                                <div class="at-icon-description-container">
                                    <span class="info-link">
                                        <img class="info-icon"
                                            data-description="New vs Returning Visitors chart compares the number of first-time visitors to those who have visited the website before."
                                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                    </span>
                                    <div class="at-description-tooltip"></div>
                                </div>
                            </div>
                            <div class="at-skeleton-loading-container skeleton-circle-container"
                                id="at-rp-new-returning-skeleton">
                                <div class="skeleton skeleton-circle" style="--c-s: 280px;--c-w: 320px;">
                                </div>
                            </div>
                            <div id="at-rp-new-returning" class="at-rp-chart"></div>
                            <div class="no-data-message">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mini-lists-container">
                    <div class="body-dashboard-row at-rp-card-wrapper at-rp-card-wrapper-mini-lists"
                        data-endpoint="operating_systems_stats">
                        <div class="at-rp-card at-rp-os">
                            <div class="at-rp-card-top">
                                <h4>Operating System</h4>
                                <div class="at-icon-description-container">
                                    <span class="info-link">
                                        <img class="info-icon"
                                            data-description="The operating system used by visitors on your website or application (e.g., Android, Chrome OS, iOS, Windows)."
                                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                    </span>
                                    <div class="at-description-tooltip"></div>
                                </div>
                            </div>
                            <div class="at-skeleton-loading-container">
                                <div style="width:100%;--lines: 5" id="at-rp-os-skeleton" class="skeleton skeleton-rect">
                                </div>
                            </div>
                            <div class="at-rp-card-mid at-rp-card-list">
                            </div>
                            <div class="no-data-message">
                            </div>
                        </div>
                    </div>
                    <div class="body-dashboard-row at-rp-card-wrapper at-rp-card-wrapper-mini-lists"
                        data-endpoint="browser_stats">
                        <div class="at-rp-card at-rp-system">
                            <div class="at-rp-card-top">
                                <h4>Browser Stats</h4>
                                <div class="at-icon-description-container">
                                    <span class="info-link">
                                        <img class="info-icon"
                                            data-description="Browser tracking involves collecting data on user behavior and interactions within a web browser. "
                                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                    </span>
                                    <div class="at-description-tooltip"></div>
                                </div>
                            </div>
                            <div class="at-skeleton-loading-container">
                                <div style="width:100%;--lines: 5" id="at-rp-browser-skeleton"
                                    class="skeleton skeleton-rect">
                                </div>
                            </div>
                            <div class="at-rp-card-mid at-rp-card-list">
                            </div>
                            <div class="no-data-message">
                            </div>
                        </div>
                    </div>

                </div>
                <div class="mini-lists-container">
                    <div class="body-dashboard-row at-rp-card-wrapper at-rp-card-wrapper-mini-lists"
                        data-endpoint="referer_stats">
                        <div class="at-rp-card at-rp-referrers">
                            <div class="at-rp-card-top">
                                <h4>Top Referrers</h4>
                                <div class="at-icon-description-container">
                                    <span class="info-link">
                                        <img class="info-icon"
                                            data-description="This list shows the top websites that send your website traffic, known as referral traffic."
                                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                    </span>
                                    <div class="at-description-tooltip"></div>
                                </div>
                            </div>
                            <div class="at-skeleton-loading-container">
                                <div style="width:100%;--lines: 5" id="at-rp-referer-skeleton"
                                    class="skeleton skeleton-rect">
                                </div>
                            </div>
                            <div class="at-rp-card-mid at-rp-card-list">
                            </div>
                            <div class="no-data-message">
                            </div>
                        </div>
                    </div>
                    <div class="body-dashboard-row at-rp-card-wrapper at-rp-card-wrapper-mini-lists"
                        data-endpoint="geographic_stats">
                        <div class="at-rp-card at-rp-referrers">
                            <div class="at-rp-card-top">
                                <h4>Geographic Stats</h4>
                                <div class="at-icon-description-container">
                                    <span class="info-link">
                                        <img class="info-icon"
                                            data-description="Geographic Stats displays the distribution of website visitors by their geographic locations"
                                            src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                    </span>
                                    <div class="at-description-tooltip"></div>
                                </div>
                            </div>
                            <div class="at-skeleton-loading-container">
                                <div style="width:100%;--lines: 5" id="at-rp-referer-skeleton"
                                    class="skeleton skeleton-rect">
                                </div>
                            </div>
                            <div class="at-rp-card-mid at-rp-card-list">
                            </div>
                            <div class="no-data-message">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="body-dashboard-row at-rp-card-wrapper" data-endpoint="top_pages_stats">
                    <div class="at-rp-card at-rp-pages-by-views">
                        <div class="at-rp-card-top">
                            <h4>Top Pages by views</h4>
                            <div class="at-icon-description-container">
                                <span class="info-link">
                                    <img class="info-icon"
                                        data-description="This list shows the most viewed posts and pages on your website."
                                        src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                </span>
                                <div class="at-description-tooltip"></div>
                            </div>
                        </div>
                        <div class="at-skeleton-loading-container">
                            <div style="width:100%;--lines: 5" id="at-rp-pageviews-list-skeleton"
                                class="skeleton skeleton-rect">
                            </div>
                        </div>
                        <div class="at-rp-card-mid at-rp-card-list at-rp-card-list-detail">
                        </div>
                        <div class="no-data-message">
                        </div>
                    </div>
                </div>

                <div class="body-dashboard-row at-rp-card-wrapper" data-endpoint="what_is_happening_stats">
                    <div class="at-rp-card at-rp-happening-stats">
                        <div class="at-rp-card-top">
                            <h4>What's happening when users come to your site</h4>
                            <div class="at-icon-description-container">
                                <span class="info-link">
                                    <img class="info-icon"
                                        data-description="This list show the actions and behaviors users engage in upon arriving at your website, including page views, interactions, and engagement patterns."
                                        src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-info.svg" />
                                </span>
                                <div class="at-description-tooltip"></div>
                            </div>
                        </div>
                        <div class="at-skeleton-loading-container">
                            <div style="width:100%;--lines: 5" id="at-rp-pageviews-list-skeleton"
                                class="skeleton skeleton-rect">
                            </div>
                        </div>
                        <div class="at-rp-card-mid at-rp-card-list at-rp-card-list-detail">
                        </div>
                        <div class="no-data-message">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
<?php include 'footer.php'; ?>