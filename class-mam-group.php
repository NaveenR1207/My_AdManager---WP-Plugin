<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Group {

    const META_TYPE  = 'mam_group_type';
    const META_AIDS  = 'mam_group_ad_ids';
    const META_WEIGHTS = 'mam_group_weights';
    const META_VISIBLE = 'mam_group_visible_ads';

    const TYPE_RANDOM    = 'random';
    const TYPE_ORDERED   = 'ordered';
    const TYPE_GRID      = 'grid';
    const TYPE_SLIDER    = 'slider';

    public function __construct() {
        add_action( 'add_meta_boxes_mam_group', [ $this, 'add_metaboxes' ] );
        add_action( 'save_post_mam_group', [ $this, 'save_meta' ] );
    }

    // ── Metaboxes ────────────────────────────────────────────

    public function add_metaboxes() {
        add_meta_box(
            'mam_group_settings',
            __( 'Group Settings', 'my-ads-manager' ),
            [ $this, 'render_metabox' ],
            'mam_group',
            'normal',
            'high'
        );
    }

    public function render_metabox( WP_Post $post ) {
        wp_nonce_field( 'mam_save_group', 'mam_group_nonce' );

        $type      = get_post_meta( $post->ID, self::META_TYPE, true ) ?: self::TYPE_RANDOM;
        $ad_ids    = (array) maybe_unserialize( get_post_meta( $post->ID, self::META_AIDS, true ) ?: [] );
        $weights   = (array) maybe_unserialize( get_post_meta( $post->ID, self::META_WEIGHTS, true ) ?: [] );
        $visible   = (int) get_post_meta( $post->ID, self::META_VISIBLE, true ) ?: 1;

        $all_ads = get_posts( [ 'post_type' => 'mam_ad', 'posts_per_page' => -1 ] );
        ?>

        <!-- Type Selection -->
        <div style="margin-bottom:20px;padding:15px;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;">
            <strong><?php _e( 'Type', 'my-ads-manager' ); ?></strong><br><br>
            <label style="margin-right:20px;">
                <input type="radio" name="mam_group_type" value="<?php echo self::TYPE_RANDOM; ?>" <?php checked( $type, self::TYPE_RANDOM ); ?> />
                <?php _e( 'Random ads', 'my-ads-manager' ); ?>
            </label>
            <label style="margin-right:20px;">
                <input type="radio" name="mam_group_type" value="<?php echo self::TYPE_ORDERED; ?>" <?php checked( $type, self::TYPE_ORDERED ); ?> />
                <?php _e( 'Ordered ads', 'my-ads-manager' ); ?>
            </label>
            <label style="margin-right:20px;">
                <input type="radio" name="mam_group_type" value="<?php echo self::TYPE_GRID; ?>" <?php checked( $type, self::TYPE_GRID ); ?> />
                <?php _e( 'Grid', 'my-ads-manager' ); ?>
            </label>
            <label>
                <input type="radio" name="mam_group_type" value="<?php echo self::TYPE_SLIDER; ?>" <?php checked( $type, self::TYPE_SLIDER ); ?> />
                <?php _e( 'Ad Slider', 'my-ads-manager' ); ?>
            </label>
        </div>

        <!-- Visible Ads -->
        <div style="margin-bottom:20px;">
            <label><strong><?php _e( 'Visible ads', 'my-ads-manager' ); ?></strong></label><br>
            <input type="number" name="mam_group_visible_ads" value="<?php echo esc_attr( $visible ); ?>" min="1" style="width:80px;" />
            <p style="color:#666;font-size:12px;margin:5px 0 0;"><?php _e( 'Number of ads that are visible at the same time', 'my-ads-manager' ); ?></p>
        </div>

        <!-- Ads Table -->
        <div style="margin-top:20px;">
            <strong><?php _e( 'Ads', 'my-ads-manager' ); ?></strong>
            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                <thead>
                    <tr style="background:#f0f0f0;border-bottom:2px solid #ddd;">
                        <th style="padding:8px;text-align:left;width:30px;"></th>
                        <th style="padding:8px;text-align:left;"><?php _e( 'Ad', 'my-ads-manager' ); ?></th>
                        <th style="padding:8px;text-align:center;width:100px;"><?php _e( 'Weight', 'my-ads-manager' ); ?></th>
                        <th style="padding:8px;text-align:center;width:80px;"></th>
                    </tr>
                </thead>
                <tbody id="mam-group-ads">
                    <?php foreach ( $ad_ids as $aid ) :
                        $ad_post = get_post( $aid );
                        if ( ! $ad_post ) continue;
                        $weight = (int) ( $weights[ $aid ] ?? 1 );
                    ?>
                        <tr style="border-bottom:1px solid #ddd;">
                            <td style="padding:8px;text-align:center;"><span class="dashicons dashicons-image-rotate" style="cursor:move;"></span></td>
                            <td style="padding:8px;">
                                <a href="<?php echo esc_url( get_edit_post_link( $aid ) ); ?>" target="_blank">
                                    <?php echo esc_html( $ad_post->post_title ); ?>
                                </a>
                            </td>
                            <td style="padding:8px;text-align:center;">
                                <input type="hidden" name="mam_group_ad_ids[]" value="<?php echo esc_attr( $aid ); ?>" />
                                <input type="number" name="mam_group_weights[]" value="<?php echo esc_attr( $weight ); ?>" min="1" style="width:60px;text-align:center;" />
                            </td>
                            <td style="padding:8px;text-align:center;">
                                <a href="#" class="mam-remove-ad" style="color:red;text-decoration:none;">
                                    <?php _e( 'Delete', 'my-ads-manager' ); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Add New Ad -->
            <table style="width:100%;border-collapse:collapse;margin-top:10px;">
                <tr style="border-top:2px solid #ddd;background:#fafafa;">
                    <th style="padding:8px;text-align:left;width:30px;"></th>
                    <td style="padding:8px;">
                        <label style="font-weight:normal;">
                            <strong><?php _e( 'New Ad', 'my-ads-manager' ); ?></strong>
                        </label>
                        <select id="mam-new-ad-select" style="width:100%;max-width:500px;margin-top:8px;">
                            <option value=""><?php _e( '-- Select Ad --', 'my-ads-manager' ); ?></option>
                            <?php foreach ( $all_ads as $ad ) : ?>
                                <option value="<?php echo esc_attr( $ad->ID ); ?>">
                                    <?php echo esc_html( $ad->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                    <td style="padding:8px;text-align:center;">
                        <input type="number" id="mam-new-ad-weight" value="10" min="1" style="width:60px;text-align:center;" />
                    </td>
                    <td style="padding:8px;text-align:center;">
                        <button type="button" class="button button-primary" id="mam-add-ad-btn"><?php _e( 'Add', 'my-ads-manager' ); ?></button>
                    </td>
                </tr>
            </table>
        </div>

        <script>
        jQuery(function($) {
            $('#mam-add-ad-btn').on('click', function(e) {
                e.preventDefault();
                var adId = $('#mam-new-ad-select').val();
                var weight = $('#mam-new-ad-weight').val();
                if (!adId) {
                    alert('<?php esc_html_e( 'Please select an ad', 'my-ads-manager' ); ?>');
                    return;
                }
                var adTitle = $('#mam-new-ad-select option:selected').text();
                var editUrl = '<?php echo admin_url('post.php?action=edit&post='); ?>' + adId;
                var html = '<tr style="border-bottom:1px solid #ddd;">' +
                    '<td style="padding:8px;text-align:center;"><span class="dashicons dashicons-image-rotate" style="cursor:move;"></span></td>' +
                    '<td style="padding:8px;"><a href="' + editUrl + '" target="_blank">' + adTitle + '</a></td>' +
                    '<td style="padding:8px;text-align:center;"><input type="hidden" name="mam_group_ad_ids[]" value="' + adId + '" /><input type="number" name="mam_group_weights[]" value="' + weight + '" min="1" style="width:60px;text-align:center;" /></td>' +
                    '<td style="padding:8px;text-align:center;"><a href="#" class="mam-remove-ad" style="color:red;text-decoration:none;"><?php esc_html_e( 'Delete', 'my-ads-manager' ); ?></a></td>' +
                    '</tr>';
                $('#mam-group-ads').append(html);
                $('#mam-new-ad-select').val('');
                $('#mam-new-ad-weight').val('10');
            });

            $(document).on('click', '.mam-remove-ad', function(e) {
                e.preventDefault();
                $(this).closest('tr').remove();
            });
        });
        </script>
        <?php
    }

    public function save_meta( int $post_id ) {
        if ( ! isset( $_POST['mam_group_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mam_group_nonce'] ) ), 'mam_save_group' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( get_post_type( $post_id ) !== 'mam_group' ) return;

        // Type
        if ( isset( $_POST['mam_group_type'] ) ) {
            update_post_meta( $post_id, self::META_TYPE, sanitize_text_field( wp_unslash( $_POST['mam_group_type'] ) ) );
        }

        // Visible ads
        if ( isset( $_POST['mam_group_visible_ads'] ) ) {
            update_post_meta( $post_id, self::META_VISIBLE, absint( $_POST['mam_group_visible_ads'] ) );
        }

        // Ads and weights
        $ad_ids = isset( $_POST['mam_group_ad_ids'] ) ? array_map( 'absint', (array) $_POST['mam_group_ad_ids'] ) : [];
        $weights = isset( $_POST['mam_group_weights'] ) ? array_map( 'absint', (array) $_POST['mam_group_weights'] ) : [];

        $final_weights = [];
        foreach ( $ad_ids as $i => $aid ) {
            $final_weights[ $aid ] = $weights[ $i ] ?? 1;
        }

        update_post_meta( $post_id, self::META_AIDS, array_values( $ad_ids ) );
        update_post_meta( $post_id, self::META_WEIGHTS, $final_weights );
    }

    // ── Render methods ──────────────────────────────────────

    public static function render( int $group_id ): string {
        $post = get_post( $group_id );
        if ( ! $post || $post->post_type !== 'mam_group' ) {
            return '';
        }

        $type  = get_post_meta( $group_id, self::META_TYPE, true ) ?: self::TYPE_RANDOM;
        $ad_ids = (array) maybe_unserialize( get_post_meta( $group_id, self::META_AIDS, true ) ?: [] );
        $ad_ids = array_filter( array_map( 'absint', $ad_ids ) );

        if ( empty( $ad_ids ) ) {
            return '';
        }

        switch ( $type ) {
            case self::TYPE_ORDERED:
                return self::render_ordered( $group_id, $ad_ids );
            case self::TYPE_GRID:
            case self::TYPE_SLIDER:
                return self::render_all( $ad_ids );
            case self::TYPE_RANDOM:
            default:
                $pick = $ad_ids[ array_rand( $ad_ids ) ];
                return MAM_Ad::render_id( (int) $pick );
        }
    }

    private static function render_ordered( int $group_id, array $ad_ids ): string {
        $key     = 'mam_group_seq_' . $group_id;
        $current = (int) get_transient( $key );
        $next    = ( $current + 1 ) % count( $ad_ids );
        set_transient( $key, $next, DAY_IN_SECONDS );
        return MAM_Ad::render_id( (int) $ad_ids[ $current ] );
    }

    private static function render_all( array $ad_ids ): string {
        $html = '';
        foreach ( $ad_ids as $id ) {
            $html .= MAM_Ad::render_id( (int) $id );
        }
        return $html;
    }
}
