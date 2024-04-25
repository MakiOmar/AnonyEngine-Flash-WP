<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link  https://github.com/MakiOmar
 * @since 1.0.0
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Wp_Public extends Anony_Flash_Public_Base {

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-delay-js.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-defer-js.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-media.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-styles.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-scripts.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-general.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-woocommerce.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-css.php';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anony_Flash_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anony_Flash_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anony_Flash_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anony_Flash_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
	}

	/**
	 * Callback for ob_start
	 *
	 * @param  string $html Document HTML.
	 * @return string
	 */
	public function wp_html_compression_finish_cb( $html ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' !== $anofl_options->compress_html ) {
			return $html;
		}

		return new ANONY_Wp_Html_Compression( $html );
	}

	/**
	 * Hooked to get_header
	 */
	public function wp_html_compression_finish() {
		// From PHP reference: ob_start(callable $callback = null, int $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): bool.
		// When callback is called, it will receive the contents of the output buffer as its parameter and is expected to return a new output buffer as a result, which will be sent to the browser..
		ob_start( array( $this, 'wp_html_compression_finish_cb' ) );
	}

	/**
	 * Load bg on interaction
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function load_bg_on_interaction( $content ) {
		$media = new Anony_Flash_Media();
		return $media->load_bg_on_interaction( $content );
	}

	/**
	 * Add missing dimensions
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function add_missing_image_dimensions( $content ) {
		$media = new Anony_Flash_Media();
		return $media->add_missing_image_dimensions( $content );
	}

	/**
	 * Start HTML buffer
	 */
	public function start_html_buffer() {

		// buffer output html..
		ob_start( array( $this, 'start_html_buffer_cb' ), 0 );
	}

	/**
	 * Manipulate buffer HTML.
	 * For now we delay JS and Load backgrounds in interaction.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function start_html_buffer_cb( $html ) {
		// Delay js.
		$html = $this->add_delay_type_attribute( $html );

		// Load backgrounds in interaction.
		$html = $this->load_bg_on_interaction( $html );

		// Add missing images dimesions and lazyload.
		$html = $this->add_missing_image_dimensions( $html );

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		return $html;
		// phpcs:enable.
	}
	/**
	 * Apply JS delay.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	protected function add_delay_type_attribute( $html ) {
		// Delay JS.
		$delay = false;

		// If general delay option is enabled.
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' === $anofl_options->load_scripts_on_interaction ) {
			$delay = true;
		}

		// If general delay option is not enabled, we check if it is enabled for a page or front page.
		if ( '1' !== $anofl_options->load_scripts_on_interaction && ( is_page() || is_front_page() ) ) {

			global $post;

			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			if ( $optimize_per_post && ! empty( $optimize_per_post ) && isset( $optimize_per_post['delay_js'] ) && '1' === $optimize_per_post['delay_js'] ) {
				$delay = true;
			}
		}

		// If delay is enabled, Start delay.
		if ( $delay && ! $this->uri_strpos( 'elementor' ) && ! $this->uri_strpos( 'wp-admin' ) ) {
			$html = $this->regex_delay_scripts( $html );
		}
		return $html;
	}

	/**
	 * End HTML buffer
	 */
	public function end_html_buffer() {
		if ( $this->is_option_enabled_for_page( 'enable_used_css' ) || $this->is_option_enabled_for_page( 'above_the_fold_styles' ) ) {
			// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo ob_get_clean();
		}
	}
}
