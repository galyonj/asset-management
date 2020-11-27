<?php
/**
 * Forms and stuff for metadata/taxonomy CRUD
 *
 * @since 1.0.0
 * @author: John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/metadata
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

/**
 * Build the basic metadata page html structure
 * and build and populate the tabs and form
 *
 * @since 1.0.0
 */
function coe_am_metadata_html() {
	$_coe       = coe_am_populate_constants();
	$action     = isset( $_GET['action'] ) ? $_GET['action'] : '';
	$classes    = array( 'nav-tab' );
	$current    = null;
	$format     = '<a href="%s" class="%s" aria-selected="%s">%s</a>';
	$page_path  = admin_url( 'edit.php?post_type=asset&page=metadata' );
	$page_title = get_admin_page_title();
	$taxonomies = get_object_taxonomies( 'asset', 'objects' );
	$tabs       = array();

	$tabs['add'] = array(
		'text'     => esc_html__( 'Add New Metadata', $_coe['text'] ),
		'classes'  => $classes,
		'url'      => esc_url( $page_path ),
		'selected' => 'false',
	);

	if ( empty( $action ) ) {
		$tabs['add']['classes'][] = 'nav-tab-active';
		$tabs['add']['selected']  = 'true';
	}

	if ( ! empty( $taxonomies ) ) {

		if ( get_query_var( 'action' ) === $action ) {
			$tab['classes'][] = 'nav-tab-active';
			$tab['selected']  = 'true';
		}

		$tabs['edit']   = array(
			'action'   => 'edit',
			'text'     => esc_html__( 'Edit Metadata', $_coe['text'] ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'edit' ) ), $page_path ),
			'selected' => 'false',
		);
		$tabs['view']   = array(
			'action'   => 'view',
			'text'     => esc_html__( 'View Metadata Terms', $_coe['text'] ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'view' ) ), $page_path ),
			'selected' => 'false',
		);
		$tabs['import'] = array(
			'action'   => 'import',
			'text'     => esc_html__( 'Import/Export Metadata', $_coe['text'] ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'import' ) ), $page_path ),
			'selected' => 'false',
		);
	}

	// if ( ! empty( $taxonomies ) ) {
	// 	$tabs[]
	// }
	?>
<div class="wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>
	<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary Menu">
		<!-- <a href="<?php echo $page_path; ?>" class="nav-tab nav-tab-active" aria-selected="true">Add New Metadata</a> -->
		<!-- <?php foreach ( $tabs as $tab ) : ?>
		<a href="<?php echo $tab['url']; ?>" class="<?php echo implode( ' ', $tab['classes'] ); ?>"><?php echo $tab['text']; ?></a>
		<?php endforeach; ?> -->
		<?php
		foreach ( $tabs as $tab ) {
			$current_tab = current( $tab );
			if ( $current_tab === $action ) {
				$tab['classes'][] = 'nav-tab-active';
			}
			echo '<a href="' . $tab['url'] . '" class="' . implode( ' ', $tab['classes'] ) . '" aria-selected="' . $tab['selected'] . '">' . $tab['text'] . '</a>';
		}
		?>
	</nav>
	<div class="tab-content">
	<?php
	switch ( $action ) {
		case 'edit':
			coe_am_display_edit();
			break;
		case 'view':
			coe_am_display_view();
			break;
		case 'import':
			coe_am_display_import();
			break;
		default:
			coe_am_display_add_metadata();
			//require_once $_coe['path'] . 'inc/coe_display_add_metadata.php';
			break;
	}
	?>
	</div>
</div>
	<?php
}

