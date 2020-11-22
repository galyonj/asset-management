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

function coe_am_activate() {
	flush_rewrite_rules();
}
add_action( 'init', 'coe_am_activate' );
