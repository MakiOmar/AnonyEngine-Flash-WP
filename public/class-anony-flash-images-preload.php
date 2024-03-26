<?php
/**
 * Images preload
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
class Anony_Flash_Images_Preload {
	/**
	 * Get preload_images by post meta.
	 *
	 * @return array
	 */
	public function get_preload_images_by_post_meta() {
		global $post;
		$arr = array();
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

		return $arr;
	}

	/**
	 * Get preload images by post type.
	 *
	 * @return array
	 */
	public function get_preload_images_by_post_type() {
		global $post;
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		$arr = array();
		if ( $post && ! is_null( $post ) ) {
			if ( is_array( $anofl_options->optimize_post_types ) && ! in_array( $post->post_type, $anofl_options->optimize_post_types, true ) ) {
				return $arr;
			}
			if ( ! wp_is_mobile() ) {
				$key = 'preload_desktop_images_' . $post->post_type;
			} else {
				$key = 'preload_mobile_images_' . $post->post_type;
			}
			if ( ! empty( $anofl_options->$key ) ) {
				$arr = array_merge( $arr, ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->$key ) );
			}
		}

		return $arr;
	}

	/**
	 * Get preload images by taxonomy.
	 *
	 * @return array
	 */
	public function get_preload_images_by_taxonomy() {
		$arr           = array();
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( is_tax() || is_category() || is_tag() ) {
			$term                = get_queried_object();
			$optimize_taxonomies = $anofl_options->optimize_taxonomies;
			if ( $optimize_taxonomies && is_array( $optimize_taxonomies ) && in_array( $term->taxonomy, $optimize_taxonomies, true ) ) {
				if ( ! wp_is_mobile() ) {
					$key = 'preload_desktop_images_' . $term->taxonomy;
				} else {
					$key = 'preload_mobile_images_' . $term->taxonomy;
				}

				if ( ! empty( $anofl_options->$key ) ) {
					$arr = array_merge( $arr, ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->$key ) );
				}
			}
		}
		return $arr;
	}

	/**
	 * Preload images sitewide
	 *
	 * @return array
	 */
	public function sitewide_preload_images() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$arr           = array();
		if ( ! empty( $anofl_options->preload_images ) ) {
			$arr = array_merge( $arr, ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->preload_images ) );
		}
		return $arr;
	}
	/**
	 * Preload images
	 *
	 * @return void
	 */
	public function preload_images() {

		if ( ! class_exists( 'ANONY_STRING_HELP' ) ) {
			return;
		}

		$sitewide_preload_images         = $this->sitewide_preload_images();
		$preload_images_by_post_meta     = $this->get_preload_images_by_post_meta();
		$get_preload_images_by_post_type = $this->get_preload_images_by_post_type();
		$get_preload_images_by_taxonomy  = $this->get_preload_images_by_taxonomy();

		$arr = array_merge(
			$sitewide_preload_images,
			$preload_images_by_post_meta,
			$get_preload_images_by_post_type,
			$get_preload_images_by_taxonomy
		);
		if ( ! is_array( $arr ) || empty( $arr ) ) {
			return;
		}
		foreach ( $arr as $line ) {
			?>
				<link rel="preload" data-by="anony-flash-wp" as="image" href="<?php echo esc_url( $line ); ?>"/>
			<?php
		}
	}
}
