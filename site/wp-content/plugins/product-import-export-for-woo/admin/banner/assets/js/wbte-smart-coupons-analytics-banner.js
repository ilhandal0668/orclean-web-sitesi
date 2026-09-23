/* Smart Coupons Analytics Banner Script */

jQuery(document).ready(function($) {
    // Handle dismiss button click using event delegation
    jQuery(document).on('click', '.wbte_smart_coupons_analytics_dismiss_2026', function(e) {
        e.preventDefault();

        var $banner = jQuery(this).closest('.wbte_sc_analytics_banner');
        var $button = jQuery(this);

        // Reduce banner opacity for instant visual feedback
        $banner.css('opacity', '0.5');

        // Disable button to prevent multiple clicks
        $button.prop('disabled', true);

        // Send AJAX request
        $.ajax({
            url: wbte_smart_coupons_analytics_banner_params.ajaxurl,
            type: 'POST',
            data: {
                action: 'wbte_smart_coupons_analytics_dismiss_2026',
                nonce: wbte_smart_coupons_analytics_banner_params.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Slide up banner on success
                    $banner.slideUp();
                } else {
                    // If AJAX fails, restore opacity and re-enable button
                    $banner.css('opacity', '1');
                    $button.prop('disabled', false);
                }
            },
            error: function() {
                // If AJAX error, restore opacity and re-enable button
                $banner.css('opacity', '1');
                $button.prop('disabled', false);
            }
        });
    });

});
