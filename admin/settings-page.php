<?php
if (!defined('ABSPATH')) exit;
?>
<div class="wrap">
    <h1>Simple Guard Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('sg_options_group'); ?>
        
        <table class="form-table">
            <tr>
                <th scope="row">Max Login Failures</th>
                <td>
                    <input type="number" name="sg_options[max_login_failures]" value="<?php echo esc_attr($opts['max_login_failures'] ?? 3); ?>" class="small-text">
                    <p class="description">Number of failures allowed before ban.</p>
                </td>
            </tr>
            <tr>
                <th scope="row">Ban Duration (Hours)</th>
                <td>
                    <input type="number" name="sg_options[ban_duration_hours]" value="<?php echo esc_attr($opts['ban_duration_hours'] ?? 4); ?>" class="small-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Reset on Success</th>
                <td>
                    <label>
                        <input type="checkbox" name="sg_options[reset_on_success]" value="1" <?php checked(!empty($opts['reset_on_success'])); ?>>
                        Reset failure count after successful login.
                    </label>
                </td>
            </tr>
        </table>

        <hr>

        <h2>Custom Login URL</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Login Slug</th>
                <td>
                    <code><?php echo home_url('/'); ?></code>
                    <input type="text" name="sg_options[custom_login_slug]" value="<?php echo esc_attr($opts['custom_login_slug'] ?? ''); ?>" class="regular-text" placeholder="my-secret-login">
                    <p class="description">Enter a custom slug to hide <code>wp-login.php</code>. Leave empty to disable.<br>
                    <strong>Important:</strong> If you get locked out, rename the plugin folder via FTP to disable it.</p>
                </td>
            </tr>
        </table>

        <hr>

        <h2>Cloudflare Turnstile</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Enable Turnstile</th>
                <td>
                    <label>
                        <input type="checkbox" name="sg_options[turnstile_enabled]" value="1" <?php checked(!empty($opts['turnstile_enabled'])); ?>>
                        Enable on Login / Register / Lost Password
                    </label>
                </td>
            </tr>
            <tr>
                <th scope="row">Site Key</th>
                <td>
                    <input type="text" name="sg_options[turnstile_sitekey]" value="<?php echo esc_attr($opts['turnstile_sitekey'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Secret Key</th>
                <td>
                    <input type="text" name="sg_options[turnstile_secret]" value="<?php echo esc_attr($opts['turnstile_secret'] ?? ''); ?>" class="regular-text">
                </td>
            </tr>
            <tr>
                <th scope="row">Mode</th>
                <td>
                    <select name="sg_options[turnstile_mode]">
                        <option value="always" <?php selected(($opts['turnstile_mode'] ?? 'always'), 'always'); ?>>Always Visible</option>
                        <option value="invisible" <?php selected(($opts['turnstile_mode'] ?? ''), 'invisible'); ?>>Invisible</option>
                    </select>
                </td>
            </tr>
        </table>

        <hr>

        <h2>IP Management</h2>
        <table class="form-table">
            <tr>
                <th scope="row">Whitelist IPs</th>
                <td>
                    <textarea name="sg_options[whitelist_ips]" rows="4" cols="60"><?php echo esc_textarea($opts['whitelist_ips'] ?? ''); ?></textarea>
                    <p class="description">One IP per line. Add your office/admin IP to avoid locking yourself out.</p>
                </td>
            </tr>
        </table>

        <?php submit_button('Save Settings'); ?>
    </form>

    <hr>

    <h2>Current Banned IPs</h2>
    <table class="widefat fixed striped">
        <thead>
            <tr>
                <th>IP</th>
                <th>Reason</th>
                <th>Fail Count</th>
                <th>Banned Until</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($banned) : ?>
                <?php foreach($banned as $row) : ?>
                    <?php $until = $row->banned_until ? $row->banned_until : '-'; ?>
                    <tr>
                        <td><?php echo esc_html($row->ip); ?></td>
                        <td><?php echo esc_html($row->reason); ?></td>
                        <td><?php echo esc_html($row->fail_count); ?></td>
                        <td><?php echo esc_html($until); ?></td>
                        <td><button class="button sg-unban" data-ip="<?php echo esc_attr($row->ip); ?>">Unban</button></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr><td colspan="5">No bans found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
(function(){
    const buttons = document.querySelectorAll('.sg-unban');
    buttons.forEach(btn => btn.addEventListener('click', function(e){
        e.preventDefault();
        if (!confirm('Unban IP ' + this.dataset.ip + '?')) return;
        const data = new FormData();
        data.append('action','sg_unban_ip');
        data.append('ip', this.dataset.ip);
        data.append('_wpnonce','<?php echo wp_create_nonce('sg_admin'); ?>');
        fetch(ajaxurl, { method:'POST', body:data}).then(r=>r.json()).then(j=>{
            if (j.success) location.reload(); else alert('Error: ' + (j.data || 'Unknown'));
        });
    }));
})();
</script>