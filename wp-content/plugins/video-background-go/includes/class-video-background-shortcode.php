<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Video_Background_Shortcode {
    public function __construct() {
        add_shortcode('video_background', array($this, 'render_shortcode'));
    }

    public function render_shortcode($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'video_background'
        );

        if (empty($atts['id'])) {
            return '';
        }

        $post = get_post($atts['id']);
        if (!$post || $post->post_type !== 'video_background') {
            return '';
        }

        // Get meta values
        $video_url = get_post_meta($post->ID, '_video_url', true);
        $video_height = get_post_meta($post->ID, '_video_height', true);
        $placeholder_image = get_post_meta($post->ID, '_placeholder_image', true);
        $show_on_mobile = get_post_meta($post->ID, '_show_on_mobile', true);
        $subtitle = get_post_meta($post->ID, '_subtitle', true);
        $subtitle_font_size = get_post_meta($post->ID, '_subtitle_font_size', true);
        $subtitle_font_weight = get_post_meta($post->ID, '_subtitle_font_weight', true);
        $subtitle_font_color = get_post_meta($post->ID, '_subtitle_font_color', true);
        $subtitle_font_margin_bottom = get_post_meta($post->ID, '_subtitle_font_margin_bottom', true);
        $subtitle_font_margin_top = get_post_meta($post->ID, '_subtitle_font_margin_top', true);
        $video_details_font_size = get_post_meta($post->ID, '_video_details_font_size', true);
        $video_details_font_weight = get_post_meta($post->ID, '_video_details_font_weight', true);
        $video_details_font_lineHeight = get_post_meta($post->ID, '_video_details_font_lineHeight', true);
        $video_details_font_maxwidth = get_post_meta($post->ID, '_video_details_font_maxwidth', true);
        $video_details_margin_bottom = get_post_meta($post->ID, '_video_details_margin_bottom', true);
        $video_details_margin_top = get_post_meta($post->ID, '_video_details_margin_top', true);
        $video_background_form_bg = get_post_meta($post->ID, '_video_background_form_bg', true);
        $video_background_form_padding = get_post_meta($post->ID, '_video_background_form_padding', true);
        $embed_shortcode = get_post_meta($post->ID, '_embed_shortcode', true);
        $form2_videobg_margin_top = get_post_meta($post->ID, '_form2_videobg_margin_top', true);
        $form2_videobg_margin_bottom = get_post_meta($post->ID, '_form2_videobg_margin_bottom', true);
        $form_style = get_post_meta($post->ID, '_form_style', true);
        $details = get_post_meta($post->ID, '_details', true);

        // Set default height if not specified
        $height = !empty($video_height) ? $video_height : '80';

        // Start output buffering
        ob_start();
        ?>
        <style>
            .video-background-container-<?php echo esc_attr($post->ID); ?> {
                height: <?php echo esc_attr($height); ?>vh;
            }
            .video-subtitle {
                font-size: <?php echo (!empty($subtitle_font_size)) ? $subtitle_font_size : '80';?>px;
                margin-top: <?php echo (!empty($subtitle_font_margin_top)) ? $subtitle_font_margin_top : '20';?>px;
                margin-bottom: <?php echo (!empty($subtitle_font_margin_bottom)) ? $subtitle_font_margin_bottom : '20';?>px;
                color: <?php echo (!empty($subtitle_font_color)) ? $subtitle_font_color : '#ffffff';?>;
                font-weight: <?php echo (!empty($subtitle_font_weight)) ? $subtitle_font_weight : '700';?>;
            }

            .video-details {
                max-width: <?php echo (!empty($video_details_font_maxwidth)) ? $video_details_font_maxwidth : '800';?>px;
                font-size: <?php echo (!empty($video_details_font_size)) ? $video_details_font_size : '44';?>px;
                line-height: <?php echo (!empty($video_details_font_lineHeight)) ? $video_details_font_lineHeight : '1.6';?>;
                font-weight: <?php echo (!empty($video_details_font_weight)) ? $video_details_font_weight : '300';?>;
                margin-top: <?php echo (!empty($video_details_margin_top)) ? $video_details_margin_top : '20';?>px;
                margin-bottom: <?php echo (!empty($video_details_margin_bottom)) ? $video_details_margin_bottom : '20';?>px;
            }

            .video-background-container .video-embed-shortcode {
                background: <?php echo (!empty($video_background_form_bg)) ? $video_background_form_bg : 'rgba(0,0,0,0.5)';?>;
                padding: <?php echo (!empty($video_background_form_padding)) ? $video_background_form_padding : '26px 0 0px'?>;
            }

            .content_form2_videobg {
                margin-top: <?php echo (!empty($form2_videobg_margin_top)) ? $form2_videobg_margin_top : '0';?>px;
                margin-bottom: <?php echo (!empty($form2_videobg_margin_bottom)) ? $form2_videobg_margin_bottom : '0';?>px;
            }


            .video-content .content_form2_videobg .video-embed-shortcode form.wpforms-form {
                display: flex !important;
                width: 100%;
                justify-content: space-between;
                align-items: center;
                padding: 0 !important;
            }

            @media screen and (max-width: 768px) {
                .video-background-container-<?php echo esc_attr($post->ID); ?> {
                    height: auto;
                }
            }


        </style>
        <div class="video-background-container video-background-container-<?php echo esc_attr($post->ID); ?>">
            
            <video style="display:none" class="video-background" preload="auto" autoplay loop muted playsinline <?php echo !empty($placeholder_image) ? 'poster="' . esc_attr($placeholder_image) . '"' : ''; ?>>
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
            </video>

            <video id="background-video" class="video-background" preload="auto" autoplay loop muted playsinline <?php echo !empty($placeholder_image) ? 'poster="' . esc_attr($placeholder_image) . '"' : ''; ?>>
                <source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
            </video>
            <div class="video-content">
                <!-- <?php if (!empty($post->post_title)) : ?>
                <h2 class="video-title"><?php echo esc_html($post->post_title); ?></h2>
                <?php endif; ?> -->
                <div class="title_area_panel_video">
                    <?php if (!empty($subtitle)) : ?>
                    <h3 class="video-subtitle"><?php echo esc_html($subtitle); ?></h3>
                    <?php endif; ?>
                    <?php if (!empty($details)) : ?>
                        <div class="video-details"><?php echo wp_kses_post($details); ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($embed_shortcode) && $form_style == 'modern'){?>
                    <div class="content_form2_videobg search-bar">
                         <?php if (!empty($embed_shortcode)) : ?>
                            <div class="video-embed-shortcode">
                                <?php echo do_shortcode(wp_kses_post($embed_shortcode)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="review_add">
                        <?php echo do_shortcode(wp_kses_post($post->post_content)); ?>
                    </div>
                    <?php }?>
                </div>
                

                <?php if (!empty($embed_shortcode) && $form_style == 'classic') : ?>
                    <div class="form_admin_">
                        <div class="video-embed-shortcode">
                        <?php echo do_shortcode(wp_kses_post($embed_shortcode)); ?>
                        </div>
                        <div class="review_add">
                            <?php echo do_shortcode(wp_kses_post($post->post_content)); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        

        

        <script>
        // Function to detect mobile devices
        function isMobileDevice() {
            return /Mobi|Android/i.test(navigator.userAgent);
        }

        // Check if it's a mobile device
        if (isMobileDevice()) {
            var video = document.getElementById('background-video-go');

            // Try to play the video when the page loads
            video.play().catch(function(error) {
            // If autoplay fails, force play with user interaction (click, scroll, etc.)
            console.log("Autoplay failed, waiting for user interaction to play the video");

            // Wait for the user to interact (like scrolling or clicking)
            window.addEventListener('click', function() {
                video.play().catch(function(err) {
                console.error('Failed to play video after user interaction', err);
                });
            });

            window.addEventListener('scroll', function() {
                video.play().catch(function(err) {
                console.error('Failed to play video after scroll interaction', err);
                });
            });
            });
        }
        </script>

        <?php
        return ob_get_clean();
    }
}