<?php

/**
 * Options Detail Branch Page
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Add Submenu Page
$detail_branch_page_hook = '';

function detail_branch_add_submenu()
{
    global $detail_branch_page_hook;
    $detail_branch_page_hook = add_submenu_page(
        'edit.php?post_type=page_branch', // Parent slug
        'Options Detail Branch',          // Page title
        'Detail Branch',                  // Menu title
        'manage_options',                 // Capability
        'options-detail-branch',          // Menu slug
        'detail_branch_page_html'         // Callback function
    );
}
add_action('admin_menu', 'detail_branch_add_submenu');

// 2. Register Settings
function detail_branch_settings_init()
{
    register_setting('detail_branch_option_group', 'detail_branch_data');
}
add_action('admin_init', 'detail_branch_settings_init');

// 3. Enqueue Assets
function detail_branch_admin_assets($hook)
{
    global $detail_branch_page_hook;
    if ($hook !== $detail_branch_page_hook) {
        return;
    }

    wp_enqueue_style(
        'detail-branch-admin-css',
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-detail-branch.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/admin/admin-detail-branch.css')
    );

    wp_enqueue_script(
        'detail-branch-admin-js',
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-detail-branch.js',
        array('jquery', 'jquery-ui-sortable'),
        filemtime(get_stylesheet_directory() . '/assets/js/admin/admin-detail-branch.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'detail_branch_admin_assets');

// 4. Render Options Page
function detail_branch_page_html()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $data = get_option('detail_branch_data', array());

    // Defaults
    $branch_count = isset($data['branch_count']) ? $data['branch_count'] : '';
    $branch_about = isset($data['branch_about']) ? $data['branch_about'] : '';
    $branch_description = isset($data['branch_description']) ? $data['branch_description'] : '';
    $youtube_list = isset($data['youtube_list']) && is_array($data['youtube_list']) ? $data['youtube_list'] : array();

?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Detail Branch Settings</h1>
        <hr class="wp-header-end">

        <?php
        if (isset($_GET['settings-updated'])) {
            add_settings_error('detail_branch_data', 'detail_branch_settings_updated', 'Detail Branch Updated.', 'updated');
        }
        settings_errors('detail_branch_data');
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('detail_branch_option_group');
            do_settings_sections('detail-branch-settings');
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
                                        <h2 class="heading">Publish</h2>
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
                                    <code>[branch_count]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงจำนวนสาขาทั้งหมด</p>
                                </div>
                                <hr>
                                <div class="postbox-code">
                                    <code>[branch_about]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงข้อมูลฟิลด์ Branch About</p>
                                </div>
                                <hr>
                                <div class="postbox-code">
                                    <code>[branch_description]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงข้อมูลฟิลด์ Branch Description</p>
                                </div>
                                <hr>
                                <div class="postbox-code">
                                    <code>[branch_youtube_list]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงข้อมูลฟิลด์ Youtube List</p>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Main Content (Left Column) -->
                    <div id="postbox-container-2" class="postbox-container">
                        <div id="normal-sortables" class="meta-box-sortables">
                            <!-- General Settings -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">Detail Branch Settings</h2>
                                </div>
                                <div class="inside">
                                    <div class="dt-field-row">
                                        <div class="dt-field-col">
                                            <label>Branch Count Number</label>
                                            <input type="number" name="detail_branch_data[branch_count]" value="<?php echo esc_attr($branch_count); ?>" <?php if (! $is_sender) { ?> class="hide-click" readonly<?php } ?>>
                                        </div>
                                    </div>
                                    <div class="dt-field-row">
                                        <div class="dt-field-col" style="width: 100%;">
                                            <label>Branch About</label>
                                            <div class="admin-editor-wrapper<?php if (! $is_sender) { ?> hide-click<?php } ?>">
                                                <?php
                                                $editor_branch_about_settings = array(
                                                    'textarea_name' => 'detail_branch_data[branch_about]',
                                                    'media_buttons' => false,
                                                    'textarea_rows' => 5,
                                                    'teeny' => false,
                                                    'quicktags' => true,
                                                    'drag_drop_upload' => false,
                                                    'wpautop' => false,
                                                    'tinymce' => array(
                                                        'wp_autoresize_on' => false,
                                                        'resize' => false,
                                                        'force_p_newlines' => false,
                                                        'force_br_newlines' => true,
                                                        'forced_root_block' => '',
                                                        'remove_linebreaks' => false,
                                                        'convert_newlines_to_brs' => true,
                                                        'remove_trailing_brs' => false,
                                                    ),
                                                    // บังคับให้เริ่มที่โหมด HTML เสมอ แก้ไข error setBaseAndExtent
                                                    'default_editor' => 'html',
                                                );

                                                if (! $is_sender) {
                                                    $editor_branch_about_settings['tinymce']['readonly'] = true;
                                                }

                                                wp_editor($branch_about, 'detail_branch_about', $editor_branch_about_settings);

                                                if (! $is_sender) {
                                                ?>
                                                    <script>
                                                        jQuery(document).ready(function($) {
                                                            $('#detail_branch_about').prop('readonly', true);
                                                        });
                                                    </script>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dt-field-row" style="margin-bottom: 0;">
                                        <div class="dt-field-col" style="width: 100%;">
                                            <label>Branch Description</label>
                                            <div class="admin-editor-wrapper<?php if (! $is_sender) { ?> hide-click<?php } ?>">
                                                <?php
                                                $editor_branch_description_settings = array(
                                                    'textarea_name' => 'detail_branch_data[branch_description]',
                                                    'media_buttons' => false,
                                                    'textarea_rows' => 5,
                                                    'teeny' => false,
                                                    'quicktags' => true,
                                                    'drag_drop_upload' => false,
                                                    'wpautop' => false,
                                                    'tinymce' => array(
                                                        'wp_autoresize_on' => false,
                                                        'resize' => false,
                                                        'force_p_newlines' => false,
                                                        'force_br_newlines' => true,
                                                        'forced_root_block' => '',
                                                        'remove_linebreaks' => false,
                                                        'convert_newlines_to_brs' => true,
                                                        'remove_trailing_brs' => false,
                                                    ),
                                                    // บังคับให้เริ่มที่โหมด HTML เสมอ แก้ไข error setBaseAndExtent
                                                    'default_editor' => 'html',
                                                );

                                                if (! $is_sender) {
                                                    $editor_branch_description_settings['tinymce']['readonly'] = true;
                                                }

                                                wp_editor($branch_description, 'detail_branch_description', $editor_branch_description_settings);

                                                if (! $is_sender) {
                                                ?>
                                                    <script>
                                                        jQuery(document).ready(function($) {
                                                            $('#detail_branch_description').prop('readonly', true);
                                                        });
                                                    </script>
                                                <?php
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Youtube List -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">Youtube List</h2>
                                </div>
                                <div class="inside" style="padding-top: 0;">

                                    <div class="dt-repeater-container">
                                        <?php
                                        if (! empty($youtube_list)) :
                                            foreach ($youtube_list as $index => $row) :
                                                detail_branch_render_row_youtube($index, $row);
                                            endforeach;
                                        endif;
                                        ?>
                                    </div>

                                    <?php if ($is_sender) { ?>
                                        <div class="dt-actions" style="margin-top: 20px;">
                                            <button class="button button-primary dt-repeater-add">Add Youtube Item</button>
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
        <script type="text/template" id="dt-repeater-youtube-template">
            <?php detail_branch_render_row_youtube('{{index}}', array()); ?>
        </script>
    </div>
<?php
}

// Helper to render a single row
function detail_branch_render_row_youtube($index, $data)
{
    // Extract values
    $title_youtube = isset($data['title_youtube']) ? $data['title_youtube'] : '';
    $id_video_youtube = isset($data['id_video_youtube']) ? $data['id_video_youtube'] : '';

    // Check if user is a sender
    $is_sender = false;
    if (defined('VSQ_SYNC_OPTION_KEY')) {
        $vsq_settings = get_option(VSQ_SYNC_OPTION_KEY, array());
        $is_sender = isset($vsq_settings['role']) && $vsq_settings['role'] === 'sender';
    }

?>
    <div class="dt-repeater-row">
        <div class="dt-row-header<?php if (! $is_sender) { ?> hide-click<?php } ?>">
            <span class="dt-row-handle dashicons dashicons-menu"></span>
            <span class="dt-row-title">Video Youtube Item</span>
            <div class="dt-row-actions">
                <span class="dt-toggle-row dashicons dashicons-minus"></span>
                <?php if ($is_sender) { ?>
                    <span class="dt-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
                <?php } ?>
            </div>
        </div>
        <div class="dt-row-content">

            <div class="dt-field-row" style="margin-bottom: 0;">
                <div class="dt-field-col col-1-2">
                    <label>Title Youtube</label>
                    <textarea name="detail_branch_data[youtube_list][<?php echo $index; ?>][title_youtube]" rows="3" style="width: 100%;" <?php if (! $is_sender) { ?> class="hide-click" readonly<?php } ?>><?php echo esc_attr($title_youtube); ?></textarea>
                </div>
                <div class="dt-field-col col-1-2">
                    <label>ID Video Youtube</label>
                    <input type="text" name="detail_branch_data[youtube_list][<?php echo $index; ?>][id_video_youtube]" value="<?php echo esc_attr($id_video_youtube); ?>" <?php if (! $is_sender) { ?> class="hide-click" readonly<?php } ?>>
                </div>
            </div>

        </div>
    </div>
<?php
}

// 5. Shortcode [branch_count]
function branch_count_shortcode()
{
    $data = get_option('detail_branch_data', array());

    $branch_count = isset($data['branch_count']) ? $data['branch_count'] : '';

    return esc_html($branch_count);
}
add_shortcode('branch_count', 'branch_count_shortcode');

// 5.1 Shortcode [branch_about]
function branch_about_shortcode()
{
    $data = get_option('detail_branch_data', array());

    $branch_about = isset($data['branch_about']) ? $data['branch_about'] : '';

    return wp_kses_post($branch_about);
}
add_shortcode('branch_about', 'branch_about_shortcode');

// 5.2 Shortcode [branch_description]
function branch_description_shortcode()
{
    $data = get_option('detail_branch_data', array());

    $branch_description = isset($data['branch_description']) ? $data['branch_description'] : '';

    return wp_kses_post($branch_description);
}
add_shortcode('branch_description', 'branch_description_shortcode');

// 5.3 Shortcode [branch_youtube_list]
function branch_youtube_list_shortcode()
{
    $data = get_option('detail_branch_data', array());

    if (empty($data) || ! isset($data['youtube_list']) || empty($data['youtube_list'])) {
        return '';
    }

    $youtube_list = $data['youtube_list'];

    ob_start();
?>
    <div class="kb-row-layout-wrap alignnone wp-block-kadence-rowlayout branch-youtube-list">
        <div class="kt-row-column-wrap kt-has-2-columns kt-row-layout-equal kt-tab-layout-inherit kt-mobile-layout-row kt-row-valign-top">
            <?php foreach ($youtube_list as $row) :
                $title_youtube = isset($row['title_youtube']) ? $row['title_youtube'] : '';
                $id_video_youtube = isset($row['id_video_youtube']) ? $row['id_video_youtube'] : '';
            ?>
                <div class="wp-block-kadence-column inner-column-1">
                    <div class="kt-inside-inner-col">
                        <p class="has-text-align-center"><?php echo nl2br(esc_html($title_youtube)); ?></p>

                        <figure class="wp-block-embed is-type-video is-provider-youtube wp-block-embed-youtube wp-embed-aspect-16-9 wp-has-aspect-ratio">
                            <div class="wp-block-embed__wrapper">
                                <iframe title="<?php echo esc_html($title_youtube); ?>" width="840" height="473" src="https://www.youtube.com/embed/<?php echo esc_html($id_video_youtube); ?>?feature=oembed&#038;enablejsapi=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                        </figure>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_shortcode('branch_youtube_list', 'branch_youtube_list_shortcode');
