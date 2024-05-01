<?php
/**
 * Public base
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.097
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();
/**
 * Public base class.
 *
 * @since      1.0.097
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/Media
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Public_Base {
	/**
	 * Helper to replace add the type="anony-delay-scripts".
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function regex_delay_scripts( $html ) {

		// Exclude inline scripts if has a specific content.
		$html = $this->exclude_inline_scripts( $html );

		$pattern     = '/<script(?!.*delay-exclude)>/is';
		$replacement = '<script type="anony-delay-scripts">';
		$html        = preg_replace( $pattern, $replacement, $html );

		$pattern     = '/<script(?![^>]*delay-exclude)([^>]*)type=("|\')text\/javascript("|\')([^>]*)>/i';
		$replacement = function ( $matches ) {
			// Don't delay wp-includes.
			if ( false === strpos( $matches[0], 'wp-includes' ) || false === strpos( $matches[0], 'defer.js' ) ) {
				return '<script' . $matches[1] . 'type="anony-delay-scripts"' . $matches[4] . '>';
			}
			return $matches[0];
		};

		$html = preg_replace_callback( $pattern, $replacement, $html );
		return $html;
	}

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
	 * Check if is taxonomy
	 *
	 * @return boolean
	 */
	public function is_tax() {
		return is_tax() || is_category() || is_tag();
	}
	/**
	 * Check if switch is on
	 *
	 * @param string       $field_name Field name.
	 * @param object|array $haystack Haystack.
	 * @return boolean
	 */
	public function is_switched_on( $field_name, $haystack ) {
		if ( is_array( $haystack ) ) {
			return ! empty( $haystack ) && ! empty( $haystack[ $field_name ] ) && '1' === $haystack[ $field_name ] ? true : false;
		} elseif ( is_object( $haystack ) ) {
			return '1' === $haystack->$field_name;
		}
		return false;
	}
	/**
	 * Check if an option is enabled for a page.
	 *
	 * @param string $option_name Option name.
	 * @return boolean
	 */
	public function is_option_enabled_for_page( $option_name ) {
		global $post;
		if ( ! $post || is_null( $post ) ) {
			return false;
		}
		$anofl_options     = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );
		if ( '1' !== $anofl_options->load_scripts_on_interaction && ( is_page() || is_front_page() ) ) {
			return $this->is_switched_on( $option_name, $optimize_per_post );
		}
		return false;
	}

	/**
	 * Check if an option is enabled for object.
	 *
	 * @param string $option_name Option name.
	 * @return boolean
	 */
	public function is_option_enabled_for_object( $option_name ) {
		$enabled = false;
		if ( is_admin() || $this->uri_strpos( 'elementor' ) ) {
			$enabled = false;
		}
		$anofl_options  = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$queried_object = get_queried_object();

		if ( is_a( $queried_object, 'WP_Taxonomy' ) || isset( $queried_object->taxonomy ) ) {
			$object_name = $queried_object->taxonomy;
			$optimze     = 'optimize_taxonomies';
		} elseif ( is_a( $queried_object, 'WP_Post_Type' ) || is_a( $queried_object, 'WP_Post' ) ) {
			$object_name = $queried_object->post_type;
			$optimze     = 'optimize_post_types';
		}
		if ( isset( $object_name ) ) {
			$option_name      = $option_name . '_' . $object_name;
			$optimize_objects = $anofl_options->$optimze;
			if ( $optimize_objects && is_array( $optimize_objects ) && in_array( $object_name, $optimize_objects, true ) && $this->is_switched_on( $option_name, $anofl_options ) ) {
				$enabled = true;
			}
		}
		return $enabled;
	}
	/**
	 * Exclude inline scripts if has a specific content.
	 *
	 * @param string $html HTML.
	 * @return string
	 */
	public function exclude_inline_scripts( $html ) {

		// Modify <script> tags that contain the string.
		$html = preg_replace_callback(
			'/<script(?![^>]*delay-exclude)(?![^>]*anony-delay-scripts)[^>]*>.*?<\/script>/is',
			function ( $_match ) {
				$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
				$exclusions    = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->delay_scripts_exclusions );
				if ( is_array( $exclusions ) ) {
					$exclusions = array_filter( $exclusions );
					foreach ( $exclusions as $exclusion ) {
						if ( ! empty( $_match[0] ) && strpos( $_match[0], $exclusion ) !== false ) {
							return str_replace( '<script', '<script delay-exclude', $_match[0] );
						}
					}
				}
				return $_match[0];
			},
			$html
		);
		return $html;
	}
}
