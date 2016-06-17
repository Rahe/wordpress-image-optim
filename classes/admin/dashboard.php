<?php
class Wp_Optim_Dashboard_Stats{
	function __construct() {
		add_action( 'wp_dashboard_setup', array( __CLASS__, 'add_widgets' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
	}

	public static function register_assets() {
		// get the scrren
		$screen = get_current_screen();

		// Skip other pages than dashboard
		if( $screen->base !== 'dashboard' ) {
			return false;
		}

		// add the widget css
		wp_enqueue_style( 'wp_optim_admin_dashboard', WP_OPTIM_URL.'/assets/css/admin-dashboard.css' );
	}

	public static function add_widgets() {
		wp_add_dashboard_widget( 'bea_medias_stats_widget', __( 'Statistics', 'wp-image-optim' ), array( __CLASS__, 'widget' ) );
	}

	public static function widget() {
		if( is_file( WP_OPTIM_DIR.'/views/admin/dashboard-stats.php' ) ) {
			require_once( WP_OPTIM_DIR.'/views/admin/dashboard-stats.php' );
		} else {
			echo 'Template Manquant';
		}
	}
}