<?php
class Wp_Optim_Admin_Actions {

	/**
	 * Wp_Optim_Admin_Actions constructor.
	 */
	public function __construct() {
		add_action( 'admin_post_wp-image-optim', [ __CLASS__, 'image_optim' ] );
		add_action( 'admin_post_wp-image-optim-thumbs', [ __CLASS__, 'image_thumbnails_optim' ] );
	}

	public static function image_optim() {
		/**
		 * Missing id
		 */
		if ( ! isset( $_GET['id'] ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 0,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		/**
		 * Security error
		 */
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 1,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		$id    = (int) $_GET['id'];
		$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : false;

		if ( ! wp_verify_nonce( $nonce, 'wp-image-optim-' . $id ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 1,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		if ( ! wp_attachment_is_image( $id ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 2,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}
		
		$facotory = New Image

		try {
			$image = new WP_Image_Optim( get_post( $id ) );
		} catch ( Exception $exception ) {
			return;
		}
		$optimizer = new WP_Image_Optim_Optimizer( $image );
		$optimizer->optimize();

		wp_safe_redirect( add_query_arg(
				[
					'code'           => 3,
					'wp-image-optim' => '',
				],
				admin_url( 'upload.php' ) )
		);
		exit;
	}

	public static function image_thumbnails_optim() {
		/**
		 * Missing id
		 */
		if ( ! isset( $_GET['id'] ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 0,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		/**
		 * Security error
		 */
		if ( ! current_user_can( 'upload_files' ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 1,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		$id    = (int) $_GET['id'];
		$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : false;

		if ( ! wp_verify_nonce( $nonce, 'wp-image-optim-thumbs-' . $id ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 1,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		if ( ! wp_attachment_is_image( $id ) ) {
			wp_safe_redirect( add_query_arg( [
					'code'           => 2,
					'wp-image-optim' => '',
				],
					admin_url( 'upload.php' ) )
			);
			exit;
		}

		try {
			$image = new WP_Image_Optim( get_post( $id ) );
		} catch ( Exception $exception ) {
			return;
		}
		$optimizer = new WP_Image_Optim_Optimizer( $image );
		$optimizer->optimize_thumbs();

		wp_safe_redirect( add_query_arg(
				[
					'code'           => 3,
					'wp-image-optim' => '',
				],
				admin_url( 'upload.php' ) )
		);
		exit;
	}


}