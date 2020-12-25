<?php
/**
 * Metadata CRUD functions
 *
 * @since 1.0.3
 * @author John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/metadata
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

/**
 * Collect the selected taxonomy from the edit tab, and return the
 * selected taxonomy as an array, or send it to coe_am_delete_tax to
 * safely delete the taxonomy.
 *
 * @param bool $tax_deleted
 *
 * @since 1.0.3
 * @return array $tax Array of selected taxonomy settings
 */
function coe_am_get_selected_tax( $data = array(), $tax_deleted = false ) {
	$nonce = ( ! empty( $_POST['coe_am_select_metadata_nonce_field'] ) ) ?? wp_verify_nonce( 'coe_am_select_metadata_nonce_field', 'coe_am_select_metadata_nonce_action' );
	$tax   = false;
	$taxes = coe_am_get_saved_taxes();

	if ( ! empty( $_POST ) ) {
		if ( $nonce ) {
			if ( isset( $data['select_tax'] ) ) {
				$tax = sanitize_text_field( $data['select_tax'] );
			}
		}
	}

	return $tax;
}

/**
 * Check form nonces and pass the $_POST data onto the
 * appropriate function for user-initiated metadata/taxonomy
 * CRUD actions.
 *
 * @since 1.0.0
 * @return void
 */
function coe_am_process_tax() {

	if ( wp_doing_ajax() || ! is_admin() ) {
		return;
	}

	if ( ! empty( $_GET ) && ( isset( $_GET['page'] ) && 'metadata' !== $_GET['page'] ) ) {
		return;
	}

	if ( ! empty( $_POST ) ) {
		$nonce  = ( ! empty( $_POST['coe_am_metadata_nonce_field'] ) ) ?? wp_verify_nonce( $_POST['coe_am_metadata_nonce_field'], 'coe_am_metadata_nonce_action' );
		$result = '';

		if ( $nonce ) {
			if ( isset( $_POST['coe_am_submit'] ) ) {
				$result = coe_am_update_tax( $_POST );
			} elseif ( isset( $_POST['coe_am_delete'] ) ) {
				$result = coe_am_delete_tax( $_POST );
				add_filter( 'coe_am_tax_deleted', '__return_true' );
			}
		}

		// @TODO Utilize anonymous function to admin_notice `$result` if it happens to error.
		if ( $result && is_callable( "coe_am_{$result}_admin_notice" ) ) {
			add_action( 'admin_notices', "coe_am_{$result}_admin_notice" );
		}

		if ( isset( $_POST['coe_am_delete'] ) && empty( coe_am_get_taxonomy_slugs() ) ) {
			wp_safe_redirect(
				add_query_arg(
					array( 'page' => 'metadata' ),
					admin_url( 'edit.php?post_type=asset' ),
				)
			);
		}
	}
}
add_action( 'init', 'coe_am_process_tax', 8 );

/**
 * Delete the selected taxonomy
 *
 * @param  array $data POST object array
 * @return void
 */
function coe_am_delete_tax( $data = array() ) {

	if ( is_string( $data ) && taxonomy_exists( $data ) ) {
		$data = array(
			'tax_name'         => $data,
			'referring_action' => 'delete',
		);
	}

	// Check if they selected one to delete.
	if ( empty( $data['tax_name'] ) ) {
		return coe_am_admin_notices( 'error', '', false, esc_html__( 'Please select a metadata to delete', $_coe['text'] ) );
	}

	/**
	 * Fires before a taxonomy is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data we are deleting.
	 */
	do_action( 'coe_am_before_delete_taxonomy', $data );

	$taxes = get_option( 'coe_am_metadata' );

	if ( array_key_exists( strtolower( $data['tax_name'] ), $taxes ) ) {

		unset( $taxes[ $data['tax_name'] ] );

		/**
		 * Filters whether or not 3rd party options were saved successfully within taxonomy deletion.
		 *
		 * @since 1.3.0
		 *
		 * @param bool  $value      Whether or not someone else saved successfully. Default false.
		 * @param array $taxonomies Array of our updated taxonomies data.
		 * @param array $data       Array of submitted taxonomy to update.
		 */
		if ( false === ( $success = apply_filters( 'coe_am_delete_tax', false, $taxes, $data ) ) ) {
			$success = update_option( 'coe_am_metadata', $taxes );
		}
	}
	delete_option( "default_term_{$data['tax_name']}" );

	/**
	 * Fires after a taxonomy is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data that was deleted.
	 */
	do_action( 'coe_am_after_delete_tax', $data );

	// Used to help flush rewrite rules on init.
	set_transient( 'coe_am_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) ) {
		return 'delete_success';
	}
	return 'delete_fail';
}

