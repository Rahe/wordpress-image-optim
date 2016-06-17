<?php
/*
Plugin Name: WordPress Image Optim
Plugin URI: http://www.beapi.fr
Description: Optim images ( jpg, png, gif ) on your site
Version: 1.0.0
Author: Nicolas JUEN
Author URI: http://nicolas-juen.fr
*/

define( 'WP_OPTIM_URL', plugin_dir_url( __FILE__ ) );
define( 'WP_OPTIM_DIR', plugin_dir_path( __FILE__ ) );

/**
 * Libs
 */
include WP_OPTIM_DIR.'/libs/exec.php';

/**
 * Basics
 */
include WP_OPTIM_DIR.'/classes/image.php';
include WP_OPTIM_DIR.'/classes/main.php';
include WP_OPTIM_DIR.'/classes/optimizer.php';
include WP_OPTIM_DIR.'/classes/stats.php';

/**
 * Admin
 */
if( is_admin() ) {
	include WP_OPTIM_DIR.'/classes/admin/dashboard.php';
	include WP_OPTIM_DIR.'/classes/admin/image-actions.php';
	include WP_OPTIM_DIR.'/classes/admin/main.php';
}



/**
 * Generate the node modules in background on activation
 */
function wp_image_optim_activate() {
	$command = sprintf( 'npm install --prefix %s', plugin_dir_path( __FILE__ ) );
	exec::background( escapeshellcmd( $command ) );
}

register_activation_hook( __FILE__, 'wp_image_optim_activate' );


add_action( "plugins_loaded", 'wp_image_optim_init' );
function wp_image_optim_init() {
	new Wp_Optim_Main();

	if ( is_admin() ) {
		new Wp_Optim_Admin_Main();
		new Wp_Optim_Admin_Actions();
		new Wp_Optim_Admin_Dashboard_Stats();
	}

}