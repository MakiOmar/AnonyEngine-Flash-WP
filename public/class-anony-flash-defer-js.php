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
}
