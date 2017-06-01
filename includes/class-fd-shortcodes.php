<?php
/**
 * Frontend Dialog Shortcode.
 *
 * @class    FD_Shortcodes
 * @version  1.0.0
 * @package  FrontendDialog/Classes
 * @category Class
 * @author   Umesh Ghimire
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * RP_Shortcodes Class
 */
class FD_Shortcodes
{

    /**
     * Init Shortcodes.
     */
    public static function init()
    {


        $shortcodes = array(

            'frontend_dialog' => __CLASS__ . '::dialog',
        );

        foreach ($shortcodes as $shortcode => $function) {

            do_action("before_{$shortcode}_shortcode");

            add_shortcode(apply_filters("{$shortcode}_shortcode_tag", $shortcode), $function);

            do_action("after_{$shortcode}_shortcode");
        }
    }


    /**
     * Frontend dialog group shortcode.
     */
    public static function dialog($atts)
    {
        if (empty($atts)) {
            return '';
        }

        if (!isset($atts['post_id']) && !isset($atts['page_id'])) {
            return '';
        }

        if (isset($atts['post_id'])) {
            $atts = shortcode_atts(array(
                'post_id' => '',
            ), $atts, 'frontend_dialog');
        }
        if (isset($atts['page_id'])) {
            $atts = shortcode_atts(array(
                'page_id' => '',
            ), $atts, 'frontend_dialog');
        }

        $content = self::get_content($atts);

        return self::frontend_dialog_output($content);


    }

    /*
     *
     *
     * Get content page or post
     */
    public static function get_content($atts)
    {

        if (isset($atts['page_id'])) {

            $id = $atts['page_id'];

            $post_or_page = "page";

        }
        if (isset($atts['post_id'])) {

            $id = $atts['post_id'];

            $post_or_page = "post";

        }

        $args = array(


            'post_type' => $post_or_page,

            'post_status' => 'publish',

            'post__in' => array($id)
        );
        $post_data = get_posts($args);

        return $post_data;

    }


    /**
     * Output for Frontend dialog.
     */
    private static function frontend_dialog_output($content)
    {


        ob_start();

        echo "<div style='display:none'>";
        echo "<a class='fd_inline_colorbox' href='#fd_inline_colorbox_content'>Inline HTML</a><div id='fd_inline_colorbox_content'>";
        if (isset($content[0])) {
            echo $content[0]->post_content;

        } else {

            echo "<h2>" . __("Post not available") . "</h2>";
        }
        echo "</div> </div>";
        return ob_get_clean();
    }
}