function coe_am_display_add_metadata( $tab = 'new' ) {
	$_coe = coe_am_populate_constants();
	$ui   = new Coe_Am_Admin_UI();
	?>
<form class="metadata-form" action="<?php esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" style="float: left;">
	<?php wp_nonce_field( 'coe_am_add_metadata_nonce_action', 'coe_am_add_metadata_nonce_field' ); ?>
	<div class="postbox-container">
		<div id="poststuff">
			<div class="postbox basic-settings">
				<div class="postbox-header">
					<h2 class="hndle ui-sortable-handle">
						<span><?php esc_html_e( 'Basic Settings', $_coe['text'] ); ?></span>
					</h2>
					<div class="handle-actions hide-if-no-js">
						<button type="button" class="handlediv" aria-expanded="true">
							<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Basic settings', $_coe['text'] ); ?></span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button> <!-- handlediv -->
					</div> <!-- handle-actions -->
				</div> <!-- postbox-header -->
				<div class="inside">
					<div class="main" style="padding-top:15px;">
						<?php
						echo $ui->make_text_input(
							array(
								'additional_text' => '',
								'field_desc'      => __( 'Please use only alphanumeric characters and spaces.', $_coe['text'] ),
								'label_text'      => __( 'Name', $_coe['text'] ),
								'maxlength'       => 32,
								'name'            => 'single_name',
								'placeholder'     => __( '(e.g. Region)', $_coe['text'] ),
								'required'        => true,
								'textvalue'       => '',
								'wrap'            => true,
							)
						);
						?>
						<?php
						echo $ui->make_text_input(
							array(
								'additional_text' => '',
								'field_desc'      => __( 'Please use only alphanumeric characters and spaces.', $_coe['text'] ),
								'label_text'      => __( 'Plural Name', $_coe['text'] ),
								'maxlength'       => 32,
								'name'            => 'plural_name',
								'placeholder'     => __( '(e.g. Regions)', $_coe['text'] ),
								'required'        => true,
								'textvalue'       => '',
								'wrap'            => true,
							)
						);
						?>
						<?php
						$options = array(
							array(
								'value'    => '0',
								'text'     => __( 'No', $_coe['text'] ),
								'selected' => 'selected',
							),
							array(
								'value' => '1',
								'text'  => __( 'Yes', $_coe['text'] ),
							),
						);
						echo $ui->make_select_input(
							array(
								'additional_text' => '',
								'field_desc'      => __( 'Choose whether you wish to be able to assign multiple terms to an asset.', $_coe['text'] ),
								'label_text'      => __( 'Assign Multiple Values', $_coe['text'] ),
								'name'            => 'assign_multiple',
								'wrap'            => true,
								'options'         => $options,
							)
						);
						?>
						<?php
						$options = array(
							array(
								'value'    => 'false',
								'text'     => __( 'No', $_coe['text'] ),
								'selected' => 'selected',
							),
							array(
								'value' => 'true',
								'text'  => __( 'Yes', $_coe['text'] ),
							),
						);
						echo $ui->make_select_input(
							array(
								'additional_text' => '',
								'field_desc'      => __( 'Choose whether you wish for the values to be hierarchical in nature.', $_coe['text'] ),
								'label_text'      => __( 'Hierarchical Values', $_coe['text'] ),
								'name'            => 'hierarchical',
								'wrap'            => true,
								'options'         => $options,
							)
						);
						?>
						<?php
						echo $ui->make_textarea(
							array(
								'additional_text' => '',
								'field_desc'      => __( '(Optional) Enter a description for your metadata.', $_coe['text'] ),
								'label_text'      => __( 'Description', $_coe['text'] ),
								'name'            => 'description',
								'wrap'            => true,
								'rows'            => 3,
								'cols'            => '',
							)
						)
						?>
						<div class="submit-basic">
							<hr>
							<p>
								<input type="hidden" name="coe_am_post_types" value="asset">
								<input type="hidden" name="action" value="coe_am_handle_metadata">
								<input type="hidden" name="coe_status" id="coe_status" value="<?php echo esc_attr( $tab ); ?>" />
								<input type="submit" class="button-primary" name="coe_am_submit" value="<?php echo esc_attr( apply_filters( 'coe_am_taxonomy_submit_edit', esc_attr__( 'Create Metadata', $_coe['text'] ) ) ); ?>" />
								<?php if ( ! empty( $current ) ) : ?>
								<input type="hidden" name="tax_original" id="tax_original" value="<?php echo esc_attr( $current['name'] ); ?>" />
								<?php endif; ?>
							</p>
						</div>
					</div> <!-- main -->
				</div> <!-- inside -->
			</div><!-- postbox.basic-settings -->
		</div> <!-- #poststuff -->
	</div> <!-- postbox-container -->
</form>
<div class="output" style="float: right;">
	<?php
	$taxes = get_object_taxonomies( array( 'asset' ), 'objects' );

	echo '<pre>';
	var_dump( get_option( 'coe_am_metadata' ) );
	var_dump( empty( get_option( 'coe_am_metadata' ) ) );
	echo '<pre><hr>';

	if ( ! empty( $taxes ) ) {
		echo '<pre>';
		var_dump( $taxes );
		echo '</pre>';
	}
	?>
</div>
	<?php
}

