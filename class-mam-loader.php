<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Loader {

    public function run() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets() {
        wp_enqueue_script(
            'mam-tracker',
            MAM_PLUGIN_URL . 'assets/js/tracker.js',
            [ 'jquery' ],
            MAM_VERSION,
            true
        );

        wp_localize_script(
            'mam-tracker',
            'mamData',
            [
                'ajaxUrl'          => admin_url( 'admin-ajax.php' ),
                'nonce'            => wp_create_nonce( 'mam_nonce' ),
                'trackImpressions' => (bool) get_option( 'mam_track_impressions', 1 ),
                'trackClicks'      => (bool) get_option( 'mam_track_clicks', 1 ),
            ]
        );

        wp_enqueue_style(
            'mam-public',
            MAM_PLUGIN_URL . 'assets/css/public.css',
            [],
            MAM_VERSION
        );
    }
}
