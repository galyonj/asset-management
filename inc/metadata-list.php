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
	$_coe = coe_am_constants();

	?>
	<div class="wrap">
		<h1 class="wp-heading-inline">
			<?php esc_html_e( 'Metadata', $_coe['text'] ); ?>
			<a href="" class="page-title-action">Add New</a>
		</h1>
		<hr class="wp-header-end">
	</div>
	<?php
}