function coe_am_display_edit() {
	$_coe = coe_am_populate_constants();
	// $taxonomies        = coe_am_get_taxonomy_data();
	// $selected_taxonomy = coe_am_get_current_taxonomy( $taxonomy_deleted );

	// if ( $selected_taxonomy && array_key_exists( $selected_taxonomy, $taxonomies ) ) {
	// 	$current = $taxonomies[ $selected_taxonomy ];
	// }
	?>
	<p style="margin-top: 20px;">Edit existing metadata term settings is coming soon.</p>
	<hr>
	<pre>
	<?php var_dump( get_object_taxonomies( array( 'asset' ), 'objects' ) ); ?>
	</pre>
	<?php
}

function coe_am_display_view() {
	$_coe = coe_am_populate_constants();
	?>
	<p style="margin-top: 20px;">Edit existing metadata term settings is coming soon.</p>
	<?php
}

function coe_am_display_import() {
	$_coe = coe_am_populate_constants();
	?>
	<p style="margin-top: 20px;">The ability to import and export metadata terms is coming soon.</p>
	<?php
}

function coe_am_process_metadata( $data = array() ) {
	$_coe = coe_am_populate_constants();

	/**
	 * Fires before a taxonomy is updated to our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data we are updating.
	 */
	do_action( 'coe_am_before_update_taxonomy', $data );

	if ( empty( $data['single_name'] ) ) {
		return coe_am_admin_notices( 'error', '', false, esc_html__( 'Please provide a name for your term', $_coe['text'] ) );
	}

	$single       = $data['single_name'];
	$name         = strtolower( str_replace( ' ', '-', $data['single_name'] ) );
	$plural       = $data['plural_name'];
	$multiple     = filter_var( $data['assign_multiple'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	$hierarchical = filter_var( $data['hierarchical'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	$description  = stripslashes_deep( $data['description'] );
	$tax_name     = strtolower( $single );
	$meta_box_cb  = '';
	$taxonomies   = coe_am_get_taxonomy_data();

	// Maybe a little harsh, but we shouldn't be saving THAT frequently.
	delete_option( "default_term_{$name}" );

	if ( empty( $data['coe_am_post_types'] ) ) {
		$data['coe_am_post_types'] = array( 'asset' );
	}

	if ( ! empty( $data['tax_original'] ) && $data['tax_original'] !== $name ) {
		if ( ! empty( $data['update_taxonomy'] ) ) {
			add_filter( 'coe_am_convert_taxonomy_terms', '__return_true' );
		}
	}

	/**
	 * We have to set the meta_box_cb
	 * value programmatically depending
	 * on the chosen settings for
	 * $data['multiple'] and $data['hierarchical']
	 */
	if ( $multiple ) {
		if ( ! $hierarchical ) {
			$meta_box_cb = 'coe_am_meta_check_box';
		} else {
			$meta_box_cb = '';
		}
	} else {
		if ( ! $hierarchical ) {
			$meta_box_cb = 'coe_am_meta_select_box';
		} else {
			$meta_box_cb = 'coe_am_meta_radio_box';
		}
	}

	$labels = array(
		'name'                       => $plural,
		'singular_name'              => $single,
		'search_items'               => sprintf( __( 'Search %s', $_coe['text'] ), $plural ),
		'popular_items'              => sprintf( __( 'Popular %s', $_coe['text'] ), $plural ),
		'all_items'                  => sprintf( __( 'All %s', $_coe['text'] ), $plural ),
		'parent_item'                => sprintf( __( 'Parent %s', $_coe['text'] ), $single ),
		'parent_item_colon'          => sprintf( __( 'Parent %s:', $_coe['text'] ), $single ),
		'edit_item'                  => sprintf( __( 'Edit %s', $_coe['text'] ), $single ),
		'view_item'                  => sprintf( __( 'View %s', $_coe['text'] ), $single ),
		'update_item'                => sprintf( __( 'Update %s', $_coe['text'] ), $single ),
		'add_new_item'               => sprintf( __( 'Add new %s', $_coe['text'] ), strtolower( $single ) ),
		'new_item_name'              => sprintf( __( 'New %s Name', $_coe['text'] ), $single ),
		'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', $_coe['text'] ), strtolower( $plural ) ),
		'add_or_remove_items'        => sprintf( __( 'Add or remove %s', $_coe['text'] ), strtolower( $plural ) ),
		'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', $_coe['text'] ), strtolower( $plural ) ),
		'not_found'                  => sprintf( __( 'No %s found', $_coe['text'] ), strtolower( $plural ) ),
		'no_terms'                   => sprintf( __( 'No %s', $_coe['text'] ), strtolower( $plural ) ),
		'items_list_navigation'      => sprintf( __( '%s list navigation', $_coe['text'] ), $plural ),
		'items_list'                 => sprintf( __( '%s list', $_coe['text'] ), $plural ),
		'most_used'                  => sprintf( __( 'Most Used %s', $_coe['text'] ), $plural ),
		'back_to_items'              => sprintf( __( '← Back to %s', $_coe['text'] ), $plural ),
		'menu_name'                  => $plural,
		'new_item'                   => sprintf( __( 'New %s', $_coe['text'] ), $single ),
		'view_items'                 => sprintf( __( 'View %s', $_coe['text'] ), $plural ),
		'not_found_in_trash'         => sprintf( __( 'No %s found in trash', $_coe['text'] ), strtolower( $plural ) ),
		'archives'                   => sprintf( __( '%s Archives', $_coe['text'] ), $single ),
		'attributes'                 => sprintf( __( 'New %s', $_coe['text'] ), $single ),
		'insert_into_item'           => sprintf( __( '%s Attributes', $_coe['text'] ), $single ),
		'uploaded_to_this_item'      => sprintf( __( 'Uploaded to this %s', $_coe['text'] ), strtolower( $single ) ),
		'archive_title'              => $plural,
		'name_admin_bar'             => $single,
	);

	$args = array(
		'labels'              => $labels,
		'description'         => ( isset( $data['description'] ) ) ? $data['description'] : '',
		'public'              => ( isset( $data['public'] ) ) ? $data['public'] : true,
		'publicly_queryable'  => ( isset( $data['publicly_queryable'] ) ) ? $data['publicly_queryable'] : true,
		'exclude_from_search' => ( isset( $data['exclude_from_search'] ) ) ? $data['exclude_from_search'] : false,
		'show_ui'             => ( isset( $data['show_ui'] ) ) ? $data['show_ui'] : true,
		'show_in_menu'        => ( isset( $data['show_in_menu'] ) ) ? $data['show_in_menu'] : true,
		'query_var'           => ( isset( $data['query_var'] ) ) ? $data['query_var'] : true,
		'show_in_admin_bar'   => ( isset( $admin ) ) ? $admin : true,
		'capability_type'     => ( isset( $data['capability_type'] ) ) ? $data['capability_type'] : 'post',
		'has_archive'         => ( isset( $data['has_archive'] ) ) ? $data['has_archive'] : true,
		'hierarchical'        => ( isset( $hierarchical ) ) ? $hierarchical : true,
		'supports'            => ( isset( $data['supports'] ) ) ? $data['supports'] : array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'revisions',
			'page-attributes',
			'post-formats',
		),
		'show_in_rest'        => ( isset( $data['show_in_rest'] ) ) ? $data['show_in_rest'] : true,
		'show_in_admin_bar'   => ( isset( $data['show_in_admin_bar'] ) ) ? $data['show_in_admin_bar'] : false,
		'menu_position'       => ( isset( $data['menu_position'] ) ) ? $data['menu_position'] : 21,
		'menu_icon'           => ( isset( $data['menu_icon'] ) ) ? $data['menu_icon'] : 'dashicons-admin-generic',
		'show_in_nav_menus'   => ( isset( $data['show_in_nav_menus'] ) ) ? $data['show_in_nav_menus'] : true,
		'meta_box_cb'         => $meta_box_cb,
	);

	$taxonomies = array(
		$name => array(
			'name'                => $name,
			'labels'              => $labels,
			'description'         => ( isset( $data['description'] ) ) ? $data['description'] : '',
			'public'              => ( isset( $data['public'] ) ) ? $data['public'] : true,
			'publicly_queryable'  => ( isset( $data['publicly_queryable'] ) ) ? $data['publicly_queryable'] : true,
			'exclude_from_search' => ( isset( $data['exclude_from_search'] ) ) ? $data['exclude_from_search'] : false,
			'show_ui'             => ( isset( $data['show_ui'] ) ) ? $data['show_ui'] : true,
			'show_in_menu'        => ( isset( $data['show_in_menu'] ) ) ? $data['show_in_menu'] : true,
			'query_var'           => ( isset( $data['query_var'] ) ) ? $data['query_var'] : true,
			'show_in_admin_bar'   => ( isset( $admin ) ) ? $admin : true,
			'capability_type'     => ( isset( $data['capability_type'] ) ) ? $data['capability_type'] : 'post',
			'has_archive'         => ( isset( $data['has_archive'] ) ) ? $data['has_archive'] : true,
			'hierarchical'        => ( isset( $hierarchical ) ) ? $hierarchical : true,
			'supports'            => ( isset( $data['supports'] ) ) ? $data['supports'] : array(
				'title',
				'editor',
				'excerpt',
				'thumbnail',
				'revisions',
				'post-formats',
			),
			'show_in_rest'        => ( isset( $data['show_in_rest'] ) ) ? $data['show_in_rest'] : true,
			'show_in_admin_bar'   => ( isset( $data['show_in_admin_bar'] ) ) ? $data['show_in_admin_bar'] : false,
			'menu_position'       => ( isset( $data['menu_position'] ) ) ? $data['menu_position'] : 21,
			'menu_icon'           => ( isset( $data['menu_icon'] ) ) ? $data['menu_icon'] : 'dashicons-admin-generic',
			'show_in_nav_menus'   => ( isset( $data['show_in_nav_menus'] ) ) ? $data['show_in_nav_menus'] : true,
			'meta_box_cb'         => $meta_box_cb,
		),
	);

	if ( false === ( $success = apply_filters( 'coe_am_metadata_update_save', false, $taxonomies, $data ) ) ) {
		$success = update_option( 'coe_am_metadata', $taxonomies );
	}
	update_option( 'coe_am_metadata', $taxonomies );

	// Used to help flush rewrite rules on init.
	set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) && 'new' === $data['cpt_tax_status'] ) {
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
 * Check the referrer and $_POST data from the form
 * and, if everything's okay, send the $_POST data
 * out for processing.
 *
 * @since 1.0.0
 * @return void
 */
function coe_am_handle_metadata() {
	if ( wp_doing_ajax() || ! is_admin() ) {
		return;
	}

	if ( ! empty( $_GET ) && isset( $_GET['page'] ) && 'metadata' !== $_GET['page'] ) {
		return;
	}

	if ( ! empty( $_POST ) ) {
		$result = '';

		if ( isset( $_POST['coe_am_submit'] ) ) {
			check_admin_referer( 'coe_am_add_metadata_nonce_action', 'coe_am_add_metadata_nonce_field' );
			$result = coe_am_process_metadata( $_POST );
		} elseif ( isset( $_POST['coe_am_delete'] ) ) {
			check_admin_referrer( 'coe_am_delete_metadata_nonce_action', 'coe_am_delete_metadata_nonce_field' );
			$result = coe_am_delete_metadata( $_POST );
		}

		if ( isset( $_POST['coe_am_delete'] ) && empty( coe_am_get_metadata_slug() ) ) {
			wp_safe_redirect( admin_url( $page_path ) );
		}
	}
}
add_action( 'init', 'coe_am_handle_metadata', 8 );
