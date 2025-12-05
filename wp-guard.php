<?php
/*
Plugin Name: WP Guard
Plugin URI: https://krefstudio.com
Description: Fail-ban + Cloudflare Turnstile integration for WordPress login/register/lostpassword.
Version: 1.0.0
Author: Kref Studio
Author URI: https://krefstudio.com
Requires at least: 6.0
Tested up to: 6.9
Text Domain: wp-guard
*/

if (!defined('ABSPATH')) exit;

define('WPGP_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPGP_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once WPGP_PLUGIN_DIR . 'includes/helpers.php';
require_once WPGP_PLUGIN_DIR . 'includes/class-ban-manager.php';
require_once WPGP_PLUGIN_DIR . 'includes/class-turnstile.php';
require_once WPGP_PLUGIN_DIR . 'includes/class-login-protect.php';
require_once WPGP_PLUGIN_DIR . 'includes/class-custom-login.php';

if (!class_exists('WPGP_Main')) {
    class WPGP_Main {
        private $ban;
        private $turnstile;
        private $protect;
        private $custom_login;

        public function __construct() {
            $this->ban = new WPGP_Ban_Manager();
            $this->turnstile = new WPGP_Turnstile();
            $this->protect = new WPGP_Login_Protect($this->ban, $this->turnstile);
            $this->custom_login = new WPGP_Custom_Login();

            // Admin Hooks
            add_action('admin_menu', [$this, 'admin_menu']);
            add_action('admin_init', [$this, 'register_settings']);
            add_action('wp_ajax_wpgp_unban_ip', [$this, 'ajax_unban_ip']);

            // Frontend / Login Hooks
            add_action('login_enqueue_scripts', [$this, 'enqueue_login_assets']);
            add_action('wp_login', [$this->protect, 'handle_success_login'], 10, 2);
            add_action('init', [$this->protect, 'block_if_banned']);
            add_action('wp_login_failed', [$this, 'handle_login_failed']);

            // Turnstile Hooks
            add_filter('registration_errors', [$this->protect, 'validate_turnstile_on_register'], 10, 3);
            add_action('lostpassword_post', [$this->protect, 'validate_turnstile_on_lostpassword']);

            // Activation
            register_activation_hook(__FILE__, [$this, 'activate']);
        }

        public function activate() {
            global $wpdb;
            $table = $wpdb->prefix . 'wpgp_bans';
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE $table (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                ip varchar(45) NOT NULL,
                fail_count int(11) DEFAULT 0,
                banned_until datetime DEFAULT NULL,
                reason varchar(255) DEFAULT '',
                created_at datetime DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY  (id),
                KEY ip (ip)
            ) $charset_collate;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

            if ($this->custom_login) {
                $this->custom_login->activate();
            }
        }

        public function admin_menu() {
            add_options_page(
                'WP Guard',
                'WP Guard',
                'manage_options',
                'wpgp-admin',
                [$this, 'settings_page']
            );
        }

        public function settings_page() {
            $opts = get_option('wpgp_options', []);
            $banned = $this->ban->get_all_bans();
            require_once WPGP_PLUGIN_DIR . 'admin/settings-page.php';
        }

        public function register_settings() {
            register_setting('wpgp_options_group', 'wpgp_options', [$this, 'validate_options']);
        }

        public function validate_options($input) {
            $out = get_option('wpgp_options', []);
            $out['max_login_failures'] = max(1, intval($input['max_login_failures'] ?? $out['max_login_failures'] ?? 3));
            $out['ban_duration_hours'] = max(1, intval($input['ban_duration_hours'] ?? $out['ban_duration_hours'] ?? 4));
            $out['reset_on_success'] = !empty($input['reset_on_success']) ? 1 : 0;
            $out['turnstile_enabled'] = !empty($input['turnstile_enabled']) ? 1 : 0;
            $out['turnstile_sitekey'] = sanitize_text_field($input['turnstile_sitekey'] ?? $out['turnstile_sitekey'] ?? '');
            $out['turnstile_secret'] = sanitize_text_field($input['turnstile_secret'] ?? $out['turnstile_secret'] ?? '');
            $m = $input['turnstile_mode'] ?? $out['turnstile_mode'] ?? 'always';
            $out['turnstile_mode'] = in_array($m, ['always','invisible','fallback']) ? $m : 'always';
            $out['whitelist_ips'] = sanitize_textarea_field($input['whitelist_ips'] ?? $out['whitelist_ips'] ?? '');
            $out['custom_login_slug'] = sanitize_title($input['custom_login_slug'] ?? $out['custom_login_slug'] ?? '');
            return $out;
        }

        public function enqueue_login_assets(){
            $opts = get_option('wpgp_options');
            if (!empty($opts['turnstile_enabled']) && !empty($opts['turnstile_sitekey'])){
                wp_enqueue_script('cloudflare-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', [], null, true);
                wp_enqueue_script('wpgp-turnstile-init', WPGP_PLUGIN_URL . 'assets/js/turnstile-init.js', ['cloudflare-turnstile'], null, true);
                wp_localize_script('wpgp-turnstile-init', 'WPGP_TURNSTILE', [
                    'sitekey' => $opts['turnstile_sitekey'],
                    'mode' => $opts['turnstile_mode'] ?? 'always'
                ]);
            }
        }

        public function handle_login_failed($username) {
            $ip = wpgp_get_client_ip();
            if (wpgp_is_ip_whitelisted($ip)) return;
            
            $opts = get_option('wpgp_options');
            $max_failures = $opts['max_login_failures'] ?? 3;
            $ban_hours = $opts['ban_duration_hours'] ?? 4;

            $this->ban->increment_failure($ip, $max_failures, $ban_hours);
        }

        public function ajax_unban_ip(){
            if (!current_user_can('manage_options')) wp_send_json_error('no');
            check_ajax_referer('wpgp_admin');
            $ip = sanitize_text_field($_POST['ip'] ?? '');
            if (!$ip) wp_send_json_error('missing ip');
            $this->ban->unban_ip($ip);
            wp_send_json_success();
        }
    }
}

new WPGP_Main();
