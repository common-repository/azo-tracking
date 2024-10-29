<?php
include 'header.php';
include 'menu-dashboard.php';
?>

<div class="azo-tracking-content azo-tracking-content-support">
    <div id="azo-tracking-support">
        <div class="head-support mt-2">
            <h3>How can we help</h3>
        </div>
        <div class="body-support mt-3">
            <a target="_blank" href="https://azonow.com/blog/">
                <div class="at-rp-card at-rp-card-wrapper at-rp-card-wrapper-3col" style="border: 2px solid #00B2A9;">
                    <div class="at-rp-card-top">
                        <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-visit-blog.svg" />
                    </div>
                    <div class="at-rp-card-mid">
                        <p style="color:#00B2A9;">Visit our blogs</p>
                    </div>
                </div>
            </a>
            <a target="_blank" href="https://my.azonow.com/support">
                <div class="at-rp-card at-rp-card-wrapper at-rp-card-wrapper-3col" style="border: 2px solid #32a571;">
                    <div class="at-rp-card-top">
                        <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-submit-ticket.svg" />
                    </div>
                    <div class="at-rp-card-mid">
                        <p style="color:#32A571;">Submit a Ticket</p>
                    </div>
                </div>
            </a>
            <a target="_blank" href="https://azonow.com">
                <div class="at-rp-card at-rp-card-wrapper at-rp-card-wrapper-3col" style="border: 2px solid #631D76;">
                    <div class="at-rp-card-top">
                        <img src="<?php echo esc_html(AZOTRACKING_BASE_URL); ?>assets/images/azo-plugin.svg" />
                    </div>
                    <div class="at-rp-card-mid">
                        <p style="color:#631D76;">Our other plugins</p>
                    </div>
                </div>
            </a>

        </div>
    </div>
</div>
<?php include 'footer.php'; ?>