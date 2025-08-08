<?php

if (! defined('ABSPATH')) {
	exit; // Exit if accessed directly
}

use Elementor\Controls_Manager;

/**
 * Cool Marketing Controllers
 *
 * Handles marketing notices and AJAX actions for Elementor forms.
 *
 * @package Timeline Widget Addon for Elementor
 */

if (! class_exists('Twae_Marketing_Controllers')) {

	class Twae_Marketing_Controllers
	{
		private static $instance = null;
		
		/**
		 * ✅ Singleton instance
		 */
		public static function get_instance()
		{

			if (self::$instance === null) {

				self::$instance = new self();
			}

			return self::$instance;
		}
		
		/**
		 * ✅ Constructor
		 *
		 * Initializes hooks and actions.
		 */
		public function __construct() {

			add_action('admin_notices', array($this, 'twae_show_tec_active_notice'));
			
			$active_plugins = get_option( 'active_plugins', [] );

			if ( in_array( 'elementor-pro/elementor-pro.php', $active_plugins ) ) {

				add_action('elementor/init', [$this, 'twae_init_hooks']);
				
				if (class_exists('acf_pro') && !in_array('loop-grid-extender-for-elementor-pro/loop-grid-extender-for-elementor-pro.php', $active_plugins, true)) {
                    add_action('elementor/element/loop-grid/section_query/before_section_end', [$this, 'twae_add_acf_repeater_mkt_query_controls']);
                }

				$required_plugins = [
					'extensions-for-elementor-form/extensions-for-elementor-form.php',
					'country-code-field-for-elementor-form/country-code-field-for-elementor-form.php',
					'conditional-fields-for-elementor-form/class-conditional-fields-for-elementor-form.php',
					'cool-formkit-for-elementor-forms/cool-formkit-for-elementor-forms.php',
					'conditional-fields-for-elementor-form-pro/class-conditional-fields-for-elementor-form-pro.php',
					'form-masks-for-elementor/form-masks-for-elementor.php',
					'mask-form-elementor/index.php'
				];

				if (empty(array_intersect($required_plugins, $active_plugins))) {

					add_action('elementor/element/form/section_form_fields/before_section_end', [$this, 'twae_marketing_controls'], 100, 2);
				}
				if(!in_array('loop-grid-extender-for-elementor-pro/loop-grid-extender-for-elementor-pro.php', $active_plugins, true)){
                    add_action("elementor/element/taxonomy-filter/section_taxonomy_filter/before_section_end", [$this, 'twae_register_controls'], 10);
                }
			}
			
			add_action('wp_ajax_twae_install_plugin', [$this, 'twae_install_plugin']);

          	add_action('wp_ajax_twae_mkt_dismiss_notice', [$this,'twae_dismiss_notice_callback']);
			 
		}
		/**
		 * ✅ AJAX: Dismiss notice callback
		 *
		 * Handles the dismissal of marketing notices via AJAX.
		 */

		function twae_dismiss_notice_callback() {

			if ( ! current_user_can( 'manage_options' ) ) {
                 wp_send_json_error([ 'message' => 'Permission denied' ]);
			}

			$type  = sanitize_text_field($_POST['notice_type'] ?? '');
           $nonce = isset($_POST['nonce']) ? $_POST['nonce'] : '';
			if ($type === 'cool_form') {

				if (! wp_verify_nonce($nonce, 'twae_dismiss_nonce_cool_form')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
				}

				update_option('twae_marketing_dismissed', true);
				wp_send_json_success();

			} elseif ($type === 'tec_notice') {

				if (! wp_verify_nonce($nonce, 'twae_dismiss_nonce_tec')) {
					wp_send_json_error(['message' => 'Invalid nonce']);
				}

				update_option('twae_tec_notice_dismissed', true);
				wp_send_json_success();
			}

			wp_send_json_error(['message' => 'Unknown notice type']);
		}

			
		public function twae_register_controls($element) {
			
			$element->add_control(
					'lgefep_taxonomy_dropdown',
					[
						'label' => __('Enable Smart Filters', 'loop-grid-extender-for-elementor-pro'),
						'type' => \Elementor\Controls_Manager::SWITCHER,
						'default' => 'no',
						'label_on' => __('Yes', 'loop-grid-extender-for-elementor-pro'),
						'label_off' => __('No', 'loop-grid-extender-for-elementor-pro'),
						'return_value' => 'yes',
						'condition' => [
							'selected_element!' => '',
						],
					]
				);

			$element->add_control(

					'lgefep_acf_mkt_repeater_tag',
					[
						'name'      => 'lgefep_acf_mkt_repeater_tag',
						'label'     => '',
						'type'      => \Elementor\Controls_Manager::RAW_HTML,
						'raw'       => '<div class="elementor-control-raw-html cool-form-wrp"><div class="elementor-control-notice elementor-control-notice-type-info">
										<div class="elementor-control-notice-icon">
										<img class="twae-highlight-icon" src="'.esc_url( TWAE_URL . 'admin/assets/images/twae-highlight-icon.svg' ).'" width="250" alt="Highlight Icon" />
										</div>
										<div class="elementor-control-notice-main">
										<div class="elementor-control-notice-main-content">Enable smart taxonomy filters for your Elementor loop grid.</div>
											<div class="elementor-control-notice-main-actions">
											<button type="button" class="elementor-button e-btn e-info e-btn-1 twae-install-plugin"  data-plugin="loop-grid" data-nonce="' . esc_attr(wp_create_nonce('twae_install_nonce')) . '">Install Loop Grid Extender</button></button>
										</div></div>
										</div></div>',

						'condition'       => array(
							'lgefep_taxonomy_dropdown' => 'yes'
						),

					]
				);
		}
		
		/**
		 * ✅ Show TEC active notice
		 *
		 * Displays a notice to install the Events Widgets for Elementor plugin if TEC is active.
		 */

		function twae_show_tec_active_notice(){
			
			$active_plugins = get_option( 'active_plugins', [] );
			if (
				!class_exists('Tribe__Events__Main') 
				|| in_array('events-widgets-pro/events-widgets-pro.php', $active_plugins, true) 
				|| in_array('events-widgets-for-elementor-and-the-events-calendar/events-widgets-for-elementor-and-the-events-calendar.php', $active_plugins, true)
				|| get_option('twae_tec_notice_dismissed')
			) {
				return;
			}

			wp_enqueue_script(
					'coolplugin-editor-js',
					plugin_dir_url(__FILE__) . 'assets/js/twae-form-marketing.js',
					['jquery'],
					TWAE_VERSION,
					true
			);

			// Check if it's tribe_events post type or tec settings page
			$is_tribe_post = isset($_GET['post_type']) && sanitize_key( $_GET['post_type'] ) === 'tribe_events';
            $is_tec_settings = isset($_GET['page']) && sanitize_key( $_GET['page'] ) === 'tec-events-settings';

			if ($is_tribe_post || $is_tec_settings) {

				?>
		
				<div class="notice notice-info is-dismissible twae-tec-notice"
                     data-notice="tec_notice"
                     data-nonce="<?php echo esc_attr( wp_create_nonce( 'twae_dismiss_nonce_tec' ) ); ?>">
     
                    <p class="ect-notice-widget">
                       <button class="button button-primary twae-install-plugin"
                               data-plugin="events-widget"
                               data-notice="tec_notice"
                               data-nonce="<?php echo esc_attr( wp_create_nonce( 'twae_install_nonce' ) ); ?>">
                               Install Events Widgets for Elementor
                      </button>
                        Easily display The Events Calendar events on your Elementor pages.
                    </p>
                </div>

				<?php

			}
		}

		/**
		 * Initialize hooks
		 * Registers the necessary hooks for marketing notices and AJAX actions.
		 */

		public function twae_init_hooks() {

			add_action('elementor/editor/after_enqueue_scripts', [$this, 'enqueue_editor_scripts']);
			add_action('elementor/editor/after_enqueue_styles', [$this, 'enqueue_editor_styles']);
		}

		/**
		 * Adds ACF Repeater marketing notice to Loop Grid Query controls
		 * 
		 * @param \Elementor\Widget_Base $element
		 */

		public function twae_add_acf_repeater_mkt_query_controls($element) {

			$element->add_control(

				'lgefep_mkt_country_notice',
					array(
						'name'            => 'ctwae_mkt_country_notice',
						'type'            => \Elementor\Controls_Manager::SWITCHER,
						'label'        => esc_html__('Use ACF Repeater', 'country-code-for-elementor-form-telephone-field'),
						'type'         => \Elementor\Controls_Manager::SWITCHER,
						'label_on'     => esc_html__('Yes', 'country-code-for-elementor-form-telephone-field'),
						'label_off'    => esc_html__('No', 'country-code-for-elementor-form-telephone-field'),

					),
			);

			$element->add_control(

				'lgefep_acf_mkt_repeater_tag',
					[
						'name'      => 'lgefep_acf_mkt_repeater_tag',
						'label'     => '',
						'type'      => \Elementor\Controls_Manager::RAW_HTML,
							'raw'       => '<div class="elementor-control-raw-html cool-form-wrp"><div class="elementor-control-notice elementor-control-notice-type-info">
											<div class="elementor-control-notice-icon"><img class="twae-highlight-icon" src="'.esc_url( TWAE_URL . 'admin/assets/images/twae-highlight-icon.svg' ).'" width="250" alt="Highlight Icon" />
											</div>
											<div class="elementor-control-notice-main">
											<div class="elementor-control-notice-main-content">Display ACF Repeater fields in your Elementor loop grid.</div>
											<div class="elementor-control-notice-main-actions">
											<button type="button" class="elementor-button e-btn e-info e-btn-1 twae-install-plugin"  data-plugin="loop-grid" data-nonce="' . esc_attr(wp_create_nonce('twae_install_nonce')) . '">Install Loop Grid Extender</button></button>
											</div></div></div></div>',
							'condition'       => array(
								'lgefep_mkt_country_notice' => 'yes'
							),
					]

			);
			
		}

		/**
		 * ✅ Enqueue editor scripts
		 */

		public function enqueue_editor_scripts(){

			wp_enqueue_script(
				'coolplugin-editor-js',
				plugin_dir_url(__FILE__) . 'assets/js/twae-form-marketing.js',
				['jquery'],
				TWAE_VERSION,
				true
			);
		}

		/**
		 * ✅ Enqueue editor styles
		 */

		public function enqueue_editor_styles(){

			wp_enqueue_style(
				'coolplugin-editor-css',
				plugin_dir_url(__FILE__) . 'assets/css/twae-mkt.css',
				[],
				TWAE_VERSION
			);
		}

		/**
		 * ✅ AJAX: Install plugin
		 * 
		 * Handles the installation of a specified plugin via AJAX.
		 */
		public function twae_install_plugin() {


             if ( ! current_user_can( 'install_plugins' ) ) {
				$status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.' );
				wp_send_json_error( $status );
			}

			check_ajax_referer('twae_install_nonce');

			if ( empty( $_POST['slug'] ) ) {
				wp_send_json_error( array(
					'slug'         => '',
					'errorCode'    => 'no_plugin_specified',
					'errorMessage' => __( 'No plugin specified.' ),
				));
			}
     	
		    $plugin_slug = sanitize_key( wp_unslash( $_POST['slug'] ) );


			$status = array(
				'install' => 'plugin',
				'slug'    => sanitize_key( wp_unslash( $_POST['slug'] ) ),
			);
			
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			
			$api = plugins_api( 'plugin_information', array(
				'slug'   => $plugin_slug,
				'fields' => array(
					'sections' => false,
				),
			));

			if ( is_wp_error( $api ) ) {
				$status['errorMessage'] = $api->get_error_message();
				wp_send_json_error( $status );
			}

			$status['pluginName'] = $api->name;
			
			$skin     = new WP_Ajax_Upgrader_Skin();
			$upgrader = new Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );
			
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$status['debug'] = $skin->get_upgrade_messages();
			}

			if ( is_wp_error( $result ) ) {

				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();
				wp_send_json_error( $status );

			} elseif ( is_wp_error( $skin->result ) ) {
				
				if($skin->result->get_error_message() === 'Destination folder already exists.'){
						
					$install_status = install_plugin_install_status( $api );
					$pagenow        = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';

					if ( current_user_can( 'activate_plugin', $install_status['file'] )) {

						$network_wide = ( is_multisite() && 'import' !== $pagenow );
						$activation_result = activate_plugin( $install_status['file'], '', $network_wide );
						if ( is_wp_error( $activation_result ) ) {
							
							$status['errorCode']    = $activation_result->get_error_code();
							$status['errorMessage'] = $activation_result->get_error_message();
							wp_send_json_error( $status );

						} else {

							$status['activated'] = true;
							
						}
						wp_send_json_success( $status );
					}
				}else{
				
					$status['errorCode']    = $skin->result->get_error_code();
					$status['errorMessage'] = $skin->result->get_error_message();
					wp_send_json_error( $status );
				}
				
			} elseif ( $skin->get_errors()->has_errors() ) {

				$status['errorMessage'] = $skin->get_error_messages();
				wp_send_json_error( $status );

			} elseif ( is_null( $result ) ) {

				global $wp_filesystem;

				$status['errorCode']    = 'unable_to_connect_to_filesystem';
				$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.' );

				if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->has_errors() ) {
					$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
				}

				wp_send_json_error( $status );
			}

			$install_status = install_plugin_install_status( $api );
			$pagenow        = isset( $_POST['pagenow'] ) ? sanitize_key( $_POST['pagenow'] ) : '';

			// 🔄 Auto-activate the plugin right after successful install
			if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {

				$network_wide = ( is_multisite() && 'import' !== $pagenow );
				$activation_result = activate_plugin( $install_status['file'], '', $network_wide );

				if ( is_wp_error( $activation_result ) ) {
					$status['errorCode']    = $activation_result->get_error_code();
					$status['errorMessage'] = $activation_result->get_error_message();
					wp_send_json_error( $status );
				} else {
					$status['activated'] = true;
				}
			}
			wp_send_json_success( $status );
		}


		/**
		 * ✅ Elementor: Adds marketing notice & AJAX install button
		 */
		public function twae_marketing_controls($widget) {

			$elementor = \Elementor\Plugin::instance();

			$control_data = $elementor->controls_manager->get_control_from_stack($widget->get_unique_name(), 'form_fields');
			
			if (is_wp_error($control_data)) {
				return;
			}

			$dismissed = get_option('twae_marketing_dismissed', false);

			if (! $dismissed) {

					$widget->add_control(
						'twae_marketing_box',
						[
							'name'      => 'twae_marketing_box',
							'label'     => '',
							'type'      => \Elementor\Controls_Manager::RAW_HTML,
							'raw'       => '<div class="elementor-control-raw-html cool-form-wrp"><div class="elementor-control-notice elementor-control-notice-type-info">
											<div class="elementor-control-notice-icon"><img class="twae-highlight-icon" src="'.esc_url( TWAE_URL . 'admin/assets/images/twae-highlight-icon.svg' ).'" width="250" alt="Highlight Icon" /></div>
											<div class="elementor-control-notice-main">
												
												<div class="elementor-control-notice-main-content">Add advanced fields & features to your Elementor forms.</div>
												<div class="elementor-control-notice-main-actions">
												<button type="button" class="elementor-button e-btn e-info e-btn-1 twae-install-plugin" data-plugin="cool-form-lite" data-nonce="' . esc_attr(wp_create_nonce('twae_install_nonce')) . '">Install Cool FormKit</button></button>
											</div></div>
											<button class="elementor-control-notice-dismiss tooltip-target twae-dismiss-cross twae-dismiss-notice" data-notice="cool_form" data-nonce="' . esc_attr(wp_create_nonce('twae_dismiss_nonce_cool_form')) . '">
												<i class="eicon eicon-close" aria-hidden="true"></i>
											</button></div></div>',
							'tab'          => 'content',
							'inner_tab'    => 'form_fields_conditions_tab',
							'tabs_wrapper' => 'form_fields_tabs',
						]
					);
			}
			
			$marketing_notice_controls    = array();

			$conditional_logic_controls   = array();

			$marketing_notice_controls = array(
				
				'ctwae-mkt-country-conditions' => array(
						'name'         => 'ctwae-mkt-country-conditions',
						'label'        => esc_html__('Enable Country Code', 'country-code-for-elementor-form-telephone-field'),
						'type'         => \Elementor\Controls_Manager::SWITCHER,
						'label_on'     => esc_html__('Yes', 'country-code-for-elementor-form-telephone-field'),
						'label_off'    => esc_html__('No', 'country-code-for-elementor-form-telephone-field'),
						'condition'    => array(
							'field_type' => array('tel', 'ehp-tel'),
						),
						'tab'          => 'content',
						'default'      => 'no',
						'inner_tab'    => 'form_fields_content_tab',
						'tabs_wrapper' => 'form_fields_tabs',
						'ai'           => array(
							'active' => false,
						),
				),

				'ctwae_mkt_country_notice' => array(

						'name'            => 'ctwae_mkt_country_notice',
						'type'            => \Elementor\Controls_Manager::RAW_HTML,
						
						'raw'             => '<div class="elementor-control-raw-html cool-form-wrp"><div class="elementor-control-notice elementor-control-notice-type-info">
											<div class="elementor-control-notice-icon"><img class="twae-highlight-icon" src="'.esc_url( TWAE_URL . 'admin/assets/images/twae-highlight-icon.svg' ).'" width="250" alt="Highlight Icon" /></div>
											<div class="elementor-control-notice-main">
				
											<div class="elementor-control-notice-main-content">Add a country code dropdown to your phone field.</div>
											<div class="elementor-control-notice-main-actions">
											<button type="button" class="elementor-button e-btn e-info e-btn-1 twae-install-plugin"  data-plugin="country-code" data-nonce="' . esc_attr(wp_create_nonce('twae_install_nonce')) . '">Install Country Code</button>
											
											</button></div></div></div></div>',
											
						'tab'             => 'content',
						'condition'       => array(

							'field_type' =>  array('tel', 'ehp-tel'),
							'ctwae-mkt-country-conditions' => 'yes'
						),
						'inner_tab'       => 'form_fields_content_tab',
						'tabs_wrapper'    => 'form_fields_tabs',
					)
				);
		
				$conditional_logic_controls = array(

					'ctwae-mkt-conditional-conditions' => array(
						'name'         => 'ctwae-mkt-conditional-conditions',
						'label'        => esc_html__('Enable Conditions', 'conditional-fields-for-elementor-form'),
						'type'         => \Elementor\Controls_Manager::SWITCHER,
						'label_on'     => esc_html__('Yes', 'conditional-fields-for-elementor-form'),
						'label_off'    => esc_html__('No', 'conditional-fields-for-elementor-form'),
						'condition'    => array(
							'field_type' => array('text', 'email', 'textarea', 'number', 'select', 'radio', 'checkbox', 'tel'),
						),
						'tab'          => 'content',
						'default'      => 'no',
						'inner_tab'    => 'form_fields_advanced_tab',
						'tabs_wrapper' => 'form_fields_tabs',
						'ai'           => array(
							'active' => false,
						),
					),

					'ctwae_mkt_condition_notice' => array(
						'name'            => 'ctwae_mkt_condition_notice',
						'type'            => \Elementor\Controls_Manager::RAW_HTML,

						'raw' => '<div class="elementor-control-raw-html cool-form-wrp"><div class="elementor-control-notice elementor-control-notice-type-info">
								<div class="elementor-control-notice-icon">
									<img class="twae-highlight-icon" src="'.esc_url( TWAE_URL . 'admin/assets/images/twae-highlight-icon.svg' ).'" width="250" alt="Highlight Icon" />
								</div>
								<div class="elementor-control-notice-main">
									<div class="elementor-control-notice-main-content">Show or hide form fields using conditional logic.</div>
									<div class="elementor-control-notice-main-actions">
									<button type="button" class="elementor-button e-btn e-info e-btn-1 twae-install-plugin"  data-plugin="conditional" data-nonce="' . esc_attr(wp_create_nonce('twae_install_nonce')) . '">Install Conditional Fields</button></button></div></div></div>
								</div>',

						'tab'             => 'content',
						'condition'       => array(
							'field_type' => array('text', 'email', 'textarea', 'number', 'select', 'radio', 'checkbox', 'tel'),
							'ctwae-mkt-conditional-conditions' => 'yes'
						),
						'inner_tab'       => 'form_fields_advanced_tab',
						'tabs_wrapper'    => 'form_fields_tabs',
					)
				);
			$field_controls = array_merge(
				$marketing_notice_controls,
				$conditional_logic_controls
			);

			$control_data['fields'] = \array_merge($control_data['fields'], $field_controls);
			$widget->update_control('form_fields', $control_data);
		}
	}

	Twae_Marketing_Controllers::get_instance();
}
