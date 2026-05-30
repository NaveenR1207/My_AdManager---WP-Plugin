<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Ad {

    public $id;
    public $meta = [];

    // Ad types
    const TYPE_PLAIN           = 'plain';
    const TYPE_IMAGE           = 'image';
    const TYPE_ADSENSE         = 'adsense';
    const TYPE_RICH            = 'rich';
    const TYPE_DUMMY           = 'dummy';
    const TYPE_AD_GROUP        = 'ad_group';
    const TYPE_GOOGLE_AD_MGR   = 'google_ad_manager';
    const TYPE_AMP             = 'amp';

    // Meta keys
    const META_TYPE            = 'mam_ad_type';
    const META_WIDTH           = 'mam_ad_width';
    const META_HEIGHT          = 'mam_ad_height';
    const META_IMAGE_URL       = 'mam_ad_image_url';
    const META_IMAGE_ID        = 'mam_ad_image_id';
    const META_LINK            = 'mam_ad_link';
    const META_LINK_TARGET     = 'mam_ad_link_target';
    const META_LINK_NOFOLLOW   = 'mam_ad_link_nofollow';
    const META_LINK_SPONSORED  = 'mam_ad_link_sponsored';
    const META_CONTENT         = 'mam_ad_content';
    const META_EXPIRY          = 'mam_ad_expiry';
    const META_TRACKING        = 'mam_ad_tracking';
    const META_GROUP_ID        = 'mam_ad_group_id';

    public function __construct( int $ad_id ) {
        $this->id = $ad_id;
        $this->load_meta();
    }

    private function load_meta() {
        $raw = get_post_meta( $this->id );
        foreach ( $raw as $k => $v ) {
            $this->meta[ $k ] = maybe_unserialize( is_array( $v ) ? $v[0] : $v );
        }
    }

    // ── Getters ──────────────────────────────────────────────

    public function get_type(): string {
        return $this->meta[ self::META_TYPE ] ?? self::TYPE_PLAIN;
    }

    public function get_width(): int {
        return (int) ( $this->meta[ self::META_WIDTH ] ?? 0 );
    }

    public function get_height(): int {
        return (int) ( $this->meta[ self::META_HEIGHT ] ?? 0 );
    }

    public function get_image_url(): string {
        return $this->meta[ self::META_IMAGE_URL ] ?? '';
    }

    public function get_image_id(): int {
        return (int) ( $this->meta[ self::META_IMAGE_ID ] ?? 0 );
    }

    public function get_link(): string {
        return $this->meta[ self::META_LINK ] ?? '';
    }

    public function get_link_target(): string {
        return $this->meta[ self::META_LINK_TARGET ] ?? '_self';
    }

    public function get_content(): string {
        return $this->meta[ self::META_CONTENT ] ?? '';
    }

    public function get_expiry(): string {
        return $this->meta[ self::META_EXPIRY ] ?? '';
    }

    public function get_tracking(): string {
        return $this->meta[ self::META_TRACKING ] ?? 'default';
    }

    public function get_group_id(): int {
        return (int) ( $this->meta[ self::META_GROUP_ID ] ?? 0 );
    }

    public function has_nofollow(): bool {
        return (bool) ( $this->meta[ self::META_LINK_NOFOLLOW ] ?? false );
    }

    public function has_sponsored(): bool {
        return (bool) ( $this->meta[ self::META_LINK_SPONSORED ] ?? false );
    }

    // ── Status checks ────────────────────────────────────────

    public function is_active(): bool {
        if ( get_post_status( $this->id ) !== 'publish' ) {
            return false;
        }
        $expiry = $this->get_expiry();
        if ( $expiry && strtotime( $expiry ) < time() ) {
            return false;
        }
        return true;
    }

    public function is_expired(): bool {
        $expiry = $this->get_expiry();
        return $expiry && strtotime( $expiry ) < time();
    }

    // ── Render ───────────────────────────────────────────────

    public function render( array $args = [] ): string {
        if ( ! $this->is_active() ) {
            return '';
        }

        // Check admin setting
        if ( get_option( 'mam_disable_for_admins', 1 ) && is_user_logged_in() && current_user_can( 'manage_options' ) ) {
            return '';
        }

        $html = $this->build_html( $args );
        if ( ! $html ) return '';

        // Wrap in tracking div
        $html = '<div class="mam-ad mam-ad-' . esc_attr( $this->id ) . '" data-ad-id="' . esc_attr( $this->id ) . '">' . $html . '</div>';

        return $html;
    }

    private function build_html( array $args ): string {
        $type = $this->get_type();

        switch ( $type ) {

            case self::TYPE_IMAGE:
                return $this->render_image();

            case self::TYPE_ADSENSE:
                return '<div class="mam-adsense">' . $this->get_content() . '</div>';

            case self::TYPE_RICH:
                return '<div class="mam-rich">' . wp_kses_post( $this->get_content() ) . '</div>';

            case self::TYPE_DUMMY:
                $w = $this->get_width()  ?: 300;
                $h = $this->get_height() ?: 250;
                return '<div class="mam-dummy" style="width:' . $w . 'px;height:' . $h . 'px;background:#e9e9e9;display:flex;align-items:center;justify-content:center;color:#999;font-size:12px;border:1px solid #ddd;">' . $w . 'x' . $h . ' Ad</div>';

            case self::TYPE_PLAIN:
            default:
                return $this->get_content();
        }
    }

    private function render_image(): string {
        $img    = esc_url( $this->get_image_url() );
        $link   = esc_url( $this->get_link() );
        $title  = esc_attr( get_the_title( $this->id ) );
        $w      = $this->get_width()  ? ' width="'  . (int) $this->get_width()  . '"' : '';
        $h      = $this->get_height() ? ' height="' . (int) $this->get_height() . '"' : '';
        $target = esc_attr( $this->get_link_target() );
        $rel    = $this->build_rel_attr();

        if ( ! $img ) return '';

        if ( $link ) {
            return '<a href="' . $link . '" target="' . $target . '" rel="' . $rel . '" class="mam-image-link" data-mam-click="' . (int) $this->id . '"><img src="' . $img . '" alt="' . $title . '" loading="lazy"' . $w . $h . '></a>';
        }

        return '<img src="' . $img . '" alt="' . $title . '" loading="lazy"' . $w . $h . '>';
    }

    private function build_rel_attr(): string {
        $rels = [];
        if ( $this->has_nofollow() ) {
            $rels[] = 'nofollow';
        }
        if ( $this->has_sponsored() ) {
            $rels[] = 'sponsored';
        }
        if ( empty( $rels ) ) {
            $rels[] = 'noopener';
        }
        return implode( ' ', $rels );
    }

    // ── Static helpers ───────────────────────────────────────

    public static function render_id( int $id, array $args = [] ): string {
        $ad = new self( $id );
        return $ad->render( $args );
    }

    public static function get_all_ids(): array {
        return get_posts( [
            'post_type'      => 'mam_ad',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ] );
    }

    public static function get_stats( int $ad_id ): array {
        global $wpdb;
        $table = $wpdb->prefix . 'mam_stats';

        $impressions = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table}` WHERE ad_id = %d AND event_type = 'impression'",
                $ad_id
            )
        );

        $clicks = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM `{$table}` WHERE ad_id = %d AND event_type = 'click'",
                $ad_id
            )
        );

        return [
            'impressions' => $impressions,
            'clicks'      => $clicks,
            'ctr'         => $impressions > 0 ? round( $clicks / $impressions * 100, 2 ) : 0,
        ];
    }
}
