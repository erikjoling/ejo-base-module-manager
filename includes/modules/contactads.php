<?php

/**
 * EJO Contactadvertenties
 */
if ( ! EJO_Base_Module::is_active('contactads') ) {

    //* Disable caps
    add_filter( 'ejo_client_ejo-contactadvertenties_enabled', function() {
        return false;                
    });

    //* Remove widget 
    add_filter( 'ejo_base_unregister_widgets', function($widgets_to_unregister) {

        if (! current_user_can( 'manage_options' ) )
            $widgets_to_unregister[] = 'EJO_Contactads_Widget';

        return $widgets_to_unregister;
    });
}