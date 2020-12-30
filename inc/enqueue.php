<?php
/**
 * Manage functionality to enqueue scripts and styles for our plugin,
 * as well as create some script localization options that will help
 * our plugin play well with JavaScript.
 *
 * @author     John Galyon
 * @since      1.0.1
 * @package    Coe_Am
 * @subpackage Coe_Am/enqueue
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

/**
 * Enqueue scripts and styles used by our plugin.
 *
 * @param string $hook_suffix output WP_Screen()->base.
 *
 * @since 1.0.1
 */
function coe_am_enqueue_scripts( string $hook_suffix ) {
	$_coe             = coe_am_constants();
	$bootstrap_css    = 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css';
	$bootstrap_js     = 'https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js';
	$fontawesome      = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css';
	$core             = get_taxonomies( array( '_builtin' => true ) );
	$public           = get_taxonomies(
		array(
			'_builtin' => false,
			'public'   => true,
		)
	);
	$private          = get_taxonomies(
		array(
			'_builtin' => false,
			'public'   => false,
		)
	);
	$registered_taxes = array_merge( $core, $public, $private );
	$options          = array(
		'confirm_delete' => array(
			'heading' => __( 'Confirm Deletion', $_coe['text'] ),
			'msg'     => __( 'This will delete the metadata and any terms assigned to it. Do you wish to proceed?', $_coe['text'] ),
		),
		'existing_taxes' => $registered_taxes,
	);
	$is_dev           = ( '127.0.0.1' === isset( $_SERVER['SERVER_NAME'] ) ) ?? '.min';

	if ( wp_doing_ajax() ) {
		return;
	}

	/**
	 * Enqueue the styles and scripts we need on our admin pages
	 *
	 * @param string $hook_suffix Returns the same data as calling WP_Screen()->base
	 *
	 * @since 1.0.1
	 * @uses  wp_enqueue_script
	 * @uses  wp_enqueue_style
	 */
	if ( 'asset_page_metadata' === $hook_suffix ) {
		// wp_enqueue_style( 'coe-am', plugins_url( "assets/coe-am{$is_dev}.css", __FILE__ ), '', $_coe['version'] );
		wp_enqueue_style( 'bootstrap', $_coe['url'] . 'assets/css/bootstrap.min.css', '', '4.5.3' );
		wp_enqueue_style( 'fontawesome', $fontawesome, '', '5.15.1' );
		wp_enqueue_script( 'bootstrap', $bootstrap_js, array( 'jquery' ), '4.5.3', true );
		wp_enqueue_script( 'coe-am', $_coe['url'] . 'assets/js/coe-am.js', array( 'jquery', 'bootstrap' ), $_coe['version'], true );
	}

	/**
	 * Now we create our options and make them available to JS
	 * to make it easier for the plugin front/back end to work together
	 *
	 * @param array $options array of options to call when creating dialogs.
	 *
	 * @since 1.0.0
	 * @uses  wp_localize_script
	 */
	wp_localize_script( 'coe-am', 'coe_am_scripts', $options );
}
add_action( 'admin_enqueue_scripts', 'coe_am_enqueue_scripts' );
