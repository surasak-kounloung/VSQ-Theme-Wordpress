<?php
/**
 * Prices Options Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Helper: Define the exact schema fields
function prices_get_schema_fields() {
    return array(
        'code',
        'treatment_group_code',
        'treatment_group_name',
        'product_master_name',
        'product_master_short_name',
        'unit_name',
        'quantity',
        'normal_price',
        'normal_unit_price',
        'sale_type',
        'body_position_group_name',
        'body_position_name',
        'treatment_by',
        'treatment_group_id',
        'body_position_id',
        'treatment_id'
    );
}

// 1. Add Admin Menu
function prices_add_admin_menu() {
    add_menu_page(
        'Prices Settings',
        'Prices',
        'manage_options',
        'prices-settings',
        'prices_options_page_html',
        'dashicons-money-alt',
        46
    );
}
add_action( 'admin_menu', 'prices_add_admin_menu' );

// 2. Register Settings
function prices_settings_init() {
    register_setting( 'prices_option_group', 'prices_data' );
}
add_action( 'admin_init', 'prices_settings_init' );

/**
 * Handle CSV Import/Export Actions
 */
function prices_handle_csv_actions() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $fields = prices_get_schema_fields();

    // --- Export Action ---
    if ( isset( $_POST['action'] ) && 'export_prices_csv' === $_POST['action'] ) {
        check_admin_referer( 'prices_export_csv', 'prices_export_nonce' );
        
        $data = get_option( 'prices_data', array() );
        $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
        
        if ( ob_get_level() ) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=prices-export-' . date('Y-m-d') . '.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $output = fopen('php://output', 'w');
        fputs($output, "\xEF\xBB\xBF"); // BOM
        
        // Header Row
        fputcsv($output, $fields);
        
        // Data Rows
        foreach ($items as $item) {
            $row = array();
            foreach ($fields as $field) {
                $row[] = isset($item[$field]) ? $item[$field] : '';
            }
            fputcsv($output, $row);
        }
        
        fclose($output);
        exit;
    }

    // --- Import Action ---
    if ( isset( $_POST['action'] ) && 'import_prices_csv' === $_POST['action'] ) {
        check_admin_referer( 'prices_import_csv', 'prices_import_nonce' );
        
        if ( ! empty( $_FILES['prices_csv_file']['tmp_name'] ) ) {
            $csv_file = $_FILES['prices_csv_file']['tmp_name'];
            $handle = fopen($csv_file, 'r');
            
            if ( $handle !== FALSE ) {
                $new_items = array();
                
                // Read header row
                $header = fgetcsv($handle); 
                
                if ($header) {
                    // Normalize header keys: trim, lowercase, remove BOM
                    if (isset($header[0])) {
                        $header[0] = preg_replace('/^\xEF\xBB\xBF/', '', $header[0]);
                    }
                    
                    $csv_headers = array_map(function($h) {
                        return trim(strtolower($h)); 
                    }, $header);

                    // Create a map of Field Name -> CSV Index
                    $index_map = array();
                    foreach ($fields as $field) {
                        $search_key = strtolower($field);
                        $found_index = array_search($search_key, $csv_headers);
                        if ($found_index !== false) {
                            $index_map[$field] = $found_index;
                        }
                    }

                    while ( ($row = fgetcsv($handle, 0, ",")) !== FALSE ) {
                        // Skip empty rows
                        if (count($row) < 1 || (count($row) === 1 && empty($row[0]))) continue;

                        $item = array();
                        $has_data = false;
                        
                        foreach ($fields as $field) {
                            if (isset($index_map[$field]) && isset($row[$index_map[$field]])) {
                                $val = trim($row[$index_map[$field]]);
                                $item[$field] = $val;
                                if (!empty($val)) $has_data = true;
                            } else {
                                $item[$field] = '';
                            }
                        }

                        // Only add if we have some data, preferably a 'code'
                        if ( $has_data && !empty($item['code']) ) {
                            $new_items[] = $item;
                        }
                    }
                }
                
                fclose($handle);
                
                if ( !empty($new_items) ) {
                    update_option( 'prices_data', array('items' => $new_items) );
                    set_transient('prices_import_message', 'Imported ' . count($new_items) . ' items successfully.', 30);
                }
            }
        }
        
        wp_redirect( remove_query_arg(array('settings-updated'), wp_get_referer()) );
        exit;
    }
}
add_action( 'admin_init', 'prices_handle_csv_actions' );

