<?php

/**
 * Class to eextend
 */
class EJO_Base_Module
{
    /* Only instantiate once */
    public static function instance() 
    {
        if ( !self::$_instance )
            self::$_instance = new self;
        return self::$_instance;
    }

    //* No cloning
    protected function __clone() {}

    /* Plugin setup. */
    protected function __construct() {}

    /**
     * Get Module
     */
    public static function get_module( $id )
    {
        $modules = EJO_Base::$modules;

        //* Return false if module id is not found
        if ( empty($modules[$id]) )
            return false;

        return $modules[$id];
    }

    /**
     * Get name
     */
    public static function get_name( $id )
    {
        $module = self::get_module( $id );

        return $module['name'];
    }

    /**
     * Get description
     */
    public static function get_description( $id )
    {
        $module = self::get_module( $id );

        if ( empty($module['description']) )
            return array();

        return $module['description'];
    }

    /**
     * Get dependancies
     */
    public static function get_dependancies( $id )
    {
        $module = self::get_module( $id );

        if ( empty($module['dependancies']) )
            return array();

        return $module['dependancies'];
    }


    //* Check if module has theme support
    public static function has_theme_support( $id ) 
    {
        //* Return true if theme supports the module
        if ( current_theme_supports( 'ejo-base-modules', $id ) )
            return true;

        return false;
    }

    /**
     * Check if module exists
     */
    public static function exists( $id ) 
    {
        return ( self::get_module( $id ) != false );
    }

    /**
     * Check if module is available
     */
    public static function is_available( $id ) 
    {
        //* Check if current theme supports the module
        if ( self::exists($id) && self::has_theme_support($id) && self::has_no_missing_dependancies($id) ) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     * Check if module is stored as active in database
     * 
     * is_active function checks wether module is_active and available
     */
    public static function is_activated( $id, $active_modules = null ) 
    {
        if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );
        
        //* Check if module is active in options
        if ( in_array($id, $active_modules) )
            return true;

        return false;
    }

    /**
     * Check if module is stored as active in database and if it is available
     */
    public static function is_active( $id, $active_modules = null ) 
    {
        if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );
        
        //* Check if module is active in options
        if ( self::is_activated($id, $active_modules) && self::is_available($id) )
            return true;

        return false;
    }

    /**
     * Wrapper for is_active
     */
    public static function is_inactive( $id, $active_modules = null ) 
    {
        if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );
        
        return ! self::is_active( $id, $active_modules );
    }


    public static function has_missing_dependancies( $id ) 
    {
        //* Get missing dependancies
        $missing_dependancies = self::get_missing_dependancies($id);

        //* If no missing dependancies, return false
        if ( empty($missing_dependancies) )
            return false;

        //* Module has missing dependancies
        return true;
    }

    public static function has_no_missing_dependancies( $id ) 
    {
        return ! self::has_missing_dependancies($id);
    }

    public static function check_dependancies( $dependancies )
    {
        $missing_dependancies = array();

        //* Check if dependancies are fulfilled
        foreach ($dependancies as $dependancy) {

            if ($dependancy['type'] == 'ejo-base-module') {

                $dependant_module = self::get_module($dependancy['id']);

                if ( ! self::is_active($dependant_module['id']) ) {
                    $missing_dependancies[] = $dependant_module['name'];
                }
            }
            elseif ( $dependancy['type'] == 'plugin' ) {

                $dependant_plugin = $dependancy;

                if ( ! class_exists( $dependant_plugin['class'] ) ) {
                    $missing_dependancies[] = $dependant_plugin['name'];
                }
            }
        }

        return $missing_dependancies;
    }

    public static function get_missing_dependancies( $id )
    {
        $dependancies = self::get_dependancies($id);

        return self::check_dependancies( $dependancies );
    }

    
    public static function show_dependancies( $id )
    {
        $dependancies = self::get_dependancies( $id );
           
        if ( empty($dependancies) )
            return;

        $output = '';

        // $output = __( 'Dependancies: ');

        foreach ($dependancies as $dependancy) {

            if ($dependancy['type'] == 'ejo-base-module') {

                $dependant_module = self::get_module($dependancy['id']);

                if ( self::is_active($dependant_module['id']) ) {
                    $output .= '<span class="dependancy-good">' . $dependant_module['name'] . ': ' . __( 'active') . ' |</span> ';
                }
                else {
                    $output .= $dependant_module['name'] . ': ' . __( 'inactive') . ' | ';
                }

            }
            elseif ( $dependancy['type'] == 'plugin' ) {

                $dependant_plugin = $dependancy;

                if ( class_exists( $dependant_plugin['class'] ) ) {
                    $output .= '<span class="dependancy-good">' . $dependant_plugin['name'] . ': ' . __( 'active') . ' |</span> ';
                }
                else {
                    $output .= $dependant_plugin['name'] . ': ' . __( 'inactive') . ' | ';
                }
            }
        }

        //* Remove ` |</span> ` and ` | ` from end of output
        $output = preg_replace('/ \|(<\/span>)? $/', '', $output);

        if ( ! self::has_theme_support($id) || self::has_no_missing_dependancies($id) ){
            $output = '<div class="dependancies-hidden">' . $output . '</div>';
        }

        echo $output;
    }

    /**
     * Show why module is not available
     */
    public static function print_why_not_available( $id ) 
    {
        if ( ! self::exists($id) ) {
            echo __( 'Module doesn\'t exist', EJO_Base::$slug );
        }
        elseif ( ! self::has_theme_support($id) ) {
            echo __( 'No theme-support', EJO_Base::$slug );
        }
        elseif ( self::has_missing_dependancies($id) ) {
            echo __( 'Missing dependancies', EJO_Base::$slug );
        }
        else {
            echo __( 'Module not available', EJO_Base::$slug );
        }
    }

    public static function activate( $id, $active_modules = null )
    {
        if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );

        //* Store module if it doesn't already exist
        if ( !self::is_activated( $id, $active_modules ) ) {

            if ( self::is_available($id) ) {

                $active_modules[] = $id;

                //* Store altered modules
                update_option( 'ejo_base_active_modules', $active_modules );

                //* Allow code to be hooked here
                do_action( 'ejo_base_module_activation', $id );

                return true;
            }
        }

        return false;
    }

    public static function deactivate( $id, $active_modules = null )
    {
        if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );

        if ( self::is_activated( $id, $active_modules ) ) {

            //* Make sure all values occur once
            $active_modules = array_unique($active_modules);

            //* Remove module from modules
            $active_modules = array_unset_by_value( $id, $active_modules );

            //* Store altered modules
            update_option( 'ejo_base_active_modules', $active_modules );

            //* Allow code to be hooked here
            do_action( 'ejo_base_module_deactivation', $id );

            //* Check for dependancy
            self::check_activated_modules( $active_modules );

            return true;
        }

        return false;
    }

    /** 
     * Check currently activated modules 
     *  
     * Deactivate modules which shouldn't be activated (ie. missing dependancies)
     */
    public static function check_activated_modules( $active_modules = null )
    {
        if ( ! $active_modules )
            $active_modules = get_option( 'ejo_base_active_modules', array() );

        foreach ($active_modules as $id) {

            if ( ! self::is_available($id) ) {
                self::deactivate( $id, $active_modules );
            }
        }
    }
}