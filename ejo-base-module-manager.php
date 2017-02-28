<?php
/**
 * Plugin Name:         EJO Base Module Manager
 * Plugin URI:          http://github.com/erikjoling/ejo-base-module-manager
 * Description:         Manages modules for my WordPress base websites
 * Version:             0.1
 * Author:              Erik Joling
 * Author URI:          http://www.ejoweb.nl/
 * Text Domain:         ejo-base-module-manager
 * Domain Path:         /languages
 *
 * GitHub Plugin URI:   https://github.com/erikjoling/ejo-base-module-manager
 * GitHub Branch:       master
 *
 * Minimum PHP version: 5.3.0
 *
 * @package   EJO Base Module Manager
 * @version   0.1.0
 * @since     0.1.0
 * @author    Erik Joling <erik@ejoweb.nl>
 * @copyright Copyright (c) 2015, Erik Joling
 * @link      http://github.com/erikjoling
 */

/**c
 *
 */
final class EJO_Base_Module_Manager 
{
    /* Holds the instance of this class. */
    private static $_instance = null;

    /* Version number of this plugin */
    public static $version = '0.1';

    /* Store the slug of this plugin */
    public static $slug = 'ejo-base-module-manager';

    /* Stores the directory path for this plugin. */
    public static $dir;

    /* Stores the directory URI for this plugin. */
    public static $uri;

    /* Stores the includes directory path for this plugin. */
    public static $inc_dir;

    /* Stores activated modules */
    public static $modules = array();

    /* Only instantiate once */
    public static function init() 
    {
        if ( !self::$_instance )
            self::$_instance = new self;
        return self::$_instance;
    }

    //* No cloning
    private function __clone() {}

    /* Plugin setup. */
    private function __construct() 
    {
        //* Setup common plugin stuff
        self::plugin_setup();

        //* Set modules
        self::set_modules();

        //* Immediatly include helpers
        add_action( 'plugins_loaded', array( 'EJO_Base_Module_Manager', 'module_manager' ) );
    }

    
    /* Defines the directory path and URI for the plugin. */
    public static function plugin_setup() 
    {
        self::$dir = plugin_dir_path( __FILE__ );
        self::$inc_dir = self::$dir . 'includes/';
        self::$uri = plugin_dir_url( __FILE__ );

        /* Load the translation for the plugin */
        load_plugin_textdomain( 'ejo-base-module-manager', false, 'ejo-base-module-manager/languages' );

        /* Include class module */
        require_once( self::$inc_dir . 'class-module.php' );
    }

    /* Add helper functions */
    public static function module_manager() 
    {
        /* Check dependancies */
        if ( ! class_exists('EJO_Base') ) {
            error_log( 'Error: EJO Base is not installed. To use the EJO Base Module Manager you must first install the EJO Base plugin.' );

            return;
        }

        /* Allow array-arguments to be passed for theme-support:ejo-base-modules */
        add_filter( 'current_theme_supports-ejo-base-modules', 'ejo_add_extended_theme_support', 10, 3 );
        
        /* Add EJObase Option page to Wordpress Option menu */
        add_action( 'admin_menu', array( 'EJO_Base_Module_Manager', 'register_menu' ), 1 );

        //* Save Activate/Deactivate actions of options page
        add_action( 'after_setup_theme', array( 'EJO_Base_Module_Manager', 'save_module_activations' ), 98 );

        //* Hook to module activation and deactivation
        add_action( 'ejo_base_module_activation', array( 'EJO_Base_Module_Manager', 'reset_caps_on_module_activation' ) ); 
        add_action( 'ejo_base_module_deactivation', array( 'EJO_Base_Module_Manager', 'reset_caps_on_module_activation' ) );

        //* Check modules after plugin (de)activations
        add_action( 'after_setup_theme', array( 'EJO_Base_Module_Manager', 'check_modules_on_every_plugin_activation' ), 98 );

        //* Code to activate when some modules are (in)active (hook after `save_module_activations`)
        add_action( 'after_setup_theme', array( 'EJO_Base_Module_Manager', 'module_manipulations' ), 99 );
    }

    /**
     * Process the actions of the EJO Base Modules Options Page
     */
    public static function save_module_activations()
    {
        //* Perform action if set
        if ( isset($_GET['action']) && isset($_GET['module']) ) {

            $module_id = esc_attr($_GET['module']);

            if ($_GET['action'] == 'activate') {

                if ( EJO_Base_Module::activate( $module_id ) ) {
                    add_action( 'admin_notices', array( 'EJO_Base_Module_Manager', 'show_activation_message' ) );
                }
            } 

            elseif ($_GET['action'] == 'deactivate') {

                if ( EJO_Base_Module::deactivate( $module_id ) ) {
                    add_action( 'admin_notices', array( 'EJO_Base_Module_Manager', 'show_deactivation_message' ) );
                }
            }
        }
    }

