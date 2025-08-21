<?php
/**
 * Plugin Name: Video Background GO
 * Plugin URI: #
 * Description: A plugin to create and manage video backgrounds with shortcode support
 * Version: 1.0.3
 * Author: Video Background
 * Author URI: #
 * Text Domain: video-background-go
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define('VB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('VB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('VB_VERSION', '1.0.3');

// Include required files
require_once VB_PLUGIN_DIR . 'includes/class-video-background.php';
require_once VB_PLUGIN_DIR . 'includes/class-video-background-cpt.php';
require_once VB_PLUGIN_DIR . 'includes/class-video-background-shortcode.php';

// Initialize the plugin
function vb_init() {
    $plugin = new Video_Background();
    $plugin->init();
}
add_action('plugins_loaded', 'vb_init');

// Activation hook
register_activation_hook(__FILE__, 'vb_activate');
function vb_activate() {
    // Activation tasks if needed
    flush_rewrite_rules();
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'vb_deactivate');
function vb_deactivate() {
    // Cleanup tasks if needed
    flush_rewrite_rules();
}