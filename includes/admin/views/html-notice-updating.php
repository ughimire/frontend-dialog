<?php
/**
 * Admin View: Notice - Updating
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div id="message" class="updated frontend-dialog-message ur-connect">
    <p><strong><?php _e('Frontend Dialog Data Update', 'frontend-dialog'); ?></strong>
        &#8211; <?php _e('Your database is being updated in the background.', 'frontend-dialog'); ?> <a
                href="<?php echo esc_url(add_query_arg('force_update_frontend_dialog', 'true', admin_url('options-general.php?page=frontend-dialog'))); ?>"><?php _e('Taking a while? Click here to run it now.', 'frontend-dialog'); ?></a>
    </p>
</div>
