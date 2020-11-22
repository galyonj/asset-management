<?php
/**
 * Construct the metadata management page
 *
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

if ( ! current_user_can( 'manage_options' ) ) {
	return;
}

/**
 * Register our tabs for the Taxonomy screen.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param array  $tabs         Array of tabs to display. Optional.
 * @param string $current_page Current page being shown. Optional. Default empty string.
 * @return array Amended array of tabs to show.
 */
/**
 * Register our tabs for the Taxonomy screen.
 *
 * @since 1.3.0
 *
 * @internal
 *
 * @param array  $tabs         Array of tabs to display. Optional.
 * @param string $current_page Current page being shown. Optional. Default empty string.
 * @return array Amended array of tabs to show.
 */
function coe_am_taxonomy_tabs( $tabs = array(), $current_page = '' ) {

	if ( 'metadata' === $current_page ) {
		$taxonomies = coe_am_get_taxonomy_data();
		$classes    = array( 'nav-tab' );

		$tabs['page_title']  = get_admin_page_title();
		$tabs['tabs']        = array();
		$tabs['tabs']['add'] = array( // Start out with our basic "Add new" tab.
			'text'          => esc_html__( 'Add New Metadata Term', 'coe-am' ),
			'classes'       => $classes,
			'url'           => coe_am_admin_url( 'edit.php?post_type=asset&page=' . $current_page ),
			'aria-selected' => 'false',
		);

		$action = coe_am_get_current_action();
		if ( empty( $action ) ) {
			$tabs['tabs']['add']['classes'][]     = 'nav-tab-active';
			$tabs['tabs']['add']['aria-selected'] = 'true';
		}

		if ( ! empty( $taxonomies ) ) {

			if ( ! empty( $action ) ) {
				$classes[] = 'nav-tab-active';
			}
			$tabs['tabs']['edit'] = array(
				'text'          => esc_html__( 'Edit Metadata Terms', 'coe-am' ),
				'classes'       => $classes,
				'url'           => esc_url( add_query_arg( array( 'action' => 'edit' ), coe_am_admin_url( 'admin.php?page=coe_am_manage_' . $current_page ) ) ),
				'aria-selected' => ! empty( $action ) ? 'true' : 'false',
			);

			$tabs['tabs']['view'] = array(
				'text'          => esc_html__( 'View Metadata Terms', 'coe-am' ),
				'classes'       => array( 'nav-tab' ), // Prevent notices.
				'url'           => esc_url( coe_am_admin_url( 'admin.php?page=coe_am_listings#taxonomies' ) ),
				'aria-selected' => 'false',
			);

			$tabs['tabs']['export'] = array(
				'text'          => esc_html__( 'Import/Export Metadata Terms (Advanced)', 'coe-am' ),
				'classes'       => array( 'nav-tab' ), // Prevent notices.
				'url'           => esc_url( coe_am_admin_url( 'admin.php?page=coe_am_tools&action=taxonomies' ) ),
				'aria-selected' => 'false',
			);
		}
	}

	return $tabs;
}

add_filter( 'coe_am_get_tabs', 'coe_am_taxonomy_tabs', 10, 2 );

