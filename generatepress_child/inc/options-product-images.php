<?php
/**
 * Product Images Options Page
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 0. Define Categories (Mockup)
function product_image_get_categories() {
    return array(
        'filler' => 'Filler',
        'botox' => 'Botox',
        'hifu' => 'HIFU',
        'ultraformer' => 'Ultraformer',
        'ulthera' => 'Ulthera',
        'thermage' => 'Thermage',
        'meso' => 'Meso',
        'meso_fat' => 'Meso Fat',
        'coolsculpting' => 'Coolsculpting',
        'facelift' => 'ร้อยไหม',
        'skin_booster' => 'Skin Booster',
        'vitamin' => 'Vitamin',
        'laser' => 'Laser',
        'wellness' => 'Wellness',
        'doctor' => 'Doctor',
        'review' => 'Review',
        'influencer_celebrity' => 'Influencer & Celebrity',
        'location' => 'Location',
        'promotion' => 'Promotion',
        'price' => 'Price',
        'banner' => 'Banner',
        'button' => 'Button',
    );
}

// 1. Add Admin Menu
function product_image_add_admin_menu() {
    add_menu_page(
        'Product Images Settings',
        'Product Images',
        'manage_options',
        'product-images-settings',
        'product_image_options_page_html',
        'dashicons-format-image',
        48
    );
}
add_action( 'admin_menu', 'product_image_add_admin_menu' );

// 2. Register Settings
function product_image_settings_init() {
    register_setting( 'product_images_option_group', 'product_images_data', 'product_image_validate_data' );
}
add_action( 'admin_init', 'product_image_settings_init' );

// 2.1 Server-side Validation Callback
function product_image_validate_data( $input ) {
    $items = isset($input['items']) ? $input['items'] : array();
    $seen_shortcodes = array();
    $has_error = false;
    
    // Check duplicates
    if ( ! empty( $items ) ) {
        foreach ( $items as $index => $item ) {
            $shortcode = isset($item['shortcode_name']) ? trim($item['shortcode_name']) : '';
            if ( ! empty($shortcode) ) {
                if ( isset($seen_shortcodes[$shortcode]) ) {
                    $has_error = true;
                    // Duplicate found
                }
                $seen_shortcodes[$shortcode] = true;
            }
        }
    }

    if ( $has_error ) {
        add_settings_error(
            'product_images_data',
            'product_image_duplicate_error',
            'Error: Duplicate Shortcode Names found. Settings NOT saved. Please check your entries.',
            'error'
        );
        
        // Return the OLD option to prevent saving invalid data
        // This ensures that we don't save broken or duplicate data
        return get_option( 'product_images_data' );
    }

    // Auto-generate sequential item_id for new items (empty item_id)
    if ( ! empty( $items ) ) {
        // Collect existing item_ids and find the max
        $existing_ids = array();
        $max_id = 0;
        foreach ( $items as $item ) {
            if ( ! empty( $item['item_id'] ) ) {
                $id = intval( $item['item_id'] );
                $existing_ids[ $id ] = true;
                if ( $id > $max_id ) {
                    $max_id = $id;
                }
            }
        }

        // Also consider max_id from previously saved data (safety net)
        $previous = get_option( 'product_images_data', array() );
        if ( ! empty( $previous['items'] ) && is_array( $previous['items'] ) ) {
            foreach ( $previous['items'] as $prev_item ) {
                if ( ! empty( $prev_item['item_id'] ) ) {
                    $prev_id = intval( $prev_item['item_id'] );
                    if ( $prev_id > $max_id ) {
                        $max_id = $prev_id;
                    }
                }
            }
        }

        // Assign next sequential id to items missing an item_id
        $next_id = $max_id + 1;
        foreach ( $items as $index => $item ) {
            if ( empty( $item['item_id'] ) ) {
                // Skip any id that's already taken (extra safety)
                while ( isset( $existing_ids[ $next_id ] ) ) {
                    $next_id++;
                }
                $items[ $index ]['item_id'] = (string) $next_id;
                $existing_ids[ $next_id ] = true;
                $next_id++;
            }
        }

        $input['items'] = $items;
    }

    return $input;
}

// 3. Enqueue Assets
function product_image_admin_assets( $hook ) {
    if ( 'toplevel_page_product-images-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media(); // Required for Media Uploader

    // Select2 (CDN)
    wp_enqueue_style( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css' );
    wp_enqueue_script( 'select2', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', array('jquery'), '4.1.0', true );

    wp_enqueue_style( 
        'product-images-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-product-images.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-product-images.css' ) 
    );

    wp_enqueue_script( 
        'product-images-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-product-images.js', 
        array( 'jquery', 'select2' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-product-images.js' ), 
        true 
    );

    wp_localize_script( 'product-images-admin-js', 'productImageAdmin', array(
        'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
        'findUsagesNonce' => wp_create_nonce( 'product_image_find_usages_action' ),
        'i18n'            => array(
            'loading'     => 'กำลังค้นหา...',
            'noResults'   => 'ไม่พบหน้าที่ใช้ภาพนี้',
            'errorLoad'   => 'เกิดข้อผิดพลาดในการค้นหา',
            'foundText'   => 'พบการใช้งานทั้งหมด',
            'itemsText'   => 'รายการ',
            'viewLabel'   => 'ดูหน้า',
            'editLabel'   => 'แก้ไข',
            'modalTitle'  => 'URL ที่ใช้ภาพนี้',
            'closeLabel'  => 'ปิด',
        ),
    ) );
}
add_action( 'admin_enqueue_scripts', 'product_image_admin_assets' );

/**
 * 3.1 Bulk Count Usages
 * Scan ฐานข้อมูล 1 ครั้งแล้ว map shortcode กลับไปแต่ละ item
 * คืนค่า: array( item_index => usage_count )
 */
