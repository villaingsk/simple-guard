Fail-ban + Cloudflare Turnstile integration for WordPress login/register/lostpassword + custom login page.

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

WordPress.org.

== Notes ==

* Turnstile requires valid sitekey & secret from Cloudflare
* Add your admin IP to whitelist to avoid lockout
* Custom login URL feature may conflict with other login customization plugins
* Database table wp_sg_bans is created on activation
