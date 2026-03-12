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
        // 'treatment_group_code',
        'treatment_group_name',
        'product_master_name',
        // 'product_master_short_name',
        'quantity',
        'unit_name',
        'normal_price',
        'normal_unit_price',
        // 'sale_type',
        // 'body_position_group_name',
        'body_position_name',
        'treatment_by',
        // 'treatment_group_id',
        // 'body_position_id',
        // 'treatment_id'
    );
}

// Helper: Define the exact schema fields
function course_prices_get_schema_fields() {
    return array(
        'code',
        'course_name',
        'session',
        'normal_price',
        'unit_price',
        'name',
        'treatment_group_name',
        'product_master_name',
        'treatment_by',
    );
}

// Helper: Define the exact schema fields
function package_prices_get_schema_fields() {
    return array(
        'code',
        'package_name',
        'initial_qty',
        'normal_price',
        'package_price',
        'normal_unit_price',
        'unit',
        'treatment_group_name',
        'product_master_name',
        'body_position_name',
        'treatment_by',
    );
}

// Helper: Define the head table fields
function prices_get_head_table() {
    return array(
        'Shortcode',
        // 'treatment_group_code',
        'Product Name',
        'Product',
        // 'product_master_short_name',
        'Quantity',
        'Unit',
        'Price',
        'Unit Price',
        // 'sale_type',
        // 'body_position_group_name',
        'Body Position',
        'By',
        // 'treatment_group_id',
        // 'body_position_id',
        // 'treatment_id'
    );
}

// Helper: Define the head table fields for Course Prices
function course_prices_get_head_table() {
    return array(
        'Code',
        'Course Name',
        'Session',
        'Price',
        'Unit Price',
        'Unit Name',
        'Group',
        'Product Master',
        'By',
    );
}

// Helper: Define the head table fields for Package Prices
function package_prices_get_head_table() {
    return array(
        'Code',
        'Package Name',
        'Quantity',
        'Price',
        'Package Price',
        'Unit Price',
        'Unit',
        'Group',
        'Product Master',
        'Body Position',
        'By',
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
        47
    );
}
add_action( 'admin_menu', 'prices_add_admin_menu' );

// 2. Register Settings
function prices_settings_init() {
    register_setting( 'prices_option_group', 'prices_data' );
}
add_action( 'admin_init', 'prices_settings_init' );

