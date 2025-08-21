<?php

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function grandcarrental_child_enqueue_scripts(){
    wp_enqueue_style('grandcarrental-child', trailingslashit(get_stylesheet_directory_uri()) . 'style.css', array('grandcarrental-main', 'jquery.modal.min', 'grandcarrental-tailwind', 'grandcarrental-global', 'grandcarrental-blog', 'grandcarrental-woocommerce', 'grandcarrental-listing', 'grandcarrental-my-account', 'grandcarrental-icomoon', 'grandcarrental-css-variables'));
    wp_enqueue_style('grandcarrental-child-css', get_stylesheet_directory_uri() . '/grandcarrental-child.css', array(), '1.0', 'all');
    wp_enqueue_script('grandcarrental-child-js', get_stylesheet_directory_uri() . '/grandcarrental-child.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'grandcarrental_child_enqueue_scripts');

// Remove optional checkout fields
add_filter('woocommerce_checkout_fields', 'custom_remove_checkout_fields');
function custom_remove_checkout_fields($fields){
    unset($fields['billing']['billing_company']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['order']['order_comments']);
    return $fields;
}

add_action('woocommerce_after_order_notes', 'custom_checkout_file_upload_fields');

function custom_checkout_file_upload_fields($checkout) {
    echo '<div id="custom_checkout_uploads"><h3>' . __('Upload Required Documents', 'woocommerce') . '</h3>';

    echo '<div class="form-row form-row-wide" id="driving_license_front_field" data-priority="">';
    echo '<label for="driving_license_front">' . __('Upload Driving License Front Side', 'woocommerce') . ' <abbr class="required" title="required">*</abbr></label>';
    echo '<div id="driving_license_front_preview" class="uploaded-file-preview"></div>'; // Placeholder for uploaded file preview
    echo '<input type="file" class="input-text" name="driving_license_front" id="driving_license_front" accept="image/*,.pdf" />';
    echo '<input type="hidden" name="driving_license_front_url" id="driving_license_front_url" value="" />';
    echo '</div>';

    echo '<div class="form-row form-row-wide" id="driving_license_back_field" data-priority="">';
    echo '<label for="driving_license_back">' . __('Upload Driving License Back Side', 'woocommerce') . ' <abbr class="required" title="required">*</abbr></label>';
    echo '<div id="driving_license_back_preview" class="uploaded-file-preview"></div>'; // Placeholder for uploaded file preview
    echo '<input type="file" class="input-text" name="driving_license_back" id="driving_license_back" accept="image/*,.pdf" />';
    echo '<input type="hidden" name="driving_license_back_url" id="driving_license_back_url" value="" />';
    echo '</div>';

    echo '<div class="form-row form-row-wide" id="insurance_document_field" data-priority="">';
    echo '<label for="insurance_document">' . __('Upload Insurance Document', 'woocommerce') . ' <abbr class="required" title="required">*</abbr></label>';
    echo '<div id="insurance_document_preview" class="uploaded-file-preview"></div>'; // Placeholder for uploaded file preview
    echo '<input type="file" class="input-text" name="insurance_document" id="insurance_document" accept="image/*,.pdf" />';
    echo '<input type="hidden" name="insurance_document_url" id="insurance_document_url" value="" />';
    echo '</div>';

    echo '</div>';
}

add_action('woocommerce_checkout_process', 'custom_checkout_file_upload_validation');

function custom_checkout_file_upload_validation() {
    if (empty($_POST['driving_license_front_url'])) {
        wc_add_notice(__('Please upload your Driving License Front Side.', 'woocommerce'), 'error');
    }
    if (empty($_POST['driving_license_back_url'])) {
        wc_add_notice(__('Please upload your Driving License Back Side.', 'woocommerce'), 'error');
    }
    if (empty($_POST['insurance_document_url'])) {
        wc_add_notice(__('Please upload your Insurance Document.', 'woocommerce'), 'error');
    }
}

add_action('woocommerce_checkout_update_order_meta', 'save_custom_checkout_file_uploads');

function save_custom_checkout_file_uploads($order_id) {
    $uploaded_files = array();

    $fields = array(
        'driving_license_front' => 'Driving License Front Side',
        'driving_license_back'  => 'Driving License Back Side',
        'insurance_document'    => 'Insurance Document',
    );

    foreach ($fields as $field_name => $field_label) {
        $file_url_key = $field_name . '_url';
        if (!empty($_POST[$file_url_key])) {
            $uploaded_files[$field_name] = array(
                'url'  => esc_url_raw($_POST[$file_url_key]),
                'name' => basename(esc_url_raw($_POST[$file_url_key])),
                'type' => wp_check_filetype(esc_url_raw($_POST[$file_url_key]))['type'],
            );
        }
    }

    if (!empty($uploaded_files)) {
        update_post_meta($order_id, '_custom_checkout_uploads', $uploaded_files);
    }
}

add_action('wp_ajax_custom_checkout_file_upload', 'custom_checkout_file_upload_handler');
add_action('wp_ajax_nopriv_custom_checkout_file_upload', 'custom_checkout_file_upload_handler');

function custom_checkout_file_upload_handler() {
    check_ajax_referer('custom-checkout-file-upload-nonce', 'nonce');

    if (!function_exists('wp_handle_upload')) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
    }

    $field_name = '';
    if (isset($_FILES['driving_license_front'])) {
        $field_name = 'driving_license_front';
    } elseif (isset($_FILES['driving_license_back'])) {
        $field_name = 'driving_license_back';
    } elseif (isset($_FILES['insurance_document'])) {
        $field_name = 'insurance_document';
    }

    if (!empty($field_name)) {
        $uploaded_file = $_FILES[$field_name];
        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($uploaded_file, $upload_overrides);

        if ($movefile && !isset($movefile['error'])) {
            wp_send_json_success(array('url' => $movefile['url'], 'type' => $movefile['type']));
        } else {
            wp_send_json_error(array('message' => $movefile['error']));
        }
    } else {
        wp_send_json_error(array('message' => 'No file received.'));
    }

    wp_die();
}


add_action('woocommerce_admin_order_data_after_shipping_address', 'display_custom_checkout_file_uploads');

function display_custom_checkout_file_uploads($order) {
    $uploaded_files = get_post_meta($order->get_id(), '_custom_checkout_uploads', true);

    if (!empty($uploaded_files)) {
        echo '<div class="address">';
        echo '<h4>' . __('Uploaded Documents', 'woocommerce') . '</h4>';
        echo '<p>';
        foreach ($uploaded_files as $field_name => $file_info) {
            $label = '';
            if ($field_name === 'driving_license_front') {
                $label = 'Driving License Front Side';
            } elseif ($field_name === 'driving_license_back') {
                $label = 'Driving License Back Side';
            } elseif ($field_name === 'insurance_document') {
                $label = 'Insurance Document';
            }
            echo '<p><strong>' . esc_html($label) . ':</strong> ';
            if (isset($file_info['type']) && strpos($file_info['type'], 'image/') === 0) {
                echo '<a href="' . esc_url($file_info['url']) . '" target="_blank"><img src="' . esc_url($file_info['url']) . '" style="max-width: 100px; max-height: 100px; vertical-align: middle; margin-left: 10px;" /></a>';
                echo '<br/><a href="' . esc_url($file_info['url']) . '" download>Download</a>';
            } else {
                echo '<a href="' . esc_url($file_info['url']) . '" target="_blank">View Document</a>';
                echo '<br/><a href="' . esc_url($file_info['url']) . '" download>Download</a>';
            }
            echo '</p>';
        }
        echo '</p>';
        echo '</div>';
    }
}

add_action('wp_enqueue_scripts', 'custom_checkout_file_upload_scripts');

function custom_checkout_file_upload_scripts() {
    if (is_checkout()) {
        wp_enqueue_script('custom-checkout-file-upload', get_stylesheet_directory_uri() . '/js/checkout-file-upload.js', array('jquery'), null, true);
        wp_localize_script('custom-checkout-file-upload', 'custom_checkout_file_upload_params', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('custom-checkout-file-upload-nonce'),
        ));
    }
}

