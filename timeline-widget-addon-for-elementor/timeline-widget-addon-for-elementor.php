<?php
/**
 * Plugin Name: Timeline Widget For Elementor
 * Description: Best timeline widget for Elementor page builder to showcase your personal or business stories in beautiful vertical or horizontal timeline layouts. <strong>[Elementor Addon]</strong>
 * Plugin URI:  https://coolplugins.net
 * Version:     1.6.9.1
 * Author:      Cool Plugins
 * Author URI:  https://coolplugins.net/?utm_source=twae_plugin&utm_medium=inside&utm_campaign=author_page&utm_content=dashboard
 * Domain Path: /languages
 * Text Domain: twae
 * Elementor tested up to: 3.28.0
 * Elementor Pro tested up to: 3.28.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( defined( 'TWAE_VERSION' ) ) {
	return;
}

define( 'TWAE_VERSION', '1.6.9.1' );
define( 'TWAE_FILE', __FILE__ );
define( 'TWAE_PATH', plugin_dir_path( TWAE_FILE ) );
define( 'TWAE_URL', plugin_dir_url( TWAE_FILE ) );
define( 'TWAE_BUY_PRO_LINK', 'https://cooltimeline.com/plugin/elementor-timeline-widget-pro/?utm_source=twae_plugin&utm_medium=inside&utm_campaign=get_pro' );
define( 'TWAE_FEEDBACK_API', 'https://feedback.coolplugins.net/' );
if ( ! defined( 'TWAE_DEMO_URL' ) ) {
	define( 'TWAE_DEMO_URL', 'https://cooltimeline.com/demo/elementor-timeline-widget/?utm_source=twae-plugin&utm_medium=inside&utm_campaign=twae-free-dashboard' );
}

register_activation_hook( TWAE_FILE, array( 'Timeline_Widget_Addon', 'twae_activate' ) );
register_deactivation_hook( TWAE_FILE, array( 'Timeline_Widget_Addon', 'twae_deactivate' ) );

/**
 * Class Timeline_Widget_Addon
 */
final class Timeline_Widget_Addon {


	/**
	 * Plugin instance.
	 *
	 * @var Timeline_Widget_Addon
	 * @access private
	 */
	private static $instance = null;

	/**
	 * Get plugin instance.
	 *
	 * @return Timeline_Widget_Addon
	 * @static
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @access private
	 */
	private function __construct() {
		// Load the plugin after Elementor (and other plugins) are loaded.
		add_action( 'plugins_loaded', array( $this, 'twae_plugins_loaded' ) );
		add_action( 'plugins_loaded', array( $this, 'twae_load_addon' ) );
		add_action('init', array($this, 'twae_plugin_textdomain'));
		 add_action( 'activated_plugin', array( $this, 'twae_plugin_redirection' ) );

	    $this->cpfm_load_file();
	}

	public function cpfm_load_file(){

        if(!class_exists('CPFM_Feedback_Notice')){
            require_once __DIR__ . '/admin/feedback/cpfm-feedback-notice.php';
        }
        require_once __DIR__ . '/includes/cron/class-cron.php';

		if (  is_plugin_active( 'elementor/elementor.php' )) {
			require_once TWAE_PATH . '/admin/twae-marketing-common.php';
		}
    }

	/**
	 * Code you want to run when all other plugins loaded.
	 */
	function twae_plugins_loaded() {

		// Notice if the Elementor is not active
		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'twae_fail_to_load' ) );
			return;
		}

		// Require the main plugin file
		// require( __DIR__ . '/includes/class-twae.php' );
		if ( is_admin() ) {
			$pluginpath= plugin_basename( __FILE__ );
			/*** Plugin review notice file */
			require_once __DIR__ . '/admin/twae-feedback-notice.php';
			new TWAEFeedbackNotice();
			require_once __DIR__ . '/admin/feedback/twae-admin-feedback-form.php';
			require_once __DIR__ . '/admin/admin-notices.php';
			require_once __DIR__ . '/admin/form-plugin-notice.php';

			require_once TWAE_PATH . '/admin/timeline-addon-page/timeline-welcome-page.php';

			twae_welcome_page( 'elementor', 'twae-welcome-page', 'Timeline Widget', 'Timeline Widget' );
			add_filter( "plugin_action_links_$pluginpath", array( $this, 'ctl_settings_link' ) );
		}

		if ( is_admin() ) {
			add_action('admin_init', array($this,'twae_form_plugin_notice'));
			add_action( 'admin_init', array( $this, 'twae_show_upgrade_notice' ) );
		}

		add_action('cpfm_register_notice', function () {
            
			if (!class_exists('CPFM_Feedback_Notice') || !current_user_can('manage_options')) {
				return;
			}

			$notice = [

				'title' => __('Timeline Plugins by Cool Plugins', 'twae'),
				'message' => __('Help us make this plugin more compatible with your site by sharing non-sensitive site data.', 'cool-plugins-feedback'),
				'pages' => ['twae-welcome-page'],
				'always_show_on' => ['twae-welcome-page'], // This enables auto-show
				'plugin_name'=>'twae'
			];

			CPFM_Feedback_Notice::cpfm_register_notice('cool-timeline', $notice);

				if (!isset($GLOBALS['cool_plugins_feedback'])) {
					$GLOBALS['cool_plugins_feedback'] = [];
				}
			
				$GLOBALS['cool_plugins_feedback']['cool-timeline'][] = $notice;
	   
		});
		add_action('cpfm_after_opt_in_twae', function($category) {

			if ($category === 'cool-timeline') {

				TWAE_cronjob::twae_send_data();
			}
		});

	}   // end of ctla_loaded()

	/**
	 * Load the plugin text domain for translation.
	 */
	public function twae_plugin_textdomain() {
		load_plugin_textdomain( 'twae', false, basename( dirname( __FILE__ ) ) . '/languages/' );
		if (!get_option( 'twae_initial_save_version' ) ) {
                add_option( 'twae_initial_save_version', TWAE_VERSION );
            }
            if(!get_option( 'twae-install-date' ) ) {
                add_option( 'twae-install-date', gmdate('Y-m-d h:i:s') );
            }
	}

		public function twae_plugin_redirection( $plugin ) {
			if ( plugin_basename( __FILE__ ) === $plugin ) {
				exit( wp_redirect( admin_url( 'admin.php?page=twae-welcome-page' ) ) );
			}
		}
