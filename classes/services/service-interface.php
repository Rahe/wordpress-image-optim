<?php

interface WP_Image_Optim_Service_Interface {
	function set_options( $option_name, $option_value );
	function optimize( WP_Image_Optim $image );
}