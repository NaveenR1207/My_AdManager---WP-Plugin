<?php
/**
 * Plugin Name:       My Ads Manager Pro
 * Plugin URI:        https://example.com
 * Description:       Professional ad management with placements, targeting, groups, image uploads, expiry dates, and advanced tracking.
 * Version:           2.0.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Custom Build
 * License:           GPLv2 or later
 * Text Domain:       my-ads-manager
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── Constants ────────────────────────────────────────────────
define( 'MAM_VERSION',    '2.0.1' );
define( 'MAM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MAM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// ── Activation / Deactivation ────────────────────────────────
register_activation_hook( __FILE__, 'mam_activate' );
function mam_activate() {
    require_once MAM_PLUGIN_DIR . 'includes/class-mam-activator.php';
    MAM_Activator::activate();
}

register_deactivation_hook( __FILE__, 'mam_deactivate' );
function mam_deactivate() {
    flush_rewrite_rules();
}

// ── Load all classes ─────────────────────────────────────────
$mam_files = [
    'includes/class-mam-activator.php',
    'includes/class-mam-post-types.php',
    'includes/class-mam-admin.php',
    'includes/class-mam-ad.php',
    'includes/class-mam-group.php',
    'includes/class-mam-placement.php',
    'includes/class-mam-shortcodes.php',
    'includes/class-mam-tracker.php',
    'includes/class-mam-loader.php',
];

foreach ( $mam_files as $file ) {
    $path = MAM_PLUGIN_DIR . $file;
    if ( file_exists( $path ) ) {
        require_once $path;
    }
}

// ── Boot ─────────────────────────────────────────────────────
add_action( 'plugins_loaded', function () {
    load_plugin_textdomain( 'my-ads-manager', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

    // Initialize all classes
    new MAM_Post_Types();
    new MAM_Admin();
    new MAM_Group();
    new MAM_Placement();
    new MAM_Shortcodes();
    new MAM_Tracker();
    ( new MAM_Loader() )->run();
} );
