<?php
/**
 * Styles
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.093
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();

/**
 * Styles class.
 *
 * @since      1.0.093
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/includes
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Styles extends Anony_Flash_Public_Base {
	/**
	 * Dequeue unwanted styles
	 *
	 * @return void
	 */
	public function dequeued_styles() {
		if ( is_admin() || $this->uri_strpos( 'elementor' ) || ! class_exists( 'ANONY_STRING_HELP' ) ) {
			return;
		}

		$anofl_options   = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$dequeued_styles = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->dequeued_styles );

		if ( is_singular() ) {
			global $post;
			$optimize_per_post    = get_post_meta( $post->ID, 'optimize_per_post', true );
			$post_dequeued_styles = ! empty( $optimize_per_post['dequeued_styles'] ) ? ANONY_STRING_HELP::line_by_line_textarea( $optimize_per_post['dequeued_styles'] ) : array();
			$dequeued_styles      = array_merge( $dequeued_styles, $post_dequeued_styles );
		}

		if ( is_array( $dequeued_styles ) ) {
			foreach ( $dequeued_styles as $handle ) {

				wp_dequeue_style( $handle );
				wp_deregister_style( $handle );

			}
		}
	}
	/**
	 * Remove unused stylesheets
	 *
	 * @param string $tag Tag.
	 * @return string
	 */
	public function remove_unused_stylesheets( $tag ) {
		//phpcs:disable
		if ( ! is_singular() || ! empty( $_GET['list_stylesheets'] ) ) {
			return $tag;
		}
		//phpcs:enable

		global $post;
		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		if ( ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['unloaded_css'] ) ) {
			foreach ( $optimize_per_post['unloaded_css'] as $stylesheet ) {
				if ( ! empty( $stylesheet ) && false !== strpos( $tag, $stylesheet ) ) {
					return '';
				}
			}
		}
		return $tag;
	}
	/**
	 * Disable dashicons
	 *
	 * @return void
	 */
	public function disable_dashicons() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_dequeue_style( 'dashicons' );
		}
	}
}
