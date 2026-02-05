<?php
/**
 * Button CTA Footer Options Page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Add Menu
function cta_footer_add_admin_menu() {
    add_menu_page(
        'Button CTA',
        'Button CTA',
        'manage_options',
        'cta-footer-settings',
        'cta_footer_options_page_html',
        'dashicons-button',
        50
    );
}
add_action( 'admin_menu', 'cta_footer_add_admin_menu' );

// 2. Register Setting
function cta_footer_settings_init() {
    register_setting( 'cta_footer_option_group', 'cta_footer_data' );
}
add_action( 'admin_init', 'cta_footer_settings_init' );

// 3. Enqueue Assets
function cta_footer_admin_assets( $hook ) {
    if ( 'toplevel_page_cta-footer-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media();
    wp_enqueue_style( 'wp-color-picker' );
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_script( 'jquery-ui-sortable' );

    wp_enqueue_style( 
        'cta-footer-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-cta-footer.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-cta-footer.css' ) 
    );

    wp_enqueue_script( 
        'cta-footer-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-cta-footer.js', 
        array( 'jquery', 'jquery-ui-sortable', 'wp-color-picker' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-cta-footer.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'cta_footer_admin_assets' );

// 4. Render Page
function cta_footer_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'cta_footer_data', array() );
    
    // Ensure data structure for each language
    $data_th = isset($data['th']) && is_array($data['th']) ? $data['th'] : array();
    $data_en = isset($data['en']) && is_array($data['en']) ? $data['en'] : array();
    $data_cn = isset($data['cn']) && is_array($data['cn']) ? $data['cn'] : array();
    ?>
    <div class="wrap">
        <h1>Button CTA Footer</h1>
        
        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'cta_footer_data', 'cta_footer_message', 'Settings Saved', 'updated' );
        }
        settings_errors( 'cta_footer_data' );
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields( 'cta_footer_option_group' );
            do_settings_sections( 'cta-footer-settings' );
            ?>
            
            <!-- TH Section -->
            <div class="cta-footer">
                <h2 style="padding: 15px; margin: 0; border-bottom: 1px solid #eee;">Button CTA (TH)</h2>
                <div style="padding: 15px;">
                    <div class="cta-list-container" data-lang="th">
                        <div class="cta-list">
                            <?php
                            foreach ( $data_th as $index => $row ) {
                                cta_footer_render_row( $index, $row, 'th' );
                            }
                            ?>
                        </div>
                        <div style="margin-top: 15px;">
                            <button type="button" class="button button-primary add-cta-row" data-lang="th">Add Button (TH)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- EN Section -->
            <div class="cta-footer">
                <h2 style="padding: 15px; margin: 0; border-bottom: 1px solid #eee;">Button CTA (EN)</h2>
                <div style="padding: 15px;">
                    <div class="cta-list-container" data-lang="en">
                        <div class="cta-list">
                            <?php
                            foreach ( $data_en as $index => $row ) {
                                cta_footer_render_row( $index, $row, 'en' );
                            }
                            ?>
                        </div>
                        <div style="margin-top: 15px;">
                            <button type="button" class="button button-primary add-cta-row" data-lang="en">Add Button (EN)</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CN Section -->
            <div class="cta-footer">
                <h2 style="padding: 15px; margin: 0; border-bottom: 1px solid #eee;">Button CTA (CN)</h2>
                <div style="padding: 15px;">
                    <div class="cta-list-container" data-lang="cn">
                        <div class="cta-list">
                            <?php
                            foreach ( $data_cn as $index => $row ) {
                                cta_footer_render_row( $index, $row, 'cn' );
                            }
                            ?>
                        </div>
                        <div style="margin-top: 15px;">
                            <button type="button" class="button button-primary add-cta-row" data-lang="cn">Add Button (CN)</button>
                        </div>
                    </div>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>

        <!-- Template -->
        <script type="text/template" id="cta-row-template">
            <?php cta_footer_render_row( '{{index}}', array(), '{{lang}}' ); ?>
        </script>
    </div>
    <?php
}

/**
 * Unified Render Function
 * @param string $index
 * @param array $row
 * @param string $lang (th, en, cn)
 */
