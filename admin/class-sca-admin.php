<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://test.com
 * @since      1.0.0
 *
 * @package    Sca
 * @subpackage Sca/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Sca
 * @subpackage Sca/admin
 * @author     Samir Vyas <samir@cmsminds.com>
 */
class Sca_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sca_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sca_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/sca-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Sca_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Sca_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/sca-admin.js', array( 'jquery' ), $this->version, false );

	}

	function sca_before_send_mail( $form_tag )
	{
		global $wpdb;
	    $table_name    = $wpdb->prefix.'sca_forms';
	    
	    $time_now      = time();

	    $form = WPCF7_Submission::get_instance();

	    if ( $form ) {

	    	$black_list   = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag',
        '_wpcf7_is_ajax_call','cfdb7_name','_wpcf7_container_post','_wpcf7cf_hidden_group_fields',
        '_wpcf7cf_hidden_groups', '_wpcf7cf_visible_groups', '_wpcf7cf_options','g-recaptcha-response');

	        $data           = $form->get_posted_data();

	        $form_data   = array();
	        /*echo "<pre>";
	        print_r($form_data);
	        wp_die();*/
	        foreach ($data as $key => $d) {
	            if ( !in_array($key, $black_list ) ) {
	                
	                $tmpD = $d;
	                
	                if ( ! is_array($d) ){

	                    $bl   = array('\"',"\'",'/','\\');
	                    $wl   = array('&quot;','&#039;','&#047;', '&#092;');

	                    $tmpD = str_replace($bl, $wl, $tmpD );
	                } 

	                $form_data[$key] = $tmpD; 
	            }
	        }

	        /* wpcmscf7 before save data. */ 
	        do_action( 'sca_before_save_data', $form_data );

	        $form_post_id = $form_tag->id();
	        $form_value   = serialize( $form_data );
	        $form_date    = current_time('Y-m-d H:i:s');
	 
	        $wpdb->insert( $table_name, array( 
	            'form_post_id' => $form_post_id,
	            'form_value'   => $form_value,
	            'form_date'    => $form_date
	        ) );

	        /* wpcmscf7 after save data */ 
	        $this->last_insert_id = $wpdb->insert_id;
	    }
		
	}

	function sca_admin_list_table_page()
	{	
    	add_submenu_page( 'wpcf7', 'Contact Forms', 'Form List', 'manage_options', 'sca-list.php', array($this, 'sca_list_table') );
	}

	function sca_list_table()
	{
		require_once plugin_dir_path(__FILE__) . 'partials/sca-admin-subpage.php';
        require_once plugin_dir_path(__FILE__) . 'partials/sca-admin-form-details.php';

        $fid  = empty($_GET['fid']) ? 0 : (int) $_GET['fid'];
        $ufid = empty($_GET['ufid']) ? 0 : (int) $_GET['ufid'];

        if ( !empty($fid) && empty($_GET['ufid']) ) {

            new Sca_Sub_Page();
            return;
        }

        if( !empty($ufid) && !empty($fid) ){

            new Sca_Form_Details();
            return;
        }

        $file = require_once plugin_dir_path(__FILE__) . 'partials/sca-List-Table.php';

        if($file) {
	        $ListEntryTable = new Sca_List_Table();
	        $ListEntryTable->prepare_items();
	        ?>
	            <div class="wrap">
	                <div id="icon-users" class="icon32"></div>
	                <h2>Contact Forms List</h2>
	                <?php $ListEntryTable->display(); ?>
	            </div>
	        <?php
	    }
	}
}