function product_image_get_usage_counts( $items ) {
    if ( empty( $items ) || ! is_array( $items ) ) {
        return array();
    }

    global $wpdb;

    // สร้าง lookup map: shortcode_name/item_id → item_index
    $name_to_idx = array();
    $id_to_idx   = array();
    foreach ( $items as $idx => $item ) {
        if ( ! empty( $item['shortcode_name'] ) ) {
            $name_to_idx[ $item['shortcode_name'] ] = $idx;
        }
        if ( ! empty( $item['item_id'] ) ) {
            $id_to_idx[ intval( $item['item_id'] ) ] = $idx;
        }
    }

    if ( empty( $name_to_idx ) && empty( $id_to_idx ) ) {
        return array();
    }

    $like    = '%' . $wpdb->esc_like( '[product_img' ) . '%';
    $statuses = array( 'publish', 'private', 'draft', 'pending', 'future' );
    $status_place = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );

    // ดึง content จาก 3 แหล่งที่มี [product_img อยู่
    $rows_posts = $wpdb->get_results( $wpdb->prepare(
        "SELECT ID as key_id, post_content as content
           FROM {$wpdb->posts}
          WHERE post_status IN ($status_place)
            AND post_type NOT IN ('revision','attachment','nav_menu_item')
            AND post_content LIKE %s",
        array_merge( $statuses, array( $like ) )
    ) );

    $rows_meta = $wpdb->get_results( $wpdb->prepare(
        "SELECT p.ID as key_id, pm.meta_value as content
           FROM {$wpdb->postmeta} pm
           INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
          WHERE p.post_status IN ($status_place)
            AND p.post_type NOT IN ('revision','attachment','nav_menu_item')
            AND pm.meta_value LIKE %s",
        array_merge( $statuses, array( $like ) )
    ) );

    $rows_opts = $wpdb->get_results( $wpdb->prepare(
        "SELECT CONCAT('opt_', option_id) as key_id, option_value as content
           FROM {$wpdb->options}
          WHERE option_value LIKE %s",
        array( $like )
    ) );

    // Regex รองรับทั้ง name="xxx" และ name=\"xxx\" (JSON escaped ใน Elementor)
    $pattern_name = '/\[product_img[^\]]*?\bname\s*=\s*\\\\?"([^"\\\\]+)\\\\?"/i';
    $pattern_id   = '/\[product_img[^\]]*?\bid\s*=\s*\\\\?"(\d+)\\\\?"/i';

    // item_index → array of unique keys (post_id / opt_id) ที่อ้างถึง
    $item_refs = array();

    $all_rows = array_merge( $rows_posts, $rows_meta, $rows_opts );
    foreach ( $all_rows as $row ) {
        if ( empty( $row->content ) ) {
            continue;
        }

        $matched_indices = array();

        if ( preg_match_all( $pattern_name, $row->content, $m_name ) && ! empty( $m_name[1] ) ) {
            foreach ( $m_name[1] as $n ) {
                if ( isset( $name_to_idx[ $n ] ) ) {
                    $matched_indices[ $name_to_idx[ $n ] ] = true;
                }
            }
        }

        if ( preg_match_all( $pattern_id, $row->content, $m_id ) && ! empty( $m_id[1] ) ) {
            foreach ( $m_id[1] as $i ) {
                $int_id = intval( $i );
                if ( isset( $id_to_idx[ $int_id ] ) ) {
                    $matched_indices[ $id_to_idx[ $int_id ] ] = true;
                }
            }
        }

        foreach ( array_keys( $matched_indices ) as $item_idx ) {
            if ( ! isset( $item_refs[ $item_idx ] ) ) {
                $item_refs[ $item_idx ] = array();
            }
            $item_refs[ $item_idx ][ $row->key_id ] = true;
        }
    }

    $counts = array();
    foreach ( $item_refs as $idx => $keys ) {
        $counts[ $idx ] = count( $keys );
    }

    return $counts;
}