function cta_footer_render_row( $index, $row, $lang = 'th' ) {
    $image_id = isset( $row['image_id'] ) ? $row['image_id'] : '';
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
    
    $id = isset( $row['id'] ) ? $row['id'] : '';
    $border_color = isset( $row['border_color'] ) ? $row['border_color'] : '#333333';
    $url = isset( $row['url'] ) ? $row['url'] : '';
    $open_new = isset( $row['open_new'] ) ? $row['open_new'] : 'off';
    $enable = isset( $row['enable'] ) ? $row['enable'] : 'enable';
    
    // Unique ID for radio inputs
    $unique_id = $lang . '_' . $index . '_' . rand(100,999);
    
    // Field Name Prefix: cta_footer_data[th][0][field]
    $prefix = "cta_footer_data[{$lang}][{$index}]";
    ?>
    <div class="cta-row">
        <h3>
            <span><span class="dashicons dashicons-menu" style="color:#ccc; margin-right:5px;"></span> Button Item (<?php echo strtoupper($lang); ?>)</span>
            <span class="cta-remove-row dashicons dashicons-no-alt remove-row-btn" title="Remove"></span>
        </h3>
        <div class="cta-fields">
            <!-- 1. Image -->
            <div class="cta-field cta-field-image">
                <label>Image</label>
                <div class="cta-image-preview">
                    <?php if($image_url): ?>
                        <img src="<?php echo esc_url($image_url); ?>">
                    <?php else: ?>
                        <span class="dashicons dashicons-format-image" style="color:#ddd; font-size: 30px; width:30px; height:30px;"></span>
                    <?php endif; ?>
                </div>
                <input type="hidden" name="<?php echo $prefix; ?>[image_id]" class="cta-image-id" value="<?php echo esc_attr($image_id); ?>">
                <button type="button" class="button upload-cta-image" style="width:100%;"><?php echo $image_id ? 'Change Image' : 'Select Image'; ?></button>
            </div>

            <!-- 2. ID -->
            <div class="cta-field cta-field-id">
                <label>ID</label>
                <input type="text" name="<?php echo $prefix; ?>[id]" value="<?php echo esc_attr($id); ?>" class="widefat" placeholder="">
            </div>

            <!-- 3. Border Color -->
            <div class="cta-field cta-field-color">
                <label>Border Color</label>
                <input type="text" name="<?php echo $prefix; ?>[border_color]" value="<?php echo esc_attr($border_color); ?>" class="color-field">
            </div>

            <!-- 4. URL -->
            <div class="cta-field cta-field-url">
                <label>URL</label>
                <input type="text" name="<?php echo $prefix; ?>[url]" value="<?php echo esc_attr($url); ?>" class="widefat" placeholder="https://">
            </div>

            <!-- 5. Open new window -->
            <div class="cta-field cta-field-switch">
                <label>Open new window</label>
                <div class="switch-field">
                    <input type="radio" id="open_new_<?php echo $unique_id; ?>_on" name="<?php echo $prefix; ?>[open_new]" value="on" <?php checked( $open_new, 'on' ); ?> />
                    <label for="open_new_<?php echo $unique_id; ?>_on">ON</label>
                    <input type="radio" id="open_new_<?php echo $unique_id; ?>_off" name="<?php echo $prefix; ?>[open_new]" value="off" <?php checked( $open_new, 'off' ); ?> />
                    <label for="open_new_<?php echo $unique_id; ?>_off">OFF</label>
                </div>
            </div>

            <!-- 6. Enable -->
            <div class="cta-field cta-field-switch">
                <label>Enable</label>
                <div class="switch-field">
                    <input type="radio" id="enable_<?php echo $unique_id; ?>_on" name="<?php echo $prefix; ?>[enable]" value="enable" <?php checked( $enable, 'enable' ); ?> />
                    <label for="enable_<?php echo $unique_id; ?>_on">Enable</label>
                    <input type="radio" id="enable_<?php echo $unique_id; ?>_off" name="<?php echo $prefix; ?>[enable]" value="disable" <?php checked( $enable, 'disable' ); ?> />
                    <label for="enable_<?php echo $unique_id; ?>_off">Disable</label>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 5. Shortcode Implementation
 * [cta_footer_th]
 * [cta_footer_en]
 * [cta_footer_cn]
 */
function cta_footer_shortcode( $atts, $content = null, $tag = '' ) {
    // Determine language from tag
    $lang = 'th';
    if ( $tag === 'cta_footer_en' ) {
        $lang = 'en';
    } elseif ( $tag === 'cta_footer_cn' ) {
        $lang = 'cn';
    }

    $data = get_option( 'cta_footer_data', array() );
    $items = isset($data[$lang]) && is_array($data[$lang]) ? $data[$lang] : array();

    if ( empty( $items ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="cta-footer-wrapper cta-footer-<?php echo esc_attr($lang); ?>">
        <?php foreach ( $items as $item ) : 
            $enable = isset( $item['enable'] ) ? $item['enable'] : 'enable';
            if ( $enable === 'disable' ) continue;

            $image_id = isset( $item['image_id'] ) ? $item['image_id'] : '';
            $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
            $id = isset( $item['id'] ) ? $item['id'] : '';
            $border_color = isset( $item['border_color'] ) ? $item['border_color'] : '#333333';
            $url = isset( $item['url'] ) ? $item['url'] : '#';
            $open_new = isset( $item['open_new'] ) ? $item['open_new'] : 'off';
            ?>
            <?php if ( $image_url ) : ?>
            <a <?php $id ? 'id="' . esc_attr( $id ) . '" ' : ''; ?>href="<?php echo esc_url( $url ); ?>" class="cta-footer-item" style="border-color: <?php echo esc_attr( $border_color ); ?>;"<?php $open_new === 'on' ? ' target="_blank"' : ''; ?>>
                <div class="cta-footer-image">
                    <?php echo wp_get_attachment_image( $image_id, 'full', false, array( 'class' => '' ) ); ?>
                </div>
            </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <style>
        .cta-footer-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }
        .cta-footer-item {
            display: flex;
            align-items: center;
            height: 50px;
            border: 2px solid #333;
            border-radius: 50px;
            padding: 0;
            text-decoration: none;
            color: #333;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #fff;
        }
        .cta-footer-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .cta-footer-image {
            display: block;
            width: 100%;
            height: 100%;
            flex-shrink: 0;
        }
        .cta-footer-image img {
            display: block;
            width: auto;
            height: 100%;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'cta_footer_th', 'cta_footer_shortcode' );
add_shortcode( 'cta_footer_en', 'cta_footer_shortcode' );
add_shortcode( 'cta_footer_cn', 'cta_footer_shortcode' );
