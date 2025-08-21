jQuery(document).ready(function($) {
    // Handle video upload
    $('#upload_video_button').on('click', function(e) {
        e.preventDefault();
        
        var mediaUploader = wp.media({
            title: 'Select Video',
            button: {
                text: 'Use this video'
            },
            multiple: false,
            library: {
                type: 'video'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#video_url').val(attachment.url);
        });
        
        mediaUploader.open();
    });
    
    // Handle image upload
    $('#upload_image_button').on('click', function(e) {
        e.preventDefault();
        
        var mediaUploader = wp.media({
            title: 'Select Image',
            button: {
                text: 'Use this image'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });
        
        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#placeholder_image').val(attachment.url);
        });
        
        mediaUploader.open();
    });

    $('.shortcode-copy-button').on('click', async function() {
        const button = $(this);
        const shortcode = button.data('shortcode');
        
        try {
            await navigator.clipboard.writeText(shortcode);
            
            // Visual feedback
            const icon = button.find('.dashicons');
            icon.removeClass('dashicons-clipboard').addClass('dashicons-yes');
            button.addClass('copied');
            
            setTimeout(function() {
                icon.removeClass('dashicons-yes').addClass('dashicons-clipboard');
                button.removeClass('copied');
            }, 1500);
        } catch (err) {
            console.error('Failed to copy shortcode:', err);
        }
    });
});