// 3. Enqueue Assets (JS/CSS)
function prices_admin_assets( $hook ) {
    if ( 'toplevel_page_prices-settings' !== $hook ) {
        return;
    }

    wp_enqueue_style( 
        'prices-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-prices.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-prices.css' ) 
    );

    wp_enqueue_script( 
        'prices-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-prices.js', 
        array( 'jquery', 'jquery-ui-sortable' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-prices.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'prices_admin_assets' );

/**
 * Handle CSV Import/Export Actions
 */
function prices_handle_csv_actions() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Determine Type (single vs course vs package)
    $price_type = isset($_POST['price_type']) ? sanitize_text_field($_POST['price_type']) : 'single';
    
    // Select Schema based on type
    if ($price_type === 'course') {
        $fields = course_prices_get_schema_fields();
        $data_key = 'course_items';
    } elseif ($price_type === 'package') {
        $fields = package_prices_get_schema_fields();
        $data_key = 'package_items';
    } else {
        $fields = prices_get_schema_fields();
        $data_key = 'items';
    }

    // --- Export Action ---
    if ( isset( $_POST['action'] ) && 'export_prices_csv' === $_POST['action'] ) {
        check_admin_referer( 'prices_export_csv', 'prices_export_nonce' );
        
        $data = get_option( 'prices_data', array() );
        $items = isset($data[$data_key]) && is_array($data[$data_key]) ? $data[$data_key] : array();
        
        if ( ob_get_level() ) {
            ob_end_clean();
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=prices-' . $price_type . '-export-' . date('Y-m-d') . '.csv');
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

                        // Only add if we have some data, checking 'code'
                        if ( $has_data && ( !empty($item['code']) ) ) {
                            $new_items[] = $item;
                        }
                    }
                }
                
                fclose($handle);
                
                if ( !empty($new_items) ) {
                    // Get current data to preserve other keys
                    $current_data = get_option( 'prices_data', array() );
                    $current_data[$data_key] = $new_items;
                    
                    update_option( 'prices_data', $current_data );
                    set_transient('prices_import_message', 'Imported ' . count($new_items) . ' items successfully into ' . ucfirst($price_type) . '.', 30);
                } else {
                    set_transient('prices_import_error', 'Import failed: No valid items found. Please check CSV headers and data.', 30);
                }
            } else {
                set_transient('prices_import_error', 'Import failed: Unable to open CSV file.', 30);
            }
        } else {
            set_transient('prices_import_error', 'Import failed: No file selected.', 30);
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

    $active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'single';

    if ($active_tab === 'course') {
        $data_key = 'course_items';
    } elseif ($active_tab === 'package') {
        $data_key = 'package_items';
    } else {
        $data_key = 'items';
    }

    $data = get_option( 'prices_data', array() );
    $all_items = isset($data[$data_key]) && is_array($data[$data_key]) ? $data[$data_key] : array();
    
    // Select Schema and Head Table based on active tab
    if ($active_tab === 'course') {
        $fields = course_prices_get_schema_fields();
        $head_table = course_prices_get_head_table();
    } elseif ($active_tab === 'package') {
        $fields = package_prices_get_schema_fields();
        $head_table = package_prices_get_head_table();
    } else {
        $fields = prices_get_schema_fields();
        $head_table = prices_get_head_table();
    }

    // Filter Logic
    $filter_options = array();
    if ($active_tab === 'course') {
        $filter_key = 'product_master_name';
    } elseif ($active_tab === 'package') {
        $filter_key = 'treatment_group_name';
    } else {
        $filter_key = 'body_position_name';
    }

    foreach ($all_items as $itm) {
        if (!empty($itm[$filter_key])) {
            $filter_options[] = $itm[$filter_key];
        }
    }
    $filter_options = array_unique($filter_options);
    sort($filter_options);

    $search_query = isset($_GET['s']) ? trim(sanitize_text_field($_GET['s'])) : '';
    $filter_val   = isset($_GET['filter_val']) ? trim(sanitize_text_field($_GET['filter_val'])) : '';

    if ( $search_query || $filter_val ) {
        $all_items = array_filter($all_items, function($item) use ($search_query, $filter_val, $filter_key) {
            // 1. Filter by Key (Body Position or Product Master)
            if ( $filter_val && ( !isset($item[$filter_key]) || $item[$filter_key] !== $filter_val ) ) {
                return false;
            }

            // 2. Search Text
            if ( $search_query ) {
                $found = false;
                foreach ($item as $val) {
                    if ( is_string($val) && stripos($val, $search_query) !== false ) {
                        $found = true;
                        break;
                    }
                }
                if ( ! $found ) return false;
            }
            return true;
        });
    }

    // Sorting Logic
    $sortable_fields = array('normal_price', 'normal_unit_price', 'unit_price', 'package_price');
    $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
    $order   = isset($_GET['order']) ? sanitize_text_field($_GET['order']) : 'asc';
    if ( ! in_array($order, array('asc', 'desc')) ) {
        $order = 'asc';
    }

    if ( $orderby && in_array($orderby, $sortable_fields) && in_array($orderby, $fields) ) {
        usort($all_items, function($a, $b) use ($orderby, $order) {
            $val_a = isset($a[$orderby]) ? floatval(str_replace(',', '', $a[$orderby])) : 0;
            $val_b = isset($b[$orderby]) ? floatval(str_replace(',', '', $b[$orderby])) : 0;

            if ($order === 'desc') {
                return $val_b <=> $val_a;
            }
            return $val_a <=> $val_b;
        });
    }

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

    if ( $err = get_transient('prices_import_error') ) {
        add_settings_error( 'prices_data', 'prices_import_error', $err, 'error' );
        delete_transient('prices_import_error');
    }

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Prices Settings</h1>
        <hr class="wp-header-end">

        <?php settings_errors( 'prices_data' ); ?>

        <!-- TABS -->
        <nav class="nav-tab-wrapper">
            <a href="?page=prices-settings&tab=single" class="nav-tab <?php echo $active_tab == 'single' ? 'nav-tab-active' : ''; ?>">Single Prices</a>
            <a href="?page=prices-settings&tab=course" class="nav-tab <?php echo $active_tab == 'course' ? 'nav-tab-active' : ''; ?>">Course Prices</a>
            <a href="?page=prices-settings&tab=package" class="nav-tab <?php echo $active_tab == 'package' ? 'nav-tab-active' : ''; ?>">Package Prices</a>
        </nav>
        
        <?php 
        // Check if user is a sender
        $is_sender = false;
        if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
            $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
            $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
        } 
        ?>
        <div class="card" style="margin-top: 20px; margin-bottom: 20px; padding: 15px 15px 25px; max-width: 100%;">
            <h2 style="margin-top:0;">Manage <?php echo ($active_tab === 'course') ? 'Course' : (($active_tab === 'package') ? 'Package' : 'Single'); ?> Prices via CSV</h2>
            <?php if ( $is_sender ) { ?>
            <p><strong>Required CSV Headers:</strong> <code><?php echo implode(', ', $fields); ?></code></p>
            <?php } ?>
            <p><strong>Shortcode Usage:</strong> <code>[price code="XXXXX"]</code> แสดงราคา (คอลัมน์ Price) สำหรับรหัสรายการนั้นๆ</p>
            
            <?php if ( $is_sender ) { ?>
            <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">
                <!-- Export -->
                <div style="flex: 1; min-width: 250px; border-right: 1px solid #eee; padding-right: 20px;">
                    <h3>Step 1: Download Data</h3>
                    <form method="post" action="">
                        <?php wp_nonce_field( 'prices_export_csv', 'prices_export_nonce' ); ?>
                        <input type="hidden" name="action" value="export_prices_csv">
                        <input type="hidden" name="price_type" value="<?php echo esc_attr($active_tab); ?>">
                        <button type="submit" class="button"><span class="dashicons dashicons-download" style="margin-top:3px;"></span> Download CSV</button>
                    </form>
                </div>
                <!-- Import -->
                <div style="flex: 2; min-width: 300px;">
                    <h3>Step 2: Upload Changes</h3>
                    <form method="post" action="" enctype="multipart/form-data">
                        <?php wp_nonce_field( 'prices_import_csv', 'prices_import_nonce' ); ?>
                        <input type="hidden" name="action" value="import_prices_csv">
                        <input type="hidden" name="price_type" value="<?php echo esc_attr($active_tab); ?>">
                        <input type="file" name="prices_csv_file" required accept=".csv,.xlsx" style="margin-bottom: 10px;">
                        <br>
                        <button type="submit" class="button button-primary btn-upload" onclick="return confirm('Are you sure? This will overwrite existing prices.');">
                            <span class="dashicons dashicons-upload" style="margin-top:3px;"></span> Upload & Import
                        </button>
                    </form>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Data Table -->
        <div style="margin-top: 20px; padding: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center; padding: 15px 0 0;">
                <h2 style="margin: 0;">Current Data Preview</h2>
                
                <!-- Search & Filter Form -->
                <form method="get" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="page" value="prices-settings" />
                    <input type="hidden" name="tab" value="<?php echo esc_attr($active_tab); ?>" />
                    
                    <select name="filter_val">
                        <option value="">-- All <?php echo ($active_tab === 'course') ? 'Products & Positions' : (($active_tab === 'package') ? 'Groups' : 'Positions'); ?> --</option>
                        <?php foreach ($filter_options as $opt): ?>
                            <option value="<?php echo esc_attr($opt); ?>" <?php selected($filter_val, $opt); ?>>
                                <?php echo esc_html($opt); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <input type="search" name="s" value="<?php echo esc_attr($search_query); ?>" placeholder="Search..." />
                    
                    <button type="submit" class="button">Filter</button>
                    
                    <?php if ($search_query || $filter_val): ?>
                        <a href="<?php echo admin_url('admin.php?page=prices-settings&tab=' . $active_tab); ?>" class="button">Reset</a>
                    <?php endif; ?>
                </form>
            </div>

            <div>
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
                                    'prev_text' => '',
                                    'next_text' => '',
                                    'total' => $total_pages,
                                    'current' => $current_page
                                ));
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="wp-list-table-wrapper">
                        <div class="wp-list-table <?php echo ($active_tab === 'course') ? 'wp-list-table-course' : (($active_tab === 'package') ? 'wp-list-table-package' : 'wp-list-table-single'); ?>">
                            <div class="wp-list-table-head">
                                <?php foreach ($head_table as $idx => $head): 
                                    $field_key = isset($fields[$idx]) ? $fields[$idx] : '';
                                    $is_sortable = in_array($field_key, $sortable_fields);
                                ?>
                                    <?php if ($is_sortable): 
                                        $is_active = ($orderby === $field_key);
                                        $next_order = ($is_active && $order === 'asc') ? 'desc' : 'asc';
                                        $sort_url = add_query_arg(array('orderby' => $field_key, 'order' => $next_order, 'paged' => 1));
                                        $sort_class = $is_active ? 'sorted ' . esc_attr($order) : 'sortable';
                                    ?>
                                        <div class="wp-list-table-head-item <?php echo $sort_class; ?>">
                                            <a href="<?php echo esc_url($sort_url); ?>"><?php echo esc_html($head); ?><span class="sort-indicator"></span></a>
                                        </div>
                                    <?php else: ?>
                                        <div class="wp-list-table-head-item"><?php echo esc_html($head); ?></div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            <div class="wp-list-table-body">
                                <?php foreach ($items as $item): ?>
                                    <div class="wp-list-table-body-item">
                                        <?php foreach ($fields as $field): 
                                            $val = isset($item[$field]) ? $item[$field] : '';
                                        ?>
                                            <?php if ($field === 'code'): ?>
                                                <div class="wp-list-table-body-item-cell"><code><?php echo esc_html($val); ?></code></div>
                                            <?php elseif ($field === 'product_master_name' || $field === 'course_name' || $field === 'package_name'): ?>
                                                <div class="wp-list-table-body-item-cell product-name"><?php echo esc_html($val); ?></div>
                                            <?php elseif ($field === 'quantity' || $field === 'session' || $field === 'initial_qty'): ?>
                                                <div class="wp-list-table-body-item-cell quantity"><?php echo esc_html($val); ?></div>
                                            <?php elseif ($field === 'normal_price' || $field === 'package_price'): ?>
                                                <div class="wp-list-table-body-item-cell price"><?php echo esc_html($val); ?></div>
                                            <?php else: ?>
                                                <div class="wp-list-table-body-item-cell"><?php echo esc_html($val); ?></div>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="tablenav bottom">
                        <div class="tablenav-pages">
                            <span class="pagination-links">
                                <?php
                                echo paginate_links( array(
                                    'base' => add_query_arg( 'paged', '%#%' ),
                                    'format' => '',
                                    'prev_text' => '',
                                    'next_text' => '',
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
 * Usage: [price code="XXXXX"]
 */
function prices_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'code' => '',
    ), $atts, 'price' );

    if ( empty( $atts['code'] ) ) {
        return '';
    }

    $data = get_option( 'prices_data', array() );
    
    $search_pools = array();
    // Search Single Prices first
    $search_pools[] = isset($data['items']) ? $data['items'] : array();
    // Then Search Course Prices
    $search_pools[] = isset($data['course_items']) ? $data['course_items'] : array();
    // Then Search Package Prices
    $search_pools[] = isset($data['package_items']) ? $data['package_items'] : array();
    
    // Find item
    $found_item = null;
    foreach ($search_pools as $items) {
        if (!is_array($items)) continue;
        
        foreach ( $items as $item ) {
            // Check Code against 'code'
            $matches_code = isset($item['code']) && strcasecmp($item['code'], $atts['code']) === 0;

            if ( !$matches_code ) {
                continue;
            }

            $found_item = $item;
            break 2;
        }
    }

    if ( ! $found_item ) {
        return '';
    }

    // Mapping for display
    $price = isset($found_item['normal_price']) ? $found_item['normal_price'] : '';

    return esc_html($price);
}
add_shortcode( 'price', 'prices_shortcode' );
