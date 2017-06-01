<?php
/**
 * Display notices in admin.
 *
 * @class    FD_Admin_Notices
 * @version  1.0.0
 * @package  FrontendDialog/Admin
 * @category Admin
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FD_Admin_Notices Class.
 */
class FD_Admin_Notices
{

    /**
     * Stores notices.
     * @var array
     */
    private static $notices = array();

    /**
     * Array of notices - name => callback
     * @var array
     */
    private static $core_notices = array(

        'update' => 'update_notice'
    );

    /**
     * Constructor.
     */
    public static function init()
    {
        self::$notices = get_option('frontend_dialog_admin_notices', array());

        add_action('wp_loaded', array(__CLASS__, 'hide_notices'));
        add_action('shutdown', array(__CLASS__, 'store_notices'));

        if (current_user_can('manage_options')) {
            add_action('admin_print_styles', array(__CLASS__, 'add_notices'));
        }
    }

    /**
     * Store notices to DB
     */
    public static function store_notices()
    {
        update_option('frontend_dialog_admin_notices', self::get_notices());
    }

    /**
     * Get notices.frontend_dialog
     * @return array
     */
    public static function get_notices()
    {
        return self::$notices;
    }

    /**
     * Remove all notices.
     */
    public static function remove_all_notices()
    {
        self::$notices = array();
    }

    /**
     * Show a notice.
     * @param string $name
     */
    public static function add_notice($name)
    {
        self::$notices = array_unique(array_merge(self::get_notices(), array($name)));
    }

    /**
     * Remove a notice from being displayed.
     * @param string $name
     */
    public static function remove_notice($name)
    {
        self::$notices = array_diff(self::get_notices(), array($name));
        delete_option('frontend_dialog_admin_notice_' . $name);
    }

    /**
     * See if a notice is being shown.
     * @param  string $name
     * @return boolean
     */
    public static function has_notice($name)
    {
        return in_array($name, self::get_notices());
    }

    /**
     * Hide a notice if the GET variable is set.
     */
    public static function hide_notices()
    {
        if (isset($_GET['ur-hide-notice']) && isset($_GET['_fd_notice_nonce'])) {
            if (!wp_verify_nonce($_GET['_fd_notice_nonce'], 'frontend_dialog_hide_notices_nonce')) {
                wp_die(__('Action failed. Please refresh the page and retry.', 'frontend-dialog'));
            }

            if (!current_user_can('manage_options')) {
                wp_die(__('Cheatin&#8217; huh?', 'frontend-dialog'));
            }

            $hide_notice = sanitize_text_field($_GET['ur-hide-notice']);
            self::remove_notice($hide_notice);
            do_action('frontend_dialog_hide_' . $hide_notice . '_notice');
        }
    }

    /**
     * Add notices + styles if needed.
     */
    public static function add_notices()
    {
        $notices = self::get_notices();

        if ($notices) {
            wp_enqueue_style('frontend-dialog-activation', FD()->plugin_url() . '/assets/css/activation.css', array(), FD_VERSION);
            foreach ($notices as $notice) {
                if (!empty(self::$core_notices[$notice]) && apply_filters('frontend_dialog_show_admin_notice', true, $notice)) {
                    add_action('admin_notices', array(__CLASS__, self::$core_notices[$notice]));
                } else {
                    add_action('admin_notices', array(__CLASS__, 'output_custom_notices'));
                }
            }
        }
    }

    /**
     * Add a custom notice.
     * @param string $name
     * @param string $notice_html
     */
    public static function add_custom_notice($name, $notice_html)
    {
        self::add_notice($name);
        update_option('frontend_dialog_admin_notice_' . $name, wp_kses_post($notice_html));
    }

    /**
     * Output any stored custom notices.
     */
    public static function output_custom_notices()
    {
        $notices = self::get_notices();

        if ($notices) {
            foreach ($notices as $notice) {
                if (empty(self::$core_notices[$notice])) {
                    $notice_html = get_option('frontend_dialog_admin_notice_' . $notice);

                    if ($notice_html) {
                        include('views/html-notice-custom.php');
                    }
                }
            }
        }
    }

    /**
     * If we need to update, include a message with the update button.
     */
    public static function update_notice()
    {
        if (version_compare(get_option('frontend_dialog_db_version'), FD_VERSION, '<')) {
            $updater = new FD_Background_Updater();
            if ($updater->is_updating() || !empty($_GET['do_update_frontend_dialog'])) {
                include('views/html-notice-updating.php');
            } else {
                include('views/html-notice-update.php');
            }
        } else {
            include('views/html-notice-updated.php');
        }
    }
}

FD_Admin_Notices::init();
