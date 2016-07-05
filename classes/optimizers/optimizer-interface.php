<?php

interface WP_Image_Optim_Optimizer_Interface {
	function optimize();
	function optimize_thumbs();
	function optimize_image();
	function set_service( WP_Image_Optim_Optimizer_Interface $service );
}