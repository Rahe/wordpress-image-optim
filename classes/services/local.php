<?php

class WP_Image_Optim_Service_HTTP implements WP_Image_Optim_Service_Interface {

	function set_options( $option_name, $option_value ) {
		return $this;
	}

	function optimize( $image, $source, $destination ) {
		$command = sprintf( 'node %s %s %s', WP_OPTIM_DIR . 'imageoptim.js', $source, $destination );
		shell_exec( escapeshellcmd( $command ) );
		clearstatcache();
	}
}