<?php
/**
 * Slide Banner Options Page (Custom Repeater UI)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Add Admin Menu
function sb_add_admin_menu() {
    add_menu_page(
        'Slide Banner Settings',
        'Slide Banner',
        'manage_options',
        'slide-banner-settings',
        'sb_options_page_html',
        'dashicons-images-alt2',
        40
    );
}
add_action( 'admin_menu', 'sb_add_admin_menu' );

// 2. Register Settings
function sb_settings_init() {
    register_setting( 'slide_banner_option_group', 'slide_banner_data' );
}
add_action( 'admin_init', 'sb_settings_init' );

// 3. Enqueue Assets (JS/CSS)
function sb_admin_assets( $hook ) {
    if ( 'toplevel_page_slide-banner-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media(); // Enqueue WordPress Media Uploader

    wp_enqueue_style( 
        'slide-banner-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-slide-banner.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-slide-banner.css' ) 
    );

    wp_enqueue_script( 
        'slide-banner-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-slide-banner.js', 
        array( 'jquery', 'jquery-ui-sortable' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-slide-banner.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'sb_admin_assets' );

// 4. Render Options Page
function sb_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $slides = get_option( 'slide_banner_data', array() );
    if ( ! is_array( $slides ) ) {
        $slides = array();
    }
    
    ?>
    <div class="wrap">
        <h1 class="wp-heading-inline">Slide Banner Settings</h1>
        <hr class="wp-header-end">

        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'slide_banner_data', 'slide_banner_settings_updated', 'Slide Banner Updated.', 'updated' );
        }
        settings_errors( 'slide_banner_data' );
        ?>
        
        <form action="options.php" method="post">
            <?php
            settings_fields( 'slide_banner_option_group' );
            do_settings_sections( 'slide-banner-settings' );
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
                                    <h2 class="hndle">Banner Slide</h2>
                                    <div class="handle-actions hide-if-no-js">
                                        <button type="button" class="handlediv" aria-expanded="true"><span class="screen-reader-text">Toggle panel: Banner Slide</span><span class="toggle-indicator" aria-hidden="true"></span></button>
                                    </div>
                                </div>
                                <div class="inside">
                                    
                                    <div class="sb-repeater-container">
                                        <?php 
                                        if ( ! empty( $slides ) ) :
                                            foreach ( $slides as $index => $slide ) :
                                                sb_render_slide_row( $index, $slide );
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
            <?php sb_render_slide_row( '{{index}}', array() ); ?>
        </script>
    </div>
    <?php
}

// Helper function to render a single row
function sb_render_slide_row( $index, $data ) {
    $image_pc = isset( $data['image_pc'] ) ? $data['image_pc'] : '';
    $image_pc_id = isset( $data['image_pc_id'] ) ? $data['image_pc_id'] : '';

    $image_mobile = isset( $data['image_mobile'] ) ? $data['image_mobile'] : '';
    $image_mobile_id = isset( $data['image_mobile_id'] ) ? $data['image_mobile_id'] : '';

    $link = isset( $data['link'] ) ? $data['link'] : '';
    $new_tab = isset( $data['new_tab'] ) ? $data['new_tab'] : '';
    
    // Construct field names: slide_banner_data[0][image_pc], etc.
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
                <!-- PC Image -->
                <div class="sb-column" style="width: 75%;">
                    <div class="sb-field">
                        <label>Banner Image PC</label>
                        <div class="sb-image-preview-wrapper">
                            <?php 
                            $has_image_pc = ! empty( $image_pc );
                            ?>
                            <div class="sb-image-preview">
                                <?php if ( $has_image_pc ) : ?>
                                    <img src="<?php echo esc_url( $image_pc ); ?>">
                                <?php endif; ?>
                            </div>
                            
                            <input type="hidden" class="sb-image-url" name="slide_banner_data[<?php echo $index; ?>][image_pc]" value="<?php echo esc_attr( $image_pc ); ?>">
                            <input type="hidden" class="sb-image-id" name="slide_banner_data[<?php echo $index; ?>][image_pc_id]" value="<?php echo esc_attr( $image_pc_id ); ?>">
                            
                            <button class="button sb-upload-image" <?php echo $has_image_pc ? 'style="display:none;"' : ''; ?>>Add Image</button>
                            <button class="button sb-remove-image" <?php echo ! $has_image_pc ? 'style="display:none;"' : ''; ?>>Remove</button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Image -->
                <div class="sb-column" style="width: 25%;">
                    <div class="sb-field">
                        <label>Banner Image Mobile</label>
                        <div class="sb-image-preview-wrapper">
                            <?php 
                            $has_image_mobile = ! empty( $image_mobile );
                            ?>
                            <div class="sb-image-preview">
                                <?php if ( $has_image_mobile ) : ?>
                                    <img src="<?php echo esc_url( $image_mobile ); ?>">
                                <?php endif; ?>
                            </div>

                            <input type="hidden" class="sb-image-url" name="slide_banner_data[<?php echo $index; ?>][image_mobile]" value="<?php echo esc_attr( $image_mobile ); ?>">
                            <input type="hidden" class="sb-image-id" name="slide_banner_data[<?php echo $index; ?>][image_mobile_id]" value="<?php echo esc_attr( $image_mobile_id ); ?>">

                            <button class="button sb-upload-image" <?php echo $has_image_mobile ? 'style="display:none;"' : ''; ?>>Add Image</button>
                            <button class="button sb-remove-image" <?php echo ! $has_image_mobile ? 'style="display:none;"' : ''; ?>>Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sb-row-columns">
                 <!-- Link -->
                 <div class="sb-column" style="width: 75%;">
                    <div class="sb-field">
                        <label>Banner Image Link</label>
                        <input type="text" name="slide_banner_data[<?php echo $index; ?>][link]" value="<?php echo esc_attr( $link ); ?>" placeholder="https://..." class="widefat">
                    </div>
                </div>
                 <!-- New Tab -->
                 <div class="sb-column" style="width: 25%;">
                    <div class="sb-field">
                         <label>Open link new window</label>
                         <div class="sb-field-checkbox">
                              <input type="checkbox" name="slide_banner_data[<?php echo $index; ?>][new_tab]" value="1" <?php checked( $new_tab, '1' ); ?>>
                              Enable
                         </div>
                      </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

// 5. Shortcode [slide_banner]
function sb_slide_banner_shortcode() {
    $slides = get_option( 'slide_banner_data', array() );
    
    if ( empty( $slides ) || ! is_array( $slides ) ) {
        return '';
    }

    ob_start();
    ?>
    <div class="sb-slide-container">
        <?php foreach ( $slides as $slide ) : 
            $image_pc_id = isset( $slide['image_pc_id'] ) ? $slide['image_pc_id'] : '';
            $image_mobile_id = isset( $slide['image_mobile_id'] ) ? $slide['image_mobile_id'] : '';

            $link = isset( $slide['link'] ) ? $slide['link'] : '#';
            $target = isset( $slide['new_tab'] ) && $slide['new_tab'] ? '_blank' : '';
            
            if ( ! $image_pc_id && ! $image_mobile_id ) continue;

            // Prepare classes
            $class_pc = 'sb-img-pc';
            if ( $image_mobile_id ) $class_pc .= ' hide-on-mobile';

            $class_mobile = 'sb-img-mobile';
            if ( $image_pc_id ) $class_mobile .= ' hide-on-pc';
            ?>
            <div class="sb-slide-item">
                <?php if ( $link ) : ?>
                    <a href="<?php echo esc_url( $link ); ?>"<?php if ( $target ) { ?> target="<?php echo esc_attr( $target ); ?>" <?php } ?> class="sb-slide-link">
                <?php endif; ?>
                    <!-- PC Image -->
                    <?php if ( $image_pc_id ) : ?>
                        <?php echo wp_get_attachment_image( $image_pc_id, 'full', false, array( 'class' => $class_pc ) ); ?>
                    <?php endif; ?>
                    
                    <!-- Mobile Image -->
                    <?php if ( $image_mobile_id ) : ?>
                        <?php echo wp_get_attachment_image( $image_mobile_id, 'full', false, array( 'class' => $class_mobile ) ); ?>
                    <?php endif; ?>
                <?php if ( $link ) : ?>
                    </a>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
    <style>
        .sb-slide-container { position: relative; }
        .sb-slide-item img { display: block; width: 100%; height: auto; }
        
        /* Simple Responsive Classes */
        @media (max-width: 768px) {
            .hide-on-mobile { display: none !important; }
        }
        @media (min-width: 769px) {
            .hide-on-pc { display: none !important; }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'slide_banner', 'sb_slide_banner_shortcode' );
