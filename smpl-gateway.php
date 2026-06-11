<?php
/*
Plugin Name: YevhenB Payments with Mono for WooCommerce
Description: Lightweight payment gateway for WooCommerce that connects to the Monobank acquiring API for online payments.
Version: 1.0.0
Author: Yevhen Bilonozhko (yevhenb)
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: yevhenb-payments-with-mono-for-woocommerce
Domain Path: /languages
Requires Plugins: woocommerce
*/

if (!defined('ABSPATH')) {
    exit;
}

// Load modules
require_once plugin_dir_path(__FILE__) . 'includes/class-gateway.php';
require_once plugin_dir_path(__FILE__) . 'includes/webhook.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/cron.php';