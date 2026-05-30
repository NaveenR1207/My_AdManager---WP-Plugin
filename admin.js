/**
 * My Ads Manager — Admin JS
 */
( function ( $ ) {
    'use strict';

    var frame;

    $( document ).on( 'click', '.mam-upload-image', function ( e ) {
        e.preventDefault();

        if ( frame ) {
            frame.open();
            return;
        }

        frame = wp.media.frames.mam_image = wp.media( {
            title   : mamAdmin.i18n.selectImage,
            button  : { text: mamAdmin.i18n.useImage },
            multiple: false,
            library : { type: 'image' }
        } );

        frame.on( 'select', function () {
            var attachment = frame.state().get( 'selection' ).first().toJSON();
            $( '#mam_ad_image_id' ).val( attachment.id );
            $( '#mam_ad_image_url' ).val( attachment.url );
            $( '#mam-image-preview' ).html( '<img src="' + attachment.url + '" style="max-width:200px;height:auto;" />' );
        } );

        frame.open();
    } );

} )( jQuery );
