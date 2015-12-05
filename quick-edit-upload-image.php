<?php
/*
Plugin Name: Quick Edit Upload Image
Description: Upload featured image through quick edit post/page
Plugin URI: http://www.aguspriyanto.net/
Author: Agus Priyanto
Author URI: http://ww.aguspriyanto.net/
Version: 1.0
License: GPL2
*/


/* Agus2 edit disini juga */
/* Coba ganti user beneran asheu2 ga */

/* Test by agus1  cikan*/
/* nyoba2 dulu deh by agus1 */

/*

    Copyright (C) 2014  Agus Priyanto  asheu321@gmail.com

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
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

// Add Settings page
add_action( 'admin_menu', 'qeui_register_setting_page' );
function qeui_register_setting_page() {
    add_options_page( 'QE Upload Image', 'QE Upload Image', 'manage_options', 'qeui_settings', 'qeui_settings_callback');
}

function qeui_settings_callback() {

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
                'ajax_url' => admin_url('admin-ajax.php')
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

// Add settings link on plugin page
add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'qeui_action_links' );

function qeui_action_links( $links ) {
   $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=qeui_settings') .'">Settings</a>';
   return $links;
}

/*
Testing using trello card id
*/