// 4. Render Options Page
function product_image_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'product_images_data', array() );
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
    $categories = product_image_get_categories();
    $usage_counts = product_image_get_usage_counts( $items );

    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Product Images Settings</h1>
        <hr class="wp-header-end">

        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            // Only show updated message if there are no errors
            $errors = get_settings_errors( 'product_images_data' );
            if ( empty( $errors ) ) {
                add_settings_error( 'product_images_data', 'vsq_product_images_settings_updated', 'Settings Updated.', 'updated' );
            }
        }
        settings_errors( 'product_images_data' );
        ?>
        
        <form action="options.php" method="post">
            <?php
            settings_fields( 'product_images_option_group' );
            do_settings_sections( 'product-images-settings' );
            ?>

            <div id="poststuff">
                <?php 
                // Check if user is a sender
                $is_sender = false;
                if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
                    $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
                    $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
                } 
                ?>
                <div id="post-body" class="metabox-holder<?php if ( $is_sender ) { ?> columns-2<?php } ?>">
                    
                    <?php if ( $is_sender ) { ?>
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
                    <?php } ?>

                    <!-- Main Content (Left Column) -->
                    <div id="postbox-container-2" class="postbox-container">
                        <div id="normal-sortables" class="meta-box-sortables">
                            
                            <!-- Image List -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">Product Images List</h2>
                                </div>
                                <div class="inside" style="padding-top: 6px;">
                                    
                                    <!-- Filter & Pagination Toolbar -->
                                    <div class="dt-toolbar tablenav top" style="margin-top: 0;">
                                        <div class="alignleft actions">
                                            <select id="product-image-filter-category">
                                                <option value="">View All Categories</option>
                                                <?php foreach($categories as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <input type="text" id="product-image-search" class="search-field" placeholder="Search by Name...">
                                        </div>
                                        <div class="tablenav-pages">
                                            <span class="displaying-num"><span id="total-items" class="total-items">0</span> items</span>
                                            <span class="pagination-links">
                                                <a href="#" class="first-page button disabled">&laquo;</a>
                                                <a href="#" class="prev-page button disabled">&lsaquo;</a>
                                                <span class="paging-input">
                                                    <span class="current-page">1</span> of <span class="total-pages">1</span>
                                                </span>
                                                <a href="#" class="next-page button disabled">&rsaquo;</a>
                                                <a href="#" class="last-page button disabled">&raquo;</a>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="dt-repeater-container">
                                        <?php 
                                        if ( ! empty( $items ) ) :
                                            foreach ( $items as $index => $row ) :
                                                $item_count = isset( $usage_counts[ $index ] ) ? intval( $usage_counts[ $index ] ) : 0;
                                                product_image_render_row( $index, $row, $item_count );
                                            endforeach;
                                        endif; 
                                        ?>
                                    </div>
                                    
                                    <!-- No Items Found Message -->
                                    <div class="dt-no-items" style="display:none; padding: 50px 20px 30px; text-align: center; color: #666;">
                                        No images found for this category.
                                    </div>

                                    <?php if ( $is_sender ) { ?>
                                    <div class="dt-actions" style="margin-top: 20px;">
                                        <button class="button button-primary dt-repeater-add">Add New Image</button>
                                    </div>
                                    <?php } ?>

                                    <!-- Pagination Toolbar -->
                                    <div class="dt-toolbar tablenav top" style="margin-top: 25px; margin-bottom: 0;">
                                        <div class="alignleft actions"></div>
                                        <div class="tablenav-pages">
                                            <span class="displaying-num"><span id="total-items" class="total-items">0</span> items</span>
                                            <span class="pagination-links">
                                                <a href="#" class="first-page button disabled">&laquo;</a>
                                                <a href="#" class="prev-page button disabled">&lsaquo;</a>
                                                <span class="paging-input">
                                                    <span class="current-page">1</span> of <span class="total-pages">1</span>
                                                </span>
                                                <a href="#" class="next-page button disabled">&rsaquo;</a>
                                                <a href="#" class="last-page button disabled">&raquo;</a>
                                            </span>
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
            <?php product_image_render_row( '{{index}}', array() ); ?>
        </script>

        <!-- Find Usage Modal -->
        <div class="dt-modal" id="dt-find-usage-modal" aria-hidden="true" role="dialog" aria-labelledby="dt-find-usage-modal-title">
            <div class="dt-modal-backdrop" data-modal-close></div>
            <div class="dt-modal-dialog" role="document">
                <div class="dt-modal-header">
                    <h2 class="dt-modal-title" id="dt-find-usage-modal-title">
                        <span class="dashicons dashicons-admin-links"></span>
                        URL ที่ใช้ภาพนี้
                    </h2>
                    <button type="button" class="dt-modal-close" data-modal-close aria-label="Close">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <div class="dt-modal-subtitle">
                    <div class="dt-modal-item-info"></div>
                </div>
                <div class="dt-modal-body">
                    <div class="dt-modal-state dt-modal-state--loading">
                        <div class="dt-spinner"></div>
                        <p>กำลังค้นหา...</p>
                    </div>
                    <div class="dt-modal-state dt-modal-state--empty" hidden>
                        <span class="dashicons dashicons-info-outline"></span>
                        <p>ไม่พบหน้าที่ใช้ภาพนี้</p>
                    </div>
                    <div class="dt-modal-state dt-modal-state--error" hidden>
                        <span class="dashicons dashicons-warning"></span>
                        <p>เกิดข้อผิดพลาดในการค้นหา</p>
                    </div>
                    <div class="dt-modal-state dt-modal-state--results" hidden>
                        <div class="dt-modal-summary"></div>
                        <ul class="dt-usage-list"></ul>
                    </div>
                </div>
                <div class="dt-modal-footer">
                    <button type="button" class="button button-close" data-modal-close>ปิด</button>
                </div>
            </div>
        </div>

    </div>
    <?php
}

