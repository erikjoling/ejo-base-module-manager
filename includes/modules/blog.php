<?php
if ( ! EJO_Base_Module::is_active('blog') ) {

    /* Remove posts menu */
    add_action( 'admin_menu', function() {

        //* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return;

        //* Try to remove posts-section from menu
        foreach ($GLOBALS['menu'] as $index => $menu_item) {

            //* edit.php represents posts menu
            if ($menu_item[2] == 'edit.php' ) {

                // Unset top level menu
                unset( $GLOBALS['menu'][$index], $GLOBALS['submenu'][ 'edit.php' ] );
                break;
            }
        }

    }, 99);

    //* Remove new-post from admin-bar
    add_action( 'admin_bar_menu', function($wp_admin_bar) {

        //* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return;

        $wp_admin_bar->remove_node('new-post');      // Remove the new-post link

    }, 99);

    //* Restrict access to posts screen (edit-posts, categories, tags, new-post)
    add_action( 'current_screen', function($current_screen) {

        //* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return;

        /**
         * Disallow access to post screens
         *
         * Fixes Bug: When saving taxonomy-term of a custom-post-type the $current_screen object will have `post` as post-type
         * Solution: Only disallow when taxonomy is default (post_tag/category) or false
         */
        if ( $current_screen->post_type == 'post' && ($current_screen->taxonomy == 'post_tag' || $current_screen->taxonomy == 'category' || $current_screen->taxonomy == false) ) {
            wp_die( __( 'You are not allowed to access the posts section. Contact your developer if this doesn\'t seem right.' ) );
        }
    });

    //* Remove post-related dashboard widgets
    add_action('admin_init', function() {

        remove_meta_box( 'dashboard_activity', 'dashboard', 'normal');
    });

    //* Remove widget 
    add_filter( 'ejo_base_unregister_widgets', function($widgets_to_unregister) {

        //* Manipulations do not count for admin users
        if (current_user_can('manage_options'))
            return $widgets_to_unregister;

        $widgets_to_unregister[] = 'WP_Widget_Recent_Posts';

        return $widgets_to_unregister;
    });

    //* Disable caps
    // add_filter( 'ejo_client_blog_enabled', function() {
    //     return false;                
    // });
}
