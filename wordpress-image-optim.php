<?php
/*
Plugin Name: WordPress Image Optim
Plugin URI: http://www.beapi.fr
Description: Optim images ( jpg, png, gif ) on your site
Version: 1.0.0
Author: Nicolas JUEN
Author URI: http://nicolas-juen.fr
*/

add_filter( 'media_row_actions', 'wp_image_option_add_actions_list', 10, 2 );

function wp_image_option_add_actions_list( $actions, $object ) {
	if ( ! wp_attachment_is_image( $object->ID ) || ! current_user_can( 'upload_files' ) ) {
		return $actions;
	}

	// Add action for regeneration
	$attachment = new WP_Image_Optim( $object );
	$original = (float) $attachment->get_original_size();
	$optimized = (float) $attachment->get_size();
	$percent = number_format( $attachment->get_optim_percentage(), 0 );

	/**
	 * Versus text with the optimized size
	 */
	$versus = ! $attachment->is_optimized() ? '' : sprintf( '%s VS %s ( %s %% )', size_format( $optimized ), size_format( $original ), $percent ) ;

	$actions['wp-image-optim'] = sprintf(
		"%s <a href='%s'>%s</a>",
		$versus,
		wp_nonce_url(add_query_arg( array(
			'action' => 'wp-image-optim',
			'id' => $object->ID,
		), admin_url( '/admin-post.php' ) ), 'wp-image-optim-'.$object->ID ),
		esc_html__( 'Optimize', 'wordpress-image-optim' )
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
				'code' => 0,
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
				'code' => 1,
				'wp-image-optim' => '',
			],
			admin_url( 'upload.php' ) )
		);
		exit;
	}

	$id = (int) $_GET['id'];
	$nonce = isset( $_GET['_wpnonce'] ) ? $_GET['_wpnonce'] : false ;

	if ( ! wp_verify_nonce( $nonce, 'wp-image-optim-'.$id ) ) {
		wp_safe_redirect( add_query_arg( [
				'code' => 1,
				'wp-image-optim' => '',
			],
				admin_url( 'upload.php' ) )
		);
		exit;
	}

	if ( ! wp_attachment_is_image( $id ) ) {
		wp_safe_redirect( add_query_arg( [
				'code' => 2,
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
				'code' => 3,
				'wp-image-optim' => '',
			],
			admin_url( 'upload.php' ) )
	);
	exit;
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

	/**
	 * Wp_Image_Optim constructor.
	 *
	 * @param WP_Post $attachment
	 *
	 * @throws Exception
	 */
	public function __construct( WP_Post $attachment ) {
		if( ! wp_attachment_is_image( $attachment ) ) {
			throw new \Exception( sprintf( '%s is not an image', $attachment->ID ));
		}

		/**
		 * Set vars
		 */
		$this->attachment = $attachment;
		$this->file_path = get_attached_file( $attachment->ID );
		$this->directory = str_replace( basename( $this->file_path ), '', $this->file_path );
	}

	/**
	 * Set the original size and store it
	 *
	 * @return false|int
	 */
	public function set_original_size() {
		return add_post_meta( $this->attachment->ID, self::META_ORIGINAL_SIZE, $this->get_size(), true );
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
		return empty( $size ) ? $this->get_size() : $size ;
	}

	/**
	 * @return int|mixed
	 */
	public function get_size() {
		return filesize( $this->get_file_path() );
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
	 * Get the optimization percentage
	 *
	 * @return float|int
	 */
	public function get_optim_percentage() {
		if( ! $this->is_optimized() ) {
			return 0;
		}

		return ( $this->get_original_size() - $this->get_size() ) / $this->get_original_size() * 100;
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
