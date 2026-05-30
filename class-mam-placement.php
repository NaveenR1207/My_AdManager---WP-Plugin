<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Placements: where and how ads display
 *
 * Types: Grid, Manual (shortcode only)
 * Positions: default, left, center, right
 * Can target specific pages via display conditions
 */
class MAM_Placement {

    const META_TYPE       = 'mam_placement_type';       // placement type
    const META_ITEM_ID    = 'mam_placement_item_id';    // ad or group ID
    const META_POSITION   = 'mam_placement_position';   // default, left, center, right
    const META_LABEL      = 'mam_placement_label';      // default, enabled, disabled
    const META_CLEARFIX   = 'mam_placement_clearfix';   // checkbox
    const META_CSS        = 'mam_placement_css';        // inline CSS
    const META_CONDITIONS = 'mam_placement_conditions'; // display conditions

    public function __construct() {
        add_action( 'add_meta_boxes_mam_placement', [ $this, 'add_metaboxes' ] );
        add_action( 'save_post_mam_placement', [ $this, 'save_meta' ] );
    }

    public function add_metaboxes() {
        add_meta_box(
            'mam_placement_settings',
            __( 'Placement Settings', 'my-ads-manager' ),
            [ $this, 'render_metabox' ],
            'mam_placement',
            'normal',
            'high'
        );
    }

