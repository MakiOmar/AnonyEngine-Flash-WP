<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.0
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/includes
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Wp {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Anony_Flash_Wp_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'ANONY_FLASH_WP_VERSION' ) ) {
			$this->version = ANONY_FLASH_WP_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'anony-flash-wp';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Anony_Flash_Wp_Loader. Orchestrates the hooks of the plugin.
	 * - Anony_Flash_Wp_I18n. Defines internationalization functionality.
	 * - Anony_Flash_Wp_Admin. Defines all hooks for the admin area.
	 * - Anony_Flash_Wp_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-anony-flash-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-anony-flash-wp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-anony-flash-wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-public-base.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-wp-public.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-defer-js.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-delay-js.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-media.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-preload.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-css.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-general.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-scripts.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-styles.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-anony-flash-woocommerce.php';
		$this->loader = new Anony_Flash_Wp_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Anony_Flash_Wp_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Anony_Flash_Wp_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Anony_Flash_Wp_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		//phpcs:disable
		if ( isset( $_SERVER['REQUEST_URI'] ) && false !== strpos( $_SERVER['REQUEST_URI'], 'checkout' ) ) {
			return;
		}
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( ( '1' === $anofl_options->debug_mode && empty( $_GET['debug_mode'] ) ) ||  defined( 'DOING_CRON' ) ) {
			return;
		}
		//phpcs:enable
		$excluded_roles = $anofl_options->excluded_roles;

		$plugin_public = new Anony_Flash_Wp_Public( $this->get_plugin_name(), $this->get_version() );

		if ( is_user_logged_in() && is_array( $excluded_roles ) ) {
			$current_user       = wp_get_current_user();
			$current_user_roles = $current_user->roles;

			$intersection = array_intersect( $current_user_roles, $excluded_roles );

			if ( ! empty( $intersection ) ) {
				return;
			}
		}

		/* ---------------------------Media------------------------------------------------------------*/
		$media = new Anony_Flash_Media();
		$this->loader->add_action( 'wp_enqueue_scripts', $media, 'lazy_elementor_background_images_js', 999 );
		$this->loader->add_action( 'wp_head', $media, 'lazy_elementor_background_images_css' );
		$this->loader->add_filter( 'the_content', $media, 'elementor_add_lazyload_class' );
		$this->loader->add_action( 'wp_head', $media, 'load_bg_on_interaction_styles' );
		$this->loader->add_filter( 'the_content', $media, 'load_bg_on_interaction' );
		$this->loader->add_action( 'wp_print_footer_scripts', $media, 'lazyload_images', 999 );
		$this->loader->add_action( 'wp_print_footer_scripts', $media, 'load_bg_on_interaction_sctipt', 999 );
		$this->loader->add_filter( 'single_product_archive_thumbnail_size', $media, 'product_custom_mobile_thumb_size_slug' );
		$this->loader->add_filter( 'wp_calculate_image_srcset_meta', $media, 'disable_product_mobile_srcset' );
		if ( '1' === $anofl_options->lazyload_images ) {
			add_filter( 'wp_lazy_loading_enabled', '__return_false' );
		}
		/* ---------------------------End Media------------------------------------------------------------*/

		/* ---------------------------CSS------------------------------------------------------------*/

		$css = new Anony_Flash_Css();
		$this->loader->add_action( 'wp_head', $css, 'load_optimized_css' );
		$this->loader->add_action( 'save_post', $css, 'start_generate_dynamic_css' );
		$this->loader->add_action( 'wp_enqueue_scripts', $css, 'enqueue_generated_css' );
		$this->loader->add_filter( 'style_loader_tag', $css, 'remove_all_stylesheets', 99 );
		if ( 'inject' === $anofl_options->defer_stylesheets_method ) {
			// phpcs:disable
			$this->loader->add_action( 'wp_print_footer_scripts', $css, 'inject_styles', 999 );
			
			// phpcs:enable

			$this->loader->add_filter( 'style_loader_tag', $css, 'to_be_injected_styles', 99 );
		}
		if ( 'media-attribute' === $anofl_options->defer_stylesheets_method ) {
			$this->loader->add_action( 'wp_footer', $css, 'stylesheets_media_to_all', 99 );
			$this->loader->add_filter( 'style_loader_tag', $css, 'stylesheet_media_to_print', 99 );
		}
		/* ---------------------------End CSS------------------------------------------------------------*/

		/* ---------------------------General------------------------------------------------------------*/
		$general = new Anony_Flash_General();
		// Disable google fonts.
		$this->loader->add_filter( 'elementor/frontend/print_google_fonts', $general, 'elementor_google_fonts', 99 );
		// controls add query strings to scripts.
		$this->loader->add_filter( 'script_loader_src', $general, 'anony_control_query_strings', 15, 2 );
		// controls add query strings to styles.
		$this->loader->add_filter( 'style_loader_src', $general, 'anony_control_query_strings', 15, 2 );
		// Use custom avatar instead of Gravatar.com.
		$this->loader->add_filter( 'get_avatar', $general, 'disable_gravatar', 200 );
		$this->loader->add_action( 'template_redirect', $general, 'disable_wp_embeds', 9999 );
		$this->loader->add_action( 'template_redirect', $general, 'disable_wp_emojis', 9999 );
		$this->loader->add_action( 'wp_print_styles', $general, 'disable_gutenburg_scripts', 99 );
		$this->loader->add_action( 'wp_body_open', $general, 'output_preloader' );
		/* ---------------------------End General------------------------------------------------------------*/

		/* ---------------------------Delay------------------------------------------------------------*/
		$delay_js = new Anony_Flash_Delay_Js();
		// Delay js execution.
		$this->loader->add_filter( 'script_loader_tag', $delay_js, 'load_scripts_on_interaction', 99, 3 );
		$this->loader->add_action( 'wp_head', $delay_js, 'inline_defer_js', 10 );
		$this->loader->add_action( 'wp_head', $delay_js, 'defer_gtgm', 30 );
		$this->loader->add_action( 'wp_head', $delay_js, 'defer_facebook_pixel', 30 );
		$this->loader->add_action( 'wp_head', $delay_js, 'defer_inline_external_scripts', 30 );
		/* ---------------------------End Delay------------------------------------------------------------*/

		/* ---------------------------Defer------------------------------------------------------------*/
		$defer_js = new Anony_Flash_Defer_Js();
		// Scripts defer.
		$this->loader->add_filter( 'script_loader_tag', $defer_js, 'defer_scripts', 99, 3 );
		/* ---------------------------End defer------------------------------------------------------------*/

		/* ---------------------------Preload images------------------------------------------------------------*/
		$preload = new Anony_Flash_Preload();
		$this->loader->add_action( 'wp_head', $preload, 'preload_images' );
		$this->loader->add_action( 'wp_head', $preload, 'preload_fonts' );
		$this->loader->add_action( 'wp_head', $preload, 'dns_prefetch' );
		/* ---------------------------End Preload images------------------------------------------------------------*/

		/* --------------------------Scripts------------------------------------------------------------*/
		$scripts = new Anony_Flash_Scripts();
		// Scripts remove.
		$this->loader->add_filter( 'script_loader_tag', $scripts, 'remove_unused_scripts', 99 );
		$this->loader->add_action( 'wp_print_scripts', $scripts, 'dequeue_scripts', 999 );
		$this->loader->add_action( 'wp_default_scripts', $scripts, 'disable_jquery_migrate' );
		$this->loader->add_action( 'wp_head', $scripts, 'anony_add_head_scripts' );
		$this->loader->add_action( 'wp_head', $scripts, 'anony_add_footer_scripts' );
		$this->loader->add_action( 'wp_head', $scripts, 'interaction_events_callback' );
		/* --------------------------End Scripts------------------------------------------------------------*/

		/* --------------------------Styles------------------------------------------------------------*/
		$styles = new Anony_Flash_Styles();
		$this->loader->add_action( 'wp_print_styles', $styles, 'dequeued_styles', 999 );
		$this->loader->add_action( 'wp_enqueue_scripts', $styles, 'disable_dashicons', 999 );
		$this->loader->add_filter( 'style_loader_tag', $styles, 'remove_unused_stylesheets', 99 );
		/* --------------------------End Styles------------------------------------------------------------*/

		/* --------------------------WooCommerce------------------------------------------------------------*/
		$woo = new Anony_Flash_Woocommerce();
		$this->loader->add_action( 'wp_print_styles', $woo, 'load_scripts_on_wc_templates_only' );
		/* --------------------------End WooCommerce------------------------------------------------------------*/

		// Actions..
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'get_header', $plugin_public, 'wp_html_compression_finish' );

		$this->loader->add_action( 'get_header', $plugin_public, 'start_html_buffer' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Anony_Flash_Wp_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
