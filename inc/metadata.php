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
		'action'   => 'add',
		'text'     => esc_html__( 'Add New Metadata', $_coe['text'] ),
		'classes'  => $classes,
		'url'      => esc_url( add_query_arg( array( 'action' => 'add' ) ), $page_path ),
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
		$tabs['build']  = array(
			'action'   => 'build',
			'text'     => esc_html__( 'Shortcode Builder', $_coe['text'] ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'build' ) ), $page_path ),
			'selected' => false,
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
	?>
<div class="wrap">
	<h1><?php echo get_admin_page_title(); ?></h1>
	<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary Menu">
		<?php
		foreach ( $tabs as $tab ) {
			$current_tab = current( $tab );
			if ( $current_tab === $action ) {
				$tab['classes'][] = 'nav-tab-active';
				$tab['selected']  = 'true';
			}
			echo sprintf( $format, $tab['url'], implode( ' ', $tab['classes'] ), $tab['selected'], $tab['text'] );
			//echo '<a href="' . $tab['url'] . '" class="' . implode( ' ', $tab['classes'] ) . '" aria-selected="' . $tab['selected'] . '">' . $tab['text'] . '</a>';
		}
		?>
	</nav>
	<div class="tab-content">
	<?php
	switch ( $action ) {
		case 'edit':
			coe_am_display_edit_metadata( $tab );
			break;
		case 'view':
			coe_am_display_view();
			break;
		case 'import':
			coe_am_display_import();
			break;
		case 'create':
			coe_am_display_create_shortcode();
			break;
		default:
			coe_am_display_add_metadata( $tab, $current );
			//require_once $_coe['path'] . 'inc/coe_display_add_metadata.php';
			break;
	}
	?>
		<div class="debug" style="float: left; padding-top: 10px; margin-left: 20px;">
			<?php echo $action; ?>
			<hr>
			<pre>
			<?php
			if ( 'edit' === $action ) {
				$terms = get_terms(
					array(
						//'taxonomy'   => 'region',
						'hide_empty' => false,
						'orderby'    => 'term_group',
					)
				);

				foreach ( $terms as $term ) {
					print_r( $term->taxonomy . ' => ' . $term->name );
					echo '<br>';
				}
			} else {
				print_r( get_option( 'coe_am_metadata' ) );
			}
			?>
			</pre>
		</div>
	</div>
</div>
	<?php
}

function coe_am_display_add_metadata( $tab = 'new' ) {
	$_coe    = coe_am_populate_constants();
	$ui      = new Coe_Am_Admin_UI();
	$current = null;
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
									'field_desc'      => __( '(Optional) Enter a short, text-only description for your metadata.', $_coe['text'] ),
									'label_text'      => __( 'Description', $_coe['text'] ),
									'name'            => 'description',
									'wrap'            => true,
									'rows'            => 3,
									'cols'            => '',
								)
							)
							?>
							<hr>
							<p style="margin-bottom: 0;">
								<input type="hidden" name="coe_am_post_types" value="asset">
								<input type="hidden" name="action" value="coe_am_handle_metadata">
								<input type="hidden" name="coe_status" id="coe_status" value="<?php echo esc_attr( $tab ); ?>" />
								<input type="submit" class="coe-am-add button button-primary" name="coe_am_submit" value="<?php echo esc_attr__( 'Create Metadata', $_coe['text'] ); ?>" />
								<?php if ( ! empty( $current ) ) : ?>
									<input type="hidden" name="tax_original" id="tax_original" value="<?php echo esc_attr( $current['name'] ); ?>" />
								<?php endif; ?>
							</p>
						</div> <!-- main -->
					</div> <!-- inside -->
				</div><!-- postbox.basic-settings -->
			</div> <!-- #poststuff -->
		</div> <!-- postbox-container -->
	</form>

	<?php
}

