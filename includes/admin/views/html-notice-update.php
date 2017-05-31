<?php
/**
 * Admin View: Notice - Update
 */

if (!defined('ABSPATH')) {
    exit;
}

?>
<div id="message" class="updated frontend-dialog-message ur-connect">
    <p><strong><?php _e('Frontend Dialog Data Update', 'frontend-dialog'); ?></strong>
        &#8211; <?php _e('We need to update your site\'s database to the latest version.', 'frontend-dialog'); ?></p>
    <p class="submit"><a
                href="<?php echo esc_url(add_query_arg('do_update_frontend_dialog', 'true', admin_url('options-general.php?page=frontend-dialog'))); ?>"
                class="ur-update-now button-primary"><?php _e('Run the updater', 'frontend-dialog'); ?></a></p>
</div>
<script type="text/javascript">
    jQuery('.ur-update-now').click('click', function () {
        return window.confirm('<?php echo esc_js(__('It is strongly recommended that you backup your database before proceeding. Are you sure you wish to run the updater now?', 'frontend-dialog')); ?>'); // jshint ignore:line
    });
</script>