function coe_am_update_tax( $data = array() ) {
	$_coe = coe_am_constants();

	/**
	 * Fires before our taxonomy is added to our saved option.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data array of taxonomy data being saved.
	 */
	do_action( 'coe_am_before_update_tax', $data );

	if ( empty( $data['tax_name'] ) ) {
		return coe_am_admin_notices( 'error', '', false, esc_html__( 'Please provide a name for your metadata', $_coe['text'] ) );
	}

	// TODO make this part not be stupid
	if ( ! empty( $data['select_metadata'] ) && $data['select_metadata'] !== $data['tax_name'] ) {
		if ( ! empty( $data['update_taxonomy'] ) ) {
			add_filter( 'coe_am_convert_terms', '__return_true' );
		}
	}

	/**
	 * It's a *really* bad idea to save form data without first sanitizing it,
	 * so we want to loop through all of the fields being sent in our $data
	 * array and, for every $value that is a string, we sanitize that field.
	 *
	 * Then we return those values back into the $data array for safe use.
	 *
	 * @since 1.0.3
	 * @param array $data array of submitted values
	 */
	foreach ( $data as $key => $value ) {
		if ( is_string( $value ) ) {
			$data[ $key ] = sanitize_text_field( $value );
		} else {
			array_map( 'sanitize_text_field', $data[ $key ] );
		}
	}

	/**
	 * Make sure we've got our metadata handy for later
	 */
	$taxes = coe_am_get_saved_taxes();

	/**
	 * Check if we already have a post type of that name.
	 *
	 * @since 1.0.3
	 *
	 * @param bool   $value      Assume we have no conflict by default.
	 * @param string $value      Post type slug being saved.
	 * @param array  $post_types Array of existing post types from CPTUI.
	 */
	$slug_exists = apply_filters( 'coe_am_tax_slug_exists', false, $data['tax_name'], $taxes );
	if ( true === $slug_exists ) {
		add_filter( 'coe_am_custom_error_message', 'coe_am_slug_matches_taxonomy' );
		return 'error';
	}

	/**
	 * Now let's collect our form data and do what formatting
	 * we can early so that there's a lower chance of data
	 * getting buggered up during the registration process
	 *
	 *
	 */
	$pattern         = array(
		'/[\'"]/',
		'/[ ]/',
	);
	$replace         = array(
		'',
		'_',
	);
	$name            = trim( strtolower( preg_replace( $pattern, $replace, sanitize_text_field( $data['tax_name'] ) ) ) );
	$assign_multiple = filter_var( $data['assign_multiple'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	$hierarchical    = filter_var( $data['assign_multiple'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	$meta_box_cb     = '';

	/**
	 * For the most part, the default WordPress meta boxes
	 * are pretty garbage. So we're going to replace them.
	 * The only one we keep will be if both $hierarchical
	 * and $assign_multiple are true, in which case we
	 * will assign `post_categories_meta_box` to $meta_box_cb.
	 *
	 * @since 1.0.0
	 * @internal
	 */
	if ( false === $hierarchical ) {
		if ( false === $assign_multiple ) {
			$meta_box_cb = 'coe_am_select_meta_box';
		} else {
			$meta_box_cb = 'coe_am_check_meta_box';
		}
	} else {
		if ( false === $assign_multiple ) {
			$meta_box_cb = 'coe_am_select_meta_box';
		} else {
			$meta_box_cb = 'post_categories_meta_box';
		}
	}

	$taxes[ $name ] = array(
		// Basic stuff
		'name'                 => $name,
		'label'                => trim( ucwords( preg_replace( $pattern, $replace, sanitize_text_field( $data['label_plural'] ) ) ) ),
		'label_singular'       => trim( ucwords( preg_replace( $pattern, $replace, sanitize_text_field( $data['label_singular'] ) ) ) ),
		'description'          => esc_textarea( $data['description'] ),
		'assign_multiple'      => $assign_multiple,
		'hierarchical'         => $hierarchical,
		'meta_box_cb'          => $meta_box_cb,
		'object_types'         => array( 'asset' ),
		// Visibility
		'public'               => filter_var( $data['public'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'publicly_queryable'   => filter_var( $data['publicly_queryable'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'show_in_menu'         => filter_var( $data['show_in_menu'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'show_in_nav_menus'    => filter_var( $data['show_in_nav_menus'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'show_in_rest'         => filter_var( $data['show_in_rest'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'show_in_quick_edit'   => filter_var( $data['show_in_quick_edit'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'show_tagcloud'        => filter_var( $data['show_tagcloud'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'show_ui'              => filter_var( $data['show_ui'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		// REST options
		'rest_base'            => trim( strtolower( preg_replace( $pattern, $replace, sanitize_text_field( $data['rest_base'] ) ) ) ),
		// Rewrite
		'rewrite'              => filter_var( $data['rewrite'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'rewrite_slug'         => trim( strtolower( preg_replace( $pattern, $replace, sanitize_text_field( $data['rewrite_slug'] ) ) ) ),
		'rewrite_hierarchical' => filter_var( $data['rewrite_hierarchical'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
		'with_front'           => filter_var( $data['with_front'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE ),
	);

	/**
	 * Filter the final data to be saved right before saving the tax data
	 *
	 * @since 1.0.0
	 * @param array $taxes Array of taxonomy data to be saved
	 * @param string $name  Taxonomy slug to be saved
	 */
	$taxes = apply_filters( 'coe_am_pre_save_taxonomy', $taxes, $name );

	update_option( 'coe_am_metadata', $taxes );

	/**
	 * Fires after a taxonomy has been added to our saved options
	 *
	 * @since 1.0.0
	 * @param array $data array of taxonomy data that was updated.
	 */
	do_action( 'coe_am_after_update_taxonomy', $data );

	// This will help us make sure the rewrite rules are flushed on init during the taxonomy registration process.
	set_transient( 'coe_am_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) && 'add' === $data['referring_action'] ) {
		return 'add_success';
	}

	return 'update_success';
}


/**
 * Given an $old_tax name and a $new_tax, we will
 * first move all the terms assigned to $old_tax
 * into $new_tax, then we will delete $old_tax.
 *
 * @param  string $old_tax Old taxonomy term.
 * @param  string $new_tax New taxonomy term.
 *
 * @since 1.0.3
 * @uses coe_am_delete_taxonomy()
 */
function coe_am_convert_terms( $old_tax, $new_tax ) {
	$args = array(
		'taxonomy'   => $old_tax,
		'hide_empty' => false,
		'fields'     => 'ids',
	);

	$terms = get_terms( $args );

	foreach ( $terms as $term ) {
		if ( false === get_term_by( 'name', $term->name, $new_tax ) ) {
			$term_args = array(
				'slug'   => $term->slug,
				'parent' => $term->parent,
			);
			wp_insert_term( $term->name, $new_tax, $term_args );
		}
	}

	/**
	 * We can only delete $old_tax AFTER
	 * the terms have been moved to $new_tax
	 */
	coe_am_delete_taxonomy( $old_tax );
}

/**
 * coe_am_process_taxonomy looks for a trigger to know when a
 * taxonomy is being updated and changed. If so, things have to
 * happen in a really specific order, otherwise the whole process
 * will fail.
 *
 * 1. The new taxonomy has to be registered
 * 2. Terms assigned to the old taxonomy have to be moved to the new taxonomy
 * 3. The old taxonomy can finally be deleted
 *
 * @since 1.0.3
 * @return void
 */
function coe_am_do_convert_terms() {
	if ( apply_filters( 'coe_am_convert_terms', false ) ) {
		check_admin_referer( 'coe_am_metadata_nonce_action', 'coe_am_metadata_nonce_field' );

		coe_am_convert_terms( sanitize_text_field( $_POST['select_metadata'] ), sanitize_text_field( $_POST['plural_name'] ) );
	}
}
add_action( 'init', 'coe_am_do_convert_terms' );

/**
 * Return an array of taxonomy terms that are reserved by WordPress.
 * Users should not register these terms for their own uses, as this
 * will likely break WordPress core functionality.
 *
 * @since 1.0.3
 *
 * @return array $reserved array of taxonomy terms that should not be user-registered
 */
function coe_am_reserved_taxes() {

	$reserved = array(
		'action',
		'attachment',
		'attachment_id',
		'author',
		'author_name',
		'calendar',
		'cat',
		'category',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comments_per_page',
		'comments_popup',
		'customize_messenger_channel',
		'customized',
		'cpage',
		'day',
		'debug',
		'error',
		'exact',
		'feed',
		'fields',
		'hour',
		'include',
		'link_category',
		'm',
		'minute',
		'monthnum',
		'more',
		'name',
		'nav_menu',
		'nonce',
		'nopaging',
		'offset',
		'order',
		'orderby',
		'p',
		'page',
		'page_id',
		'paged',
		'pagename',
		'pb',
		'perm',
		'post',
		'post__in',
		'post__not_in',
		'post_format',
		'post_mime_type',
		'post_status',
		'post_tag',
		'post_type',
		'posts',
		'posts_per_archive_page',
		'posts_per_page',
		'preview',
		'robots',
		's',
		'search',
		'second',
		'sentence',
		'showposts',
		'static',
		'subpost',
		'subpost_id',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'taxonomy',
		'tb',
		'term',
		'theme',
		'type',
		'w',
		'withcomments',
		'withoutcomments',
		'year',
		'output',
	);

	/**
	 * Filters the list of reserved post types to check against.
	 * 3rd party plugin authors could use this to prevent duplicate post types.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Array of post type slugs to forbid.
	 */
	$custom_reserved = apply_filters( 'coe_am_reserved_taxonomies', array() );

	if ( is_string( $custom_reserved ) && ! empty( $custom_reserved ) ) {
		$reserved[] = $custom_reserved;
	} elseif ( is_array( $custom_reserved ) && ! empty( $custom_reserved ) ) {
		foreach ( $custom_reserved as $slug ) {
			$reserved[] = $slug;
		}
	}

	return $reserved;
}

function coe_am_check_existing_slugs( $slug_exists = false, $tax_slug = '', $taxes = array() ) {
	if ( true === $slug_exists ) {
		return $slug_exists;
	}
}
