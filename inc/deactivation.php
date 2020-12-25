<?php
/**
 * Fun WordPress fact: A custom post type isn't only registered once.
 * It's registered every time the plugin loads. Custom Post Types aren't persistent.
 *
 * However, there is still some housekeeping we need to do during plugin
 * deactivation to be good database stewards:
 * -- Delete the terms that have been set for each custom taxonomy
 * -- Unregister the taxonomy that was created for our CPT
 * -- flush rewrite rules
 *
 * @since 1.0.1
 * @package Coe_Am
 * @subpackage Coe_Am/deactivation
 * @author John Galyon
 *
 * TODO: Should we also remove the option that holds the custom taxonomy rules?
 */

function coe_am_deactivation() {
	$taxes = get_object_taxonomies( 'asset' );

	/**
	 * Loop through all the taxes registered for our 'asset' object
	 * and do the following:
	 * -- Get all the terms assigned to that taxonomy
	 * -- Delete each term
	 * -- Delete the taxonomy itself
	 *
	 * @since 1.0.0
	 */
	foreach ( $taxes as $tax ) {
		$terms = get_terms(
			array(
				'hide_empty' => false,
				'taxonomy'   => $tax,
			)
		);

		foreach ( $terms as $term ) {
			wp_delete_term( $term->term_id, $tax );
		}

		unregister_taxonomy( $tax->name );
	}
	flush_rewrite_rules( false );
}
register_deactivation_hook( __FILE__, 'coe_am_deactivation' );
