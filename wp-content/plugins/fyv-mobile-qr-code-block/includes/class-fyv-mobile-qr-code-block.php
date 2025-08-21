<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('FYV_Mobile_QR_Code_Block')) {
    class FYV_Mobile_QR_Code_Block
    {
        const OPTION_NAME = 'fyv_mqrcb_options';

        private static $instance = null;

        public static function instance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        private function __construct()
        {
            add_action('admin_menu', [$this, 'register_settings_page']);
            add_action('admin_init', [$this, 'register_settings']);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

            add_action('wp_footer', [$this, 'render_frontend_block']);
        }

        public function get_options()
        {
            $defaults = [
                'enabled' => 0,
                'title' => '',
                'qr_code_image' => '',
                'link' => '',
                'link_text' => '',
                'home_only' => 0,
            ];
            $opts = get_option(self::OPTION_NAME, []);
            if (!is_array($opts)) {
                $opts = [];
            }
            return wp_parse_args($opts, $defaults);
        }

        public function register_settings_page()
        {
            add_options_page(
                __('FYV Mobile QR Code Block', 'fyv-mobile-qr-code-block'),
                __('FYV QR Code', 'fyv-mobile-qr-code-block'),
                'manage_options',
                'fyv-mobile-qr-code-block',
                [$this, 'render_settings_page']
            );
        }

        public function enqueue_admin_assets($hook_suffix)
        {
            if ($hook_suffix !== 'settings_page_fyv-mobile-qr-code-block') {
                return;
            }
            wp_enqueue_media();

            $assets_dir = plugin_dir_path(__FILE__) . '../assets/';
            $assets_url = plugin_dir_url(__FILE__) . '../assets/';

            $css_path = $assets_dir . 'admin.css';
            $css_ver = file_exists($css_path) ? filemtime($css_path) : '1.0.0';
            wp_enqueue_style(
                'fyv-mqrcb-admin',
                $assets_url . 'admin.css',
                [],
                $css_ver
            );

            $js_path = $assets_dir . 'admin.js';
            $js_ver = file_exists($js_path) ? filemtime($js_path) : '1.0.0';
            wp_enqueue_script(
                'fyv-mqrcb-admin',
                $assets_url . 'admin.js',
                ['jquery'],
                $js_ver,
                true
            );
        }

        public function register_settings()
        {
            register_setting(
                'fyv_mqrcb_settings_group',
                self::OPTION_NAME,
                [$this, 'sanitize_options']
            );

            add_settings_section(
                'fyv_mqrcb_main_section',
                __('QR Code Block Settings', 'fyv-mobile-qr-code-block'),
                function () {
                    echo '<p>' . esc_html__('Configure the QR code callout shown on the frontend.', 'fyv-mobile-qr-code-block') . '</p>';
                },
                'fyv-mobile-qr-code-block'
            );

            add_settings_field(
                'enabled',
                __('Enabled', 'fyv-mobile-qr-code-block'),
                [$this, 'render_checkbox_field'],
                'fyv-mobile-qr-code-block',
                'fyv_mqrcb_main_section',
                [
                    'id' => 'enabled',
                    'label' => __('Enable frontend QR block', 'fyv-mobile-qr-code-block'),
                    'desc' => __('Turn the block on or off across the site (subject to homepage-only setting).', 'fyv-mobile-qr-code-block'),
                ]
            );

            add_settings_field(
                'title',
                __('Title', 'fyv-mobile-qr-code-block'),
                [$this, 'render_text_field'],
                'fyv-mobile-qr-code-block',
                'fyv_mqrcb_main_section',
                [
                    'id' => 'title',
                    'placeholder' => __('e.g., Rent FYV', 'fyv-mobile-qr-code-block'),
                    'desc' => __('Shown as the heading above the QR code.', 'fyv-mobile-qr-code-block'),
                ]
            );

            add_settings_field(
                'qr_code_image',
                __('QR Code Image', 'fyv-mobile-qr-code-block'),
                [$this, 'render_media_field'],
                'fyv-mobile-qr-code-block',
                'fyv_mqrcb_main_section',
                [
                    'id' => 'qr_code_image',
                    'desc' => __('Upload or select the QR code image.', 'fyv-mobile-qr-code-block'),
                ]
            );

            add_settings_field(
                'link',
                __('Link', 'fyv-mobile-qr-code-block'),
                [$this, 'render_text_field'],
                'fyv-mobile-qr-code-block',
                'fyv_mqrcb_main_section',
                [
                    'id' => 'link',
                    'placeholder' => __('https://example.com', 'fyv-mobile-qr-code-block'),
                    'desc' => __('The URL to open when clicking the QR code or the button.', 'fyv-mobile-qr-code-block'),
                ]
            );

            add_settings_field(
                'link_text',
                __('Link Text', 'fyv-mobile-qr-code-block'),
                [$this, 'render_text_field'],
                'fyv-mobile-qr-code-block',
                'fyv_mqrcb_main_section',
                [
                    'id' => 'link_text',
                    'placeholder' => __('Get the app', 'fyv-mobile-qr-code-block'),
                    'desc' => __('The label for the call-to-action button.', 'fyv-mobile-qr-code-block'),
                ]
            );

            add_settings_field(
                'home_only',
                __('Show only on homepage', 'fyv-mobile-qr-code-block'),
                [$this, 'render_checkbox_field'],
                'fyv-mobile-qr-code-block',
                'fyv_mqrcb_main_section',
                [
                    'id' => 'home_only',
                    'label' => __('Only display on the homepage/front page', 'fyv-mobile-qr-code-block'),
                    'desc' => __('If enabled, the block will only appear on the front page.', 'fyv-mobile-qr-code-block'),
                ]
            );
        }

        public function sanitize_options($input)
        {
            $output = $this->get_options();

            $output['enabled'] = isset($input['enabled']) ? 1 : 0;
            $output['home_only'] = isset($input['home_only']) ? 1 : 0;

            $output['title'] = isset($input['title']) ? sanitize_text_field($input['title']) : '';
            $output['qr_code_image'] = isset($input['qr_code_image']) ? esc_url_raw($input['qr_code_image']) : '';
            $output['link'] = isset($input['link']) ? esc_url_raw($input['link']) : '';
            $output['link_text'] = isset($input['link_text']) ? sanitize_text_field($input['link_text']) : '';

            return $output;
        }

        public function render_text_field($args)
        {
            $options = $this->get_options();
            $id = $args['id'];
            $value = isset($options[$id]) ? $options[$id] : '';
            $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '';
            $desc = isset($args['desc']) ? $args['desc'] : '';
            echo '<div class="fyv-field fyv-text-field">';
            echo '<input type="text" id="' . esc_attr($id) . '" name="' . esc_attr(self::OPTION_NAME) . '[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" placeholder="' . esc_attr($placeholder) . '" class="regular-text" />';
            if (!empty($desc)) {
                echo '<p class="description">' . esc_html($desc) . '</p>';
            }
            echo '</div>';
        }

        public function render_checkbox_field($args)
        {
            $options = $this->get_options();
            $id = $args['id'];
            $checked = !empty($options[$id]) ? 'checked' : '';
            $label = isset($args['label']) ? $args['label'] : '';
            $desc = isset($args['desc']) ? $args['desc'] : '';
            echo '<div class="fyv-field fyv-switch">';
            echo '<input type="checkbox" id="' . esc_attr($id) . '" name="' . esc_attr(self::OPTION_NAME) . '[' . esc_attr($id) . ']" value="1" ' . $checked . ' />';
            echo '<span class="fyv-slider" aria-hidden="true"></span>';
            if (!empty($label)) {
                echo '<label class="fyv-switch-label" for="' . esc_attr($id) . '">' . esc_html($label) . '</label>';
            }
            if (!empty($desc)) {
                echo '<p class="description">' . esc_html($desc) . '</p>';
            }
            echo '</div>';
        }

        public function render_media_field($args)
        {
            $options = $this->get_options();
            $id = $args['id'];
            $value = isset($options[$id]) ? $options[$id] : '';
            $button_id = $id . '_button';
            $preview_id = $id . '_preview';
            $desc = isset($args['desc']) ? $args['desc'] : '';
            echo '<div class="fyv-field fyv-media-field">';
            echo '<div class="fyv-media-controls">';
            echo '<input type="text" id="' . esc_attr($id) . '" name="' . esc_attr(self::OPTION_NAME) . '[' . esc_attr($id) . ']" value="' . esc_attr($value) . '" class="regular-text" /> ';
            echo '<button type="button" class="button button-secondary" id="' . esc_attr($button_id) . '">' . esc_html__('Select Image', 'fyv-mobile-qr-code-block') . '</button>';
            echo '</div>';
            if (!empty($desc)) {
                echo '<p class="description">' . esc_html($desc) . '</p>';
            }
            echo '<div class="fyv-media-preview" style="margin-top:10px;">';
            if (!empty($value)) {
                echo '<img id="' . esc_attr($preview_id) . '" src="' . esc_url($value) . '" style="max-width:150px;height:auto;" alt="" />';
            } else {
                echo '<img id="' . esc_attr($preview_id) . '" src="" style="display:none;max-width:150px;height:auto;" alt="" />';
            }
            echo '</div>';
            echo '</div>';
            echo '<script type="text/javascript">window.FYV_MQRCB_MEDIA_FIELD_ID = ' . wp_json_encode($id) . ';</script>';
        }

        public function render_settings_page()
        {
            if (!current_user_can('manage_options')) {
                return;
            }
            echo '<div id="fyv-mqrcb-settings" class="wrap">';
            echo '<h1>' . esc_html__('FYV Mobile QR Code Block', 'fyv-mobile-qr-code-block') . '</h1>';
            // Display settings API notices
            settings_errors();
            echo '<div class="fyv-card">';
            echo '<form method="post" action="options.php">';
            settings_fields('fyv_mqrcb_settings_group');
            do_settings_sections('fyv-mobile-qr-code-block');
            echo '<div class="fyv-card-actions">';
            submit_button(__('Save Changes', 'fyv-mobile-qr-code-block'));
            echo '</div>';
            echo '</form>';
            echo '</div>';
            echo '</div>';
        }

        private function should_render_on_this_page()
        {
            $options = $this->get_options();
            if (empty($options['enabled'])) {
                return false;
            }
            if (!empty($options['home_only'])) {
                return is_front_page() || is_home();
            }
            return true;
        }

        public function render_frontend_block()
        {
            if (!apply_filters('fyv_mqrcb_should_render', $this->should_render_on_this_page())) {
                return;
            }

            $options = $this->get_options();

            $title = isset($options['title']) ? $options['title'] : '';
            $image = isset($options['qr_code_image']) ? $options['qr_code_image'] : '';
            $link = isset($options['link']) ? $options['link'] : '';
            $link_text = isset($options['link_text']) ? $options['link_text'] : '';

            if (empty($title) && empty($image) && empty($link) && empty($link_text)) {
                return; // Nothing to render
            }

            // Remove inline display to allow CSS to control visibility per device
            echo '<div class="mobile-qrcode-block">';
            echo '<style>';
            echo '.mobile-qrcode-block {background-color:#fff;border-radius:15px;padding:20px;text-align:center;position:fixed;bottom:15px;left:15px;z-index:1000;box-shadow:0 4px 8px rgba(0,0,0,0.1);}';
            echo '.mobile-qrcode-block .close-button{position:absolute;top:10px;right:10px;background:#f0f0f0;border-radius:50%;width:24px;height:24px;display:flex;align-items:center;justify-content:center;cursor:pointer;font-size:14px;color:#333;text-decoration:none;transition:all .3s ease;}';
            echo '.mobile-qrcode-block h3{font-size:24px;color:#333;}';
            echo '.mobile-qrcode-block img{max-width:150px;height:auto;}';
            echo '.mobile-qrcode-block a.link{display:inline-block;background-color:#f0f0f0;color:#333;padding:10px 30px;border-radius:25px;text-decoration:none;font-weight:bold;text-transform:uppercase;font-size:14px;transition:all .3s ease;}';
            echo '.mobile-qrcode-block .close-button:hover,.mobile-qrcode-block a.link:hover{background-color:#2f2121;color:#fff;transition:all .3s ease;}';
            // Force-hide on mobile with !important in case other inline/server styles attempt to show it
            echo '@media (max-width:768px){.mobile-qrcode-block{display:none!important;}}';
            echo '</style>';

            echo '<div class="item">';
            echo '<a href="#" class="close-button" onclick="hideMobileQRBlockForSixHours(); return false;">×</a>';
            if (!empty($title)) {
                echo '<h3>' . esc_html($title) . '</h3>';
            }
            if (!empty($image)) {
                echo '<div>';
                if (!empty($link)) {
                    echo '<a target="_blank" rel="noopener nofollow" href="' . esc_url($link) . '">';
                }
                echo '<img src="' . esc_url($image) . '" alt="' . esc_attr__('QR Code', 'fyv-mobile-qr-code-block') . '" />';
                if (!empty($link)) {
                    echo '</a>';
                }
                echo '</div>';
            }
            if (!empty($link) && !empty($link_text)) {
                echo '<a target="_blank" rel="noopener nofollow" class="link" href="' . esc_url($link) . '">' . esc_html($link_text) . '</a>';
            }
            echo '</div>';

            echo '<script>';
            echo 'function hideMobileQRBlockForSixHours(){var block=document.querySelector(".mobile-qrcode-block");if(block){block.style.display="none";var now=new Date();var hideUntil=now.getTime()+6*60*60*1000;localStorage.setItem("hideMobileQRBlockUntil",hideUntil);}}';
            // Do not force-show the block; only hide when needed (CSS controls default visibility per viewport)
            echo 'document.addEventListener("DOMContentLoaded",function(){var block=document.querySelector(".mobile-qrcode-block");if(block){var hideUntil=localStorage.getItem("hideMobileQRBlockUntil");if(hideUntil){var now=new Date().getTime();if(now<hideUntil){block.style.display="none";}}}});';
            echo '</script>';

            echo '</div>';
        }
    }
}