// Helper to render a single row
function product_image_render_row( $index, $data, $usage_count = 0 ) {
    // Extract values
    $category = isset( $data['category'] ) ? $data['category'] : array();
    // Ensure category is an array (backward compatibility)
    if ( ! is_array( $category ) ) {
        $category = array_filter( array( $category ) );
    }
    $item_id = isset( $data['item_id'] ) ? $data['item_id'] : '';
    $name = isset( $data['name'] ) ? $data['name'] : '';
    $shortcode_name = isset( $data['shortcode_name'] ) ? $data['shortcode_name'] : '';
    $image_url = isset( $data['image_url'] ) ? $data['image_url'] : '';
    $image_id = isset( $data['image_id'] ) ? $data['image_id'] : '';
    
    $categories = product_image_get_categories();

    // Check if user is a sender
    $is_sender = false;
    if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
        $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
        $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
    } 
    
    ?>
    <div class="dt-repeater-row" data-category="<?php echo esc_attr( implode( ',', $category ) ); ?>">
        <div class="dt-row-header">
            <span class="dt-row-title">Image Item</span>
            <div class="dt-row-actions">
                 <span class="dt-toggle-row dashicons dashicons-minus"></span>
                 <?php if ( $is_sender ) { ?>
                    <span class="dt-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
                 <?php } ?>
            </div>
        </div>
        <div class="dt-row-content">
            <div class="dt-field-row">
                <!-- Image Upload -->
                <div class="dt-field-col">
                    <label>Image</label>
                    <div class="product-image-preview-wrapper">
                        <img class="product-image-preview" src="<?php echo esc_url($image_url); ?>" style="<?php echo empty($image_url) ? 'display:none;' : ''; ?>">
                    </div>
                    <div style="margin-top: 10px; margin-bottom: 5px;">
                        <input type="hidden" class="image-url-field" name="product_images_data[items][<?php echo $index; ?>][image_url]" value="<?php echo esc_attr($image_url); ?>">
                        <input type="hidden" class="image-id-field" name="product_images_data[items][<?php echo $index; ?>][image_id]" value="<?php echo esc_attr($image_id); ?>">
                        <?php if ( $is_sender ) { ?>
                        <button type="button" class="button upload-image-button">Select Image</button>
                        <button type="button" class="button remove-image-button" style="color: #a00;">Remove</button>
                        <?php } ?>
                    </div>
                </div>

                <!-- ID -->
                <div class="dt-field-col">
                    <label>ID</label>
                    <input type="text" name="product_images_data[items][<?php echo $index; ?>][item_id]" value="<?php echo esc_attr( $item_id ); ?>" placeholder="Auto-generated on save" class="hide-click" readonly>
                </div>

                <!-- Name -->
                <div class="dt-field-col">
                    <label>Name</label>
                    <input type="text" name="product_images_data[items][<?php echo $index; ?>][name]" value="<?php echo esc_attr( $name ); ?>" placeholder=""<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>>
                </div>

                <!-- Category -->
                <div class="dt-field-col dt-field-select">
                    <label>Category</label>
                    <select name="product_images_data[items][<?php echo $index; ?>][category][]" class="category-select" multiple="multiple"<?php if ( ! $is_sender ) { ?> disabled<?php } ?>>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php echo in_array( $key, $category ) ? 'selected="selected"' : ''; ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if ( ! $is_sender ) : ?>
                        <?php foreach( $category as $cat_val ): ?>
                            <input type="hidden" name="product_images_data[items][<?php echo $index; ?>][category][]" value="<?php echo esc_attr($cat_val); ?>">
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- Shortcode Name -->
                <div class="dt-field-col">
                    <label>Shortcode Name (Key)</label>
                    <input type="text" name="product_images_data[items][<?php echo $index; ?>][shortcode_name]" value="<?php echo esc_attr( $shortcode_name ); ?>" placeholder="<?php if ( $is_sender ) { ?>e.g. filler_under_eye<?php } ?>"<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>>
                </div>

                <div class="dt-field-col">
                    <?php if ( ! empty( $shortcode_name ) || ! empty( $item_id ) ) :
                        $sc_by_name = ! empty( $shortcode_name ) ? '[product_img name="' . esc_attr( $shortcode_name ) . '" alt="" class="" caption=""]' : '';
                        $sc_by_id   = ! empty( $item_id )      ? '[product_img id="' . esc_attr( $item_id ) . '" alt="" class="" caption=""]' : '';
                    ?>
                    <div class="dt-shortcode-box">
                        <div class="dt-shortcode-box-title">
                            <span class="dashicons dashicons-shortcode"></span>
                            <span>Shortcode</span>
                        </div>
                        <?php if ( ! empty( $sc_by_name ) ) : ?>
                        <div class="dt-shortcode-item">
                            <span class="dt-shortcode-badge dt-shortcode-badge--name">NAME</span>
                            <code class="dt-shortcode-code"><?php echo esc_html( $sc_by_name ); ?></code>
                            <button type="button" class="dt-shortcode-copy" data-clipboard-text="<?php echo esc_attr( $sc_by_name ); ?>" title="Copy to clipboard">
                                <span class="dashicons dashicons-admin-page"></span>
                                <span class="dt-shortcode-copy-label">Copy</span>
                            </button>
                        </div>
                        <?php endif; ?>
                        <?php if ( ! empty( $sc_by_id ) ) : ?>
                        <div class="dt-shortcode-item">
                            <span class="dt-shortcode-badge dt-shortcode-badge--id">ID</span>
                            <code class="dt-shortcode-code"><?php echo esc_html( $sc_by_id ); ?></code>
                            <button type="button" class="dt-shortcode-copy" data-clipboard-text="<?php echo esc_attr( $sc_by_id ); ?>" title="Copy to clipboard">
                                <span class="dashicons dashicons-admin-page"></span>
                                <span class="dt-shortcode-copy-label">Copy</span>
                            </button>
                        </div>
                        <?php endif; ?>

                        <div class="dt-shortcode-footer">
                            <span class="dt-usage-count<?php echo $usage_count > 0 ? ' is-active' : ' is-zero'; ?>" data-count="<?php echo esc_attr( $usage_count ); ?>">
                                <span class="dashicons dashicons-admin-links"></span>
                                <?php if ( $usage_count > 0 ) : ?>
                                    ใช้งาน <strong class="dt-usage-count-num"><?php echo esc_html( $usage_count ); ?></strong> ลิงก์
                                <?php else : ?>
                                    <span class="dt-usage-count-num">ยังไม่มีการใช้งาน</span>
                                <?php endif; ?>
                            </span>
                            <button type="button"
                                class="button dt-shortcode-find-usage"
                                data-shortcode-name="<?php echo esc_attr( $shortcode_name ); ?>"
                                data-item-id="<?php echo esc_attr( $item_id ); ?>"
                                data-item-name="<?php echo esc_attr( $name ); ?>">
                                <span class="dashicons dashicons-search"></span>
                                URL ที่ใช้ภาพนี้
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 5. Shortcode Implementation
 * Usage:
 *   By name: [product_img name="filler_01" alt="" class="" caption=""]
 *   By ID:   [product_img id="5" alt="" class="" caption=""]
 */
