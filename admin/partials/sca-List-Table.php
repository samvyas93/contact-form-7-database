<?php
// WP_List_Table is not loaded automatically so we need to load it in our application
if( ! class_exists( 'WP_List_Table' ) ) {
    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Sca_List_Table extends WP_List_Table
{
    /**
     * Prepare the items for the table to process
     *
     * @return Void
     */
    public function prepare_items()
    {   

        global $wpdb;

        $table_name  = $wpdb->prefix.'sca_forms';
        $columns     = $this->get_columns();
        $hidden      = $this->get_hidden_columns();
        $data        = $this->table_data();
        $perPage     = 10;
        $currentPage = $this->get_pagenum(); 
        $count_forms = wp_count_posts('wpcf7_contact_form');
        $totalItems  = $count_forms->publish;


        $this->set_pagination_args( array(
            'total_items' => $totalItems,
            'per_page'    => $perPage
        ) );

        $this->_column_headers = array($columns, $hidden );
        $this->items = $data;
    }
    /**
     * Override the parent columns method. Defines the columns to use in your listing table
     *
     * @return Array
     */
    public function get_columns()
    {


        $columns = array(
            'name' => 'Name',
            'noofentry'=> 'No of Entries'
        );

        return $columns;
    }
    /**
     * Define which columns are hidden
     *
     * @return Array
     */
    public function get_hidden_columns()
    {
        return array();
    }
  
    /**
     * Get the table data
     *
     * @return Array
     */
    private function table_data()
    {   
        global $wpdb;

        $data        = array();
        $table_name  = $wpdb->prefix.'sca_forms';
        $page         = $this->get_pagenum();
        $page         = $page - 1;
        $start        = $page * 10;
        
        $args = array(
            'post_type'=> 'wpcf7_contact_form',
            'order'    => 'ASC',
            'posts_per_page' => 10,
            'offset' => $start
        );              

        $the_query = new WP_Query( $args );

        while ( $the_query->have_posts() ) : $the_query->the_post();
            $form_post_id = get_the_id();
            $totalItems   = $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE form_post_id = $form_post_id"); 
            $title = get_the_title();
            $link  = "<a class='row-title' href=admin.php?page=sca-list.php&fid=$form_post_id>%s</a>";
            $data_value['name']  = sprintf( $link, $title );
            $data_value['noofentry'] = sprintf( $link, $totalItems );
            $data[] = $data_value;
        endwhile;
    
        return $data;
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
}