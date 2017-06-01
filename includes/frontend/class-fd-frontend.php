<?php
/**
 * FrontendDialog Frontend.
 *
 * @class    FD_Admin
 * @version  1.0.0
 * @package  FrontendDialog/Frontend
 * @category Admin
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * FD_Admin Class
 */
class FD_Frontend
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

        include_once(FD_ABSPATH . 'includes' . FD_DS . 'frontend' . FD_DS . 'class-fd-frontend-assets.php');

       
    }
}

return new FD_Frontend();
