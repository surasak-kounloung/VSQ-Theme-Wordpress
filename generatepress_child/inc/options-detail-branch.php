<?php
/**
 * Options Detail Branch Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Add Submenu Page
$detail_branch_page_hook = '';

function detail_branch_add_submenu() {
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
add_action( 'admin_menu', 'detail_branch_add_submenu' );

// 2. Register Settings
function detail_branch_settings_init() {
    register_setting( 'detail_branch_option_group', 'detail_branch_data' );
}
add_action( 'admin_init', 'detail_branch_settings_init' );

// 3. Enqueue Assets
function detail_branch_admin_assets( $hook ) {
    global $detail_branch_page_hook;
    if ( $hook !== $detail_branch_page_hook ) {
        return;
    }

    wp_enqueue_style( 
        'detail-branch-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-detail-branch.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-detail-branch.css' ) 
    );

    wp_enqueue_script( 
        'detail-branch-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-detail-branch.js', 
        array( 'jquery', 'jquery-ui-sortable' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-detail-branch.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'detail_branch_admin_assets' );

// 4. Render Options Page
function detail_branch_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'detail_branch_data', array() );
    
    // Defaults
    $branch_about = isset($data['branch_about']) ? $data['branch_about'] : '';
    $branch_description = isset($data['branch_description']) ? $data['branch_description'] : '';
    $youtube_list = isset($data['youtube_list']) && is_array($data['youtube_list']) ? $data['youtube_list'] : array();
    $update_date_doctor_table = isset($data['update_date_doctor_table']) ? $data['update_date_doctor_table'] : '';

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Detail Branch Settings</h1>
        <hr class="wp-header-end">

        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'detail_branch_data', 'detail_branch_settings_updated', 'Detail Branch Updated.', 'updated' );
        }
        settings_errors( 'detail_branch_data' );
        ?>
        
        <form action="options.php" method="post">
            <?php
            settings_fields( 'detail_branch_option_group' );
            do_settings_sections( 'detail-branch-settings' );
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
                                    <h2 class="heading">Publish</h2>
                                    <div class="handle-actions hide-if-no-js">
                                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Publish</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                    </div>
                                </div>
                                <div class="inside">
                                    <div id="major-publishing-actions">
                                        <div id="publishing-action">
                                            <span class="spinner"></span>
                                            <?php submit_button( 'Update', 'primary large', 'submit', false ); ?>
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
                                    <code>[detail_branch]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงตารางข้อมูลสาขาทั้งหมด</p>
                                </div>
                                <hr>
                                <div class="postbox-code">
                                    <code>[detail_branch_update_date]</code>
                                </div>
                                <div class="postbox-code-description">
                                    <p>แสดงเฉพาะวันที่อัปเดตข้อมูล</p>
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
                                        <div class="dt-field-col" style="width: 100%;">
                                            <label>Branch About</label>
                                            <div class="admin-editor-wrapper<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>">
                                            <?php 
                                            $editor_branch_about_settings = array(
                                                'textarea_name' => 'detail_branch_data[branch_about]',
                                                'media_buttons' => false,
                                                'textarea_rows' => 5,
                                                'teeny' => false,
                                                'quicktags' => true,
                                                'drag_drop_upload' => false,
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

                                            if ( ! $is_sender ) {
                                                $editor_branch_about_settings['tinymce']['readonly'] = true;
                                            }

                                            wp_editor( $branch_about, 'detail_branch_about', $editor_branch_about_settings );

                                            if ( ! $is_sender ) {
                                                ?>
                                                <script>jQuery(document).ready(function($){ $('#detail_branch_about').prop('readonly', true); });</script>
                                                <?php
                                            }
                                            ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dt-field-row" style="margin-bottom: 0;">
                                        <div class="dt-field-col" style="width: 100%;">
                                            <label>Branch Description</label>
                                            <div class="admin-editor-wrapper<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>">
                                            <?php 
                                            $editor_branch_description_settings = array(
                                                'textarea_name' => 'detail_branch_data[branch_description]',
                                                'media_buttons' => false,
                                                'textarea_rows' => 5,
                                                'teeny' => false,
                                                'quicktags' => true,
                                                'drag_drop_upload' => false,
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

                                            if ( ! $is_sender ) {
                                                $editor_branch_description_settings['tinymce']['readonly'] = true;
                                            }

                                            wp_editor( $branch_description, 'detail_branch_description', $editor_branch_description_settings );

                                            if ( ! $is_sender ) {
                                                ?>
                                                <script>jQuery(document).ready(function($){ $('#detail_branch_description').prop('readonly', true); });</script>
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
                                        if ( ! empty( $youtube_list ) ) :
                                            foreach ( $youtube_list as $index => $row ) :
                                                detail_branch_render_row_youtube( $index, $row );
                                            endforeach;
                                        endif; 
                                        ?>
                                    </div>

                                    <?php if ( $is_sender ) { ?>
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
            <?php detail_branch_render_row_youtube( '{{index}}', array() ); ?>
        </script>
    </div>
    <?php
}

// Helper to render a single row
function detail_branch_render_row_youtube( $index, $data ) {
    // Extract values
    $title_youtube = isset( $data['title_youtube'] ) ? $data['title_youtube'] : '';
    $id_video_youtube = isset( $data['id_video_youtube'] ) ? $data['id_video_youtube'] : '';

    // Check if user is a sender
    $is_sender = false;
    if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
        $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
        $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
    } 
    
    ?>
    <div class="dt-repeater-row">
        <div class="dt-row-header<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>">
            <span class="dt-row-handle dashicons dashicons-menu"></span>
            <span class="dt-row-title">Video Youtube Item</span>
            <div class="dt-row-actions">
                 <span class="dt-toggle-row dashicons dashicons-minus"></span>
                 <?php if ( $is_sender ) { ?>
                 <span class="dt-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
                 <?php } ?>
            </div>
        </div>
        <div class="dt-row-content">

            <div class="dt-field-row" style="margin-bottom: 0;">
                <div class="dt-field-col col-1-2">
                    <label>Title Youtube</label>
                    <textarea name="detail_branch_data[youtube_list][<?php echo $index; ?>][title_youtube]" rows="3" style="width: 100%;"<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>><?php echo esc_attr( $title_youtube ); ?></textarea>
                </div>
                <div class="dt-field-col col-1-2">
                    <label>ID Video Youtube</label>
                    <input type="text" name="detail_branch_data[youtube_list][<?php echo $index; ?>][id_video_youtube]" value="<?php echo esc_attr( $id_video_youtube ); ?>"<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>>
                </div>
            </div>

        </div>
    </div>
    <?php
}

// 5. Shortcode [detail_branch]
function detail_branch_shortcode() {
    $data = get_option( 'detail_branch_data', array() );
    
    if ( empty( $data ) || ! isset( $data['body_list'] ) || empty( $data['body_list'] ) ) {
        return '';
    }

    $branch_count = isset( $data['branch_count'] ) ? $data['branch_count'] : '';
    $head_table = isset( $data['head_table'] ) ? $data['head_table'] : array();
    $body_list = $data['body_list'];
    $update_date_doctor_table = isset( $data['update_date_doctor_table'] ) ? $data['update_date_doctor_table'] : '';

    ob_start();
    ?>
    <div class="dt-table-wrapper">
        <table class="dt-doctors-table">
            <thead>
                <tr>
                    <th class="dt-head-branch"><?php echo isset($branch_count) ? esc_html($branch_count) : ''; ?></th>
                    <?php for($i=1; $i<=8; $i++): ?>
                        <th class="dt-head-col-<?php echo $i; ?>">
                            <?php echo isset($head_table[$i]) ? esc_html($head_table[$i]) : ''; ?>
                        </th>
                    <?php endfor; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $body_list as $row ) : 
                    $branch_name = isset( $row['branch_name'] ) ? $row['branch_name'] : '';
                    $is_close = isset( $row['branch_close'] ) && $row['branch_close'];
                    $close_text = isset( $row['branch_close_text'] ) ? $row['branch_close_text'] : '';
                    $contact = isset( $row['branch_contact'] ) ? $row['branch_contact'] : '';
                    $contact_url = isset( $row['branch_contact_url'] ) ? $row['branch_contact_url'] : '';
                ?>
                    <tr class="dt-row <?php echo $is_close ? 'dt-is-closed' : ''; ?>">
                        <td class="dt-col-branch" data-label="<?php echo isset($branch_count) ? esc_html($branch_count) : ''; ?>">
                            <span class="dt-branch-text"><?php echo esc_html( $branch_name ); ?></span>
                        </td>

                        <?php if ( $is_close ) : ?>
                            <td colspan="8" class="dt-col-closed-msg">
                                <?php echo esc_html( $close_text ); ?>
                            </td>
                        <?php else : ?>
                            <?php 
                            // Days 1-7
                            for($d=1; $d<=7; $d++): 
                                $doctor = isset( $row['days'][$d] ) ? $row['days'][$d] : '';
                                $header_label = isset($head_table[$d]) ? $head_table[$d] : "Day $d";
                            ?>
                                <td class="dt-col-day" data-label="<?php echo esc_attr($header_label); ?>"><?php echo esc_html( $doctor ); ?></td>
                            <?php endfor; ?>

                            <!-- Contact (Head Table 8) -->
                            <?php 
                            $header_label_contact = isset($head_table[8]) ? $head_table[8] : "Contact";
                            ?>
                            <td class="dt-col-contact" data-label="<?php echo esc_attr($header_label_contact); ?>">
                                <?php if ( $contact_url ) : ?>
                                    <a href="<?php echo esc_url( $contact_url ); ?>" target="_blank"><?php echo esc_html( $contact ); ?></a>
                                <?php else : ?>
                                    <?php echo esc_html( $contact ); ?>
                                <?php endif; ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="dt-update-date-wrapper">
            <p>Update Date: <?php echo esc_html( $update_date_doctor_table ); ?></p>
        </div>
    </div>
    
    <style>
        .dt-table-wrapper {
            overflow-x: auto;
            margin-bottom: 20px;
        }
        .dt-doctors-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            min-width: 900px;
        }
        .dt-doctors-table th, 
        .dt-doctors-table td {
            border: 1px solid #e5e5e5;
            padding: 12px 8px;
            text-align: center;
            vertical-align: middle;
        }
        .dt-doctors-table th {
            background-color: #f9f9f9;
            font-weight: 600;
            color: #333;
        }
        .dt-doctors-table .dt-col-branch {
            font-weight: bold;
            background-color: #fcfcfc;
            text-align: left;
            min-width: 150px;
        }
        .dt-doctors-table a {
            text-decoration: none;
            color: #0073aa;
        }
        .dt-doctors-table a:hover {
            text-decoration: underline;
        }
        
        /* Closed State */
        .dt-is-closed .dt-col-branch {
            color: #999;
        }
        .dt-is-closed .dt-col-closed-msg {
            text-align: center;
            color: #d63638;
            font-weight: bold;
            background-color: #fff8f8;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'detail_branch', 'detail_branch_shortcode' );

// 5.1 Shortcode [detail_branch_update_date]
function detail_branch_update_date_shortcode() {
    $data = get_option( 'detail_branch_data', array() );
    
    $update_date_doctor_table = isset( $data['update_date_doctor_table'] ) ? $data['update_date_doctor_table'] : '';

    return esc_html( $update_date_doctor_table );
}
add_shortcode( 'detail_branch_update_date', 'detail_branch_update_date_shortcode' );
