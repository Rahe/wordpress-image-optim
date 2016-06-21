<?php
class Wp_Optim_Admin_Settings {
	/**
	 * Base options
	 */
	private static $options = [];

	/**
	 * Default options
	 *
	 * @var array
	 */
	private static $default_options = [
		'base' => '',
	];

	public function __construct() {
		add_action( 'admin_menu', [ __CLASS__, 'admin_menu' ] );
		add_action( 'admin_init', [ __CLASS__, 'admin_init' ] );
	}

	public static function admin_menu() {
		add_options_page( 'WP optimize', 'ADEL', 'manage_options', 'wp-optimize', [ __CLASS__, 'page' ] );
	}

	/**
	 * Add the settings
	 */
	public static function admin_init() {
		//register our settings
		register_setting( 'wp-image-optim', 'wp-image-optim' );

		add_settings_section(
			'wp-image-optim', // ID
			'Réglages', // Title
			[ __CLASS__, 'sanitize' ], // Sanitize,
			'wp-optimize' // Page
		);

		add_settings_field(
			'base', // ID
			'Base Annuaire', // Title
			[ __CLASS__, 'text_field' ], // Callback
			'wp-optimize', // Page
			'wp-image-optim', // Section
			[
				'name' => 'base',
				'id'   => 'base',
				'default' => '',
				'description' => 'La base annuaire à appeler pour récupérer les données.',
				'placeholder' => 'Sélectionner',
			]
		);
	}

	/**
	 * Display the page
	 */
	public static function page() {
		include_once( WP_OPTIM_DIR . 'views/admin/settings.php' );
	}

	/**
	 * Get one option value
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	public static function get_option( $name ) {
		$options = self::get_options();
		return isset( $options[$name] ) ? $options[$name] : false ;
	}

	/**
	 * GEt all options filtered by defaults
	 *
	 * @return array|mixed|string|void
	 */
	private static function get_options() {
		if ( ! empty( self::$options ) ) {
			return self::$options;
		}

		/**
		 * Get options with the defaults
		 */
		self::$options = wp_parse_args( get_option( 'wp-image-optim' ), self::$default_options );

		return self::$options;
	}

	/**
	 * Sanitize the settings fields
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public static function sanitize( array $input ) {
		$new_input = [];
		if( isset( $input['base'] ) ) {
			$new_input['base'] = sanitize_text_field( $input['base'] );
		}

		return $new_input;
	}

	/**
	 * Get the settings option array and print one of its values
	 */
	public function text_field( $args ) {
		$option = self::get_option( $args['name'] );
		?>
		<input class="regular-text" type="text" name="wp-image-optim[<?php echo esc_attr( $args['name'] ) ?>]" id="wp-image-optim[<?php echo esc_attr( $args['id'] ) ?>]" value="<?php echo esc_attr( $option ); ?>" placeholder="<?php echo isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : ''; ?>" />
		<?php
		if ( isset( $args['description'] ) && ! empty( $args['description'] ) ) { ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php }
	}
}