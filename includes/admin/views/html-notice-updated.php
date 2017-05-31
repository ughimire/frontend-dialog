<?php
/**
 * Admin View: Notice - Updated
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div id="message" class="updated frontend-dialog-message ess-connect">
    <a class="frontend-dialog-message-close notice-dismiss"
       href="<?php echo esc_url(wp_nonce_url(add_query_arg('ur-hide-notice', 'update', remove_query_arg('do_update_frontend_dialog')), 'frontend_dialog_hide_notices_nonce', '_ur_notice_nonce')); ?>"><?php _e('Dismiss', 'frontend-dialog'); ?></a>

    <p><?php _e('Frontend Dialog data update complete. Thank you for updating to the latest version!', 'frontend-dialog'); ?></p>
</div>
