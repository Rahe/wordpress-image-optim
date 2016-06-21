<div class="wrap">
	<h2><?php echo get_admin_page_title(); ?></h2>
	<form method="POST" action="<?php echo esc_attr( admin_url( 'options.php' ) ) ?>" >
		<?php // This prints out all hidden setting fields
		settings_fields( 'wp-image-optim' );
		do_settings_sections( 'wp-image-optim' );
		submit_button();  ?>
	</form>
</div>
