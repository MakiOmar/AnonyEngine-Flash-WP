<?php
/**
 * Delay JS
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.097
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

defined( 'ABSPATH' ) || die();

/**
 * Delay JS class.
 *
 * @since      1.0.097
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/Delay_JS
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Delay_Js {
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
	 * Load scripts on interaction
	 *
	 * @param string $tag Tag.
	 * @param string $handle Handle.
	 * @param string $src Src.
	 * @return string
	 */
	public function load_scripts_on_interaction( $tag, $handle, $src ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$delay         = false;
		if ( is_admin() || '1' !== $anofl_options->load_scripts_on_interaction || $this->uri_strpos( 'elementor' ) ) {
			$delay = false; // don't break WP Admin.
		}

		if ( '1' === $anofl_options->load_scripts_on_interaction ) {
			$delay = true;
		}

		if ( '1' !== $anofl_options->load_scripts_on_interaction && ( is_page() || is_front_page() ) ) {
			global $post;
			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			if ( $optimize_per_post && ! empty( $optimize_per_post ) && isset( $optimize_per_post['delay_js'] ) && '1' === $optimize_per_post['delay_js'] ) {
				$delay = true;
			}
		}

		if ( ! $delay ) {
			return $tag;
		}

		$exclusions = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->delay_scripts_exclusions );
		if ( is_array( $exclusions ) ) {
			foreach ( $exclusions as $exclusion ) {
				if ( false !== strpos( $tag, $exclusion ) ) {
					$tag = str_replace( '<script', '<script delay-exclude', $tag );
					return $tag;
				}
			}
		}

		if ( false === strpos( $src, '.js' ) ) {
			return $tag;
		}

		if ( false !== strpos( $tag, 'anony-delay-scripts' ) ) {
			return $tag;
		}

		$exclusion_list = apply_filters( 'load_scripts_on_interaction_exclude', array( 'jquery-core-js', 'wp-includes' ) );

		foreach ( $exclusion_list as $target ) {
			if ( false !== strpos( $tag, $target ) ) {
				$tag = str_replace( '<script', '<script delay-exclude', $tag );
				return $tag;
			}
		}

		$tag = str_replace( 'text/javascript', 'anony-delay-scripts', $tag );

		return $tag;
	}
	/**
	 * Delay GTAG script
	 *
	 * @param string $tag_id Tag ID.
	 * @param string $defer_js_id Defer JS ID.
	 * @param string $console Console string.
	 * @return void
	 */
	public function google_tag_script( $tag_id, $defer_js_id, $console = '' ) {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		?>
		Defer.js('https://www.googletagmanager.com/gtag/js?id=<?php echo esc_html( $tag_id ); ?>', '<?php echo esc_html( $defer_js_id ); ?>', 1500, 
		function() {
			window.dataLayer = window.dataLayer || [];
			dataLayer.push(['js', new Date()]);
			dataLayer.push(['config', '<?php echo esc_html( $tag_id ); ?>']);
			console.info('<?php echo esc_html( $console ); ?>'); // debug.
		}, false);

		<?php
	}

	/**
	 * Load Google tag manager deferred depending on defer.js
	 */
	public function defer_gtgm() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$gads_id       = $anofl_options->gads_id;
		$ganalytics_id = $anofl_options->ganalytics_id;
		if ( ! empty( $anofl_options->gtgm_id ) ) {
			?>
			<script>
				<?php
				$this->google_tag_script( $anofl_options->gtgm_id, 'google-tag-main', 'Google tag manager is loaded' );

				if ( ! empty( $gads_id ) ) {
					$this->google_tag_script( $gads_id, 'google-tag-ads', 'Google ADs tag is loaded' );
				}

				if ( ! empty( $ganalytics_id ) ) {
					$this->google_tag_script( $ganalytics_id, 'google-tag-analytics', 'Google analytics tag is loaded' );
				}
				?>
		</script>
			<?php
			$this->gtag_events();
		}
	}
	/**
	 * Gtag events
	 *
	 * @return void
	 */
	public function gtag_events() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$gtm_events    = $anofl_options->gtm_events;
		if ( ! empty( $gtm_events ) ) {
			?>
			<script type="anony-gtag-events-scripts">
			<?php
			//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $gtm_events;
			//phpcs:enable
			?>
			</script>

			<script data-use="defer.js">
				Defer.all('script[type="anony-gtag-events-scripts"]', 2800);
			</script>
			<?php
		}
	}
	/**
	 * Load Facebook pixel deferred depending on defer.js
	 */
	public function defer_facebook_pixel() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );

		if ( ! empty( $anofl_options->facebook_pixel_id ) ) {
			?>
			<!-- Meta Pixel Code -->
			<script type="anony-facebook-pixel">

				!function(f,b,e,v,n,t,s)
				{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
				n.callMethod.apply(n,arguments):n.queue.push(arguments)};
				if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
				n.queue=[];t=b.createElement(e);t.async=!0;
				t.src=v;s=b.getElementsByTagName(e)[0];
				s.parentNode.insertBefore(t,s)}(window, document,'script',
				'https://connect.facebook.net/en_US/fbevents.js');
				fbq('init', '<?php echo esc_html( $anofl_options->facebook_pixel_id ); ?>');
				fbq('track', 'PageView');

			</script>

			<noscript>
				<img height="1" width="1" style="display:none"
				src="https://www.facebook.com/tr?id=<?php echo esc_html( $anofl_options->facebook_pixel_id ); ?>&ev=PageView&noscript=1"
				/>
			</noscript>
			<!-- End Meta Pixel Code -->

			<script data-use="defer.js">
				Defer.all('script[type="anony-facebook-pixel"]', 1500);
			</script>
			<?php
		}
	}

	/**
	 * Load external inline scripts deferred depending on defer.js
	 */
	public function defer_inline_external_scripts() {
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		if ( ! empty( $anofl_options->external_scripts ) ) {
			?>
			<script type="anony-external-scripts">
			<?php
			//phpcs:disable
			echo $anofl_options->external_scripts;
			//phpcs:enable
			?>
			</script>

			<script data-use="defer.js">
				Defer.all('script[type="anony-external-scripts"]', 1500);
			</script>
			<?php
		}

		if ( '1' === $anofl_options->load_scripts_on_interaction ) {

			?>
			<script data-use="defer.js">
				Defer.all('script[type="anony-delay-scripts"]', 0, true);
			</script>
			<?php
		}

		if ( '1' !== $anofl_options->load_scripts_on_interaction && ( is_page() || is_front_page() ) ) {
			global $post;
			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			if ( $optimize_per_post && ! empty( $optimize_per_post ) && isset( $optimize_per_post['delay_js'] ) && '1' === $optimize_per_post['delay_js'] ) {
				?>
				<script data-use="defer.js">
					Defer.all('script[type="anony-delay-scripts"]', 0, true);
				</script>
				<?php
			}
		}
	}
}
