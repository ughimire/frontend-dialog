<?php
/**
 * FrontendDialog Admin Assets
 *
 * Load Admin Assets.
 *
 * @class    FD_Admin_Assets
 * @version  1.0.0
 * @package  FrontendDialog/Admin
 * @category Admin
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FD_Admin_Assets Class
 */
class FD_Admin_Assets
{

    /**
     * Hook in tabs.
     */
    public function __construct()
    {

        add_action('admin_enqueue_scripts', array($this, 'admin_styles'));
        add_action('admin_enqueue_scripts', array($this, 'admin_scripts'));
    }

    /**
     * Enqueue styles.
     */
    public function admin_styles()
    {

        global $wp_scripts;

        $screen = get_current_screen();

        $screen_id = $screen ? $screen->id : '';

        $jquery_version = isset($wp_scripts->registered['jquery-ui-core']->ver) ? $wp_scripts->registered['jquery-ui-core']->ver : '1.9.2';

        wp_register_style('jquery-ui-style', '//code.jquery.com/ui/' . $jquery_version . '/themes/smoothness/jquery-ui.css', array(), $jquery_version);


        wp_enqueue_style('jquery-ui-style');


    }

    /**
     * Enqueue scripts.
     */
    public function admin_scripts()
    {

        $screen = get_current_screen();

        $screen_id = $screen ? $screen->id : '';

        $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

        // Register Scripts
        wp_register_script('frontend-dialog-admin', UR()->plugin_url() . '/assets/js/admin/admin' . $suffix . '.js', array(
            'jquery'
        ), FD_VERSION);

        
        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'user_input_dropped' => wp_create_nonce('user_input_dropped_nonce')
        );

        wp_localize_script('frontend-dialog-admin', 'frontend_dialog_admin_data', $params);


    }
}

new FD_Admin_Assets();
