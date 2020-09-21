<?php 

if (!defined( 'ABSPATH')) exit;

/**
* 
*/
class Sca_Form_Details  
{
    private $form_id;
    private $form_post_id;


    public function __construct()
    {   
       $this->form_post_id = esc_sql( $_GET['fid'] );
       $this->form_id = esc_sql( $_GET['ufid'] );
       
       $this->form_details_page();
    }

    public function form_details_page(){
        global $wpdb;
        $table_name = $wpdb->prefix.'sca_forms';
        
        if ( is_numeric($this->form_post_id) && is_numeric($this->form_id) ) {  
            $qry = "SELECT * FROM $table_name AS f 
                        WHERE f.form_post_id = $this->form_post_id 
                        AND f.form_id = $this->form_id 
                        LIMIT 1";
           $results    = $wpdb->get_results( $qry, OBJECT );
        }

        if ( empty($results) ) {
            if ( is_numeric($this->form_post_id) && is_numeric($this->form_id) ) {

               $results    = $wpdb->get_results( "SELECT * FROM $table_name WHERE form_post_id = $this->form_post_id AND form_id = $this->form_id LIMIT 1", OBJECT );
            }

            if ( empty($results) ) {
                wp_die( $message = 'Not valid contact form' );
            }
        }
        ?>
        <div class="wrap">
            <div id="welcome-panel" class="welcome-panel">
                <?php if(isset($_GET['paged'])) { ?>
                    <a href="<?php echo admin_url('admin.php?page=sca-list.php&fid='.$_GET['fid']).'&paged='.$_GET['paged']; ?>">Back</a>
                <?php } else { ?>
                    <a href="<?php echo admin_url('admin.php?page=sca-list.php&fid='.$_GET['fid']); ?>">Back</a>
                <?php } ?>
                <div class="welcome-panel-content">
                    <div class="welcome-panel-column-container">
                        <h3><?php echo get_the_title( $this->form_post_id ); ?></h3>
                        <p></span><?php echo $results[0]->form_date; ?></p>
                        <?php $form_data  = unserialize( $results[0]->form_value );

                        foreach ($form_data as $key => $data):

                            if ( is_array($data) ) {

                                    $key_val = str_replace('your-', '', $key); 
                                    $key_val = ucfirst( $key_val );
                                    $arr_str_data =  implode(', ',$data);
                                    echo '<p><b>'.$key_val.'</b>: '. $arr_str_data .'</p>';

                                }else{

                                    $key_val = str_replace('your-', '', $key); 
                                    $key_val = ucfirst( $key_val );
                                    echo '<p><b>'.$key_val.'</b>: '.$data.'</p>';
                                }

                        endforeach;
                    ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }  

}