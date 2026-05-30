/**
 * My Ads Manager — Frontend Tracker
 */
( function ( $ ) {
    'use strict';

    if ( typeof mamData === 'undefined' ) return;

    var trackedImpressions = {};

    function trackImpression( adId ) {
        if ( ! mamData.trackImpressions ) return;
        if ( trackedImpressions[ adId ] ) return;

        trackedImpressions[ adId ] = true;

        $.post( mamData.ajaxUrl, {
            action : 'mam_track_event',
            nonce  : mamData.nonce,
            ad_id  : adId,
            event  : 'impression'
        } );
    }

    function trackClick( adId ) {
        if ( ! mamData.trackClicks ) return;

        $.post( mamData.ajaxUrl, {
            action : 'mam_track_event',
            nonce  : mamData.nonce,
            ad_id  : adId,
            event  : 'click'
        } );
    }

    $( function () {
        // Track impressions for all ads
        $( '.mam-ad' ).each( function () {
            var adId = $( this ).data( 'ad-id' );
            if ( adId ) {
                trackImpression( adId );
            }
        } );

        // Track clicks
        $( document ).on( 'click', '.mam-ad a', function () {
            var $ad = $( this ).closest( '.mam-ad' );
            var adId = $ad.data( 'ad-id' );
            if ( adId ) {
                trackClick( adId );
            }
        } );
    } );

    window.MAM = window.MAM || {};
    window.MAM.trackImpression = trackImpression;
    window.MAM.trackClick = trackClick;

} )( jQuery );