    public function render_metabox( WP_Post $post ) {
        wp_nonce_field( 'mam_save_placement', 'mam_placement_nonce' );

        $placement_type = get_post_meta( $post->ID, self::META_TYPE, true ) ?: 'manual';
        $item_id   = (int) get_post_meta( $post->ID, self::META_ITEM_ID, true );
        $position  = get_post_meta( $post->ID, self::META_POSITION, true ) ?: 'default';
        $label     = get_post_meta( $post->ID, self::META_LABEL, true ) ?: 'default';
        $clearfix  = (bool) get_post_meta( $post->ID, self::META_CLEARFIX, true );
        $css       = get_post_meta( $post->ID, self::META_CSS, true );


        $placement_types = [
            'manual' => __( 'Manual / Shortcode', 'my-ads-manager' ),
            'header' => __( 'Header', 'my-ads-manager' ),
            'footer' => __( 'Footer', 'my-ads-manager' ),
            'sidebar' => __( 'Sidebar', 'my-ads-manager' ),
            'inline' => __( 'Inline Content', 'my-ads-manager' ),
            'popup' => __( 'Popup', 'my-ads-manager' ),
            'floating' => __( 'Floating Bar', 'my-ads-manager' ),
        ];

        $ads    = get_posts( [ 'post_type' => 'mam_ad', 'posts_per_page' => -1 ] );
        $groups = get_posts( [ 'post_type' => 'mam_group', 'posts_per_page' => -1 ] );
        ?>

        <div style="margin-bottom:20px;">
            <h2 style="margin-bottom:10px;"><?php _e( 'Choose a placement type', 'my-ads-manager' ); ?></h2>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;max-width:900px;">
                <?php foreach ( $placement_types as $type_key => $type_label ) : ?>
                    <label style="border:2px solid <?php echo $placement_type === $type_key ? '#2271b1' : '#dcdcde'; ?>;border-radius:8px;padding:14px;text-align:center;background:#fff;cursor:pointer;display:block;">
                        <input type="radio" name="mam_placement_type" value="<?php echo esc_attr( $type_key ); ?>" <?php checked( $placement_type, $type_key ); ?> style="margin-bottom:10px;" />
                        <div style="font-weight:600;"><?php echo esc_html( $type_label ); ?></div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <table class="form-table">
            <!-- Item Selection -->
            <tr>
                <th><label for="mam_placement_item_id"><?php _e( 'Item', 'my-ads-manager' ); ?></label></th>
                <td>
                    <select id="mam_placement_item_id" name="mam_placement_item_id" style="width:100%;max-width:400px;">
                        <option value="">-- <?php _e( 'Select Ad or Group', 'my-ads-manager' ); ?> --</option>
                        <optgroup label="<?php esc_attr_e( 'Ads', 'my-ads-manager' ); ?>">
                            <?php foreach ( $ads as $ad ) : ?>
                                <option value="<?php echo esc_attr( $ad->ID ); ?>" data-type="ad" <?php selected( $item_id, $ad->ID ); ?>>
                                    <?php echo esc_html( $ad->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                        <optgroup label="<?php esc_attr_e( 'Groups', 'my-ads-manager' ); ?>">
                            <?php foreach ( $groups as $group ) : ?>
                                <option value="<?php echo esc_attr( $group->ID ); ?>" data-type="group" <?php selected( $item_id, $group->ID ); ?>>
                                    <?php echo esc_html( $group->post_title ); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </td>
            </tr>

            <!-- Position -->
            <tr>
                <th><?php _e( 'Position', 'my-ads-manager' ); ?></th>
                <td>
                    <label style="margin-right:20px;">
                        <input type="radio" name="mam_placement_position" value="default" <?php checked( $position, 'default' ); ?> />
                        <?php _e( 'default', 'my-ads-manager' ); ?>
                    </label>
                    <label style="margin-right:20px;">
                        <input type="radio" name="mam_placement_position" value="left" <?php checked( $position, 'left' ); ?> />
                        <?php _e( 'left', 'my-ads-manager' ); ?>
                    </label>
                    <label style="margin-right:20px;">
                        <input type="radio" name="mam_placement_position" value="center" <?php checked( $position, 'center' ); ?> />
                        <?php _e( 'center', 'my-ads-manager' ); ?>
                    </label>
                    <label>
                        <input type="radio" name="mam_placement_position" value="right" <?php checked( $position, 'right' ); ?> />
                        <?php _e( 'right', 'my-ads-manager' ); ?>
                    </label>
                    <br><br>
                    <label style="display:block;margin-top:10px;">
                        <input type="checkbox" name="mam_placement_clearfix" value="1" <?php checked( $clearfix, true ); ?> />
                        <?php _e( "Check this if you don't want the following elements to float around the ad. (adds a placement_clearfix)", 'my-ads-manager' ); ?>
                    </label>
                </td>
            </tr>

            <!-- Ad Label -->
            <tr>
                <th><?php _e( 'Ad label', 'my-ads-manager' ); ?></th>
                <td>
                    <label style="margin-right:20px;">
                        <input type="radio" name="mam_placement_label" value="default" <?php checked( $label, 'default' ); ?> />
                        <?php _e( 'default', 'my-ads-manager' ); ?>
                    </label>
                    <label style="margin-right:20px;">
                        <input type="radio" name="mam_placement_label" value="enabled" <?php checked( $label, 'enabled' ); ?> />
                        <?php _e( 'enabled', 'my-ads-manager' ); ?>
                    </label>
                    <label>
                        <input type="radio" name="mam_placement_label" value="disabled" <?php checked( $label, 'disabled' ); ?> />
                        <?php _e( 'disabled', 'my-ads-manager' ); ?>
                    </label>
                </td>
            </tr>

            <!-- Inline CSS -->
            <tr>
                <th><label for="mam_placement_css"><?php _e( 'Inline CSS', 'my-ads-manager' ); ?></label></th>
                <td>
                    <input type="text" id="mam_placement_css" name="mam_placement_css" value="<?php echo esc_attr( $css ); ?>" style="width:100%;max-width:400px;" placeholder="e.g., margin: 20px 0; text-align: center;" />
                </td>
            </tr>
        </table>

        <div style="margin-top:20px;padding:15px;background:#fff;border:1px solid #dcdcde;border-radius:4px;">
            <h3 style="margin-top:0;"><?php _e( 'Placement Tag', 'my-ads-manager' ); ?></h3>
            <p><?php _e( 'Use this shortcode or PHP tag to display the placement anywhere on your website.', 'my-ads-manager' ); ?></p>
            <input type="text" readonly value='[mam_placement id="<?php echo esc_attr( $post->ID ); ?>"]' style="width:100%;max-width:500px;margin-bottom:10px;" onclick="this.select();" />
            <textarea readonly style="width:100%;max-width:500px;height:70px;" onclick="this.select();"><?php echo esc_textarea("<?php echo do_shortcode('[mam_placement id=\"{$post->ID}\"]'); ?>"); ?></textarea>
        </div>

        <!-- Display Conditions -->
        <div style="margin-top:20px;padding:15px;background:#f9f9f9;border:1px solid #ddd;border-radius:4px;">
            <h3 style="margin-top:0;"><?php _e( 'Display Conditions', 'my-ads-manager' ); ?></h3>
            <p style="color:#666;">
                <?php _e( 'Use display conditions for placements. The free version provides conditions on the ad edit page.', 'my-ads-manager' ); ?>
                <a href="#" style="color:#2271b1;"><?php _e( 'Pro Feature', 'my-ads-manager' ); ?></a>
            </p>
        </div>
        <?php
    }

    public function save_meta( int $post_id ) {
        if ( ! isset( $_POST['mam_placement_nonce'] ) ) return;
        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mam_placement_nonce'] ) ), 'mam_save_placement' ) ) return;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
        if ( ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( get_post_type( $post_id ) !== 'mam_placement' ) return;

        // Placement Type
        if ( isset( $_POST['mam_placement_type'] ) ) {
            update_post_meta( $post_id, self::META_TYPE, sanitize_text_field( wp_unslash( $_POST['mam_placement_type'] ) ) );
        }

        // Item ID
        if ( isset( $_POST['mam_placement_item_id'] ) ) {
            update_post_meta( $post_id, self::META_ITEM_ID, absint( $_POST['mam_placement_item_id'] ) );
        }

        // Position
        if ( isset( $_POST['mam_placement_position'] ) ) {
            update_post_meta( $post_id, self::META_POSITION, sanitize_text_field( wp_unslash( $_POST['mam_placement_position'] ) ) );
        }

        // Label
        if ( isset( $_POST['mam_placement_label'] ) ) {
            update_post_meta( $post_id, self::META_LABEL, sanitize_text_field( wp_unslash( $_POST['mam_placement_label'] ) ) );
        }

        // Clearfix
        if ( isset( $_POST['mam_placement_clearfix'] ) ) {
            update_post_meta( $post_id, self::META_CLEARFIX, (bool) $_POST['mam_placement_clearfix'] );
        } else {
            delete_post_meta( $post_id, self::META_CLEARFIX );
        }

        // CSS
        if ( isset( $_POST['mam_placement_css'] ) ) {
            update_post_meta( $post_id, self::META_CSS, sanitize_text_field( wp_unslash( $_POST['mam_placement_css'] ) ) );
        }
    }

    /**
     * Render a placement by ID
     */
    public static function render( int $placement_id ): string {
        $post = get_post( $placement_id );
        if ( ! $post || $post->post_type !== 'mam_placement' ) {
            return '';
        }

        $item_id  = (int) get_post_meta( $placement_id, self::META_ITEM_ID, true );
        $position = get_post_meta( $placement_id, self::META_POSITION, true ) ?: 'default';
        $label    = get_post_meta( $placement_id, self::META_LABEL, true ) ?: 'default';
        $clearfix = (bool) get_post_meta( $placement_id, self::META_CLEARFIX, true );
        $css      = get_post_meta( $placement_id, self::META_CSS, true );

        if ( ! $item_id ) return '';

        // Determine if ad or group
        $ad_post = get_post( $item_id );
        if ( ! $ad_post ) return '';

        $html = '';
        if ( $ad_post->post_type === 'mam_ad' ) {
            $html = MAM_Ad::render_id( $item_id );
        } elseif ( $ad_post->post_type === 'mam_group' ) {
            $html = MAM_Group::render( $item_id );
        }

        if ( ! $html ) return '';

        // Apply positioning and CSS
        $style = '';
        if ( $position === 'left' ) {
            $style .= 'float:left;';
        } elseif ( $position === 'center' ) {
            $style .= 'text-align:center;margin:0 auto;';
        } elseif ( $position === 'right' ) {
            $style .= 'float:right;';
        }
        if ( $css ) {
            $style .= $css;
        }

        $wrapper = '<div class="mam-placement mam-placement-' . esc_attr( $placement_id ) . '"' . ( $style ? ' style="' . esc_attr( $style ) . '"' : '' ) . '>' . $html . '</div>';

        if ( $clearfix ) {
            $wrapper .= '<div class="placement_clearfix" style="clear:both;"></div>';
        }

        return $wrapper;
    }
}
