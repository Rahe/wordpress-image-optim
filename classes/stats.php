<?php
class WP_Image_Optim_Statistics {

	public static function wp_optimize_get_total_optimized() {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key=%s OR meta_key=%s", WP_Image_Optim::META_OPTIMIZED_SIZE, WP_Image_Optim::META_OPTIMIZED_THUMB_SIZE ) );
	}

	public static function wp_optimize_get_total_original() {
		global $wpdb;
		return $wpdb->get_var( $wpdb->prepare( "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key=%s OR meta_key=%s", WP_Image_Optim::META_ORIGINAL_SIZE, WP_Image_Optim::META_ORIGINAL_THUMB_SIZE ) );
	}

	public static function wp_optimize_get_total_optimized_formated() {
		return size_format( self::wp_optimize_get_total_optimized(), 2 );
	}

	public static function wp_optimize_get_total_original_formated() {
		return size_format( self::wp_optimize_get_total_original(), 2 );
	}

	public static function wp_optimize_get_percentage() {
		return number_format_i18n( ( self::wp_optimize_get_total_original() - self::wp_optimize_get_total_optimized() ) / self::wp_optimize_get_total_original() * 100, 2 );
	}

	public static function wp_optimize_get_diff() {
		return size_format( self::wp_optimize_get_total_original() - self::wp_optimize_get_total_optimized(), 2 );
	}

}