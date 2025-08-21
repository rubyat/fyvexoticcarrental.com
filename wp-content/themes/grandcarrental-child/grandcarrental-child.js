(function ($) {
  'use strict';
})(jQuery);
jQuery(document).ready(function($) {
    // Handle file upload fields in checkout
    if ($('form.checkout').length) {
        // Show selected file names
        $('input[type="file"]').on('change', function() {
            var fileName = $(this).val().split('\\').pop();
            if (fileName) {
                $(this).closest('.form-row').find('.description').after(
                    '<div class="file-selected">Selected: ' + fileName + '</div>'
                );
            }
        });

        // Add file validation before submission
        $('form.checkout').on('checkout_place_order', function() {
            var valid = true;
            $('input[type="file"][required]').each(function() {
                if (!$(this).val()) {
                    valid = false;
                    $(this).closest('.form-row').addClass('woocommerce-invalid');
                    $(this).focus();
                }
            });
            return valid;
        });
    }
});
