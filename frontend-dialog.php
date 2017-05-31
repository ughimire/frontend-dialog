<?php
/**
 * Plugin Name: Frontend Dialog
 * Plugin URI: http://umeshghimire.com.np
 * Description: Show dialog on home page or any other page and on posts
 * Version: 1.0.0
 * Author: Umesh Ghimire
 * Author URI: http://umeshghimire.com.np
 * Requires at least: 4.0
 * Tested up to: 4.8
 *
 * Text Domain: frontend-dialog
 * Domain Path: /languages/
 *
 * @package  FrontendDialog
 * @category Core
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('FrontendDialog')) :

    /**
     * Main FrontendDialog Class.
     *
     * @class   FrontendDialog
     * @version 1.0.0
     */
    final class FrontendDialog
    {

        /**
         * Plugin version.
         * @var string
         */
        public $version = '1.0.0';

        /**
         * Instance of this class.
         * @var object
         */
        protected static $_instance = null;

        /**
         * Return an instance of this class.
         * @return object A single instance of this class.
         */
        public static function instance()
        {
            // If the single instance hasn't been set, set it now.
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }

            return self::$_instance;
        }

        /**
         * Cloning is forbidden.
         * @since 1.0
         */
        public function __clone()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'frontend-dialog'), '1.0');
        }

        /**
         * Unserializing instances of this class is forbidden.
         * @since 1.0
         */
        public function __wakeup()
        {
            _doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'frontend-dialog'), '1.0');
        }

        /**
         * FlashToolkit Constructor.
         */
        public function __construct()
        {
            $this->define_constants();

            $this->includes();

            $this->init_hooks();

            do_action('frontend_dialog_loaded');
        }

        /**
         * Hook into actions and filters.
         */
        private function init_hooks()
        {

            register_activation_hook(__FILE__, array('FD_Install', 'install'));

            add_action('init', array($this, 'load_plugin_textdomain'));
        }

        /**
         * Define FT Constants.
         */
        private function define_constants()
        {
            $this->define('FD_DS', DIRECTORY_SEPARATOR);
            $this->define('FD_PLUGIN_FILE', __FILE__);
            $this->define('FD_ABSPATH', dirname(__FILE__) . FD_DS);
            $this->define('FD_PLUGIN_BASENAME', plugin_basename(__FILE__));
            $this->define('FD_VERSION', $this->version);
            $this->define('FD_FORM_PATH', FD_ABSPATH . 'includes' . FD_DS . 'form' . FD_DS);
        }

        /**
         * Define constant if not already set.
         *
         * @param string $name
         * @param string|bool $value
         */
        private function define($name, $value)
        {
            if (!defined($name)) {
                define($name, $value);
            }
        }

        /**
         * What type of request is this?
         *
         * @param  string $type admin or frontend.
         *
         * @return bool
         */
        private function is_request($type)
        {
            switch ($type) {
                case 'admin' :
                    return is_admin();
                case 'frontend' :
                    return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
            }
        }

        /**
         * Includes.
         */
        private function includes()
        {
            /**
             * Class autoloader.
             */
            include_once(FD_ABSPATH . 'includes/class-fd-autoloader.php');

            /**
             * Core classes.
             */

            include_once(FD_ABSPATH . 'includes/class-fd-install.php');

            include_once(FD_ABSPATH . 'includes/class-fd-ajax.php');

            if ($this->is_request('admin')) {

                include_once(FD_ABSPATH . 'includes/admin/class-fd-admin.php');

            }

            include_once(FD_ABSPATH . 'includes/frontend/class-fd-frontend.php');

        }

        /**
         * Load Localisation files.
         *
         * Note: the first-loaded translation file overrides any following ones if the same translation is present.
         *
         * Locales found in:
         *      - WP_LANG_DIR/frontend-dialog/frontend-dialog-LOCALE.mo
         *      - WP_LANG_DIR/plugins/frontend-dialog-LOCALE.mo
         */
        public function load_plugin_textdomain()
        {
            $locale = apply_filters('plugin_locale', get_locale(), 'frontend-dialog');

            load_textdomain('frontend-dialog', WP_LANG_DIR . '/frontend-dialog/frontend-dialog-' . $locale . '.mo');
            load_plugin_textdomain('frontend-dialog', false, plugin_basename(dirname(__FILE__)) . '/languages');
        }

        /**
         * Get the plugin url.
         * @return string
         */
        public function plugin_url()
        {
            return untrailingslashit(plugins_url('/', __FILE__));
        }

        /**
         * Get the plugin path.
         * @return string
         */
        public function plugin_path()
        {
            return untrailingslashit(plugin_dir_path(__FILE__));
        }

        /**
         * Get Ajax URL.
         * @return string
         */
        public function ajax_url()
        {
            return admin_url('admin-ajax.php', 'relative');
        }
    }

endif;

/**
 * Main instance of FrontendDialog.
 *
 * Returns the main instance of FT to prevent the need to use globals.
 *
 * @since  1.0.0
 * @return FrontendDialog
 */
function FD()
{
    return FrontendDialog::instance();
}

// Global for backwards compatibility.
$GLOBALS['frontend-dialog'] = FD();
