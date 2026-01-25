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

== Changelog ==

= 1.3.0 =
* Security: Fixed all WordPress.org validation errors and warnings
* Security: Added proper input sanitization with wp_unslash() and sanitize_text_field()
* Security: Added proper output escaping with esc_html(), esc_url(), esc_js()
* I18n: Added 'simple-guard' text domain to all translation functions
* I18n: Added translators comments for placeholder strings
* Code Quality: Replaced date() with gmdate() for timezone safety
* Code Quality: Added nonce verification comments for WordPress core forms
* Code Quality: Improved $_SERVER and $_POST handling
* Documentation: Added comment explaining Cloudflare external script requirement
* Compatibility: Fully compliant with WordPress.org plugin guidelines

= 1.2.0 =
* Enhancement: Improved custom login URL handling
* Enhancement: Better redirect logic for wp-admin access
* Security: Enhanced nonce verification
* Performance: Optimized database queries

= 1.1.0 =
* Feature: Added IP whitelist functionality
* Feature: Added reset on success option
* Enhancement: Improved ban management interface
* Bug Fix: Fixed timezone issues in ban expiration

= 1.0.2 =
* Enhancement: Hides wp-login.php and wp-admin with 404 redirect when custom login URL is active
* Security: Prevents unauthorized admin access for guest users by redirecting to 404

= 1.0.1 =
* Fixed: Layout shift (CLS) on login page and homepage by reserving space for Turnstile widget
* Fixed: 0.5s flicker issue on page load

= 1.0.0 =
* Initial release
* Fail-ban protection
* Cloudflare Turnstile integration
* Custom login URL
* Admin dashboard

== Upgrade Notice ==

= 1.3.0 =
Major security and code quality update. Fixes all WordPress.org validation errors. Recommended for all users before submitting to WordPress.org.

== Notes ==

* Turnstile requires valid sitekey & secret from Cloudflare
* Add your admin IP to whitelist to avoid lockout
* Custom login URL feature may conflict with other login customization plugins
* Database table wp_sg_bans is created on activation
