== Description ==

Simple Guard provides comprehensive login security for WordPress with fail-ban protection and Cloudflare Turnstile integration.

**Features:**

* **Fail-Ban Protection:** Automatically ban IPs after failed login attempts
* **Cloudflare Turnstile:** CAPTCHA protection for login, registration, and password reset
* **Custom Login URL:** Hide wp-login.php with custom URL slug
* **IP Whitelist:** Prevent lockout for trusted IPs
* **Admin Dashboard:** View and manage banned IPs
* **Flexible Configuration:** Customize ban duration, failure threshold, and more

== Installation ==

1. Upload folder to wp-content/plugins
2. Activate plugin
3. Go to Settings → Simple Guard
4. Configure Turnstile Site Key & Secret (get from Cloudflare)
5. Set max login failures and ban duration
6. Add your admin IP to whitelist to avoid lockout

== Frequently Asked Questions ==

= How do I get Cloudflare Turnstile keys? =

1. Sign up at cloudflare.com
2. Go to Turnstile dashboard
3. Create a new site
4. Copy Site Key and Secret Key
5. Paste into Simple Guard settings

= What if I get locked out? =

1. Add your IP to whitelist before testing
2. If locked out, rename plugin folder via FTP to disable
3. Or manually delete ban from database: wp_sg_bans table

= Does this work with custom login plugins? =

Yes, Simple Guard includes its own custom login URL feature. Disable other custom login plugins to avoid conflicts.

== Changelog ==

= 1.3.1 =
* Security: Fixed SQL injection warnings in Ban Manager
* Security: Fixed direct database query warnings
* Security: Improved nonce verification handling in login protection
* Code Quality: Switched to wp_parse_url for better compatibility
* Code Quality: Fixed enqueued resource warnings

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
