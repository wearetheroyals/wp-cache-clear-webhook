<?php

/**
 * Plugin Name: Cache Clear Webhook
 * Description: You can access the plugin's settings in WordPress by accessing the 'Settings' panel on the left hand side of the dashboard and then clicking 'Cache Clear Webhooks'.
 * Author: The Royals (Fork from Christopher Geary)
 * Author URI: https://theroyals.com.au
 * Version: 0.2.3
 */

if (!defined('ABSPATH')) {
    exit;
}

define('THEROYALS_CACHE_CLEAR_WEBHOOK_FILE', __FILE__);
define('THEROYALS_CACHE_CLEAR_WEBHOOK_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('THEROYALS_CACHE_CLEAR_WEBHOOK_URL', untrailingslashit(plugin_dir_url(__FILE__)));

require_once (THEROYALS_CACHE_CLEAR_WEBHOOK_PATH.'/src/App.php');

Theroyals\CacheClearWebhook\App::instance();
