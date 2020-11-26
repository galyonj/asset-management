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

$_coe = coe_am_populate_constants();

?>

<form action="" method="post">
	<div class="postbox-container">
		<div class="poststuff">
			<div class="postbox">
				<div class="postbox-header">
					<h2 class="hndle ui-sortable-handle">
						<span><?php esc_html_e( 'Basic Settings', $_coe['text'] ); ?></span>
					</h2>
				</div>
			</div>
		</div>
	</div>
</form>
