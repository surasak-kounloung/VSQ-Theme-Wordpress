<?php
/**
 * Doctor Table Options Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Add Admin Menu
function dt_add_admin_menu() {
    add_menu_page(
        'Doctor Table Settings',
        'Doctor Table',
        'manage_options',
        'doctor-table-settings',
        'dt_options_page_html',
        'dashicons-editor-table',
        41
    );
}
add_action( 'admin_menu', 'dt_add_admin_menu' );

// 2. Register Settings
function dt_settings_init() {
    register_setting( 'doctors_table_option_group', 'doctors_table_data' );
}
add_action( 'admin_init', 'dt_settings_init' );

// 3. Enqueue Assets
function dt_admin_assets( $hook ) {
    if ( 'toplevel_page_doctor-table-settings' !== $hook ) {
        return;
    }

    wp_enqueue_style( 
        'dt-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-doctors-table.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-doctors-table.css' ) 
    );

    wp_enqueue_script( 
        'dt-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-doctors-table.js', 
        array( 'jquery', 'jquery-ui-sortable' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-doctors-table.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'dt_admin_assets' );

// 4. Render Options Page
function dt_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'doctors_table_data', array() );
    
    // Defaults
    $branch_count = isset($data['branch_count']) ? $data['branch_count'] : '';
    $head_tables = isset($data['head_table']) ? $data['head_table'] : array();
    $body_list = isset($data['body_list']) && is_array($data['body_list']) ? $data['body_list'] : array();
    $update_date_doctor_table = isset($data['update_date_doctor_table']) ? $data['update_date_doctor_table'] : '';

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Doctor Table Settings</h1>
        <hr class="wp-header-end">

        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'doctors_table_data', 'doctors_table_settings_updated', 'Doctor Table Updated.', 'updated' );
        }
        settings_errors( 'doctors_table_data' );
        ?>
        
        <form action="options.php" method="post">
            <?php
            settings_fields( 'doctors_table_option_group' );
            do_settings_sections( 'doctor-table-settings' );
            ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    
                    <!-- Right Sidebar (Publish Box) -->
                    <div id="postbox-container-1" class="postbox-container">
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
                    </div>

                    <!-- Main Content (Left Column) -->
                    <div id="postbox-container-2" class="postbox-container">
                        <div id="normal-sortables" class="meta-box-sortables">
                            
                            <!-- General Settings -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">General Settings</h2>
                                </div>
                                <div class="inside">
                                    <div class="dt-field-row">
                                        <div class="dt-field-col" style="width: 100%;">
                                            <label>Branch count</label>
                                            <input type="text" name="doctors_table_data[branch_count]" value="<?php echo esc_attr($branch_count); ?>">
                                        </div>
                                    </div>
                                    <div class="dt-field-row">
                                        <?php for($i=1; $i<=8; $i++): 
                                            $val = isset($head_tables[$i]) ? $head_tables[$i] : '';
                                        ?>
                                            <div class="dt-field-col col-1-8">
                                                <label>Head Table <?php echo $i; ?></label>
                                                <input type="text" name="doctors_table_data[head_table][<?php echo $i; ?>]" value="<?php echo esc_attr($val); ?>">
                                            </div>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                            </div>

                            <!-- Body Table List -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">Body Table List</h2>
                                </div>
                                <div class="inside">
                                    
                                    <div class="dt-repeater-container">
                                        <?php 
                                        if ( ! empty( $body_list ) ) :
                                            foreach ( $body_list as $index => $row ) :
                                                dt_render_row( $index, $row );
                                            endforeach;
                                        endif; 
                                        ?>
                                    </div>

                                    <div class="dt-actions" style="margin-top: 20px;">
                                        <button class="button button-primary dt-repeater-add">Add Row</button>
                                    </div>

                                </div>
                            </div>

                            <!-- Update Date Settings -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">Update Date Settings</h2>
                                </div>
                                <div class="inside">
                                    <div class="dt-field-row">
                                        <div class="dt-field-col" style="width: 100%;">
                                            <label>Update Date Table</label>
                                            <input type="text" name="doctors_table_data[update_date_doctor_table]" value="<?php echo esc_attr($update_date_doctor_table); ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div><!-- #post-body -->
                <br class="clear">
            </div><!-- #poststuff -->

        </form>

        <!-- Hidden Template for New Row -->
        <script type="text/template" id="dt-repeater-template">
            <?php dt_render_row( '{{index}}', array() ); ?>
        </script>
    </div>
    <?php
}

// Helper to render a single row
function dt_render_row( $index, $data ) {
    // Extract values
    $branch_name = isset( $data['branch_name'] ) ? $data['branch_name'] : '';
    $branch_close = isset( $data['branch_close'] ) ? $data['branch_close'] : '';
    $branch_close_text = isset( $data['branch_close_text'] ) ? $data['branch_close_text'] : '';
    $branch_contact = isset( $data['branch_contact'] ) ? $data['branch_contact'] : '';
    $branch_contact_url = isset( $data['branch_contact_url'] ) ? $data['branch_contact_url'] : '';
    
    $days = isset( $data['days'] ) ? $data['days'] : array();
    
    ?>
    <div class="dt-repeater-row">
        <div class="dt-row-header">
            <span class="dt-row-handle dashicons dashicons-menu"></span>
            <span class="dt-row-title">Branch Item</span>
            <div class="dt-row-actions">
                 <span class="dt-toggle-row dashicons dashicons-minus"></span>
                 <span class="dt-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
            </div>
        </div>
        <div class="dt-row-content">
            <!-- Row 1: Name, Close, Close Text -->
            <div class="dt-field-row">
                <div class="dt-field-col col-1-3">
                    <label>Branch name</label>
                    <input type="text" name="doctors_table_data[body_list][<?php echo $index; ?>][branch_name]" value="<?php echo esc_attr( $branch_name ); ?>">
                </div>
                <div class="dt-field-col col-1-3">
                    <label>Branch Close</label>
                    <div class="dt-field-checkbox">
                        <input type="checkbox" name="doctors_table_data[body_list][<?php echo $index; ?>][branch_close]" value="1" <?php checked( $branch_close, '1' ); ?>> Close
                    </div>
                </div>
                <div class="dt-field-col col-1-3">
                    <label>Branch Close Text</label>
                    <input type="text" name="doctors_table_data[body_list][<?php echo $index; ?>][branch_close_text]" value="<?php echo esc_attr( $branch_close_text ); ?>">
                </div>
            </div>

            <!-- Row 2: Days -->
            <div class="dt-field-row">
                <?php for($d=1; $d<=7; $d++): 
                    $val = isset($days[$d]) ? $days[$d] : '';
                ?>
                    <div class="dt-field-col col-1-7">
                        <label>Day <?php echo $d; ?></label>
                        <input type="text" name="doctors_table_data[body_list][<?php echo $index; ?>][days][<?php echo $d; ?>]" value="<?php echo esc_attr( $val ); ?>">
                    </div>
                <?php endfor; ?>
            </div>

            <!-- Row 3: Contact -->
            <div class="dt-field-row">
                <div class="dt-field-col col-1-3">
                    <label>Branch Contact</label>
                    <input type="text" name="doctors_table_data[body_list][<?php echo $index; ?>][branch_contact]" value="<?php echo esc_attr( $branch_contact ); ?>">
                </div>
                <div class="dt-field-col col-3-4" style="width: 66.66%;">
                    <label>Branch Contact URL</label>
                    <input type="text" name="doctors_table_data[body_list][<?php echo $index; ?>][branch_contact_url]" value="<?php echo esc_attr( $branch_contact_url ); ?>">
                </div>
            </div>

        </div>
    </div>
    <?php
}

// 5. Shortcode [doctors_table]
function dt_doctors_table_shortcode() {
    $data = get_option( 'doctors_table_data', array() );
    
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
add_shortcode( 'doctors_table', 'dt_doctors_table_shortcode' );

// 5.1 Shortcode [doctors_table_update_date]
function dt_doctors_table_update_date_shortcode() {
    $data = get_option( 'doctors_table_data', array() );
    
    $update_date_doctor_table = isset( $data['update_date_doctor_table'] ) ? $data['update_date_doctor_table'] : '';

    return esc_html( $update_date_doctor_table );
}
add_shortcode( 'doctors_table_update_date', 'dt_doctors_table_update_date_shortcode' );
