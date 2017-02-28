<?php

/**
 * Blog Comments
 */
if ( ! EJO_Base_Module::is_active('blog-comments') ) {

	/* Remove comments menu */
    add_action( 'admin_menu', function() {

        //* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return;

        //* Loop through menu array
        foreach ($GLOBALS['menu'] as $index => $menu_item) {

	        //* edit-comments.php represents comments menu
	        if ($menu_item[2] == 'edit-comments.php' ) {

				// Unset top level menu
				unset( $GLOBALS['menu'][$index], $GLOBALS['submenu'][ 'edit-comments.php' ] );
				break;
			}
		}

    }, 1);

	//* Remove comments from admin-bar
	add_action( 'admin_bar_menu', function($wp_admin_bar) {

		//* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return;

        $wp_admin_bar->remove_node('comments');      // Remove the comments link

    }, 99);

    //* Restrict access to comments screen
    add_action( 'current_screen', function($current_screen) {

        //* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return;

        if ( 'edit-comments' == $current_screen->base ) {
            wp_die( __( 'You are not allowed to access the comments section. Contact your developer if this doesn\'t seem right.' ) );
        }
    });

    //* Remove comment-related dashboard widgets
    add_action('admin_init', function() {

        remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
    });

    //* Remove widget 
    add_filter( 'ejo_base_unregister_widgets', function($widgets_to_unregister) {

    	//* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return $widgets_to_unregister;

		$widgets_to_unregister[] = 'WP_Widget_Recent_Comments';

        return $widgets_to_unregister;
    });
}