/**
 * Helpers: map uploads URL to absolute file path
 */
function grandcarrental_child_local_path_from_url($url) {
    if (empty($url)) return '';
    $uploads = wp_upload_dir();
    if (!empty($uploads['baseurl']) && !empty($uploads['basedir']) && strpos($url, $uploads['baseurl']) === 0) {
        $path = str_replace($uploads['baseurl'], $uploads['basedir'], $url);
        $path = wp_normalize_path($path);
        return $path;
    }
    return '';
}

/**
 * Attach uploaded customer documents to Admin New Order email
 */
add_filter('woocommerce_email_attachments', 'grandcarrental_child_attach_custom_uploads_to_email', 10, 4);
function grandcarrental_child_attach_custom_uploads_to_email($attachments, $email_id, $order, $email) {
    if ($email_id !== 'new_order' || !($order instanceof WC_Order)) {
        return $attachments;
    }

    $uploaded_files = get_post_meta($order->get_id(), '_custom_checkout_uploads', true);
    if (!is_array($uploaded_files) || empty($uploaded_files)) {
        return $attachments;
    }

    foreach ($uploaded_files as $field_name => $file_info) {
        $url = isset($file_info['url']) ? esc_url_raw($file_info['url']) : '';
        $path = grandcarrental_child_local_path_from_url($url);
        if ($path && file_exists($path)) {
            $attachments[] = $path;
        }
    }

    return $attachments;
}

