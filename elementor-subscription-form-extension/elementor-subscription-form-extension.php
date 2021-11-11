<?php
/*
	Plugin name: Subscribers - Elementor Extenstion
	Description: This plugin adds Elementor Extenstion.y
	Author: Shafqat Khan
	Author URI: https://linkedin.com/shafqatkhan60
	Version: 1.0.2
*/

//-------Elementor form addon starts here



add_action( 'elementor_pro/init', function() {
	// Here its safe to include our action class file
	include_once( plugin_dir_path(__FILE__) .'/elementor_form_action_addon.php' );

	// Instantiate the action class
	$subscriber_action = new subscriber_Action_After_Submit();

	// Register the action with form widget
	\ElementorPro\Plugin::instance()->modules_manager->get_modules( 'forms' )->add_form_action( $subscriber_action->get_name(), $subscriber_action );
});



/*
* Creating a function to create our CPT
*/
 
function custom_elementor_subscribers_post_type() {
 
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Subscribers', 'Post Type General Name', 'twentytwenty' ),
        'singular_name'       => _x( 'Subscriber', 'Post Type Singular Name', 'twentytwenty' ),
        'menu_name'           => __( 'Subscribers', 'twentytwenty' ),
        'parent_item_colon'   => __( 'Parent Subscriber', 'twentytwenty' ),
        'all_items'           => __( 'All Subscribers', 'twentytwenty' ),
        'view_item'           => __( 'View Subscriber', 'twentytwenty' ),
        'add_new_item'        => __( 'Add New Subscriber', 'twentytwenty' ),
        'add_new'             => __( 'Add New', 'twentytwenty' ),
        'edit_item'           => __( 'Edit Subscriber', 'twentytwenty' ),
        'update_item'         => __( 'Update Subscriber', 'twentytwenty' ),
        'search_items'        => __( 'Search Subscriber', 'twentytwenty' ),
        'not_found'           => __( 'Not Found', 'twentytwenty' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentytwenty' ),
    );
     
// Set other options for Custom Post Type
     
    $args = array(
        'label'               => __( 'ele-subscribers', 'twentytwenty' ),
        'description'         => __( 'Subscribers', 'twentytwenty' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title'),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => false,
        'register_meta_box_cb' => 'ele_subscribers_meta_box',
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_icon'           => 'dashicons-businessperson',
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => true,
        'publicly_queryable'  => false,
        'capability_type'     => 'post',
        'show_in_rest' => false,
       
    );
     
    // Registering your Custom Post Type
    register_post_type( 'ele-subscribers', $args );
 
}
 


/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/
 
add_action( 'init', 'custom_elementor_subscribers_post_type', 0 );


function ele_subscribers_meta_box() {

    add_meta_box(
        'global-notice',
        __( 'Subscriber Information', 'sitepoint' ),
        'ele_subscribers_meta_box_callback',
        'ele-subscribers'
    );
}


function ele_subscribers_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'ele_subscribers_nonce', 'ele_subscribers_nonce' );

    $value = get_post_meta( $post->ID, 'email', true );

    echo '<input style="width:100%" id="email" name="email" value="'.esc_attr( $value ).'"></input>';
}
//add_action( 'add_meta_boxes', 'ele_subscribers_meta_box' );





function save_ele_subscribers_meta_box_data( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['ele_subscribers_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['ele_subscribers_nonce'], 'ele_subscribers_nonce' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['email'] ) ) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['email'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'email', $my_data );
}

add_action( 'save_post', 'save_ele_subscribers_meta_box_data' );





function ele_subscriber_before_post( $content ) {

    global $post;

    // retrieve the global notice for the current post
    $ele_subscriber = esc_attr( get_post_meta( $post->ID, 'email', true ) );

    $notice = "<div class='sp_ele_subscriber'>$ele_subscriber</div>";

    return $notice . $content;

}



// Add the custom columns to the ele-subscribers post type:
add_filter( 'manage_ele-subscribers_posts_columns', 'set_custom_edit_ele_subscribers_columns' );
function set_custom_edit_ele_subscribers_columns($columns) {
    unset( $columns['date'] );
   // $columns['ele-subscribers_author'] = __( 'Email', 'ele-subscribers' );

    return array_merge ( $columns, array ( 
     'ele-subscribers_author' => __ ('Email'),
     'subscription-date' => __('Subscription Date')
   ) );
}

// Add the data to the custom columns for the ele-subscribers post type:
add_action( 'manage_ele-subscribers_posts_custom_column' , 'custom_ele_subscribers_column', 10, 2 );
function custom_ele_subscribers_column( $column, $post_id ) {
    switch ( $column ) {

        case 'ele-subscribers_author' :
            $email = get_post_meta( $post_id , 'email' ,true);
            if ( is_string( $email ) )
                echo '<a href="mailto:'.$email.'">'.$email.'</a>';
            else
                _e( 'Unable to get email', 'ele-subscribers' );
            break;

            case 'subscription-date' :
            $date = get_the_date();
            if ( is_string( $date ) )
                echo $date;
            else
                _e( 'Unable to get date', 'ele-subscribers' );
            break;

        

    }
}


//-------------ELementor form addon ends here

?>
