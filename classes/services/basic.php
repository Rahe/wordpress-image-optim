<?php
class WP_Image_Optim_Service_Basic{

	private $url;

	private $method;

	public function __construct( array $args ) {
		$this->method = $args[ 'method' ];
		$this->url = $args['url'];
	}

	public function get_url() {
		return $this->url;
	}

	public function get_method() {
		return $this->method;
	}
}