/**
 * Render thumbnails + download links in Admin New Order email body
 * Placement: after Billing/Shipping addresses in the email.
 */
add_action('woocommerce_email_customer_details', 'grandcarrental_child_render_custom_uploads_in_email', 999, 4);
function grandcarrental_child_render_custom_uploads_in_email($order, $sent_to_admin, $plain_text, $email) {
    if (!$sent_to_admin || !isset($email->id) || $email->id !== 'new_order') {
        return;
    }

    $uploaded_files = get_post_meta($order->get_id(), '_custom_checkout_uploads', true);
    if (!is_array($uploaded_files) || empty($uploaded_files)) {
        return;
    }

    $labels = array(
        'driving_license_front' => 'Driving License Front Side',
        'driving_license_back'  => 'Driving License Back Side',
        'insurance_document'    => 'Insurance Document',
    );

    if ($plain_text) {
        echo "\nUploaded Documents:\n";
        foreach ($uploaded_files as $field_name => $file_info) {
            $label = isset($labels[$field_name]) ? $labels[$field_name] : ucfirst(str_replace('_', ' ', $field_name));
            $url   = isset($file_info['url']) ? esc_url($file_info['url']) : '';
            echo sprintf("- %s: %s\n", $label, $url);
        }
        echo "\n";
        return;
    }

    echo '<h2 style="margin:20px 0 10px 0;">Uploaded Documents</h2>';
    echo '<div style="display:flex;gap:16px;flex-wrap:wrap;align-items:flex-start;">';
    foreach ($uploaded_files as $field_name => $file_info) {
        $label = isset($labels[$field_name]) ? $labels[$field_name] : ucfirst(str_replace('_', ' ', $field_name));
        $url   = isset($file_info['url']) ? esc_url($file_info['url']) : '';
        $type  = isset($file_info['type']) ? sanitize_text_field($file_info['type']) : '';
        $name  = isset($file_info['name']) ? sanitize_text_field($file_info['name']) : basename(parse_url($url, PHP_URL_PATH));

        echo '<div style="border:1px solid #e5e7eb;border-radius:8px;padding:10px;max-width:180px;text-align:center;">';
        echo '<div style="font-weight:600;margin-bottom:8px;font-size:13px;">' . esc_html($label) . '</div>';

        if ($type && strpos($type, 'image/') === 0) {
            echo '<a href="' . $url . '" target="_blank" style="display:inline-block;">';
            echo '<img src="' . $url . '" alt="' . esc_attr($label) . '" style="max-width:160px;max-height:120px;border-radius:4px;border:1px solid #eee;" />';
            echo '</a>';
        } else {
            // Non-image (e.g., PDF) placeholder box
            echo '<a href="' . $url . '" target="_blank" style="display:block;text-decoration:none;">';
            echo '<div style="width:160px;height:120px;display:flex;align-items:center;justify-content:center;border:1px dashed #cbd5e1;border-radius:4px;background:#f8fafc;color:#1f2937;font-size:12px;">Document</div>';
            echo '</a>';
        }

        echo '<div style="margin-top:8px;word-break:break-all;font-size:12px;color:#374151;">' . esc_html($name) . '</div>';
        echo '<div style="margin-top:8px;">';
        echo '<a href="' . $url . '" download style="display:inline-block;padding:6px 10px;background:#2563eb;color:#ffffff;border-radius:4px;text-decoration:none;font-size:12px;">Download</a>';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';
}
