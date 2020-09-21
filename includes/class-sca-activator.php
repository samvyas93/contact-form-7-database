<?php

/**
 * Fired during plugin activation
 *
 * @link       http://test.com
 * @since      1.0.0
 *
 * @package    Sca
 * @subpackage Sca/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Sca
 * @subpackage Sca/includes
 * @author     Samir Vyas <samir@cmsminds.com>
 */
class Sca_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;
	    $table_name = $wpdb->prefix.'sca_forms';

	    if( $wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name ) {

	        $charset_collate = $wpdb->get_charset_collate();

	        $sql = "CREATE TABLE $table_name (
	            form_id bigint(20) NOT NULL AUTO_INCREMENT,
	            form_post_id bigint(20) NOT NULL,
	            form_value longtext NOT NULL,
	            form_date datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
	            PRIMARY KEY  (form_id)
	        ) $charset_collate;";

	        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	        dbDelta( $sql );
	    }
	}

}
