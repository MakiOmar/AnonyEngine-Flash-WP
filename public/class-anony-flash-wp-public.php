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
class Anony_Flash_Wp_Public {


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

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/anony-flash-wp-public.css', array(), $this->version, 'all' );
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

		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/anony-flash-wp-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Callback for ob_start
	 *
	 * @param  string $html
	 * @return string
	 */
	public function wp_html_compression_finish_cb( $html ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( $anofl_options->compress_html != 1 ) {
			return $html;
		}

		return new ANONY_Wp_Html_Compression( $html );
	}

	/**
	 * Hooked to get_header
	 */
	public function wp_html_compression_finish() {
		// From PHP reference: ob_start(callable $callback = null, int $chunk_size = 0, int $flags = PHP_OUTPUT_HANDLER_STDFLAGS): bool
		// When callback is called, it will receive the contents of the output buffer as its parameter and is expected to return a new output buffer as a result, which will be sent to the browser.
		ob_start( array( $this, 'wp_html_compression_finish_cb' ) );
	}


	/**
	 * Controls add query strings to scripts/styles
	 */
	public function anony_control_query_strings( $src, $handle ) {
		if ( is_admin() ) {
			return $src;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		// Keep query string for these items
		$neglected = array();

		if ( ! empty( $anofl_options->keep_query_string ) ) {
			$neglected = explode( ',', $anofl_options->keep_query_string );
		}

		if ( $anofl_options->query_string != '0' && ! in_array( $handle, $neglected ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;

	}
	/**
	 * Defer stylesheet
	 */
	public function defer_stylesheets( $tag ) {

		if ( is_admin() ) {
			return $tag;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( $anofl_options->defer_stylesheets !== '1' || false !== strpos( $tag, 'anony-main' ) || false !== strpos( $tag, 'anony-theme-styles' ) || false !== strpos( $tag, 'anony-responsive' ) ) {
			return $tag;
		}
		$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );

		return $tag;
	}

	/**
	 * Defer scripts
	 */
	public function defer_scripts( $tag, $handle, $src ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( is_admin() || $anofl_options->defer_scripts !== '1' ) {
			return $tag; // don't break WP Admin
		}

		if ( false === strpos( $src, '.js' ) ) {
			return $tag;
		}

		if ( false !== strpos( $tag, 'defer' ) ) {
			return $tag;
		}

		// if ( strpos( $src, 'wp-includes/js' ) ) return $tag; //Exclude all from w-includes

		// Try not defer all
		$not_deferred = array(
			'syntaxhighlighter-core',
			'jquery-core',
			'wp-polyfill',
			'wp-hooks',
			'wp-i18n',
			'wp-tinymce-root',
		);
		if ( in_array( $handle, $not_deferred ) ) {
			return $tag;
		}
		return str_replace( ' src', ' defer src', $tag );
	}

	public function disable_gravatar( $avatar ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! $anofl_options->disable_gravatar || $anofl_options->disable_gravatar != '1' ) {
			return $avatar;
		}

		$avatar = '<img src="#" width="48" height="48"/>';

		return $avatar;
	}

	public function disable_wp_embeds() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$keep = true;

		if ( $anofl_options->disable_embeds == '1' ) {
			$keep = false;
		}

		if ( $anofl_options->enable_singular_embeds == '1' && is_single() ) {
			$keep = true;
		}

		if ( $keep ) {
			return;
		}

		// Remove the REST API endpoint.
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );

		// Turn off oEmbed auto discovery.
		add_filter( 'embed_oembed_discover', '__return_false' );

		// Don't filter oEmbed results.
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

		// Remove oEmbed discovery links.
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Remove oEmbed-specific JavaScript from the front-end and back-end.
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		add_filter(
			'tiny_mce_plugins',
			function ( $plugins ) {
				return array_diff( $plugins, array( 'wpembed' ) );
			}
		);

		// Remove all embeds rewrite rules.
		add_filter(
			'rewrite_rules_array',
			function ( $rules ) {
				foreach ( $rules as $rule => $rewrite ) {
					if ( false !== strpos( $rewrite, 'embed=true' ) ) {
						unset( $rules[ $rule ] );
					}
				}
				return $rules;
			}
		);

		// Remove filter of the oEmbed result before any HTTP requests are made.
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}

