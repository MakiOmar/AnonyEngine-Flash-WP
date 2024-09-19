<?php
/**
 * Media
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.097
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();

/**
 * Defer JS class.
 *
 * @since      1.0.097
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/Media
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Media extends Anony_Flash_Public_Base {
	/**
	 * Filter product thumbnail size
	 *
	 * @param string $size Product thumbnail size.
	 * @return string
	 */
	public function product_custom_mobile_thumb_size_slug( $size ) {
		if ( wp_is_mobile() ) {
			$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
			if ( ! empty( $anofl_options->wc_mobile_thumb_size ) ) {
				$size = $anofl_options->wc_mobile_thumb_size;
			}
		}
		return $size;
	}
	/**
	 * Callback for wp_calculate_image_srcset_meta hook. Disables srcset meta for product thumbnail.
	 *
	 * @param  array $image_meta An array of srcsets.
	 * @return mixed False if srcset need to be disabled otherwise an array of srcsets.
	 */
	public function disable_product_mobile_srcset( $image_meta ) {
		$is_true = apply_filters( 'disable_product_mobile_srcset', true );
		if ( class_exists( 'woocommerce' ) && wp_is_mobile() && $is_true ) {
			$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

			if ( '1' === $anofl_options->wc_disable_srcset ) {
				return false;
			}

			return $image_meta;
		}

		return $image_meta;
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

			if ( ! empty( $lazyloaded_backgrounds ) ) {
				foreach ( $lazyloaded_backgrounds as $selector ) {
					$content = str_replace( $selector . ' ', $selector . ' lazyelementorbackgroundimages ', $content );
				}
			}
		}

		return $content;
	}
	/**
	 * Load bg on interaction
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function load_bg_on_interaction( $content ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$opt_targets   = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->interact_lazyload_this_classes );
		$targets       = apply_filters( 'load_bg_on_interaction', array() );
		if ( ! empty( $opt_targets ) && is_array( $opt_targets ) ) {
			$targets = array_merge( $targets, $opt_targets );
		}
		$targets = array_filter( $targets );

		if ( empty( $targets ) ) {
			return $content;
		}
		$targets = array_map(
			function ( $value ) {
				return str_replace( ' interact-hidden', '', $value );
			},
			$targets
		);

		if ( ! empty( $targets ) ) {
			foreach ( $targets as $target ) {

				$pattern = '/(?<=class="|\s)' . $target . '(?=\s|")/';
				$content = preg_replace( $pattern, '$1' . $target . ' interact-hidden$2', $content );
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
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo ob_get_clean();
		// phpcs:enable
	}
	/**
	 * Load bg on interaction styles
	 *
	 * @return void
	 */
	public function load_bg_on_interaction_styles() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$opt_targets   = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->interact_lazyload_this_classes );
		$targets       = apply_filters( 'load_bg_on_interaction', array() );
		if ( ! empty( $opt_targets ) && is_array( $opt_targets ) ) {
			$targets = array_merge( $targets, $opt_targets );
		}
		$styles = '';
		if ( ! empty( $targets ) ) {
			$targets = array_filter( $targets );
			$styles .= '<style>';

			foreach ( $targets as $target ) {
				if ( ! empty( $target ) ) {
					$styles .= '.' . $target . '.interact-hidden,';
				}
			}
			$styles  = trim( $styles, ',' );
			$styles .= '{
				background-image: none !important;
			}';
			$styles .= '</style>';
		}
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $styles;
		//phpcs:enable.
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

	/**
	 * Add missing dimensions
	 *
	 * @param string $content Content.
	 * @return string
	 */
	public function add_missing_image_dimensions( $content ) {
		//phpcs:disable
		if ( wp_doing_ajax() || ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( $_SERVER['HTTP_X_REQUESTED_WITH'] ) === 'xmlhttprequest' ) ) {
			return $content;
		}
		//phpcs:enable

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( '1' === $anofl_options->add_missing_image_dimensions ) {
			if ( '1' === $anofl_options->lazyload_images ) {
				$lazyload = true;
			} else {
				$lazyload = false;
			}

			return ANONY_IMAGES_HELP::add_missing_dimensions( $content, $lazyload );
		}
		return $content;
	}
	/**
	 * Load backgrounds on interaction sctipt
	 *
	 * @return void
	 */
	public function load_bg_on_interaction_sctipt() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$opt_targets   = array_filter( ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->interact_lazyload_this_classes ) );
		$targets       = apply_filters( 'load_bg_on_interaction', array() );
		if ( ! empty( $opt_targets ) && is_array( $opt_targets ) ) {
			$targets = array_merge( $targets, $opt_targets );
		}
		if ( empty( $targets ) ) {
			return;
		}
		$targets = array_map(
			function ( $value ) {
				return str_replace( ' interact-hidden', '', $value );
			},
			$targets
		);
		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		// Convert PHP array to JSON.
		$json_array = wp_json_encode( $targets );
		?>

		<script delay-exclude>
			document.addEventListener('DOMContentLoaded', function() {
				var loadBgOnInteract = function() {
					// Decode JSON array in JavaScript
					var jsArray = <?php echo $json_array; ?>;
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
				interactionEventsCallback( loadBgOnInteract );
			});

		</script>
		<?php
	}
	/**
	 * Lazyload images
	 *
	 * @return void
	 */
	public function lazyload_images() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( '1' === $anofl_options->lazyload_images ) {
			$page_full_lazyload_images = $this->is_option_enabled_for_page( 'full_lazyload_images' );
			?>
			<script data-use="defer.js">
				<?php if ( '1' === $anofl_options->full_lazyload_images || $page_full_lazyload_images ) { ?>
				document.addEventListener('DOMContentLoaded', function() {
					vanillaLazyload = function (){
						const lazyImages = document.querySelectorAll('img[loading="lazy"]');
						// Loop through each lazy image
						lazyImages.forEach((img) => {
							// Get the data-src and data-srcest attributes.
							var dataSrc = img.getAttribute('data-src');
							var dataSrcest = img.getAttribute('data-srcest');
							//console.log(dataSrc);
							// Set the src and srcest attributes.
							if ( null !== dataSrc ) {
								img.src = dataSrc;
							}
							
							if ( null !== dataSrcest ) {
								img.srcset = dataSrcest;
							}
						});
					};
					if ( typeof interactionEventsCallback !== 'undefined' ) {
						interactionEventsCallback( vanillaLazyload );
					}
				});
				<?php } else { ?>
					Defer.dom('img', 500);
					Defer.lazy = true;
				<?php } ?>
			</script>
			<?php
		}
	}
}