    private static function set_modules()
    {
        EJO_Base_Module_Manager::$modules = array(
            'blog' => array(
                'id'           => 'blog',
                'name'         => __( 'Blog', EJO_Base_Module_Manager::$slug ),
                'description'  => __( 'Overzichtspagina, artikelpagina en widget met laatste artikelen. Voor bloggen of nieuws.', EJO_Base_Module_Manager::$slug ),
                'dependancies' => array(),
            ),
            'blog-comments' => array(
                'id'           => 'blog-comments',
                'name'         => __( 'Blog Comments', EJO_Base_Module_Manager::$slug ),
                'description'  => __( 'Mogelijkheid voor bezoekers om reacties achter te laten onder uw blogartikelen', EJO_Base_Module_Manager::$slug ),
                'dependancies' => array(
                    array(
                        'type' => 'ejo-base-module',
                        'id'   => 'blog',
                    ),
                ),
            ),
            'testimonials' => array(
                'id'           => 'testimonials',
                'name'         => __( 'Testimonials', EJO_Base_Module_Manager::$slug ),
                'description'  => __( 'Overzichtspagina met referenties en widget met laatste referenties', EJO_Base_Module_Manager::$slug ),
                'dependancies' => array(
                    array(
                        'type'  => 'plugin',
                        'name'  => 'EJO Simple Testimonials',
                        'class' => 'EJO_Simple_Testimonials',
                    ),
                ),
            ),
            'portfolio' => array(
                'id'           => 'portfolio',
                'name'         => __( 'Portfolio', EJO_Base_Module_Manager::$slug ),
                'description'  => __( 'Overzichtspagina met projecten en widget met laatste projecten', EJO_Base_Module_Manager::$slug ),
                'dependancies' => array(
                    array(
                        'type'  => 'plugin',
                        'name'  => 'EJO Portfolio',
                        'class' => 'EJO_Portfolio',
                    ),
                ),
            ),
        );
    }

    public static function show_activation_message()
    {
        $class = 'notice updated is-dismissible';
        $message = 'Module <strong>' . __('activated') . '</strong>';

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
    }

    public static function show_deactivation_message()
    {
        $class = 'notice updated is-dismissible';
        $message = 'Module <strong>' . __('deactivated') . '</strong>';

        printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
    }

    /* Register EJObase Options Menu Page */
    public static function register_menu()
    {
        add_submenu_page( 'options-general.php', __('EJO Base Modules'), __('EJO Base Modules'), 'manage_options', self::$slug, array( 'EJO_Base_Module_Manager', 'add_menu_page' ) );
    }

    /* Add EJObase Options Menu Page */
    public static function add_menu_page()
    {
        /* Include theme options page */
        require_once( self::$inc_dir . 'options-page.php' );
    }

    /** 
     * Module Manipulations 
     *
     * Must be hooked after `check_modules_on_every_plugin_activation`
     * Because it needs to check if modules are active
     * And `check_modules_on_every_plugin_activation` impacts that
     */
    public static function module_manipulations()
    {
        //* Modules
        require_once( self::$inc_dir . 'modules/blog.php' ); // Blog
        require_once( self::$inc_dir . 'modules/blog-comments.php' ); // Blog Comments
        // require_once( self::$inc_dir . 'modules/contactads.php' ); // EJO Contactadvertenties
        require_once( self::$inc_dir . 'modules/testimonials.php' ); // EJO Simple testimonials
        // require_once( self::$inc_dir . 'modules/portfolio.php' ); // EJO Portfolio
        // require_once( self::$inc_dir . 'modules/popup-box.php' ); // EJO Popup-box
        // require_once( self::$inc_dir . 'modules/photo-gallery.php' ); // EJO Photo Gallery
        // require_once( self::$inc_dir . 'modules/team.php' ); // EJO Team
        // require_once( self::$inc_dir . 'modules/social-media-extra.php' ); // EJO Social Media Pack
        // require_once( self::$inc_dir . 'modules/faq.php' ); // FAQ
    }

    /* On module activation */
    public static function reset_caps_on_module_activation( $module_id )
    {
        //* Hook to end of admin init to ensure all module manipulations and checks are done
        add_action( 'admin_init', array( 'EJO_Base_Module_Manager', 'reset_client_caps') );
    }

    /* On module activation */
    public static function check_modules_on_every_plugin_activation()
    {
        global $pagenow;

        if ($pagenow == 'plugins.php') {

            if ( isset($_GET['activate']) || isset($_GET['deactivate']) || isset($_GET['activate-multi']) || isset($_GET['deactivate-multi']) ) {
                EJO_Base_Module::check_activated_modules();
            }
        }
    }

    /* Reset the caps of the client-role */
    public static function reset_client_caps()
    {
        if ( class_exists('EJO_Client') ) {

            EJO_Client::reset_client_caps();

            /**
             * Remove double client-cap-reset
             *
             * Situation: 
             * - EJO_Client reset caps on every plugin (de)activation
             * - When a plugin is deactivated which causes a module to deactivate this class will run a EJO_Client reset cap
             * - No need to run two client cap resets
             */
            remove_action( 'admin_init', array( 'EJO_Client', 'reset_on_every_plugin_activation'), 99);
        }
    }
}

/* Call EJO Base Module Manager */
EJO_Base_Module_Manager::init();
