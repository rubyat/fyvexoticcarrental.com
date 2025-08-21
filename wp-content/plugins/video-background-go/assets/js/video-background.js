jQuery(document).ready(function($) {
    // Check if video should be shown on mobile
    function handleMobileVisibility() {
        $('.video-background-container').each(function() {
            var $container = $(this);
            var showOnMobile = $container.data('show-mobile');
            
            if (!showOnMobile && window.innerWidth <= 768) {
                $container.find('.video-background').get(0).pause();
            } else {
                var video = $container.find('.video-background').get(0);
                if (video) {
                    video.play().catch(function(error) {
                        console.log('Video autoplay failed:', error);
                    });
                }
            }
        });
    }
    
    // Handle initial state
    handleMobileVisibility();
    
    // Handle resize events
    var resizeTimer;
    $(window).on('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(handleMobileVisibility, 250);
    });
});