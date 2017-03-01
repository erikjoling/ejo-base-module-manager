<?php

/** 
 * By default blog features are hidden by EJO Base unless the theme adds theme support
 */
if ( ! EJO_Base_Module::is_active('blog') ) {
    remove_theme_support( 'blog' );
}
else {
    add_theme_support( 'blog' );
}
