<?php
/*
Plugin Name: Quick Edit Upload Image
Description: Quick upload featured images through post list table
Plugin URI: http://www.aguspriyanto.net/
Author: Agus Priyanto
Author URI: http://ww.aguspriyanto.net/
Version: 1.0
License: GPL2
*/

function qeui_register_setting() {
    register_setting( 'qeui_options', 'qeui_place', 'post' );     
}
add_action( 'admin_init', 'qeui_register_setting' );


// Get current post type
function get_current_post_type() {
    global $post, $typenow, $current_screen;
    //we have a post so we can just get the post type from that
    
    if ( $post && $post->post_type ) {
        return $post->post_type;
    }
        
    //check the global $typenow - set in admin.php
    elseif( $typenow ) {
       return $typenow;
    }
        
    //check the global $current_screen object - set in screen.php
    elseif( $current_screen && $current_screen->post_type ) {
        return $current_screen->post_type;
    }
        
    //lastly check the post_type querystring
    elseif( isset( $_REQUEST['post_type'] ) ) {
        return sanitize_key( $_REQUEST['post_type'] );
    }
        
    //we do not know the post type!
    else {
        return null;
    }
    
}


add_filter('manage_post_posts_columns', 'qeui_add_post_columns');
function qeui_add_post_columns($columns) {
    $columns['featured_image'] = 'Featured Image';
    return $columns;
}

// Add to our admin_init function
add_action('manage_post_posts_custom_column', 'qeui_render_post_columns', 10, 2);
function qeui_render_post_columns($column_name, $id) {
    switch ($column_name) {
    case 'featured_image':
        // show widget set
        $thumb_id = get_post_meta( $id, '_thumbnail_id', true);

        if( has_post_thumbnail($id) ) {
        	echo '<span class="pid-'.$id.'" id="'.$thumb_id.'">';
        	the_post_thumbnail('thumbnail');
        	echo '<br/><a class="qeui-thumb-remove button-secondary" data-id="'.$id.'" href="'.$thumb_id.'"><i class="qeui-ajax-loading" style="display:none"><img src="'.plugins_url('images/ajax-loader.gif', __FILE__).'">  </i>remove</a></span>';
        }else{
        	echo '<a class="button-secondary qeui-upload-button" href="#" id="qeui-button-'.$id.'" data-id="'.$id.'"><i class="qeui-ajax-loading" style="display:none"><img src="'.plugins_url('images/ajax-loader.gif', __FILE__).'">  </i>Upload Image</a>';
        }
                      
        break;
    }
}

add_action('admin_enqueue_scripts', 'qeui_admin_script');
function qeui_admin_script() {
    global $post_type;

    if(is_array($post_type)){
        $post_type = $post_type[1];
    }

   // if('listing' == $post_type){
        wp_enqueue_media();
        wp_register_script( 'qeui_main', plugins_url('js/qeui_main.js', __FILE__) );
        wp_enqueue_script( 'qeui_main' );
        wp_localize_script( 'qeui_main', 'qeui_obj', 
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'plugin_url' => plugins_url( '', __FILE__ )
            )
        );    
    //}
	
}

add_action('wp_ajax_qeui_delete_thumbnail', 'qeui_delete_thumbnail_callback');
function qeui_delete_thumbnail_callback() {
	$thumb_id = $_POST['thumb_id'];
	$post_id = $_POST['post_id'];

	update_post_meta($post_id, '_thumbnail_id', '');
	echo $post_id;
	die();
}

add_action('wp_ajax_qeui_add_thumbnail', 'qeui_add_thumbnail_callback');
function qeui_add_thumbnail_callback() {
    $thumb_id = $_POST['thumb_id'];
    $post_id = $_POST['post_id'];
    $thumbnail = wp_get_attachment_thumb_url($thumb_id);
    update_post_meta($post_id, '_thumbnail_id', $thumb_id);
    echo json_encode(array('post_id' => $post_id, 'img_url' => $thumbnail, 'thumb_id' => $thumb_id));
    die();
}

add_action('admin_head', 'qeui_admin_head');
function qeui_admin_head() {
?>
    <style type="text/css">     
        i.qeui-ajax-loading {
            display: inline-block;
            margin-top: 3px;
            padding-right: 8px;
            vertical-align: top;
        }

        html .wp-core-ui .qeui-thumb-remove.button-secondary {
            background-color: #ec6464;
            border: 1px solid #bd3535;
            box-shadow: 0 1px 0 #ff9797 inset;
            color: #fff;
        }
    </style>
<?php
}
