<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.0
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/admin
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Wp_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'init', array( $this, 'plugin_options' ) );

		// Array of metaboxes to register.
		add_filter( 'anony_metaboxes', array( $this, 'optimize_per_post' ) );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/anony-flash-wp-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/anony-flash-wp-admin.js', array( 'jquery' ), $this->version, false );
	}
	/**
	 * Get user roles
	 *
	 * @return array
	 */
	public function user_roles() {
		$roles = wp_roles()->roles;

		$_roles = array();
		foreach ( $roles as $slug => $role ) {
			$_roles[ $slug ] = $role['name'];
		}

		return $_roles;
	}
	/**
	 * An array of available thumbs
	 *
	 * @return array
	 */
	public function thumbs_sizes() {
		$registered = wp_get_registered_image_subsizes();
		$temp       = array();
		foreach ( $registered as $key => $data ) {
			$temp[ $key ] = $data['width'] . ' x ' . $data['height'];
		}
		return $temp;
	}
	/**
	 * Create plugin's options' page
	 */
	public function plugin_options() {
		if ( ! class_exists( 'ANONY_Options_Model' ) ) {
			return;
		}
		$public_post_types = ANONY_Post_Help::get_post_types_list();

		$args       = array(
			'public' => true,
		);
		$output     = 'names'; // or objects.
		$operator   = 'and';
		$taxonomies = get_taxonomies( $args, $output, $operator );

		$default_optimize_post_types = array();
		$default_optimize_taxonomies = array();

		if ( get_option( 'Anofl_Options' ) ) {
			$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

			$optimize_post_types = $anofl_options->optimize_post_types;

			if ( $optimize_post_types && is_array( $optimize_post_types ) ) {
				$default_optimize_post_types = array_unique( array_merge( $default_optimize_post_types, $optimize_post_types ) );
			}

			$optimize_taxonomies = $anofl_options->optimize_taxonomies;

			if ( $optimize_taxonomies && is_array( $optimize_taxonomies ) ) {
				$default_optimize_taxonomies = array_unique( array_merge( $default_optimize_taxonomies, $optimize_taxonomies ) );
			}
		}

		// Navigation elements.
		$options_nav = array(
			// General --------------------------------------------.
			'general'                 => array(
				'title' => esc_html__( 'General', 'anony-flash-wp' ),
			),

			// Scripts --------------------------------------------.
			'scripts'                 => array(
				'title' => esc_html__( 'Scripts/Styles', 'anony-flash-wp' ),
			),
			// Preloading --------------------------------------------.
			'preloads'                => array(
				'title' => esc_html__( 'Preloading', 'anony-flash-wp' ),
			),
			// Images --------------------------------------------.
			'media'                   => array(
				'title' => esc_html__( 'Media', 'anony-flash-wp' ),
			),

			// Custom Head/Footer scripts --------------------------------------------.
			'custom-scripts'          => array(
				'title'    => esc_html__( 'Custom scripts', 'anony-flash-wp' ),
				'sections' => array( 'custom-scripts', 'external-services' ),
			),

			// post types optimizations --------------------------------------------.
			'post_types_optimization' => array(
				'title'    => esc_html__( 'Optimize per post type', 'anony-flash-wp' ),
				'sections' => array_merge( array( 'post_types_optimization' ), $default_optimize_post_types ),

			),

			// Taxonomies optimizations --------------------------------------------.
			'taxonomies_optimization' => array(
				'title'    => esc_html__( 'Optimize per taxonomy', 'anony-flash-wp' ),
				'sections' => array_merge( array( 'taxonomies_optimization' ), $default_optimize_taxonomies ),

			),

		);

		if ( class_exists( 'woocommerce' ) ) {
			$options_nav['woocommerce'] = array(
				'title' => esc_html__( 'Woocommerce', 'anony-flash-wp' ),
			);
		}

		if ( ANONY_Wp_Plugin_Help::is_active( 'elementor/elementor.php' ) ) {
			$options_nav['elementor'] = array(
				'title' => esc_html__( 'Elementor', 'anony-flash-wp' ),
			);

		}

		$anofl_sections['general'] = array(
			'title'  => esc_html__( 'General', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'       => 'debug_mode',
					'title'    => esc_html__( 'Debug mode', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Optimizations will only be applied if passed debug_mode=1 to the url.', 'anony-flash-wp' ),
				),
				array(
					'id'       => 'excluded_roles',
					'title'    => esc_html__( 'Don\'t optimize for', 'anony-flash-wp' ),
					'type'     => 'checkbox',
					'validate' => 'no_html',
					'options'  => $this->user_roles(),
				),
				array(
					'id'       => 'compress_html',
					'title'    => esc_html__( 'Compress HTML', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Please activate only if you think that GZIP is not enabled on your server.', 'anony-flash-wp' ) . ' <a href="https://www.giftofspeed.com/gzip-test/">' . esc_html__( 'Check gzip compression', 'anony-flash-wp' ) . '</a>',
				),
				array(
					'id'       => 'preloader',
					'title'    => esc_html__( 'Enable preloader', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Enabel or disable page preloader', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'preloader_timeout',
					'title'    => esc_html__( 'Preloader timeout', 'anony-flash-wp' ),
					'type'     => 'number',
					'validate' => 'no_html',
					'default'  => '3000',
					'desc'     => esc_html__( 'Hide preloader after x miliseconds', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'disable_gravatar',
					'title'    => esc_html__( 'Disable gravatar.com', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Stops getting gravatar from gravatar.com', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'disable_embeds',
					'title'    => esc_html__( 'Disable WP embeds', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Disables WP embeds completely', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'enable_singular_embeds',
					'title'    => esc_html__( 'Enable WP embeds on singular', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Enables WP embeds on singular pages (e.g. post/page). Will override (disable WP embeds) option', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'disable_emojis',
					'title'    => esc_html__( 'Disable WP emojis', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Disables WP emojis completely', 'anony-flash-wp' ),
				),
				array(
					'id'       => 'enable_singular_emojis',
					'title'    => esc_html__( 'Enable WP emojis on singular', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Enables WP emojis on singular pages (e.g. post/page). Will override (disable WP emojis) option', 'anony-flash-wp' ),
				),

			),
		);

		$anofl_sections['custom-scripts'] = array(
			'title'  => esc_html__( 'Custom scripts', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'         => 'head_scripts',
					'title'      => esc_html__( 'Head scripts', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					'desc'       => esc_html__( 'Scripts added to this option will applied to the entire site', 'anony-flash-wp' ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),

				array(
					'id'         => 'footer_scripts',
					'title'      => esc_html__( 'Footer scripts', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					'desc'       => esc_html__( 'Scripts added to this option will applied to the entire site', 'anony-flash-wp' ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),
			),
		);

		$anofl_sections['external-services'] = array(
			'title'  => esc_html__( 'External services', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'        => 'gtgm_id',
					'title'     => esc_html__( 'Google tag manager\'s ID', 'anony-flash-wp' ),
					'type'      => 'text',
					'validat e' => 'no_html',
					'desc'      => esc_html__( 'This option will load Google tag manager without affecting page loading speed', 'anony-flash-wp' ),
					'direction' => 'ltr',
				),
				array(
					'id'        => 'gads_id',
					'title'     => esc_html__( 'Google ads ID', 'anony-flash-wp' ),
					'type'      => 'text',
					'validat e' => 'no_html',
					'desc'      => esc_html__( 'This option will load Google ADs without affecting page loading speed', 'anony-flash-wp' ),
					'direction' => 'ltr',
				),

				array(
					'id'        => 'ganalytics_id',
					'title'     => esc_html__( 'Google analytics ID', 'anony-flash-wp' ),
					'type'      => 'text',
					'validat e' => 'no_html',
					'desc'      => esc_html__( 'This option will load Google analytics without affecting page loading speed', 'anony-flash-wp' ),
					'direction' => 'ltr',
				),

				array(
					'id'         => 'gtm_events',
					'title'      => esc_html__( 'GTM Events', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					// translators: An escaped script tag.
					'desc'       => sprintf( __( 'Scripts should be added without the <code>%s</code> tag', 'anony-flash-wp' ), esc_html( '<script>' ) ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),
				array(
					'id'        => 'facebook_pixel_id',
					'title'     => esc_html__( 'Facebook\'s pixel\'s id', 'anony-flash-wp' ),
					'type'      => 'text',
					'validate'  => 'no_html',
					'desc'      => esc_html__( 'This option will load Facebook\'s pixel without affecting page loading speed', 'anony-flash-wp' ),
					'direction' => 'ltr',
				),

				array(
					'id'         => 'external_scripts',
					'title'      => esc_html__( 'External scripts', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					// translators: An escaped script tag.
					'desc'       => sprintf( __( 'Scripts should be added without the <code>%s</code> tag', 'anony-flash-wp' ), esc_html( '<script>' ) ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),

				array(
					'id'         => 'external_scripts_exclusions',
					'title'      => esc_html__( 'Exclude from External scripts', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					// translators: An escaped script tag.
					'desc'       => esc_html__( 'Add full URLs or URL sub-strings to prevent loading external scripts on these URLs. Please add one per line', 'anony-flash-wp' ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),
			),
		);

		$anofl_sections['scripts'] = array(
			'title'  => esc_html__( 'Scripts/Style', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'       => 'query_string',
					'title'    => esc_html__( 'Remove query string', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Removes query string from styles/scripts and help speed up your website', 'anony-flash-wp' ),
				),
				array(
					'id'       => 'keep_query_string',
					'title'    => esc_html__( 'Keep query string', 'anony-flash-wp' ),
					'type'     => 'text',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Add comma separated handles of scripts/styles you want to keep query string', 'anony-flash-wp' ),
				),
				array(
					'id'       => 'defer_stylesheets_method',
					'title'    => esc_html__( 'Defer stylesheets method', 'anony-flash-wp' ),
					'type'     => 'radio',
					'options'  => array(

						'media-attribute' => array(
							'title' => esc_html__( 'Media attribute', 'anony-flash-wp' ),
						),

						'inject'          => array(
							'title' => esc_html__( 'Inject', 'anony-flash-wp' ),
						),

					),
					'default'  => 'inject',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'When choose (Media attribute), this will change the media attibute value to print, then later will be replaced back with all. But if you choos Inject, this will inject stylesheets later on (user interact/Window load)', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'load_stylesheets_on',
					'title'    => esc_html__( 'load deferred stylesheets on', 'anony-flash-wp' ),
					'type'     => 'radio',
					'options'  => array(

						'load'     => array(
							'title' => esc_html__( 'Window loaded', 'anony-flash-wp' ),
						),

						'interact' => array(
							'title' => esc_html__( 'User interaction', 'anony-flash-wp' ),
						),

					),
					'default'  => 'load',
					'validate' => 'no_html',
					'desc'     => __( 'When choose <code>Window loaded</code>, this will load deferred stylesheets after window is loaded, but <code>user interaction</code> will load them once user interacts with the page', 'anony-flash-wp' ),
				),

				array(
					'id'         => 'deferred_styles',
					'title'      => esc_html__( 'Deferred styles', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'no_html',
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
					'desc'       => esc_html__( 'Help to improve eliminate render-blocking resources.', 'anony-flash-wp' ),
					'note'       => esc_html__( 'Please add one handle per line', 'anony-flash-wp' ),
				),
				array(
					'id'         => 'dequeued_styles',
					'title'      => esc_html__( 'Dequeued styles', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'no_html',
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
					'desc'       => esc_html__( 'Stop loading unneccessary styles', 'anony-flash-wp' ),
				),
				array(
					'id'       => 'defer_scripts',
					'title'    => esc_html__( 'Defer scripts loading', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Improves First content paint, and get higher score on page speed insights.', 'anony-flash-wp' ),
				),
				array(
					'id'         => 'not_to_be_defered_scripts',
					'title'      => esc_html__( 'Do not defer these files', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					// translators: An escaped script tag.
					'desc'       => esc_html__( 'Add full URLs or URL sub-strings to prevent scripts defer on these URLs. Please add one per line', 'anony-flash-wp' ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),
				array(
					'id'       => 'load_scripts_on_interaction',
					'title'    => esc_html__( 'Delay javascript execution', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => __( 'This will delay javascript execution until user interacts.<code>jQuery</code> will not be delayed', 'anony-flash-wp' ),
				),

				array(
					'id'         => 'delay_scripts_exclusions',
					'title'      => esc_html__( 'Exclude from scripts delay', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'validate'   => 'html',
					// translators: An escaped script tag.
					'desc'       => esc_html__( 'Add full URLs or URL sub-strings to prevent scripts delay on these URLs. Please add one per line', 'anony-flash-wp' ),
					'text-align' => 'left',
					'rows'       => '10',
					'columns'    => '60',
					'direction'  => 'ltr',
				),
				array(
					'id'       => 'disable_gutenburg_scripts',
					'title'    => esc_html__( 'Disable Gutenburg editor scripts', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'If your using classic editor, enable this to remove unwanted Gutenburg\'s editor scripts', 'anony-flash-wp' ),
				),

				array(
					'id'       => 'disable_jq_migrate',
					'title'    => esc_html__( 'Disable jquery migrate', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'This will prevent the jQuery Migrate script from being loaded on the front end while keeping the jQuery script itself intact. It\'s still being loaded in the admin to not break anything there.)', 'anony-flash-wp' ),
				),
			),
		);

		// If contact form 7 is acive.
		if ( defined( 'WPCF7_PLUGIN' ) ) {
			$anofl_sections['scripts']['fields'][] = array(
				'id'       => 'cf7_scripts',
				'title'    => esc_html__( 'Contact form 7 scripts/styles', 'anony-flash-wp' ),
				'type'     => 'select2',
				'multiple' => true,
				'options'  => ANONY_Post_Help::queryPostTypeSimple( 'page' ),
				'validate' => 'multiple_options',
				'desc'     => esc_html__( 'Choose your contact form page, so cf7 styles/scripts will only be loaded in this page', 'anony-flash-wp' ),
			);

		}
		$anofl_sections['preloads'] = array(
			'title'  => esc_html__( 'Preloads', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'         => 'preload_fonts',
					'title'      => esc_html__( 'Preload fonts', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'columns'    => '70',
					'rows'       => '8',
					'validate'   => 'no_html',
					'text-align' => 'left',
					'desc'       => esc_html__( 'Help to improve CLS. Please add a URL perline.', 'anony-flash-wp' ),
				),

				array(
					'id'         => 'preload_images',
					'title'      => esc_html__( 'Preload images', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'columns'    => '70',
					'rows'       => '8',
					'validate'   => 'no_html',
					'text-align' => 'left',
					'desc'       => esc_html__( 'Help to improve largest content paint.Please add a URL perline.', 'anony-flash-wp' ),
				),

				array(
					'id'         => 'dns_prefetch',
					'title'      => esc_html__( 'Prefetch DNS Requests', 'anony-flash-wp' ),
					'type'       => 'textarea',
					'columns'    => '70',
					'rows'       => '8',
					'validate'   => 'no_html',
					'text-align' => 'left',
					'desc'       => __( 'DNS prefetching can make external files load faster, especially on mobile networks. Please add a URL per line without <code>http:</code>', 'anony-flash-wp' ),
				),
			),
		);

		$anofl_sections['media'] = array(
			'title'  => esc_html__( 'Media', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'       => 'add_missing_image_dimensions',
					'title'    => esc_html__( 'Optimize images', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'This will by default add width and height to imgs, but if you want to lazyload image, Please enable the lazyload option below.', 'anony-flash-wp' ),

				),

				array(
					'id'       => 'lazyload_images',
					'title'    => esc_html__( 'Lazyload images', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Please enable optimize images first. This will lazyload of screen images only.', 'anony-flash-wp' ),
				),
				array(
					'id'       => 'full_lazyload_images',
					'title'    => esc_html__( 'Fully Lazyload images', 'anony-flash-wp' ),
					'type'     => 'switch',
					'validate' => 'no_html',
					'desc'     => esc_html__( 'Please enable optimize images first and Lazyload images. This will force lazyload all images.', 'anony-flash-wp' ),
				),
			),
		);
		$anofl_sections['media']['fields'][] = array(
			'id'       => 'interact_lazyload_this_classes',
			'title'    => esc_html__( 'Load backgrounds on interaction', 'anony-flash-wp' ),
			'type'     => 'textarea',
			'validate' => 'no_html',
			'desc'     => esc_html__( 'Add classes of the elements with backgrounds you want to load only on user interaction', 'anony-flash-wp' ),
		);

		if ( ANONY_Wp_Plugin_Help::is_active( 'elementor/elementor.php' ) ) {
			$anofl_sections['media']['fields'][] = array(
				'id'       => 'lazyload_elementor_backgrounds',
				'title'    => esc_html__( 'lazyload elementor\'s backgrounds', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
			);
			$anofl_sections['media']['fields'][] = array(
				'id'       => 'lazyload_this_classes',
				'title'    => esc_html__( 'lazyload elements with the following classes', 'anony-flash-wp' ),
				'type'     => 'textarea',
				'validate' => 'no_html',
				'default'  => 'elementor-section
elementor-column-wrap
elementor-widget-wrap
elementor-widget-container
elementor-background-overlay
anony-lazyload-bg',
			);
			$anofl_sections['media']['fields'][] = array(
				'id'       => 'lazyloading_elementor_bg_method',
				'title'    => esc_html__( 'lazyloading elementor\'s backgrounds\s method', 'anony-flash-wp' ),
				'type'     => 'radio',
				'validate' => 'no_html',
				'options'  => array(

					'with_jquery'    => array(
						'title' => esc_html__( 'Using jquery/waypoint', 'anony-flash-wp' ),
					),

					'without_jquery' => array(
						'title' => esc_html__( 'Without jquery', 'anony-flash-wp' ),
					),

				),
				'default'  => 'with_jquery',
			);
		}
		if ( class_exists( 'woocommerce' ) ) {
			$anofl_sections['woocommerce'] = array(
				'title'  => esc_html__( 'Woocommerce', 'anony-flash-wp' ),
				'icon'   => 'x',
				'fields' => array(
					array(
						'id'       => 'wc_shop_only_scripts',
						'title'    => esc_html__( 'Woocommerce shop only scripts/styles', 'anony-flash-wp' ),
						'type'     => 'switch',
						'validate' => 'no_html',
						'desc'     => esc_html__( 'Only allow woocommerce scripts/styles on shop related pages (e.g. product, cart and checkout pages)', 'anony-flash-wp' ),
					),
					array(
						'id'       => 'wc_disable_srcset',
						'title'    => esc_html__( 'Disable srcset meta', 'anony-flash-wp' ),
						'type'     => 'switch',
						'validate' => 'no_html',
						'desc'     => esc_html__( 'Sometimes you may need to disable srcsets on mobile if you need to set the image size manually on mobile devices. Use the option below to set product thumbnail size on mobile' ),
					),
					array(
						'id'       => 'wc_mobile_thumb_size',
						'title'    => esc_html__( 'Product thumnbnail size on mobile', 'anony-flash-wp' ),
						'type'     => 'select',
						'options'  => $this->thumbs_sizes(),
						'validate' => 'no_html',
					),
				),
			);
		}

		if ( ANONY_Wp_Plugin_Help::is_active( 'elementor/elementor.php' ) ) {

			$anofl_sections['elementor'] = array(
				'title'  => esc_html__( 'Elementor', 'anony-flash-wp' ),
				'icon'   => 'x',
				'fields' => array(
					array(
						'id'       => 'disable_elementor_google_fonts',
						'title'    => esc_html__( 'Disable google fonts', 'anony-flash-wp' ),
						'type'     => 'switch',
						'validate' => 'no_html',
					),
				),
			);
		}

		$anofl_sections['post_types_optimization'] = array(
			'title'  => esc_html__( 'Optimize post types', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'       => 'optimize_post_types',
					'title'    => esc_html__( 'Optimize selected post types', 'anony-flash-wp' ),
					'type'     => 'checkbox',
					'validate' => 'no_html',
					'options'  => $public_post_types,
				),
			),
		);

		foreach ( $default_optimize_post_types as $default_optimize_post_type ) {
			$anofl_sections[ $default_optimize_post_type ] = array(
				'title'  => $public_post_types[ $default_optimize_post_type ],
				'icon'   => 'x',
				'fields' => $this->optimization_fields( '_' . $default_optimize_post_type ),
			);
		}

		$anofl_sections['taxonomies_optimization'] = array(
			'title'  => esc_html__( 'Optimize taxonomies', 'anony-flash-wp' ),
			'icon'   => 'x',
			'fields' => array(
				array(
					'id'       => 'optimize_taxonomies',
					'title'    => esc_html__( 'Optimize selected taxonomies', 'anony-flash-wp' ),
					'type'     => 'checkbox',
					'validate' => 'no_html',
					'options'  => $taxonomies,
				),
			),
		);

		foreach ( $default_optimize_taxonomies as $default_optimize_taxonomy ) {
			$anofl_sections[ $default_optimize_taxonomy ] = array(
				'title'  => $default_optimize_taxonomy,
				'icon'   => 'x',
				'fields' => $this->optimization_fields( '_' . $default_optimize_taxonomy ),
			);
		}

		$anofl_options_page['opt_name']      = 'Anofl_Options';
		$anofl_options_page['menu_title']    = esc_html__( 'Flash WP', 'anony-flash-wp' );
		$anofl_options_page['page_title']    = esc_html__( 'Flash WP', 'anony-flash-wp' );
		$anofl_options_page['menu_slug']     = 'Anofl_Options';
		$anofl_options_page['page_cap']      = 'manage_options';
		$anofl_options_page['icon_url']      = 'dashicons-performance';
		$anofl_options_page['page_position'] = 100;
		$anofl_options_page['page_type']     = 'menu';

		new ANONY_Theme_Settings( $options_nav, $anofl_sections, array(), $anofl_options_page );
	}

	/**
	 * Optimiztion fields
	 *
	 * @param string $suffix Input fields id/name suffix.
	 * @return array
	 */
	public function optimization_fields( $suffix = '' ) {
		return array(
			array(
				'id'          => 'css' . $suffix,
				'title'       => 'CSS',
				'collapsible' => true,
				'type'        => 'group_start',
			),
			array(
				'id'       => 'enable_used_css' . $suffix,
				'title'    => esc_html__( 'Enable used css', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
				'desc'     => esc_html__( 'Enabling this will disable all stylesheets of this page/post and will replace them with used css that you will add below', 'anony-flash-wp' ),

			),
			array(
				'id'       => 'external_used_css' . $suffix,
				'title'    => esc_html__( 'load used css externaly', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
				'desc'     => esc_html__( 'Enabling this will generate a stylesheet for this specific page', 'anony-flash-wp' ),

			),

			array(
				'id'         => 'desktop_used_css' . $suffix,
				'title'      => esc_html__( 'Desktop\'s Used css', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'html',
				'desc'       => __( 'Add css used in this page/post. CSS should be added without <code>style</code> tag.', 'anony-flash-wp' ),
				'note'       => esc_html__( 'Works only on desktop\'s version', 'anony-flash-wp' ),
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
			),

			array(
				'id'       => 'separate_mobile_used_css' . $suffix,
				'title'    => esc_html__( 'Separate mobile used css', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
				'desc'     => esc_html__( 'Enable this if you need to load separate used css for mobile', 'anony-flash-wp' ),

			),

			array(
				'id'         => 'mobile_used_css' . $suffix,
				'title'      => esc_html__( 'Mobile\'s Used css', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'html',
				'desc'       => __( 'Add css used in this page/post. CSS should be added without <code>style</code> tag.', 'anony-flash-wp' ),
				'note'       => esc_html__( 'Works only on mobile\'s version', 'anony-flash-wp' ),
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
			),
			array(
				'id'   => 'group_close_css',
				'type' => 'group_close',
			),
			array(
				'id'          => 'images-preload',
				'title'       => 'Images optimizations',
				'collapsible' => true,
				'type'        => 'group_start',
			),
			array(
				'id'       => 'full_lazyload_images' . $suffix,
				'title'    => esc_html__( 'Full Lazy load images', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
			),
			array(
				'id'         => 'preload_desktop_images' . $suffix,
				'title'      => esc_html__( 'Images to preload on desktop', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Help to improve largest content paint.Please add a URL perline.', 'anony-flash-wp' ),
			),

			array(
				'id'         => 'preload_mobile_images' . $suffix,
				'title'      => esc_html__( 'Images to preload on mobile', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Help to improve largest content paint.Please add a URL perline.', 'anony-flash-wp' ),
			),
			array(
				'type' => 'group_close',
				'id'   => 'group_close_images-preload',
			),
			array(
				'id'          => 'styles-files',
				'title'       => 'Styles\' files',
				'collapsible' => true,
				'type'        => 'group_start',
			),
			array(
				'id'         => 'deferred_styles' . $suffix,
				'title'      => esc_html__( 'Deferred styles', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Help to improve eliminate render-blocking resources.', 'anony-flash-wp' ),
				'note'       => esc_html__( 'Please add one handle per line', 'anony-flash-wp' ),
			),
			array(
				'id'         => 'dequeued_styles' . $suffix,
				'title'      => esc_html__( 'Dequeued styles', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Stop loading unneccessary styles', 'anony-flash-wp' ),
				'note'       => esc_html__( 'Please add one handle per line', 'anony-flash-wp' ),
			),

			array(
				'id'         => 'unloaded_css' . $suffix,
				'title'      => esc_html__( 'Unload css files', 'anony-flash-wp' ),
				'type'       => 'checkbox',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Select files you need to unload on frontend of this page', 'anony-flash-wp' ),
				'options'    => ANONY_Wp_Misc_Help::list_post_stylesheets(),
			),

			array(
				'type' => 'group_close',
				'id'   => 'group_close_styles-files',
			),
			array(
				'id'          => 'scripts-files',
				'title'       => 'Scripts\'s files',
				'collapsible' => true,
				'type'        => 'group_start',
			),

			array(
				'id'       => 'delay_js' . $suffix,
				'title'    => esc_html__( 'Delay js files loading', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
				'desc'     => esc_html__( 'Turn this on to delay js loading for this page', 'anony-flash-wp' ),
			),

			array(
				'id'       => 'unloaded_js' . $suffix,
				'title'    => esc_html__( 'Unload js files', 'anony-flash-wp' ),
				'type'     => 'checkbox',
				'validate' => 'no_html',
				'desc'     => esc_html__( 'Select files you need to unload on frontend of this page', 'anony-flash-wp' ),
				'options'  => ANONY_Wp_Misc_Help::list_post_scripts(),
			),
			array(
				'type' => 'group_close',
				'id'   => 'group_close_scripts-files',

			),

			array(
				'id'          => 'load-css-asynchronously',
				'title'       => 'Load CSS asynchronously',
				'collapsible' => true,
				'type'        => 'group_start',
			),
			array(
				'id'       => 'above_the_fold_styles' . $suffix,
				'title'    => esc_html__( 'Enable above the fold styles', 'anony-flash-wp' ),
				'type'     => 'switch',
				'validate' => 'no_html',
				'desc'     => esc_html__( 'Usefull for first content paint', 'anony-flash-wp' ),
				'note'     => esc_html__( 'Shouldn\'t be used if you enabled the used css option.', 'anony-flash-wp' ),
			),

			array(
				'id'         => 'desktop_above_fold_css' . $suffix,
				'title'      => esc_html__( 'Desktop\'s above the fold css', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Please add your above the fold css for desktop', 'anony-flash-wp' ),
			),
			array(
				'id'         => 'mobile_above_fold_css' . $suffix,
				'title'      => esc_html__( 'Mobile\'s above the fold css', 'anony-flash-wp' ),
				'type'       => 'textarea',
				'validate'   => 'no_html',
				'text-align' => 'left',
				'rows'       => '10',
				'columns'    => '60',
				'direction'  => 'ltr',
				'desc'       => esc_html__( 'Please add your above the fold css for mobile', 'anony-flash-wp' ),
			),
			array(
				'type' => 'group_close',
				'id'   => 'group_close_load-css-asynchronously',
			),
		);
	}
	/**
	 * Create optimization's metabox. By default for page and post post types.
	 *
	 * @param array $metaboxes An array of metaboxes.
	 * @return array An array of metaboxes.
	 */
	public function optimize_per_post( $metaboxes ) {
		$metaboxes[] =
		array(
			'id'            => 'optimize_per_post', // Meta box ID.
			'title'         => esc_html__( 'Optimize this page/post', 'anony-flash-wp' ),
			'context'       => 'normal',
			'priority'      => 'high', // high|low.
			'hook_priority' => '10', // Default 10.
			'post_type'     => apply_filters( 'optimize_per_post_types', array( 'post', 'page', 'product' ) ),
			'layout'        => 'tabs',
			'fields'        => $this->optimization_fields(),

		);

		return $metaboxes;
	}
}
