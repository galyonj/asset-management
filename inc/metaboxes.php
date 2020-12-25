<?php
/**
 /**
 * Custom metaboxes for asset creation page
 *
 * @since 1.0.0
 * @author: John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/metaboxes
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}


function coe_am_remove_default_meta_box() {
	$_coe = coe_am_constants();
}

// coe_am_meta_select_box
function coe_am_create_select_meta_box() {
	$_coe = coe_am_constants();

}
add_action( 'add_meta_boxes', 'coe_am_create_select_meta_box' );
