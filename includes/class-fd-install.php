<?php
/**
 * Installation related functions and actions.
 *
 * @class    FD_Install
 * @version  1.0.0
 * @package  FrontendDialog/Classes
 * @category Admin
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FD_Install Class.
 */
class FD_Install
{
    /** @var array DB updates and callbacks that need to be run per version */
    private static $db_updates = array(
        '1.0.0' => array(
            'ur_update_100_db_version',
        ),
    );

    /** @var object Background update class */
    private static $background_updater;

    /**
     * Hook in tabs.
     */
    public static function init()
    {
        add_action('init', array(__CLASS__, 'check_version'), 5);
        add_action('init', array(__CLASS__, 'init_background_updater'), 5);
        add_action('admin_init', array(__CLASS__, 'install_actions'));
        add_action('in_plugin_update_message-frontend-dialog/frontend-dialog.php', array(__CLASS__, 'in_plugin_update_message'));
        add_filter('plugin_action_links_' . FD_PLUGIN_BASENAME, array(__CLASS__, 'plugin_action_links'));
        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);
    }

    /**
     * Init background updates.
     */
    public static function init_background_updater()
    {
        include_once('class-fd-background-updater.php');
        self::$background_updater = new FD_Background_Updater();
    }

    /**
     * Check FrontendDialog version and run the updater is required.
     *
     * This check is done on all requests and runs if the versions do not match.
     */
    public static function check_version()
    {
        if (!defined('IFRAME_REQUEST') && get_option('frontend_dialog_version') !== FD()->version) {
            self::install();
            do_action('frontend_dialog_updated');
        }
    }

    /**
     * Install actions when a update button is clicked within the admin area.
     *
     * This function is hooked into admin_init to affect admin only.
     */
    public static function install_actions()
    {
        if (!empty($_GET['do_update_frontend_dialog'])) {
            self::update();
            FD_Admin_Notices::add_notice('update');
        }
        if (!empty($_GET['force_update_frontend_dialog'])) {
            do_action('wp_ur_updater_cron');
            wp_safe_redirect(admin_url('options-general.php?page=frontend-dialog'));
        }
    }

    /**
     * Install FD.
     */
    public static function install()
    {
        global $wpdb;

        if (!is_blog_installed()) {
            return;
        }

        if (!defined('FD_INSTALLING')) {
            define('FD_INSTALLING', true);
        }

        // Ensure needed classes are loaded
        include_once(dirname(__FILE__) . '/admin/class-fd-admin-notices.php');

     
        // Queue upgrades wizard
        $current_ur_version = get_option('frontend_dialog_version', null);
        $current_db_version = get_option('frontend_dialog_db_version', null);

        FD_Admin_Notices::remove_all_notices();

        // No versions? This is a new install :)
        if (is_null($current_ur_version) && is_null($current_db_version) && apply_filters('frontend_dialog_enable_setup_wizard', true)) {
            set_transient('_ur_activation_redirect', 1, 30);
        }

        if (!is_null($current_db_version) && version_compare($current_db_version, max(array_keys(self::$db_updates)), '<')) {
            FD_Admin_Notices::add_notice('update');
        } else {
            self::update_db_version();
        }

        self::update_ur_version();

        // Flush rules after install
        do_action('frontend_dialog_flush_rewrite_rules');

        /*
         * Deletes all expired transients. The multi-table delete syntax is used
         * to delete the transient record from table a, and the corresponding
         * transient_timeout record from table b.
         *
         * Based on code inside core's upgrade_network() function.
         */
        $sql = "DELETE a, b FROM $wpdb->options a, $wpdb->options b
			WHERE a.option_name LIKE %s
			AND a.option_name NOT LIKE %s
			AND b.option_name = CONCAT( '_transient_timeout_', SUBSTRING( a.option_name, 12 ) )
			AND b.option_value < %d";
        $wpdb->query($wpdb->prepare($sql, $wpdb->esc_like('_transient_') . '%', $wpdb->esc_like('_transient_timeout_') . '%', time()));

        // Trigger action
        do_action('frontend_dialog_installed');
    }

    /**
     * Update FD version to current.
     */
    private static function update_ur_version()
    {
        delete_option('frontend_dialog_version');
        add_option('frontend_dialog_version', FD()->version);
    }

    /**
     * Push all needed DB updates to the queue for processing.
     */
    private static function update()
    {
        $current_db_version = get_option('frontend_dialog_db_version');
        $update_queued = false;

        foreach (self::$db_updates as $version => $update_callbacks) {
            if (version_compare($current_db_version, $version, '<')) {
                foreach ($update_callbacks as $update_callback) {
                    self::$background_updater->push_to_queue($update_callback);
                    $update_queued = true;
                }
            }
        }

        if ($update_queued) {
            self::$background_updater->save()->dispatch();
        }
    }

    /**
     * Update DB version to current.
     * @param string $version
     */
    public static function update_db_version($version = null)
    {
        delete_option('frontend_dialog_db_version');
        add_option('frontend_dialog_db_version', is_null($version) ? FD()->version : $version);
    }

    /**
     * Show plugin changes. Code adapted from W3 Total Cache.
     */
    public static function in_plugin_update_message($args)
    {
        $transient_name = 'ur_upgrade_notice_' . $args['Version'];

        if (false === ($upgrade_notice = get_transient($transient_name))) {
            $response = wp_safe_remote_get('https://plugins.svn.wordpress.org/frontend-dialog/trunk/readme.txt');

            if (!is_wp_error($response) && !empty($response['body'])) {
                $upgrade_notice = self::parse_update_notice($response['body'], $args['new_version']);
                set_transient($transient_name, $upgrade_notice, DAY_IN_SECONDS);
            }
        }

        echo wp_kses_post($upgrade_notice);
    }

    /**
     * Parse update notice from readme file
     * @param  string $content
     * @param  string $new_version
     * @return string
     */
    private static function parse_update_notice($content, $new_version)
    {
        // Output Upgrade Notice.
        $matches = null;
        $regexp = '~==\s*Upgrade Notice\s*==\s*=\s*(.*)\s*=(.*)(=\s*' . preg_quote(FD_VERSION) . '\s*=|$)~Uis';
        $upgrade_notice = '';

        if (preg_match($regexp, $content, $matches)) {
            $version = trim($matches[1]);
            $notices = (array)preg_split('~[\r\n]+~', trim($matches[2]));

            // Check the latest stable version and ignore trunk.
            if ($version === $new_version && version_compare(FD_VERSION, $version, '<')) {

                $upgrade_notice .= '<div class="ur_plugin_upgrade_notice">';

                foreach ($notices as $index => $line) {
                    $upgrade_notice .= wp_kses_post(preg_replace('~\[([^\]]*)\]\(([^\)]*)\)~', '<a href="${2}">${1}</a>', $line));
                }

                $upgrade_notice .= '</div> ';
            }
        }

        return wp_kses_post($upgrade_notice);
    }

    /**
     * Display action links in the Plugins list table.
     * @param  array $actions
     * @return array
     */
    public static function plugin_action_links($actions)
    {
        $new_actions = array(
            'settings' => '<a href="' . admin_url('options-general.php?page=frontend-dialog') . '" title="' . esc_attr(__('View Frontend Dialog Settings', 'frontend-dialog')) . '">' . __('Settings', 'frontend-dialog') . '</a>',
        );

        return array_merge($new_actions, $actions);
    }

    /**
     * Display row meta in the Plugins list table.
     * @param  array $plugin_meta
     * @param  string $plugin_file
     * @return array
     */
    public static function plugin_row_meta($plugin_meta, $plugin_file)
    {
        if ($plugin_file == FD_PLUGIN_BASENAME) {
            $new_plugin_meta = array(
                'docs' => '<a href="' . esc_url(apply_filters('frontend_dialog_docs_url', 'http://umeshghimire.com.np/frontend-dialog/')) . '" title="' . esc_attr(__('View Frontend Dialog Documentation', 'frontend-dialog')) . '">' . __('Docs', 'frontend-dialog') . '</a>',
                'support' => '<a href="' . esc_url(apply_filters('frontend_dialog_support_url', 'http://umeshghimire.com.np/frontend-dialog/')) . '" title="' . esc_attr(__('Visit Free Customer Support Forum', 'frontend-dialog')) . '">' . __('Free Support', 'frontend-dialog') . '</a>',
            );

            return array_merge($plugin_meta, $new_plugin_meta);
        }

        return (array)$plugin_meta;
    }
}

FD_Install::init();
