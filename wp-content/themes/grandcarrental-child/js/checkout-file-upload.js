jQuery(document).ready(function($) {
    var file_fields = ['driving_license_front', 'driving_license_back', 'insurance_document'];

    $.each(file_fields, function(index, field_name) {
        $('#' + field_name).on('change', function() {
            var file_input = $(this);
            var file_label = $('label[for="' + field_name + '"]');
            var hidden_url_field = $('#' + field_name + '_url');
            var file_data = file_input[0].files[0];
            var form_data = new FormData();

            form_data.append('action', 'custom_checkout_file_upload');
            form_data.append('nonce', custom_checkout_file_upload_params.nonce);
            form_data.append(field_name, file_data);

            if (file_data) {
                file_label.append('<span class="uploading-spinner"><img src="' + custom_checkout_file_upload_params.ajax_url.replace('admin-ajax.php', '') + 'images/spinner.gif" style="height:1em;vertical-align:text-top;"/> Uploading...</span>');
                file_input.prop('disabled', true);

                $.ajax({
                    url: custom_checkout_file_upload_params.ajax_url,
                    type: 'POST',
                    data: form_data,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        $('.uploading-spinner').remove();
                        file_input.prop('disabled', false);
                        if (response.success) {
                            hidden_url_field.val(response.data.url);
                            $('#' + field_name + '_preview').empty(); // Clear previous preview
                            if (response.data.type && response.data.type.startsWith('image/')) {
                                $('#' + field_name + '_preview').append(
                                    '<div class="uploaded-thumbnail-card">' +
                                    '<img src="' + response.data.url + '" class="uploaded-thumbnail-img" />' +
                                    '<span class="remove-uploaded-file">&times;</span>' +
                                    '</div>'
                                );
                                $('.remove-uploaded-file').on('click', function() {
                                    file_input.val('');
                                    hidden_url_field.val('');
                                    $(this).closest('.uploaded-thumbnail-card').remove();
                                    // Remove text-based success message if present
                                    file_label.find('.uploaded-success').remove();
                                });
                            } else {
                                // For non-image files, show a text-based success message in the label itself
                                file_label.find('abbr.required').after('<span class="uploaded-success" style="color: green; margin-left: 10px;">Uploaded!</span>');
                            }
                        } else {
                            alert('File upload failed: ' + response.data.message);
                            hidden_url_field.val('');
                        }
                    },
                    error: function(response) {
                        $('.uploading-spinner').remove();
                        file_input.prop('disabled', false);
                        alert('An error occurred during file upload.');
                        hidden_url_field.val('');
                    }
                });
            } else {
                hidden_url_field.val('');
                file_label.find('.uploaded-thumbnail, .uploaded-success').remove(); // Remove thumbnail or success message if file is removed
            }
        });
    });
});