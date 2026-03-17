jQuery(document).ready(function($) {
    var $status = $('#c7vl-action-status');

    $('#c7vl-sync-btn').on('click', function(e) {
        e.preventDefault();
        var $btn = $(this);

        // Disable button and show loading text
        $btn.prop('disabled', true);
        $status.text(' Fetching data from Commerce7 (this may take a minute if you have lots of products)...').css('color', '#333');

        // Send AJAX request to our new sync function
        $.post(c7vlAdminData.ajax_url, {
            action: 'c7vl_sync_data',
            nonce: c7vlAdminData.nonce
        }, function(response) {
            if (response.success) {
                // Success! Show green text and reload the page to update the UI
                $status.css('color', 'green').text(' ' + response.data);
                setTimeout(() => location.reload(), 1500); 
            } else {
                // Error handled by our PHP script
                $status.css('color', 'red').text(' Error: ' + response.data);
                $btn.prop('disabled', false);
            }
        }).fail(function() {
            // Server error (500) or timeout
            $status.css('color', 'red').text(' AJAX Request Failed. Check PHP error logs.');
            $btn.prop('disabled', false);
        });
    });
});