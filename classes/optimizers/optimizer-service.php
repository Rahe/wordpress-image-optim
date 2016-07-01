<?php

class WP_Image_Optim_Optimizer_Service {

	/**
	 * Image to optimize
	 *
	 * @var WP_Image_Optim
	 */
	private $image;

	/**
	 * Service to use
	 *
	 * @var
	 */
	private $service;

	/**
	 * WP_Image_Optim_Optimizer constructor.
	 *
	 * @param WP_Image_Optim $image
	 */
	public function __construct( $service, WP_Image_Optim $image ) {
		$this->image   = $image;
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
		$cfile = new CURLFile( $file, $this->image->get_mime_type(), basename( $file ) );
		$post  = array(
			'fileupload' => $cfile,
		);

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->service->get_url() );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_HEADER, 0 );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)" );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 'Content-Type: multipart/form-data' ) );
		curl_setopt( $ch, CURLOPT_FRESH_CONNECT, 1 );
		curl_setopt( $ch, CURLOPT_FORBID_REUSE, 1 );
		curl_setopt( $ch, CURLOPT_TIMEOUT, 100 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $post );

		$result = curl_exec( $ch );

		curl_close( $ch );

		if ( $result === false ) {
			return new WP_Error( 'error_service', "Error sending" . $file . " " . curl_error( $ch ) );
		}


		if( false === file_put_contents( $file, $result ) ) {
			return new WP_Error( 'error_writing', "Error writing" . $file );
		}

		clearstatcache();

		return true;
	}
}