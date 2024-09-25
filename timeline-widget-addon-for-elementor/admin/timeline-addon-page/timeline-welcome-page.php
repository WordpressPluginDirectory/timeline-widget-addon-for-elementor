<?php
use Elementor\Modules\Promotions\Module as Promotions_Module;
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Do not use namespace to keep this on global space to keep the singleton initialization working
if ( ! class_exists( 'twae_welcome_page' ) ) {

	/**
	 *
	 * This is the main class for creating dashbord addon page and all submenu items
	 *
	 * Do not call or initialize this class directly, instead use the function mentioned at the bottom of this file
	 */
	class twae_welcome_page {


		/**
		 * None of these variables should be accessable from the outside of the class
		 */
		private static $instance;
		private $main_menu_slug = null;
		private $menu_slug      = null;
		private $page_heading;
		private $addon_dir             = __DIR__; // point to the main addon-page directory
		private $menu_title            = '';
		private $dashboar_page_heading = 'Timeline Addons';

		/**
		 * initialize the class and create dashboard page only one time
		 */
		public static function init() {
			if ( empty( self::$instance ) ) {
				return self::$instance = new self();
			}
			return self::$instance;

		}

		/**
		 * Initialize the dashboard with specific plugins as per plugin tag
		 */
		public function show_plugins( $main_menu_slug, $menu_slug, $page_heading, $menu_title ) {
			if ( ! empty( $menu_slug ) && ! empty( $page_heading ) ) {
				$this->menu_slug      = sanitize_text_field( $menu_slug );
				$this->main_menu_slug = sanitize_text_field( $main_menu_slug );
				$this->page_heading   = sanitize_text_field( $page_heading );
				$this->menu_title     = sanitize_text_field( $menu_title );
			} else {
				return false;
			}

			add_action( 'admin_menu', array( $this, 'init_plugins_dasboard_page' ), 999 );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_required_scripts' ) );
		}

		/**
		 * This function will initialize the main dashboard menu for all plugins
		 */
		public function init_plugins_dasboard_page() {
			$twae_addon_page_slug = menu_page_url( 'cool-plugins-timeline-addon', false );

			$twae_parent_page_slug = ! empty( $twae_addon_page_slug ) ? 'cool-plugins-timeline-addon' : $this->main_menu_slug;
			$twae_page_position    = ! empty( $twae_addon_page_slug ) ? 2 : Promotions_Module::ADMIN_MENU_PRIORITY + 15;

			add_submenu_page( $twae_parent_page_slug, $this->menu_title, $this->menu_title, 'manage_options', $this->menu_slug, array( $this, 'twae_welcome_page_content' ), $twae_page_position );
		}

		/**
		 * This function will render and create the HTML display of dashboard page.
		 * All the HTML can be located in other template files.
		 * Avoid using any HTML here or use nominal HTML tags inside this function.
		 */
		public function twae_welcome_page_content() {
				require $this->addon_dir . '/includes/dashboard-header.php';
				echo '<div class="cool-body-left">';
				require $this->addon_dir . '/includes/twae-get-started-content.php';
				echo $this->request_wp_plugins_data();
				echo '</div>'; // end of .cool-body-left
				require $this->addon_dir . '/includes/dashboard-sidebar.php';
		}

		/**
		 * Lets enqueue all the required CSS & JS
		 */
		public function enqueue_required_scripts() {
			// A common CSS file will be enqueued for admin panel
			wp_enqueue_style( 'cool-plugins-timeline-addon', plugin_dir_url( __FILE__ ) . 'assets/css/styles.css', null, null, 'all' );
		}

		/**
		 * Gather all the free plugin information from wordpress.org API
		 */
		public function request_wp_plugins_data() {
			$html        = '';
			$plugin_info = array(
				'cool-timeline'  => array(
					'icon'            => plugin_dir_url( __FILE__ ) . '/assets/images/cool-timeline.png',
					'desc'            => 'Showcase your story or company history in a precise and elegant way using a powerful and advanced Cool Timeline',
					'slug'            => 'https://wordpress.org/plugins/cool-timeline/',
					'name'            => 'Cool Timeline (Horizontal and Vertical Timeline)',
					'plugin_path'     => 'cool-timeline/cooltimeline.php',
					'pro_plugin_path' => 'cool-timeline-pro/cool-timeline-pro.php',
				),
				'timeline-block' => array(
					'icon'        => plugin_dir_url( __FILE__ ) . '/assets/images/timeline-block.png',
					'desc'        => 'Showcase your story or company history,events,process steps and Roadmap in precise and elegant way using…',
					'slug'        => 'https://wordpress.org/plugins/timeline-block/',
					'name'        => 'Timeline Block For Gutenberg',
					'plugin_path' => 'timeline-block/timeline-block.php',
				),
			);

			$html .= '<div class="twae_welcome_plugin_list plugins-list">';
			foreach ( $plugin_info as $plugin ) {
				$html .= $this->extra_plugins_html( $plugin );
			}
			$html .= '</div>';
			return $html;
		}

		public function extra_plugins_html( $data ) {
			$pro_path             = isset( $data['pro_plugin_path'] ) ? $data['pro_plugin_path'] : false;
			$plugin_path          = isset( $data['plugin_path'] ) ? $data['plugin_path'] : false;
			$icon                 = isset( $data['icon'] ) ? $data['icon'] : '';
			$plugin_download_link = isset( $data['slug'] ) ? $data['slug'] : '';
			$plugin_name          = isset( $data['name'] ) ? $data['name'] : '';
			$plugin_desc          = isset( $data['desc'] ) ? $data['desc'] : '';
			$plugin_disable       = false;
			$button_html          = '';
			$classes              = 'plugin-block';
			if ( $pro_path && is_plugin_active( $pro_path ) ) {
				$plugin_disable = true;
				$classes       .= ' plugin-not-required installed-plugin inactive-plugin';
			}
			if ( $plugin_path && is_plugin_active( $plugin_path ) ) {
				$plugin_disable = true;
				$classes       .= ' plugin-not-required installed-plugin active-plugin';
			}
			if ( $plugin_disable ) {
				$button_html .= '<button class="' . esc_html( 'button button-secondary button-disabled' ) . '">Download</a>';
			} else {
				$button_html .= '<a class="' . esc_html( 'button button-secondary' ) . '" href="' . esc_url( $plugin_download_link ) . '" target="' . esc_html( '_blank' ) . '">Download</a>';
			}
			$html  = '';
			$html .= '<div class="' . esc_attr( $classes ) . '">
					<div class="plugin-block-inner">
					<div class="plugin-logo">';
			$html .= '<img src="' . esc_url( $icon ) . '" width="250px" /></div>
				<div class="plugin-info">';
			$html .= '<h4 class="plugin-title">' . wp_kses_post( $plugin_name ) . '</h4>';
			$html .= '<div class="plugin-desc">' . wp_kses_post( $plugin_desc ) . '</div>';
			$html .= '<div class="plugin-stats">';
			$html .= wp_kses_post( $button_html );
			$html .= '</div></div></div></div>';
			return $html;
		}
	}

	/**
	 *
	 * initialize the main dashboard class with all required parameters
	 */
	function twae_welcome_page( $main_menu_slug, $settings_page_slug, $page_heading, $menu_title ) {
		 $event_page = twae_welcome_page::init();
		$event_page->show_plugins( $main_menu_slug, $settings_page_slug, $page_heading, $menu_title );
	}
}
