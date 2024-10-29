<div id="azo-tracking-authentication">
    <?php wp_nonce_field('azo-tracking-authentication', 'azo-tracking-tab-authentication', ) ?>
    <div class="azo-settings-guildance">Set up a liaison between AZO Tracking and your Google Analytics account.</div>
    <div class="azo-settings-row">
        <span class="azo-settings-row-item">Google Authentication</span>
        <div class="azo-settings-row-item">
            <?php if (get_option('azo_google_token')): ?>
                <?php
                $GLOBALS['AZO_TRACKING_GA']->fetch_ga_properties();
                $GLOBALS['AZO_TRACKING_GA']->fetch_all_ga_streams();
                ?>
                <a href="#" class="azo-btn azo-auth-logout-ga" id="azo-auth-logout">
                    <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-google.svg"
                        alt="<?php esc_html_e('Signout', 'azo-tracking'); ?>">
                    Logout</a>
                <p class="azo-settings-description">You have allowed your site to access the data from your Google Analytics
                    account. Click on logout button to disconnect or re-authenticate.</p>
            <?php else: ?>
                <a href="<?php echo esc_html($GLOBALS['AZO_TRACKING_GA']->AZO_google_login_url()) ?>"
                    class="azo-btn azo-auth-login-ga">
                    <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-google.svg"
                        alt="<?php esc_html_e('Login', 'azo-tracking'); ?>">
                    Login with your Google Analytics Account</a>
                <p class="azo-settings-description">It is required to set up your account and a website profile at Google
                    Analytics to see AZO Tracking Dashboard reports.</p>
            <?php endif; ?>
        </div>
    </div>
</div>