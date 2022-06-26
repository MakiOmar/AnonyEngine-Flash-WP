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
	 * - Anony_Flash_Wp_i18n. Defines internationalization functionality.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-anony-flash-wp-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-anony-flash-wp-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-anony-flash-wp-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-anony-flash-wp-public.php';

		$this->loader = new Anony_Flash_Wp_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Anony_Flash_Wp_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Anony_Flash_Wp_i18n();

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

		$plugin_public = new Anony_Flash_Wp_Public( $this->get_plugin_name(), $this->get_version() );

		// Actions.
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'lazy_elementor_background_images_js', 999 );

		$this->loader->add_action( 'wp_print_styles', $plugin_public, 'dequeued_styles', 999 );

		$this->loader->add_action( 'wp_print_scripts', $plugin_public, 'dequeue_scripts', 999 );

		$this->loader->add_action( 'wp_print_footer_scripts', $plugin_public, 'inject_scripts', 999 );

		$this->loader->add_action( 'wp_head', $plugin_public, 'lazy_elementor_background_images_css' );

		$this->loader->add_action( 'wp_default_scripts', $plugin_public, 'deregister_jquery_migrate' );

		$this->loader->add_action( 'template_redirect', $plugin_public, 'disable_wp_embeds' );

		$this->loader->add_action( 'template_redirect', $plugin_public, 'disable_wp_emojis' );

		// Filters.
		$this->loader->add_filter( 'the_content', $plugin_public, 'elementor_add_lazyload_class' );

		// Add missing image dimensions
		$this->loader->add_filter( 'the_content', $plugin_public, 'add_missing_image_Dimensions' );

		//Remove query strings to scripts
		$this->loader->add_filter('script_loader_src', $plugin_public,'remove_query_strings', 99, 2);

		//Remove add query strings to styles
		$this->loader->add_filter('style_loader_src', $plugin_public, 'remove_query_strings', 99, 2);

		// Defer js scripts
		$this->loader->add_filter('script_loader_tag', $plugin_public, 'defer_scripts', 99, 3);

		if( wp_is_mobile() ){
			$this->loader->add_filter('style_loader_tag', $plugin_public, 'mobile_injected_scripts', 99, 3);
		}
		

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
