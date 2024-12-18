<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use Elementor\Modules\Usage\Module as Usage_Module;
use ElementorPro\Modules\Forms\Submissions\Database\Query as Form_Submissions_Query;

/**
 * Class twae_free_form_plugin_notice
 *
 * Handles the display of admin notices for the free form plugin.
 */
if ( ! class_exists( 'twae_free_form_plugin_notice' ) ) {
    class twae_free_form_plugin_notice {
        private $id;
        private $message;
        private $days_interval;
        private static $instance;
        
        public static function instance($id = '', $message = '', $days_interval = 5) {
            if (is_null(self::$instance)) {
                self::$instance = new self($id, $message, $days_interval);
            }
            return self::$instance;
        }

        /**
         * Constructor for the class.
         *
         * @param string $id             Unique identifier for the notice.
         * @param string $message        The message to display in the notice.
         * @param int    $days_interval  The number of days after which the notice should be displayed.
         */
        public function __construct( $id = '', $message = '', $days_interval = 5 ) {
            $this->id             = esc_attr( $id );
            $this->message        = wp_kses_post( $message );
            $this->days_interval   = absint( $days_interval );
            $notice_dismiss = get_option( 'twae-' . $this->id . '_remove_notice', false );

            if ( ! empty( $this->id ) && ! empty( $this->message ) && 'yes' !== $notice_dismiss ) {
                $this->twae_admin_notice();
            }
        }

        /**
         * Initializes the admin notice logic.
         */
        public function twae_admin_notice() {
            $display_date = date( 'Y-m-d H:i:s' );
            $installation_date = get_option( 'twae-installDate', false );
       
            if ( empty( $installation_date ) || ! $installation_date ) {
                $installation_date = (new DateTime())->format('Y-m-d H:i:s');
            }   

            $twae_already_rated = get_option( 'twae-alreadyRated', false );
            $twae_upgrade_notice_spare_me = get_option( 'twae-upgrade-notices_spare_me', false );
            $twae_upgrade_notice_remove_notice = get_option( 'twae-upgrade-notices_remove_notice', false );

            $install_date = new DateTime( $installation_date );
            $current_date = new DateTime( $display_date );
            $difference   = $install_date->diff( $current_date );
            $diff_days    = $difference->days;
            $after_days   = $this->days_interval;

            $twae_free_notice = get_option( 'twae-v', false );

            if ( isset( $twae_free_notice ) && $twae_free_notice && 'yes' !== $twae_upgrade_notice_remove_notice && 'yes' !== $twae_upgrade_notice_spare_me ) {
                return;
            }

            if ( function_exists( 'is_plugin_active' ) &&
                 is_plugin_active( 'elementor-pro/elementor-pro.php' ) &&
                 ! is_plugin_active( 'timeline-widget-addon-for-elementor-pro/timeline-widget-addon-pro-for-elementor.php' ) &&
                 ! file_exists(plugin_dir_path(__FILE__) . 'conditional-fields-for-elementor-form-pro/class-conditional-fields-for-elementor-form-pro.php') &&
                 ! file_exists(plugin_dir_path(__FILE__) . 'conditional-fields-for-elementor-form/class-conditional-fields-for-elementor-form.php') &&
                 ! file_exists(plugin_dir_path(__FILE__) . 'cool-formkit-for-elementor-forms/cool-formkit-for-elementor-forms.php') &&
                 $diff_days >= $after_days &&
                 ! empty( $twae_already_rated ) &&
                 $twae_already_rated === 'yes' ) {
                add_action('wp_ajax_twae-' . $this->id . '_promotion', array( $this, 'twae_free_promotion_notice_dismiss' ));
                add_action( 'admin_notices', array( $this, 'twae_free_form_plugin_notice' ) );
            }
        }

        /**
         * Retrieves the form widget usage data.
         *
         * @return int|false The number of form widgets used or false if not available.
         */
        public function form_widget_usage_data() {
            $data = false;

            if ( class_exists( 'Elementor\Modules\Usage\Module' ) && method_exists( 'Elementor\Modules\Usage\Module', 'instance' ) && method_exists( 'Elementor\Modules\Usage\Module', 'recalc_usage' ) && method_exists( 'Elementor\Modules\Usage\Module', 'get_formatted_usage' ) ) {
                $usage_module = Usage_Module::instance();
                $usage_module->recalc_usage();
                $raw_usage = $usage_module->get_formatted_usage( 'raw' );

                if ( isset( $raw_usage['wp-page']['elements']['form'] ) ) {
                    $data = $raw_usage['wp-page']['elements']['form'];
                }
            }

            if ( class_exists( 'ElementorPro\Modules\Forms\Submissions\Database\Query' ) && method_exists( 'ElementorPro\Modules\Forms\Submissions\Database\Query', 'get_instance' ) && method_exists( 'ElementorPro\Modules\Forms\Submissions\Database\Query', 'get_submissions' ) && false === $data ) {
                try {
                    $form_submissions_query = Form_Submissions_Query::get_instance();
                    $submissions_data = $form_submissions_query->get_submissions( [ 'filters' => [ 'status' => [ 'value' => 'all' ] ] ] );

                    if ( isset( $submissions_data['data'] ) && is_array( $submissions_data['data'] ) && count( $submissions_data['data'] ) > 0 ) {
                        $data = count( $submissions_data['data'] );
                    }
                } catch ( Exception $e ) {
                    // Handle the exception as needed, e.g., log the error or set $data to a default value
                    $data = false; // or any other default value
                }
            }

            return $data;
        }

        /**
         * Displays the admin notice for the free form plugin.
         */
        public function twae_free_form_plugin_notice() {
            if ( function_exists( 'get_current_screen' ) ) {
                $screen = get_current_screen();
                if ( in_array( $screen->id, array( 'plugins', 'plugin-install' ) ) ) {
                    $widget_usage_data = $this->form_widget_usage_data() ?? false;
                    if ( false !== $widget_usage_data && $widget_usage_data > 0 ) {
                        
                        $script  = '<script>
                            jQuery(document).ready(function ($) {
                                $(document).on("click","#twae-' . $this->id . '_admin_notice .dismiss-button", function (event) {
                                    var $this = $(this);
                                    var ajaxURL="' . esc_url(admin_url('admin-ajax.php')) . '";
                                    var wp_nonce="' . wp_create_nonce('twae-' . $this->id . '_promotion') . '" ;
                                    $.post(ajaxURL, { "action":"twae-' . esc_attr($this->id) . '_promotion","id":"' . esc_attr($this->id) . '","_nonce":wp_nonce }, function( data ) {
                                        $("#twae-' . $this->id . '_admin_notice").slideUp("fast");
                                    }, "json");
                                });
                            });
                        </script>';

                        $style = '#twae-' . esc_attr( $this->id ) . '_admin_notice{border-radius:5px; max-width:870px; width:calc(100% - 10px); box-sizing:border-box; display:flex; align-items:center; column-gap:10px; padding: 5px .5rem; padding-right: 2.5rem;} #twae-' . esc_attr( $this->id ) . '_admin_notice button.dismiss-button:after{color: #f12945; content: "\f153"; font: normal 18px/21px dashicons; display: inline-block; vertical-align: middle; margin-left: 3px;}.twae-form-img-link{line-height: 1;}';
                        
                        $class = 'twae-' . $this->id . ' notice notice-success is-dismissible';
                        echo "<div id='twae-" . esc_attr( $this->id ) . "_admin_notice' class='" . esc_attr( $class ) . "'><style>" . wp_kses_post( $style ) . "</style><a href='".esc_url(site_url().'/wp-admin/plugin-install.php?tab=plugin-information&plugin=conditional-fields-for-elementor-form&TB_iframe=true&width=772&height=885')."' class='thickbox open-plugin-details-modal twae-form-img-link'><img src='" . TWAE_URL . "assets/images/conditional-fields-icon.png' alt='twae logo' style='width: 80px; height: auto;'/></a><p>" . wp_kses_post( $this->message ) . "<button class='button button-secondary dismiss-button'>".esc_html__( 'Not Interested', 'twae' )."</button></p></div>" . $script;
                    }
                }
            }
        }

        public function twae_free_promotion_notice_dismiss() {
            check_ajax_referer( 'twae-' . $this->id . '_promotion', '_nonce' );
            update_option( 'twae-' . $this->id . '_remove_notice', 'yes' );
            wp_send_json_success();
        }
    }
}