// 4. Render Options Page
function prices_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'prices_data', array() );
    $all_items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
    $fields = prices_get_schema_fields();

    // Pagination Logic
    $per_page = 50;
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $total_items = count($all_items);
    $total_pages = ceil($total_items / $per_page);
    $offset = ($current_page - 1) * $per_page;
    $items = array_slice($all_items, $offset, $per_page);

    if ( $msg = get_transient('prices_import_message') ) {
        add_settings_error( 'prices_data', 'prices_imported', $msg, 'updated' );
        delete_transient('prices_import_message');
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Prices Settings</h1>
        <hr class="wp-header-end">

        <?php settings_errors( 'prices_data' ); ?>
        
        <div class="card" style="margin-top: 20px; margin-bottom: 20px; padding: 15px; max-width: 100%;">
            <h2 style="margin-top:0;">Manage Prices via CSV</h2>
            <p><strong>Required CSV Headers:</strong> <code><?php echo implode(', ', $fields); ?></code></p>
            
            <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                <!-- Export -->
                <div style="flex: 1; min-width: 250px; border-right: 1px solid #eee; padding-right: 20px;">
                    <h3>Step 1: Download Data</h3>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'prices_export_csv', 'prices_export_nonce' ); ?>
                        <input type="hidden" name="action" value="export_prices_csv">
                        <button type="submit" class="button"><span class="dashicons dashicons-download" style="margin-top:3px;"></span> Download CSV</button>
                    </form>
                </div>
                <!-- Import -->
                <div style="flex: 2; min-width: 300px;">
                    <h3>Step 2: Upload Changes</h3>
                    <form method="post" action="" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'prices_import_csv', 'prices_import_nonce' ); ?>
                        <input type="hidden" name="action" value="import_prices_csv">
                        <input type="file" name="prices_csv_file" required accept=".csv,.xlsx" style="margin-bottom: 10px;">
                        <br>
                        <button type="submit" class="button button-primary" onclick="return confirm('Are you sure? This will overwrite existing prices.');">
                            <span class="dashicons dashicons-upload" style="margin-top:3px;"></span> Upload & Import
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Data Table -->
        <div style="margin-top: 20px; padding: 0;">
            <h2 style="padding: 15px 15px 0; margin: 0;">Current Data Preview</h2>
            <div style="padding: 15px;">
                <?php if ( empty( $all_items ) ) : ?>
                    <p>No prices found. Please import a CSV file.</p>
                <?php else : ?>
                    <div class="tablenav top">
                        <div class="alignleft actions bulkactions">
                            <span class="displaying-num"><?php echo number_format($total_items); ?> items</span>
                        </div>
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php
                                echo paginate_links( array(
                                    'base' => add_query_arg( 'paged', '%#%' ),
                                    'format' => '',
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                    'total' => $total_pages,
                                    'current' => $current_page
                                ));
                                ?>
                            </span>
                        </div>
                        <br class="clear">
                    </div>

                    <div style="overflow-x: auto;">
                        <table class="wp-list-table widefat fixed striped table-view-list">
                            <thead>
                                <tr>
                                    <?php foreach ($fields as $field): ?>
                                        <th><?php echo esc_html($field); ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ( $items as $item ) : ?>
                                    <tr>
                                        <?php foreach ($fields as $field): 
                                            $val = isset($item[$field]) ? $item[$field] : '-';
                                        ?>
                                            <td>
                                                <?php if ($field === 'code'): ?>
                                                    <code><?php echo esc_html($val); ?></code>
                                                <?php elseif ($field === 'product_master_name'): ?>
                                                    <strong><?php echo esc_html($val); ?></strong>
                                                <?php else: ?>
                                                    <?php echo esc_html($val); ?>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php
                                echo paginate_links( array(
                                    'base' => add_query_arg( 'paged', '%#%' ),
                                    'format' => '',
                                    'prev_text' => '&laquo;',
                                    'next_text' => '&raquo;',
                                    'total' => $total_pages,
                                    'current' => $current_page
                                ));
                                ?>
                            </span>
                        </div>
                    </div>

                <?php endif; ?>
            </div>
        </div>

    </div>
    <?php
}

/**
 * 5. Shortcode Implementation
 * Usage: [price name="XXXXX"] (where name matches 'code' column)
 */
function prices_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'name' => '',
    ), $atts, 'price' );

    if ( empty( $atts['name'] ) ) {
        return '';
    }

    $data = get_option( 'prices_data', array() );
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
    
    // Find item by 'code'
    $found_item = null;
    foreach ( $items as $item ) {
        if ( isset($item['code']) && strcasecmp($item['code'], $atts['name']) === 0 ) {
            $found_item = $item;
            break;
        }
    }

    if ( ! $found_item ) {
        return '';
    }

    // Mapping for display
    $product_name = isset($found_item['product_master_name']) ? $found_item['product_master_name'] : '';
    $price = isset($found_item['normal_price']) ? $found_item['normal_price'] : '';

    return '<div class="price-item">
        <div class="price-item-name">' . esc_html($product_name) . '</div>
        <div class="price-item-price">' . esc_html($price) . '</div>
    </div>';
}
add_shortcode( 'price', 'prices_shortcode' );