function coe_am_manage_metadata() {
	$tab       = ( ! empty( $_GET ) && ! empty( $_GET['action'] ) && 'edit' == $_GET['action'] ) ? 'edit' : 'new';
	$tab_class = 'cptui-' . $tab;
	$current   = null;
	?>

	<div class="wrap <?php echo esc_attr( $tab_class ); ?>">
	<?php
	/**
	 * Fires right inside the wrap div for the taxonomy editor screen.
	 *
	 * @since 1.3.0
	 */
	do_action( 'coe_am_inside_taxonomy_wrap' );

	/**
	 * Filters whether or not a taxonomy was deleted.
	 *
	 * @since 1.4.0
	 *
	 * @param bool $value Whether or not taxonomy deleted. Default false.
	 */
	$taxonomy_deleted = apply_filters( 'coe_am_taxonomy_deleted', false );

	// Create our tabs.
	coe_am_settings_tab_menu( 'metadata' );

	/**
	 * Fires below the output for the tab menu on the taxonomy add/edit screen.
	 *
	 * @since 1.3.0
	 */
	do_action( 'coe_am_below_taxonomy_tab_menu' );

	if ( 'edit' === $tab ) {

		$taxonomies = coe_am_get_taxonomy_data();

		$selected_taxonomy = coe_am_get_current_taxonomy( $taxonomy_deleted );

		if ( $selected_taxonomy && array_key_exists( $selected_taxonomy, $taxonomies ) ) {
			$current = $taxonomies[ $selected_taxonomy ];
		}
	}

	$ui = new COE_AM_Admin_UI();

	?>

		<form class="taxonomiesui" method="post" action="<?php echo esc_url( coe_am_get_post_form_action( $ui ) ); ?>">
			<div class="postbox-container">
				<div id="poststuff">
					<div class="cptui-section postbox">
						<div class="postbox-header">
							<h2 class="hndle ui-sortable-handle">
								<span><?php esc_html_e( 'Basic settings', 'coe-am' ); ?></span>
							</h2>
							<div class="handle-actions hide-if-no-js">
								<button type="button" class="handlediv" aria-expanded="true">
									<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Basic settings', 'coe-am' ); ?></span>
									<span class="toggle-indicator" aria-hidden="true"></span>
								</button>
							</div>
						</div> <!-- postbox-header -->
						<div class="inside">
							<div class="main" style="padding-top: 15px;">
								<div class="form-group row">
									<label for="single" class="col-sm-3 col-form-label">
										<strong><?php echo esc_html_e( 'Term Name', 'coe-am' ); ?></strong> <span style="color: #981e32;">*</span>
									</label>
									<div class="col-sm-9">
										<input type="text" class="form-control form-control-sm" name="single" placeholder="<?php echo esc_html_e( 'Term', 'coe-am' ); ?>" id="single" aria-describedby="singular-label-help" required>
										<p id="ssingle-help" class="form-text" style="margin-bottom: 0;">
											<?php echo esc_html_e( 'Please use only alphanumeric characters and spaces.', 'coe-am' ); ?>
										</p>
									</div>
								</div>
								<div class="form-group row">
									<label for="plural" class="col-sm-3 col-form-label">
										<strong><?php echo esc_html_e( 'Plural Term Name', 'coe-am' ); ?></strong> <span style="color: #981e32;">*</span>
									</label>
									<div class="col-sm-9">
										<input type="text" class="form-control form-control-sm" name="plural" placeholder="<?php echo esc_html_e( 'Terms', 'coe-am' ); ?>" id="plural" aria-describedby="plural_term-name-help" required>
										<p id="plural-help" class="form-text" style="margin-bottom: 0;">
											<?php echo esc_html_e( 'Plural version of the term name. Please use only alphanumeric characters and spaces.', 'coe-am' ); ?>
										</p>
									</div>
								</div>
								<div class="form-group row">
									<label for="term_select_multiple" class="col-sm-3 col-form-label">
										<strong><?php echo esc_html_e( 'Select Multiple Terms', 'coe-am' ); ?></strong> <span style="color: #981e32;">*</span>
									</label>
									<div class="col-sm-9">
										<select name="term_select_multiple" id="term_select_multiple" class="custom-select" aria-describedby="term-select-multiple-help" required>
											<option selected>Select an option</option>
											<option value="true">Yes</option>
											<option value="false">No</option>
										</select>
										<p id="term-select-multiple-help" class="form-text" style="margin-bottom: 0;">
											<?php echo esc_html_e( 'Select whether you might need to add multple values from this term to one asset. (e.g. Can an asset belong to multiple regions?)', 'coe-am' ); ?>
										</p>
									</div>
								</div>
								<div class="form-group row">
									<label for="hierarchical" class="col-sm-3 col-form-label">
										<strong><?php echo esc_html_e( 'Hierarchical Terms', 'coe-am' ); ?></strong>
									</label>
									<div class="col-sm-9">
										<select name="hierarchical" id="hierarchical" class="custom-select" aria-describedby="hierarchical-help">
											<option value="true">Yes</option>
											<option value="false" selected>No</option>
										</select>
										<p id="hierarchical-help" class="form-text" style="margin-bottom: 0;">
											<?php echo esc_html_e( 'Select whether values for this term should have parent/child relationships. (e.g. Can you have a region within a region?)', 'coe-am' ); ?>
										</p>
									</div>
								</div>
								<div class="form-group row">
									<label for="show_admin_column" class="col-sm-3 col-form-label">
										<strong><?php echo esc_html_e( 'Display Admin Column', 'coe-am' ); ?></strong>
									</label>
									<div class="col-sm-9">
										<select name="show_admin_column" id="show_admin_column" class="custom-select" aria-describedby="show-admin-column-help" required>
											<option value="true" selected>Yes</option>
											<option value="false">No</option>
										</select>
										<p id="show-admin-column-help" class="form-text" style="margin-bottom: 0;">
											<?php echo esc_html_e( 'Select whether you wish for values associated with this taxonomy term to appear as a coumn when viewing all assets.', 'coe-am' ); ?>
										</p>
									</div>
								</div>
								<div class="form-group row">
									<label for="show_in_nav_menus" class="col-sm-3 col-form-label">
										<strong><?php echo esc_html_e( 'Show In Navigation', 'coe-am' ); ?></strong>
									</label>
									<div class="col-sm-9">
										<select name="show_in_nav_menus" id="show_in_nav_menus" class="custom-select" aria-describedby="show-in-nav-help">
											<option value="true">Yes</option>
											<option value="false" selected>No</option>
										</select>
										<p id="show-in-nav-help" class="form-text" style="margin-bottom: 0;">
											<?php echo esc_html_e( 'Select whether you wish to make the term available for selection in navigation menus.', 'coe-am' ); ?>
										</p>
									</div>
								</div>
								<hr>
								<p>That's all we need. All of the label text and settings will be created automatically using the selections you made above.</p>
								<!--
									your form fields if any,
									you could use add_settings_section hook
									@link https://codex.wordpress.org/Function_Reference/add_settings_section
								-->
								<?php wp_nonce_field( 'coe_am_addedit_taxonomy_nonce_action', 'coe_am_addedit_taxonomy_nonce_field' ); ?>
								<input type="submit" class="button-primary coe-am-metadata-term-submit" name="coe_am_submit" value="<?php echo esc_attr( apply_filters( 'coe_am_taxonomy_submit_add', esc_attr__( 'Add Metadata Term', 'coe-am' ) ) ); ?>" />
							</div> <!-- main -->
						</div> <!-- inside -->
					</div>
				</div>
			</div>
		</form>
	</div> <!-- wrap -->
	<?php
}

