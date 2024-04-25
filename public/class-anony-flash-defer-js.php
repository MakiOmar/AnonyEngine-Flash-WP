<?php
/**
 * Defer JS
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
 * @subpackage Anony_Flash_Wp/Defer_JS
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Defer_Js {
	/**
	 * If url contains string
	 *
	 * @param bool $_string Search string.
	 * @return bool
	 */
	public function uri_strpos( $_string ) {
		//phpcs:disable
		if ( ( ! empty( $_SERVER['REQUEST_URI'] ) && ! empty( $_string ) && false !== strpos( $_SERVER['REQUEST_URI'], $_string ) ) ||
			( ! empty( $_SERVER['QUERY_STRING'] ) && ! empty( $_string ) && false !== strpos( $_SERVER['QUERY_STRING'], $_string ) )
		) {
			return true;
		}
		//phpcs:enable

		return false;
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
			'firebase-auth',

		);

		if ( ! empty( $anofl_options->not_to_be_defered_scripts ) ) {
			$not_to_be_defered_scripts = array_filter( ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->not_to_be_defered_scripts ) );

			if ( ! empty( $not_to_be_defered_scripts ) ) {
				$not_deferred = array_merge( $not_deferred, $not_to_be_defered_scripts );
			}
		}

		$not_deferred = apply_filters( 'anony_not_to_be_defered_scripts', $not_deferred );
		foreach ( $not_deferred as $search ) {
			if ( false !== strpos( $tag, $search ) ) {
				return $tag;
			}
		}
		return str_replace( ' src', ' defer src', $tag );
	}
}
