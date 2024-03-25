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

		$sitewide_preload_images     = $this->sitewide_preload_images();
		$preload_images_by_post_meta = $this->get_preload_images_by_post_meta();
		$arr                         = array_merge( $sitewide_preload_images, $preload_images_by_post_meta );
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