/**
 * Add to or update our CPTUI option with new data.
 *
 * @since 1.0.0
 *
 * @internal
 *
 * @param array $data Array of taxonomy data to update. Optional.
 * @return bool|string False on failure, string on success.
 */
function coe_am_update_taxonomy( $data ) {
	$plural = ucwords( $data['plural'] );
	$single = ucwords( $data['single'] );
	$labels = array(
		'name'                       => strtolower( str_replace( ' ', '-', $plural ) ),
		'singular_name'              => $single,
		'menu_name'                  => $plural,
		'all_items'                  => sprintf( __( 'All %s', 'coe-asset-management' ), $plural ),
		'edit_item'                  => sprintf( __( 'Edit %s', 'coe-asset-management' ), $single ),
		'view_item'                  => sprintf( __( 'View %s', 'coe-asset-management' ), $single ),
		'update_item'                => sprintf( __( 'Update %s', 'coe-asset-management' ), $single ),
		'add_new_item'               => sprintf( __( 'Add New %s', 'coe-asset-management' ), $single ),
		'new_item_name'              => sprintf( __( 'New %s Name', 'coe-asset-management' ), $single ),
		'parent_item'                => sprintf( __( 'Parent %s', 'coe-asset-management' ), $single ),
		'parent_item_colon'          => sprintf( __( 'Parent %s:', 'coe-asset-management' ), $single ),
		'search_items'               => sprintf( __( 'Search %s', 'coe-asset-management' ), $plural ),
		'popular_items'              => sprintf( __( 'Popular %s', 'coe-asset-management' ), $plural ),
		'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'coe-asset-management' ), $plural ),
		'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'coe-asset-management' ), $plural ),
		'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'coe-asset-management' ), $plural ),
		'not_found'                  => sprintf( __( 'No %s found', 'coe-asset-management' ), $plural ),
	);

	$args = array(
		'label'                 => $plural,
		'labels'                => $labels,
		'hierarchical'          => ( isset( $data['hierarchical'] ) ) ? $data['hierarchical'] : false,
		'public'                => ( isset( $data['public'] ) ) ? $data['public'] : true,
		'show_ui'               => ( isset( $data['show_ui'] ) ) ? $data['show_ui'] : true,
		'show_in_nav_menus'     => ( isset( $data['show_in_nav_menus'] ) ) ? $data['show_in_nav_menus'] : true,
		'show_tagcloud'         => ( isset( $data['show_tagcloud'] ) ) ? $data['show_tagcloud'] : true,
		'meta_box_cb'           => ( isset( $data['meta_box_cb'] ) ) ? $data['meta_box_cb'] : null,
		'show_admin_column'     => ( isset( $data['show_admin_column'] ) ) ? $data['show_admin_column'] : true,
		'show_in_quick_edit'    => ( isset( $data['show_in_quick_edit'] ) ) ? $data['show_in_quick_edit'] : true,
		'update_count_callback' => ( isset( $data['update_count_callback'] ) ) ? $data['update_count_callback'] : '',
		'show_in_rest'          => ( isset( $data['show_in_rest'] ) ) ? $data['show_in_rest'] : true,
		'rest_base'             => strtolower( $single ),
		'rest_controller_class' => ( isset( $data['rest_controller_class'] ) ) ? $data['rest_controller_class'] : 'WP_REST_Terms_Controller',
		'query_var'             => strtolower( $single ),
		'rewrite'               => ( isset( $data['rewrite'] ) ) ? $data['rewrite'] : true,
		'sort'                  => ( isset( $data['sort'] ) ) ? $data['sort'] : '',
	);

	$args = apply_filters( $labels['name'] . '_args', $args );

	register_taxonomy( $labels['name'], 'asset', $args );

	// Used to help flush rewrite rules on init.
	set_transient( 'coe_am_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) && 'new' === $data['coe_tax_status'] ) {
		return 'add_success';
	}

	return 'update_success';
}

