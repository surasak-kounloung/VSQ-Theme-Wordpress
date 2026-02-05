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
        47
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
}
add_action( 'admin_enqueue_scripts', 'product_image_admin_assets' );

// 4. Render Options Page
function product_image_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'product_images_data', array() );
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
    $categories = product_image_get_categories();

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
                            
                            <!-- Image List -->
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="heading">Product Images List</h2>
                                </div>
                                <div class="inside">
                                    
                                    <!-- Filter & Pagination Toolbar -->
                                    <div class="dt-toolbar tablenav top">
                                        <div class="alignleft actions">
                                            <select id="product-image-filter-category">
                                                <option value="">View All Categories</option>
                                                <?php foreach($categories as $key => $label): ?>
                                                    <option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="tablenav-pages">
                                            <span class="displaying-num"><span id="total-items">0</span> items</span>
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
                                        <br class="clear">
                                    </div>

                                    <div class="dt-repeater-container">
                                        <?php 
                                        if ( ! empty( $items ) ) :
                                            foreach ( $items as $index => $row ) :
                                                product_image_render_row( $index, $row );
                                            endforeach;
                                        endif; 
                                        ?>
                                    </div>
                                    
                                    <!-- No Items Found Message -->
                                    <div class="dt-no-items" style="display:none; padding: 50px 20px 30px; text-align: center; color: #666;">
                                        No images found for this category.
                                    </div>

                                    <div class="dt-actions" style="margin-top: 20px;">
                                        <button class="button button-primary dt-repeater-add">Add New Image</button>
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
    </div>
    <?php
}

// Helper to render a single row
function product_image_render_row( $index, $data ) {
    // Extract values
    $category = isset( $data['category'] ) ? $data['category'] : '';
    $shortcode_name = isset( $data['shortcode_name'] ) ? $data['shortcode_name'] : '';
    $image_url = isset( $data['image_url'] ) ? $data['image_url'] : '';
    $image_id = isset( $data['image_id'] ) ? $data['image_id'] : '';
    
    $categories = product_image_get_categories();
    
    ?>
    <div class="dt-repeater-row" data-category="<?php echo esc_attr($category); ?>">
        <div class="dt-row-header">
            <span class="dt-row-title">Image Item</span>
            <div class="dt-row-actions">
                 <span class="dt-toggle-row dashicons dashicons-minus"></span>
                 <span class="dt-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
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
                        <button type="button" class="button upload-image-button">Select Image</button>
                        <button type="button" class="button remove-image-button" style="color: #a00;">Remove</button>
                    </div>
                </div>

                <!-- Category -->
                <div class="dt-field-col">
                    <label>Category</label>
                    <select name="product_images_data[items][<?php echo $index; ?>][category]" class="category-select">
                        <option value="">Select Category</option>
                        <?php foreach($categories as $key => $label): ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($category, $key); ?>><?php echo esc_html($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Shortcode Name -->
                <div class="dt-field-col">
                    <label>Shortcode Name (Key)</label>
                    <input type="text" name="product_images_data[items][<?php echo $index; ?>][shortcode_name]" value="<?php echo esc_attr( $shortcode_name ); ?>" placeholder="e.g. filler_under_eye">
                    <p class="description">Shortcode: [product_img name="<?php echo esc_attr( $shortcode_name ); ?>" alt="" class="" caption=""]</p>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 5. Shortcode Implementation
 * Usage: [product_img name="filler_01" alt="" class="" caption=""]
 */
function product_image_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'name' => '',
        'class' => '',
        'alt' => '',
        'caption' => '',
    ), $atts, 'product_img' );

    if ( empty( $atts['name'] ) ) {
        return '';
    }

    $data = get_option( 'product_images_data', array() );
    $items = isset($data['items']) && is_array($data['items']) ? $data['items'] : array();
    
    // Find the image with the matching shortcode name
    $found_image = null;
    foreach ( $items as $item ) {
        if ( isset($item['shortcode_name']) && $item['shortcode_name'] === $atts['name'] ) {
            $found_image = $item;
            break;
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
        $alt = ! empty( $atts['alt'] ) ? $atts['alt'] : $atts['name'];
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
