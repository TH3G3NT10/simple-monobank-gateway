=== YevhenB Payments with Mono for WooCommerce ===

Contributors: evgenyb
Tags: monobank, mono, woocommerce, payment gateway, ukraine
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Lightweight payment gateway for WooCommerce that connects to the Monobank acquiring API for online payments.

== Description ==

YevhenB Payments with Mono for WooCommerce adds support for online card payments through the Monobank acquiring API.

Features:

* Payment gateway for WooCommerce
* Invoice creation through the Monobank acquiring API
* Automatic order status updates
* Manual payment status check
* Webhook support with signature verification
* Payment history panel
* Multilingual support
* Ukrainian localization included

This plugin is designed to be lightweight and easy to configure.

This plugin is not affiliated with, endorsed by, or officially supported by Monobank, mono, Universal Bank, or WooCommerce.

== External services ==

This plugin connects to the Monobank acquiring API to create payment invoices, check invoice status, and verify webhook signatures.

The Monobank acquiring API is used only when this payment method is enabled and used by a store owner or customer.

When a customer chooses this payment method during checkout, the plugin sends payment data to Monobank to create a payment invoice. This may include the order amount, currency, WooCommerce order ID as a payment reference, redirect URL, webhook URL, and the merchant API token configured by the store owner.

When the store administrator manually checks payment status, or when the scheduled fallback status check runs, the plugin sends the Monobank invoice ID to Monobank to retrieve the current invoice status.

When Monobank sends a webhook to the store, the plugin requests Monobank's public key from the Monobank API to verify the webhook signature.

This service is provided by Monobank / Universal Bank.

Service information and documents:
https://monobank.ua/en/documents

API documentation:
https://monobank.ua/api-docs/acquiring

Developer API documentation:
https://api.monobank.ua/docs/acquiring.html

Security information:
https://monobank.ua/en/security

== Installation ==

1. Upload the plugin to `/wp-content/plugins/`
2. Activate the plugin
3. Go to WooCommerce → Settings → Payments
4. Enable "YevhenB Payments with Mono"
5. Enter your Monobank API token
6. Save settings

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =

Yes. WooCommerce must be installed and activated.

= Is this an official Monobank plugin? =

No. This plugin is not affiliated with, endorsed by, or officially supported by Monobank, mono, Universal Bank, or WooCommerce.

= Does this plugin support Ukrainian language? =

Yes. Ukrainian localization is included.

= Does it support Google Pay and Apple Pay? =

If these payment methods are available through Monobank acquiring for your merchant account, they may be available on the Monobank payment page.

== Changelog ==

= 1.0.0 =

* Initial public release
* Monobank acquiring API support
* Webhook support with signature verification
* Payment history panel
* Manual payment status check
* Localization support

== Upgrade Notice ==

= 1.0.0 =

Initial release.
