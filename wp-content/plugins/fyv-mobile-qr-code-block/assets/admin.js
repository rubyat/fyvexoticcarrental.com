(function($){
  $(function(){
    // Media uploader for the QR code image field
    var mediaFrame;

    // Attach handler using the known button ID for the single media field on the page
    $(document).on('click', '#qr_code_image_button', function(e){
      e.preventDefault();
      var inputId = 'qr_code_image';
      var previewId = inputId + '_preview';

      if (mediaFrame) {
        mediaFrame.open();
        return;
      }

      mediaFrame = wp.media({
        title: 'Select QR Code Image',
        button: { text: 'Use this image' },
        multiple: false
      });

      mediaFrame.on('select', function(){
        var attachment = mediaFrame.state().get('selection').first().toJSON();
        $('#' + inputId).val(attachment.url).trigger('change');
        var $preview = $('#' + previewId);
        if ($preview.length) {
          $preview.attr('src', attachment.url).css('display','inline-block');
        }
      });

      mediaFrame.open();
    });
  });
})(jQuery);
