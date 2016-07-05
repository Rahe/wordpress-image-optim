<?php
class WP_Image_Optim_Optimizer_Factory {

	public function get_optimizer( $type, WP_Image_Optim $image ) {
		switch ( $type ) {
			case 'service' :
				return new WP_Image_Optim_Optimizer_Service( $image );
				break;
			default :
				return new WP_Image_Optim_Optimizer( $image );
				break;
		}
	}

}