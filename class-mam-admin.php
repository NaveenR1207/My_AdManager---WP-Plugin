<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Admin {

    public function __construct() {
        add_action( 'admin_menu',            [ $this, 'register_menus' ] );
        add_action( 'add_meta_boxes_mam_ad', [ $this, 'add_ad_metaboxes' ] );
        add_action( 'save_post_mam_ad',      [ $this, 'save_ad_meta' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
        add_filter( 'manage_mam_ad_posts_columns', [ $this, 'ad_columns' ] );
        add_action( 'manage_mam_ad_posts_custom_column', [ $this, 'ad_column_output' ], 10, 2 );
    }

    public function register_menus() {
        add_menu_page(
            __( 'My Ads Manager', 'my-ads-manager' ),
            __( 'My Ads Manager', 'my-ads-manager' ),
            'manage_options',
            'mam-dashboard',
            [ $this, 'dashboard_page' ],
            'dashicons-chart-area',
            25
        );

        add_submenu_page(
            'mam-dashboard',
            __( 'Dashboard', 'my-ads-manager' ),
            __( 'Dashboard', 'my-ads-manager' ),
            'manage_options',
            'mam-dashboard',
            [ $this, 'dashboard_page' ]
        );

        add_submenu_page(
            'mam-dashboard',
            __( 'All Ads', 'my-ads-manager' ),
            __( 'All Ads', 'my-ads-manager' ),
            'manage_options',
            'edit.php?post_type=mam_ad'
        );

        add_submenu_page(
            'mam-dashboard',
            __( 'Add New Ad', 'my-ads-manager' ),
            __( 'Add New Ad', 'my-ads-manager' ),
            'manage_options',
            'post-new.php?post_type=mam_ad'
        );

        add_submenu_page(
            'mam-dashboard',
            __( 'Ad Groups', 'my-ads-manager' ),
            __( 'Ad Groups', 'my-ads-manager' ),
            'manage_options',
            'edit.php?post_type=mam_group'
        );

        add_submenu_page(
            'mam-dashboard',
            __( 'Placements', 'my-ads-manager' ),
            __( 'Placements', 'my-ads-manager' ),
            'manage_options',
            'edit.php?post_type=mam_placement'
        );

        add_submenu_page(
            'mam-dashboard',
            __( 'Settings', 'my-ads-manager' ),
            __( 'Settings', 'my-ads-manager' ),
            'manage_options',
            'mam-settings',
            [ $this, 'settings_page' ]
        );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'mam' ) === false && get_current_screen()->post_type !== 'mam_ad' ) {
            return;
        }

        wp_enqueue_media();
        wp_enqueue_style(
            'mam-admin',
            MAM_PLUGIN_URL . 'admin/css/admin.css',
            [],
            MAM_VERSION
        );

        wp_enqueue_script(
            'mam-admin',
            MAM_PLUGIN_URL . 'admin/js/admin.js',
            [ 'jquery', 'media-upload' ],
            MAM_VERSION,
            true
        );

        wp_localize_script( 'mam-admin', 'mamAdmin', [
            'i18n' => [
                'selectImage' => __( 'Select Image', 'my-ads-manager' ),
                'useImage'    => __( 'Use Image', 'my-ads-manager' ),
            ],
        ] );
    }

    // ── Ad Metaboxes ────────────────────────────────────────

    public function add_ad_metaboxes() {
        add_meta_box( 'mam_ad_type',     __( 'Ad Type', 'my-ads-manager' ),          [ $this, 'meta_ad_type' ],     'mam_ad' );
        add_meta_box( 'mam_ad_content',  __( 'Ad Content', 'my-ads-manager' ),       [ $this, 'meta_ad_content' ],   'mam_ad' );
        add_meta_box( 'mam_ad_image',    __( 'Image Settings', 'my-ads-manager' ),   [ $this, 'meta_ad_image' ],    'mam_ad' );
        add_meta_box( 'mam_ad_size',     __( 'Size', 'my-ads-manager' ),             [ $this, 'meta_ad_size' ],     'mam_ad' );
        add_meta_box( 'mam_ad_link',     __( 'Link & Tracking', 'my-ads-manager' ),  [ $this, 'meta_ad_link' ],     'mam_ad' );
        add_meta_box( 'mam_ad_expiry',   __( 'Expiry Date', 'my-ads-manager' ),      [ $this, 'meta_ad_expiry' ],   'mam_ad' );
        add_meta_box( 'mam_ad_group',    __( 'Ad Groups & Rotation', 'my-ads-manager' ), [ $this, 'meta_ad_group' ],  'mam_ad' );
    }

    public function meta_ad_type( WP_Post $post ) {
        $current = get_post_meta( $post->ID, MAM_Ad::META_TYPE, true ) ?: MAM_Ad::TYPE_PLAIN;
        wp_nonce_field( 'mam_save_ad', 'mam_nonce' );
        $types = [
            MAM_Ad::TYPE_PLAIN          => __( 'Plain Text and Code', 'my-ads-manager' ),
            MAM_Ad::TYPE_DUMMY          => __( 'Dummy', 'my-ads-manager' ),
            MAM_Ad::TYPE_RICH           => __( 'Rich Content', 'my-ads-manager' ),
            MAM_Ad::TYPE_IMAGE          => __( 'Image Ad', 'my-ads-manager' ),
            MAM_Ad::TYPE_AD_GROUP       => __( 'Ad Group', 'my-ads-manager' ),
            MAM_Ad::TYPE_GOOGLE_AD_MGR  => __( 'Google Ad Manager', 'my-ads-manager' ),
            MAM_Ad::TYPE_AMP            => __( 'AMP', 'my-ads-manager' ),
            MAM_Ad::TYPE_ADSENSE        => __( 'AdSense ad', 'my-ads-manager' ),
        ];
        ?>
        <div class="mam-type-options">
            <?php foreach ( $types as $value => $label ) : ?>
                <label style="display:block;margin:8px 0;">
                    <input type="radio" name="mam_ad_type" value="<?php echo esc_attr( $value ); ?>" <?php checked( $current, $value ); ?> />
                    <?php echo esc_html( $label ); ?>
                </label>
            <?php endforeach; ?>
        </div>
        <?php
    }

    public function meta_ad_content( WP_Post $post ) {
        $content = get_post_meta( $post->ID, MAM_Ad::META_CONTENT, true );
        ?>
        <textarea name="mam_ad_content" rows="8" style="width:100%;font-family:monospace;border:1px solid #ddd;padding:8px;"><?php echo esc_textarea( $content ); ?></textarea>
        <p class="description"><?php _e( 'Paste ad code, HTML, or script tags here.', 'my-ads-manager' ); ?></p>
        <?php
    }

    public function meta_ad_image( WP_Post $post ) {
        $image_id  = (int) get_post_meta( $post->ID, MAM_Ad::META_IMAGE_ID, true );
        $image_url = esc_url( get_post_meta( $post->ID, MAM_Ad::META_IMAGE_URL, true ) );
        ?>
        <div id="mam-image-preview">
            <?php if ( $image_url ) : ?>
                <img src="<?php echo $image_url; ?>" style="max-width:200px;height:auto;margin-bottom:10px;" />
            <?php endif; ?>
        </div>
        <input type="hidden" id="mam_ad_image_id" name="mam_ad_image_id" value="<?php echo $image_id; ?>" />
        <input type="hidden" id="mam_ad_image_url" name="mam_ad_image_url" value="<?php echo $image_url; ?>" />
        <button type="button" class="button mam-upload-image"><?php _e( 'Select Image', 'my-ads-manager' ); ?></button>
        <?php
    }

    public function meta_ad_size( WP_Post $post ) {
        $width  = get_post_meta( $post->ID, MAM_Ad::META_WIDTH, true );
        $height = get_post_meta( $post->ID, MAM_Ad::META_HEIGHT, true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="mam_ad_width"><?php _e( 'Width (px)', 'my-ads-manager' ); ?></label></th>
                <td><input type="number" id="mam_ad_width" name="mam_ad_width" value="<?php echo esc_attr( $width ); ?>" style="width:100px;" /></td>
            </tr>
            <tr>
                <th><label for="mam_ad_height"><?php _e( 'Height (px)', 'my-ads-manager' ); ?></label></th>
                <td><input type="number" id="mam_ad_height" name="mam_ad_height" value="<?php echo esc_attr( $height ); ?>" style="width:100px;" /></td>
            </tr>
        </table>
        <?php
    }

    public function meta_ad_link( WP_Post $post ) {
        $link      = esc_url( get_post_meta( $post->ID, MAM_Ad::META_LINK, true ) );
        $target    = get_post_meta( $post->ID, MAM_Ad::META_LINK_TARGET, true ) ?: '_self';
        $tracking  = get_post_meta( $post->ID, MAM_Ad::META_TRACKING, true ) ?: 'default';
        $nofollow  = (bool) get_post_meta( $post->ID, MAM_Ad::META_LINK_NOFOLLOW, true );
        $sponsored = (bool) get_post_meta( $post->ID, MAM_Ad::META_LINK_SPONSORED, true );
        ?>
        <table class="form-table">
            <tr>
                <th><label for="mam_ad_link"><?php _e( 'Target URL', 'my-ads-manager' ); ?></label></th>
                <td><input type="url" id="mam_ad_link" name="mam_ad_link" value="<?php echo $link; ?>" style="width:100%;max-width:500px;" /></td>
            </tr>
            <tr>
                <th><?php _e( 'Target Window', 'my-ads-manager' ); ?></th>
                <td>
                    <label><input type="radio" name="mam_ad_link_target" value="_self" <?php checked( $target, '_self' ); ?> /> <?php _e( 'Same Window', 'my-ads-manager' ); ?></label><br>
                    <label><input type="radio" name="mam_ad_link_target" value="_blank" <?php checked( $target, '_blank' ); ?> /> <?php _e( 'New Window', 'my-ads-manager' ); ?></label>
                </td>
            </tr>
            <tr>
                <th><?php _e( 'Tracking', 'my-ads-manager' ); ?></th>
                <td>
                    <select name="mam_ad_tracking" style="width:100%;max-width:300px;">
                        <option value="default" <?php selected( $tracking, 'default' ); ?>><?php _e( 'Default (Impressions & Clicks)', 'my-ads-manager' ); ?></option>
                        <option value="manual" <?php selected( $tracking, 'manual' ); ?>><?php _e( 'Manual', 'my-ads-manager' ); ?></option>
                    </select>
                </td>
            </tr>
            <tr>
                <th></th>
                <td>
                    <label><input type="checkbox" name="mam_ad_link_nofollow" value="1" <?php checked( $nofollow, true ); ?> /> <?php _e( 'Add "nofollow"', 'my-ads-manager' ); ?></label><br>
                    <label><input type="checkbox" name="mam_ad_link_sponsored" value="1" <?php checked( $sponsored, true ); ?> /> <?php _e( 'Add "sponsored"', 'my-ads-manager' ); ?></label>
                </td>
            </tr>
        </table>
        <?php
    }

    public function meta_ad_expiry( WP_Post $post ) {
        $expiry = get_post_meta( $post->ID, MAM_Ad::META_EXPIRY, true );
        ?>
        <input type="datetime-local" name="mam_ad_expiry" value="<?php echo esc_attr( $expiry ); ?>" style="width:100%;max-width:400px;" />
        <p class="description"><?php _e( 'Leave blank for no expiry.', 'my-ads-manager' ); ?></p>
        <?php
    }

    public function meta_ad_group( WP_Post $post ) {
        $group_id = (int) get_post_meta( $post->ID, MAM_Ad::META_GROUP_ID, true );
        $groups   = get_posts( [ 'post_type' => 'mam_group', 'posts_per_page' => -1 ] );
        ?>
        <select name="mam_ad_group_id" style="width:100%;max-width:300px;">
            <option value=""><?php _e( 'None', 'my-ads-manager' ); ?></option>
            <?php foreach ( $groups as $group ) : ?>
                <option value="<?php echo esc_attr( $group->ID ); ?>" <?php selected( $group_id, $group->ID ); ?>>
                    <?php echo esc_html( $group->post_title ); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php
    }

    public function save_ad_meta( int $post_id ) {
        if ( ! isset( $_POST['mam_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mam_nonce'] ) ), 'mam_save_ad' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;

        // Type
        if ( isset( $_POST['mam_ad_type'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_TYPE, sanitize_text_field( wp_unslash( $_POST['mam_ad_type'] ) ) );
        }

        // Content (allow scripts)
        if ( isset( $_POST['mam_ad_content'] ) ) {
            $allowed = array_merge( wp_kses_allowed_html( 'post' ), [
                'script' => [ 'src' => true, 'async' => true, 'defer' => true, 'type' => true, 'data-*' => true ],
                'ins'    => [ 'class' => true, 'data-ad-*' => true, 'style' => true ],
            ] );
            update_post_meta( $post_id, MAM_Ad::META_CONTENT, wp_kses( wp_unslash( $_POST['mam_ad_content'] ), $allowed ) );
        }

        // Image
        if ( isset( $_POST['mam_ad_image_id'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_IMAGE_ID, absint( $_POST['mam_ad_image_id'] ) );
        }
        if ( isset( $_POST['mam_ad_image_url'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_IMAGE_URL, esc_url_raw( wp_unslash( $_POST['mam_ad_image_url'] ) ) );
        }

        // Size
        if ( isset( $_POST['mam_ad_width'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_WIDTH, absint( $_POST['mam_ad_width'] ) );
        }
        if ( isset( $_POST['mam_ad_height'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_HEIGHT, absint( $_POST['mam_ad_height'] ) );
        }

        // Link
        if ( isset( $_POST['mam_ad_link'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_LINK, esc_url_raw( wp_unslash( $_POST['mam_ad_link'] ) ) );
        }
        if ( isset( $_POST['mam_ad_link_target'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_LINK_TARGET, sanitize_text_field( wp_unslash( $_POST['mam_ad_link_target'] ) ) );
        }
        if ( isset( $_POST['mam_ad_link_nofollow'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_LINK_NOFOLLOW, (bool) $_POST['mam_ad_link_nofollow'] );
        } else {
            delete_post_meta( $post_id, MAM_Ad::META_LINK_NOFOLLOW );
        }
        if ( isset( $_POST['mam_ad_link_sponsored'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_LINK_SPONSORED, (bool) $_POST['mam_ad_link_sponsored'] );
        } else {
            delete_post_meta( $post_id, MAM_Ad::META_LINK_SPONSORED );
        }

        // Tracking
        if ( isset( $_POST['mam_ad_tracking'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_TRACKING, sanitize_text_field( wp_unslash( $_POST['mam_ad_tracking'] ) ) );
        }

        // Expiry
        if ( isset( $_POST['mam_ad_expiry'] ) && $_POST['mam_ad_expiry'] ) {
            update_post_meta( $post_id, MAM_Ad::META_EXPIRY, sanitize_text_field( wp_unslash( $_POST['mam_ad_expiry'] ) ) );
        } else {
            delete_post_meta( $post_id, MAM_Ad::META_EXPIRY );
        }

        // Group
        if ( isset( $_POST['mam_ad_group_id'] ) ) {
            update_post_meta( $post_id, MAM_Ad::META_GROUP_ID, absint( $_POST['mam_ad_group_id'] ) );
        } else {
            delete_post_meta( $post_id, MAM_Ad::META_GROUP_ID );
        }
    }

    // ── Ad List Columns ─────────────────────────────────────

    public function ad_columns( $columns ) {
        unset( $columns['date'] );
        $columns['type']       = __( 'Type', 'my-ads-manager' );
        $columns['used']       = __( 'Used', 'my-ads-manager' );
        $columns['impressions'] = __( 'Impressions', 'my-ads-manager' );
        $columns['clicks']     = __( 'Clicks', 'my-ads-manager' );
        $columns['ctr']        = __( 'CTR', 'my-ads-manager' );
        $columns['expiry']     = __( 'Expiry', 'my-ads-manager' );
        return $columns;
    }

    public function ad_column_output( $column, $post_id ) {
        $ad = new MAM_Ad( $post_id );

        switch ( $column ) {
            case 'type':
                echo esc_html( ucfirst( str_replace( '_', ' ', $ad->get_type() ) ) );
                break;

            case 'used':
                echo '—';
                break;

            case 'impressions':
                $stats = MAM_Ad::get_stats( $post_id );
                echo number_format( $stats['impressions'] );
                break;

            case 'clicks':
                $stats = MAM_Ad::get_stats( $post_id );
                echo number_format( $stats['clicks'] );
                break;

            case 'ctr':
                $stats = MAM_Ad::get_stats( $post_id );
                echo $stats['ctr'] . '%';
                break;

            case 'expiry':
                $expiry = $ad->get_expiry();
                if ( ! $expiry ) {
                    echo '—';
                } elseif ( $ad->is_expired() ) {
                    echo '<span style="color:red;">' . esc_html( $expiry ) . ' (Expired)</span>';
                } else {
                    echo esc_html( $expiry );
                }
                break;
        }
    }

    // ── Dashboard ────────────────────────────────────────────

    public function dashboard_page() {
        global $wpdb;
        $table = $wpdb->prefix . 'mam_stats';

        $total_ads = wp_count_posts( 'mam_ad' )->publish ?? 0;
        $total_groups = wp_count_posts( 'mam_group' )->publish ?? 0;

        // Get stats
        $impressions = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE event_type = 'impression'" );
        $clicks      = (int) $wpdb->get_var( "SELECT COUNT(*) FROM `{$table}` WHERE event_type = 'click'" );
        $ctr = $impressions > 0 ? round( $clicks / $impressions * 100, 2 ) : 0;

        ?>
        <div class="wrap mam-dashboard">
            <h1><?php _e( 'My Ads Manager Dashboard', 'my-ads-manager' ); ?></h1>

            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin:20px 0;">
                <div style="background:#fff;padding:20px;border:1px solid #e0e0e0;border-radius:6px;text-align:center;">
                    <div style="font-size:32px;font-weight:700;color:#2271b1;"><?php echo esc_html( $total_ads ); ?></div>
                    <div style="color:#666;"><?php _e( 'Total Ads', 'my-ads-manager' ); ?></div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #e0e0e0;border-radius:6px;text-align:center;">
                    <div style="font-size:32px;font-weight:700;color:#2271b1;"><?php echo esc_html( $total_groups ); ?></div>
                    <div style="color:#666;"><?php _e( 'Ad Groups', 'my-ads-manager' ); ?></div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #e0e0e0;border-radius:6px;text-align:center;">
                    <div style="font-size:32px;font-weight:700;color:#2271b1;"><?php echo number_format( $impressions ); ?></div>
                    <div style="color:#666;"><?php _e( 'Impressions', 'my-ads-manager' ); ?></div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #e0e0e0;border-radius:6px;text-align:center;">
                    <div style="font-size:32px;font-weight:700;color:#2271b1;"><?php echo number_format( $clicks ); ?></div>
                    <div style="color:#666;"><?php _e( 'Clicks', 'my-ads-manager' ); ?></div>
                </div>
                <div style="background:#fff;padding:20px;border:1px solid #e0e0e0;border-radius:6px;text-align:center;">
                    <div style="font-size:32px;font-weight:700;color:#2271b1;"><?php echo esc_html( $ctr ); ?>%</div>
                    <div style="color:#666;"><?php _e( 'Click-Through Rate', 'my-ads-manager' ); ?></div>
                </div>
            </div>
        </div>
        <?php
    }

    // ── Settings ─────────────────────────────────────────────

    public function settings_page() {
        if ( isset( $_POST['mam_settings_nonce'] ) &&
             wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mam_settings_nonce'] ) ), 'mam_save_settings' )
        ) {
            update_option( 'mam_track_impressions', isset( $_POST['mam_track_impressions'] ) ? 1 : 0 );
            update_option( 'mam_track_clicks', isset( $_POST['mam_track_clicks'] ) ? 1 : 0 );
            update_option( 'mam_disable_for_admins', isset( $_POST['mam_disable_for_admins'] ) ? 1 : 0 );
            echo '<div class="notice notice-success"><p>' . esc_html__( 'Settings saved.', 'my-ads-manager' ) . '</p></div>';
        }

        $track_imp     = get_option( 'mam_track_impressions', 1 );
        $track_clicks  = get_option( 'mam_track_clicks', 1 );
        $disable_admin = get_option( 'mam_disable_for_admins', 1 );
        ?>
        <div class="wrap">
            <h1><?php _e( 'My Ads Manager Settings', 'my-ads-manager' ); ?></h1>
            <form method="post">
                <?php wp_nonce_field( 'mam_save_settings', 'mam_settings_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="mam_track_impressions"><?php _e( 'Track Impressions', 'my-ads-manager' ); ?></label></th>
                        <td><input type="checkbox" id="mam_track_impressions" name="mam_track_impressions" value="1" <?php checked( $track_imp, 1 ); ?> /></td>
                    </tr>
                    <tr>
                        <th><label for="mam_track_clicks"><?php _e( 'Track Clicks', 'my-ads-manager' ); ?></label></th>
                        <td><input type="checkbox" id="mam_track_clicks" name="mam_track_clicks" value="1" <?php checked( $track_clicks, 1 ); ?> /></td>
                    </tr>
                    <tr>
                        <th><label for="mam_disable_for_admins"><?php _e( 'Disable Ads for Admins', 'my-ads-manager' ); ?></label></th>
                        <td><input type="checkbox" id="mam_disable_for_admins" name="mam_disable_for_admins" value="1" <?php checked( $disable_admin, 1 ); ?> /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
