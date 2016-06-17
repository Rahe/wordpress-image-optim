<?php
class Wp_Optim_Admin_Main{
	function __construct() {
		add_action( 'media_row_actions', array( __CLASS__, 'media_row_actions' ),10, 2 );

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
	}

	/**
	 * Add row action
	 *
	 * @param $actions
	 * @param $object
	 *
	 * @return mixed
	 */
	public static function media_row_actions( $actions, $object ) {
		if ( ! wp_attachment_is_image( $object->ID ) || ! current_user_can( 'upload_files' ) ) {
			return $actions;
		}

		// Add action for regeneration
		$attachment = new WP_Image_Optim( $object );
		$original   = (float) $attachment->get_original_size();
		$optimized  = (float) $attachment->get_size();
		$percent    = number_format( $attachment->get_optim_percentage(), 0 );

		/**
		 * Versus text with the optimized size
		 */
		$versus = ! $attachment->is_optimized() ? '' : sprintf( '%s VS %s ( %s %% )', size_format( $optimized ), size_format( $original ), $percent );

		$actions['wp-image-optim'] = sprintf(
			"%s <a href='%s'>%s</a>",
			$versus,
			wp_nonce_url( add_query_arg( array(
				'action' => 'wp-image-optim',
				'id'     => $object->ID,
			), admin_url( '/admin-post.php' ) ), 'wp-image-optim-' . $object->ID ),
			esc_html__( 'Optimize', 'wordpress-image-optim' )
		);

		// Add action for regeneration
		$attachment = new WP_Image_Optim( $object );
		$original   = (float) $attachment->get_original_thumbnails_size();
		$optimized  = (float) $attachment->get_thumbnails_size();
		$percent    = number_format( $attachment->get_thumbnails_optim_percentage(), 0 );
		/**
		 * Versus text with the optimized size
		 */
		$versus = ! $attachment->is_thumbnails_optimized() ? '' : sprintf( '%s VS %s ( %s %% )', size_format( $optimized ), size_format( $original ), $percent );

		$actions['wp-image-optim-thumbs'] = sprintf(
			"%s <a href='%s'>%s</a>",
			$versus,
			wp_nonce_url( add_query_arg( array(
				'action' => 'wp-image-optim-thumbs',
				'id'     => $object->ID,
			), admin_url( '/admin-post.php' ) ), 'wp-image-optim-thumbs-' . $object->ID ),
			esc_html__( 'Optimize thumbs', 'wordpress-image-optim' )
		);

		// Return actions
		return $actions;
	}

	public static function admin_notices() {
		$messages = [
			0 => 'Sorry attachment ID is missing',
			1 => 'Security error',
			2 => 'This attachment is not an image',
			3 => 'Image optimized !',
		];

		if ( ! isset( $_GET['wp-image-optim'] ) || ! isset( $_GET['code'] ) || ! isset( $messages[ $_GET['code'] ] ) ) {
			return;
		}
		?>
		<div class="updated notice">
			<p><?php echo $messages[ $_GET['code'] ]; ?></p>
		</div>
		<?php
	}
}