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

	public function elementor_google_fonts( $print_google_fonts )
	{
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' === $anofl_options->disable_elementor_google_fonts) {
			return false;
		}

		return $print_google_fonts;
		
	}

	public function anony_add_head_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( !empty( $anofl_options->head_scripts ) ){
			echo $anofl_options->head_scripts;
		}
	}

	public function anony_add_footer_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( !empty( $anofl_options->footer_scripts ) ){
			echo $anofl_options->footer_scripts;
		}
	}

	public function output_preloader(){
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$preloader_timeout = $anofl_options->preloader_timeout;
		 if ($anofl_options->preloader == '1' ) : ?>
		 	<style>
				#anony-preloader p{
					font-size: 18px;
				}
				#anony-preloader{
					position: fixed;
					display: flex;
					align-items: center;
					justify-content: center;
					flex-direction: column;
					width: 100%;
					height: 100%;
					background: #fff;
					z-index: 9999999999;
					background-color: rgb(249, 249, 249)
				}
			</style>
			<div id="anony-preloader">
				<p><?php echo esc_html__( 'Loading...', 'anony-flash-wp' ); ?></p>
			</div>
			<script>
				window.onload = function() {
					"use strict";
					setTimeout(function(){
						var loader = document.getElementById('anony-preloader');
						if(loader !== null) loader.style.display = 'none';
					}, <?php echo $preloader_timeout ?>);
				};
			</script>
		<?php endif;
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
	 * Controls add query strings to scripts/styles
	 *
	 * @param string $src Script/Style source.
	 * @param string $handle Script/Style handle.
	 * @return string
	 */
	public function anony_control_query_strings( $src, $handle ) {
		if ( is_admin() ) {
			return $src;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		// Keep query string for these items.
		$neglected = array();

		if ( ! empty( $anofl_options->keep_query_string ) ) {
			$neglected = explode( ',', $anofl_options->keep_query_string );
		}

		if ( '0' !== $anofl_options->query_string && ! in_array( $handle, $neglected, true ) ) {
			$src = remove_query_arg( 'ver', $src );
		}
		return $src;

	}

	public function remove_unused_scripts( $tag, $handle, $src )
	{
		if( !is_singular() || !empty( $_GET['list_scripts'] ) ) return $tag;
		
		global $post;
		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		if ( ! empty( $optimize_per_post ) && ! empty( $optimize_per_post[ 'unloaded_js' ] ) ) {
			foreach( $optimize_per_post[ 'unloaded_js' ] as $script )
			{
				if( false !== strpos( $tag,  $script) ){
					return '';
				}
			}
			
		}
		return $tag;
	}

	public function load_scripts_on_interaction( $tag, $handle, $src ){
		
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( is_admin() || '1' !== $anofl_options->load_scripts_on_interaction ||  $this->uri_strpos( 'elementor' ) ) {
			return $tag; // don't break WP Admin.
		}

		$exclusions = ANONY_STRING_HELP::line_by_line_textarea($anofl_options->delay_scripts_exclusions);
		if( is_array( $exclusions ) ){
			foreach( $exclusions as $exclusion ){
				if( $this->uri_strpos($exclusion) ){
					return $tag;
				}
			}
		}

		if ( false === strpos( $src, '.js' ) ) {
			return $tag;
		}

		if ( false !== strpos( $tag, 'anony-delay-scripts' ) ) {
			return $tag;
		}

		$exclusion_list = apply_filters( 'load_scripts_on_interaction_exclude', array('jquery-core-js') );
		
		foreach( $exclusion_list as $target ){
			if ( false !== strpos( $tag, $target )) {
				return $tag;
			}
		}

		$tag = str_replace('text/javascript', 'anony-delay-scripts' ,$tag);

		return $tag;
	}
	/**
	 * Defer scripts
	 *
	 * @param string $tag Script tag.
	 * @param string $handle Script handle.
	 * @param string $src Script source.
	 * @return string
	 */
	public function defer_scripts( $tag, $handle, $src ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( is_admin() || '1' !== $anofl_options->defer_scripts ) {
			return $tag; // don't break WP Admin.
		}

		if ( false === strpos( $src, '.js' ) ) {
			return $tag;
		}
		
		if ( false !== strpos( $tag, 'defer' ) ) {
			return $tag;
		}
		if ( strpos( $tag, 'wp-includes' ) !== false ){
			return $tag; //Exclude all from w-includes.
		} 

		// Try not defer all.
		$not_deferred = array(
			'syntaxhighlighter-core',
			'jquery-core',
			'wp-polyfill',
			'wp-hooks',
			'wp-i18n',
			'wp-tinymce-root',
			'wc_price_slider',
			'firebase',
			'firebase-auth'
			
		);

		if( !empty( $anofl_options->not_to_be_defered_scripts ) ){
			$not_to_be_defered_scripts = array_filter(ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->not_to_be_defered_scripts ));
			
			if( !empty( $not_to_be_defered_scripts ) ){
				$not_deferred = array_merge( $not_deferred, $not_to_be_defered_scripts );
			}
		}

		$not_deferred = apply_filters( 'anony_not_to_be_defered_scripts', $not_deferred );
		foreach( $not_deferred as $search ){
			if( false !== strpos( $tag, $search ) ){
				return $tag;
			}
		}
		return str_replace( ' src', ' defer src', $tag );
	}

	public function disable_gravatar( $avatar ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! $anofl_options->disable_gravatar || '1' !== $anofl_options->disable_gravatar ) {
			return $avatar;
		}

		$avatar = '<div style="max-width: 48px;display:inline-flex;justify-content:center;align-items:center;height: 48px;width: 48px;border: 1px solid #ccc;border-radius: 50%;"><svg height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><g transform="translate(0 -1028.4)"><path d="m12 1039.4c-1.277 0-2.4943 0.2-3.5938 0.7 0.6485 1.3 2.0108 2.3 3.5938 2.3s2.945-1 3.594-2.3c-1.1-0.5-2.317-0.7-3.594-0.7z" fill="#95a5a6"/><path d="m8.4062 1041.1c-2.8856 1.3-4.9781 4-5.3437 7.3 0 1.1 0.8329 2 1.9375 2h14c1.105 0 1.938-0.9 1.938-2-0.366-3.3-2.459-6-5.344-7.3-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z" fill="#d35400"/><path d="m8.4062 1040.1c-2.8856 1.3-4.9781 4-5.3437 7.3 0 1.1 0.8329 2 1.9375 2h14c1.105 0 1.938-0.9 1.938-2-0.366-3.3-2.459-6-5.344-7.3-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z" fill="#e67e22"/><path d="m12 11c-1.147 0-2.2412 0.232-3.25 0.625 0.9405 0.616 2.047 1 3.25 1 1.206 0 2.308-0.381 3.25-1-1.009-0.393-2.103-0.625-3.25-0.625z" fill="#7f8c8d" transform="translate(0 1028.4)"/><path d="m17 4a5 5 0 1 1 -10 0 5 5 0 1 1 10 0z" fill="#bdc3c7" transform="translate(0 1031.4)"/><path d="m8.4062 1040.1c-0.3172 0.2-0.6094 0.3-0.9062 0.5 0.8153 1.6 2.541 2.8 4.5 2.8s3.685-1.2 4.5-2.8c-0.297-0.2-0.589-0.3-0.906-0.5-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z" fill="#d35400" style="block-progression:tb;text-indent:0;color:#000000;text-transform:none"/></g></svg></div>';

		return $avatar;
	}

	public function disable_wp_embeds() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$keep = true;

		if ( '1' === $anofl_options->disable_embeds ) {
			$keep = false;
		}

		if ( '1' === $anofl_options->enable_singular_embeds && is_single() ) {
			$keep = true;
		}

		if ( $keep ) {
			return;
		}

		// Remove the REST API endpoint..
		remove_action( 'rest_api_init', 'wp_oembed_register_route' );

		// Turn off oEmbed auto discovery..
		add_filter( 'embed_oembed_discover', '__return_false' );

		// Don't filter oEmbed results..
		remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );

		// Remove oEmbed discovery links..
		remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );

		// Remove oEmbed-specific JavaScript from the front-end and back-end..
		remove_action( 'wp_head', 'wp_oembed_add_host_js' );

		add_filter(
			'tiny_mce_plugins',
			function ( $plugins ) {
				return array_diff( $plugins, array( 'wpembed' ) );
			}
		);

		// Remove all embeds rewrite rules..
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

		// Remove filter of the oEmbed result before any HTTP requests are made..
		remove_filter( 'pre_oembed_result', 'wp_filter_pre_oembed_result', 10 );
	}

	public function disable_wp_emojis() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$keep = true;

		if ( '1' === $anofl_options->disable_emojis ) {
			$keep = false;
		}

		if ( '1' === $anofl_options->enable_singular_emojis && is_single() ) {
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

		if ( '1' === $anofl_options->disable_gutenburg_scripts ) {
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

		if ( '1' !== $anofl_options->disable_jq_migrate ) {
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

		$arr = array();
		if ( ! empty( $anofl_options->preload_images ) ) {
			$arr = array_merge( $arr, ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->preload_images ) );
		}

		global $post;

		if ( $post && ! is_null( $post ) ) {
			if ( ! wp_is_mobile() ) {
				$key = 'preload_desktop_images';
			} else {
				$key = 'preload_mobile_images';
			}

			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			if ( ! empty( $optimize_per_post ) && ! empty( $optimize_per_post[ $key ] ) ) {
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
	public function stylesheets_media_to_all(){
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		
		//var_dump($this->is_above_the_fold_styles_enabled());
		/*
		if( !$this->is_above_the_fold_styles_enabled() ){
			return;
		}
		*/
		?>
		<script type="text/javascript">
			jQuery(document).ready(function($){
				function load_deferred_stylesheets(){
					$('link[media="print"]').each(function() {
						var media = $(this).attr('media');
						media = media.replace('print', 'all');
						$(this).attr('media', media);
					});
				}
				<?php if( 'interact' === $anofl_options->load_stylesheets_on ) {?>
					document.body.addEventListener('mousemove', load_deferred_stylesheets);
					document.body.addEventListener('scroll', load_deferred_stylesheets);
					document.body.addEventListener('keydown', load_deferred_stylesheets);
					document.body.addEventListener('click', load_deferred_stylesheets);
					document.body.addEventListener('touchstart', load_deferred_stylesheets);
				<?php } ?>

				<?php if( 'load' === $anofl_options->load_stylesheets_on ) {?>
					window.addEventListener('load', load_deferred_stylesheets);
				<?php } ?>
			});

		</script>
	<?php
	}

	public function google_tag_script( $tag_id, $defer_js_id , $console = '' ){
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		?>
		Defer.js('https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html( $tag_id ); ?>', '<?php echo $defer_js_id ?>', 1500, 
		function() {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push(['js', new Date()]);
			dataLayer.push(['config', '<?php echo esc_html( $tag_id ); ?>']);
			console.info('<?php echo esc_html( $console ); ?>'); // debug.
		}, false);

		<?php
	}
	/**
	 * Load Google tag manager deferred depending on defer.js
	 */
	public function defer_gtgm() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$gads_id = $anofl_options->gads_id;
		$ganalytics_id = $anofl_options->ganalytics_id;
		if ( ! empty( $anofl_options->gtgm_id ) ) {
		?>
			<script>
				<?php 
				$this->google_tag_script($anofl_options->gtgm_id, 'google-tag-main' ,'Google tag manager is loaded' );

				if ( ! empty( $gads_id ) ) { 
					$this->google_tag_script($gads_id, 'google-tag-ads' , 'Google ADs tag is loaded' );
				}

				if ( ! empty( $ganalytics_id ) ) { 
					$this->google_tag_script($ganalytics_id, 'google-tag-analytics' , 'Google analytics tag is loaded' );
				} 
				?>
		</script>
		<?php
			$this->gtag_events();
		}
	}
	public function gtag_events(){
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$gtm_events = $anofl_options->gtm_events;
		if ( ! empty( $gtm_events ) ) {
			?>
			<script type="anony-gtag-events-scripts">
			<?php echo $gtm_events ?>
			</script>

			<script>
				Defer.all('script[type="anony-gtag-events-scripts"]', 1800);
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
				fbq('init', '<?php echo esc_html( $anofl_options->facebook_pixel_id ); ?>');
				fbq('track', 'PageView');

			</script>

			<noscript>
				<img height="1" width="1" style="display:none"
				src="https://www.facebook.com/tr?id=<?php echo esc_html( $anofl_options->facebook_pixel_id ); ?>&ev=PageView&noscript=1"
				/>
			</noscript>
			<!-- End Meta Pixel Code -->

			<script>
				Defer.all('script[type="anony-facebook-pixel"]', 1500);
			</script>
			<?php
		}
	}

	public function uri_strpos( $string ){
		if( 
			(!empty( $_SERVER['REQUEST_URI'] ) && !empty( $string ) && false !== strpos($_SERVER['REQUEST_URI'], $string)) ||
			(!empty( $_SERVER['QUERY_STRING'] ) && !empty( $string ) && false !== strpos($_SERVER['QUERY_STRING'], $string))
		){
			return true;
		}

		return false;
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
				Defer.all('script[type="anony-external-scripts"]', 1500);
			</script>
			<?php
		}

		if ( '1' === $anofl_options->load_scripts_on_interaction ) {

			?>
			<script>
				Defer.all('script[type="anony-delay-scripts"]', 0, true);
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
				$this->dequeue_wc_style();
				$this->dequeue_wc_scripts();
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

		if ( empty( $anofl_options->wc_mobile_thumb_width ) || empty( $anofl_options->wc_mobile_thumb_height ) ) {
			return $size;
		}

		$size['width'] = $anofl_options->wc_mobile_thumb_width;

		$size['height'] = $anofl_options->wc_mobile_thumb_height;

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

		

		if ( class_exists( 'ANONY_STRING_HELP' ) ) {

			$lazyloaded_backgrounds = array_filter( ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->lazyload_this_classes ) );
			
			if( !empty( $lazyloaded_backgrounds )  )
			{
				foreach ($lazyloaded_backgrounds as $selector) {
					$content = str_replace( $selector. ' ', $selector . ' lazyelementorbackgroundimages ', $content );
				}
			}
		}

		return $content;

	}
	public function load_bg_on_interaction($content){
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$opt_targets = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->interact_lazyload_this_classes );
		$targets = apply_filters( 'load_bg_on_interaction', array());
		if( !empty( $opt_targets ) && is_array( $opt_targets ) ){
			$targets = array_merge($targets, $opt_targets);
		}
		if( !empty( $targets ) ){
			foreach( $targets as $target ){
				$content = str_replace( $target, $target.' interact-hidden', $content );
			}
		}
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
			return; // don't add css if scripts weren't added.
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
	
	public function load_bg_on_interaction_styles() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$opt_targets = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->interact_lazyload_this_classes );
		$targets = apply_filters( 'load_bg_on_interaction', array());
		if( !empty( $opt_targets ) && is_array( $opt_targets ) ){
			$targets = array_merge($targets, $opt_targets);
		}
		$styles = '';
		if( !empty( $targets ) ){
			
			$styles .= '<style>';

			foreach( $targets as $target ){ 
				$styles .= '.'.$target.'.interact-hidden,';
			}
			$styles = trim($styles,',');
			$styles .= '{
				background-image: none !important;
			}';
			$styles .= '</style>';
		}	
		echo $styles;
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
					// if Waypoint is not available, then we MUST remove our class from all elements because otherwise BGs will never show.
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
						offset: $(window).height()*1.5 // when item is within 1.5x the viewport size, start loading it.
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

		if ( wp_doing_ajax() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ){
			return $content;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		
		if ( '1' === $anofl_options->add_missing_image_dimensions ) {
			if( '1' === $anofl_options->lazyload_images ){
				$lazyload = true;
			}else{
				$lazyload = false;
			}
	
			return ANONY_IMAGES_HELP::add_missing_dimensions( $content, $lazyload );
		}
		return $content;

	}

	public function dequeued_styles() {
		if ( is_admin() || $this->uri_strpos( 'elementor' ) ) {
			return;
		}

		if ( class_exists( 'ANONY_STRING_HELP' ) ) {
			$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

			$dequeued_styles = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->dequeued_styles );

			if( !empty( $dequeued_styles )  )
			{
				foreach ($dequeued_styles as $handle) {

					wp_dequeue_style( $handle );
					wp_deregister_style( $handle );
					
				}
			}
		}

	}

	public function dequeue_scripts() {
		if ( is_admin() ) {
			return;
		}
		$dequeued_scripts = apply_filters(
			'anony_dequeue_scripts',
			array()
		);
		foreach ( $dequeued_scripts as $script ) {
			wp_dequeue_script( $script );
			wp_deregister_script( $script );
		}
	}

	function is_post_type_used_css_enabled(){
		global $post;

		if ( !is_singular() || is_admin() || $this->uri_strpos( 'elementor' ) || ! $post || is_null( $post ) ) {
			return false;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$option_name = 'enable_used_css_' . $post->post_type;

		$optimize_post_types = $anofl_options->optimize_post_types;

		if( $optimize_post_types && is_array( $optimize_post_types ) && in_array( $post->post_type,  $optimize_post_types ) && '1' === $anofl_options->$option_name ){
			return true;
		}

		return false;
	}
	
	function is_taxonomy_used_css_enabled(){

		if ( 
			
			is_admin() || 
			$this->uri_strpos( 'elementor' ) || 
			(
				! is_tax() && 
				! is_category() &&
				! is_tag()
			)
		) 
		{
			return false;
		}


		
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$term = get_queried_object();
		$option_name = 'enable_used_css_' . $term->taxonomy;
		$optimize_taxonomies = $anofl_options->optimize_taxonomies;
		if( 
			$optimize_taxonomies && 
			is_array( $optimize_taxonomies ) && 
			in_array( $term->taxonomy,  $optimize_taxonomies ) && 
			'1' === $anofl_options->$option_name 
		)
		{
			return true;
		}

		return false;
	}
	public function is_used_css_enabled() {
		
		global $post;
		if ( !is_singular('page') || is_admin() || $this->uri_strpos( 'elementor' ) || ! $post || is_null( $post ) ) {
			return false;
		}

		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		$is_used_css_enabled = ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['enable_used_css'] ) && '1' === $optimize_per_post['enable_used_css'] ? true : false;

		//$defer_all_styles = ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['defer_all_styles'] ) && '1' === $optimize_per_post['defer_all_styles'] ? true : false;

		if( $is_used_css_enabled/* && !$defer_all_styles*/){
			return true;
		}

		return false;
	}
	public function remove_all_stylesheets( $tag ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if( $this->is_tax() ){
			
			$term = get_queried_object();
			$option_name = 'defer_all_styles_' . $term->taxonomy;
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if( 
				$optimize_taxonomies && 
				is_array( $optimize_taxonomies ) && 
				in_array( $term->taxonomy,  $optimize_taxonomies ) && 
				'1' === $anofl_options->$option_name 
			){
				return $tag;
			}
		}

		if( is_singular() ){
			global $post;
			$option_name = 'defer_all_styles_' . $post->post_type;

			$optimize_post_types = $anofl_options->optimize_post_types;

			if( 
				$optimize_post_types && 
				is_array( $optimize_post_types ) &&
				in_array( $post->post_type,  $optimize_post_types ) && 
				'1' === $anofl_options->$option_name 
			){
				return $tag;
			}
		}
		
		if( $this->is_taxonomy_used_css_enabled() ){
			return '';
		}
		if ( $this->is_used_css_enabled() || $this->is_post_type_used_css_enabled() ) {
			return '';
		}

		return $tag;
	}
	protected function is_switch_meta_field_enabled( $field_name ){
		if( !is_singular() ) return false;
		global $post;
		if ( is_admin() || $this->uri_strpos( 'elementor' ) || ! $post || is_null( $post ) ) {
			return false;
		}

		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );
	
		$enabled = ! empty( $optimize_per_post ) && ! empty( $optimize_per_post[$field_name] ) && '1' === $optimize_per_post[$field_name] ? true : false;

		

		return $enabled;
	}
	public function is_tax(){
		return is_tax() || is_category() || is_tag();
	}
	public function stylesheet_media_to_print($tag){
		if ( is_admin() || $this->uri_strpos( 'wp-admin' ) ) {
			return $tag;
		}
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if( $this->is_tax() ){

			
			$term = get_queried_object();
			$option_name = 'defer_all_styles_' . $term->taxonomy;
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if( 
				$optimize_taxonomies && 
				is_array( $optimize_taxonomies ) && 
				in_array( $term->taxonomy,  $optimize_taxonomies ) && 
				'1' === $anofl_options->$option_name 
			)
			{
				$method = 'interact';
				if('onload' === $method ){
					$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );
				}else{
					$tag = preg_replace( "/media='\w+'/", "media='print'", $tag );
				}

				return $tag;

			}

		}

		
		if( !is_singular() ) return $tag;
		global $post;
		
		if ( ! $post || is_null( $post ) ) {
			return $tag;
		}

		if( is_singular() ){

			$option_name = 'defer_all_styles_' . $post->post_type;

			$optimize_post_types = $anofl_options->optimize_post_types;

			if( $optimize_post_types && is_array( $optimize_post_types ) && in_array( $post->post_type,  $optimize_post_types ) && '1' === $anofl_options->$option_name ){
				$method = 'interact';
				if('onload' === $method ){
					$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );
				}else{
					$tag = preg_replace( "/media='\w+'/", "media='print'", $tag );
				}

				return $tag;
			}
		}

		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		$defer_all_styles = ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['defer_all_styles'] ) && '1' === $optimize_per_post['defer_all_styles'] ? true : false;
		if( $defer_all_styles ){
			$method = 'interact';
			if('onload' === $method ){
				$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );
			}else{
				$tag = preg_replace( "/media='\w+'/", "media='print'", $tag );
			}
			
		}
		return $tag;
	}

	public function is_above_the_fold_styles_enabled() {
		
		return $this->is_switch_meta_field_enabled( 'above_the_fold_styles' );

	}

	public function above_the_fold_css( $post, $optimize_per_post ){
		$style = '';
		
		if ( ! wp_is_mobile() && ! empty( $optimize_per_post['desktop_above_fold_css'] ) ) {
			$style .= '<style type="text/css" id="anony-desktop-above-the-fold-css-' . esc_attr( $post->ID ) . '">
			' . $optimize_per_post['desktop_above_fold_css'] . '
			</style>';

		}

				
		if ( wp_is_mobile() && ! empty( $optimize_per_post['mobile_above_fold_css'] ) ) {
			$style .= '<style type="text/css" id="anony-mobile-above-the-fold-css-' . esc_attr( $post->ID ) . '">
			' . $optimize_per_post['mobile_above_fold_css'] . '
			</style>';

		}

		
		return $style;
	}

	public function used_css( $post, $optimize_per_post ){
		$style = '';

		if ( ! wp_is_mobile() && ! empty( $optimize_per_post['desktop_used_css'] ) ) {
			$style .= '<style type="text/css" id="anony-desktop-used-css-' . esc_attr( $post->ID ) . '">
			' . $optimize_per_post['desktop_used_css'] . '
			</style>';

		}

		if ( wp_is_mobile() ) {
			if ( ( empty( $optimize_per_post['separate_mobile_used_css'] ) || '1' !== $optimize_per_post['separate_mobile_used_css'] ) && ! empty( $optimize_per_post['desktop_used_css'] ) ) {
				$style .= '<style type="text/css" id="anony-all-used-css-' . esc_attr( $post->ID ) . '">' . $optimize_per_post['desktop_used_css'] . '</style>';
			} elseif ( '1' === $optimize_per_post['separate_mobile_used_css'] && ! empty( $optimize_per_post['mobile_used_css'] ) ) {
				$style .= '<style type="text/css" id="anony-mobile-used-css-' . esc_attr( $post->ID ) . '">' . $optimize_per_post['mobile_used_css'] . '</style>';
			}
		}

		return $style;
	}


	public function post_type_global_used_css( $post, $options ){
		$style = '';
		$desktop_used_css = 'desktop_used_css_' . $post->post_type;
		$separate_mobile_used_css = 'separate_mobile_used_css_' . $post->post_type;
		$mobile_used_css = 'mobile_used_css_' . $post->post_type;

		if ( ! wp_is_mobile() && ! empty( $options->$desktop_used_css ) ) {
			$style .= '<style type="text/css" id="anony-desktop-used-css-' . esc_attr( $post->ID ) . '">
			' . $options->$desktop_used_css . '
			</style>';

		}

		if ( wp_is_mobile() ) {
			if ( ( empty( $options->$separate_mobile_used_css ) || '1' !== $options->$separate_mobile_used_css ) && ! empty( $options->$desktop_used_css ) ) {
				$style .= '<style type="text/css" id="anony-all-used-css-' . esc_attr( $post->ID ) . '">' . $options->$desktop_used_css . '</style>';
			} elseif ( '1' === $options->$separate_mobile_used_css && ! empty( $options->$mobile_used_css ) ) {
				$style .= '<style type="text/css" id="anony-mobile-used-css-' . esc_attr( $post->ID ) . '">' . $options->$mobile_used_css . '</style>';
			}
		}

		return $style;
	}


	public function taxonomy_global_used_css( $term, $options ){
		$style = '';
		$desktop_used_css = 'desktop_used_css_' . $term->taxonomy;
		$separate_mobile_used_css = 'separate_mobile_used_css_' . $term->taxonomy;
		$mobile_used_css = 'mobile_used_css_' . $term->taxonomy;

		if ( ! wp_is_mobile() && ! empty( $options->$desktop_used_css ) ) {
			$style .= '<style type="text/css" id="anony-desktop-used-css-' . esc_attr( $term->term_id ) . '">
			' . $options->$desktop_used_css . '
			</style>';

		}

		if ( wp_is_mobile() ) {
			if ( ( empty( $options->$separate_mobile_used_css ) || '1' !== $options->$separate_mobile_used_css ) && ! empty( $options->$desktop_used_css ) ) {
				$style .= '<style type="text/css" id="anony-all-used-css-' . esc_attr( $term->term_id ) . '">' . $options->$desktop_used_css . '</style>';
			} elseif ( '1' === $options->$separate_mobile_used_css && ! empty( $options->$mobile_used_css ) ) {
				$style .= '<style type="text/css" id="anony-mobile-used-css-' . esc_attr( $term->term_id ) . '">' . $options->$mobile_used_css . '</style>';
			}
		}

		return $style;
	}

	
	/**
	 * Remove inline <style> blocks.
	 * Start HTML buffer
	 */
	public function start_html_buffer() 
	{

		// buffer output html..
		ob_start( array( $this, 'start_html_buffer_cb' ), 0  );
		
	}

	public function start_html_buffer_cb( $html )
	{
	
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		return $html;
		// phpcs:enable.
	}

	public function load_optimized_css()
	{
		
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if( !is_singular() ) {
			if( $this->is_taxonomy_used_css_enabled() ){
				echo $this->taxonomy_global_used_css( get_queried_object(), $anofl_options );
				return;
			}
			return '';
		}

		global $post;
		
		
		if(  $this->is_post_type_used_css_enabled() &&  !$this->is_used_css_enabled()){
			echo $this->post_type_global_used_css($post, $anofl_options);
			return;
		}
		
		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		$style = '';
		
		if( $this->is_used_css_enabled() && !$this->is_above_the_fold_styles_enabled() ){
			
			$style = $this->used_css( $post, $optimize_per_post );

		}
		
		
		if( $this->is_above_the_fold_styles_enabled() && !$this->is_used_css_enabled() ) {
			
			$style = $this->above_the_fold_css($post, $optimize_per_post);
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $style;
		// phpcs:enable.
	}

	/**
	 * End HTML buffer
	 */
	public function end_html_buffer() {
		if ( $this->is_used_css_enabled() || $this->is_above_the_fold_styles_enabled() ) {
			// get buffered HTML.
			echo ob_get_clean();
		}
	}

	public function to_be_injected_styles( $tag ) {
		
		if ( is_admin() || $this->uri_strpos( 'wp-admin' ) || (!is_singular() && !$this->is_tax()) ) {
			return $tag;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if( $this->is_tax() ){
			
			$term = get_queried_object();
			$option_name = 'defer_all_styles_' . $term->taxonomy;
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if( 
				!$optimize_taxonomies ||
				!is_array( $optimize_taxonomies ) || 
				!in_array( $term->taxonomy,  $optimize_taxonomies ) || 
				'1' !== $anofl_options->$option_name 
			){
				return $tag;
			}
		}
		if( is_singular() ){
			global $post;
			$option_name = 'defer_all_styles_' . $post->post_type;

			$optimize_post_types = $anofl_options->optimize_post_types;

			if( 
				!$optimize_post_types || 
				!is_array( $optimize_post_types ) ||
				!in_array( $post->post_type,  $optimize_post_types ) || 
				'1' !== $anofl_options->$option_name 
			){
				return $tag;
			}
		}
		
		if ( preg_match( "/rel='stylesheet'/im", $tag ) ) {

				preg_match( "/id='(.*?)'/im", $tag, $id );
				$style_id = $id[1];

				preg_match( "/href='(.*?)'/im", $tag, $href );
				$style_href = $href[1];
				
				add_action(
					'wp_head',
					function () use ( $style_id, $style_href ) {
						?>
						<script>
							Defer.css('<?php echo $style_href ?>', '<?php echo $style_href ?>', 0, function() {
								
							}, true);
						</script>
						<?php
					}
				);
				return '';
		}
		return $tag;
	}
	/**
	 * Injects stylesheets using css.
	 */
	public function inject_styles() {
		?>

		<script>
			var inject_stylesheets_upon_interact = function() {
				var h = document.getElementsByTagName('head')[0];
				document.querySelectorAll('.create-style-tag').forEach(function(styleInput) {
					var l = document.createElement('link'); 
					l.rel = 'stylesheet';
					l.href = styleInput.value;
					l.id = styleInput.id.replace('create-', '');
					l.media = "all";
					l.type = "text/css";
					h.appendChild(l, h);
				});
			};
			document.body.addEventListener('mousemove', inject_stylesheets_upon_interact);
			document.body.addEventListener('scroll', inject_stylesheets_upon_interact);
			document.body.addEventListener('keydown', inject_stylesheets_upon_interact);
			document.body.addEventListener('click', inject_stylesheets_upon_interact);
			document.body.addEventListener('touchstart', inject_stylesheets_upon_interact);
		</script>
		<?php
	}	
	
	public function load_bg_on_interaction_sctipt() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$opt_targets = array_filter(ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->interact_lazyload_this_classes ));
		$targets = apply_filters( 'load_bg_on_interaction', array());
		if( !empty( $opt_targets ) && is_array( $opt_targets ) ){
			$targets = array_merge($targets, $opt_targets);
		}
		if ( empty( $targets ) ){
			return;
		}
		// Convert PHP array to JSON
		$jsonArray = json_encode($targets);
		?>

		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var loadBgOnInteract = function() {
					// Decode JSON array in JavaScript
					var jsArray = <?php echo $jsonArray; ?>;
					// Loop through JavaScript array
					for (var i = 0; i < jsArray.length; i++) {
						if( jsArray[i] !== '' ){
							var lazyBgElements = document.querySelectorAll('.' + jsArray[i]);
							lazyBgElements.forEach(function(element) {
								element.classList.remove('interact-hidden');
							});
						}

					}
					
				};

				document.body.addEventListener('mousemove', loadBgOnInteract);
				document.body.addEventListener('scroll', loadBgOnInteract);
				document.body.addEventListener('keydown', loadBgOnInteract);
				document.body.addEventListener('click', loadBgOnInteract);
				document.body.addEventListener('touchstart', loadBgOnInteract);
			});

		</script>
		<?php
	}

	public function lazyload_images() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if( '1' === $anofl_options->lazyload_images ){?>
			<script>
				Defer.dom('img', 500);
				Defer.lazy = true;
			</script>
		<?php }
	}


}