function product_image_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'name' => '',
        'id' => '',
        'class' => '',
        'alt' => '',
        'caption' => '',
    ), $atts, 'product_img' );

    if ( empty( $atts['name'] ) && empty( $atts['id'] ) ) {
        return '';
    }

    $data = get_option( 'product_images_data', array() );
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
    
    // Find the image with the matching shortcode name
    $found_image = null;
    // foreach ( $items as $item ) {
    //     if ( isset($item['shortcode_name']) && $item['shortcode_name'] === $atts['name'] ) {
    //         $found_image = $item;
    //         break;
    //     }
    // }
    if ( ! empty( $atts['id'] ) ) {
        // $lookup_id = intval( $atts['id'] );
        foreach ( $items as $item ) {
            // if ( isset( $item['item_id'] ) && intval( $item['item_id'] ) === $lookup_id ) {
            if ( isset($item['item_id']) && $item['item_id'] === $atts['id'] ) {
                $found_image = $item;
                break;
            }
        }
    } elseif ( ! empty( $atts['name'] ) ) {
        foreach ( $items as $item ) {
            if ( isset($item['shortcode_name']) && $item['shortcode_name'] === $atts['name'] ) {
                $found_image = $item;
                break;
            }
        }
    }

    if ( ! $found_image ) {
        return '';
    }

    $img_id = isset($found_image['image_id']) ? $found_image['image_id'] : 0;
    
    // Prepare attributes
    $args = array( 'class' => $atts['class'] );
    if ( ! empty( $atts['alt'] ) ) {
        $args['alt'] = $atts['alt'];
    }

    // Return image using wp_get_attachment_image if ID exists
    if ( $img_id ) {
        if ( ! empty( $atts['caption'] ) ) {
            return '<div class="wp-block-image"><figure class="aligncenter size-full">' . wp_get_attachment_image( $img_id, 'full', false, $args ) . '<figcaption class="wp-element-caption">' . esc_html( $atts['caption'] ) . '</figcaption></figure></div>';
        } else {
            return '<div class="wp-block-image"><figure class="aligncenter size-full">' . wp_get_attachment_image( $img_id, 'full', false, $args ) . '</figure></div>';
        }
    } 
    
    // Fallback if no ID (only URL)
    if ( ! empty( $found_image['image_url'] ) ) {
        // $alt = ! empty( $atts['alt'] ) ? $atts['alt'] : $atts['name'];
        $fallback_alt = ! empty( $atts['name'] ) ? $atts['name'] : ( isset( $found_image['shortcode_name'] ) ? $found_image['shortcode_name'] : '' );
        $alt = ! empty( $atts['alt'] ) ? $atts['alt'] : $fallback_alt;
        if ( ! empty( $atts['caption'] ) ) {
            return sprintf(
                '<div class="wp-block-image"><figure class="aligncenter size-full"><img src="%s" alt="%s" class="%s" /><figcaption class="wp-element-caption">%s</figcaption></figure></div>',
                esc_url( $found_image['image_url'] ),
                esc_attr( $alt ),
                esc_attr( $atts['class'] ),
                esc_html( $atts['caption'] )
            );
        } else {
            return sprintf(
                '<div class="wp-block-image"><figure class="aligncenter size-full"><img src="%s" alt="%s" class="%s" /></figure></div>',
                esc_url( $found_image['image_url'] ),
                esc_attr( $alt ),
                esc_attr( $atts['class'] ),
            );
        }
    }

    return '';
}
add_shortcode( 'product_img', 'product_image_shortcode' );

