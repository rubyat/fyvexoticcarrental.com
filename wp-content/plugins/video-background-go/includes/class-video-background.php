<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Video_Background {
    private static $instance = null;

    public function __construct() {
        // Initialize plugin components
        add_action('init', array($this, 'init_components'));
        
        // Register scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        add_action('wp_enqueue_scripts', array($this, 'frontend_enqueue_scripts'));
    }

    public function init() {
        // Initialize custom post type
        new Video_Background_CPT();
        
        // Initialize shortcode
        new Video_Background_Shortcode();
    }

    public function init_components() {
        // Add any additional initialization here
    }

    public function admin_enqueue_scripts() {
        wp_enqueue_style('video-background-admin', VB_PLUGIN_URL . 'assets/css/admin.css', array(), VB_VERSION);
        wp_enqueue_script('video-background-admin', VB_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), VB_VERSION, true);
        
        // Add media uploader scripts
        wp_enqueue_media();
    }

    public function frontend_enqueue_scripts() {
        wp_enqueue_style('video-background', VB_PLUGIN_URL . 'assets/css/video-background.css', array(), VB_VERSION);
        wp_enqueue_script('video-background', VB_PLUGIN_URL . 'assets/js/video-background.js', array('jquery'), VB_VERSION, true);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}