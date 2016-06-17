<?php
/*
Plugin Name: WordPress Image Optim
Plugin URI: http://www.beapi.fr
Description: Optim images ( jpg, png, gif ) on your site
Version: 1.0.0
Author: Nicolas JUEN
Author URI: http://nicolas-juen.fr
*/

include 'libs/exec.php';
add_filter( 'media_row_actions', 'wp_image_option_add_actions_list', 10, 2 );


add_action('add_attachment', 'wp_image_optim_add_attachment');
function wp_image_optim_add_attachment($post_ID) {
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


/**
 * Generate the node modules in background on activation
 */
function wp_image_optim_activate() {
	$command = sprintf( 'npm install --prefix %s', plugin_dir_path( __FILE__ ) );
	exec::background( escapeshellcmd( $command ) );
}

function wp_image_option_add_actions_list( $actions, $object ) {
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

function wp_image_optim_notice() {
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

add_action( 'admin_notices', 'wp_image_optim_notice' );

add_action( 'admin_post_wp-image-optim', 'wp_image_optim_admin_post' );
function wp_image_optim_admin_post() {
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

add_action( 'admin_post_wp-image-optim-thumbs', 'wp_image_optim_thumbs_admin_post' );
function wp_image_optim_thumbs_admin_post() {
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

function wp_optimize_get_total_optimized() {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key=%s OR meta_key=%s", WP_Image_Optim::META_OPTIMIZED_SIZE, WP_Image_Optim::META_OPTIMIZED_THUMB_SIZE ) );
}

function wp_optimize_get_total_original() {
	global $wpdb;
	return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key=%s OR meta_key=%s", WP_Image_Optim::META_ORIGINAL_SIZE, WP_Image_Optim::META_ORIGINAL_THUMB_SIZE ) );
}

function wp_optimize_get_total_optimized_formated() {
	return size_format( wp_optimize_get_total_optimized(), 0 );
}

function wp_optimize_get_total_original_formated() {
	return size_format( wp_optimize_get_total_original(), 0 );
}

function wp_optimize_get_percentage() {
	return ( wp_optimize_get_total_original() - wp_optimize_get_total_optimized() ) / wp_optimize_get_total_original() * 100;
}

Class WP_Image_Optim {

	/**
	 * @var WP_Post
	 */
	private $attachment;

	/**
	 * @var false|string
	 */
	private $file_path;

	/**
	 * @var mixed
	 */
	private $directory;

	const META_ORIGINAL_SIZE = 'wp_image_optim_att_size';
	const META_ORIGINAL_THUMB_SIZE = 'wp_image_optim_thumb_size';

	const META_OPTIMIZED_SIZE = 'wp_image_optim_att_optimized_size';
	const META_OPTIMIZED_THUMB_SIZE = 'wp_image_optim_optimized_thumb_size';

	/**
	 * Wp_Image_Optim constructor.
	 *
	 * @param WP_Post $attachment
	 *
	 * @throws Exception
	 */
	public function __construct( WP_Post $attachment ) {
		if ( ! wp_attachment_is_image( $attachment ) ) {
			throw new \Exception( sprintf( '%s is not an image', $attachment->ID ) );
		}

		/**
		 * Set vars
		 */
		$this->attachment = $attachment;
		$this->file_path  = get_attached_file( $attachment->ID );
		$this->directory  = str_replace( basename( $this->file_path ), '', $this->file_path );
	}

	/**
	 * Set the original size and store it
	 *
	 * @return false|int
	 */
	public function set_original_size() {
		return add_post_meta( $this->attachment->ID, self::META_ORIGINAL_SIZE, $this->get_size(), true );
	}

	public function set_original_thumb_size() {
		return add_post_meta( $this->attachment->ID, self::META_ORIGINAL_THUMB_SIZE, $this->get_thumbnails_size(), true );
	}

	/**
	 * Optimize the image using the optimizer
	 *
	 * @param WP_Image_Optim_Optimizer $optimizer
	 *
	 * @return bool
	 */
	public function optimize( WP_Image_Optim_Optimizer $optimizer ) {
		return $optimizer->optimize();
	}

	/**
	 * Return the file path
	 *
	 * @return false|string
	 */
	public function get_file_path() {
		return $this->file_path;
	}

	/**
	 * Return the target directory
	 *
	 * @return mixed
	 */
	public function get_directory() {
		return $this->directory;
	}

	/**
	 * @return int|mixed
	 */
	public function get_original_size() {
		$size = get_post_meta( $this->attachment->ID, self::META_ORIGINAL_SIZE, true );

		return empty( $size ) ? $this->get_size() : $size;
	}

	/**
	 * @return int|mixed
	 */
	public function get_original_thumbnails_size() {
		$size = get_post_meta( $this->attachment->ID, self::META_ORIGINAL_THUMB_SIZE, true );

		return empty( $size ) ? $this->get_thumbnails_size() : $size;
	}

	/**
	 * @return int|mixed
	 */
	public function get_size() {
		if( ! $this->is_optimized() ) {
			return filesize( $this->get_file_path() );
		}
		$size = get_post_meta( $this->attachment->ID, self::META_OPTIMIZED_SIZE, true );
		if ( ! empty( $size ) ) {
			return $size;
		}

		$size = filesize( $this->get_file_path() );
		update_post_meta( $this->attachment->ID, self::META_OPTIMIZED_SIZE, $size );
		return $size;
	}

	/**
	 * @return int|mixed
	 */
	public function get_thumbnails_size() {

		if( ! $this->is_thumbnails_optimized() ) {
			$thumbnails = $this->get_thumbnails();
			$file = $this->get_file_path();
			$size = 0;

			foreach ( $thumbnails as $size => $size_info ) {
				$size += filesize( str_replace( basename( $file ), $size_info['file'], $file ) );
			}

			return $size;
		}

		$size = get_post_meta( $this->attachment->ID, self::META_OPTIMIZED_THUMB_SIZE, true );
		if ( ! empty( $size ) ) {
			return $size;
		}

		$thumbnails = $this->get_thumbnails();
		$file = $this->get_file_path();
		$size = 0;

		foreach ( $thumbnails as $size => $size_info ) {
			$size += filesize( str_replace( basename( $file ), $size_info['file'], $file ) );
		}
		update_post_meta( $this->attachment->ID, self::META_OPTIMIZED_THUMB_SIZE, $size );

		return $size;
	}

	/**
	 * Check if the image is optimized or not
	 *
	 * @return bool
	 */
	public function is_optimized() {
		$size = get_post_meta( $this->attachment->ID, self::META_ORIGINAL_SIZE, true );

		return ! empty( $size );
	}

	/**
	 * Check if the image is optimized or not
	 *
	 * @return bool
	 */
	public function is_thumbnails_optimized() {
		$size = get_post_meta( $this->attachment->ID, self::META_ORIGINAL_THUMB_SIZE, true );

		return ! empty( $size );
	}

	/**
	 * Get the optimization percentage
	 *
	 * @return float|int
	 */
	public function get_optim_percentage() {
		if ( ! $this->is_optimized() ) {
			return 0;
		}

		return ( $this->get_original_size() - $this->get_size() ) / $this->get_original_size() * 100;
	}

	/**
	 * Get the optimization percentage
	 *
	 * @return float|int
	 */
	public function get_thumbnails_optim_percentage() {
		if ( ! $this->is_thumbnails_optimized() ) {
			return 0;
		}

		return ( $this->get_original_thumbnails_size() - $this->get_thumbnails_size() ) / $this->get_original_thumbnails_size() * 100;
	}

	public function get_thumbnails() {
		$meta_data =  wp_get_attachment_metadata( $this->attachment->ID );

		return empty( $meta_data['sizes'] ) ? [] : $meta_data['sizes'];
	}
}

class WP_Image_Optim_Optimizer {

	/**
	 * Image to optimize
	 *
	 * @var WP_Image_Optim
	 */
	private $image;

	/**
	 * WP_Image_Optim_Optimizer constructor.
	 *
	 * @param WP_Image_Optim $image
	 */
	public function __construct( WP_Image_Optim $image ) {
		$this->image = $image;
	}

	/**
	 * Optimize the given image
	 *
	 * @return bool
	 */
	public function optimize() {
		$this->image->set_original_size();
		$this->optimize_image( $this->image->get_file_path(), $this->image->get_directory() );

		return true;
	}

	public function optimize_thumbs() {
		$thumbnails = $this->image->get_thumbnails();

		if ( empty( $thumbnails ) ) {
			return false;
		}

		$this->image->set_original_thumb_size();
		$file = $this->image->get_file_path();
		foreach ( $thumbnails as $size => $size_info ) {
			$this->optimize_image( str_replace( basename( $file ), $size_info['file'], $file ), $this->image->get_directory() );
		}

		return true;
	}

	/**
	 * Optimize the given file and destination
	 *
	 * @param $file
	 * @param $destination
	 */
	private function optimize_image( $file, $destination ) {
		$command = sprintf( 'nodejs %s %s %s', plugin_dir_path( __FILE__ ) . 'imageoptim.js', $file, $destination );
		shell_exec( escapeshellcmd( $command ) );
		clearstatcache();
	}
}

register_activation_hook( __FILE__, 'wp_image_optim_activate' );