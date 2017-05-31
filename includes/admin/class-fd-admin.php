<?php
/**
 * FrontendDialog Admin.
 *
 * @class    FD_Admin
 * @version  1.0.0
 * @package  FrontendDialog/Admin
 * @category Admin
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FD_Admin Class
 */
class FD_Admin
{

    /**
     * Hook in tabs.
     */
    public function __construct()
    {

        add_action('init', array($this, 'includes'));
    }

    /**
     * Includes any classes we need within admin.
     */
    public function includes()
    {

        include_once(FD_ABSPATH . 'includes' . FD_DS . 'admin' . FD_DS . 'class-fd-admin-assets.php');

        include_once(dirname(__FILE__) . '/class-fd-admin-notices.php');

    }
}

return new FD_Admin();