	public function disable_wp_emojis() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$keep = true;

		if ( $anofl_options->disable_emojis == '1' ) {
			$keep = false;
		}

		if ( $anofl_options->enable_singular_emojis == '1' && is_single() ) {
			$keep = true;
		}

		if ( $keep ) {
			return;
		}

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		remove_action( 'admin_print_styles', 'print_emoji_styles' );
		remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
		remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
		remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );

		/**
		 * Filter function used to remove the tinymce emoji plugin.
		 *
		 * @param  array $plugins
		 * @return array Difference betwen the two arrays
		 */
		add_filter(
			'tiny_mce_plugins',
			function ( $plugins ) {

				return ( is_array( $plugins ) ) ? array_diff( $plugins, array( 'wpemoji' ) ) : array();
			}
		);

		/**
		 * Remove emoji CDN hostname from DNS prefetching hints.
		 *
		 * @param  array $urls URLs to print for resource hints.
		 * @param  string $relation_type The relation type the URLs are printed for.
		 * @return array Difference betwen the two arrays.
		 */
		add_filter(
			'wp_resource_hints',
			function ( $urls, $relation_type ) {
				if ( 'dns-prefetch' == $relation_type ) {
					/**
					* This filter is documented in wp-includes/formatting.php
					  */
					$emoji_svg_url = apply_filters( 'emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/' );

					$urls = array_diff( $urls, array( $emoji_svg_url ) );
				}

				return $urls;
			},
			10,
			2
		);
	}

	public function disable_gutenburg_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$dequeued_styles = array();

		if ( $anofl_options->disable_gutenburg_scripts == '1' ) {
			$dequeued_styles = array_merge( $dequeued_styles, array( 'wp-block-library', 'wp-block-library-theme', 'wc-block-style' ) );
		}

		foreach ( $dequeued_styles as $style ) {
			wp_dequeue_style( $style );
			wp_deregister_style( $style );
		}
	}

	/**
	 * This will prevent the jQuery Migrate script from being loaded on the front end while keeping the jQuery script itself intact. It's still being loaded in the admin to not break anything there.
	 */
	public function disable_jquery_migrate( $scripts ) {

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( $anofl_options->disable_jq_migrate != '1' ) {
			return;
		}

		if ( ! is_admin() && ! empty( $scripts->registered['jquery'] ) ) {
			$scripts->registered['jquery']->deps = array_diff(
				$scripts->registered['jquery']->deps,
				array( 'jquery-migrate' )
			);
		}
	}

	public function preload_fonts() {

		if ( ! class_exists( 'ANONY_STRING_HELP' ) ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->preload_fonts ) ) {
			$arr = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->preload_fonts );

			if ( ! is_array( $arr ) ) {
				return;
			}

			foreach ( $arr as $line ) { ?>
					<link rel="preload" href="<?php echo $line; ?>" as="font" type="font/woff2" crossorigin>
				<?php
			}
		}
	}

	public function preload_images() {

		if ( ! class_exists( 'ANONY_STRING_HELP' ) ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$arr = [];
		if ( ! empty( $anofl_options->preload_images ) ) {
			$arr = array_merge( $arr, ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->preload_images ) );
		}

		global $post;

		if ( $post && !is_null( $post ) ) {
			if ( !wp_is_mobile() ) {
				$key = 'preload_desktop_images';
			}else{
				$key = 'preload_mobile_images';
			}

			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			if ( !empty( $optimize_per_post ) && !empty( $optimize_per_post[ $key ] )) {
				$arr = array_merge( $arr, ANONY_STRING_HELP::line_by_line_textarea( $optimize_per_post[ $key ] ) );
			}
		}

		if ( ! is_array( $arr ) || empty( $arr ) ) {
			return;
		}
		foreach ( $arr as $line ) {
			?>
					 
					<link rel="preload" as="image" href="<?php echo $line; ?>"/>

			<?php
		}
	}


	public function dns_prefetch() {

		if ( ! class_exists( 'ANONY_STRING_HELP' ) ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->dns_prefetch ) ) {
			$arr = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->dns_prefetch );

			if ( ! is_array( $arr ) ) {
				return;
			}

			foreach ( $arr as $line ) {
				?>
							<link rel="preconnect" href="//<?php echo esc_url( $line ); ?>">
							<link rel="dns-prefetch" href="//<?php echo esc_url( $line ); ?>">
				<?php
			}
		}
	}

	/**
	 * Inlines the defer.js. (https://github.com/shinsenter/defer.js)
	 * A JavaScript micro-library that helps you lazy load (almost) anything. Defer.js is zero-dependency, super-efficient, and Web Vitals friendly.
	 */
	public function inline_defer_js() {
		?>
		<script type="text/javascript">
			/*!@shinsenter/defer.js@3.4.0*/
			!(function(n){function t(e){n.addEventListener(e,B)}function o(e){n.removeEventListener(e,B)}function u(e,n,t){L?C(e,n):(t||u.lazy&&void 0===t?q:S).push(e,n)}function c(e){k.head.appendChild(e)}function i(e,n){z.call(e.attributes)[y](n)}function r(e,n,t,o){return o=(n?k.getElementById(n):o)||k.createElement(e),n&&(o.id=n),t&&(o.onload=t),o}function s(e,n,t){(t=e.src)&&((n=r(m)).rel="preload",n.as=h,n.href=t,(t=e[g](w))&&n[b](w,t),(t=e[g](x))&&n[b](x,t),c(n))}function a(e,n){return z.call((n||k).querySelectorAll(e))}function f(e,n){e.parentNode.replaceChild(n,e)}function l(t,e){a("source,img",t)[y](l),i(t,function(e,n){(n=/^data-(.+)/.exec(e.name))&&t[b](n[1],e.value)}),"string"==typeof e&&e&&(t.className+=" "+e),p in t&&t[p]()}function e(e,n,t){u(function(t){(t=a(e||N))[y](s),(function o(e,n){(e=t[E]())&&((n=r(e.nodeName)).text=e.text,i(e,function(e){"type"!=e.name&&n[b](e.name,e.value)}),n.src&&!n[g]("async")?(n.onload=n.onerror=o,f(e,n)):(f(e,n),o()))})()},n,t)}var d="Defer",m="link",h="script",p="load",v="pageshow",y="forEach",g="getAttribute",b="setAttribute",E="shift",w="crossorigin",x="integrity",A=["mousemove","keydown","touchstart","wheel"],I="on"+v in n?v:p,N=h+"[type=deferjs]",j=n.IntersectionObserver,k=n.document||n,C=n.setTimeout,L=/p/.test(k.readyState),S=[],q=[],z=S.slice,B=function(e,n){for(n=I==e.type?(o(I),L=u,A[y](t),S):(A[y](o),q);n[0];)C(n[E](),n[E]())};e(),u.all=e,u.dom=function(e,n,i,c,r){u(function(t){function o(e){c&&!1===c(e)||l(e,i)}t=!!j&&new j(function(e){e[y](function(e,n){e.isIntersecting&&(t.unobserve(n=e.target),o(n))})},r),a(e||"[data-src]")[y](function(e){e[d]!=u&&(e[d]=u,t?t.observe(e):o(e))})},n,!1)},u.css=function(n,t,e,o,i){u(function(e){(e=r(m,t,o)).rel="stylesheet",e.href=n,c(e)},e,i)},u.js=function(n,t,e,o,i){u(function(e){(e=r(h,t,o)).src=n,c(e)},e,i)},u.reveal=l,n[d]=u,L||t(I)})(this);
			 
			 
		</script>
		<?php
	}

	/**
	 * Load Google tag manager deferred depending on defer.js
	 */
	public function defer_gtgm() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->gtgm_id ) ) {
			?>
			<script>
				var GTM_ID = '<?php echo $anofl_options->gtgm_id; ?>';
				window.dataLayer = window.dataLayer || [];
				dataLayer.push(['js', new Date()]);
				dataLayer.push(['config', GTM_ID]);

				Defer.js('https://www.googletagmanager.com/gtag/js?id=' + GTM_ID, 'google-tag', 0, function() {
				console.info('Google Tag Manager is loaded.'); // debug
			}, true);
		</script>
			<?php
		}
	}

	/**
	 * Load Facebook pixel deferred depending on defer.js
	 */
	public function defer_facebook_pixel() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->facebook_pixel_id ) ) {
			?>
			<!-- Meta Pixel Code -->
			<script type="anony-facebook-pixel">

				!function(f,b,e,v,n,t,s)
				{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
				n.callMethod.apply(n,arguments):n.queue.push(arguments)};
				if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
				n.queue=[];t=b.createElement(e);t.async=!0;
				t.src=v;s=b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t,s)}(window, document,'script',
				'https://connect.facebook.net/en_US/fbevents.js');
				fbq('init', '<?php echo $anofl_options->facebook_pixel_id; ?>');
				fbq('track', 'PageView');

			</script>

			<noscript>
				<img height="1" width="1" style="display:none"
				src="https://www.facebook.com/tr?id=<?php echo $anofl_options->facebook_pixel_id; ?>&ev=PageView&noscript=1"
				/>
			</noscript>
			<!-- End Meta Pixel Code -->

			<script>
				Defer.all('script[type="anony-facebook-pixel"]', 5000);
			</script>
			<?php
		}
	}

	/**
	 * Load external inline scripts deferred depending on defer.js
	 */
	public function defer_inline_external_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->external_scripts ) ) {
			?>
			<script type="anony-external-scripts">
			<?php echo $anofl_options->external_scripts; ?>
			</script>

			<script>
				Defer.all('script[type="anony-external-scripts"]', 5000);
			</script>
			<?php
		}
	}

	/**
	 * Disable All WooCommerce  Styles
	 */
	public function dequeue_wc_style() {
		$woo_styles = array(
			'woocommerce-general',
			'woocommerce-general-rtl',
			'woocommerce-layout',
			'woocommerce-layout-rtl',
			'woocommerce-smallscreen',
			'woocommerce-smallscreen-rtl',
			'woocommerce_frontend_styles',
			'woocommerce_fancybox_styles',
			'woocommerce_chosen_styles',
			'woocommerce_prettyPhoto_css',
			'wc-blocks-vendors-style',
			'wc-blocks-style-rtl',

		);

		foreach ( $woo_styles as $style ) {
			wp_deregister_style( $style );
			wp_dequeue_style( $style );

		}
	}

	/**
	 * Disable All WooCommerce Scripts
	 */
	function dequeue_wc_scripts() {
		$woo_scripts = array(
			'wc_price_slider',
			'wc-single-product',
			'wc-add-to-cart',
			'wc-cart-fragments',
			'wc-checkout',
			'wc-add-to-cart-variation',
			'wc-single-product',
			'wc-cart',
			'wc-chosen',
			'woocommerce',
			'prettyPhoto',
			'prettyPhoto-init',
			'jquery-blockui',
			'jquery-placeholder',
			'fancybox',
			'jqueryui',
		);

		foreach ( $woo_scripts as $script ) {
			wp_deregister_script( $script );
			wp_dequeue_script( $script );

		}
	}

	/**
	 * Disable All WooCommerce  Styles and Scripts Except Shop Pages
	 */
	public function load_scripts_on_wc_templates_only() {
		if ( is_admin() ) {
			return;
		}
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( $anofl_options->wc_shop_only_scripts != 1 ) {
			return;
		}
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
				dequeue_wc_style();
				dequeue_wc_scripts();
			}
		}
	}


	public function load_styles_on_cf7_pages_only() {

		global $post;

		if ( ! $post || is_null( $post ) ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( is_array( $anofl_options->cf7_scripts ) && ! in_array( $post->ID, $anofl_options->cf7_scripts ) ) {
			wp_dequeue_style( 'contact-form-7' );
			wp_deregister_style( 'contact-form-7' );

			wp_dequeue_style( 'contact-form-7-rtl' );
			wp_deregister_style( 'contact-form-7-rtl' );
		}

	}

	public function load_scripts_on_cf7_pages_only() {

		global $post;

		if ( ! $post || is_null( $post ) ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( is_array( $anofl_options->cf7_scripts ) && ! in_array( $post->ID, $anofl_options->cf7_scripts ) ) {
			wp_dequeue_script( 'contact-form-7' );
			wp_deregister_script( 'contact-form-7' );

			wp_dequeue_script( 'google-recaptcha' );
			wp_deregister_script( 'google-recaptcha' );
		}

	}

	/**
	 * Callback for wp_calculate_image_srcset_meta hook. Disables srcset meta for product thumbnail.
	 *
	 * @param  array $image_meta An array of srcsets.
	 * @return mixed False if srcset need to be disabled otherwise an array of srcsets.
	 */
	public function disable_product_mobile_srcset( $image_meta ) {
		if ( class_exists( 'woocommerce' ) && ! is_single() && wp_is_mobile() ) {
			$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

			if ( '1' == $anofl_options->wc_disable_srcset ) {
				return false;
			}

			return $image_meta;
		}

		return $image_meta;
	}

	/**
	 * Callback for woocommerce_get_image_size_thumbnail hook. Sets product thumbnail size on mobile.
	 *
	 * @param  array $size An array of image dimentions.
	 * @return array An array of image dimentions.
	 */
	public function product_custom_mobile_thumb_size( $size ) {
		if ( ! wp_is_mobile() ) {
			return $size;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( empty( $anofl_options->wc_mobile_thumb_size ) ) {
			return $size;
		}

		$size['width'] = $anofl_options->wc_mobile_thumb_size;

		$size['height'] = $anofl_options->wc_mobile_thumb_size;

		return $size;
	}
	/**
	 * Filter content to add lazyload class if elemntor
	 *
	 * @since 1.0.0
	 * @param string $content Post/page's content.
	 *
	 * @return string Filtered content with lazyload class added.
	 */
	public function elementor_add_lazyload_class( $content ) {

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' !== $anofl_options->lazyload_elementor_backgrounds ) {
			return $content;
		}

		$content = str_replace( 'elementor-section ', 'elementor-section lazyelementorbackgroundimages ', $content );

		$content = str_replace( 'elementor-column-wrap', 'elementor-column-wrap lazyelementorbackgroundimages', $content );

		$content = str_replace( 'elementor-widget-wrap', 'elementor-widget-wrap lazyelementorbackgroundimages', $content );

		$content = str_replace( 'elementor-widget-container', 'elementor-widget-container lazyelementorbackgroundimages', $content );

		$content = str_replace( 'elementor-background-overlay', 'elementor-background-overlay lazyelementorbackgroundimages', $content );

		$content = str_replace( 'e-gallery-image', 'e-gallery-image lazyelementorbackgroundimages', $content );

		$content = str_replace( 'anony-lazyload-bg', 'anony-lazyload-bg lazyelementorbackgroundimages', $content );

		return $content;

	}

	/**
	 * Add css to hide bg image on images with lazyelementorbackgroundimages class.
	 *
	 * @since 1.0.0
	 */
	public function lazy_elementor_background_images_css() {
		if ( is_admin() ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' !== $anofl_options->lazyload_elementor_backgrounds ) {
			return;
		}
		global $lazy_elementor_background_images_js_added;
		if ( ! ( $lazy_elementor_background_images_js_added ) ) {
			return; // don't add css if scripts weren't added
		}
		ob_start();
		?>
		<style>
			.lazyelementorbackgroundimages:not(.elementor-motion-effects-element-type-background) {
				background-image: none !important; /* lazyload fix for elementor */
			}
		</style>
		<?php
		echo ob_get_clean();
	}

	public function lazy_elementor_background_images_js_no_jquery() {
		global $lazy_elementor_background_images_js_added;
		ob_start();
		?>
		 
		window.onload = function() {
			var elems = document.querySelectorAll(".lazyelementorbackgroundimages");

			[].forEach.call(elems, function(el) {
				el.classList.remove("lazyelementorbackgroundimages");
			});
		};
		<?php
		$skrip = ob_get_clean();

		$lazy_elementor_background_images_js_added = wp_add_inline_script( 'backbone', $skrip );

	}

	/**
	 * Add js to remove the lazyelementorbackgroundimages class as the item approaches the viewport. (jQuery and Waypoint are dependencies)
	 *
	 * @since 1.0.0
	 */
	public function lazy_elementor_background_images_js() {

		if ( is_admin() ) {
			return;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' !== $anofl_options->lazyload_elementor_backgrounds ) {
			return;
		}
		global $lazy_elementor_background_images_js_added;

		if ( 'with_jquery' === $anofl_options->lazyloading_elementor_bg_method ) {
			$dependancy = 'jquery';
			ob_start();
			?>

			jQuery( function ( $ ) {
				 
				if ( ! ( window.Waypoint ) ) {
					// if Waypoint is not available, then we MUST remove our class from all elements because otherwise BGs will never show
					$('.elementor-section.lazyelementorbackgroundimages,.elementor-column-wrap.lazyelementorbackgroundimages, .elementor-widget-wrap.lazyelementorbackgroundimages').removeClass('lazyelementorbackgroundimages');
					if ( window.console && console.warn ) {
						console.warn( 'Waypoint library is not loaded so backgrounds lazy loading is turned OFF' );
					}
					return;
				} 
				$('.lazyelementorbackgroundimages').each( function () {
					 
					var $section = $( this );
					new Waypoint({
						element: $section.get( 0 ),
						handler: function( direction ) {
							//console.log( [ 'waypoint hit', $section.get( 0 ), $(window).scrollTop(), $section.offset() ] );
							$section.removeClass('lazyelementorbackgroundimages');
						},
						offset: $(window).height()*1.5 // when item is within 1.5x the viewport size, start loading it
					});
				} );
			});

			<?php
			$skrip = ob_get_clean();

			if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery' );
			}
		} else {
			$dependancy = 'backbone';

			ob_start();
			?>
			 
			window.onload = function() {
				var elems = document.querySelectorAll(".lazyelementorbackgroundimages");

				[].forEach.call(elems, function(el) {
					el.classList.remove("lazyelementorbackgroundimages");
				});
			};
			<?php
			$skrip = ob_get_clean();
		}

		$lazy_elementor_background_images_js_added = wp_add_inline_script( $dependancy, $skrip );
	}

	public function add_missing_image_Dimensions( $content ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' === $anofl_options->add_missing_image_dimensions ) {
			return ANONY_IMAGES_HELP::add_missing_dimensions( $content );
		}

		return $content;

	}

	public function dequeued_styles() {
		if ( current_user_can( 'administrator' ) || is_admin() || false !== strpos( $_SERVER['REQUEST_URI'], 'elementor' ) ) {
			return;
		}
		$dequeued_styles = apply_filters(
			'anony_dequeue_styles',
			array(
				'wpml-tm-admin-bar',
				'wc-blocks-vendors-style',
				'wc-blocks-style-rtl',
				'wp-block-library',
				'wp-block-library-theme',
				'wc-block-style',
				'wd-wp-gutenberg',
				'google-fonts-1',
				'allow-webp-image',

			)
		);

		if ( wp_is_mobile() ) {
			$mobile_dequeued_styles = array(
				'woocommerce-packing-slips',
				'woocommerce-pdf-invoices',
			);

			$dequeued_styles = array_merge( $dequeued_styles, $mobile_dequeued_styles );
		}

		foreach ( $dequeued_styles as $style ) {
			wp_dequeue_style( $style );
			wp_deregister_style( $style );
		}

	}

	public function dequeue_scripts() {
		if ( is_admin() ) {
			return;
		}
		$dequeued_scripts = apply_filters(
			'anony_dequeue_scripts',
			array(
				'allow-webp-image',
				'jupiterx-child',
				'jupiterx-utils',
			)
		);

		if ( wp_is_mobile() ) {
			$dequeued_scripts[] = 'jet-vue';
		}

		foreach ( $dequeued_scripts as $script ) {
			wp_dequeue_script( $script );
			wp_deregister_script( $script );
		}
	}

	public function is_used_css_enabled() {
		global $post;
		if ( current_user_can( 'administrator' ) || is_admin() || false !== strpos( $_SERVER['REQUEST_URI'], 'elementor' ) || ! $post || is_null( $post ) ) {
			return false;
		}

		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		$is_used_css_enabled = ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['enable_used_css'] ) && '1' === $optimize_per_post['enable_used_css'] ? true : false;

		return $is_used_css_enabled;
	}
	public function remove_all_stylesheets( $tag ) {

		if ( $this->is_used_css_enabled() ) {
			return '';
		}

		return $tag;
	}

	public function used_css_placeholder() {
		if ( $this->is_used_css_enabled() ) {
			echo '{ussedcss}';
		}
	}

	/**
	 * Remove inline <style> blocks.
	 * Start HTML buffer
	 */
	public function start_html_buffer() {
		if ( $this->is_used_css_enabled() ) {
			// buffer output html.
			ob_start();
		}
	}

	/**
	 * End HTML buffer
	 */
	public function end_html_buffer() {
		if ( $this->is_used_css_enabled() ) {
			global $post;
			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			$style = '';

			if ( ! wp_is_mobile() && ! empty( $optimize_per_post['desktop_used_css'] ) ) {
				$style .= '<style id="anony-desktop-used-css-' . esc_attr( $post->ID ) . '">
				' . $optimize_per_post['desktop_used_css'] . '
				</style>';

			}

			if ( wp_is_mobile()  ) {
				if ( ( empty( $optimize_per_post['separate_mobile_used_css'] ) || '1' !== $optimize_per_post['separate_mobile_used_css'] ) && ! empty( $optimize_per_post['desktop_used_css'] ) ) {
					$style .= '<style id="anony-all-used-css-' . esc_attr( $post->ID ) . '">' . $optimize_per_post['desktop_used_css'] . '</style>';
				}elseif ( '1' === $optimize_per_post['separate_mobile_used_css'] && ! empty( $optimize_per_post['mobile_used_css'] ) ) {
					$style .= '<style id="anony-mobile-used-css-' . esc_attr( $post->ID ) . '">' . $optimize_per_post['mobile_used_css'] . '</style>';
				}
			}

			// get buffered HTML.
			$wp_html = ob_get_clean();

			// remove <style> blocks using regular expression.
			$wp_html = preg_replace( '/<style[^>]*>[^<]*<\/style>/m', '', $wp_html );

			$wp_html = str_replace( '{ussedcss}', $style, $wp_html );
			echo $wp_html;
		}
	}

	public function common_injected_styles( $tag ) {

		if ( is_admin() ) {
			return $tag;
		}

		if ( preg_match( "/rel='stylesheet'/im", $tag ) ) {

			if ( false !== strpos( $tag, 'wpml-legacy-horizontal-list' )
				|| false !== strpos( $tag, 'flexible_shipping_notices' )
				|| false !== strpos( $tag, 'jet-cw' )
				|| false !== strpos( $tag, 'jet-cw-frontend' )
				|| false !== strpos( $tag, 'jet-popup-frontend' )
				|| false !== strpos( $tag, 'photoswipe' )
				|| false !== strpos( $tag, 'photoswipe-default-skin' )
			) {
				preg_match( "/id='(.*?)'/im", $tag, $id );
				$style_id = $id[1];

				preg_match( "/href='(.*?)'/im", $tag, $href );
				$style_href = $href[1];

				add_action(
					'wp_print_footer_scripts',
					function () use ( $style_id, $style_href ) {
						?>
						<input type="hidden" class="create-style-tag" id="create-<?php echo $style_id; ?>" value="<?php echo $style_href; ?>"/>
						<?php
					}
				);
				return '';
			}

			return $tag;
		}
	}
	public function mobile_injected_scripts( $tag ) {

		if ( is_admin() ) {
			return $tag;
		}

		if ( preg_match( "/rel='stylesheet'/im", $tag ) ) {

			if ( false !== strpos( $tag, 'font-awesome-all' )
				|| false !== strpos( $tag, 'fontawesome' )
				|| false !== strpos( $tag, 'jet-elements-skin' )
				|| false !== strpos( $tag, 'jet-menu-public-styles' )
				|| false !== strpos( $tag, 'font-awesome' )
				|| false !== strpos( $tag, 'elementor-icons' )
				|| false !== strpos( $tag, 'elementor-pro' )
				|| false !== strpos( $tag, 'e-animations' )
				|| false !== strpos( $tag, 'elementor-icons-shared-0' )
				|| false !== strpos( $tag, 'elementor-icons-fa-solid' )
				|| false !== strpos( $tag, 'elementor-icons-fa-brands' )
				|| false !== strpos( $tag, 'elementor-icons-fa-regular' )
				|| false !== strpos( $tag, 'elementor-icons' )
				|| false !== strpos( $tag, 'jet-menu-general' )
				|| false !== strpos( $tag, 'font-awesome-v4-shims' )
				|| false !== strpos( $tag, 'wp-block-library-theme-inline' )
				|| false !== strpos( $tag, 'global-styles-inline' )
				|| false !== strpos( $tag, 'jet-engine-frontend' )
			) {
				preg_match( "/id='(.*?)'/im", $tag, $id );
				$style_id = $id[1];

				preg_match( "/href='(.*?)'/im", $tag, $href );
				$style_href = $href[1];

				add_action(
					'wp_print_footer_scripts',
					function () use ( $style_id, $style_href ) {
						?>
						<input type="hidden" class="create-style-tag" id="create-<?php echo $style_id; ?>" value="<?php echo $style_href; ?>"/>
						<?php
					}
				);
				return '';
			}

			return $tag;
		}
	}

	public function inject_scripts() {
		?>

		<script>
			var cb = function() {
				var h = document.getElementsByTagName('head')[0];
				 
				document.querySelectorAll('.create-style-tag').forEach(function(styleInput) {
					var l = document.createElement('link'); 
					l.rel = 'stylesheet';
					l.href = styleInput.value;
					l.id = styleInput.id;
					l.media = "all";
					l.type = "text/css";
					h.appendChild(l, h);
					 
				});

				 
			};
			var raf = requestAnimationFrame || mozRequestAnimationFrame ||
			webkitRequestAnimationFrame || msRequestAnimationFrame;
			if (raf) raf(cb);
			else window.addEventListener('load', cb);
		</script>

		<?php
	}


}
