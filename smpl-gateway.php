<?php
/*
Plugin Name: Simple Monobank Gateway for WooCommerce
Description: Simple payment gateway for Monobank.
Version: 1.0.0
Author: Yevhen Bilonozhko
License: GPL2+
Text Domain: smpl-mono-gateway
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit;
}

// Load modules
require_once plugin_dir_path(__FILE__) . 'includes/class-gateway.php';
require_once plugin_dir_path(__FILE__) . 'includes/webhook.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/cron.php';