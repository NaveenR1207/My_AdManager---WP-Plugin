<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Tracker {

    public function __construct() {
        add_action( 'wp_ajax_mam_track_event', [ $this, 'handle_event' ] );
        add_action( 'wp_ajax_nopriv_mam_track_event', [ $this, 'handle_event' ] );
    }

    public function handle_event() {
        check_ajax_referer( 'mam_nonce', 'nonce' );

        $ad_id  = absint( $_POST['ad_id'] ?? 0 );
        $event  = sanitize_key( $_POST['event'] ?? 'impression' );

        if ( ! $ad_id || ! in_array( $event, [ 'impression', 'click' ], true ) ) {
            wp_send_json_error( 'invalid_params', 400 );
        }

        // Check tracking is enabled
        $opt = ( $event === 'impression' ) ? 'mam_track_impressions' : 'mam_track_clicks';
        if ( ! get_option( $opt, 1 ) ) {
            wp_send_json_success( 'tracking_disabled' );
        }

        // Check admin skip
        if ( get_option( 'mam_disable_for_admins', 1 ) && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            wp_send_json_success( 'skipped_admin' );
        }

        // Dedup impressions (one per IP per ad per day)
        if ( $event === 'impression' ) {
            $ip = $this->get_ip();
            $key = 'mam_dedup_imp_' . $ad_id . '_' . md5( $ip . date( 'Y-m-d' ) );
            if ( get_transient( $key ) ) {
                wp_send_json_success( 'already_tracked' );
            }
            set_transient( $key, 1, DAY_IN_SECONDS );
        }

        // Log to DB
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'mam_stats',
            [
                'ad_id'      => $ad_id,
                'event_type' => $event,
                'ip_hash'    => md5( $this->get_ip() ),
                'user_agent' => substr( sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ?? '' ) ), 0, 255 ),
                'referrer'   => substr( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ?? '' ) ), 0, 500 ),
                'created_at' => current_time( 'mysql' ),
            ],
            [ '%d', '%s', '%s', '%s', '%s', '%s' ]
        );

        wp_send_json_success();
    }

    private function get_ip(): string {
        $keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ];
        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = trim( explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}
