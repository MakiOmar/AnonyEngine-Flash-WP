<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/MakiOmar
 * @since      1.0.0
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Anony_Flash_Wp
 * @subpackage Anony_Flash_Wp/public
 * @author     Makiomar <maki3omar@gmail.com>
 */
class Anony_Flash_Wp_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anony_Flash_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anony_Flash_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		// wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/anony-flash-wp-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Anony_Flash_Wp_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Anony_Flash_Wp_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/anony-flash-wp-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Filter content to add lazyload class if elemntor
	 *
	 * @since    1.0.0
	 * @param string $content  Post/page's content.
	 *
	 * @return string Filtered content with lazyload class added.
	 */
	public function elementor_add_lazyload_class($content) {

		$content = str_replace( 'elementor-section ', 'elementor-section lazyelementorbackgroundimages ', $content );
    
	    $content = str_replace( 'elementor-column-wrap ', 'elementor-column-wrap lazyelementorbackgroundimages ', $content );
		
		$content = str_replace( 'elementor-widget-wrap ', 'elementor-widget-wrap lazyelementorbackgroundimages ', $content );
		
		$content = str_replace( 'elementor-widget-container ', 'elementor-widget-container lazyelementorbackgroundimages ', $content );
		
		$content = str_replace( 'elementor-background-overlay ', 'elementor-background-overlay lazyelementorbackgroundimages ', $content );

		
		return $content;  

	}

	/**
	 * Add css to hide bg image on images with lazyelementorbackgroundimages class.
	 *
	 * @since    1.0.0
	 */
	public function lazy_elementor_background_images_css () { 
		if ( is_admin() ) return;
		global $lazy_elementor_background_images_js_added;
		if ( ! ( $lazy_elementor_background_images_js_added ) ) return; // don't add css if scripts weren't added
		ob_start(); ?>
			<style>
				.lazyelementorbackgroundimages:not(.elementor-motion-effects-element-type-background) {
					background-image: none !important; /* lazyload fix for elementor */
				}
			</style>
		<?php
		echo ob_get_clean();
	}
	
	public function lazy_elementor_background_images_js_no_jquery(){

		ob_start();?>
		
			window.onload = function() {
			  var elems = document.querySelectorAll(".lazyelementorbackgroundimages");

				[].forEach.call(elems, function(el) {
					el.classList.remove("lazyelementorbackgroundimages");
				});
			};
		<?php
		$skrip = ob_get_clean();

		$lazy_elementor_background_images_js_added = wp_add_inline_script( 'backbone', $skrip );
		
	}

	/**
	 * Add js (jQuery and Waypoint are dependencies) to remove the lazyelementorbackgroundimages class as the item approaches the viewport.
	 *
	 * @since    1.0.0
	 */
	public function lazy_elementor_background_images_js () { 

		if ( is_admin() ) return;
		
		global $lazy_elementor_background_images_js_added;

		ob_start(); ?>

		jQuery( function ( $ ) {
		
			if ( ! ( window.Waypoint ) ) {
				// if Waypoint is not available, then we MUST remove our class from all elements because otherwise BGs will never show
				$('.elementor-section.lazyelementorbackgroundimages,.elementor-column-wrap.lazyelementorbackgroundimages, .elementor-widget-wrap.lazyelementorbackgroundimages').removeClass('lazyelementorbackgroundimages');
				if ( window.console && console.warn ) {
					console.warn( 'Waypoint library is not loaded so backgrounds lazy loading is turned OFF' );
				}
				return;
			} 
			$('.lazyelementorbackgroundimages').each( function () {
				
				var $section = $( this );
				new Waypoint({
					element: $section.get( 0 ),
					handler: function( direction ) {
						//console.log( [ 'waypoint hit', $section.get( 0 ), $(window).scrollTop(), $section.offset() ] );
						$section.removeClass('lazyelementorbackgroundimages');
					},
					offset: $(window).height()*1.5 // when item is within 1.5x the viewport size, start loading it
				});
			} );
		});

		<?php
		$skrip = ob_get_clean();
		
	   	if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
			wp_enqueue_script( 'jquery' );
		}
	   											 
		$lazy_elementor_background_images_js_added = wp_add_inline_script( 'jquery', $skrip );
	}

	/**
	 * This will prevent the jQuery Migrate script from being loaded on the front end while keeping the jQuery script itself intact. It's still being loaded in the admin to not break anything there.
	 *
	 * @since    1.0.0
	 */
	public function deregister_jquery_migrate ( $scripts ) {
        if (! is_admin() && ! empty($scripts->registered['jquery']) ) {
            $scripts->registered['jquery']->deps = array_diff(
                $scripts->registered['jquery']->deps,
                [ 'jquery-migrate' ]
            );
        }
    } 

    public function disable_wp_embeds() {
        
	    // Remove the REST API endpoint.
	    remove_action('rest_api_init', 'wp_oembed_register_route');

	    // Turn off oEmbed auto discovery.
	    add_filter('embed_oembed_discover', '__return_false');

	    // Don't filter oEmbed results.
	    remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);

	    // Remove oEmbed discovery links.
	    remove_action('wp_head', 'wp_oembed_add_discovery_links');

	    // Remove oEmbed-specific JavaScript from the front-end and back-end.
	    remove_action('wp_head', 'wp_oembed_add_host_js');
	    
	    add_filter(
	        'tiny_mce_plugins', function ($plugins) {
	            return array_diff($plugins, array('wpembed'));
	        }
	    );

	    // Remove all embeds rewrite rules.
	    add_filter(
	        'rewrite_rules_array', function ($rules) {
	            foreach($rules as $rule => $rewrite) {
	                if(false !== strpos($rewrite, 'embed=true')) {
	                     unset($rules[$rule]);
	                }
	            }
	            return $rules;
	        } 
	    );

	    // Remove filter of the oEmbed result before any HTTP requests are made.
	    remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
	}

	public function disable_wp_emojis() {
	    
	    remove_action('wp_head', 'print_emoji_detection_script', 7);
	    remove_action('admin_print_scripts', 'print_emoji_detection_script');
	    remove_action('wp_print_styles', 'print_emoji_styles');
	    remove_action('admin_print_styles', 'print_emoji_styles'); 
	    remove_filter('the_content_feed', 'wp_staticize_emoji');
	    remove_filter('comment_text_rss', 'wp_staticize_emoji'); 
	    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
	    
	    /**
	     * Filter function used to remove the tinymce emoji plugin.
	     * 
	     * @param  array $plugins 
	     * @return array Difference betwen the two arrays
	     */
	    add_filter(
	        'tiny_mce_plugins', function ( $plugins ) {

	            return ( is_array($plugins) ) ? array_diff($plugins, array( 'wpemoji' )) : [];
	        } 
	    );
	    
	    
	    /**
	     * Remove emoji CDN hostname from DNS prefetching hints.
	     *
	     * @param  array $urls URLs to print for resource hints.
	     * @param  string $relation_type The relation type the URLs are printed for.
	     * @return array Difference betwen the two arrays.
	     */
	    add_filter(
	        'wp_resource_hints', function ( $urls, $relation_type ) {
	            if ('dns-prefetch' == $relation_type ) {
	                /**
	        
	          * This filter is documented in wp-includes/formatting.php 
	               */
	                $emoji_svg_url = apply_filters('emoji_svg_url', 'https://s.w.org/images/core/emoji/2/svg/');

	                $urls = array_diff($urls, array( $emoji_svg_url ));
	            }

	            return $urls;
	        }, 10, 2 
	    );
	}

	public function remove_query_strings($src, $handle){
        if(is_admin()) { 
        	return $src;
        }

		$src = remove_query_arg('ver', $src);
        return $src;
        
    }

    public function add_missing_image_Dimensions( $content ) {

		$pattern = '/<img [^>]*?src="(\w+?:\/\/[^"]+?)"[^>]*?>/iu';
		
		preg_match_all( $pattern, $content, $imgs );

		foreach ( $imgs[0] as $i => $img ) {

			if ( false !== strpos( $img, 'width=' ) && false !== strpos( $img, 'height=' ) ) {
				continue;
			}

			$img_url  = $imgs[1][ $i ];
			$img_size = @getimagesize( $img_url );

			if ( false === $img_size ) {
				continue;
			}

			$replaced_img = str_replace( '<img ', '<img ' . $img_size[3] . ' ', $imgs[0][ $i ] );
			$content      = str_replace( $img, $replaced_img, $content );
		}

		return $content;
	}

	public function dequeued_styles () {
        if ( current_user_can( 'administrator' ) || is_admin() || false !== strpos( $_SERVER['REQUEST_URI'], 'elementor' ) ) return;
        $dequeued_styles = [
            'wpml-tm-admin-bar',
            'wc-blocks-vendors-style',
            'wc-blocks-style-rtl',
            'dashicons',
            'wd-wp-gutenberg',
			'google-fonts-1',
			'allow-webp-image',
			
        ];

        $dequeued_styles = array_merge($dequeued_styles, ['wp-block-library', 'wp-block-library-theme', 'wc-block-style']);

        

        if ( wp_is_mobile() ){
        	$mobile_dequeued_styles = [
				'woocommerce-packing-slips',
				'woocommerce-pdf-invoices'
			];

	        $dequeued_styles = array_merge($dequeued_styles, $mobile_dequeued_styles);
        }


        foreach($dequeued_styles as $style){
            wp_dequeue_style($style);
            wp_deregister_style($style);
        }
    
    }

    public function dequeue_scripts(){
    	if ( is_admin() ) return;
        $dequeued_scripts = [
            'allow-webp-image',
            'jupiterx-child',
            'jupiterx-utils'
        ];

        if ( wp_is_mobile() ){
        	$dequeued_scripts[] = 'jet-vue';
        }
        


        foreach($dequeued_scripts as $script){
            wp_dequeue_script($script);
            wp_deregister_script($script);
        }
    }

    public function defer_scripts( $tag, $handle, $src ){
    	if (is_admin() || false === strpos($src, '.js') || false !== strpos($tag, 'defer') ) { 
    		return $tag; //don't break WP Admin
        }
    
        //if ( strpos( $src, 'wp-includes/js' ) ) return $tag; //Exclude all from w-includes
    
        //Try not defer all
        $not_deferred = array(
	        'wp-polyfill',
	        'wp-hooks',
	        'wp-i18n',
	        'wp-tinymce-root',
			'jquery-core'
        );

        if (in_array($handle, $not_deferred)   ) { 
        	return $tag;
        }

        return str_replace(' src', ' defer src', $tag);
    }
    public function common_injected_scripts( $tag ){

    	if(is_admin()) return $tag;

		if(preg_match("/rel='stylesheet'/im",$tag)){
			
			if( 
				false  !== strpos( $tag, 'wpml-legacy-horizontal-list' ) ||
				false  !== strpos( $tag, 'flexible_shipping_notices' ) ||
				false  !== strpos( $tag, 'jet-cw' ) ||
				false  !== strpos( $tag, 'jet-cw-frontend' ) ||
				false  !== strpos( $tag, 'jet-popup-frontend' ) ||
				false  !== strpos( $tag, 'photoswipe' ) ||
				false  !== strpos( $tag, 'photoswipe-default-skin' )
				
				
			  
			  ) {
				preg_match("/id='(.*?)'/im",$tag, $id);
				$style_id = $id[1];

				preg_match("/href='(.*?)'/im",$tag, $href);
				$style_href = $href[1];

				add_action('wp_print_footer_scripts', function() use($style_id, $style_href){?>
					<input type="hidden" class="create-style-tag" id="create-<?php echo $style_id ?>" value="<?php echo $style_href ?>"/>
				<?php  });
				return '';
			}
			
			return $tag;
		}
    }
    public function mobile_injected_scripts( $tag ){

    	if(is_admin()) return $tag;

		if(preg_match("/rel='stylesheet'/im",$tag)){
			
			if( 
				false  !== strpos( $tag, 'font-awesome-all' ) ||
			  	false  !== strpos( $tag, 'fontawesome' ) ||
				false  !== strpos( $tag, 'jet-elements-skin' ) ||			
				false  !== strpos( $tag, 'jet-menu-public-styles' ) ||
				false  !== strpos( $tag, 'font-awesome' ) ||
				false  !== strpos( $tag, 'elementor-icons' ) ||
				false  !== strpos( $tag, 'elementor-pro' ) ||
				false  !== strpos( $tag, 'e-animations' ) ||
				false  !== strpos( $tag, 'elementor-icons-shared-0' ) ||
				false  !== strpos( $tag, 'elementor-icons-fa-solid' ) ||
				false  !== strpos( $tag, 'elementor-icons-fa-brands' ) ||
				false  !== strpos( $tag, 'elementor-icons-fa-regular' ) ||
				false  !== strpos( $tag, 'elementor-icons' ) ||
				
				false  !== strpos( $tag, 'jet-menu-general' ) ||
				false  !== strpos( $tag, 'font-awesome-v4-shims' ) || 
				false  !== strpos( $tag, 'wp-block-library-theme-inline' ) ||
				false  !== strpos( $tag, 'global-styles-inline' ) ||
				
				false  !== strpos( $tag, 'jet-engine-frontend' )
				
				
			  
			  ) {
				preg_match("/id='(.*?)'/im",$tag, $id);
				$style_id = $id[1];

				preg_match("/href='(.*?)'/im",$tag, $href);
				$style_href = $href[1];

				add_action('wp_print_footer_scripts', function() use($style_id, $style_href){?>
					<input type="hidden" class="create-style-tag" id="create-<?php echo $style_id ?>" value="<?php echo $style_href ?>"/>
				<?php  });
				return '';
			}
			
			return $tag;
		}
    }

    public function inject_scripts(){
    	?>

    	<script>
            var cb = function() {
            var h = document.getElementsByTagName('head')[0];
            
            document.querySelectorAll('.create-style-tag').forEach(function(styleInput) {
                var l = document.createElement('link'); 
                l.rel = 'stylesheet';
                l.href = styleInput.value;
                l.id = styleInput.id;
                l.media = "all";
                l.type = "text/css";
                h.appendChild(l, h);
                
            });

            
            };
            var raf = requestAnimationFrame || mozRequestAnimationFrame ||
            webkitRequestAnimationFrame || msRequestAnimationFrame;
            if (raf) raf(cb);
            else window.addEventListener('load', cb);
        </script>

    	<?php
    }


}
