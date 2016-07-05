<?php

class WP_Image_Optim_Optimizer_Service implements WP_Image_Optim_Optimizer_Interface {
	/**
	 * Service to use
	 *
	 * @var WP_Image_Optim_Optimizer_Interface
	 */
	private $service;

	/**
	 * WP_Image_Optim_Optimizer constructor.
	 *
	 * @param WP_Image_Optim $image
	 */
	public function set_image( WP_Image_Optim $image ) {
		$this->image   = $image;
	}
	
	public function set_service( WP_Image_Optim_Optimizer_Interface $service ) {
		$this->service = $service;
	}

	/**
	 * Optimize the given image
	 *
	 * @return bool
	 */
	public function optimize( WP_Image_Optim $image ) {
		$image->set_original_size();
		$this->optimize_image( $image->get_file_path(), $image->get_directory() );
		$image->get_size();

		return true;
	}

	public function optimize_thumbs( WP_Image_Optim $image ) {
		$thumbnails = $image->get_thumbnails();

		if ( empty( $thumbnails ) ) {
			return false;
		}

		$image->set_original_thumb_size();
		$file = $image->get_file_path();
		foreach ( $thumbnails as $size => $size_info ) {
			$this->optimize_image( $image, str_replace( basename( $file ), $size_info['file'], $file ), $image->get_directory() );
		}

		$image->get_original_thumbnails_size();

		return true;
	}

	/**
	 * Optimize the given file and destination
	 *
	 * @param $source
	 * @param $destination
	 */
	private function optimize_image($image, $source, $destination ) {
		$this->service->optimize( $image, $source, $destination );
	}
}