<?php
/**
 * Scripts
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.093
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();

/**
 * Scripts class.
 *
 * @since      1.0.093
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/includes
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Scripts {
	/**
	 * Add interaction calback to head
	 *
	 * @return void
	 */
	public function interaction_events_callback() {
		?>
		<script data-use="defer.js">
			function interactionEventsCallback( callBack ) {
				document.body.addEventListener('mousemove', callBack);
				document.body.addEventListener('scroll', callBack);
				document.body.addEventListener('keydown', callBack);
				document.body.addEventListener('click', callBack);
				document.body.addEventListener('touchstart', callBack);
			}
		</script>
		<?php
	}
	/**
	 * Add footer scripts
	 *
	 * @return void
	 */
	public function anony_add_footer_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->footer_scripts ) ) {
			//phpcs:disable
			echo $anofl_options->footer_scripts;
			//phpcs:enable
		}
	}
	/**
	 * Disable jquery migrate
	 *
	 * @param object $scripts Scribts object.
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

	/**
	 * Head scripts
	 *
	 * @return void
	 */
	public function anony_add_head_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->head_scripts ) ) {
			//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $anofl_options->head_scripts;
			//phpcs:enable
		}
	}
	/**
	 * Dequeue scripts
	 *
	 * @return void
	 */
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
	/**
	 * Remove unused scripts
	 *
	 * @param string $tag Tag.
	 * @return string
	 */
	public function remove_unused_scripts( $tag ) {
		//phpcs:disable
		if ( ! is_singular() || ! empty( $_GET['list_scripts'] ) ) {
			return $tag;
		}
		//phpcs:enable

		global $post;
		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

		if ( ! empty( $optimize_per_post ) && ! empty( $optimize_per_post['unloaded_js'] ) ) {
			foreach ( $optimize_per_post['unloaded_js'] as $script ) {
				if ( ! empty( $script ) && false !== strpos( $tag, $script ) ) {
					return '';
				}
			}
		}
		return $tag;
	}
}
