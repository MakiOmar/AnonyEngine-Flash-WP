<?php
/**
 * CSS
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.097
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();
/**
 * CSS class.
 *
 * @since      1.0.097
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/Media
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Css extends Anony_Flash_Public_Base {
	/**
	 * Remove all stylesheets
	 *
	 * @param string $tag Tag.
	 * @return string
	 */
	public function remove_all_stylesheets( $tag ) {
		if ( false !== strpos( $tag, 'google' ) ) {
			return $tag;
		}
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( $this->is_tax() ) {

			$term                = get_queried_object();
			$option_name         = 'defer_all_styles_' . $term->taxonomy;
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if ( $optimize_taxonomies &&
				is_array( $optimize_taxonomies ) &&
				in_array( $term->taxonomy, $optimize_taxonomies, true ) &&
				'1' === $anofl_options->$option_name
			) {
				return $tag;
			}
		}
		if ( is_singular() ) {
			global $post;
			$option_name = 'defer_all_styles_' . $post->post_type;

			$optimize_post_types = $anofl_options->optimize_post_types;

			if ( $optimize_post_types &&
				is_array( $optimize_post_types ) &&
				in_array( $post->post_type, $optimize_post_types, true ) &&
				'1' === $anofl_options->$option_name
			) {
				return $tag;
			}
		}
		if ( ( $this->is_option_enabled_for_object( 'enable_used_css' ) || $this->is_option_enabled_for_page( 'enable_used_css' ) ) ) {
			if ( ! strpos( $tag, 'anony-flash-' ) ) {
				return '';
			}
		}
		return $tag;
	}
	/**
	 * Outpust above the fold css
	 *
	 * @param object $post Post object.
	 * @param array  $optimize_per_post Post optimization settings.
	 * @return string
	 */
	public function above_the_fold_css( $post, $optimize_per_post ) {
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
	/**
	 * Used css for post type
	 *
	 * @param object $post Post object.
	 * @param array  $optimize_per_post Ppgae options.
	 * @return string
	 */
	public function used_css( $post, $optimize_per_post ) {
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
	/**
	 * Initialize wp_filesystem
	 *
	 * @return void
	 */
	public function init_wp_filesystem() {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}
	}
	/**
	 * Enqueue generated css
	 *
	 * @return void
	 */
	public function enqueue_generated_css() {
		global $post;
		if ( ! $post || is_null( $post ) ) {
			return;
		}
		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );
		if ( ! $this->is_switched_on( 'external_used_css', $optimize_per_post ) ) {
			return;
		}
		// Define the path where the CSS file will be stored.
		$upload_dir  = wp_upload_dir();
		$css_dir     = $upload_dir['basedir'] . '/anony-flash-css';
		$css_url     = $upload_dir['baseurl'] . '/anony-flash-css';
		$desktop_url = $css_url . '/post-' . $post->ID . '.css';
		$mobile_url  = $css_url . '/post-' . $post->ID . '-mobile.css';
		if ( wp_is_mobile() ) {
			wp_enqueue_style( 'anony-flash-post-css-' . $post->ID, $mobile_url, array(), time() );
		} else {
			wp_enqueue_style( 'anony-flash-post-css-' . $post->ID, $desktop_url, array(), time() );
		}
	}
	/**
	 * Generate dynamic css
	 *
	 * @param int $post_id Post ID.
	 * @return mixed
	 */
	public function generate_dynamic_css( $post_id ) {
		$this->init_wp_filesystem();
		global $wp_filesystem;
		$optimize_per_post = get_post_meta( $post_id, 'optimize_per_post', true );
		if ( ! $this->is_switched_on( 'enable_used_css', $optimize_per_post ) && ! $this->is_switched_on( 'external_used_css', $optimize_per_post ) ) {
			return;
		}
		$css_comment_regex = '/\/\*.*?\*\//s';
		$newline_regex     = '/\R/';
		// Retrieve the CSS from the metafield.
		$desktop_custom_css = preg_replace( array( $css_comment_regex, $newline_regex ), '', $optimize_per_post['desktop_used_css'] );
		if ( '1' === $optimize_per_post['separate_mobile_used_css'] ) {
			$mobile_custom_css = $optimize_per_post['mobile_used_css'];
		} else {
			$mobile_custom_css = $desktop_custom_css;
		}
		$mobile_custom_css = preg_replace( array( $css_comment_regex, $newline_regex ), '', $mobile_custom_css );
		if ( empty( $desktop_custom_css ) ) {
			return;
		}

		// Define the path where the CSS file will be stored.
		$upload_dir = wp_upload_dir();
		$css_dir    = $upload_dir['basedir'] . '/anony-flash-css';
		$css_url    = $upload_dir['baseurl'] . '/anony-flash-css';

		// Ensure the directory exists.
		if ( ! $wp_filesystem->is_dir( $css_dir ) ) {
			$wp_filesystem->mkdir( $css_dir );
		}

		// Create a unique filename for the CSS file based on the post ID.
		$desktop_css_file = $css_dir . '/post-' . $post_id . '.css';
		$mobile_css_file  = $css_dir . '/post-' . $post_id . '-mobile.css';
		$stylesheets      = array();
		// Write the CSS content to the file using WP_Filesystem.
		if ( $wp_filesystem->put_contents( $desktop_css_file, $desktop_custom_css, FS_CHMOD_FILE ) ) {
			$stylesheets['descktop'] = $css_url . '/post-' . $post_id . '.css';
		}
		if ( $wp_filesystem->put_contents( $mobile_css_file, $mobile_custom_css, FS_CHMOD_FILE ) ) {
			$stylesheets['mobile'] = $css_url . '/post-' . $post_id . '-mobile.css';
		}
		return $stylesheets;
	}
	/**
	 * Strat generate dynamic css
	 *
	 * @param int $post_id Post ID.
	 * @return void
	 */
	public function start_generate_dynamic_css( $post_id ) {
		if ( ! is_admin() ) {
			return;
		}
		$this->generate_dynamic_css( $post_id );
	}

	/**
	 * Used css for post type
	 *
	 * @param object $post Post object.
	 * @param object $options Options object.
	 * @return string
	 */
	public function post_type_global_used_css( $post, $options ) {
		$style                    = '';
		$desktop_used_css         = 'desktop_used_css_' . $post->post_type;
		$separate_mobile_used_css = 'separate_mobile_used_css_' . $post->post_type;
		$mobile_used_css          = 'mobile_used_css_' . $post->post_type;

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

	/**
	 * Used css for term
	 *
	 * @param object $term Term object.
	 * @param object $options Options object.
	 * @return string
	 */
	public function taxonomy_global_used_css( $term, $options ) {
		$style                    = '';
		$desktop_used_css         = 'desktop_used_css_' . $term->taxonomy;
		$separate_mobile_used_css = 'separate_mobile_used_css_' . $term->taxonomy;
		$mobile_used_css          = 'mobile_used_css_' . $term->taxonomy;

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
	 * Load optimized css
	 *
	 * @return void
	 */
	public function load_optimized_css() {

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		if ( ! is_singular() ) {
			if ( $this->is_option_enabled_for_object( 'enable_used_css' ) ) {
				echo $this->taxonomy_global_used_css( get_queried_object(), $anofl_options );
				return;
			}
			return '';
		}

		global $post;

		if ( $this->is_option_enabled_for_object( 'enable_used_css' ) && ! $this->is_option_enabled_for_page( 'enable_used_css' ) ) {
			echo $this->post_type_global_used_css( $post, $anofl_options );
			return;
		}

		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );
		if ( $this->is_switched_on( 'enable_used_css', $optimize_per_post ) && $this->is_switched_on( 'external_used_css', $optimize_per_post ) ) {
			return;
		}
		$style = '';

		if ( $this->is_option_enabled_for_page( 'enable_used_css' ) && ! $this->is_option_enabled_for_page( 'above_the_fold_styles' ) ) {

			$style = $this->used_css( $post, $optimize_per_post );

		}

		if ( $this->is_option_enabled_for_page( 'above_the_fold_styles' ) && ! $this->is_option_enabled_for_page( 'enable_used_css' ) ) {

			$style = $this->above_the_fold_css( $post, $optimize_per_post );
		}

		// phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
		echo $style;
		// phpcs:enable.
	}
	/**
	 * Set media attribute to all
	 *
	 * @return void
	 */
	public function stylesheets_media_to_all() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
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
				<?php if ( 'interact' === $anofl_options->load_stylesheets_on ) { ?>
					interactionEventsCallback( load_deferred_stylesheets );
				<?php } ?>

				<?php if ( 'load' === $anofl_options->load_stylesheets_on ) { ?>
					window.addEventListener('load', load_deferred_stylesheets);
				<?php } ?>
			});

		</script>
		<?php
	}
	/**
	 * Conver style media tag to print
	 *
	 * @param string $tag Style tag.
	 * @return string
	 */
	public function stylesheet_media_to_print( $tag ) {
		if ( is_admin() || $this->uri_strpos( 'wp-admin' ) ) {
			return $tag;
		}
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( $this->is_tax() ) {

			$term                = get_queried_object();
			$option_name         = 'defer_all_styles_' . $term->taxonomy;
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if ( $optimize_taxonomies &&
				is_array( $optimize_taxonomies ) &&
				in_array( $term->taxonomy, $optimize_taxonomies, true ) &&
				'1' === $anofl_options->$option_name
			) {
				$method = 'interact';
				if ( 'onload' === $method ) {
					$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );
				} else {
					$tag = preg_replace( "/media='\w+'/", "media='print'", $tag );
				}

				return $tag;

			}
		}

		if ( ! is_singular() ) {
			return $tag;
		}
		global $post;

		if ( ! $post || is_null( $post ) ) {
			return $tag;
		}

		if ( is_singular() ) {

			$option_name = 'defer_all_styles_' . $post->post_type;

			$optimize_post_types = $anofl_options->optimize_post_types;

			if ( $optimize_post_types && is_array( $optimize_post_types ) && in_array( $post->post_type, $optimize_post_types, true ) && '1' === $anofl_options->$option_name ) {
				$method = 'interact';
				if ( 'onload' === $method ) {
					$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );
				} else {
					$tag = preg_replace( "/media='\w+'/", "media='print'", $tag );
				}

				return $tag;
			}
		}

		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		$defer_all_styles = ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['defer_all_styles'] ) && '1' === $optimize_per_post['defer_all_styles'] ? true : false;
		if ( $defer_all_styles ) {
			$method = 'interact';
			if ( 'onload' === $method ) {
				$tag = preg_replace( "/media='\w+'/", "media='print' onload=\"this.media='all'\"", $tag );
			} else {
				$tag = preg_replace( "/media='\w+'/", "media='print'", $tag );
			}
		}
		return $tag;
	}
	/**
	 * Injected styles
	 *
	 * @param string $tag Tag.
	 * @return string
	 */
	public function to_be_injected_styles( $tag ) {

		if ( is_admin() || $this->uri_strpos( 'wp-admin' ) || ( ! is_singular() && ! $this->is_tax() ) ) {
			return $tag;
		}

		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( $this->is_tax() ) {

			$term                = get_queried_object();
			$option_name         = 'defer_all_styles_' . $term->taxonomy;
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if ( ! $optimize_taxonomies ||
				! is_array( $optimize_taxonomies ) ||
				! in_array( $term->taxonomy, $optimize_taxonomies, true ) ||
				'1' !== $anofl_options->$option_name
			) {
				return $tag;
			}
		}
		if ( is_singular() ) {
			global $post;
			$option_name = 'defer_all_styles_' . $post->post_type;

			$optimize_post_types = $anofl_options->optimize_post_types;

			if ( ! $optimize_post_types ||
				! is_array( $optimize_post_types ) ||
				! in_array( $post->post_type, $optimize_post_types, true ) ||
				'1' !== $anofl_options->$option_name
			) {
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
						<script data-use="defer.js">
							Defer.css('<?php echo esc_url( $style_href ); ?>', '<?php echo esc_url( $style_href ); ?>', 0, function() {
								
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
			interactionEventsCallback( inject_stylesheets_upon_interact );
		</script>
		<?php
	}
}
