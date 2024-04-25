<?php
/**
 * Woocommerce
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.093
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();

/**
 * Woocommerce class.
 *
 * @since      1.0.093
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/includes
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Woocommerce extends Anony_Flash_Public_Base {
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
	public function dequeue_wc_scripts() {
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

		if ( '1' !== $anofl_options->wc_shop_only_scripts ) {
			return;
		}
		if ( function_exists( 'is_woocommerce' ) ) {
			if ( ! is_woocommerce() && ! is_cart() && ! is_checkout() ) {
				$this->dequeue_wc_style();
				$this->dequeue_wc_scripts();
			}
		}
	}
}
