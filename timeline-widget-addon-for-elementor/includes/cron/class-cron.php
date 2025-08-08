<?php
if (!defined('ABSPATH')) {
    exit();
}

if (!class_exists('TWAE_cronjob')) {
    class TWAE_cronjob
    {
    

        public function __construct() {
           
       
          // Register cron jobs
            add_filter('cron_schedules', array($this, 'twae_cron_schedules'));
            add_action('twae_extra_data_update', array($this, 'twae_cron_extra_data_autoupdater'));
        }
        
        function twae_cron_extra_data_autoupdater() {
       
                if (class_exists('TWAE_cronjob')) {
                    TWAE_cronjob::twae_send_data();
                }

        }
           
       static public function twae_send_data() {
                   
            $feedback_url = TWAE_FEEDBACK_API.'wp-json/coolplugins-feedback/v1/site';
            require_once TWAE_PATH . 'admin/feedback/twae-admin-feedback-form.php';

            if (!defined('TWAE_PATH')  || !class_exists('\TWAE_feddback\feedback\cool_plugins_feedback') ) {
                return;
            }
            
            $extra_data         = new TWAE_feddback\feedback\cool_plugins_feedback();
            $extra_data_details = $extra_data->twae_get_user_info();


            $server_info    = $extra_data_details['server_info'];
            $extra_details  = $extra_data_details['extra_details'];
            $site_url       = get_site_url();
            $install_date   = get_option('twae-install-date');
            $uni_id         = '50';
            $site_id        = $site_url . '-' . $install_date . '-' . $uni_id;
            $initial_version = get_option('twae_initial_save_version');
            $initial_version = is_string($initial_version) ? sanitize_text_field($initial_version) : 'N/A';
            $plugin_version = defined('TWAE_VERSION') ? TWAE_VERSION : 'N/A';
            $admin_email    = sanitize_email(get_option('admin_email') ?: 'N/A');
            
            $post_data = array(

                'site_id'           => md5($site_id),
                'plugin_version'    => $plugin_version,
                'plugin_name'       => 'Timeline Widget Addon For Elementor',
                'plugin_initial'    => $initial_version,
                'email'             => $admin_email,
                'site_url'          => esc_url_raw($site_url),
                'server_info'       => $server_info,
                'extra_details'     => $extra_details,
            );
            
            $response = wp_remote_post($feedback_url, array(

                'method'    => 'POST',
                'timeout'   => 30,
                'headers'   => array(
                    'Content-Type' => 'application/json',
                ),
                'body'      => wp_json_encode($post_data),
            ));

            
            if (is_wp_error($response)) {

                error_log('TWAE Feedback Send Failed: ' . $response->get_error_message());
                return;
            }
            
            $response_body  = wp_remote_retrieve_body($response);
            $decoded        = json_decode($response_body, true);
            if (!wp_next_scheduled('twae_extra_data_update')) {

                wp_schedule_event(time(), 'every_30_days', 'twae_extra_data_update');
            }
        }
          
        /**
         * Cron status schedule(s).
         */
        public function twae_cron_schedules($schedules)
        {
            // 30days schedule for update information
            if (!isset($schedules['every_30_days'])) {

                $schedules['every_30_days'] = array(
                    'interval' => 30 * 24 * 60 * 60, // 2,592,000 seconds
                    'display'  => __('Once every 30 days'),
                );
            }

            return $schedules;
        }

      
    }

    $cron_init = new TWAE_cronjob();
}