/**
 * Return an array of names that users should not or can not use for taxonomy names.
 *
 * @since 1.3.0
 *
 * @return array $value Array of names that are recommended against.
 */
function coe_am_reserved_taxonomies() {

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

/**
 * Checks if we are trying to register an already registered taxonomy slug.
 *
 * @since 1.3.0
 *
 * @param bool   $slug_exists   Whether or not the post type slug exists. Optional. Default false.
 * @param string $taxonomy_slug The post type slug being saved. Optional. Default empty string.
 * @param array  $taxonomies    Array of CPTUI-registered post types. Optional.
 *
 * @return bool
 */
function coe_am_check_existing_taxonomy_slugs( $slug_exists = false, $taxonomy_slug = '', $taxonomies = array() ) {

	// If true, then we'll already have a conflict, let's not re-process.
	if ( true === $slug_exists ) {
		return $slug_exists;
	}

	// Check if CPTUI has already registered this slug.
	if ( array_key_exists( strtolower( $taxonomy_slug ), $taxonomies ) ) {
		return true;
	}

	// Check if we're registering a reserved post type slug.
	if ( in_array( $taxonomy_slug, coe_am_reserved_taxonomies() ) ) {
		return true;
	}

	// Check if other plugins have registered this same slug.
	$public                = get_taxonomies(
		array(
			'_builtin' => false,
			'public'   => true,
		)
	);
	$private               = get_taxonomies(
		array(
			'_builtin' => false,
			'public'   => false,
		)
	);
	$registered_taxonomies = array_merge( $public, $private );
	if ( in_array( $taxonomy_slug, $registered_taxonomies ) ) {
		return true;
	}

	// If we're this far, it's false.
	return $slug_exists;
}
add_filter( 'coe_am_taxonomy_slug_exists', 'coe_am_check_existing_taxonomy_slugs', 10, 3 );

/**
 * Handle the save and deletion of taxonomy data.
 *
 * @since 1.4.0
 */
function coe_am_process_taxonomy() {

	if ( wp_doing_ajax() ) {
		return;
	}

	if ( ! is_admin() ) {
		return;
	}

	if ( ! empty( $_GET ) && isset( $_GET['page'] ) && 'coe_am_manage_taxonomies' !== $_GET['page'] ) {
		return;
	}

	if ( ! empty( $_POST ) ) {
		$result = '';
		if ( isset( $_POST['coe_submit'] ) ) {
			check_admin_referer( 'coe_am_addedit_taxonomy_nonce_action', 'coe_am_addedit_taxonomy_nonce_field' );
			$result = coe_am_update_taxonomy( $_POST );
		} elseif ( isset( $_POST['coe_delete'] ) ) {
			check_admin_referer( 'coe_am_addedit_taxonomy_nonce_action', 'coe_am_addedit_taxonomy_nonce_field' );
			$result = coe_am_delete_taxonomy( $_POST );
			add_filter( 'coe_am_taxonomy_deleted', '__return_true' );
		}

		// @TODO Utilize anonymous function to admin_notice `$result` if it happens to error.
		if ( $result && is_callable( "coe_am_{$result}_admin_notice" ) ) {
			add_action( 'admin_notices', "coe_am_{$result}_admin_notice" );
		}

		if ( isset( $_POST['coe_delete'] ) && empty( coe_am_get_taxonomy_slugs() ) ) {
			wp_safe_redirect(
				add_query_arg(
					array( 'page' => 'coe_am_manage_taxonomies' ),
					coe_am_admin_url( 'admin.php?page=coe_am_manage_taxonomies' )
				)
			);
		}
	}
}
add_action( 'init', 'coe_am_process_taxonomy', 8 );

/**
 * Handle the conversion of taxonomy terms.
 *
 * This function came to be because we needed to convert AFTER registration.
 *
 * @since 1.4.3
 */
function coe_am_do_convert_taxonomy_terms() {

	/**
	 * Whether or not to convert taxonomy terms.
	 *
	 * @since 1.4.3
	 *
	 * @param bool $value Whether or not to convert.
	 */
	if ( apply_filters( 'coe_am_convert_taxonomy_terms', false ) ) {
		check_admin_referer( 'coe_am_addedit_taxonomy_nonce_action', 'coe_am_addedit_taxonomy_nonce_field' );

		coe_am_convert_taxonomy_terms( sanitize_text_field( $_POST['tax_original'] ), sanitize_text_field( $_POST['coe_custom_tax']['name'] ) );
	}
}
add_action( 'init', 'coe_am_do_convert_taxonomy_terms' );

/**
 * Handles slug_exist checks for cases of editing an existing taxonomy.
 *
 * @since 1.5.3
 *
 * @param bool   $slug_exists   Current status for exist checks.
 * @param string $taxonomy_slug Taxonomy slug being processed.
 * @param array  $taxonomies    CPTUI taxonomies.
 * @return bool
 */
function coe_am_updated_taxonomy_slug_exists( $slug_exists, $taxonomy_slug = '', $taxonomies = array() ) {
	if (
		( ! empty( $_POST['coe_tax_status'] ) && 'edit' === $_POST['coe_tax_status'] ) &&
		! in_array( $taxonomy_slug, coe_am_reserved_taxonomies(), true ) &&
		( ! empty( $_POST['tax_original'] ) && $taxonomy_slug === $_POST['tax_original'] )
	) {
		$slug_exists = false;
	}
	return $slug_exists;
}
add_filter( 'coe_am_taxonomy_slug_exists', 'coe_am_updated_taxonomy_slug_exists', 11, 3 );
