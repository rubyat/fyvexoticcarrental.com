<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Video_Background_CPT {
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('add_meta_boxes', array($this, 'add_meta_boxes'));
        add_action('save_post', array($this, 'save_meta_box_data'));
        add_filter('manage_video_background_posts_columns', array($this, 'add_shortcode_column'));
        add_action('manage_video_background_posts_custom_column', array($this, 'render_shortcode_column'), 10, 2);
    }

    public function register_post_type() {
        $labels = array(
            'name'               => __('Video Backgrounds', 'video-background'),
            'singular_name'      => __('Video Background', 'video-background'),
            'menu_name'          => __('Video BG', 'video-background'),
            'add_new'            => __('Add New', 'video-background'),
            'add_new_item'       => __('Add New Video Background', 'video-background'),
            'edit_item'          => __('Edit Video Background', 'video-background'),
            'new_item'           => __('New Video Background', 'video-background'),
            'view_item'          => __('View Video Background', 'video-background'),
            'search_items'       => __('Search Video Backgrounds', 'video-background'),
            'not_found'          => __('No video backgrounds found', 'video-background'),
            'not_found_in_trash' => __('No video backgrounds found in trash', 'video-background')
        );

        $args = array(
            'labels'              => $labels,
            'public'              => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'query_var'           => true,
            'rewrite'             => array('slug' => 'video-background'),
            'capability_type'     => 'post',
            'has_archive'         => true,
            'hierarchical'        => false,
            'menu_position'       => 20,
            'menu_icon'           => 'dashicons-format-video',
            'supports'            => array('title', 'editor')
        );

        register_post_type('video_background', $args);
    }

    public function add_meta_boxes() {
        add_meta_box(
            'video_background_settings',
            __('Video Background Settings', 'video-background'),
            array($this, 'render_meta_box'),
            'video_background',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post) {
        // Add nonce for security
        wp_nonce_field('video_background_meta_box', 'video_background_meta_box_nonce');

        // Get existing values
        $video_url = get_post_meta($post->ID, '_video_url', true);
        $video_height = get_post_meta($post->ID, '_video_height', true);
        $placeholder_image = get_post_meta($post->ID, '_placeholder_image', true);
        $subtitle = get_post_meta($post->ID, '_subtitle', true);
        $details = get_post_meta($post->ID, '_details', true);
        ?>
        <div class="video-background-meta-box" id="video_background_settings">
            
            <div class="panel">
                <div class="panel_title">Video Settings</div>
                <div class="panel_body">
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="video_url"><?php _e('Video URL:', 'video-background'); ?></label><br>
                                <input type="text" id="video_url" name="video_url" value="<?php echo esc_attr($video_url); ?>" class="widefat">
                                <button type="button" class="button" id="upload_video_button"><?php _e('Upload Video', 'video-background'); ?></button>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="video_height"><?php _e('Video Height (vh):', 'video-background'); ?></label><br>
                                <input type="number" id="video_height" name="video_height" value="<?php echo esc_attr($video_height); ?>" class="small-text" placeholder="80">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="placeholder_image"><?php _e('Placeholder Image URL:', 'video-background'); ?></label><br>
                                <input type="text" id="placeholder_image" name="placeholder_image" value="<?php echo esc_attr($placeholder_image); ?>" class="widefat">
                                <button type="button" class="button" id="upload_image_button"><?php _e('Upload Image', 'video-background'); ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div class="panel">
                <div class="panel_title">Video Title</div>
                <div class="panel_body">
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="subtitle"><?php _e('Subtitle:', 'video-background'); ?></label><br>
                                <input type="text" id="subtitle" name="subtitle" value="<?php echo esc_attr($subtitle); ?>" class="widefat">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="subtitle_font_size"><?php _e('Title Font Size:', 'video-background'); ?></label><br>
                                <input type="text" id="subtitle_font_size" name="subtitle_font_size" value="<?php echo esc_attr(get_post_meta($post->ID, '_subtitle_font_size', true)); ?>" class="widefat" placeholder="80">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="subtitle_font_weight"><?php _e('Title Font Weight:', 'video-background'); ?></label><br>
                                <input type="text" id="subtitle_font_weight" name="subtitle_font_weight" value="<?php echo esc_attr(get_post_meta($post->ID, '_subtitle_font_weight', true)); ?>" class="widefat" placeholder="700">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="subtitle_font_color"><?php _e('Title Font Color:', 'video-background'); ?></label><br>
                                <input type="text" id="subtitle_font_color" name="subtitle_font_color" value="<?php echo esc_attr(get_post_meta($post->ID, '_subtitle_font_color', true)); ?>" class="widefat" placeholder="#ffffff">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="subtitle_font_margin_bottom"><?php _e('Title Font Margin Bottom:', 'video-background'); ?></label><br>
                                <input type="text" id="subtitle_font_margin_bottom" name="subtitle_font_margin_bottom" value="<?php echo esc_attr(get_post_meta($post->ID, '_subtitle_font_margin_bottom', true)); ?>" class="widefat" placeholder="20">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="subtitle_font_margin_top"><?php _e('Title Font Margin Top:', 'video-background'); ?></label><br>
                                <input type="text" id="subtitle_font_margin_top" name="subtitle_font_margin_top" value="<?php echo esc_attr(get_post_meta($post->ID, '_subtitle_font_margin_top', true)); ?>" class="widefat" placeholder="20">
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>


            <div class="panel">
                <div class="panel_title">Video Subtitle</div>
                <div class="panel_body">
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="details"><?php _e('Details:', 'video-background'); ?></label><br>
                                <?php wp_editor($details, 'details', array('textarea_name' => 'details', 'textarea_rows' => 4)); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="video_details_font_size"><?php _e('Video details font size:', 'video-background'); ?></label><br>
                                <input type="text" id="video_details_font_size" name="video_details_font_size" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_details_font_size', true)); ?>" class="widefat" placeholder="44">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_details_font_weight"><?php _e('Video details font Weight:', 'video-background'); ?></label><br>
                                <input type="text" id="video_details_font_weight" name="video_details_font_weight" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_details_font_weight', true)); ?>" class="widefat" placeholder="300">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_details_font_lineHeight"><?php _e('Video details font Line Height:', 'video-background'); ?></label><br>
                                <input type="text" id="video_details_font_lineHeight" name="video_details_font_lineHeight" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_details_font_lineHeight', true)); ?>" class="widefat" placeholder="1.6">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_details_font_maxwidth"><?php _e('Video details font Maxwidth:', 'video-background'); ?></label><br>
                                <input type="text" id="video_details_font_maxwidth" name="video_details_font_maxwidth" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_details_font_maxwidth', true)); ?>" class="widefat" placeholder="800">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_details_margin_bottom"><?php _e('Video details Margin Bottom:', 'video-background'); ?></label><br>
                                <input type="text" id="video_details_margin_bottom" name="video_details_margin_bottom" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_details_margin_bottom', true)); ?>" class="widefat" placeholder="20">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_details_margin_top"><?php _e('Video details Margin Top:', 'video-background'); ?></label><br>
                                <input type="text" id="video_details_margin_top" name="video_details_margin_top" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_details_margin_top', true)); ?>" class="widefat" placeholder="20">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel">
                <div class="panel_title">Video Form</div>
                <div class="panel_body">
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="embed_shortcode"><?php _e('Embed Shortcode:', 'video-background'); ?></label><br>
                                <input type="text" id="embed_shortcode" name="embed_shortcode" value="<?php echo esc_attr(get_post_meta($post->ID, '_embed_shortcode', true)); ?>" class="widefat" placeholder="[your_shortcode]">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="form_style"><?php _e('Form Style:', 'video-background'); ?></label><br>
                                <select id="form_style" name="form_style" class="widefat">
                                    <option value="modern" <?php selected(get_post_meta($post->ID, '_form_style', true), 'modern'); ?>>Modern</option>
                                    <option value="classic" <?php selected(get_post_meta($post->ID, '_form_style', true), 'classic'); ?>>Classic</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <div>
                                <label for="form2_videobg_margin_bottom"><?php _e('Form2 Margin Bottom:', 'video-background'); ?></label><br>
                                <input type="text" id="form2_videobg_margin_bottom" name="form2_videobg_margin_bottom" value="<?php echo esc_attr(get_post_meta($post->ID, '_form2_videobg_margin_bottom', true)); ?>" class="widefat" placeholder="0">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="form2_videobg_margin_top"><?php _e('Form2 Margin Top:', 'video-background'); ?></label><br>
                                <input type="text" id="form2_videobg_margin_top" name="form2_videobg_margin_top" value="<?php echo esc_attr(get_post_meta($post->ID, '_form2_videobg_margin_top', true)); ?>" class="widefat" placeholder="0">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_background_form_bg"><?php _e('Video Background From BG:', 'video-background'); ?></label><br>
                                <input type="text" id="video_background_form_bg" name="video_background_form_bg" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_background_form_bg', true)); ?>" class="widefat" placeholder="rgba(0,0,0,0.5)">
                            </div>
                        </div>
                        <div class="col">
                            <div>
                                <label for="video_background_form_padding"><?php _e('Video Background From Padding:', 'video-background'); ?></label><br>
                                <input type="text" id="video_background_form_padding" name="video_background_form_padding" value="<?php echo esc_attr(get_post_meta($post->ID, '_video_background_form_padding', true)); ?>" class="widefat" placeholder="26px 0 0px">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            

            

            

            

            

           


            
        </div>
        <?php
    }

    public function add_shortcode_column($columns) {
        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key === 'title') {
                $new_columns['shortcode'] = __('Shortcode', 'video-background');
            }
        }
        return $new_columns;
    }

    public function render_shortcode_column($column, $post_id) {
        if ($column === 'shortcode') {
            $shortcode = sprintf('[video_background id="%d"]', $post_id);
            echo '<div class="shortcode-copy-container">';
            echo '<code class="shortcode-text">' . esc_html($shortcode) . '</code>';
            echo '<button type="button" class="button shortcode-copy-button" data-shortcode="' . esc_attr($shortcode) . '">';
            echo '<span class="dashicons dashicons-clipboard"></span>';
            echo '</button>';
            echo '</div>';
        }
    }

    public function save_meta_box_data($post_id) {
        // Check if nonce is set
        if (!isset($_POST['video_background_meta_box_nonce'])) {
            return;
        }

        // Verify nonce
        if (!wp_verify_nonce($_POST['video_background_meta_box_nonce'], 'video_background_meta_box')) {
            return;
        }

        // If this is an autosave, don't do anything
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        // Check user permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        // Save meta box data
        $fields = array(
            '_video_url' => 'video_url',
            '_video_height' => 'video_height',
            '_placeholder_image' => 'placeholder_image',
            '_subtitle' => 'subtitle',
            '_subtitle_font_size' => 'subtitle_font_size',
            '_subtitle_font_weight' => 'subtitle_font_weight',
            '_subtitle_font_color' => 'subtitle_font_color',
            '_subtitle_font_margin_bottom' => 'subtitle_font_margin_bottom',
            '_video_details_font_size' => 'video_details_font_size',
            '_video_details_font_weight' => 'video_details_font_weight',
            '_video_details_font_lineHeight' => 'video_details_font_lineHeight',
            '_video_details_font_maxwidth' => 'video_details_font_maxwidth',
            '_video_details_margin_bottom' => 'video_details_margin_bottom',
            '_video_details_margin_top' => 'video_details_margin_top',
            '_video_details_margin_top' => 'video_details_margin_top',
            '_form2_videobg_margin_top' => 'form2_videobg_margin_top',
            '_form2_videobg_margin_bottom' => 'form2_videobg_margin_bottom',
            '_video_background_form_padding' => 'video_background_form_padding',
            '_embed_shortcode' => 'embed_shortcode',
            '_form_style' => 'form_style',
            '_details' => 'details'
        );

        foreach ($fields as $meta_key => $post_key) {
            if (isset($_POST[$post_key])) {
                update_post_meta($post_id, $meta_key, sanitize_text_field($_POST[$post_key]));
            }
        }

        // Save checkbox
        $show_on_mobile = isset($_POST['show_on_mobile']) ? '1' : '0';
        update_post_meta($post_id, '_show_on_mobile', $show_on_mobile);
    }
}