function coe_am_display_edit_metadata( $tab = 'edit' ) {
	$_coe    = coe_am_populate_constants();
	$options = array();
	$taxes   = get_option( 'coe_am_metadata' );
	$ui      = new Coe_Am_Admin_UI();
	?>
	<form action="" class="metadata-form" id="edit-metadata-form" method="post" style="float: left;">
		<?php wp_nonce_field( 'coe_am_editdelete_metadata_nonce_action', 'coe_am_editdelete_metadata_nonce_field' ); ?>
		<div class="postbox-container">
			<div id="poststuff">
				<div class="postbox edit-metadata">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">
							<span><?php esc_html_e( 'Select Metadata', $_coe['text'] ); ?></span>
						</h2>
						<div class="handle-actions hide-if-no-js">
							<button type="button" class="handlediv" aria-expanded="true">
								<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Select metadata', $_coe['text'] ); ?></span>
								<span class="toggle-indicator" aria-hidden="true"></span>
							</button> <!-- handlediv -->
						</div> <!-- handle-actions -->
					</div> <!-- postbox-header -->
					<div class="inside">
						<div class="main">
							<?php
							foreach ( $taxes as $tax ) {
								$options[] = array(
									'value' => $tax['name'],
									'text'  => $tax['labels']['name'],
								);
							}
							echo $ui->make_select_input(
								array(
									'additional_text' => '',
									'field_desc'      => __( 'Select the metadata term you wish to edit.', $_coe['text'] ),
									'label_text'      => __( 'Select Metadata', $_coe['text'] ),
									'name'            => 'select_metadata',
									'wrap'            => true,
									'options'         => $options,
								)
							);
							?>
							<hr>
							<div class="submit-select-metadata button-group">
								<p style="margin-bottom: 0;">
									<input type="hidden" name="coe_am_post_types" value="asset">
									<input type="hidden" name="action" value="coe_am_handle_metadata">
									<input type="submit" class="coe-am-edit button button-primary" name="coe_am_edit" value="<?php echo esc_attr__( 'Edit Metadata', $_coe['text'] ); ?>" />
									<input type="submit" class="coe-am-delete button button-secondary" name="coe_am_delete" value="<?php echo esc_attr__( 'Delete Metadata', $_coe['text'] ); ?>" />
									<?php if ( ! empty( $current ) ) : ?>
										<input type="hidden" name="tax_original" id="tax_original" value="<?php echo esc_attr( $current['name'] ); ?>" />
									<?php endif; ?>
								</p>
							</div>
						</div> <!-- main -->
					</div> <!-- inside -->
				</div><!-- postbox.edit-metadata -->
				<div class="postbox edit-metadata-basic">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">
							<span><?php esc_html_e( 'Basic Settings', $_coe['text'] ); ?></span>
						</h2>
						<div class="handle-actions hide-if-no-js">
							<button class="handlediv">
								<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Basic settings', $_coe['text'] ); ?></span>
								<span class="toggle-indicator" aria-hidden="true"></span>
							</button>
						</div>
					</div> <!-- header -->
					<div class="inside">
						<div class="main">
							<?php
							echo $ui->make_text_input(
								array(
									'additional_text' => '',
									'field_desc'      => __( 'Please use only alphanumeric characters and spaces.', $_coe['text'] ),
									'label_text'      => __( 'Name', $_coe['text'] ),
									'maxlength'       => 32,
									'name'            => 'single_name',
									'placeholder'     => __( '(e.g. Region)', $_coe['text'] ),
									'required'        => false,
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
									'required'        => false,
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
									'field_desc'      => __( '(Optional) Enter a short, text-only description for your metadata.', $_coe['text'] ),
									'label_text'      => __( 'Description', $_coe['text'] ),
									'name'            => 'description',
									'wrap'            => true,
									'rows'            => 3,
									'cols'            => '',
								)
							)
							?>
							<p style="margin-bottom: 0;">
								<input type="hidden" name="coe_am_post_types" value="asset">
								<input type="hidden" name="action" value="coe_am_handle_metadata">
								<input type="hidden" name="coe_status" id="coe_status" value="<?php echo esc_attr( $tab ); ?>" />
								<input type="submit" class="coe-am-update button button-primary" name="coe_am_update" value="<?php echo esc_attr__( 'Update Metadata', $_coe['text'] ); ?>" />
								<?php if ( ! empty( $current ) ) : ?>
									<input type="hidden" name="tax_original" id="tax_original" value="<?php echo esc_attr( $current['name'] ); ?>" />
								<?php endif; ?>
							</p>
						</div>
					</div>
				</div> <!-- basic-settings -->
				<div class="postbox edit-metadata-advanced">
					<div class="postbox-header">
						<h2 class="hndle ui-sortable-handle">
							<span><?php esc_html_e( 'Advanced Settings', $_coe['text'] ); ?></span>
						</h2>
						<div class="handle-actions hide-if-no-js">
							<button class="handlediv" aria-expanded="true" type="button">
								<span class="screen-reader-text"><?php esc_html_e( 'Toggle panel: Advanced settings', $_coe['text'] ); ?></span>
								<span class="toggle-indicator" aria-hidden="true"></span>
							</button>
						</div>
					</div> <!--header -->
					<div class="inside">
						<div class="main">
							<p style="margin-bottom: 0;">
								<input type="hidden" name="coe_am_post_types" value="asset">
								<input type="hidden" name="action" value="coe_am_handle_metadata">
								<input type="hidden" name="coe_status" id="coe_status" value="<?php echo esc_attr( $tab ); ?>" />
								<input type="submit" class="coe-am-update button button-primary" name="coe_am_update" value="<?php echo esc_attr__( 'Update Metadata', $_coe['text'] ); ?>" />
								<?php if ( ! empty( $current ) ) : ?>
									<input type="hidden" name="tax_original" id="tax_original" value="<?php echo esc_attr( $current['name'] ); ?>" />
								<?php endif; ?>
							</p>
						</div> <!-- main -->
					</div> <!-- inside -->
				</div> <!-- advanced settings -->
			</div> <!-- #poststuff -->
		</div> <!-- postbox-container -->
	</form>
	<?php
	// echo '<pre>';
	// print_r( $taxes );
	// echo '</pre>';
}

