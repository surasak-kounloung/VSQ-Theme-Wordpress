<?php

/**
 * Services List Options Page (Custom Repeater UI)
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Add Admin Menu
function sl_add_admin_menu()
{
    add_menu_page(
        'Services List Settings',
        'Services List',
        'manage_options',
        'services-list-settings',
        'sl_options_page_html',
        'dashicons-screenoptions',
        41
    );
}
add_action('admin_menu', 'sl_add_admin_menu');

// 2. Register Settings
function sl_settings_init()
{
    register_setting('services_list_option_group', 'services_list_data');
}
add_action('admin_init', 'sl_settings_init');

// 3. Enqueue Assets (JS/CSS)
function sl_admin_assets($hook)
{
    if ('toplevel_page_services-list-settings' !== $hook) {
        return;
    }

    wp_enqueue_media(); // Enqueue WordPress Media Uploader

    wp_enqueue_style(
        'services-list-admin-css',
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-services-list.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/admin/admin-services-list.css')
    );

    wp_enqueue_script(
        'services-list-admin-js',
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-services-list.js',
        array('jquery', 'jquery-ui-sortable'),
        filemtime(get_stylesheet_directory() . '/assets/js/admin/admin-services-list.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'sl_admin_assets');

// 4. Render Options Page
function sl_options_page_html()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $items = get_option('services_list_data', array());
    if (! is_array($items)) {
        $items = array();
    }

?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Services List Settings</h1>
        <hr class="wp-header-end">

        <?php
        if (isset($_GET['settings-updated'])) {
            add_settings_error('services_list_data', 'services_list_settings_updated', 'Services List Updated.', 'updated');
        }
        settings_errors('services_list_data');
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('services_list_option_group');
            do_settings_sections('services-list-settings');
            ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">

                    <!-- Right Sidebar (Publish Box) -->
                    <div id="postbox-container-1" class="postbox-container">
                        <?php
                        // Check if user is a sender
                        $is_sender = false;
                        if (defined('VSQ_SYNC_OPTION_KEY')) {
                            $vsq_settings = get_option(VSQ_SYNC_OPTION_KEY, array());
                            $is_sender = isset($vsq_settings['role']) && $vsq_settings['role'] === 'sender';
                        }

                        if ($is_sender) {
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
                                    <code>[services_list]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงรายการ Services List</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Main Content (Left Column) -->
                    <div id="postbox-container-2" class="postbox-container">
                        <div id="normal-sortables" class="meta-box-sortables">
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle">Services Items</h2>
                                    <div class="handle-actions hide-if-no-js">
                                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Services Items</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                    </div>
                                </div>
                                <div class="inside" style="padding-top: 6px;">

                                    <div class="sl-repeater-container">
                                        <?php
                                        if (! empty($items)) :
                                            foreach ($items as $index => $item) :
                                                sl_render_row($index, $item);
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>

                                    <?php if ($is_sender) { ?>
                                        <div class="sl-actions">
                                            <button class="button button-primary sl-repeater-add">Add Row</button>
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
        <script type="text/template" id="sl-repeater-template">
            <?php sl_render_row('{{index}}', array()); ?>
        </script>
    </div>
<?php
}

// Helper function to render a single row
function sl_render_row($index, $data)
{
    $image_url = isset($data['image_url']) ? $data['image_url'] : '';
    $image_id = isset($data['image_id']) ? $data['image_id'] : '';
    $external_url = isset($data['external_url']) ? $data['external_url'] : '';
    $url = isset($data['url']) ? $data['url'] : '';

    // Check if user is a sender
    $is_sender = false;
    if (defined('VSQ_SYNC_OPTION_KEY')) {
        $vsq_settings = get_option(VSQ_SYNC_OPTION_KEY, array());
        $is_sender = isset($vsq_settings['role']) && $vsq_settings['role'] === 'sender';
    }

?>
    <div class="sl-repeater-row">
        <div class="sl-row-header<?php if (! $is_sender) { ?> hide-click<?php } ?>">
            <span class="sl-row-handle dashicons dashicons-menu"></span>
            <span class="sl-row-number"><?php echo is_numeric($index) ? $index + 1 : ''; ?></span>
            <?php if ($is_sender) { ?>
                <span class="sl-row-actions">
                    <span class="sl-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
                </span>
            <?php } ?>
        </div>
        <div class="sl-row-content">
            <div class="sl-row-columns">
                <!-- Image -->
                <div class="sl-column" style="width: 30%;">
                    <div class="sl-field">
                        <label>Image</label>
                        <div class="sl-image-preview-wrapper">
                            <?php
                            $has_image = ! empty($image_url);
                            ?>
                            <div class="sl-image-preview">
                                <?php if ($has_image) : ?>
                                    <img src="<?php echo esc_url($image_url); ?>">
                                <?php endif; ?>
                            </div>

                            <input type="hidden" class="sl-image-url" name="services_list_data[<?php echo $index; ?>][image_url]" value="<?php echo esc_attr($image_url); ?>">
                            <input type="hidden" class="sl-image-id" name="services_list_data[<?php echo $index; ?>][image_id]" value="<?php echo esc_attr($image_id); ?>">

                            <button class="button sl-upload-image hide-not-sender" <?php echo $has_image ? 'style="display:none;"' : ''; ?>>Add Image</button>
                            <button class="button sl-remove-image hide-not-sender" <?php echo ! $has_image ? 'style="display:none;"' : ''; ?>>Remove</button>
                        </div>
                    </div>
                </div>

                <!-- Link Info -->
                <div class="sl-column" style="width: 70%;">
                    <div class="sl-field">
                        <label>External URL</label>
                        <div class="sl-field-checkbox">
                            <input type="checkbox" class="sl-external-url-checkbox<?php if (! $is_sender) { ?> hide-click<?php } ?>" name="services_list_data[<?php echo $index; ?>][external_url]" value="1" <?php checked($external_url, '1'); ?><?php if (! $is_sender) { ?> onclick="return false;" <?php } ?>>
                            Enable
                        </div>
                    </div>
                    <div class="sl-field" style="margin-top: 15px;">
                        <label>URL Path / External URL</label>
                        <input type="text" name="services_list_data[<?php echo $index; ?>][url]" value="<?php echo esc_attr($url); ?>" placeholder="<?php echo ($external_url === '1') ? 'https://...' : '/service-path/'; ?>" class="widefat sl-url-input<?php if (! $is_sender) { ?> hide-click<?php } ?>" <?php if (! $is_sender) { ?> readonly<?php } ?>>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}

// 5. Shortcode [services_list]
function sl_shortcode_render($atts)
{
    $items = get_option('services_list_data', array());
    if (empty($items) || !is_array($items)) {
        return '';
    }

    ob_start();
?>
    <div class="kb-row-layout-wrap alignnone wp-block-kadence-rowlayout services-list">
        <div class="kt-row-column-wrap kt-row-layout-equal kt-tab-layout-inherit kt-mobile-layout-row kt-row-valign-top">
            <?php foreach ($items as $item) :
                $image_url = isset($item['image_url']) ? $item['image_url'] : '';
                $image_id = isset($item['image_id']) ? $item['image_id'] : '';
                $url = isset($item['url']) ? $item['url'] : '';
                $external = isset($item['external_url']) && $item['external_url'] === '1';
                $target = $external ? '_blank' : '';

                if (empty($image_url)) continue;
            ?>
            <div class="wp-block-kadence-column">
                <div class="kt-inside-inner-col">
                    <figure class="wp-block-image size-full">
                    <?php if ($url) { ?>
                        <a href="<?php if ( $external ) { echo esc_url($url); } else { echo esc_url( home_url( $url ) ); } ?>"<?php if ( $target ) { ?> target="<?php echo esc_attr($target); ?>"<?php } ?>>
                    <?php } ?>
                        <?php if ($image_id) { ?>
                            <?php echo wp_get_attachment_image($image_id, 'full', false, array('class' => '')); ?>
                        <?php } else { ?>
                            <img src="<?php echo esc_url($image_url); ?>" alt="Service Image">
                        <?php } ?>
                    <?php if ($url) { ?>
                        </a>
                    <?php } ?>
                    </figure>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('services_list', 'sl_shortcode_render');

// Enqueue Frontend Assets for Shortcode
function sl_frontend_assets()
{
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'services_list')) {
        wp_enqueue_style('services-list-style', get_stylesheet_directory_uri() . '/assets/css/services-list.css', array(), filemtime( get_stylesheet_directory() . '/assets/css/services-list.css' ));
    }
}
add_action('wp_enqueue_scripts', 'sl_frontend_assets', 99);