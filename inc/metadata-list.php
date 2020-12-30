<?php
/**
 * Show Metadata management options in a table format instead of the
 * existing tabbed format
 *
 * @since 1.0.0
 * @author: John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/metadata-list
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'That\'s illegal.' );
}

/**
 * Output out metadata list html
 *
 * @since 1.0.1
 */
function coe_am_metadata_list_html() {
	$_coe     = coe_am_constants();
	$taxes    = get_object_taxonomies( 'asset', 'objects' );
	$edit_url = '<a href="edit-tags.php?taxonomy=%s"><strong>%s</strong></a>';

	$col_headings = array(
		esc_html__( 'Metadata', $_coe['text'] ),
		esc_html__( 'Assigned Terms', $_coe['text'] ),

	)
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Metadata', $_coe['text'] ); ?>
			<a href="<?php echo admin_url( 'edit.php?post_type=asset&page=metadata' ); ?>" class="page-title-action">Add New</a>
		</h1>
		<hr class="wp-header-end">
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="metadata-list-form">
			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<thead>
					<tr>
					<?php
					foreach ( $col_headings as $heading ) {
						echo '<th scope="col">' . $heading . '</th>';
					}
					?>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $taxes as $tax ) {
						?>
						<tr>
							<td>
								<?php echo sprintf( $edit_url, $tax->name, $tax->label ); ?>
							</td>
							<td>
							</td>
						</tr>
						<?php
					}
					?>
				</tbody>
				<tfoot>
					<tr>

					</tr>
				</tfoot>
			</table>
		</form>
	</div>
	<?php
}