function coe_am_display_view() {
	$_coe = coe_am_populate_constants();
	?>
	<p style="margin-top: 20px;">The ability to view metadata/term relationships is coming soon.</p>
	<?php
}

function coe_am_display_create_shortcode() {
	$_coe = coe_am_populate_constants();
	?>
	<p style="margin-top: 20px;">The ability to create custom shortcodes is coming soon.</p>
	<?php
}

function coe_am_display_import() {
	$_coe = coe_am_populate_constants();
	?>
	<p style="margin-top: 20px;">The ability to import and export metadata terms is coming soon.</p>
	<?php
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

		if ( isset( $_POST['coe_am_delete'] ) || isset( $_POST['coe_am_edit'] ) ) {
			check_admin_referer( 'coe_am_editdelete_metadata_nonce_action', 'coe_am_editdelete_metadata_nonce_field' );
			if ( isset( $_POST['coe_am_delete'] ) ) {
				$result = coe_am_delete_metadata( $_POST );
			} else {
				$result = coe_am_process_metadata( $_POST );
			}
		} else {
			check_admin_referer( 'coe_am_add_metadata_nonce_action', 'coe_am_add_metadata_nonce_field' );
			$result = coe_am_process_metadata( $_POST );
		}

		if ( isset( $_POST['coe_am_delete'] ) && empty( coe_am_get_metadata_slugs() ) ) {
			wp_safe_redirect( admin_url( 'edit.php?post_type=asset&page=metadata' ) );
		}
	}
}
add_action( 'init', 'coe_am_handle_metadata', 8 );

