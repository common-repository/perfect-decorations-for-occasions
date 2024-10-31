<?php
/**
 * Plugin Name: Perfect Decorations For Occasions
 * Plugin URI: https://decorationsforoccasions.com
 * Description: Display special decorations for various holidays, seasons, and more.
 * Version: 1.2.0
 * Text Domain: perfect-decorations-for-occasions
 * Author: Mateusz Podraza, Grzegorz Pabian, Andrzej Kawula, Piotr Moćko
 * Author URI: http://www.perfect-web.co
 * License: GNU/GPL http://www.gnu.org/licenses/gpl-3.0.html
 */

// No direct access
function_exists('add_action') or die;

// Do not use any PHP 5.3+ syntax in this file
/**
 * Include the TGM_Plugin_Activation class.
 */
require_once dirname(__FILE__) . '/includes/class-tgm-plugin-activation.php';

function dfo_register_dependencies()
{
    /*
     * Array of plugin arrays. Required keys are name and slug.
     * If the source is NOT from the .org repo, then source is also required.
     */
    $plugins = array(
        array(
            'name' => 'Perfect Core Library',
            // The plugin name.
            'slug' => 'pwebcore',
            // The plugin slug (typically the folder name).
            'source' => 'https://www.perfect-web.co/downloads/perfect-core-for-wordpress/latest/pwebcore-zip?format=raw',
            // The plugin source.
            'required' => true,
            // If false, the plugin is only 'recommended' instead of required.
            'version' => '1.1.0',
            // E.g. 1.0.0. If set, the active plugin must be this version or higher. If the plugin version is higher than the plugin version installed, the user will be notified to update the plugin.
            'force_activation' => true,
            // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch.
            'force_deactivation' => false,
            // If true, plugin is deactivated upon theme switch, useful for theme-specific plugins.
            'external_url' => '',
            // If set, overrides default API URL and points to an external URL.
            'is_callable' => '',
            // If set, this callable will be be checked for availability to determine if a plugin is active.
        )
    );
    /*
     * Array of configuration settings. Amend each line as needed.
     *
     * TGMPA will start providing localized text strings soon. If you already have translations of our standard
     * strings available, please help us make TGMPA even better by giving us access to these translations or by
     * sending in a pull-request with .po file(s) with the translations.
     *
     * Only uncomment the strings in the config array if you want to customize the strings.
     */
    $config = array(
        'id' => 'tgmpa',
        // Unique ID for hashing notices for multiple instances of TGMPA.
        'default_path' => '',
        // Default absolute path to bundled plugins.
        'menu' => 'tgmpa-install-plugins',
        // Menu slug.
        'parent_slug' => 'plugins.php',
        // Parent menu slug.
        'capability' => 'edit_theme_options',
        // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
        'has_notices' => true,
        // Show admin notices or not.
        'dismissable' => false,
        // If false, a user cannot dismiss the nag message.
        'dismiss_msg' => '',
        // If 'dismissable' is false, this message will be output at top of nag.
        'is_automatic' => true,
        // Automatically activate plugins after installation or not.
        'message' => '',
        // Message to output right before the plugins table.
        'strings' => array(
            'notice_can_install_required' => _n_noop(
                'Decorations For Occasions requires the following plugin: %1$s.',
                'Decorations For Occasions requires the following plugins: %1$s.',
                'theme-slug'
            ),
            'notice_ask_to_update'            => _n_noop(
                'The following plugin needs to be updated to its latest version to ensure maximum compatibility with Decorations For Occasions: %1$s.',
                'The following plugins need to be updated to their latest version to ensure maximum compatibility with Decorations For Occasions: %1$s.',
                'theme-slug'
            ),
        )
    );
    tgmpa($plugins, $config);
}

//Register dependencies
add_action('tgmpa_register', 'dfo_register_dependencies');

if (is_file(dirname(dirname(__FILE__)) . '/pwebcore/pwebcore.php')) {
    require_once dirname(dirname(__FILE__)) . '/pwebcore/pwebcore.php';

    if (!defined('PERFECTCORE_ABSPATH')) {
        return;
    }
    //An additional check needed, since the plugin activation thingie needs 3.7
    if (!version_compare($GLOBALS['wp_version'], '3.7', '>=')) {
        function dfo_requirements_notice() {
            ?>
            <div class="error">
                <p>Perfect Decorations For Occasions plugin requires WordPress 3.7+</p>
            </div>
        <?php
        }
        add_action( 'admin_notices', 'dfo_requirements_notice' );
        return;
    }
    //Check for localhost
    $blacklist = array('127.0.0.1', "::1", '127.0.1.1');
    if(in_array($_SERVER['REMOTE_ADDR'], $blacklist)){
        function dfo_host_notice() {
            ?>
            <div class="error">
                <p>We're sorry, but Perfect Decorations For Occasions plugin does not work on localhost.</p>
            </div>
        <?php
        }
        add_action( 'admin_notices', 'dfo_host_notice' );
        return;
    }
    //Things went okay, so let's register namespace loader:
    require_once dirname(__FILE__) . '/loader.php';
    //Hooks - not using the activation hook as the dependency breaks it. Also, using wp's get_option is kinda required here
    $options = get_option('perfect-decorations-for-occasions');
    if (!$options->setup_performed) {
        //Bypass the loader and manually include the file. I know, it's bad.
        require_once(dirname(__FILE__).'/includes/Setup.php');
        \Perfect\DecorationsForOccasions\Setup::Activate();
    }
	if(!isset($options->subscription)){
		function dfo_trial_notice(){
			if(!empty($_GET['page'])){
				return;
			}
			?>
			<div class="updated position-relative display-block">
				<p><a href="admin.php?page=perfect-decorations-for-occasions">Click here</a> to activate your <strong>FREE 14 day trial</strong> and start celebrating</p>
			</div>
			<?php
		}
		add_action('admin_notices', 'dfo_trial_notice');
	}
    register_deactivation_hook(__FILE__, array('Perfect\DecorationsForOccasions\Setup', 'Deactivate'));
    //Register WP-Cron actions
    add_action('dfo_service_sync', array('Perfect\DecorationsForOccasions\Sync', 'performAssetSync'));
    add_action('dfo_service_sync', array('Perfect\DecorationsForOccasions\Sync', 'requestSubInfo'));
    add_action('dfo_daily_digest', array('Perfect\DecorationsForOccasions\Mailer', 'dailyDigest'));
    //And now run the app:
    \Perfect\DecorationsForOccasions\API::process(); //Start of by checking for API calls. Not the best solution, but hey - WP query system is idiotic
    if ( is_admin() ) {
        //No need to load admin stuff outside wp-admin...
        $admin = new \Perfect\DecorationsForOccasions\Admin();
        //Since we're in wp-admin, we can check if sync is needed and if so - perform it. That will download the whole DB dump :E
        \Perfect\DecorationsForOccasions\Sync::execute();
    } else {
        //Likewise for the site part - don't load admin stuff here
        $site = new \Perfect\DecorationsForOccasions\Site();
    }
}
