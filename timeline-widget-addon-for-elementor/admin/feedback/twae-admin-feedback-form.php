<?php
namespace TWAE_feddback\feedback;

/**
 *
 * --------------- ALWAYS CHANGE THE NAMESPACE ACCORDING TO THE PLUGIN ---------------------------
 *
 * This is the main file for creating admin feedback form.
 * It is displayed when user deactivate the plugin.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class cool_plugins_feedback {

	private static $instance = null;
	private $plugin_name     = 'Timeline Widget Addon For Elementor';
	private $plugin_url      = TWAE_URL;
	private $plugin_version  = TWAE_VERSION;
	private $plugin_slug     = 'timeline-widget-addon-for-elementor';
	private $feedback_url    = TWAE_FEEDBACK_API.'wp-json/coolplugins-feedback/v1/feedback';

	/**
	 * Avoid creating multiple instance of this class
	 */
	static function get_instance() {

		if ( empty( self::$instance ) ) {
			return self::$instance = new self();
		}
		return self::$instance;

	}
	/*
	|-----------------------------------------------------------------|
	|   Use this constructor to fire all actions and filters          |
	|-----------------------------------------------------------------|
	*/
	public function __construct() {

		if ( ! is_admin() ) {
			return;
		}
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_feedback_scripts' ) );
		add_action( 'admin_head', array( $this, 'show_deactivate_feedback_popup' ) );
		add_action( 'wp_ajax_' . $this->plugin_slug . '_submit_deactivation_response', array( $this, 'submit_deactivation_response' ) );
	}

	/*
	|-----------------------------------------------------------------|
	|   Enqueue all scripts and styles to required page only          |
	|-----------------------------------------------------------------|
	*/
	function enqueue_feedback_scripts() {
		$screen = get_current_screen();
		if ( isset( $screen ) && $screen->id == 'plugins' ) {
			wp_enqueue_script( __NAMESPACE__ . '-feedback-script', $this->plugin_url . 'admin/feedback/js/admin-feedback.js' );
			wp_enqueue_style( 'cool-plugins-feedback-style', $this->plugin_url . 'admin/feedback/css/admin-feedback.css' );
		}
	}

	/*
	|-----------------------------------------------------------------|
	|   HTML for creating feedback popup form                         |
	|-----------------------------------------------------------------|
	*/
	public function show_deactivate_feedback_popup() {

		$screen = get_current_screen();
		if ( ! isset( $screen ) || $screen->id != 'plugins' ) {
			return;
		}
		$deactivate_reasons = array(
			'didnt_work_as_expected'         => array(
				'title'             => __( 'The plugin didn\'t work as expected', 'cool-plugins' ),
				'input_placeholder' => 'What did you expect?',
			),
			'found_a_better_plugin'          => array(
				'title'             => __( 'I found a better plugin', 'cool-plugins' ),
				'input_placeholder' => __( 'Please share which plugin', 'cool-plugins' ),
			),
			'couldnt_get_the_plugin_to_work' => array(
				'title'             => __( 'The plugin is not working', 'cool-plugins' ),
				'input_placeholder' => 'Please share your issue. So we can fix that for other users.',
			),
			'temporary_deactivation'         => array(
				'title'             => __( 'It\'s a temporary deactivation', 'cool-plugins' ),
				'input_placeholder' => '',
			),
			'other'                          => array(
				'title'             => __( 'Other', 'cool-plugins' ),
				'input_placeholder' => __( 'Please share the reason', 'cool-plugins' ),
			),
		);

		?>
		<div id="cool-plugins-deactivate-feedback-dialog-wrapper" class="hide-feedback-popup" data-slug="<?php echo $this->plugin_slug ?>" >
						
			<div class="cool-plugins-deactivation-response">
			<div id="cool-plugins-deactivate-feedback-dialog-header">
				<span id="cool-plugins-feedback-form-title"><?php echo esc_html__( 'Quick Feedback', 'cool-plugins' ); ?></span>
			</div>
			<div id="cool-plugins-loader-wrapper">
				<div class="cool-plugins-loader-container">
					<img class="cool-plugins-preloader" src="<?php echo esc_url( $this->plugin_url ); ?>admin/feedback/images/cool-plugins-preloader.gif">
				</div>
			</div>
			<div id="cool-plugins-form-wrapper" class="cool-plugins-form-wrapper-cls">
			<form id="cool-plugins-deactivate-feedback-dialog-form" method="post">
				<?php
				wp_nonce_field( '_cool-plugins_deactivate_feedback_nonce' );
				?>
				<input type="hidden" name="action" value="cool-plugins_deactivate_feedback" />
				<div id="cool-plugins-deactivate-feedback-dialog-form-caption"><?php echo esc_html__( 'If you have a moment, please share why you are deactivating this plugin.', 'cool-plugins' ); ?></div>
				<div id="cool-plugins-deactivate-feedback-dialog-form-body">
					<?php
					$reason_key_arr = array( 'didnt_work_as_expected', 'found_a_better_plugin', 'couldnt_get_the_plugin_to_work' );
					foreach ( $deactivate_reasons as $reason_key => $reason ) :
						?>
						<div class="cool-plugins-deactivate-feedback-dialog-input-wrapper">
							<input id="cool-plugins-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="cool-plugins-deactivate-feedback-dialog-input" type="radio" name="reason_key" value="<?php echo esc_attr( $reason_key ); ?>" />
							<label for="cool-plugins-deactivate-feedback-<?php echo esc_attr( $reason_key ); ?>" class="cool-plugins-deactivate-feedback-dialog-label"><?php echo esc_html( $reason['title'] ); ?></label>
							<?php
							if ( ! empty( $reason['input_placeholder'] ) ) :
								?>
								<textarea class="cool-plugins-feedback-text" type="textarea" name="reason_<?php echo esc_attr( $reason_key ); ?>" placeholder="<?php echo esc_attr( $reason['input_placeholder'] ); ?>"></textarea>
								<?php
								if ( in_array( $reason_key, $reason_key_arr, true ) ) {
									$ctl_plugin_url = 'https://wordpress.org/plugins/cool-timeline/';
									$ctb_plugin_url = 'https://wordpress.org/plugins/timeline-block/';
									?>
								  <div class="cool-plugins-extra-links">
									<?php echo esc_html__( 'Please try', 'cool-plugins' ); ?> <a href="<?php echo esc_url( $ctl_plugin_url ); ?>" target="_blank"><?php echo esc_html__( 'Cool Timeline', 'cool-plugins' ); ?></a> <?php echo esc_html__( 'or', 'cool-plugins' ); ?> <a href="<?php echo esc_url( $ctb_plugin_url ); ?>" target="_blank"><?php echo esc_html__( 'Timeline Block', 'cool-plugins' ); ?></a> <?php echo esc_html__( 'plugin.', 'cool-plugins' ); ?>
									</div>
									<?php
								}

							endif;
							?>
							<?php if ( ! empty( $reason['alert'] ) ) : ?>
								<div class="cool-plugins-feedback-text"><?php echo esc_html( $reason['alert'] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
<input class="cool-plugins-GDPR-data-notice" id="cool-plugins-GDPR-data-notice-<?php echo $this->plugin_slug; ?>" type="checkbox"><label for="cool-plugins-GDPR-data-notice"><?php echo esc_html__( 'I agree to share anonymous usage data and basic site details (such as server, PHP, and WordPress versions) to support Cool Timeline improvement efforts. Additionally, I allow Cool Plugins to store all information provided through this form and to respond to my inquiry.' ); ?></label>				</div>
				<div class="cool-plugin-popup-button-wrapper">
					<a class="cool-plugins-button button-deactivate" id="twae-cool-plugin-submitNdeactivate"><?php echo esc_html__( 'Submit and Deactivate', 'cool-plugins' ); ?></a>
					<a class="cool-plugins-button" id="twae-cool-plugin-skipNdeactivate"><?php echo esc_html__( 'Skip and Deactivate', 'cool-plugins' ); ?></a>
				</div>
			</form>
			</div>
		   </div>
		</div>
		<?php
	}

	function twae_get_user_info() {
		global $wpdb;
	
		// Server and WP environment details
		$server_info = [
			'server_software'        => isset($_SERVER['SERVER_SOFTWARE']) ? sanitize_text_field($_SERVER['SERVER_SOFTWARE']) : 'N/A',
			'mysql_version'          => $wpdb ? sanitize_text_field($wpdb->get_var("SELECT VERSION()")) : 'N/A',
			'php_version'            => sanitize_text_field(phpversion() ?: 'N/A'),
			'wp_version'             => sanitize_text_field(get_bloginfo('version') ?: 'N/A'),
			'wp_debug'               => (defined('WP_DEBUG') && WP_DEBUG) ? 'Enabled' : 'Disabled',
			'wp_memory_limit'        => sanitize_text_field(ini_get('memory_limit') ?: 'N/A'),
			'wp_max_upload_size'     => sanitize_text_field(ini_get('upload_max_filesize') ?: 'N/A'),
			'wp_permalink_structure' => sanitize_text_field(get_option('permalink_structure') ?: 'Default'),
			'wp_multisite'           => is_multisite() ? 'Enabled' : 'Disabled',
			'wp_language'            => sanitize_text_field(get_option('WPLANG') ?: get_locale()),
			'wp_prefix'              => isset($wpdb->prefix) ? sanitize_key($wpdb->prefix) : 'N/A',
		];
	
		// Theme details
		$theme = wp_get_theme();
		$theme_data = [
			'name'      => sanitize_text_field($theme->get('Name')),
			'version'   => sanitize_text_field($theme->get('Version')),
			'theme_uri' => esc_url($theme->get('ThemeURI')),
		];
	

		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		if (!function_exists('get_plugin_data')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
	

		$plugin_data = [];
		$active_plugins = get_option('active_plugins', []);
	
		foreach ($active_plugins as $plugin_path) {
			$plugin_file = WP_PLUGIN_DIR . '/' . ltrim($plugin_path, '/');
	
			if (file_exists($plugin_file)) {

				$plugin_info = get_plugin_data($plugin_file, false, false);
				$plugin_url = !empty($plugin_info['PluginURI']) ? esc_url($plugin_info['PluginURI']) : (!empty($plugin_info['AuthorURI']) ? esc_url($plugin_info['AuthorURI']) : 'N/A');
				$plugin_data[] = [
					'name'       => sanitize_text_field($plugin_info['Name']),
					'version'    => sanitize_text_field($plugin_info['Version']),
					'plugin_uri' => !empty($plugin_url) ? $plugin_url : 'N/A',
				];
			}
		}
	
		return [
			'server_info'   => $server_info,
			'extra_details' => [
				'wp_theme'       => $theme_data,
				'active_plugins' => $plugin_data,
			],
		];
	}
	


	function submit_deactivation_response() {
		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], '_cool-plugins_deactivate_feedback_nonce' ) ) {
			wp_send_json_error();
		} else {
			$reason             = htmlspecialchars( $_POST['reason'], ENT_QUOTES );
			$deactivate_reasons = array(
				'didnt_work_as_expected'         => array(
					'title'             => esc_html__( 'The plugin didn\'t work as expected', 'cool-plugins' ),
					'input_placeholder' => esc_html__( 'What did you expect?', 'cool-plugins' ),
				),
				'found_a_better_plugin'          => array(
					'title'             => esc_html__( 'I found a better plugin', 'cool-plugins' ),
					'input_placeholder' => esc_html__( 'Please share which plugin', 'cool-plugins' ),
				),
				'couldnt_get_the_plugin_to_work' => array(
					'title'             => esc_html__( 'The plugin is not working', 'cool-plugins' ),
					'input_placeholder' => esc_html__( 'Please share your issue. So we can fix that for other users.', 'cool-plugins' ),
				),
				'temporary_deactivation'         => array(
					'title'             => esc_html__( 'It\'s a temporary deactivation', 'cool-plugins' ),
					'input_placeholder' => '',
				),
				'other'                          => array(
					'title'             => esc_html__( 'Other', 'cool-plugins' ),
					'input_placeholder' => esc_html__( 'Please share the reason', 'cool-plugins' ),
				),
			);

			$deativation_reason = array_key_exists( $reason, $deactivate_reasons ) ? $reason : 'other';
			$plugin_initial =  get_option( 'twae_initial_save_version' );
			$sanitized_message = sanitize_text_field( $_POST['message'] ) == '' ? 'N/A' : sanitize_text_field( $_POST['message'] );
			$admin_email       = sanitize_email( get_option( 'admin_email' ) );
			$site_url          = esc_url( site_url() );
			$install_date 		= get_option('twae-install-date');
			$unique_key     	= '50';  // Ensure this key is unique per plugin to prevent collisions when site URL and install date are the same across plugins
            $site_id        	= $site_url . '-' . $install_date . '-' . $unique_key;
			$response          = wp_remote_post(
				$this->feedback_url,
				array(
					'timeout' => 30,
					'body'    => array(
						'server_info' => serialize($this->twae_get_user_info()['server_info']), 
						'extra_details' => serialize($this->twae_get_user_info()['extra_details']),
						'plugin_version' => $this->plugin_version,
						'plugin_name'    => $this->plugin_name,
						'reason'         => $deativation_reason,
						'review'         => $sanitized_message,
						'email'          => $admin_email,
						'domain'         => $site_url,
						'site_id'        => md5($site_id),
						'plugin_initial' => $plugin_initial,
					),
				)
			);

			die( json_encode( array( 'response' => $response ) ) );
		}

	}
}
cool_plugins_feedback::get_instance();
