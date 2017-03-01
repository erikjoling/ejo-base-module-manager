<?php

/**
 * EJO Simple Testimonials
 */
if ( ! EJO_Base_Module::is_active('testimonials') ) {

    //* Disable options
    add_filter( 'ejo_simple_testimonials_cap', function() {
        return 'manage_options';                
    });

    //* Remove widget 
    add_action( 'widgets_init', function() {

        if (! current_user_can( 'manage_options' ) )
            unregister_widget( 'EJO_Simple_Testimonials_Widget' );
    }, 11);
}