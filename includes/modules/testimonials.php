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
    add_filter( 'ejo_base_unregister_widgets', function($widgets_to_unregister) {

        if (! current_user_can( 'manage_options' ) )
            $widgets_to_unregister[] = 'EJO_Simple_Testimonials_Widget';

        return $widgets_to_unregister;
    });
}