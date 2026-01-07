<?php
if (!defined('ABSPATH')) exit;


class SG_Turnstile {
    public function __construct(){
        // nothing heavy
    }


    // Validate token server-side using secret
    public function verify($token){
        $opts = get_option('sg_options', []);
        $secret = $opts['turnstile_secret'] ?? '';
        if (empty($secret)) return false;


        $response = wp_remote_post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
            'body' => [
                'secret' => $secret,
                'response' => $token,
                'remoteip' => sg_get_client_ip(),
            ],
            'timeout' => 10,
        ]);


        if (is_wp_error($response)) return false;
        $body = wp_remote_retrieve_body($response);
        $json = json_decode($body, true);
        return !empty($json['success']);
    }
}