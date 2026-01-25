<?php
if (!defined('ABSPATH')) exit;

if (!class_exists('SG_Ban_Manager')) {
    class SG_Ban_Manager {
        private $table;

        public function __construct() {
            global $wpdb;
            $this->table = $wpdb->prefix . 'sg_bans';
        }

        public function ban_ip($ip, $hours = 1, $reason = 'too_many_failures'){
            global $wpdb;
            $until = gmdate('Y-m-d H:i:s', time() + ($hours * 3600));
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE ip=%s", $ip));
            if ($row){
                $wpdb->update($this->table, ['banned_until' => $until, 'reason' => $reason], ['id' => $row->id]);
            } else {
                $wpdb->insert($this->table, ['ip' => $ip, 'fail_count' => 0, 'banned_until' => $until, 'reason' => $reason]);
            }
        }

        public function increment_failure($ip, $max_failures, $ban_hours) {
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE ip=%s", $ip));
            if ($row) {
                $count = $row->fail_count + 1;
                if ($count >= $max_failures) {
                    $this->ban_ip($ip, $ban_hours, 'too_many_failures');
                } else {
                    $wpdb->update($this->table, ['fail_count' => $count], ['id' => $row->id]);
                }
            } else {
                // First failure
                if ($max_failures <= 1) {
                     $this->ban_ip($ip, $ban_hours, 'too_many_failures');
                } else {
                     $wpdb->insert($this->table, ['ip' => $ip, 'fail_count' => 1]);
                }
            }
        }

        public function is_banned($ip){
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$this->table} WHERE ip=%s", $ip));
            if (!$row) return false;
            if (empty($row->banned_until)) return false;
            $until = strtotime($row->banned_until);
            if (time() >= $until){
                // expired -> clear ban
                $this->unban_ip($ip);
                return false;
            }
            return true;
        }


        public function get_banned_until($ip){
            global $wpdb;
            $row = $wpdb->get_row($wpdb->prepare("SELECT banned_until FROM {$this->table} WHERE ip=%s", $ip));
            return $row ? $row->banned_until : null;
        }


        public function unban_ip($ip){
            global $wpdb;
            $wpdb->delete($this->table, ['ip' => $ip]);
        }


        public function reset_fail_count($ip){
            global $wpdb;
            $wpdb->query($wpdb->prepare("UPDATE {$this->table} SET fail_count=0 WHERE ip=%s", $ip));
        }


        public function get_all_bans(){
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT 500");
        }
    }
}