function coe_am_delete_metadata( $data = array() ) {

	// Check if they selected one to delete.
	if ( empty( $data['select_metadata'] ) ) {
		return cptui_admin_notices( 'error', '', false, esc_html__( 'Please provide a taxonomy to delete', 'custom-post-type-ui' ) );
	}

	/**
	 * Fires before a taxonomy is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data we are deleting.
	 */
	do_action( 'coe_am_before_delete_metadata', $data );

	$taxonomies = coe_am_get_saved_metadata();

	if ( array_key_exists( strtolower( $data['select_metadata'] ), $taxonomies ) ) {

		/**
		 * If 'delete_terms' is set as part of the $_POST request
		 * we delete the terms before we delete the taxonomy term
		 * itself.
		 *
		 * @since 1.0.1
		 * @uses array $data array of $_POST request options
		 * @uses get_terms function to get terms for a named taxonomy
		 * @uses wp_delete_term function to delete a term for a specific taxonomy
		*/
		$terms = get_terms(
			array(
				'taxonomy'   => $data['select_metadata'],
				'hide_empty' => false,
			)
		);

		// This can be sped up by swapping wp_delete_term with a
		// prepared wpdb query
		foreach ( $terms as $term ) {
			wp_delete_term( $term->id, $data['select_metadata'] );
		}

		unset( $taxonomies[ $data['select_metadata'] ] );

		/**
		 * Filters whether or not 3rd party options were saved successfully within taxonomy deletion.
		 *
		 * @since 1.3.0
		 *
		 * @param bool  $value      Whether or not someone else saved successfully. Default false.
		 * @param array $taxonomies Array of our updated taxonomies data.
		 * @param array $data       Array of submitted taxonomy to update.
		 */
		if ( false === ( $success = apply_filters( 'coe_am_delete_metadata', false, $taxonomies, $data ) ) ) {
			$success = update_option( 'coe_am_metadata', $taxonomies );
		}
	}
	delete_option( "default_term_{$data['select_metadata']}" );

	/**
	 * Fires after a taxonomy is deleted from our saved options.
	 *
	 * @since 1.0.0
	 *
	 * @param array $data Array of taxonomy data that was deleted.
	 */
	do_action( 'coe_am_after_delete_metadata', $data );

	// Used to help flush rewrite rules on init.
	set_transient( 'cptui_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) ) {
		return 'delete_success';
	}
	return 'delete_fail';

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
		return coe_am_admin_notices( 'error', '', false, esc_html__( 'Please provide a name for your metadata', $_coe['text'] ) );
	}

	$single       = $data['single_name'];
	$name         = strtolower( str_replace( ' ', '-', $data['single_name'] ) );
	$plural       = $data['plural_name'];
	$multiple     = filter_var( $data['assign_multiple'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	$hierarchical = filter_var( $data['hierarchical'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
	$description  = stripslashes_deep( $data['description'] );
	$tax_name     = strtolower( $single );
	$meta_box_cb  = '';
	$taxonomies   = coe_am_get_metadata_slugs();

	// Maybe a little harsh, but we shouldn't be saving THAT frequently.
	// delete_option( "default_term_{$name}" );

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
		'add_new_item'               => sprintf( __( 'Add new %s', $_coe['text'] ), $single ),
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
		'name'                => $name,
		'capability_type'     => ( isset( $data['capability_type'] ) ) ? $data['capability_type'] : 'post',
		'description'         => ( isset( $data['description'] ) ) ? $data['description'] : '',
		'exclude_from_search' => ( isset( $data['exclude_from_search'] ) ) ? $data['exclude_from_search'] : false,
		'has_archive'         => ( isset( $data['has_archive'] ) ) ? $data['has_archive'] : true,
		'hierarchical'        => ( isset( $hierarchical ) ) ? $hierarchical : true,
		'labels'              => $labels,
		'menu_icon'           => ( isset( $data['menu_icon'] ) ) ? $data['menu_icon'] : 'dashicons-admin-generic',
		'menu_position'       => ( isset( $data['menu_position'] ) ) ? $data['menu_position'] : 21,
		'meta_box_cb'         => $meta_box_cb,
		'public'              => ( isset( $data['public'] ) ) ? $data['public'] : true,
		'publicly_queryable'  => ( isset( $data['publicly_queryable'] ) ) ? $data['publicly_queryable'] : true,
		'query_var'           => ( isset( $data['query_var'] ) ) ? $data['query_var'] : true,
		'rewrite'             => array(
			'slug'         => $name,
			'with_front'   => true,
			'hierarchical' => false,
		),
		'show_admin_column'   => ( isset( $data['show_admin_column'] ) ) ? $data['show_admin_column'] : true,
		'show_in_admin_bar'   => ( isset( $data['show_in_admin_bar'] ) ) ? $data['show_in_admin_bar'] : false,
		'show_in_menu'        => ( isset( $data['show_in_menu'] ) ) ? $data['show_in_menu'] : true,
		'show_in_nav_menus'   => ( isset( $data['show_in_nav_menus'] ) ) ? $data['show_in_nav_menus'] : true,
		'show_in_rest'        => ( isset( $data['show_in_rest'] ) ) ? $data['show_in_rest'] : true,
		'rest_base'           => ( isset( $data['rest_base'] ) ) ? $data['rest_base'] : strtolower( $plural ),
		'show_ui'             => ( isset( $data['show_ui'] ) ) ? $data['show_ui'] : true,
		'supports'            => ( isset( $data['supports'] ) ) ? $data['supports'] : array(
			'title',
			'editor',
			'excerpt',
			'thumbnail',
			'revisions',
			'page-attributes',
			'post-formats',
		),
	);

	$taxonomies[ $name ] = $args;

	// if ( false === ( $success = apply_filters( 'coe_am_metadata_update_save', false, $taxonomies, $data ) ) ) {
	// 	$success = update_option( 'coe_am_metadata', $taxonomies );
	// }
	update_option( 'coe_am_metadata', $taxonomies );

	// Used to help flush rewrite rules on init.
	set_transient( 'coe_am_flush_rewrite_rules', 'true', 5 * 60 );

	if ( isset( $success ) && 'new' === $data['coe_status'] ) {
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
