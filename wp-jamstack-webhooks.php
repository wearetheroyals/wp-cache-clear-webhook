<?php

/**
 * Plugin Name: JAMstack Webhooks
 * Description: A WordPress plugin for JAMstack webhooks on Netlify (and other platforms).
 * Author: The Royals (Fork from Christopher Geary)
 * Author URI: https://theroyals.com.au
 * Version: 0.2.3
 */

if (!defined('ABSPATH')) {
    exit;
}

define('THEROYALS_JAMSTACK_WEBHOOK_FILE', __FILE__);
define('THEROYALS_JAMSTACK_WEBHOOK_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('THEROYALS_JAMSTACK_WEBHOOK_URL', untrailingslashit(plugin_dir_url(__FILE__)));

require_once (THEROYALS_JAMSTACK_WEBHOOK_PATH.'/src/App.php');

Theroyals\JAMstackWebhook\App::instance();
