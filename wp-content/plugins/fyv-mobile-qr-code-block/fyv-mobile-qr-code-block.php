<?php
/**
 * Plugin Name: FYV Mobile QR Code Block
 * Description: Displays a dismissible QR code callout with a QR image, link, and text. Includes enable toggle and an option to show only on the homepage.
 * Version: 1.0.0
 * Author: FYV
 * License: GPLv2 or later
 * Text Domain: fyv-mobile-qr-code-block
 * Domain Path: /languages
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('plugins_loaded', function () {
    load_plugin_textdomain('fyv-mobile-qr-code-block', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    require_once plugin_dir_path(__FILE__) . 'includes/class-fyv-mobile-qr-code-block.php';

    \FYV_Mobile_QR_Code_Block::instance();
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
    $url = admin_url('options-general.php?page=fyv-mobile-qr-code-block');
    $links[] = '<a href="' . esc_url($url) . '">' . esc_html__('Settings', 'fyv-mobile-qr-code-block') . '</a>';
    return $links;
});
