<?php
class WP_Image_Optim_Optimizer implements WP_Image_Optim_Optimizer_Interface {

	/**
	 * Image to optimize
	 *
	 * @var WP_Image_Optim
	 */
	private $image;

	/**
	 * Image to optimize
	 *
	 * @var WP_Image_Optim
	 */
	private $service;

	/**
	 * WP_Image_Optim_Optimizer constructor.
	 *
	 * @param WP_Image_Optim $image
	 */
	public function __construct( WP_Image_Optim $image ) {
		$this->image = $image;
	}

	public function set_service( $service ) {
		$this->service = $service;
	}

	/**
	 * Optimize the given image
	 *
	 * @return bool
	 */
	public function optimize() {
		$this->image->set_original_size();
		$this->optimize_image( $this->image->get_file_path(), $this->image->get_directory() );
		$this->image->get_size();
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

		$this->image->get_original_thumbnails_size();

		return true;
	}

	/**
	 * Optimize the given file and destination
	 *
	 * @param $file
	 * @param $destination
	 */
	private function optimize_image( $file, $destination ) {
		$this->service->optimize( $this->image );
	}
}