<?php
if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

if (!class_exists('AZO_Tracking_Settings')) {

	class AZO_Tracking_Settings
	{

		/**
		 * @var         AZO_TRACKING_SETTINGS 
		 * @since       1.0.0
		 */
		private static $instance = null;

		/**
		 * settings sections array
		 *
		 * @var array
		 */
		protected $settings_sections = array();

		/**
		 * Settings fields array
		 *
		 * @var array
		 */
		protected $settings_fields = array();

		protected $ga_instance;


		public function __construct()
		{
			if (current_user_can('manage_options') && (!defined('DOING_AJAX') || !DOING_AJAX)) {
				add_action('admin_init', array($this, 'admin_init'));
				add_filter('azo_tracking_profile_fields', array($this, 'azo_tracking_profile_fields'));
				add_action('pre_update_option_azo-tracking-dashboard-page', array($this, 'show_dashboard_roles_maybe_empty'), 10, 2);
			}
			$uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI']));
			if (strpos($uri, 'azo-tracking') !== false) {
				require_once __DIR__ . '/../vendor/autoload.php';
				$this->ga_instance = AZO_Tracking_GA::get_instance();
			}
		}

		/**
		 * Initialize and registers the settings sections and fields to WordPress
		 */
		function admin_init()
		{
			global $pagenow;
			$page = filter_input(INPUT_GET, 'page', FILTER_SANITIZE_FULL_SPECIAL_CHARS);

			if ((('admin.php' === $pagenow) && ('azo-tracking-settings' === $page) || 'options.php' === $pagenow)) {
				$this->set_sections($this->get_settings_sections());
				$this->set_fields($this->get_settings_fields());
				// register settings sections
				// creates our settings in the options table
				foreach ($this->settings_sections as $section) {
					register_setting($section['id'], $section['id'], array($this, 'sanitize_options'));
				}

				$this->register_settings();
				$this->add_default_settings_option();
			}
		}

		/**
		 * Set settings sections
		 *
		 * @param array $sections setting sections array
		 */
		function set_sections($sections)
		{

			$this->settings_sections = $sections;

			return $this;
		}

		/**
		 * Add a single section
		 *
		 * @param array $section
		 */
		function add_section($section)
		{
			$this->settings_sections[] = $section;

			return $this;
		}

		/**
		 * get an array section
		 *
		 * @return array $section
		 */

		function get_settings_sections()
		{

			$tabs = array(
				array(
					'id' => 'azo-tracking-authentication',
					'title' => __('Authentication', 'azo-tracking'),
					'priority' => '10',
				),
				array(
					'id' => 'azo-tracking-profile',
					'title' => __('Profile', 'azo-tracking'),
					'desc' => 'Select the profile for tracking.',
					'priority' => '20',
				),
				array(
					'id' => 'azo-tracking-configuration',
					'title' => __('Configuration', 'azo-tracking'),
					'desc' => 'Configure the following settings for tracking.',
					'priority' => '30',
				),
				array(
					'id' => 'azo-tracking-dashboard-page',
					'title' => __('Dashboard Page', 'azo-tracking'),
					'desc' => 'Select roles and stats for admin dashboard',
					'priority' => '40',
				),
			);

			$setting_tabs = apply_filters('azo_tracking_setting_tabs', $tabs);

			usort(
				$setting_tabs,
				function ($a, $b) {
					return $a['priority'] - $b['priority'];
				}
			);

			return $setting_tabs;
		}

		/**
		 * Set settings fields
		 *
		 * @param array $fields settings fields array
		 */
		function set_fields($fields)
		{
			$this->settings_fields = $fields;

			return $this;
		}

		/**
		 * [add_field description]
		 *
		 * @param [type] $section [description]
		 * @param [type] $field   [description]
		 */
		function add_field($section, $field)
		{
			$defaults = array(
				'name' => '',
				'label' => '',
				'desc' => '',
				'type' => 'text',
			);

			$arg = wp_parse_args($field, $defaults);
			$this->settings_fields[$section][] = $arg;

			return $this;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		public function get_settings_fields()
		{

			$settings_fields = array(
				'azo-tracking-authentication' => array(),
				'azo-tracking-profile' => apply_filters(
					'azo_tracking_profile_fields',
					array(
						array(
							'name' => 'manual_ga_code',
							'label' => __('GA Tracking ID', 'azo-tracking'),
							'desc' => wp_sprintf('<p class="description"><code>%s</code> </p>', 'G-XXXXXXXXXX'),
							'type' => 'text',
							'default' => '',
						),
						array(
							'name' => 'add_ga_script',
							'label' => __('Install GA code', 'azo-tracking'),
							'tooltip' => __('Add GA tracking code to your website, turn off this if you only want show the dashboard of analytics', 'azo-tracking'),
							'type' => 'checkbox',
							'default' => '',
							'default' => 'on'
						),
						array(
							'name' => 'dashboard_profile',
							'label' => __('Profile for dashboard', 'azo-tracking'),
							'desc' => __('Select the profile for get the data stream options', 'azo-tracking'),
							'type' => 'group_select',
							'default' => '',
							'options' => $this->get_ga_properties()
						),
						array(
							'name' => 'dashboard_data_stream',
							'label' => __('Data stream for dashboard', 'azo-tracking'),
							'desc' => __('Select GA4 data stream to use for website tracking.', 'azo-tracking'),
							'type' => 'select',
							'default' => '',
							'options' => $this->get_all_streams()
						),
					)
				),
				'azo-tracking-configuration' => array(
					array(
						'name' => 'exclude_users_roles_tracking',
						'label' => __('Exclude users from tracking', 'azo-tracking'),
						'desc' => __("Don't insert the tracking code for the above user roles.", 'azo-tracking'),
						'type' => 'multi_select',
						'default' => array('administrator'),
						'options' => $this->get_current_roles()
					),
					array(
						'name' => 'locally_host_analytics',
						'label' => __('Using gtag.js locally', 'azo-tracking'),
						'tooltip' => __('Using gtag.js locally may improve your site speed and other core web vitals.', 'azo-tracking'),
						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'track_user_id',
						'label' => __('User Tracking', 'azo-tracking'),
						'desc' => sprintf(
							// translators: %1$s and %2$s are HTML anchor tags to create a link.
							__('Detailed information about Track User ID in Google Analytics can be found %1$shere%2$s.', 'azo-tracking'),
							'<a href="https://support.google.com/analytics/answer/9213390?hl=en" target="_blank">',
							'</a>'
						),

						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'scroll_depth',
						'label' => __('Scroll Depth', 'azo-tracking'),
						'tooltip' => __('Track page scroll depth percentage at the 25%, 50%, 75%, and 100% scrolling points. This will help you figure out the most highlighted area of the page.', 'azo-tracking'),
						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'demographic_tracking',
						'label' => __('Demographic Tracking', 'azo-tracking'),
						'tooltip' => __('This allows you to view extra dimensions about age range, gender and interests, etc.', 'azo-tracking'),
						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => '404_page_track',
						'label' => __('Page Not Found (404)', 'azo-tracking'),
						'tooltip' => __('Track all 404 pages.', 'azo-tracking'),
						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'js_error_track',
						'label' => __('Javascript error tracking', 'azo-tracking'),
						'tooltip' => __('Track all javascript error ', 'azo-tracking'),
						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'ajax_error_track',
						'label' => __('Ajax error tracking', 'azo-tracking'),
						'tooltip' => __('Track all ajax tracking.', 'azo-tracking'),
						'type' => 'checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'linker_cross_domain_tracking',
						'label' => __('Setup Cross-domain Tracking', 'azo-tracking'),
						'desc' => sprintf(
							// translators: %1$s is the code snippet <code>allowLinker:true</code>, %2$s and %3$s are HTML anchor tags to create a link.
							__('This will add the %1$s tag to your tracking code. Read this %2$sguide%3$s for more information.', 'azo-tracking'),
							'<code>allowLinker:true</code>',
							'<a href="https://support.google.com/analytics/answer/10071811?hl=en" target="_blank">',
							'</a>'
						),

						'type' => 'checkbox',
						'class' => 'cross-domain-tracking-checkbox',
						'default' => 'off'
					),
					array(
						'name' => 'linked_domain',
						'label' => __('Domains for tracking', 'azo-tracking'),
						'desc' => __('All the linked domains separated by a comma', 'azo-tracking'),
						'type' => 'text',
						'class' => 'linker-tracking-list',
						'default' => '',
						'sanitize_callback' => 'trim'
					),
				),
				'azo-tracking-dashboard-page' => array(
					array(
						'name' => 'show_dashboard_for_roles',
						'label' => __('Show dashboard for role(s)', 'azo-tracking'),
						'desc' => __('Show dashboard for above-selected user roles only. At least 1 role to show.', 'azo-tracking'),
						'type' => 'multi_select',
						'default' => array('administrator'),
						'options' => $this->get_current_roles()
					),
					array(
						'name' => 'show_dashboard_stats',
						'label' => __('Show dashboard stats', 'azo-tracking'),
						'desc' => __('Show selected stats in dashboard. At least 1 stats to show.', 'azo-tracking'),
						'type' => 'multi_select',
						'default' => array(
							'general_stats',
							'daily_visitors_stats',
							'visitor_devices_stats',
							'new_vs_returning_visitors_stats',
							'operating_systems_stats',
							'referer_stats',
							'browser_stats',
							'top_pages_stats',
							'what_is_happening_stats',
							'geographic_stats',
						),
						'options' => array(
							'general_stats' => __('General Stats', 'azo-tracking'),
							'daily_visitors_stats' => __('Daily Visitor Stats', 'azo-tracking'),
							'visitor_devices_stats' => __('Visitor Devices Stats', 'azo-tracking'),
							'new_vs_returning_visitors_stats' => __('New vs Returning Visitors Stats', 'azo-tracking'),
							'operating_systems_stats' => __('Operating Systems Stats', 'azo-tracking'),
							'referer_stats' => __('Referer Stats', 'azo-tracking'),
							'browser_stats' => __('Browser Stats', 'azo-tracking'),
							'top_pages_stats' => __('Top Pages Stats', 'azo-tracking'),
							'what_is_happening_stats' => __("What's Happening Stats", 'azo-tracking'),
							'geographic_stats' => __('Geographic Stats', 'azo-tracking'),
						)
					),
				)
			);

			$settings_fields = apply_filters('azo_tracking_setting_fields', $settings_fields);

			return $settings_fields;
		}

		function register_settings()
		{

			foreach ($this->settings_sections as $section) {

				if (false == get_option($section['id'])) {
					add_option($section['id']);
				}

				if (isset($section['callback'])) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				add_settings_section($section['id'], '', $callback, $section['id']);
			}

			// register settings fields
			foreach ($this->settings_fields as $section => $field) {
				foreach ($field as $option) {

					$type = isset($option['type']) ? $option['type'] : 'text';

					$args = array(
						'id' => $option['name'],
						'label_for' => $args['label_for'] = "{$section}[{$option['name']}]",
						'desc' => isset($option['desc']) ? $option['desc'] : '',
						'name' => $option['label'],
						'section' => $section,
						'size' => isset($option['size']) ? $option['size'] : null,
						'options' => isset($option['options']) ? $option['options'] : '',
						'std' => isset($option['default']) ? $option['default'] : '',
						'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
						'class' => isset($option['class']) ? $option['class'] : '',
						'type' => $type,
						'tooltip' => isset($option['tooltip']) ? $option['tooltip'] : false,
					);

					add_settings_field($section . '[' . $option['name'] . ']', $option['label'], array($this, 'callback_' . $type), $section, $section, $args);
				}
			}
		}

		function add_default_settings_option()
		{
			foreach ($this->settings_fields as $section => $field) {
				if (!get_option($section)) {
					$default_value = array();
					foreach ($field as $option) {
						$default_value[$option['name']] = $option['default'];
					}
					update_option($section, $default_value);
				}
			}
		}

		/**
		 * [show_tabs description]
		 *
		 * @return [type] [description]
		 */
		function show_tabs()
		{
			$html = '<ul class="azo-tracking-menu">';
			foreach ($this->settings_sections as $tab) {
				$html .= sprintf(
					'<li><a href="#%1$s" class="azo_tracking_nav_tab" data-id="%1$s" id="%1$s-tab">%2$s</a></li>',
					esc_attr($tab['id']),
					esc_html($tab['title'])
				);
			}
			$html .= '</ul>';
			$allowed_html = array(
				'ul' => array('class' => array()),
				'li' => array(),
				'a' => array(
					'href' => array(),
					'class' => array(),
					'data-id' => array(),
					'id' => array(),
				),
			);
			echo wp_kses($html, $allowed_html);
		}


		/**
		 * get current list of all roles and display in dropdown
		 *
		 * @return array
		 */
		public static function get_current_roles()
		{

			$roles = array();

			if (get_editable_roles() > 0) {

				foreach (get_editable_roles() as $role => $name) {

					$roles[$role] = $name['name'];
				}
			} else {
				$roles['empty'] = 'Not found';
			}

			return $roles;
		}

		function show_form_by_sections_id($section_id)
		{
			global $wp_settings_sections, $wp_settings_fields;
			if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$section_id])) {
				return;
			}
?>
			<form method="post" action="options.php" class="azo-tracking-form">
				<?php
				settings_fields($section_id);
				foreach ((array) $wp_settings_sections[$section_id] as $field) {
					if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$section_id]) || !isset($wp_settings_fields[$section_id][$field['id']])) {
						continue;
					}
					$this->do_settings_fields($section_id, $field['id']);
				}
				?>
				<div class="azo-submit-btn">
					<?php submit_button(); ?>
				</div>
			</form>
