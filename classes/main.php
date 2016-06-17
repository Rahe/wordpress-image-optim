<?php
class Wp_Optim_Main{

	/**
	 * Wp_Optim_Main constructor.
	 */
	public function __construct() {
		add_action('add_attachment', [ __CLASS__, 'add_attachment' ] );
	}

	public static function add_attachment($post_ID) {
		if ( ! wp_attachment_is_image( $post_ID ) ) {
			return;
		}

		try {
			$image = new WP_Image_Optim( get_post( $post_ID ) );
		} catch ( Exception $exception ) {
			return;
		}
		$optimizer = new WP_Image_Optim_Optimizer( $image );
		$optimizer->optimize();
	}
}