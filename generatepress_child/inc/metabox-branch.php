<?php
/**
 * Meta Boxes for Page Branch
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Add Meta Box
function branch_add_meta_boxes() {
    add_meta_box(
        'branch_details_meta_box',
        'Branch Information',
        'branch_render_meta_box',
        'page_branch',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'branch_add_meta_boxes' );

// 2. Render Meta Box
function branch_render_meta_box( $post ) {
    wp_nonce_field( 'branch_save_data', 'branch_meta_nonce' );

    // Get Values
    $branch_thumbnail = get_post_meta( $post->ID, '_branch_thumbnail', true );
    $branch_thumbnail_name = get_post_meta( $post->ID, '_branch_thumbnail_name', true );
    $branch_image_360 = get_post_meta( $post->ID, '_branch_image_360', true );
    $branch_location_image = get_post_meta( $post->ID, '_branch_location_image', true );
    $branch_order = get_post_meta( $post->ID, '_branch_order', true );
    $branch_about = get_post_meta( $post->ID, '_branch_about', true );
    $branch_title = get_post_meta( $post->ID, '_branch_title', true );
    $branch_title_floor = get_post_meta( $post->ID, '_branch_title_floor', true );
    $branch_telephone = get_post_meta( $post->ID, '_branch_telephone', true );
    $branch_id_line = get_post_meta( $post->ID, '_branch_id_line', true );
    $branch_url_line = get_post_meta( $post->ID, '_branch_url_line', true );
    $branch_google_map = get_post_meta( $post->ID, '_branch_google_map', true );
    $branch_google_map_iframe = get_post_meta( $post->ID, '_branch_google_map_iframe', true );
    $branch_car = get_post_meta( $post->ID, '_branch_car', true );
    $branch_bts_or_mrt = get_post_meta( $post->ID, '_branch_bts_or_mrt', true );
    $branch_bus = get_post_meta( $post->ID, '_branch_bus', true );
    $branch_address = get_post_meta( $post->ID, '_branch_address', true );
    $branch_nearby_landmark = get_post_meta( $post->ID, '_branch_nearby_landmark', true );
    $branch_all_opening_time = get_post_meta( $post->ID, '_branch_all_opening_time', true );

    // Get Services
    $branch_services = get_post_meta( $post->ID, '_branch_services', true );
    if ( ! is_array( $branch_services ) ) {
        $branch_services = array();
    }

    // Get Opening Time
    $branch_opening_time = get_post_meta( $post->ID, '_branch_opening_time', true );
    if ( ! is_array( $branch_opening_time ) ) {
        $branch_opening_time = array();
    }

    ?>
    <div class="admin-meta-wrapper">
        
        <div class="admin-field-row-wrapper">
            <div class="w-50 pr-30">
                <!-- Thumbnail -->
                <div class="admin-field-row">
                    <label><strong>Thumbnail</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="branch_thumbnail" id="branch_thumbnail" value="<?php echo esc_attr( $branch_thumbnail ); ?>">
                        <div class="admin-image-preview thumbnail-image-preview">
                            <?php 
                            if ( ! empty( $branch_thumbnail ) ) {
                                $url = wp_get_attachment_image_url( $branch_thumbnail, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $branch_thumbnail ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 200px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt thumbnail-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="branch_add_thumbnail"><?php echo empty($branch_thumbnail) ? 'Add Thumbnail' : 'Change Thumbnail'; ?></button>
                    </div>
                </div>
            </div>

            <div class="w-50 pl-30">
                <!-- Thumbnail Name -->
                <div class="admin-field-row">
                    <label><strong>Thumbnail Name</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="branch_thumbnail_name" id="branch_thumbnail_name" value="<?php echo esc_attr( $branch_thumbnail_name ); ?>">
                        <div class="admin-image-preview thumbnail-name-image-preview">
                            <?php 
                            if ( ! empty( $branch_thumbnail_name ) ) {
                                $url = wp_get_attachment_image_url( $branch_thumbnail_name, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $branch_thumbnail_name ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 200px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt thumbnail-name-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="branch_add_thumbnail_name"><?php echo empty($branch_thumbnail_name) ? 'Add Thumbnail' : 'Change Thumbnail'; ?></button>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <div class="admin-field-row-wrapper">
            <div class="w-50 pr-30">
                <!-- About Branch -->
                <div class="admin-field-row">
                    <label for="branch_about"><strong>About</strong></label>
                    <div class="admin-editor-wrapper">
                    <?php 
                    wp_editor( $branch_about, 'branch_about', array(
                        'textarea_name' => 'branch_about',
                        'media_buttons' => false,
                        'textarea_rows' => 5,
                        'teeny' => false, // Set to false to allow custom toolbar
                        'quicktags' => true,
                        'drag_drop_upload' => false,
                        'tinymce' => array(
                            'toolbar1' => 'formatselect,bold,italic,bullist,numlist,link,unlink,undo,redo', // Add formatselect for headings
                            'block_formats' => 'Paragraph=p;Heading 1=h1;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6',
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
                    )); 
                    ?>
                    </div>
                </div>
                <!-- Image 360 View -->
                <div class="admin-field-row">
                    <label><strong>Image 360 View</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="branch_image_360" id="branch_image_360" value="<?php echo esc_attr( $branch_image_360 ); ?>">
                        <div class="admin-image-preview image-360-preview">
                            <?php 
                            if ( ! empty( $branch_image_360 ) ) {
                                $url = wp_get_attachment_image_url( $branch_image_360, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $branch_image_360 ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 500px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt image-360-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="branch_add_image_360"><?php echo empty($branch_image_360) ? 'Add Image' : 'Change Image'; ?></button>
                    </div>
                </div>
            </div>

            <div class="w-50 pl-30">
                <!-- Order -->
                <div class="admin-field-row">
                    <label for="branch_order"><strong>Order</strong></label>
                    <input type="number" name="branch_order" id="branch_order" value="<?php echo esc_attr( $branch_order ); ?>" class="widefat" style="width: 80px;">
                </div>
                <!-- Title -->
                <div class="admin-field-row">
                    <label for="branch_title"><strong>Title</strong></label>
                    <input type="text" name="branch_title" id="branch_title" value="<?php echo esc_attr( $branch_title ); ?>" class="widefat">
                </div>
                <!-- Title Floor -->
                <div class="admin-field-row">
                    <label for="branch_title_floor"><strong>Title Floor</strong></label>
                    <input type="text" name="branch_title_floor" id="branch_title_floor" value="<?php echo esc_attr( $branch_title_floor ); ?>" class="widefat">
                </div>
                <!-- Telephone -->
                <div class="admin-field-row">
                    <label for="branch_telephone"><strong>Telephone</strong></label>
                    <input type="text" name="branch_telephone" id="branch_telephone" value="<?php echo esc_attr( $branch_telephone ); ?>" class="widefat">
                </div>
                <!-- ID LINE -->
                <div class="admin-field-row">
                    <label for="branch_id_line"><strong>ID LINE</strong></label>
                    <input type="text" name="branch_id_line" id="branch_id_line" value="<?php echo esc_attr( $branch_id_line ); ?>" class="widefat">
                </div>
                <!-- URL LINE -->
                <div class="admin-field-row">
                    <label for="branch_url_line"><strong>URL LINE</strong></label>
                    <input type="text" name="branch_url_line" id="branch_url_line" value="<?php echo esc_attr( $branch_url_line ); ?>" class="widefat">
                </div>
                <!-- Google Map -->
                <div class="admin-field-row">
                    <label for="branch_google_map"><strong>Google Map</strong></label>
                    <input type="text" name="branch_google_map" id="branch_google_map" value="<?php echo esc_attr( $branch_google_map ); ?>" class="widefat">
                </div>
                <!-- Google Map iframe -->
                <div class="admin-field-row">
                    <label for="branch_google_map_iframe"><strong>Google Map iframe</strong></label>
                    <textarea name="branch_google_map_iframe" id="branch_google_map_iframe" class="widefat" rows="5"><?php echo esc_textarea( $branch_google_map_iframe ); ?></textarea>
                </div>
            </div>
        </div>
        <hr>

        <div class="admin-field-row-wrapper">
            <div class="w-50 pr-30">
                <!-- Location Image -->
                <div class="admin-field-row">
                    <label><strong>Location Image</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="branch_location_image" id="branch_location_image" value="<?php echo esc_attr( $branch_location_image ); ?>">
                        <div class="admin-image-preview location-image-preview">
                            <?php 
                            if ( ! empty( $branch_location_image ) ) {
                                $url = wp_get_attachment_image_url( $branch_location_image, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $branch_location_image ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 350px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt location-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="branch_add_location_image"><?php echo empty($branch_location_image) ? 'Add Image' : 'Change Image'; ?></button>
                    </div>
                </div>
                <!-- List of Opening Time -->
                <div class="admin-field-row">
                    <label><strong>List of Opening Time</strong></label>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th width="30"></th>
                                    <th>Day</th>
                                    <th>Time</th>
                                    <th width="30"></th>
                                </tr>
                            </thead>
                            <tbody id="opening-time-table-body">
                                <?php 
                                if ( ! empty( $branch_opening_time ) ) {
                                    foreach ( $branch_opening_time as $index => $row ) {
                                        ?>
                                        <tr class="admin-table-row">
                                            <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                            <td><input type="text" name="branch_opening_time[<?php echo $index; ?>][day]" value="<?php echo esc_attr( $row['day'] ); ?>" class="widefat"></td>
                                            <td><input type="text" name="branch_opening_time[<?php echo $index; ?>][time]" value="<?php echo esc_attr( $row['time'] ); ?>" class="widefat"></td>
                                            <td><span class="remove-table-row dashicons dashicons-no-alt remove-opening-time-row"></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    // Default 1 row if empty
                                    ?>
                                    <tr class="admin-table-row">
                                        <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                        <td><input type="text" name="branch_opening_time[0][day]" value="" class="widefat"></td>
                                        <td><input type="text" name="branch_opening_time[0][time]" value="" class="widefat"></td>
                                        <td><span class="remove-table-row dashicons dashicons-no-alt remove-opening-time-row"></span></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="text-right" style="margin-top: 10px;">
                            <button type="button" class="button button-primary" id="add-opening-time-row">Add Row</button>
                        </div>
                    </div>
                </div>
                <!-- All Opening Time -->
                <div class="admin-field-row">
                    <label for="branch_all_opening_time"><strong>All Opening Time</strong></label>
                    <input type="text" name="branch_all_opening_time" id="branch_all_opening_time" value="<?php echo esc_attr( $branch_all_opening_time ); ?>" class="widefat">
                </div>
            </div>

            <div class="w-50 pl-30">
                <!-- Car -->
                <div class="admin-field-row">
                    <label for="branch_car"><strong>Car</strong></label>
                    <input type="text" name="branch_car" id="branch_car" value="<?php echo esc_attr( $branch_car ); ?>" class="widefat">
                </div>
                <!-- BTS or MRT -->
                <div class="admin-field-row">
                    <label for="branch_bts_or_mrt"><strong>BTS or MRT</strong></label>
                    <input type="text" name="branch_bts_or_mrt" id="branch_bts_or_mrt" value="<?php echo esc_attr( $branch_bts_or_mrt ); ?>" class="widefat">
                </div>
                <!-- Bus -->
                <div class="admin-field-row">
                    <label for="branch_bus"><strong>Bus</strong></label>
                    <textarea name="branch_bus" id="branch_bus" class="widefat" rows="3"><?php echo esc_textarea( $branch_bus ); ?></textarea>
                </div>
                <!-- Address -->
                <div class="admin-field-row">
                    <label for="branch_address"><strong>Address</strong></label>
                    <input type="text" name="branch_address" id="branch_address" value="<?php echo esc_attr( $branch_address ); ?>" class="widefat">
                </div>
                <!-- Nearby Landmark -->
                <div class="admin-field-row">
                    <label for="branch_nearby_landmark"><strong>Nearby Landmark</strong></label>
                    <textarea name="branch_nearby_landmark" id="branch_nearby_landmark" class="widefat" rows="2"><?php echo esc_textarea( $branch_nearby_landmark ); ?></textarea>
                </div>

                <!-- Services -->
                <div class="admin-field-row">
                    <label><strong>Services</strong></label>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th width="30"></th>
                                    <th>Service Name</th>
                                    <th width="30"></th>
                                </tr>
                            </thead>
                            <tbody id="services-table-body">
                                <?php 
                                if ( ! empty( $branch_services ) ) {
                                    foreach ( $branch_services as $index => $service ) {
                                        ?>
                                        <tr class="admin-table-row">
                                            <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                            <td><input type="text" name="branch_services[<?php echo $index; ?>][name]" value="<?php echo esc_attr( $service['name'] ?? '' ); ?>" class="widefat"></td>
                                            <td><span class="remove-table-row dashicons dashicons-no-alt remove-services-row"></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    // Default 1 row if empty
                                    ?>
                                    <tr class="admin-table-row">
                                        <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                        <td><input type="text" name="branch_services[0][name]" value="" class="widefat"></td>
                                        <td><span class="remove-table-row dashicons dashicons-no-alt remove-services-row"></span></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="text-right" style="margin-top: 10px;">
                            <button type="button" class="button button-primary" id="add-services-row">Add Row</button>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>

    </div>
    <?php
}

// 3. Save Data
function branch_save_meta_data( $post_id ) {
    if ( ! isset( $_POST['branch_meta_nonce'] ) || ! wp_verify_nonce( $_POST['branch_meta_nonce'], 'branch_save_data' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save Thumbnail
    if ( isset( $_POST['branch_thumbnail'] ) ) {
        update_post_meta( $post_id, '_branch_thumbnail', sanitize_text_field( $_POST['branch_thumbnail'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_thumbnail' );
    }

    // Save Thumbnail Name
    if ( isset( $_POST['branch_thumbnail_name'] ) ) {
        update_post_meta( $post_id, '_branch_thumbnail_name', sanitize_text_field( $_POST['branch_thumbnail_name'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_thumbnail_name' );
    }

    // Save Image 360
    if ( isset( $_POST['branch_image_360'] ) ) {
        update_post_meta( $post_id, '_branch_image_360', sanitize_text_field( $_POST['branch_image_360'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_image_360' );
    }

    // Save Location Image
    if ( isset( $_POST['branch_location_image'] ) ) {
        update_post_meta( $post_id, '_branch_location_image', sanitize_text_field( $_POST['branch_location_image'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_location_image' );
    }

    // Save Editors (Allow HTML)
    $editors = array( 'branch_about' );
    foreach ( $editors as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, wp_kses_post( $_POST[ $field ] ) );
        }
    }

    // Save Simple Text Fields
    $text_fields = array(
        'branch_order',
        'branch_title',
        'branch_title_floor',
        'branch_telephone',
        'branch_id_line',
        'branch_url_line',
        'branch_google_map',
        'branch_car',
        'branch_bts_or_mrt',
        'branch_address',
        'branch_all_opening_time'
    );

    foreach ( $text_fields as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, sanitize_text_field( $_POST[ $field ] ) );
        } else {
            delete_post_meta( $post_id, '_' . $field );
        }
    }

    // Save Google Map iframe (Textarea - allow newlines + HTML)
    if ( isset( $_POST['branch_google_map_iframe'] ) ) {
        update_post_meta( $post_id, '_branch_google_map_iframe', stripslashes( $_POST['branch_google_map_iframe'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_google_map_iframe' );
    }

    // Save Bus (Textarea - allow newlines)
    if ( isset( $_POST['branch_bus'] ) ) {
        update_post_meta( $post_id, '_branch_bus', sanitize_textarea_field( $_POST['branch_bus'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_bus' );
    }

    // Save Nearby Landmark (Textarea - allow newlines)
    if ( isset( $_POST['branch_nearby_landmark'] ) ) {
        update_post_meta( $post_id, '_branch_nearby_landmark', sanitize_textarea_field( $_POST['branch_nearby_landmark'] ) );
    } else {
        delete_post_meta( $post_id, '_branch_nearby_landmark' );
    }

    // Save Opening Time
    if ( isset( $_POST['branch_opening_time'] ) && is_array( $_POST['branch_opening_time'] ) ) {
        $opening_time = array();
        foreach ( $_POST['branch_opening_time'] as $row ) {
            if ( ! empty( $row['day'] ) || ! empty( $row['time'] ) ) {
                $opening_time[] = array(
                    'day'   => sanitize_text_field( $row['day'] ),
                    'time'  => sanitize_text_field( $row['time'] ),
                );
            }
        }
        update_post_meta( $post_id, '_branch_opening_time', $opening_time );
    } else {
        delete_post_meta( $post_id, '_branch_opening_time' );
    }

    // Save Services
    if ( isset( $_POST['branch_services'] ) && is_array( $_POST['branch_services'] ) ) {
        $services = array();
        foreach ( $_POST['branch_services'] as $row ) {
            if ( ! empty( $row['name'] ) ) {
                $services[] = array(
                    'name' => sanitize_text_field( $row['name'] ),
                );
            }
        }
        update_post_meta( $post_id, '_branch_services', $services );
    } else {
        delete_post_meta( $post_id, '_branch_services' );
    }
}
add_action( 'save_post', 'branch_save_meta_data' );

// 4. Enqueue Scripts
function branch_admin_enqueue_scripts( $hook ) {
    global $post;
    
    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'page_branch' === $post->post_type ) {
        wp_enqueue_media();
        
        wp_enqueue_style( 
            'branch-admin-css', 
            get_stylesheet_directory_uri() . '/assets/css/admin/admin-metabox.css', 
            array(), 
            filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-metabox.css' ) 
        );

        wp_enqueue_script( 
            'branch-admin-js', 
            get_stylesheet_directory_uri() . '/assets/js/admin/admin-branch-single.js', 
            array( 'jquery', 'jquery-ui-sortable' ), 
            filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-branch-single.js' ), 
            true 
        );
    }
}
add_action( 'admin_enqueue_scripts', 'branch_admin_enqueue_scripts' );