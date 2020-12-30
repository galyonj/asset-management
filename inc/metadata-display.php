<?php
/**
 * Build and output html for managing asset metadata
 *
 * @author     : John Galyon
 * @since      1.0.3
 * @package    Coe_Am
 * @subpackage Coe_Am/metadata-display
 * @license    GPL-2.0+
 *
 */

function coe_am_metadata_display() {
	$_coe        = coe_am_constants();
	$action      = isset( $_GET['action'] ) ? $_GET['action'] : '';
	$classes     = array( 'nav-tab' );
	$current     = null;
	$is_dev      = ( isset( $_SERVER['SERVER_NAME'] ) && '127.0.0.1' === $_SERVER['SERVER_NAME'] );
	$nonce       = wp_verify_nonce( 'coe_am_metadata_nonce_field', 'coe_am_metadata_nonce_action' );
	$page_path   = admin_url( 'edit.php?post_type=asset&page=metadata' );
	$page_title  = get_admin_page_title();
	$tab_item    = '<a href="%s" class="%s" aria-selected="%s">%s</a>';
	$tabs        = array();
	$tax_deleted = apply_filters( 'coe_am_tax_deleted', false );
	$taxes       = coe_am_get_saved_taxes();
	$ui          = new Coe_Am_Admin_UI();

	/**
	 * Set up default tab
	 */
	$tabs['add'] = array(
		'action'   => 'add',
		'text'     => esc_html__( 'Add Metadata', $_coe['text'] ),
		'classes'  => $classes,
		'url'      => esc_url( add_query_arg( array( 'action' => 'add' ) ), $page_path ),
		'selected' => 'false',
	);

	if ( empty( $action ) ) {
		$tabs['add']['classes'][] = 'nav-tab-active';
		$tabs['add']['selected']  = 'true';
	}

	if ( ! empty( $taxes ) ) {

		if ( get_query_var( 'action' ) === $action ) {
			$tab['classes'][] = 'nav-tab-active';
			$tab['selected']  = 'true';
		}

		$tabs['edit']   = array(
			'action'   => 'edit',
			'text'     => esc_html__( 'Edit Metadata', 'coe-asset-management' ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'edit' ) ), $page_path ),
			'selected' => 'false',
		);
		$tabs['build']  = array(
			'action'   => 'build',
			'text'     => esc_html__( 'Shortcode Builder', 'coe-asset-management' ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'build' ) ), $page_path ),
			'selected' => false,
		);
		$tabs['view']   = array(
			'action'   => 'view',
			'text'     => esc_html__( 'View Metadata Terms', 'coe-asset-management' ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'view' ) ), $page_path ),
			'selected' => 'false',
		);
		$tabs['import'] = array(
			'action'   => 'import',
			'text'     => esc_html__( 'Import/Export Metadata', 'coe-asset-management' ),
			'classes'  => $classes,
			'url'      => esc_url( add_query_arg( array( 'action' => 'import' ) ), $page_path ),
			'selected' => 'false',
		);
	}
	?>
	<div class="wrap">
		<h1>
			<?php
			echo esc_html( get_admin_page_title() );
			?>
		</h1>
		<nav class="nav-tab-wrapper wp-clearfix" aria-label="Secondary Menu">
		<?php
		/**
		 * Output each tab in the $tabs array
			* as a hyperlink
			*
			* @since 1.0.0
			* @internal
			*/
		foreach ( $tabs as $tab ) {
			/**
			 * Get the current tab and, if it matches
			 * the value of $_GET['action'], update the
			 * classes array and the selected value for the tab
			 */
			$current_tab = current( $tab );
			if ( $current_tab === $action ) {
				$tab['classes'][] = 'nav-tab-active';
				$tab['selected']  = 'true';
			}

			/**
			 * PHPCS complains that the values aren't properly escaped, but I'm
			 * not going to escape them because the only place it is possible to
			 * set them is at line 19 of this very file.
			 *
			 * @param string $format output string
			 * @param mixed ...$values values to be inserted into the formatted string.
			 *
			 * @since 1.0.0
			 * @link https://www.php.net/manual/en/function.sprintf.php
			 */
			echo sprintf( $tab_item, $tab['url'], implode( ' ', $tab['classes'] ), $tab['selected'], $tab['text'] );
		}
		?>
		</nav>
		<div class="tab-content">
		<?php
		if ( empty( $action ) || 'add' === $action || 'edit' === $action ) {
			$taxes                = get_option( 'coe_am_metadata' );
					$selected_tax = coe_am_get_selected_tax( $_POST, $tax_deleted );

			if ( $selected_tax && array_key_exists( $selected_tax, $taxes ) ) {
				$current = $taxes[ $selected_tax ];
			}
			?>
			<div class="postbox-container">
				<div id="poststuff">
				<?php if ( 'edit' === $action ) : ?>
				<form action="<?php esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="metadata-form">
					<?php wp_nonce_field( 'coe_am_select_metadata_nonce_action', 'coe_am_select_metadata_nonce_field' ); ?>
					<input type="hidden" name="action" value="coe_am_get_selected_tax">
					<div class="postbox metadata-select">
						<!-- <div class="postbox-header">
							<h2><?php echo esc_html_e( 'Select Metadata', $_coe['text'] ); ?></h2>
						</div> -->
						<div class="inside">
							<div class="main">
								<?php
								$select['options'][] = array(
									'value' => '',
									'text'  => esc_attr__( 'Make a selection to begin', $_coe['text'] ),
								);
								if ( ! empty( $taxes ) ) {
									foreach ( $taxes as $tax ) {
										$select['options'][] = array(
											'value' => $tax['name'],
											'text'  => $tax['label'],
										);
									}

									$select['selected'] = isset( $current['name'] ) ? esc_attr( $current['name'] ) : '';
									echo $ui->make_select_input(
										array(
											'additional_text' => '',
											'field_desc' => esc_attr__( 'Select the metadata term you wish to modify.' ),
											'label_text' => esc_attr__( 'Select Metadata' ),
											'name'       => 'select_tax',
											'wrap'       => true,
											'selections' => $select,
										)
									);
								}
								?>
								<div class="btn-wrapper">
									<hr>
									<input type="submit" class="coe-am-edit button button-primary" id="coe_am_edit" name="coe_am_edit" value="<?php echo esc_attr__( 'Edit Metadata' ); ?>">
									<input type="submit" class="coe-am-delete button button-secondary" id="coe_am_delete" name="coe_am_delete" value="<?php echo esc_attr__( 'Delete Metadata' ); ?>">
								</div>
							</div>
						</div>
					</div>
				</form>
				<?php endif; ?>
				<?php if ( empty( $action ) || 'add' === $action || ( 'edit' === $ action && isset( $current ) && isset( $_POST['coe_am_edit'] ) ) ) : ?>
				<form action="<?php esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" class="metadata-form <?php echo ( ! empty( $action ) ) ? $action : 'add'; ?>-metadata">
					<?php wp_nonce_field( 'coe_am_metadata_nonce_action', 'coe_am_metadata_nonce_field' ); ?>
					<input type="hidden" name="action" value="coe_am_process_tax">
					<div class="postbox metadata-settings basic">
						<div class="postbox-header">
							<h2><?php echo esc_html_e( 'Basic Settings', $_coe['text'] ); ?></h2>
						</div>
						<div class="inside">
							<div class="main">
								<div class="btn-wrapper">
									<?php
									echo $ui->make_text_input(
										array(
											'additional_text' => '',
											'field_desc'  => esc_attr__( 'Please use only alphanumeric characters and spaces.', $_coe['text'] ),
											'label_text'  => esc_attr__( 'Singular Label', $_coe['text'] ),
											'maxlength'   => 32,
											'name'        => 'label_singular', // cptui `singular_label`
											'placeholder' => esc_attr__( '(e.g. Method)', $_coe['text'] ),
											'required'    => true,
											'textvalue'   => ( ! empty( $current ) ) ? $current['label_singular'] : '',
											'wrap'        => true,
										)
									);
									?>
									<?php
									echo $ui->make_text_input(
										array(
											'additional_text' => '',
											'field_desc'  => esc_attr__( 'Please use only alphanumeric characters and spaces.', $_coe['text'] ),
											'label_text'  => esc_attr__( 'Plural Label', $_coe['text'] ),
											'maxlength'   => 32,
											'name'        => 'label_plural', // cptui `label`
											'placeholder' => esc_attr__( '(e.g. Methods)', $_coe['text'] ),
											'required'    => true,
											'textvalue'   => ( ! empty( $current ) ) ? $current['label'] : '',
											'wrap'        => true,
										)
									);
									?>
									<?php
									$select['options'] = array(
										array(
											'value'   => '0',
											'text'    => esc_attr__( 'No', $_coe['text'] ),
											'default' => 'true',
										),
										array(
											'value' => '1',
											'text'  => esc_attr__( 'Yes', $_coe['text'] ),
										),
									);

									$selected           = isset( $current ) ? coerce_bool( $current['assign_multiple'] ) : '';
									$select['selected'] = ( ! empty( $selected ) ) ? $current['assign_multiple'] : '';
									echo $ui->make_select_input(
										array(
											'additional_text' => '',
											'field_desc' => esc_attr__( 'Choose whether you wish to be able to assign multiple terms to an asset.', $_coe['text'] ),
											'label_text' => esc_attr__( 'Assign Multiple Values', $_coe['text'] ),
											'name'       => 'assign_multiple',
											'wrap'       => true,
											'selections' => $select,
										)
									);
									?>
									<?php
									$select['options'] = array(
										array(
											'value'   => '0',
											'text'    => esc_attr__( 'No', $_coe['text'] ),
											'default' => '1',
										),
										array(
											'value' => '1',
											'text'  => esc_attr__( 'Yes', $_coe['text'] ),
										),
									);

									$selected           = isset( $current ) ? coerce_bool( $current['hierarchical'] ) : '';
									$select['selected'] = ( ! empty( $selected ) ) ? $current['hierarchical'] : '';
									echo $ui->make_select_input(
										array(
											'additional_text' => '',
											'field_desc' => esc_attr__( 'Choose whether you wish for the values to be hierarchical in nature.', $_coe['text'] ),
											'label_text' => esc_attr__( 'Hierarchical Values', $_coe['text'] ),
											'name'       => 'hierarchical',
											'wrap'       => true,
											'selections' => $select,
										)
									);
									?>
									<?php
									echo $ui->make_textarea(
										array(
											'additional_text' => '',
											'field_desc' => esc_attr__( '(Optional) Enter a short, text-only description for your metadata.', $_coe['text'] ),
											'label_text' => esc_attr__( 'Description', $_coe['text'] ),
											'name'       => 'description',
											'wrap'       => true,
											'textvalue'  => ( ! empty( $current['description'] ) ) ? $current['description'] : '',
											'required'   => false,
											'rows'       => 3,
											'cols'       => '',
										)
									)
									?>
									<?php
									if ( 'edit' === $action ) {
										echo '<hr>';
										echo $ui->make_checkbox(
											array(
												'additional_text' => '',
												'field_desc' => '',
												'label_text' => esc_attr__( 'Migrate values to the new metadata term', $_coe['text'] ),
												'name'    => 'migrate_terms',
												'wrap'    => true,
												'offset'  => true,
												'checkvalue' => true,
												'checked' => false,
											)
										);
										echo $ui->make_checkbox(
											array(
												'additional_text' => '',
												'field_desc' => '',
												'label_text' => esc_attr__( 'Delete the old metadata term', $_coe['text'] ),
												'name'    => 'keep_old_tax',
												'wrap'    => true,
												'offset'  => true,
												'checkvalue' => true,
												'checked' => false,
											)
										);
									}
									?>
									<div class="btn-wrapper">
										<hr>
										<?php if ( 'edit' === $action ) : ?>
										<input type="submit" class="coe-am-submit button button-primary" class="coe_am_submit" name="coe_am_submit" value="<?php echo esc_attr__( 'Save Changes' ); ?>">
										<?php else : ?>
										<input type="submit" class="coe-am-submit button button-primary" class="coe_am_submit" name="coe_am_submit" value="<?php echo esc_attr__( 'Create Metadata' ); ?>">
										<input type="reset" class="coe-am-reset button button-secondary" class="coe_am_reset" name="coe_am_reset" value="<?php echo esc_attr__( 'Reset Form' ); ?>" style="color: #6c757d;" />
										<?php endif; ?>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="postbox metadata-settings advanced">
						<div class="postbox-header">
							<h2><?php echo esc_html_e( 'Advanced Settings', $_coe['text'] ); ?></h2>
						</div>
						<div class="inside">
							<div class="main">
								<div class="p-3 mb-2 warning-msg">
									<i class="fas fa-exclamation-triangle fa-pull-left"></i>
									<p style="margin-bottom: 0;">Take care with the settings found below, as changing them from the defaults may cause unexpected behavior.</p>
								</div> <!-- warning div -->
								<h3><?php echo esc_html_e( 'Visibility', $_coe['text'] ); ?></h3>
								<p>The settings below determine whether the metadata is visible in certain parts of the WordPress interface.</p>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['public'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['public'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether a metadata term is intended for use publicly either via the admin interface or by front-end users.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Public', $_coe['text'] ),
										'name'            => 'public',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['publicly_queryable'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['publicly_queryable'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether or not the taxonomy should be publicly queryable.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Publicly Queryable', $_coe['text'] ),
										'name'            => 'publicly_queryable',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value'   => '0',
										'text'    => esc_attr__( 'No', $_coe['text'] ),
										'default' => '1',
									),
									array(
										'value' => '1',
										'text'  => esc_attr__( 'Yes', $_coe['text'] ),
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['show_in_menu'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_menu'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether to show the taxonomy in the admin menu.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Show in Menu', $_coe['text'] ),
										'name'            => 'show_in_menu',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['show_in_nav_menus'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_nav_menus'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether to make this metadata available for selection in navigation menus.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Show in Navigation', $_coe['text'] ),
										'name'            => 'show_in_nav_menus',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['show_in_quick_edit'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_quick_edit'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether to show the metadata in the quick/bulk edit panel.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Show in Quick Edit', $_coe['text'] ),
										'name'            => 'show_in_quick_edit',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['show_tagcloud'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['show_tagcloud'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether to list the metadata in the Tag Cloud Widget controls.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Show Tag Cloud', $_coe['text'] ),
										'name'            => 'show_tagcloud',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['show_ui'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['show_ui'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether to generate a default UI for managing this metadata.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Show UI', $_coe['text'] ),
										'name'            => 'show_ui',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<h3 style="font-size: 1rem; border-bottom: 1px solid #ddd; margin-bottom: 15px;">URL Rewrite</h3>
								<p>The settings below control how the metadata is presented in the URL structure for this website.</p>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['rewrite'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether or not WordPress should use rewrites for this metadata.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Rewrite', $_coe['text'] ),
										'name'            => 'rewrite',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								echo $ui->make_text_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Custom metadata rewrite slug. Defaults to metadata slug.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Custom Rewrite Slug', $_coe['text'] ),
										'maxlength'       => 32,
										'name'            => 'rewrite_slug',
										'placeholder'     => '',
										'textvalue'       => ( ! empty( $current['rewrite_slug'] ) ) ? $current['rewrite_slug'] : '',
										'wrap'            => true,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['with_front'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['with_front'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Should the permastruct be prepended with the front base?', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Rewrite With Front', $_coe['text'] ),
										'name'            => 'with_front',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								$select['options'] = array(
									array(
										'value'   => '0',
										'text'    => esc_attr__( 'No', $_coe['text'] ),
										'default' => '1',
									),
									array(
										'value' => '1',
										'text'  => esc_attr__( 'Yes', $_coe['text'] ),
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['rewrite_hierarchical'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['rewrite_hierarchical'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Should the permastruct allow hierarchical urls?', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Hierarchical Rewrite', $_coe['text'] ),
										'name'            => 'rewrite_hierarchical',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<h3 style="font-size: 1rem; border-bottom: 1px solid #ddd; margin-bottom: 15px;">REST API</h3>
								<p>The settings below determine whether (and how) the metadata is displayed via the WordPress REST API.<br>Disabling these options can break metadata functionality.</p>
								<?php
								$select['options'] = array(
									array(
										'value' => '0',
										'text'  => esc_attr__( 'No', $_coe['text'] ),
									),
									array(
										'value'   => '1',
										'text'    => esc_attr__( 'Yes', $_coe['text'] ),
										'default' => '1',
									),
								);

								$selected           = isset( $current ) ? coerce_bool( $current['show_in_rest'] ) : '';
								$select['selected'] = ( ! empty( $selected ) ) ? $current['show_in_rest'] : '';
								echo $ui->make_select_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Whether to include the metadata in the REST API.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'Show in REST API', $_coe['text'] ),
										'name'            => 'show_in_rest',
										'wrap'            => true,
										'selections'      => $select,
									)
								);
								?>
								<?php
								echo $ui->make_text_input(
									array(
										'additional_text' => '',
										'field_desc'      => esc_attr__( 'Slug to use in REST API URLs. Defaults to metadata slug.', $_coe['text'] ),
										'label_text'      => esc_attr__( 'REST API Base Slug', $_coe['text'] ),
										'maxlength'       => 32,
										'name'            => 'rest_base',
										'placeholder'     => '',
										'textvalue'       => ( ! empty( $current['rest_base'] ) ) ? $current['rest_base'] : '',
										'wrap'            => true,
									)
								);
								?>
								<div class="btn-wrapper">
									<hr>
									<?php if ( 'edit' === $action ) : ?>
									<input type="submit" class="coe-am-submit button button-primary" class="coe_am_submit" name="coe_am_submit" value="<?php echo esc_attr__( 'Save Changes' ); ?>">
									<?php else : ?>
									<input type="submit" class="coe-am-submit button button-primary" class="coe_am_submit" name="coe_am_submit" value="<?php echo esc_attr__( 'Create Metadata' ); ?>">
									<input type="reset" class="coe-am-reset button button-secondary" class="coe_am_reset" name="coe_am_reset" value="<?php echo esc_attr__( 'Reset Form' ); ?>" style="color: #6c757d;" />
									<?php endif; ?>
								</div>
							</div>
						</div>
					</div>
				</form>
				<?php endif; ?>
				</div>
			</div>
			<?php
		} elseif ( 'view' === $action ) {

		}
		?>
		</div>
	</div>
}
