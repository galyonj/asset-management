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
	$_coe = coe_am_populate_constants();

	$labels  = array(
		'name'                   => _x( 'Assets', 'Post Type General Name', $_coe['text'] ),
		'singular_name'          => _x( 'Asset', 'Post Type Singular Name', $_coe['text'] ),
		'menu_name'              => __( 'Assets', $_coe['text'] ),
		'name_admin_bar'         => __( 'Assets', $_coe['text'] ),
		'archives'               => __( 'Asset Archives', $_coe['text'] ),
		'attributes'             => __( 'Asset Attributes', $_coe['text'] ),
		'parent_asset_colon'     => __( 'Parent Asset:', $_coe['text'] ),
		'all_assets'             => __( 'All Assets', $_coe['text'] ),
		'add_new_asset'          => __( 'Add New Asset', $_coe['text'] ),
		'add_new'                => __( 'Add New', $_coe['text'] ),
		'new_asset'              => __( 'New Asset', $_coe['text'] ),
		'edit_asset'             => __( 'Edit Asset', $_coe['text'] ),
		'update_asset'           => __( 'Update Asset', $_coe['text'] ),
		'view_asset'             => __( 'View Asset', $_coe['text'] ),
		'view_assets'            => __( 'View Assets', $_coe['text'] ),
		'search_assets'          => __( 'Search Asset', $_coe['text'] ),
		'not_found'              => __( 'Not found', $_coe['text'] ),
		'not_found_in_trash'     => __( 'Not found in Trash', $_coe['text'] ),
		'featured_image'         => __( 'Featured Image', $_coe['text'] ),
		'set_featured_image'     => __( 'Set featured image', $_coe['text'] ),
		'remove_featured_image'  => __( 'Remove featured image', $_coe['text'] ),
		'use_featured_image'     => __( 'Use as featured image', $_coe['text'] ),
		'insert_into_asset'      => __( 'Insert into asset', $_coe['text'] ),
		'uploaded_to_this_asset' => __( 'Uploaded to this asset', $_coe['text'] ),
		'assets_list'            => __( 'Assets list', $_coe['text'] ),
		'assets_list_navigation' => __( 'Assets list navigation', $_coe['text'] ),
		'filter_assets_list'     => __( 'Filter assets list', $_coe['text'] ),
	);
	$rewrite = array(
		'slug'       => 'assets',
		'with_front' => true,
		'pages'      => true,
		'feeds'      => true,
	);
	$args    = array(
		'label'               => __( 'Asset', $_coe['text'] ),
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