/**
 * 6. Find Usages Handler
 * ค้นหาโพสต์/เพจที่มีการเรียกใช้ shortcode [product_img name="..."] หรือ [product_img id="..."]
 * ค้นทั้งใน post_content และ postmeta (รองรับทั้ง shortcode ปกติและแบบ escaped ใน JSON/Elementor)
 */
add_action( 'wp_ajax_product_image_find_usages', 'product_image_find_usages_handler' );
function product_image_find_usages_handler() {
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_send_json_error( array( 'message' => 'Unauthorized' ) );
    }
    check_ajax_referer( 'product_image_find_usages_action', '_nonce' );

    global $wpdb;

    $shortcode_name = isset( $_POST['shortcode_name'] ) ? sanitize_text_field( wp_unslash( $_POST['shortcode_name'] ) ) : '';
    $item_id      = isset( $_POST['item_id'] ) ? intval( $_POST['item_id'] ) : 0;

    if ( empty( $shortcode_name ) && empty( $item_id ) ) {
        wp_send_json_error( array( 'message' => 'Missing identifier' ) );
    }

    // สร้าง LIKE patterns - ค้นทั้งรูปแบบปกติและรูปแบบ JSON-escaped (\")
    // ใน MySQL ต้อง escape backslash เป็น \\ ใน PHP string ก็ต้องใช้ \\\\
    $patterns = array();

    if ( ! empty( $shortcode_name ) ) {
        $name_esc = $wpdb->esc_like( $shortcode_name );
        // รูปแบบปกติ: [product_img ... name="xxx"
        $patterns[] = '%[product_img%name="' . $name_esc . '"%';
        // รูปแบบ JSON escaped (ใน Elementor/page builder): [product_img ... name=\"xxx\"
        $patterns[] = '%[product_img%name=\\\\"' . $name_esc . '\\\\"%';
    }

    if ( ! empty( $item_id ) ) {
        $id_esc = $wpdb->esc_like( (string) $item_id );
        $patterns[] = '%[product_img%id="' . $id_esc . '"%';
        $patterns[] = '%[product_img%id=\\\\"' . $id_esc . '\\\\"%';
    }

    if ( empty( $patterns ) ) {
        wp_send_json_success( array( 'results' => array() ) );
    }

    $statuses       = array( 'publish', 'private', 'draft', 'pending', 'future' );
    $status_place   = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
    $like_place     = implode( ' OR ', array_fill( 0, count( $patterns ), 'post_content LIKE %s' ) );

    // Query 1: ค้นใน post_content
    $sql_posts = "SELECT DISTINCT ID, post_title, post_type, post_status
                  FROM {$wpdb->posts}
                  WHERE post_status IN ($status_place)
                    AND post_type NOT IN ('revision', 'attachment', 'nav_menu_item')
                    AND ( $like_place )";

    $args_posts = array_merge( $statuses, $patterns );
    $rows_posts = $wpdb->get_results( $wpdb->prepare( $sql_posts, $args_posts ) );

    // Query 2: ค้นใน postmeta (สำหรับ page builder เช่น Elementor)
    $meta_like_place = implode( ' OR ', array_fill( 0, count( $patterns ), 'pm.meta_value LIKE %s' ) );
    $sql_meta = "SELECT DISTINCT p.ID, p.post_title, p.post_type, p.post_status
                 FROM {$wpdb->posts} p
                 INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
                 WHERE p.post_status IN ($status_place)
                   AND p.post_type NOT IN ('revision', 'attachment', 'nav_menu_item')
                   AND ( $meta_like_place )";

    $args_meta = array_merge( $statuses, $patterns );
    $rows_meta = $wpdb->get_results( $wpdb->prepare( $sql_meta, $args_meta ) );

    // Query 3: ค้นใน options (widget/theme options) - เช่น customizer, widgets
    $sql_opts = "SELECT option_id, option_name
                 FROM {$wpdb->options}
                 WHERE " . implode( ' OR ', array_fill( 0, count( $patterns ), 'option_value LIKE %s' ) );
    $rows_opts = $wpdb->get_results( $wpdb->prepare( $sql_opts, $patterns ) );

    // รวมผลลัพธ์ไม่ซ้ำ
    $results = array();
    $seen    = array();

    foreach ( array_merge( $rows_posts, $rows_meta ) as $row ) {
        if ( isset( $seen[ $row->ID ] ) ) {
            continue;
        }
        $seen[ $row->ID ] = true;

        $view_url = get_permalink( $row->ID );
        $edit_url = get_edit_post_link( $row->ID, 'raw' );
        $pt_obj   = get_post_type_object( $row->post_type );

        $results[] = array(
            'id'          => (int) $row->ID,
            'title'       => $row->post_title ? $row->post_title : '(ไม่มีชื่อ)',
            'post_type'   => $row->post_type,
            'type_label'  => $pt_obj ? $pt_obj->labels->singular_name : $row->post_type,
            'status'      => $row->post_status,
            'view_url'    => $view_url ? $view_url : '',
            'edit_url'    => $edit_url ? $edit_url : '',
        );
    }

    // เรียงตาม post_type แล้วตาม title
    usort( $results, function ( $a, $b ) {
        if ( $a['post_type'] === $b['post_type'] ) {
            return strcasecmp( $a['title'], $b['title'] );
        }
        return strcmp( $a['post_type'], $b['post_type'] );
    } );

    // แปลง options เป็น entry (ไม่มี edit link แต่แสดงชื่อ option)
    $option_results = array();
    foreach ( $rows_opts as $opt ) {
        $option_results[] = array(
            'option_name' => $opt->option_name,
        );
    }

    wp_send_json_success( array(
        'results' => $results,
        'options' => $option_results,
        'total'   => count( $results ),
    ) );
}