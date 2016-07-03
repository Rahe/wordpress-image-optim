<?php
class OptimTestCest {
	/**
	 * @var \UnitTester
	 */
	protected $tester;

	public function _before( \UnitTester $I ) {

	}

	public function _after( \UnitTester $I ) {
	}

	// tests
	public function tryToTest( \UnitTester $I ) {
		$I->wantTo( 'Check if error throwed' );

		$post = get_post( 1 );

		try{
			$image = new WP_Image_Optim($post);
		} catch ( Exception $e ) {
			$error = $e;
		}
		$I->assertEquals( '1 is not an image', $error->getMessage() );

	}

	public function TestNotOptim(\UnitTester $I) {

		// this image is smaller than the thumbnail size so it won't have one
		$filename = ( dirname( __FILE__ ).'/../images/test-image.jpg' );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits(basename($filename), null, $contents);
		$I->assertTrue( empty($upload['error']) );
		
		$id = $this->_make_attachment($upload);

		$image = new WP_Image_Optim( get_post( $id ) );

		$I->wantTo( 'Check if image is really not optimized' );
		$I->assertFalse( $image->is_optimized() );
		$I->assertFalse( $image->is_thumbnails_optimized() );

		$optimizer = new WP_Image_Optim_Optimizer( $image );

		/**
		 * Optimize the image
		 */
		$optimizer->optimize();

		$I->wantTo( 'Check if image is optimized and not thumbs' );
		$I->assertTrue( $image->is_optimized() );
		$I->assertFalse( $image->is_thumbnails_optimized() );

		/**
		 * Optimize the thumbs
		 */
		$optimizer->optimize_thumbs();
		$I->wantTo( 'Check if thumbs are optimized' );
		$I->assertTrue( $image->is_optimized() );
		$I->assertTrue( $image->is_thumbnails_optimized() );
	}

	public function TestNotOptimThumbs( \UnitTester $I ) {
		// this image is smaller than the thumbnail size so it won't have one
		$filename = ( dirname( __FILE__ ).'/../images/test-image.jpg' );
		$contents = file_get_contents($filename);
		$upload = wp_upload_bits(basename($filename), null, $contents);
		$I->assertTrue( empty($upload['error']) );

		$id = $this->_make_attachment($upload);

		$image = new WP_Image_Optim( get_post( $id ) );

		$I->wantTo( 'Check if image is really not optimized' );
		$I->assertFalse( $image->is_optimized() );
		$I->assertFalse( $image->is_thumbnails_optimized() );

		$optimizer = new WP_Image_Optim_Optimizer( $image );

		/**
		 * Optimize the image
		 */
		$optimizer->optimize_thumbs();

		$I->wantTo( 'Check if image is optimized and not thumbs' );
		$I->assertFalse( $image->is_optimized() );
		$I->assertTrue( $image->is_thumbnails_optimized() );

		/**
		 * Optimize the thumbs
		 */
		$optimizer->optimize();
		$I->wantTo( 'Check if thumbs are optimized' );
		$I->assertTrue( $image->is_optimized() );
		$I->assertTrue( $image->is_thumbnails_optimized() );
	}

	function _make_attachment( $upload, $parent_post_id = 0 ) {

		$type = '';
		if ( !empty($upload['type']) ) {
			$type = $upload['type'];
		} else {
			$mime = wp_check_filetype( $upload['file'] );
			if ($mime)
				$type = $mime['type'];
		}

		$attachment = array(
			'post_title' => basename( $upload['file'] ),
			'post_content' => '',
			'post_type' => 'attachment',
			'post_parent' => $parent_post_id,
			'post_mime_type' => $type,
			'guid' => $upload[ 'url' ],
		);

		// Save the data
		$id = wp_insert_attachment( $attachment, $upload[ 'file' ], $parent_post_id );
		wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $upload['file'] ) );

		return $this->ids[] = $id;

	}

}