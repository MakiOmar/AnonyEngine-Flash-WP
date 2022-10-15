<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/MakiOmar
 * @since             1.0.0
 * @package           Anony_Flash_Wp
 *
 * @wordpress-plugin
 * Plugin Name:       AnonyEngine Flash WP
 * Plugin URI:        http://makiomar.com
 * Description:       For WordPress higher speed.
 * Version:           1.0.0
 * Author:            Makiomar
 * Author URI:        https://github.com/MakiOmar
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       anony-flash-wp
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

include_once('updater.php');

if (is_admin()) { // note the use of is_admin() to double check that this is happening in the admin
    $config = array(
        'slug' => plugin_basename(__FILE__), // this is the slug of your plugin
        'proper_folder_name' => 'anony-flash-wp', // this is the name of the folder your plugin lives in
        'api_url' => 'https://api.github.com/repos/MakiOmar/AnonyEngine-Flash-WP', // the GitHub API url of your GitHub repo
        'raw_url' => 'https://raw.github.com/MakiOmar/AnonyEngine-Flash-WP/master', // the GitHub raw url of your GitHub repo
        'github_url' => 'https://github.com/MakiOmar/AnonyEngine-Flash-WP', // the GitHub url of your GitHub repo
        'zip_url' => 'https://github.com/MakiOmar/AnonyEngine-Flash-WP/zipball/master', // the zip url of the GitHub repo
        'sslverify' => true, // whether WP should check the validity of the SSL cert when getting an update, see https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/2 and https://github.com/jkudish/WordPress-GitHub-Plugin-Updater/issues/4 for details
        'requires' => '3.0', // which version of WordPress does your plugin require?
        'tested' => '3.3', // which version of WordPress is your plugin tested up to?
        'readme' => 'README.md', // which file to use as the readme for the version number
        'access_token' => '', // Access private repositories by authorizing under Plugins > GitHub Updates when this example plugin is installed
    );
    new WP_GitHub_Updater($config);
}
/**
 * Activation hook
 */ 
function anony_flash_wp_active()
{
    if (!defined("ANOENGINE")) 
    {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( 'Flash wp plugin requires AnonyEngine plugin to be installed/activated. Please install/activate AnonyEngine plugin first.' );
    }
}

register_activation_hook( __FILE__, 'anony_flash_wp_active' );


/**
 * Display a notification if one of required plugins is not activated/installed
 */
add_action( 'admin_notices', function() {
    if (!defined('ANOENGINE')) {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php esc_html_e( 'Flash wp plugin requires AnonyEngine plugin to be installed/activated. Please install/activate AnonyEngine plugin first.' ); ?></p>
        </div>
    <?php }
});

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'ANONY_FLASH_WP_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-anony-flash-wp-activator.php
 */
function activate_anony_flash_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-anony-flash-wp-activator.php';
	Anony_Flash_Wp_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-anony-flash-wp-deactivator.php
 */
function deactivate_anony_flash_wp() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-anony-flash-wp-deactivator.php';
	Anony_Flash_Wp_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_anony_flash_wp' );
register_deactivation_hook( __FILE__, 'deactivate_anony_flash_wp' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-anony-flash-wp.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_anony_flash_wp() {

	$plugin = new Anony_Flash_Wp();
	$plugin->run();

}
run_anony_flash_wp();
