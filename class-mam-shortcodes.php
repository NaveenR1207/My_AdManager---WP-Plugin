<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Shortcodes {

    public function __construct() {
        add_shortcode( 'mam_ad', [ $this, 'render_ad' ] );
        add_shortcode( 'mam_group', [ $this, 'render_group' ] );
        add_shortcode( 'mam_placement', [ $this, 'render_placement' ] );
    }

    public function render_ad( $atts ): string {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'mam_ad' );
        $ad_id = absint( $atts['id'] );
        if ( ! $ad_id ) return '';

        $post = get_post( $ad_id );
        if ( ! $post || $post->post_type !== 'mam_ad' ) return '';

        return MAM_Ad::render_id( $ad_id );
    }

    public function render_group( $atts ): string {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'mam_group' );
        $group_id = absint( $atts['id'] );
        if ( ! $group_id ) return '';

        return MAM_Group::render( $group_id );
    }

    public function render_placement( $atts ): string {
        $atts = shortcode_atts( [ 'id' => 0 ], $atts, 'mam_placement' );
        $placement_id = absint( $atts['id'] );
        if ( ! $placement_id ) return '';

        return MAM_Placement::render( $placement_id );
    }
}
