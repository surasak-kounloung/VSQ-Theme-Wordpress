<?php
/**
 * Promotion Options Page (Custom Repeater UI)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Add Admin Menu
function promotion_add_admin_menu() {
    add_menu_page(
        'Promotion Settings',
        'Promotion',
        'manage_options',
        'promotion-settings',
        'promotion_options_page_html',
        'dashicons-images-alt',
        45
    );
}
add_action( 'admin_menu', 'promotion_add_admin_menu' );

// 2. Register Settings
function promotion_settings_init() {
    register_setting( 'promotion_option_group', 'promotion_data' );
}
add_action( 'admin_init', 'promotion_settings_init' );

// 3. Enqueue Assets (JS/CSS)
function promotion_admin_assets( $hook ) {
    if ( 'toplevel_page_promotion-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media(); // Enqueue WordPress Media Uploader

    wp_enqueue_style( 
        'promotion-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-promotion.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-promotion.css' ) 
    );

    wp_enqueue_script( 
        'promotion-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-promotion.js', 
        array( 'jquery', 'jquery-ui-sortable' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-promotion.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'promotion_admin_assets' );

// 4. Render Options Page
function promotion_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $promotions = get_option( 'promotion_data', array() );
    if ( ! is_array( $promotions ) ) {
        $promotions = array();
    }
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Promotion Settings</h1>
        <hr class="wp-header-end">

        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'promotion_data', 'promotion_settings_updated', 'Promotion Updated.', 'updated' );
        }
        settings_errors( 'promotion_data' );
        ?>
        
        <form action="options.php" method="post">
            <?php
            settings_fields( 'promotion_option_group' );
            do_settings_sections( 'promotion-settings' );
            ?>

            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    
                    <!-- Right Sidebar (Publish Box) -->
                    <div id="postbox-container-1" class="postbox-container">
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
                            <div class="postbox">
                                <div class="postbox-header">
                                    <h2 class="hndle">Promotion List</h2>
                                    <div class="handle-actions hide-if-no-js">
                                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Promotion List</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                    </div>
                                </div>
                                <div class="inside">
                                    
                                    <div class="sb-repeater-container">
                                        <?php 
                                        if ( ! empty( $promotions ) ) :
                                            foreach ( $promotions as $index => $promotion ) :
                                                promotion_render_row( $index, $promotion );
                                            endforeach;
                                        endif; 
                                        ?>
                                    </div>

                                    <div class="sb-actions">
                                        <button class="button button-primary sb-repeater-add">Add Row</button>
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
        <script type="text/template" id="sb-repeater-template">
            <?php promotion_render_row( '{{index}}', array() ); ?>
        </script>
    </div>
    <?php
}

// Helper function to render a single row
function promotion_render_row( $index, $data ) {
    $image_promotion = isset( $data['image_promotion'] ) ? $data['image_promotion'] : '';
    $image_promotion_id = isset( $data['image_promotion_id'] ) ? $data['image_promotion_id'] : '';
    $type_promotion = isset( $data['type_promotion'] ) ? $data['type_promotion'] : '';
    
    // Data for slides
    $slides_promotion = isset( $data['slides_promotion'] ) ? $data['slides_promotion'] : array();
    $id_promotion = isset( $data['id_promotion'] ) ? $data['id_promotion'] : '';

    ?>
    <div class="sb-repeater-row">
        <div class="sb-row-header">
            <span class="sb-row-handle dashicons dashicons-menu"></span>
            <span class="sb-row-number"><?php echo is_numeric($index) ? $index + 1 : ''; ?></span>
            <span class="sb-row-actions">
                 <span class="sb-remove-row dashicons dashicons-no-alt" title="Remove row"></span>
            </span>
        </div>
        <div class="sb-row-content">
            <div class="sb-row-columns">
                <div class="sb-column" style="width: 15%;">
                    <div class="sb-field">
                         <label>Type Promotion</label>
                         <div class="sb-field-checkbox">
                              <input type="checkbox" class="sb-type-promotion-checkbox" name="promotion_data[<?php echo $index; ?>][type_promotion]" value="1" <?php checked( $type_promotion, '1' ); ?>>
                              Slides
                         </div>
                      </div>
                </div>
                <!-- Image Section -->
                <div class="sb-column" style="width: 85%;">
                    
                    <!-- 1. Single Image Mode -->
                    <div class="sb-single-image-group" style="<?php echo $type_promotion == '1' ? 'display:none;' : ''; ?>">
                        <div class="sb-field">
                            <label>Promotion Image</label>
                            <div class="sb-image-preview-wrapper">
                                <?php 
                                $has_image_promotion = ! empty( $image_promotion );
                                ?>
                                <div class="sb-image-preview single-image-preview">
                                    <?php if ( $has_image_promotion ) : ?>
                                        <img src="<?php echo esc_url( $image_promotion ); ?>">
                                    <?php endif; ?>
                                </div>
                                
                                <input type="hidden" class="sb-image-url" name="promotion_data[<?php echo $index; ?>][image_promotion]" value="<?php echo esc_attr( $image_promotion ); ?>">
                                <input type="hidden" class="sb-image-id" name="promotion_data[<?php echo $index; ?>][image_promotion_id]" value="<?php echo esc_attr( $image_promotion_id ); ?>">
                                
                                <button class="button sb-upload-image" <?php echo $has_image_promotion ? 'style="display:none;"' : ''; ?>>Add Image</button>
                                <button class="button sb-remove-image" <?php echo ! $has_image_promotion ? 'style="display:none;"' : ''; ?>>Remove</button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Slides Mode (Repeater inside Repeater) -->
                    <div class="sb-slides-group" style="<?php echo $type_promotion == '1' ? '' : 'display:none;'; ?>">
                        <label style="display:block; margin-bottom: 10px;">Promotion Images (Slides)</label>
                        
                        <div class="sb-slides-list">
                            <?php 
                            if ( ! empty( $slides_promotion ) && is_array( $slides_promotion ) ) :
                                foreach ( $slides_promotion as $slide_key => $slide ) :
                                    $slide_img = isset( $slide['image'] ) ? $slide['image'] : '';
                                    $slide_id  = isset( $slide['id'] ) ? $slide['id'] : '';
                                    $has_slide = ! empty( $slide_img );
                                    ?>
                                    <div class="sb-slide-item" style="border: 1px dashed #ccc; padding: 10px; position: relative;">
                                        <span class="sb-slide-handle dashicons dashicons-menu"></span>
                                        <span class="sb-remove-slide-item dashicons dashicons-no-alt" style="position: absolute; top: 5px; right: 5px; width: 30px; height: 30px; font-size: 30px; cursor: pointer; color: #a00;" title="Remove Slide"></span>
                                        <div class="sb-image-preview-wrapper">
                                            <div class="sb-image-preview">
                                                <?php if ( $has_slide ) : ?>
                                                    <img src="<?php echo esc_url( $slide_img ); ?>">
                                                <?php endif; ?>
                                            </div>
                                            <!-- Use a unique key for array to avoid index conflicts -->
                                            <input type="hidden" class="sb-image-url" name="promotion_data[<?php echo $index; ?>][slides_promotion][<?php echo $slide_key; ?>][image]" value="<?php echo esc_attr( $slide_img ); ?>">
                                            <input type="hidden" class="sb-image-id" name="promotion_data[<?php echo $index; ?>][slides_promotion][<?php echo $slide_key; ?>][id]" value="<?php echo esc_attr( $slide_id ); ?>">
                                            
                                            <button class="button sb-upload-image" <?php echo $has_slide ? 'style="display:none;"' : ''; ?>>Select Image</button>
                                            <button class="button sb-remove-image" <?php echo ! $has_slide ? 'style="display:none;"' : ''; ?>>Remove Image</button>
                                        </div>
                                    </div>
                                    <?php
                                endforeach;
                            endif;
                            ?>
                        </div>

                        <button class="button sb-add-slide" style="margin-top: 15px;">+ Add Slide Image</button>
                    </div>

                    <div class="sb-field">
                        <label>ID Promotion</label>
                        <input type="text" name="promotion_data[<?php echo $index; ?>][id_promotion]" value="<?php echo esc_attr( $id_promotion ); ?>" placeholder="" class="widefat">
                    </div>

                </div>
            </div>
        </div>
    </div>
    <?php
}

// 5. Shortcode [promotion_list]
function promotion_list_shortcode() {
    $promotions = get_option( 'promotion_data', array() );
    
    if ( empty( $promotions ) || ! is_array( $promotions ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="sb-promotion-container">
        <?php foreach ( $promotions as $promo ) : 
            $type = isset( $promo['type_promotion'] ) ? $promo['type_promotion'] : '';
            
            // --- Case: Slides ---
            if ( $type === '1' ) {
                $slides = isset( $promo['slides_promotion'] ) ? $promo['slides_promotion'] : array();
                if ( ! empty( $slides ) && is_array( $slides ) ) :
                    ?>
                    <div class="sb-promotion-item sb-type-slides">
                        <?php if ( isset( $promo['id_promotion'] ) && ! empty( $promo['id_promotion'] ) ) : ?>
                            <div id="<?php echo $promo['id_promotion']; ?>" class="sb-promotion-id"></div>
                        <?php endif; ?>
                        <div class="sb-slider-wrapper">
                            <?php foreach ( $slides as $slide ) : 
                                $img_url = isset( $slide['image'] ) ? $slide['image'] : '';
                                $img_id  = isset( $slide['id'] ) ? $slide['id'] : '';
                                
                                if ( ! $img_url && ! $img_id ) continue;
                                ?>
                                <div class="sb-slide">
                                    <?php 
                                    if ( $img_id ) {
                                        echo wp_get_attachment_image( $img_id, 'full' );
                                    } else {
                                        echo '<img src="' . esc_url( $img_url ) . '" alt="">';
                                    }
                                    ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php
                endif;
            } 
            // --- Case: Single Image ---
            else {
                $img_url = isset( $promo['image_promotion'] ) ? $promo['image_promotion'] : '';
                $img_id  = isset( $promo['image_promotion_id'] ) ? $promo['image_promotion_id'] : '';
                
                if ( $img_url || $img_id ) :
                    ?>
                    <div class="sb-promotion-item sb-type-single">
                        <?php if ( isset( $promo['id_promotion'] ) && ! empty( $promo['id_promotion'] ) ) : ?>
                            <div id="<?php echo $promo['id_promotion']; ?>" class="sb-promotion-id"></div>
                        <?php endif; ?>
                        <?php 
                        if ( $img_id ) {
                            echo wp_get_attachment_image( $img_id, 'full' );
                        } else {
                            echo '<img src="' . esc_url( $img_url ) . '" alt="">';
                        }
                        ?>
                    </div>
                    <?php
                endif;
            }
        endforeach; ?>
    </div>
    
    <style>
        .sb-promotion-container { width: 100%; max-width: 100%; }
        .sb-promotion-item { margin-bottom: 20px; }
        .sb-promotion-item img { display: block; width: 100%; height: auto; }
        
        /* Optional: Basic slider styles (if using a script, these classes can be targeted) */
        .sb-type-slides .sb-slider-wrapper {
            display: flex;
            overflow-x: auto;
            scroll-snap-type: x mandatory;
            gap: 0;
        }
        .sb-type-slides .sb-slide {
            min-width: 100%;
            scroll-snap-align: start;
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'promotion_list', 'promotion_list_shortcode' );