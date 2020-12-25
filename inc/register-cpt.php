<?php
/**
 * Activation functions for the plugin
 *
 * @since 1.0.0
 * @author: John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/activation
 * @license GPL-2.0+
 */

defined( 'ABSPATH' ) || die( 'That\'s illegal. ' );

function create_assets() {

	/**
	 * Put value of plugin constants into an array for easier access
	 */
	$_coe = coe_am_constants();

	$labels  = array(
		'name'                   => _x( 'Assets', 'Post Type General Name', 'coe-asset-management' ),
		'singular_name'          => _x( 'Asset', 'Post Type Singular Name', 'coe-asset-management' ),
		'menu_name'              => __( 'Assets', 'coe-asset-management' ),
		'name_admin_bar'         => __( 'Assets', 'coe-asset-management' ),
		'archives'               => __( 'Asset Archives', 'coe-asset-management' ),
		'attributes'             => __( 'Asset Attributes', 'coe-asset-management' ),
		'parent_asset_colon'     => __( 'Parent Asset:', 'coe-asset-management' ),
		'all_assets'             => __( 'All Assets', 'coe-asset-management' ),
		'add_new_asset'          => __( 'Add New Asset', 'coe-asset-management' ),
		'add_new'                => __( 'Add New', 'coe-asset-management' ),
		'new_asset'              => __( 'New Asset', 'coe-asset-management' ),
		'edit_asset'             => __( 'Edit Asset', 'coe-asset-management' ),
		'update_asset'           => __( 'Update Asset', 'coe-asset-management' ),
		'view_asset'             => __( 'View Asset', 'coe-asset-management' ),
		'view_assets'            => __( 'View Assets', 'coe-asset-management' ),
		'search_assets'          => __( 'Search Asset', 'coe-asset-management' ),
		'not_found'              => __( 'Not found', 'coe-asset-management' ),
		'not_found_in_trash'     => __( 'Not found in Trash', 'coe-asset-management' ),
		'featured_image'         => __( 'Featured Image', 'coe-asset-management' ),
		'set_featured_image'     => __( 'Set featured image', 'coe-asset-management' ),
		'remove_featured_image'  => __( 'Remove featured image', 'coe-asset-management' ),
		'use_featured_image'     => __( 'Use as featured image', 'coe-asset-management' ),
		'insert_into_asset'      => __( 'Insert into asset', 'coe-asset-management' ),
		'uploaded_to_this_asset' => __( 'Uploaded to this asset', 'coe-asset-management' ),
		'assets_list'            => __( 'Assets list', 'coe-asset-management' ),
		'assets_list_navigation' => __( 'Assets list navigation', 'coe-asset-management' ),
		'filter_assets_list'     => __( 'Filter assets list', 'coe-asset-management' ),
	);
	$rewrite = array(
		'slug'       => 'assets',
		'with_front' => true,
		'pages'      => true,
		'feeds'      => true,
	);
	$args    = array(
		'label'               => __( 'Asset', 'coe-asset-management' ),
		'labels'              => $labels,
		'supports'            => array( 'title', 'editor', 'thumbnail', 'revisions', 'post-formats' ),
		'taxonomies'          => array(),
		'hierarchical'        => false,
		'public'              => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_position'       => 5,
		'menu_icon'           => 'dashicons-clipboard',
		'show_in_admin_bar'   => true,
		'show_in_nav_menus'   => true,
		'can_export'          => true,
		'has_archive'         => true,
		'exclude_from_search' => false,
		'publicly_queryable'  => true,
		'rewrite'             => $rewrite,
		'capability_type'     => 'post',
		'show_in_rest'        => true,
	);
	register_post_type( 'asset', $args );

}
add_action( 'init', 'create_assets', 0 );
