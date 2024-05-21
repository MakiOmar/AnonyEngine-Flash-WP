<?php
/**
 * General
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.093
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();

/**
 * Images preload class.
 *
 * @since      1.0.093
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/includes
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_General {
	/**
	 * Disable elementor google foonts.
	 *
	 * @param bool $print_google_fonts true/false.
	 * @return bool
	 */
	public function elementor_google_fonts( $print_google_fonts ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' === $anofl_options->disable_elementor_google_fonts ) {
			return false;
		}

		return $print_google_fonts;
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
	/**
	 * Disable gravatar
	 *
	 * @param string $avatar Avatar.
	 * @return string
	 */
	public function disable_gravatar( $avatar ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( '1' !== $anofl_options->disable_gravatar ) {
			return $avatar;
		}

		$avatar = '<div style="max-width: 48px;display:inline-flex;justify-content:center;align-items:center;height: 48px;width: 48px;border: 1px solid #ccc;border-radius: 50%;vertical-align: middle;"><svg height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><g transform="translate(0 -1028.4)"><path d="m12 1039.4c-1.277 0-2.4943 0.2-3.5938 0.7 0.6485 1.3 2.0108 2.3 3.5938 2.3s2.945-1 3.594-2.3c-1.1-0.5-2.317-0.7-3.594-0.7z" fill="#95a5a6"/><path d="m8.4062 1041.1c-2.8856 1.3-4.9781 4-5.3437 7.3 0 1.1 0.8329 2 1.9375 2h14c1.105 0 1.938-0.9 1.938-2-0.366-3.3-2.459-6-5.344-7.3-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z" fill="#d35400"/><path d="m8.4062 1040.1c-2.8856 1.3-4.9781 4-5.3437 7.3 0 1.1 0.8329 2 1.9375 2h14c1.105 0 1.938-0.9 1.938-2-0.366-3.3-2.459-6-5.344-7.3-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z" fill="#e67e22"/><path d="m12 11c-1.147 0-2.2412 0.232-3.25 0.625 0.9405 0.616 2.047 1 3.25 1 1.206 0 2.308-0.381 3.25-1-1.009-0.393-2.103-0.625-3.25-0.625z" fill="#7f8c8d" transform="translate(0 1028.4)"/><path d="m17 4a5 5 0 1 1 -10 0 5 5 0 1 1 10 0z" fill="#bdc3c7" transform="translate(0 1031.4)"/><path d="m8.4062 1040.1c-0.3172 0.2-0.6094 0.3-0.9062 0.5 0.8153 1.6 2.541 2.8 4.5 2.8s3.685-1.2 4.5-2.8c-0.297-0.2-0.589-0.3-0.906-0.5-0.649 1.3-2.011 2.3-3.594 2.3s-2.9453-1-3.5938-2.3z" fill="#d35400" style="block-progression:tb;text-indent:0;color:#000000;text-transform:none"/></g></svg></div>';

		return apply_filters( 'anony_custom_gravatar', $avatar );
	}
	/**
	 * Remove embeds.
	 *
	 * @return void
	 */
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
	/**
	 * Disable emojis
	 *
	 * @return void
	 */
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
				if ( 'dns-prefetch' === $relation_type ) {
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
	/**
	 * Preloder
	 *
	 * @return void
	 */
	public function output_preloader() {
		$anofl_options     = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$preloader_timeout = $anofl_options->preloader_timeout;
		if ( '1' === $anofl_options->preloader ) : ?>
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
					}, <?php echo esc_html( $preloader_timeout ); ?>);
				};
			</script>
			<?php
		endif;
	}
	/**
	 * Disable gutenburg scripts.
	 *
	 * @return void
	 */
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
}
