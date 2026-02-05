<?php
/**
 * Meta Boxes for Page Case Review
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Add Meta Box
function case_review_add_meta_boxes() {
    add_meta_box(
        'case_review_details_meta_box',
        'Case Review Information',
        'case_review_render_meta_box',
        'page_case_review',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'case_review_add_meta_boxes' );

// 2. Render Meta Box
function case_review_render_meta_box( $post ) {
    wp_nonce_field( 'case_review_save_data', 'case_review_meta_nonce' );

    // Get Values
    $case_review_thumbnail = get_post_meta( $post->ID, '_case_review_thumbnail', true );
    $case_review_id_video = get_post_meta( $post->ID, '_case_review_id_video', true );
    $case_review_image_before_after = get_post_meta( $post->ID, '_case_review_image_before_after', true );
    $case_review_image_before = get_post_meta( $post->ID, '_case_review_image_before', true );
    $case_review_image_after = get_post_meta( $post->ID, '_case_review_image_after', true );

    // Get Procedures Data
    $case_review_procedures = get_post_meta( $post->ID, '_case_review_procedures', true );
    if ( ! is_array( $case_review_procedures ) ) {
        $case_review_procedures = array();
    }

    ?>
    <div class="admin-meta-wrapper">
        
        <div class="admin-field-row-wrapper">
            <div class="w-50 pr-30">
                <!-- Thumbnail Video -->
                <div class="admin-field-row">
                    <label><strong>Thumbnail Video</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="case_review_thumbnail" id="case_review_thumbnail" value="<?php echo esc_attr( $case_review_thumbnail ); ?>">
                        <div class="admin-image-preview thumbnail-image-preview">
                            <?php 
                            if ( ! empty( $case_review_thumbnail ) ) {
                                $url = wp_get_attachment_image_url( $case_review_thumbnail, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $case_review_thumbnail ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 200px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt thumbnail-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="case_review_add_thumbnail"><?php echo empty($case_review_thumbnail) ? 'Add Thumbnail' : 'Change Thumbnail'; ?></button>
                    </div>
                </div>
                <!-- ID Video -->
                <div class="admin-field-row">
                    <label for="case_review_id_video"><strong>ID Video</strong></label>
                    <input type="text" name="case_review_id_video" id="case_review_id_video" value="<?php echo esc_attr( $case_review_id_video ); ?>" class="widefat">
                </div>
            </div>

            <div class="w-50 pl-30">
                <!-- Image Before & After -->
                <div class="admin-field-row">
                    <label><strong>Image Before & After</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="case_review_image_before_after" id="case_review_image_before_after" value="<?php echo esc_attr( $case_review_image_before_after ); ?>">
                        <div class="admin-image-preview image-before-after-image-preview">
                            <?php 
                            if ( ! empty( $case_review_image_before_after ) ) {
                                $url = wp_get_attachment_image_url( $case_review_image_before_after, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $case_review_image_before_after ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 200px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt image-before-after-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="case_review_add_image_before_after"><?php echo empty($case_review_image_before_after) ? 'Add Image' : 'Change Image'; ?></button>
                    </div>
                </div>
                <!-- List of Procedures -->
                <div class="admin-field-row">
                    <label><strong>List of Procedures</strong></label>
                    <div class="admin-table-container">
                        <table class="admin-table">
                            <thead>
                                <tr>
                                    <th width="30"></th>
                                    <th>Procedures</th>
                                    <th>Quantity</th>
                                    <th width="30"></th>
                                </tr>
                            </thead>
                            <tbody id="procedures-table-body">
                                <?php 
                                if ( ! empty( $case_review_procedures ) ) {
                                    foreach ( $case_review_procedures as $index => $row ) {
                                        ?>
                                        <tr class="admin-table-row">
                                            <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                            <td><input type="text" name="case_review_procedures[<?php echo $index; ?>][procedures]" value="<?php echo esc_attr( $row['procedures'] ); ?>" class="widefat"></td>
                                            <td><input type="text" name="case_review_procedures[<?php echo $index; ?>][quantity]" value="<?php echo esc_attr( $row['quantity'] ); ?>" class="widefat"></td>
                                            <td><span class="remove-table-row dashicons dashicons-no-alt remove-procedures-row"></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    // Default 1 row if empty
                                    ?>
                                    <tr class="admin-table-row">
                                        <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                        <td><input type="text" name="case_review_procedures[0][procedures]" value="" class="widefat"></td>
                                        <td><input type="text" name="case_review_procedures[0][quantity]" value="" class="widefat"></td>
                                        <td><span class="remove-table-row dashicons dashicons-no-alt remove-procedures-row"></span></td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                        <div class="text-right" style="margin-top: 10px;">
                            <button type="button" class="button button-primary" id="add-procedures-row">Add Row</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <hr>

        <div class="admin-field-row-wrapper">
            <div class="w-50 pr-30">
                <!-- Image Before -->
                <div class="admin-field-row">
                    <label><strong>Image Before</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="case_review_image_before" id="case_review_image_before" value="<?php echo esc_attr( $case_review_image_before ); ?>">
                        <div class="admin-image-preview image-before-image-preview">
                            <?php 
                            if ( ! empty( $case_review_image_before ) ) {
                                $url = wp_get_attachment_image_url( $case_review_image_before, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $case_review_image_before ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 300px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt image-before-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="case_review_add_image_before"><?php echo empty($case_review_image_before) ? 'Add Image' : 'Change Image'; ?></button>
                    </div>
                </div>
            </div>
            <div class="w-50 pl-30">
                <!-- Image After -->
                <div class="admin-field-row">
                    <label><strong>Image After</strong></label>
                    <div class="admin-image-container">
                        <input type="hidden" name="case_review_image_after" id="case_review_image_after" value="<?php echo esc_attr( $case_review_image_after ); ?>">
                        <div class="admin-image-preview image-after-image-preview">
                            <?php 
                            if ( ! empty( $case_review_image_after ) ) {
                                $url = wp_get_attachment_image_url( $case_review_image_after, 'full' );
                                if ( $url ) {
                                    echo '<div class="admin-image-item" data-id="' . esc_attr( $case_review_image_after ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 300px; width: auto;">';
                                    echo '<span class="admin-remove-image dashicons dashicons-no-alt image-after-remove-image"></span>';
                                    echo '</div>';
                                }
                            }
                            ?>
                        </div>
                        <button type="button" class="button" id="case_review_add_image_after"><?php echo empty($case_review_image_after) ? 'Add Image' : 'Change Image'; ?></button>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <?php
}

// 3. Save Data
function case_review_save_meta_data( $post_id ) {
    if ( ! isset( $_POST['case_review_meta_nonce'] ) || ! wp_verify_nonce( $_POST['case_review_meta_nonce'], 'case_review_save_data' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save Thumbnail Video
    if ( isset( $_POST['case_review_thumbnail'] ) ) {
        update_post_meta( $post_id, '_case_review_thumbnail', sanitize_text_field( $_POST['case_review_thumbnail'] ) );
    } else {
        delete_post_meta( $post_id, '_case_review_thumbnail' );
    }

    // Save ID Video
    if ( isset( $_POST['case_review_id_video'] ) ) {
        update_post_meta( $post_id, '_case_review_id_video', sanitize_text_field( $_POST['case_review_id_video'] ) );
    } else {
        delete_post_meta( $post_id, '_case_review_id_video' );
    }

    // Save Image Before & After
    if ( isset( $_POST['case_review_image_before_after'] ) ) {
        update_post_meta( $post_id, '_case_review_image_before_after', sanitize_text_field( $_POST['case_review_image_before_after'] ) );
    } else {
        delete_post_meta( $post_id, '_case_review_image_before_after' );
    }

    // Save Procedures Data
    if ( isset( $_POST['case_review_procedures'] ) && is_array( $_POST['case_review_procedures'] ) ) {
        $procedures = array();
        foreach ( $_POST['case_review_procedures'] as $row ) {
            if ( ! empty( $row['procedures'] ) || ! empty( $row['quantity'] ) ) {
                $procedures[] = array(
                    'procedures' => sanitize_text_field( $row['procedures'] ),
                    'quantity' => sanitize_text_field( $row['quantity'] ),
                );
            }
        }
        update_post_meta( $post_id, '_case_review_procedures', $procedures );
    } else {
        delete_post_meta( $post_id, '_case_review_procedures' );
    }

    // Save Image Before
    if ( isset( $_POST['case_review_image_before'] ) ) {
        update_post_meta( $post_id, '_case_review_image_before', sanitize_text_field( $_POST['case_review_image_before'] ) );
    } else {
        delete_post_meta( $post_id, '_case_review_image_before' );
    }

    // Save Image After
    if ( isset( $_POST['case_review_image_after'] ) ) {
        update_post_meta( $post_id, '_case_review_image_after', sanitize_text_field( $_POST['case_review_image_after'] ) );
    } else {
        delete_post_meta( $post_id, '_case_review_image_after' );
    }
}
add_action( 'save_post', 'case_review_save_meta_data' );

// 4. Enqueue Scripts
function case_review_admin_enqueue_scripts( $hook ) {
    global $post;
    
    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'page_case_review' === $post->post_type ) {
        wp_enqueue_media();
        
        wp_enqueue_style( 
            'case-review-admin-css', 
            get_stylesheet_directory_uri() . '/assets/css/admin/admin-metabox.css', 
            array(), 
            filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-metabox.css' ) 
        );

        wp_enqueue_script( 
            'case-review-admin-js', 
            get_stylesheet_directory_uri() . '/assets/js/admin/admin-case-review-single.js', 
            array( 'jquery', 'jquery-ui-sortable' ), 
            filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-case-review-single.js' ), 
            true 
        );
    }
}
add_action( 'admin_enqueue_scripts', 'case_review_admin_enqueue_scripts' );
