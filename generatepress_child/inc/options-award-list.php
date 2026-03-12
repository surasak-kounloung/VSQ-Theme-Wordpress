<?php

/**
 * Award List Options Page (Custom Repeater UI)
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Add Admin Menu
function al_add_admin_menu()
{
    add_menu_page(
        'Award List Settings',
        'Award List',
        'manage_options',
        'award-list-settings',
        'al_options_page_html',
        'dashicons-awards',
        49
    );
}
add_action('admin_menu', 'al_add_admin_menu');

// 2. Register Settings
function al_settings_init()
{
    register_setting('award_list_option_group', 'award_list_data');
}
add_action('admin_init', 'al_settings_init');

// 3. Enqueue Assets (JS/CSS)
function al_admin_assets($hook)
{
    if ('toplevel_page_award-list-settings' !== $hook) {
        return;
    }

    wp_enqueue_media(); // Enqueue WordPress Media Uploader

    wp_enqueue_style(
        'award-list-admin-css',
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-award-list.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/admin/admin-award-list.css')
    );

    wp_enqueue_script(
        'award-list-admin-js',
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-award-list.js',
        array('jquery', 'jquery-ui-sortable'),
        filemtime(get_stylesheet_directory() . '/assets/js/admin/admin-award-list.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'al_admin_assets');

// 4. Render Options Page
function al_options_page_html()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $items = get_option('award_list_data', array());
    if (! is_array($items)) {
        $items = array();
    }

?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Award List Settings</h1>
        <hr class="wp-header-end">

        <?php
        if (isset($_GET['settings-updated'])) {
            add_settings_error('award_list_data', 'award_list_settings_updated', 'Award List Updated.', 'updated');
        }
        settings_errors('award_list_data');
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('award_list_option_group');
            do_settings_sections('award-list-settings');
            ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">

                    <!-- Right Sidebar (Publish Box) -->
                    <div id="postbox-container-1" class="postbox-container">
                        <?php 
                        // Check if user is a sender
                        $is_sender = false;
                        if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
                            $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
                            $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
                        } 
                        
                        if ( $is_sender ) {
                        ?>
                        <div id="side-sortables" class="meta-box-sortables">
                            <div id="submitdiv" class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle">Publish</h2>
                                    <div class="handle-actions hide-if-no-js">
                                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Publish</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                    </div>
                                </div>
                                <div class="inside">
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <?php submit_button('Update', 'primary large', 'submit', false); ?>
                                        </div>
                                        <div class="clear"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php } ?>

                        <!-- Shortcode Usage Box -->
                        <div class="postbox">
                            <div class="postbox-header">
                                <h2 class="heading">Shortcode Usage</h2>
                            </div>
                            <div class="inside">
                                <div class="postbox-code">
                                    <code>[award_list]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงรายการรางวัลทั้งหมด</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Main Content (Left Column) -->
                    <div id="postbox-container-2" class="postbox-container">
                        <div id="normal-sortables" class="meta-box-sortables">
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle">Award Items</h2>
                                    <div class="handle-actions hide-if-no-js">
                                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Award Items</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                    </div>
                                </div>
                                <div class="inside" style="padding-top: 6px;">

                                    <div class="al-repeater-container">
                                        <?php
                                        if (! empty($items)) :
                                            foreach ($items as $index => $item) :
                                                al_render_row($index, $item);
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>

                                    <?php if ( $is_sender ) { ?>
                                    <div class="al-actions">
                                        <button class="button button-primary al-repeater-add">Add Row</button>
                                    </div>
                                    <?php } ?>

                                </div>
                            </div>
                        </div>
                    </div>

                </div><!-- #post-body -->
                <br class="clear">
            </div><!-- #poststuff -->

        </form>

        <!-- Hidden Template for New Row -->
        <script type="text/template" id="al-repeater-template">
            <?php al_render_row('{{index}}', array()); ?>
        </script>
    </div>
<?php
}

// Helper function to render a single row
function al_render_row($index, $data)
{
    $image_url = isset($data['image_url']) ? $data['image_url'] : '';
    $image_id = isset($data['image_id']) ? $data['image_id'] : '';
    $year = isset($data['year']) ? $data['year'] : '';
    $period = isset($data['period']) ? $data['period'] : '';

    // Check if user is a sender
    $is_sender = false;
    if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
        $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
        $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
    } 
?>
    <div class="al-repeater-row">
        <div class="al-row-header<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>">
            <span class="al-row-handle dashicons dashicons-menu"></span>
            <span class="al-row-number"><?php echo is_numeric($index) ? $index + 1 : ''; ?></span>
            <?php if ( $is_sender ) { ?>
            <span class="al-row-actions">
                <span class="al-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
            </span>
            <?php } ?>
        </div>
        <div class="al-row-content">
            <div class="al-row-columns">
                <!-- Image -->
                <div class="al-column" style="width: 50%;">
                    <div class="al-field">
                        <label>Image</label>
                        <div class="al-image-preview-wrapper">
                            <?php
                            $has_image = ! empty($image_url);
                            ?>
                            <div class="al-image-preview">
                                <?php if ($has_image) : ?>
                                    <img src="<?php echo esc_url($image_url); ?>">
                                <?php endif; ?>
                            </div>

                            <input type="hidden" class="al-image-url" name="award_list_data[<?php echo $index; ?>][image_url]" value="<?php echo esc_attr($image_url); ?>">
                            <input type="hidden" class="al-image-id" name="award_list_data[<?php echo $index; ?>][image_id]" value="<?php echo esc_attr($image_id); ?>">
                            
                            <?php if ( $is_sender ) { ?>
                            <button class="button al-upload-image" <?php echo $has_image ? 'style="display:none;"' : ''; ?>>Add Image</button>
                            <button class="button al-remove-image" <?php echo ! $has_image ? 'style="display:none;"' : ''; ?>>Remove</button>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Text Info -->
                <div class="al-column" style="width: 50%;">
                    <div class="al-field">
                        <label>Year</label>
                        <input type="text" name="award_list_data[<?php echo $index; ?>][year]" value="<?php echo esc_attr($year); ?>" class="widefat al-text-input<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>"<?php if ( ! $is_sender ) { ?> readonly<?php } ?>>
                    </div>
                    <div class="al-field">
                        <label>Period</label>
                        <input type="text" name="award_list_data[<?php echo $index; ?>][period]" value="<?php echo esc_attr($period); ?>" class="widefat al-text-input<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>"<?php if ( ! $is_sender ) { ?> readonly<?php } ?>>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

// 5. Shortcode [award_list]
function al_shortcode_render($atts)
{
    $items = get_option('award_list_data', array());
    if (empty($items) || !is_array($items)) {
        return '';
    }

    ob_start();
?>
    <div class="award-list-wrap">
        <?php foreach ($items as $item) :
            $image_url = isset($item['image_url']) ? $item['image_url'] : '';
            $image_id = isset($item['image_id']) ? $item['image_id'] : '';
            $year = isset($item['year']) ? $item['year'] : '';
            $period = isset($item['period']) ? $item['period'] : '';

            if (empty($image_url) && empty($year) && empty($period)) continue;
        ?>
            <div class="award-item">
                <?php if ($image_url) : ?>
                    <div class="award-image">
                        <?php if ($image_id) {
                            echo wp_get_attachment_image($image_id, 'full');
                        } else { ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr($year); ?>">
                        <?php } ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($year) : ?>
                    <div class="award-text"><?php echo esc_html($year); ?></div>
                <?php endif; ?>
                <?php if ($period) : ?>
                    <div class="award-text"><?php echo esc_html($period); ?></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('award_list', 'al_shortcode_render');
