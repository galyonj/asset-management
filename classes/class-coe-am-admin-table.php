<?php
/**
 * Table view for metadata management
 *
 * @since 1.1.0
 * @author John Galyon
 * @package Coe_Am
 * @subpackage Coe_Am/class-coe-am-admin-table
 */

if ( ! defined( ABSPATH ) ) {
	die( 'That\'s illegal.' );
}

/**
 * Instantiate the WP_List_Table class so that we can use it
 */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Class Coe_Am_List_Table
 */
class Coe_Am_Table  extends WP_List_Table {


}
