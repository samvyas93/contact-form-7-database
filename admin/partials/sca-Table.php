<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/**
 * Create a new table class that will extend the WP_List_Table
 */
class SCA_Table extends WP_List_Table
{   
    private $form_post_id;

    public function __construct() {

        parent::__construct(
            array(
                'singular' => 'contact_form',
                'plural'   => 'contact_forms',
                'ajax'     => false
            )
        );

    }

    public function prepare_items()
    {
        $this->form_post_id =  (int) $_GET['fid'];
        $search = empty( $_REQUEST['s'] ) ? false :  esc_sql( $_POST['s'] );
        echo $this->search;
        $form_post_id  = $this->form_post_id;

        global $wpdb;

        $this->process_bulk_action();

        $table_name  = $wpdb->prefix.'sca_forms';
        $columns     = $this->get_columns();
        $hidden      = $this->get_hidden_columns();
        $sortable    = $this->get_sortable_columns();
        $data        = $this->table_data();
        
        //usort( $data, array( &$this, 'sort_data' ) );

        $perPage     = 10;
        $currentPage = $this->get_pagenum();
        if ( ! empty($search) ) {
            
            $totalItems  = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE form_value LIKE '%$search%'");
         }else{

            $totalItems  = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE form_post_id = '$form_post_id'"); 
        }

        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );
        $this->_column_headers = array($columns, $hidden ,$sortable);
        $this->items = $data;
    }
    /**
    * Override the parent columns method. Defines the columns to use in your listing table
    *
    * @return Array
    */
    public function get_columns()
    {
        $form_post_id  = $this->form_post_id;

        global $wpdb;
        $table_name = $wpdb->prefix.'sca_forms';

        $results    = $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $form_post_id LIMIT 1", OBJECT );

        $first_row  = isset($results[0]) ? unserialize( $results[0]->form_value ): 0 ;
        $columns    = array();

        if( !empty($first_row) ){
            $columns['form_id'] = $results[0]->form_id;
            $columns['cb']      = '<input type="checkbox" />';
            foreach ($first_row as $key => $value) {

                if ( $key == '_wpcf7_container_post' ) continue;

                $key_val       = str_replace('your-', '', $key); 
                $columns[$key] = ucfirst( $key_val );
                
                if ( sizeof($columns) > 4) break;
            } 
            $columns['form_date'] = 'Date';
        }


        return $columns;
    }
    /**
     * Define check box for bulk action (each row)
     * @param  $item
     * @return checkbox
     */
    public function column_cb($item){
        return sprintf(
             '<input type="checkbox" name="%1$s[]" value="%2$s" />',
             $this->_args['singular'],  
             $item['form_id']                 
        );
    }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return  array('form_id');
    }
    /**
     * Define the sortable columns
     *
     * @return Array
     */
    public function get_sortable_columns()
    {
       return array('form_date' => array('form_date', false));
    }
    /**
     * Define bulk action 
     * @return Array
     */
    public function get_bulk_actions() {

        return array(
            'delete' => 'Delete'
        );

    }
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {
        $data = array();
        global $wpdb;
        $search       = empty( $_REQUEST['s'] ) ? false :  esc_sql( $_POST['s'] );
        $table_name   = $wpdb->prefix.'sca_forms';
        $page         = $this->get_pagenum();
        $page         = $page - 1;
        $start        = $page * 10;
        $orderby      = (isset($_GET['orderby']) && !empty($_GET['orderby']))?$_GET['orderby']:'form_date';
        $order      = (isset($_GET['order']) && !empty($_GET['order']))?$_GET['order']:'desc';
        $form_post_id = $this->form_post_id;
        
        if ( ! empty($search) ) {
            if(!empty($order) && !empty($orderby))
                $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE  form_value LIKE '%$search%' ORDER BY $orderby $order LIMIT $start,10", OBJECT ); 
            else
                $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE  form_value LIKE '%$search%' LIMIT $start,10", OBJECT );
        }else{
            if(!empty($order) && !empty($orderby))
                $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $form_post_id ORDER BY $orderby $order LIMIT $start,10", OBJECT );
            else
                $results = $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $form_post_id LIMIT $start,10", OBJECT );
        }
        
        foreach ( $results as $result ) {
        
            $form_value = unserialize( $result->form_value );
            if(isset($_GET['paged'])) {
                $link  = "<a href=admin.php?page=sca-list.php&fid=%s&ufid=%s&paged=%s>%s</a>";
            }
            else{
                $link  = "<a href=admin.php?page=sca-list.php&fid=%s&ufid=%s>%s</a>";
            }

                
            
            $fid   = $result->form_post_id;
            $form_values['form_id'] = $result->form_id;
           
            foreach ($form_value as $k => $value) {

                $ktmp = str_replace('wpcmscf7_file', '', $k);

                $can_foreach = is_array($value) || is_object($value);
               
                if ( $can_foreach ) {

                    foreach ($value as $k_val => $val):
                        
                        $form_values[$ktmp] = ( strlen($val) > 150 ) ? substr($val, 0, 150).'...': $val;
                        if(isset($_GET['paged']))
                            $form_values[$ktmp] = sprintf($link, $fid, $result->form_id, $_GET['paged'], $form_values[$ktmp]);
                        else
                            $form_values[$ktmp] = sprintf($link, $fid, $result->form_id, $form_values[$ktmp]);
                    
                    endforeach;
                }else{
                   $form_values[$ktmp] = ( strlen($value) > 150 ) ? substr($value, 0, 150).'...': $value;
                    if(isset($_GET['paged']))
                        $form_values[$ktmp] = sprintf($link, $fid, $result->form_id, $_GET['paged'], $form_values[$ktmp]);
                    else
                        $form_values[$ktmp] = sprintf($link, $fid, $result->form_id, $form_values[$ktmp]);
                }
                
            }
            if(isset($_GET['paged']))
                $form_values['form_date'] = sprintf($link, $fid, $result->form_id, $_GET['paged'], $result->form_date );
            else
                $form_values['form_date'] = sprintf($link, $fid, $result->form_id, $result->form_date );

            $data[] = $form_values;
        }
        
        return $data;
    }
    /**
     * Define bulk action 
     * 
     */
    public function process_bulk_action(){
        
        global $wpdb;
        $table_name = $wpdb->prefix.'sca_forms';
        $action = $this->current_action(); 

        if ( isset( $_POST['_wpnonce'] ) && ! empty( $_POST['_wpnonce'] ) ) {

            $nonce        = filter_input( INPUT_POST, '_wpnonce', FILTER_SANITIZE_STRING );
            $nonce_action = 'bulk-' . $this->_args['plural'];

            if ( !wp_verify_nonce( $nonce, $nonce_action ) ){

                wp_die( 'Not valid..!!' );
            }
        } 
 
        if( 'delete' === $action ) {
 
            $form_ids = esc_sql( $_POST['contact_form'] );

            foreach ($form_ids as $form_id):
               
                $results       = $wpdb->get_results( "SELECT * FROM $table_name WHERE form_id = $form_id LIMIT 1", OBJECT );
                $result_value  = $results[0]->form_value;
                $result_values = unserialize($result_value);
                
                $wpdb->delete( 
                    $table_name , 
                    array( 'form_id' => $form_id ), 
                    array( '%d' ) 
                );  
            endforeach;
        
        }
    }
    /**
     * Define what data to show on each column of the table
     *
     * @param  Array $item        Data
     * @param  String $column_name - Current column name
     *
     * @return Mixed
     */
    public function column_default( $item, $column_name )
    {  
        return $item[ $column_name ];
       
    }
    /**
     * Allows you to sort the data by the variables set in the $_GET
     *
     * @return Mixed
     */
    private function sort_data( $a, $b )
    {
        // Set defaults
        $orderby = 'form_date';
        $order = 'asc';
        // If orderby is set, use this as the sort column
        if(!empty($_GET['orderby']))
        {
            $orderby = $_GET['orderby'];
        }
        // If order is set use this as the order
        if(!empty($_GET['order']))
        {
            $order = $_GET['order'];
        }
        $result = strcmp( $a[$orderby], $b[$orderby] );
        if($order === 'asc')
        {
            return $result;
        }
        return -$result;
    }
    /**
     * Display the bulk actions dropdown.
     *
     * @since 1.0.0
     * @access protected
     *
     * @param string $which The location of the bulk actions: 'top' or 'bottom'.
     *                      This is designated as optional for backward compatibility.
     */
    protected function bulk_actions( $which = '' ) {
        if ( is_null( $this->_actions ) ) {
            $this->_actions = $this->get_bulk_actions();
            
            $this->_actions = apply_filters( "bulk_actions-{$this->screen->id}", $this->_actions );
            $two = '';
        } else {
            $two = '2';
        }
 
        if ( empty( $this->_actions ) )
            return;
 
        echo '<label for="bulk-action-selector-' . esc_attr( $which ) . '" class="screen-reader-text">' . __( 'Select bulk action' ) . '</label>';
        echo '<select name="action' . $two . '" id="bulk-action-selector-' . esc_attr( $which ) . "\">\n";
        echo '<option value="-1">' . __( 'Bulk Actions' ) . "</option>\n";
 
        foreach ( $this->_actions as $name => $title ) {
            $class = 'edit' === $name ? ' class="hide-if-no-js"' : '';
 
            echo "\t" . '<option value="' . $name . '"' . $class . '>' . $title . "</option>\n";
        }
 
        echo "</select>\n";
 
        submit_button( __( 'Apply' ), 'action', '', false, array( 'id' => "doaction$two" ) );
        echo "\n";
    }
}