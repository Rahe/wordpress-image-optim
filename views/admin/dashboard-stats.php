<div class="block">
	<h4 class="sub"> <?php printf( esc_html__( 'Space optimized (%s - %s%%)', 'wordpress-image-optim' ), WP_Image_Optim_Statistics::wp_optimize_get_diff(), WP_Image_Optim_Statistics::wp_optimize_get_percentage() ); ?></h4>
	<div class="size-files" >
		<?php echo sprintf( esc_html__( '%s originaly', 'wordpress-image-optim' ), WP_Image_Optim_Statistics::wp_optimize_get_total_original_formated() ); ?>
		VS
		<?php echo sprintf( esc_html__( '%s optimized', 'wordpress-image-optim' ), WP_Image_Optim_Statistics::wp_optimize_get_total_optimized_formated() ); ?>
	</div>
</div>