<?php
		}

		function do_settings_fields($section_id, $field)
		{
			global $wp_settings_fields;

			if (!isset($wp_settings_fields[$section_id][$field])) {
				return;
			}

			foreach ((array) $wp_settings_fields[$section_id][$field] as $field) {
				if (!empty($field['args']['class'])) {
					echo '<div class="azo-settings-row ' . esc_attr($field['args']['class']) . '">';
				} else {
					echo '<div class="azo-settings-row">';
				}

				if (!empty($field['args']['label_for'])) {
					echo '<label class="azo-settings-row-item" for="' . esc_attr($field['args']['label_for']) . '">' . esc_html($field['title']) . '</label>';
				} else {
					echo '<span scope="row">' . esc_html($field['title']) . '</span>';
				}

				echo '<div class="azo-settings-row-item">';
				call_user_func($field['callback'], $field['args']);
				echo '</div>';
				echo '</div>';
			}
		}


		/**
		 * Prints out all settings sections added to a particular settings page
		 *
		 * @since 1.0.0
		 */
		function do_settings_sections($page)
		{
			global $wp_settings_sections, $wp_settings_fields;

			if (!isset($wp_settings_sections) || !isset($wp_settings_sections[$page])) {
				return;
			}

			foreach ((array) $wp_settings_sections[$page] as $section) {

				echo '<h3>' . esc_html($section['title']) . '</h3>' . "\n";

				if (!empty($section['callback'])) {
					call_user_func($section['callback']);
				}

				if (!isset($wp_settings_fields) || !isset($wp_settings_fields[$page]) || !isset($wp_settings_fields[$page][$section['id']])) {
					continue;
				}

				echo '<table class="form-table">';
				do_settings_fields($page, $section['id']);
				echo '</table>';
			}
		}

		/**
		 * Sanitize callback for Settings API
		 */
		function sanitize_options($options)
		{

			if (!$options) {
				return;
			}

			foreach ($options as $option_slug => $option_value) {
				$sanitize_callback = $this->get_sanitize_callback($option_slug);

				// If callback is set, call it
				if ($sanitize_callback) {
					$options[$option_slug] = call_user_func($sanitize_callback, $option_value);
					continue;
				}
			}

			return $options;
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug
		 *
		 * @return mixed string or bool false
		 */
		function get_sanitize_callback($slug = '')
		{
			if (empty($slug)) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback
			foreach ($this->settings_fields as $section => $options) {
				foreach ($options as $option) {
					if ($option['name'] != $slug) {
						continue;
					}

					// Return the callback name
					return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_checkbox($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

			$is_disabled = (isset($args['options']['disabled']) and $args['options']['disabled']) ? 'disabled' : '';
			$default_value = ('disabled' === $is_disabled and 'on' === $value) ? 'on' : 'off';

			$checkbox_value = (isset($args['options']['disabled']) and $args['options']['disabled']) ? 'off' : $value;

			$checkbox_name_override = (isset($args['options']['disabled']) and $args['options']['disabled']) ? '__' : '';

			$checked = checked($checkbox_value, 'on', false);

			$html = sprintf(
				'<input type="hidden" name="%1$s[%2$s]" value="%3$s" />',
				esc_attr($args['section']),
				esc_attr($args['id']),
				esc_attr($default_value)
			);

			$html .= sprintf(
				'<input type="checkbox" class="form-check-box" id="%1$s%2$s[%3$s]" name="%1$s%2$s[%3$s]" value="on" %4$s %5$s />',
				esc_attr($checkbox_name_override),
				esc_attr($args['section']),
				esc_attr($args['id']),
				esc_attr($checked),
				esc_attr($is_disabled)
			);

			if ($args['tooltip']) {
				$html .= sprintf(
					'<div class="at-icon-description-container">
            <span class="info-link">
                <img class="info-icon" data-description="%1$s" src="%2$sassets/images/azo-info.svg" />
            </span>
            <div class="at-description-tooltip" style="display: none;"></div>
        </div>',
					esc_attr($args['tooltip']),
					esc_url(AZOTRACKING_BASE_URL)
				);
			} else {
				$html .= $this->get_field_description($args);
			}

			$allowed_html = array(
				'input' => array(
					'type' => array(),
					'name' => array(),
					'value' => array(),
					'class' => array(),
					'id' => array(),
					'checked' => array(),
					'disabled' => array(),
				),
				'div' => array(
					'class' => array(),
					'style' => array(),
				),
				'span' => array(
					'class' => array(),
				),
				'img' => array(
					'class' => array(),
					'data-description' => array(),
					'src' => array(),
				),
				'p' => array(
					'class' => array()
				),
				'a' => array(
					'href' => array(),
					'target' => array()
				)
			);

			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_text($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$type = isset($args['type']) ? $args['type'] : 'text';

			$html = sprintf(
				'<input type="%1$s" class="azo-tracking-form-control" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"/>',
				esc_attr($type),
				esc_attr($size),
				esc_attr($args['section']),
				esc_attr($args['id']),
				esc_attr($value)
			);

			$html .= $this->get_field_description($args);

			$allowed_html = array(
				'input' => array(
					'type' => array(),
					'class' => array(),
					'id' => array(),
					'name' => array(),
					'value' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			echo wp_kses($html, $allowed_html);
		}


		/**
		 * Displays a chosen selectbox for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_multi_select($args)
		{
			$value = $this->get_option($args['id'], $args['section'], array());
			$size = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html = sprintf(
				'<select multiple class="%1$s custom-select multi-select" name="%2$s[%3$s][]" id="%2$s[%3$s]">',
				esc_attr($size),
				esc_attr($args['section']),
				esc_attr($args['id'])
			);
			$html .= '<option class="hide" value="">Select options</option>';

			foreach ($args['options'] as $key => $label) {
				$selected = in_array($key, $value) ? 'selected="selected"' : '';
				$html .= sprintf(
					'<option value="%1$s" %2$s>%3$s</option>',
					esc_attr($key),
					esc_attr($selected),
					esc_html($label)
				);
			}

			$html .= '</select>';

			$html .= $this->get_field_description($args);

			$allowed_html = array(
				'select' => array(
					'multiple' => array(),
					'class' => array(),
					'name' => array(),
					'id' => array(),
				),
				'option' => array(
					'class' => array(),
					'value' => array(),
					'selected' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a select box for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_select($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

			$html = sprintf(
				'<select class="custom-select single-select" name="%1$s[%2$s]" id="%1$s[%2$s]">',
				esc_attr($args['section']),
				esc_attr($args['id'])
			);

			$html .= '<option class="data-stream-option hide" value="">No data stream</option>';

			foreach ($args['options'] as $property_id => $streams) {
				foreach ($streams as $measurement_id => $stream_data) {
					$selected = selected($value, $measurement_id, false);
					$html .= sprintf(
						'<option class="data-stream-option hide" data-property="%1$s" value="%2$s"%3$s>%4$s</option>',
						esc_attr($property_id),
						esc_attr($measurement_id),
						esc_attr($selected),
						esc_html($stream_data['stream_name'])
					);
				}
			}

			$html .= '</select>';

			$html .= $this->get_field_description($args);

			$allowed_html = array(
				'select' => array(
					'class' => array(),
					'name' => array(),
					'id' => array(),
				),
				'option' => array(
					'class' => array(),
					'value' => array(),
					'selected' => array(),
					'data-property' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			echo wp_kses($html, $allowed_html);
		}

		/**
		 * Displays a multi select box for a settings field
		 *
		 * @param array $args settings field args
		 */
		function callback_group_select($args)
		{
			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

			$html = sprintf(
				'<select class="custom-select grouped-select" name="%1$s[%2$s]" id="grouped_profile">',
				esc_attr($args['section']),
				esc_attr($args['id'])
			);

			$html .= '<option value="">Choose profile for dashboard</option>';

			foreach ($args['options'] as $group => $profiles) {
				$html .= '<optgroup label="' . esc_attr($group) . '">';
				foreach ($profiles as $profile_id => $profile_data) {
					$selected = $profile_id == $value ? ' selected="selected"' : '';
					$html .= sprintf(
						'<option value="%1$s"%2$s>%3$s</option>',
						esc_attr($profile_id),
						esc_attr($selected),
						esc_html($profile_data['name'])
					);
				}
				$html .= '</optgroup>';
			}

			$html .= '</select>';

			$html .= $this->get_field_description($args);

			$allowed_html = array(
				'select' => array(
					'class' => array(),
					'name' => array(),
					'id' => array(),
				),
				'option' => array(
					'value' => array(),
					'selected' => array(),
				),
				'optgroup' => array(
					'label' => array(),
				),
				'p' => array(
					'class' => array()
				)
			);

			echo wp_kses($html, $allowed_html);
		}



		/**
		 * Return a value of 
		 *
		 * @param array $args settings field args
		 */
		function get_option($option, $section, $default = '')
		{

			$options = get_option($section);

			if (isset($options[$option])) {
				return $options[$option];
			}
			return $default;
		}

		/**
		 * Get field description for display
		 *
		 * @param array $args settings field args
		 */
		public function get_field_description($args)
		{
			if (!empty($args['desc'])) {
				$desc = sprintf('<p class="azo-settings-description">%s</p>', $args['desc']);
			} else {
				$desc = '';
			}

			return $desc;
		}

		function get_all_streams()
		{

			$ga_streams = $this->ga_instance->fetch_all_ga_streams();
			if (!$ga_streams)
				return array();
			return $ga_streams;
		}

		/**
		 * Get field description for display
		 *
		 * @return array properties
		 */
		function get_ga_properties()
		{
			require_once __DIR__ . '/../vendor/autoload.php';
			$this->ga_instance = AZO_Tracking_GA::get_instance();
			$ga_properties = $this->ga_instance->fetch_ga_properties();
			if (!$ga_properties)
				return array();
			return $ga_properties;
		}

		/**
		 * Filter the fields of profiles
		 *
		 * @return array fields
		 */
		function azo_tracking_profile_fields($fields)
		{
			if (!get_option('azo_google_token')) {
				$fields = array_filter($fields, function ($field) {
					return in_array($field['name'], array('manual_ga_code'));
				});
			} else {
				$fields = array_filter($fields, function ($field) {
					return !in_array($field['name'], array('manual_ga_code'));
				});
			}
			return $fields;
		}

		public function get_ga_code()
		{
			if (get_option('azo_google_token')) {
				$ga_code = $this->get_option('dashboard_data_stream', 'azo-tracking-profile');
			} else {
				$ga_code = $this->get_option('manual_ga_code', 'azo-tracking-profile');
			}
			return $ga_code;
		}

		public function get_all_linked_domain()
		{
			$all_linked_domains = $this->get_option('linked_domain', 'azo-tracking-configuration');
			$all_linked_domains = trim($all_linked_domains);

			if (!empty($all_linked_domains)) {
				$all_linked_domains = str_replace("'", '', $all_linked_domains);
				$all_linked_domains = str_replace('"', '', $all_linked_domains);

				$all_linked_domains = preg_replace('/\s+/', '', $all_linked_domains);

				$list_linked_domains = explode(',', $all_linked_domains);
				$number_of_linked_domains = count($list_linked_domains);

				if ($number_of_linked_domains > 0) {
					$linked_domains = array_filter((array) $list_linked_domains, 'strlen');
				} else {
					$linked_domains = (array) $all_linked_domains;
				}
			} else {
				$linked_domains = '';
			}

			return $linked_domains;
		}

		function show_dashboard_roles_maybe_empty($new_value, $old_value)
		{
			if (!isset($new_value['show_dashboard_for_roles']) || empty($new_value['show_dashboard_for_roles'])) {
				$new_value['show_dashboard_for_roles'] = array('administrator');
			}
			if (!isset($new_value['show_dashboard_stats']) || empty($new_value['show_dashboard_stats'])) {
				$new_value['show_dashboard_stats'] = array('general_stats');
			}
			return $new_value;
		}

		public static function get_instance()
		{
			if (!self::$instance) {
				self::$instance = new AZO_Tracking_Settings();
			}
			return self::$instance;
		}
	}
}


function azo_tracking_settings_instance()
{
	$GLOBALS['AZO_TRACKING_SETTINGS'] = AZO_Tracking_Settings::get_instance();
}
add_action('plugins_loaded', 'azo_tracking_settings_instance', 20);
