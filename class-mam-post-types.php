<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class MAM_Post_Types {

    public function __construct() {
        add_action( 'init', [ $this, 'register_cpts' ] );
        add_action( 'init', [ $this, 'register_taxonomies' ] );
    }

    public function register_cpts() {
        // ── Ad CPT ──────────────────────────────────────────
        register_post_type( 'mam_ad', [
            'labels' => [
                'name'               => __( 'Ads', 'my-ads-manager' ),
                'singular_name'      => __( 'Ad', 'my-ads-manager' ),
                'add_new'            => __( 'Add New', 'my-ads-manager' ),
                'add_new_item'       => __( 'Add New Ad', 'my-ads-manager' ),
                'edit_item'          => __( 'Edit Ad', 'my-ads-manager' ),
                'new_item'           => __( 'New Ad', 'my-ads-manager' ),
                'view_item'          => __( 'View Ad', 'my-ads-manager' ),
                'search_items'       => __( 'Search Ads', 'my-ads-manager' ),
                'not_found'          => __( 'No ads found', 'my-ads-manager' ),
                'not_found_in_trash' => __( 'No ads in trash', 'my-ads-manager' ),
                'menu_name'          => __( 'My Ads Manager', 'my-ads-manager' ),
            ],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'mam-dashboard',
            'show_in_rest'        => true,
            'supports'            => [ 'title' ],
            'capability_type'     => 'post',
            'has_archive'         => false,
            'rewrite'             => false,
            'menu_icon'           => 'dashicons-megaphone',
        ] );

        // ── Ad Group CPT ────────────────────────────────────
        register_post_type( 'mam_group', [
            'labels' => [
                'name'          => __( 'Ad Groups', 'my-ads-manager' ),
                'singular_name' => __( 'Ad Group', 'my-ads-manager' ),
                'add_new_item'  => __( 'Add New Group', 'my-ads-manager' ),
                'edit_item'     => __( 'Edit Group', 'my-ads-manager' ),
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'mam-dashboard',
            'show_in_rest'    => true,
            'supports'        => [ 'title' ],
            'rewrite'         => false,
        ] );

        // ── Placement CPT ───────────────────────────────────
        register_post_type( 'mam_placement', [
            'labels' => [
                'name'          => __( 'Placements', 'my-ads-manager' ),
                'singular_name' => __( 'Placement', 'my-ads-manager' ),
                'add_new_item'  => __( 'Add New Placement', 'my-ads-manager' ),
                'edit_item'     => __( 'Edit Placement', 'my-ads-manager' ),
            ],
            'public'          => false,
            'show_ui'         => true,
            'show_in_menu'    => 'mam-dashboard',
            'show_in_rest'    => true,
            'supports'        => [ 'title' ],
            'rewrite'         => false,
        ] );
    }

    public function register_taxonomies() {
        register_taxonomy( 'mam_ad_type', 'mam_ad', [
            'label'        => __( 'Ad Type', 'my-ads-manager' ),
            'hierarchical' => true,
            'show_ui'      => false,
            'show_in_rest' => false,
            'rewrite'      => false,
        ] );
    }
}
