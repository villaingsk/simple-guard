<?php
if (!defined('ABSPATH')) exit;

class SG_Login_Protect {
    private $ban;
    private $turnstile;

    public function __construct($ban_manager, $turnstile) {
        $this->ban = $ban_manager;
        $this->turnstile = $turnstile;
    }

    public function handle_success_login($user_login, $user){
        $ip = sg_get_client_ip();
        $opts = get_option('sg_options');
        if (!empty($opts['reset_on_success'])){
            $this->ban->reset_fail_count($ip);
        }
    }


    public function block_if_banned(){
        // only block access to wp-login.php (and auth endpoints)
        $request_uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : '';
        if (strpos($request_uri, 'wp-login.php') === false) return;
        $ip = sg_get_client_ip();
        if (sg_is_ip_whitelisted($ip)) return;
        if ($this->ban->is_banned($ip)){
            /* translators: %s: IP address */
            wp_die(esc_html(sprintf(__('Your IP (%s) is temporarily banned.', 'simple-guard'), $ip)), 403);
        }
    }


    // registration handler: run turnstile validation early
    public function validate_turnstile_on_register($user_login, $user_email, $errors){
        $opts = get_option('sg_options');
        if (empty($opts['turnstile_enabled'])) return;
        // Nonce is verified by WordPress core in wp-login.php for registration
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';
        if (empty($token)){
            $errors->add('sg_captcha', __('Please complete the CAPTCHA (Turnstile).', 'simple-guard'));
            return;
        }
        if (!$this->turnstile->verify($token)){
            $errors->add('sg_captcha_failed', __('CAPTCHA verification failed.', 'simple-guard'));
            return;
        }
    }


    public function validate_turnstile_on_lostpassword(){
        $opts = get_option('sg_options');
        if (empty($opts['turnstile_enabled'])) return;
        // Nonce is verified by WordPress core in wp-login.php for lostpassword
        // phpcs:ignore WordPress.Security.NonceVerification.Missing
        $token = isset($_POST['cf-turnstile-response']) ? sanitize_text_field(wp_unslash($_POST['cf-turnstile-response'])) : '';
        if (empty($token)){
            wp_die(esc_html(__('Please complete the CAPTCHA (Turnstile).', 'simple-guard')), 400);
        }
        if (!$this->turnstile->verify($token)){
            wp_die(esc_html(__('CAPTCHA verification failed.', 'simple-guard')), 400);
        }
    }
    public function render_turnstile_widget(){
        $opts = get_option('sg_options');
        if (empty($opts['turnstile_enabled']) || empty($opts['turnstile_sitekey'])) return;
        echo '<div class="cf-turnstile" data-sitekey="' . esc_attr($opts['turnstile_sitekey']) . '"></div>';
    }
}