# wp-guad
Protect wp login for wordpress

Login Protection (Fail-Ban / Brute Force Protection)
- Logs failed login attempts by IP.
- Auto-Block: Blocks an IP after reaching a certain failure threshold (default: 3 attempts).
- Block Duration: Sets how long an IP is blocked (default: 4 hours).
- IP Whitelist: List of IPs that will never be blocked (for admins).
- Reset on Success: Option to reset the failure count if a user successfully logs in.
- Ban Management: Admins can view a list of banned IPs and perform manual unbans.

Cloudflare Turnstile Integration (Captcha)
- Adds Turnstile verification to the Login, Register, and Lost Password forms.
- Supports Site Key and Secret Key settings.
- Display mode: Always, Invisible, or Fallback.

Custom Login URL
- Change the default WordPress login URL (e.g., from wp-login.php to a custom slug like /member-login).
- Prevent direct access to wp-login.php (redirect to home if accessed directly without custom slug).
