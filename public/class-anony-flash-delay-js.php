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
class Anony_Flash_Delay_Js extends Anony_Flash_Public_Base {
	/**
	 * Inlines the defer.js. (https://github.com/shinsenter/defer.js)
	 * A JavaScript micro-library that helps you lazy load (almost) anything. Defer.js is zero-dependency, super-efficient, and Web Vitals friendly.
	 */
	public function inline_defer_js() {
		?>
		<script data-use="defer.js">
			/*!@shinsenter/defer.js@3.4.0*/
			!(function(n){function t(e){n.addEventListener(e,B)}function o(e){n.removeEventListener(e,B)}function u(e,n,t){L?C(e,n):(t||u.lazy&&void 0===t?q:S).push(e,n)}function c(e){k.head.appendChild(e)}function i(e,n){z.call(e.attributes)[y](n)}function r(e,n,t,o){return o=(n?k.getElementById(n):o)||k.createElement(e),n&&(o.id=n),t&&(o.onload=t),o}function s(e,n,t){(t=e.src)&&((n=r(m)).rel="preload",n.as=h,n.href=t,(t=e[g](w))&&n[b](w,t),(t=e[g](x))&&n[b](x,t),c(n))}function a(e,n){return z.call((n||k).querySelectorAll(e))}function f(e,n){e.parentNode.replaceChild(n,e)}function l(t,e){a("source,img",t)[y](l),i(t,function(e,n){(n=/^data-(.+)/.exec(e.name))&&t[b](n[1],e.value)}),"string"==typeof e&&e&&(t.className+=" "+e),p in t&&t[p]()}function e(e,n,t){u(function(t){(t=a(e||N))[y](s),(function o(e,n){(e=t[E]())&&((n=r(e.nodeName)).text=e.text,i(e,function(e){"type"!=e.name&&n[b](e.name,e.value)}),n.src&&!n[g]("async")?(n.onload=n.onerror=o,f(e,n)):(f(e,n),o()))})()},n,t)}var d="Defer",m="link",h="script",p="load",v="pageshow",y="forEach",g="getAttribute",b="setAttribute",E="shift",w="crossorigin",x="integrity",A=["mousemove","keydown","touchstart","wheel"],I="on"+v in n?v:p,N=h+"[type=deferjs]",j=n.IntersectionObserver,k=n.document||n,C=n.setTimeout,L=/p/.test(k.readyState),S=[],q=[],z=S.slice,B=function(e,n){for(n=I==e.type?(o(I),L=u,A[y](t),S):(A[y](o),q);n[0];)C(n[E](),n[E]())};e(),u.all=e,u.dom=function(e,n,i,c,r){u(function(t){function o(e){c&&!1===c(e)||l(e,i)}t=!!j&&new j(function(e){e[y](function(e,n){e.isIntersecting&&(t.unobserve(n=e.target),o(n))})},r),a(e||"[data-src]")[y](function(e){e[d]!=u&&(e[d]=u,t?t.observe(e):o(e))})},n,!1)},u.css=function(n,t,e,o,i){u(function(e){(e=r(m,t,o)).rel="stylesheet",e.href=n,c(e)},e,i)},u.js=function(n,t,e,o,i){u(function(e){(e=r(h,t,o)).src=n,c(e)},e,i)},u.reveal=l,n[d]=u,L||t(I)})(this);
			 
			 
		</script>
		<?php
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
		if ( false === strpos( $src, '.js' ) ) {
			return $tag;
		}

		if ( false !== strpos( $tag, 'anony-delay-scripts' ) ) {
			return $tag;
		}
		$anofl_options = ANONY_Options_Model::get_instance( 'Anofl_Options' );
		$delay         = false;
		if ( is_admin() || '1' !== $anofl_options->load_scripts_on_interaction || $this->uri_strpos( 'elementor' ) ) {
			$delay = false; // don't break WP Admin.
		}

		if ( '1' === $anofl_options->load_scripts_on_interaction ) {
			$delay = true;
		}
		// Pages delay.
		$delay = $this->is_option_enabled_for_page( 'delay_js' );

		$delay = $this->is_option_enabled_for_object( 'delay_js' );

		if ( ! $delay ) {
			return $tag;
		}
		$exclusions     = ANONY_STRING_HELP::line_by_line_textarea( $anofl_options->delay_scripts_exclusions );
		$exclusion_list = apply_filters( 'load_scripts_on_interaction_exclude', array( 'jquery-core-js', 'wp-includes' ) );
		if ( is_array( $exclusions ) ) {
			$exclusion_list = array_merge( $exclusion_list, $exclusions );
		}
		foreach ( $exclusion_list as $target ) {
			if ( false !== strpos( $tag, $target ) ) {
				$tag = str_replace( '<script', '<script delay-exclude', $tag );
				return $tag;
			}
		}
		if ( strpos( $tag, 'text/javascript' ) !== false ) {
			$tag = str_replace( 'text/javascript', 'anony-delay-scripts', $tag );
		} elseif ( strpos( $tag, 'type' ) === false ) {
			$tag = str_replace( '<script', '<script type="anony-delay-scripts"', $tag );
		}
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
		$anony_defer_js_trigger = false;

		if ( '1' === $anofl_options->load_scripts_on_interaction ) {
			$anony_defer_js_trigger = true;
		}

		if ( '1' !== $anofl_options->load_scripts_on_interaction && ( is_page() || is_front_page() ) ) {
			global $post;
			$optimize_per_post = get_post_meta( $post->ID, 'optimize_per_post', true );

			if ( $optimize_per_post && ! empty( $optimize_per_post ) && isset( $optimize_per_post['delay_js'] ) && '1' === $optimize_per_post['delay_js'] ) {
				$anony_defer_js_trigger = true;
			}
		}

		if ( $anony_defer_js_trigger ) {
			?>
			<script data-use="defer.js">
				Defer.all('script[type="anony-delay-scripts"]', 0, true);
			</script>
			<?php
		}
	}
}
