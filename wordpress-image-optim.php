<?php
/*
Plugin Name: WordPress Image Optim
Plugin URI: http://www.beapi.fr
Description: Optim images on your install
Version: 1.0.0
Author: BeAPI
Author URI: http://www.beapi.fr
*/

add_filter( 'media_row_actions', 'wp_image_option_add_actions_list', 10, 2 );

function wp_image_option_add_actions_list( $actions, $object ) {
	if ( ! wp_attachment_is_image( $object->ID ) ) {
		return $actions;
	}
	// Add action for regeneration
	$file_path = get_attached_file( $object->ID );

	$actions['sis-regenerate'] = sprintf(
		"%s vs %s <a href='%s'>%s</a>",
		size_format( filesize( $file_path ) ),
		size_format( get_post_meta( $object->ID, 'att_size', true ) ),
		admin_url( '?action=optim&id=' . $object->ID ),
		esc_html__( 'Optimize', 'wordpress-image-optim' )
	);

	// Return actions
	return $actions;
}

add_action( 'admin_init', 'wp_image_option_admin_init' );
function wp_image_option_admin_init() {
	if ( ! isset( $_GET['action'] ) || ! isset( $_GET['id'] ) ) {
		return;
	}

	$id = (int) $_GET['id'];

	if ( ! wp_attachment_is_image( $id ) ) {
		return;
	}

	$file_path = get_attached_file( $id );
	add_post_meta( $id, 'att_size', filesize( $file_path ), true );

	$command = sprintf( 'nodejs %s %s %s', plugin_dir_path( __FILE__ ) . 'imageoptim.js', $file_path, str_replace( basename( $file_path ), '', $file_path ) )
	shell_exec( escapeshellcmd( $command ) );
	clearstatcache();

	add_post_meta( $id, 'att_size', filesize( $file_path ), true );
}