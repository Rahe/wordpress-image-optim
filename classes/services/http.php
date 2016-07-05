<?php

class WP_Image_Optim_Service_HTTP implements WP_Image_Optim_Service_Interface {

	function set_options( $option_name, $option_value ) {
		return $this;
	}

	function optimize( $image, $source, $destination ) {
		$cfile = new CURLFile( $source, $image->get_mime_type(), basename( $source ) );
		$post  = array(
			'fileupload' => $cfile,
		);

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $this->get_url() );
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


		if ( false === file_put_contents( $file, $result ) ) {
			return new WP_Error( 'error_writing', "Error writing" . $file );
		}

		clearstatcache();

		return true;
	}
}