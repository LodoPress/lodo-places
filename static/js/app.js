(function( $ ) {
    'use strict';

    console.log( 'loaded' );
    $('.button').click( function() {
        var button = $(this);
        $.ajax({
            url: LODO_PLACES_SYNC.ajax_url,
            type: 'GET',
            data: {
                action: 'lodo_places_import_location',
                id: $(button).attr('data-id')
            }
        });
        console.log($(this).attr('data-id'));
    } );

})( jQuery );