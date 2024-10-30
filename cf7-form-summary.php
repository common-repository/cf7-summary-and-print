<?php
/**
 * Plugin Name: Contact Form 7 Summary and Print
 * Version: 1.2.6
 * Description: This plugin helps you to view summary of contact form 7 form with a print summary button. Users can view their form's summary of all the fields which they have entered during form submission with the Print button at the bottom so they can easily print out their form summary.
 * Author: Muhammad Rehman
 * Author URI: https://muhammadrehman.com
 * Requires Plugins: contact-form-7
 * Text Domain: CF7_SP
 *
 * @package cf7-summary-and-print
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CF7_SP_VERSION', '1.2.6' );
define( 'CF7_SP_SLUG', 'CF7_SP' );
define( 'CF7_SP_TEXTDOMAIN', 'CF7_SP' );

define( 'CF7_SP_THIS', __FILE__ );
define( 'CF7_SP_ROOT_DIR', plugin_dir_path( CF7_SP_THIS ) );
define( 'CF7_SP_DIR', CF7_SP_ROOT_DIR . 'assets/' );
define( 'CF7_SP_INCLUDES_DIR', CF7_SP_ROOT_DIR . 'includes/' );

if ( !function_exists( 'cf7sapp_fs' ) ) {
    // Create a helper function for easy SDK access.
    function cf7sapp_fs()
    {
        global  $cf7sapp_fs;
        
        if ( !isset( $cf7sapp_fs ) ) {
            // Include Freemius SDK.
            require_once dirname( __FILE__ ) . '/freemius/start.php';
            $cf7sapp_fs = fs_dynamic_init( array(
                'id'             => '12246',
                'slug'           => 'cf7-summary-and-print',
                'premium_slug'   => 'cf7-summary-and-print-pro',
                'type'           => 'plugin',
                'public_key'     => 'pk_f105cc9e656a9f81b9f92c560e0b9',
                'is_premium'     => false,
                'premium_suffix' => 'Premium',
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'slug'    => 'cf7-summary-Print',
                    'support' => false,
                    'account' => true,
                    'pricing' => true,
                    'parent'  => array(
                    'slug' => 'wpcf7',
                ),
            ),
                'is_live'        => true,
            ) );
        }
        
        return $cf7sapp_fs;
    }
    
    // Init Freemius.
    cf7sapp_fs();
    // Signal that SDK was initiated.
    do_action( 'cf7sapp_fs_loaded' );
}

/**
 * Load plugin files
 *
 * @since 1.0
 * @version 1.0
 */
function cf7_sp_load() {

	require_once CF7_SP_INCLUDES_DIR . 'admin/class-cf7-sp-settings.php';
	require_once CF7_SP_INCLUDES_DIR . 'class-cf7-sp-summary-print.php';
}

cf7_sp_load();
