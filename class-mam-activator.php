<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Activator {

    public static function activate() {
        global $wpdb;

        $charset = $wpdb->get_charset_collate();

        // ── Tracking/Stats Table ─────────────────────────────
        $stats_table = $wpdb->prefix . 'mam_stats';
        $sql_stats = "CREATE TABLE IF NOT EXISTS {$stats_table} (
            id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            ad_id      BIGINT UNSIGNED NOT NULL,
            event_type VARCHAR(20)     NOT NULL DEFAULT 'impression',
            ip_hash    VARCHAR(64)     NOT NULL DEFAULT '',
            user_agent VARCHAR(255)    NOT NULL DEFAULT '',
            referrer   VARCHAR(500)    NOT NULL DEFAULT '',
            created_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY ad_id (ad_id),
            KEY event (event_type),
            KEY created (created_at)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql_stats );

        // ── Default options ─────────────────────────────────
        $defaults = [
            'mam_version'           => MAM_VERSION,
            'mam_track_impressions' => 1,
            'mam_track_clicks'      => 1,
            'mam_disable_for_admins'=> 1,
        ];

        foreach ( $defaults as $key => $value ) {
            add_option( $key, $value );
        }

        flush_rewrite_rules();
    }
}