public function ctl_settings_link( $links ) {
			
			$links[] = '<a style="font-weight:bold; color:#852636;" href="https://cooltimeline.com/plugin/elementor-timeline-widget-pro/?utm_source=twae_plugin&utm_medium=inside&utm_campaign=get_pro&utm_content=plugin_list" target="_blank">Get Pro</a>';

			return $links;
		}

	function twae_load_addon() {
		// Load plugin file
		require_once TWAE_PATH . '/includes/class-twae-free-main.php';
		// Run the plugin
		TWAE_Free_Main::instance();

	}

	public function twae_show_upgrade_notice() {
		if ( get_option( 'twae-v' ) != false ) {
			twae_free_create_admin_notice(
				array(
					'id'      => 'twae-upgrade-notices',
					'message' => wp_kses_post( '<strong>Major Update Notice!</strong> Please update your timeline widget settings if you face any style issue after an update of <strong>Timeline Widget for Elementor</strong>.' ),
				)
			);
		}
	}

	public function twae_form_plugin_notice() {
		if(class_exists('twae_free_form_plugin_notice')){
			twae_free_form_plugin_notice::instance('cool-form-free','Are you using the <strong>Elementor Form widget</strong> to create forms? Make your forms smarter with conditional fields and improve your form-building experience!<br><a href="'.esc_url(site_url().'/wp-admin/plugin-install.php?tab=plugin-information&plugin=conditional-fields-for-elementor-form&TB_iframe=true&width=772&height=885').'" class="thickbox button button-primary open-plugin-details-modal" style="margin-right: 10px; margin-top: 10px;">'.esc_html__( 'Install Plugin', 'twae' ).'</a><a href="'.esc_url("https://coolplugins.net/product/conditional-fields-for-elementor-form/?utm_source=twae_plugin&utm_medium=inside&utm_campaign=plugins_list&utm_content=demo#demos").'" class="button button-primary" target="_blank" style="margin-right: 10px;">'.esc_html__( 'View Demos', 'twae' ).'</a>',5);
		}
	}

	function twae_fail_to_load() {

		if ( ! is_plugin_active( 'elementor/elementor.php' ) ) : ?>
			<div class="notice notice-warning is-dismissible">
				<p><?php echo wp_kses_post( sprintf( __( '<a href="%s"  target="_blank" >Elementor Page Builder</a>  must be installed and activated for "<strong>Timeline Widget Addon For Elementor</strong>" to work' ), 'https://wordpress.org/plugins/elementor/' ) ); ?></p>
			</div>
			<?php
			deactivate_plugins( 'timeline-widget-addon-for-elementor/timeline-widget-addon-for-elementor.php' );
		endif;

	}

	/**
	 * Run when activate plugin.
	 */
	public static function twae_activate() {
		update_option( 'twae-free-v', sanitize_text_field( TWAE_VERSION ) );
		update_option( 'twae-type', 'FREE' );
		update_option( 'twae-installDate', date( 'Y-m-d h:i:s' ) );

		
		if (!get_option( 'twae_initial_save_version' ) ) {
			add_option( 'twae_initial_save_version', TWAE_VERSION );
		}

		if(!get_option( 'twae-install-date' ) ) {
			add_option( 'twae-install-date', gmdate('Y-m-d h:i:s') );
		}
		$review_option = get_option( 'cpfm_opt_in_choice_cool-timeline' );
		if($review_option === 'yes'){

			if (!wp_next_scheduled('twae_extra_data_update')) {
	
				wp_schedule_event(time(), 'every_30_days', 'twae_extra_data_update');
	
			}
	}

	}

	/**
	 * Run when deactivate plugin.
	 */
	public static function twae_deactivate() {

		if (wp_next_scheduled('twae_extra_data_update')) {
			wp_clear_scheduled_hook('twae_extra_data_update');
		}

	}
}

function Timeline_Widget_Addon() {
	return Timeline_Widget_Addon::get_instance();
}

$GLOBALS['Timeline_Widget_Addon'] = Timeline_Widget_Addon();
