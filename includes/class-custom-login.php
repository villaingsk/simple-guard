<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('SG_Custom_Login')) {
    class SG_Custom_Login {
        public function __construct() {
            add_action('init', [$this, 'init']);
            add_filter('site_url', [$this, 'filter_site_url'], 10, 4);
            add_filter('network_site_url', [$this, 'filter_site_url'], 10, 3);
            add_filter('wp_redirect', [$this, 'filter_wp_redirect'], 10, 2);
            add_action('init', [$this, 'handle_redirects']);
        }

        public function init() {
            $slug = $this->get_slug();
            if (!$slug) return;

            // Check if we need to handle the custom URL
            if (isset($_SERVER['REQUEST_URI'])) {
                $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
                $home_path = parse_url(home_url(), PHP_URL_PATH);
                
                // Normalize paths
                $request_path = rtrim($request_path, '/');
                $home_path = rtrim((string)$home_path, '/');
                $slug_path = $home_path . '/' . $slug;

                // Check for exact match or match with action parameter
                // We handle /slug and /slug/action
                if ($request_path === $slug_path || strpos($request_path, $slug_path . '/') === 0) {
                    $this->render_login_page();
                    exit;
                }
            }
        }

        public function get_slug() {
            $opts = get_option('sg_options', []);
            return !empty($opts['custom_login_slug']) ? trim($opts['custom_login_slug']) : false;
        }

        public function filter_site_url($url, $path, $scheme, $blog_id = null) {
            return $this->replace_login_url($url, $scheme);
        }

        public function filter_wp_redirect($location, $status) {
            return $this->replace_login_url($location);
        }

        private function replace_login_url($url, $scheme = null) {
            $slug = $this->get_slug();
            if (!$slug) return $url;

            if (strpos($url, 'wp-login.php') !== false) {
                // Avoid replacing if it's already the custom slug to prevent loops if logic is flawed
                // But simplified: just replace wp-login.php with slug
                $url = str_replace('wp-login.php', $slug, $url);
            }
            return $url;
        }
        
        private function render_login_page() {
            // Globalize variables used by wp-login.php and its template parts
            global $user_login, $error, $action, $interim_login, $user_identity, $wp_error, $redirect_to, $rememberme;
            
            // Set global variables to mimic wp-login.php environment
            $GLOBALS['pagenow'] = 'wp-login.php';
            $_SERVER['PHP_SELF'] = '/wp-login.php';
            
            // Handle actions from URL segments if any
            // e.g. /my-login/logout/ -> $_GET['action'] = 'logout'
            $slug = $this->get_slug();
            $request_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $home_path = rtrim((string)parse_url(home_url(), PHP_URL_PATH), '/');
            $slug_path = $home_path . '/' . $slug;
            
            // If path is longer than slug, extract action
            if (strlen($request_path) > strlen($slug_path)) {
                $sub = substr($request_path, strlen($slug_path) + 1); // +1 for slash
                $parts = explode('/', trim($sub, '/'));
                if (!empty($parts[0])) {
                    $_GET['action'] = sanitize_key($parts[0]);
                }
            }
            
            // Define ABSPATH if not defined (it is), but ensure we can load the file
            // require_once avoids reloading if it was already loaded (it shouldn't be for the content, but wp-settings is)
            
            // We need to look for wp-login.php in the root directory
            $login_file = ABSPATH . 'wp-login.php';
            
            if (file_exists($login_file)) {
                // Determine if we need to bypass headers already sent checks (rare in init)
                require_once $login_file;
            } else {
                wp_die('wp-login.php not found.');
            }
        }

        public function handle_redirects() {
            $slug = $this->get_slug();
            if (!$slug) return;

            // Block direct access to wp-admin for non-logged in users
            if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX')) {
                 // Double check it's not admin-ajax
                 if (strpos($_SERVER['PHP_SELF'], 'admin-ajax.php') !== false) return;

                 wp_safe_redirect(home_url('404'));
                 exit;
            }

            // Block direct access to wp-login.php
            $pagenow = $GLOBALS['pagenow'] ?? '';
            
            // If we are actually ON the real wp-login.php (not our faked one)
            // But wait, render_login_page sets pagenow to wp-login.php.
            // We can check SCRIPT_NAME or direct URI.
            
            // Logic: If request URI contains wp-login.php AND does not match our slug logic
            // (Actually if we are here, we are NOT in the init-exit block, so we are continuing normal load)
            // So if we are here and pagenow is wp-login.php, it means it was accessed directly!
            
            if ($pagenow === 'wp-login.php' && !isset($_REQUEST['interim-login'])) {
                 // Check if valid action
                 $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
                 
                 // If action is postpass, we might allow it? Usually better to redirect everything.
                 if ($action === 'postpass') return;

                 wp_safe_redirect(home_url('404'));
                 exit;
            }
        }
        
        public function activate() {
            $this->init();
            flush_rewrite_rules();
        }
    }
}
