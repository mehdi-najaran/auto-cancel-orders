<?php
/*
Plugin Name: Auto Cancel Orders
Description: Automatically cancels WooCommerce orders after the specified duration in "pending payment" status. Provides an option for the admin to manually trigger the cancellation process for existing orders.
Version: 1.0.0
Author: Mehdi Najaran
Author URI: https://mehdi-najaran.ir/
Text Domain: auto-cancel-orders
Domain Path: /languages
*/

// Include the cron and admin files
require_once plugin_dir_path(__FILE__) . 'includes/cron.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin.php';

// Load plugin text domain for translations
add_action('plugins_loaded', 'auto_cancel_orders_load_textdomain');

function auto_cancel_orders_load_textdomain()
{
    load_plugin_textdomain('auto-cancel-orders', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
