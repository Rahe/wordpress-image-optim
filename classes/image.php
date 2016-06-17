<?php

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