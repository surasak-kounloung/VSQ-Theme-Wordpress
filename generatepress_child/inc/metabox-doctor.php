<?php
/**
 * Meta Boxes for Page Doctor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Add Meta Box
function doctor_add_meta_boxes() {
    add_meta_box(
        'doctor_details_meta_box',
        'Doctor Information',
        'doctor_render_meta_box',
        'page_doctor',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes', 'doctor_add_meta_boxes' );

// 2. Render Meta Box
function doctor_render_meta_box( $post ) {
    wp_nonce_field( 'doctor_save_data', 'doctor_meta_nonce' );

    // Get Values
    $doctor_thumbnail = get_post_meta( $post->ID, '_doctor_thumbnail', true );
    $doctor_thumbnail_name = get_post_meta( $post->ID, '_doctor_thumbnail_name', true );
    $doctor_image = get_post_meta( $post->ID, '_doctor_image', true );
    $nickname = get_post_meta( $post->ID, '_doctor_nickname', true );
    $fullname_th = get_post_meta( $post->ID, '_doctor_fullname_th', true );
    $fullname_en = get_post_meta( $post->ID, '_doctor_fullname_en', true );
    $medical_license_no = get_post_meta( $post->ID, '_doctor_medical_license_no', true );
    $education = get_post_meta( $post->ID, '_doctor_education', true );
    $experience = get_post_meta( $post->ID, '_doctor_experience', true );
    $specialty = get_post_meta( $post->ID, '_doctor_specialty', true );
    $certificates = get_post_meta( $post->ID, '_doctor_certificates', true );
    $certificate_gallery = get_post_meta( $post->ID, '_doctor_certificate_gallery', true );
    $training_gallery = get_post_meta( $post->ID, '_doctor_training_gallery', true );
    $case_reviews = get_post_meta( $post->ID, '_doctor_case_reviews', true );
    
    // Get Schedule Data
    $schedule = get_post_meta( $post->ID, '_doctor_schedule', true );
    if ( ! is_array( $schedule ) ) {
        $schedule = array();
    }

    ?>
    <div class="doc-meta-wrapper">

        <div class="doc-field-row-wrapper">
            <!-- Doctor Thumbnail -->
            <div class="doc-field-row w-50">
                <label><strong>Doctor Thumbnail</strong></label>
                <div class="doc-image-container">
                    <input type="hidden" name="doctor_thumbnail" id="doctor_thumbnail" value="<?php echo esc_attr( $doctor_thumbnail ); ?>">
                    <div class="doc-thumbnail-preview">
                        <?php 
                        if ( ! empty( $doctor_thumbnail ) ) {
                            $url = wp_get_attachment_image_url( $doctor_thumbnail, 'full' );
                            if ( $url ) {
                                echo '<div class="doc-image-item" data-id="' . esc_attr( $doctor_thumbnail ) . '">';
                                echo '<img src="' . esc_url( $url ) . '" style="max-height: 250px; width: auto;">';
                                echo '<span class="doc-remove-thumbnail dashicons dashicons-no-alt"></span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button" id="doctor_add_thumbnail"><?php echo empty($doctor_thumbnail) ? 'Add Thumbnail' : 'Change Thumbnail'; ?></button>
                </div>
            </div>

            <!-- Doctor Thumbnail + Name-->
            <div class="doc-field-row w-50">
                <label><strong>Doctor Thumbnail + Name</strong></label>
                <div class="doc-image-container">
                    <input type="hidden" name="doctor_thumbnail_name" id="doctor_thumbnail_name" value="<?php echo esc_attr( $doctor_thumbnail_name ); ?>">
                    <div class="doc-thumbnail-name-preview">
                        <?php 
                        if ( ! empty( $doctor_thumbnail_name ) ) {
                            $url = wp_get_attachment_image_url( $doctor_thumbnail_name, 'full' );
                            if ( $url ) {
                                echo '<div class="doc-image-item" data-id="' . esc_attr( $doctor_thumbnail_name ) . '">';
                                echo '<img src="' . esc_url( $url ) . '" style="max-height: 250px; width: auto;">';
                                echo '<span class="doc-remove-thumbnail-name dashicons dashicons-no-alt"></span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button" id="doctor_add_thumbnail_name"><?php echo empty($doctor_thumbnail_name) ? 'Add Thumbnail' : 'Change Thumbnail'; ?></button>
                </div>
            </div>
        </div>
        <hr>
        
        <div class="doc-field-row-wrapper">
            <!-- Doctor Image -->
            <div class="doc-field-row w-50">
                <label><strong>Doctor Image</strong></label>
                <div class="doc-image-container">
                    <input type="hidden" name="doctor_image" id="doctor_image" value="<?php echo esc_attr( $doctor_image ); ?>">
                    <div class="doc-image-preview">
                        <?php 
                        if ( ! empty( $doctor_image ) ) {
                            $url = wp_get_attachment_image_url( $doctor_image, 'full' );
                            if ( $url ) {
                                echo '<div class="doc-image-item" data-id="' . esc_attr( $doctor_image ) . '">';
                                echo '<img src="' . esc_url( $url ) . '" style="max-height: 250px; width: auto;">';
                                echo '<span class="doc-remove-image dashicons dashicons-no-alt"></span>';
                                echo '</div>';
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button" id="doctor_add_image"><?php echo empty($doctor_image) ? 'Add Image' : 'Change Image'; ?></button>
                </div>
            </div>

            <div class="w-50">
                <!-- Nickname -->
                <div class="doc-field-row">
                    <label for="doctor_nickname"><strong>Nickname</strong></label>
                    <input type="text" name="doctor_nickname" id="doctor_nickname" value="<?php echo esc_attr( $nickname ); ?>" class="widefat">
                </div>
                <!-- Full Name TH -->
                <div class="doc-field-row">
                    <label for="doctor_fullname_th"><strong>Full Name TH</strong></label>
                    <input type="text" name="doctor_fullname_th" id="doctor_fullname_th" value="<?php echo esc_attr( $fullname_th ); ?>" class="widefat">
                </div>
                <!-- Full Name EN -->
                <div class="doc-field-row">
                    <label for="doctor_fullname_en"><strong>Full Name EN</strong></label>
                    <input type="text" name="doctor_fullname_en" id="doctor_fullname_en" value="<?php echo esc_attr( $fullname_en ); ?>" class="widefat">
                </div>
                <!-- Medical License No. -->
                <div class="doc-field-row">
                    <label for="doctor_medical_license_no"><strong>Medical License No.</strong></label>
                    <input type="text" name="doctor_medical_license_no" id="doctor_medical_license_no" value="<?php echo esc_attr( $medical_license_no ); ?>" class="widefat">
                </div>
            </div>
        </div>
        <hr>

        <!-- Education -->
        <div class="doc-field-row">
            <label><strong>Education</strong></label>
            <div class="doc-editor-wrapper">
            <?php 
            wp_editor( $education, 'doctor_education', array(
                'textarea_name' => 'doctor_education',
                'media_buttons' => false,
                'textarea_rows' => 5,
                'teeny' => true,
                'quicktags' => true,
                'drag_drop_upload' => false,
                'tinymce' => array(
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
        <hr>

        <!-- Experience -->
        <div class="doc-field-row">
            <label><strong>Experience</strong></label>
            <div class="doc-editor-wrapper">
            <?php 
            wp_editor( $experience, 'doctor_experience', array(
                'textarea_name' => 'doctor_experience',
                'media_buttons' => false,
                'textarea_rows' => 5,
                'teeny' => true,
                'quicktags' => true,
                'drag_drop_upload' => false,
                'tinymce' => array(
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
        <hr>

        <!-- Specialty -->
        <div class="doc-field-row">
            <label><strong>Specialty</strong></label>
            <div class="doc-editor-wrapper">
            <?php 
            wp_editor( $specialty, 'doctor_specialty', array(
                'textarea_name' => 'doctor_specialty',
                'media_buttons' => false,
                'textarea_rows' => 5,
                'teeny' => true,
                'quicktags' => true,
                'drag_drop_upload' => false,
                'tinymce' => array(
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
        <hr>

        <!-- Certificate Training -->
        <div class="doc-field-row">
            <label><strong>Certificate</strong></label>
            <div class="doc-editor-wrapper">
            <?php 
            wp_editor( $certificates, 'doctor_certificates', array(
                'textarea_name' => 'doctor_certificates',
                'media_buttons' => false,
                'textarea_rows' => 5,
                'teeny' => true,
                'quicktags' => true,
                'drag_drop_upload' => false,
                'tinymce' => array(
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
        <hr>

        <div class="doc-field-row-wrapper">
            <!-- Certificate Gallery -->
            <div class="doc-field-row w-50 pr-15">
                <label><strong>Certificate Gallery</strong></label>
                <div class="doc-certs-container">
                    <input type="hidden" name="doctor_certificate_gallery" id="doctor_certificate_gallery" value="<?php echo esc_attr( $certificate_gallery ); ?>">
                    <div class="doc-certs-preview">
                        <?php 
                        if ( ! empty( $certificate_gallery ) ) {
                            $ids = explode( ',', $certificate_gallery );
                            foreach ( $ids as $id ) {
                                $url = wp_get_attachment_image_url( $id, 'full' );
                                if ( $url ) {
                                    echo '<div class="doc-cert-item" data-id="' . esc_attr( $id ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 150px; width: auto;">';
                                    echo '<span class="doc-remove-cert dashicons dashicons-no-alt"></span>';
                                    echo '</div>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button" id="doctor_add_certificate">Add Certificates</button>
                </div>
            </div>
            <!-- Training Gallery -->
            <div class="doc-field-row w-50 pl-15">
                <label><strong>Training Gallery</strong></label>
                <div class="doc-training-container">
                    <input type="hidden" name="doctor_training_gallery" id="doctor_training_gallery" value="<?php echo esc_attr( $training_gallery ); ?>">
                    <div class="doc-training-preview">
                        <?php 
                        if ( ! empty( $training_gallery ) ) {
                            $ids = explode( ',', $training_gallery );
                            foreach ( $ids as $id ) {
                                $url = wp_get_attachment_image_url( $id, 'full' );
                                if ( $url ) {
                                    echo '<div class="doc-training-item" data-id="' . esc_attr( $id ) . '">';
                                    echo '<img src="' . esc_url( $url ) . '" style="max-height: 150px; width: auto;">';
                                    echo '<span class="doc-remove-training dashicons dashicons-no-alt"></span>';
                                    echo '</div>';
                                }
                            }
                        }
                        ?>
                    </div>
                    <button type="button" class="button" id="doctor_add_training">Add Training</button>
                </div>
            </div>
        </div>
        <hr>

        <!-- Schedule Table -->
        <div class="doc-field-row">
            <label><strong>Schedule table</strong></label>
            <div class="doc-schedule-container">
                <table class="doc-schedule-table">
                    <thead>
                        <tr>
                            <th width="30"></th>
                            <th>Schedule date</th>
                            <th>Schedule branch</th>
                            <th width="30"></th>
                        </tr>
                    </thead>
                    <tbody id="doc-schedule-body">
                        <?php 
                        if ( ! empty( $schedule ) ) {
                            foreach ( $schedule as $index => $row ) {
                                ?>
                                <tr class="doc-schedule-row">
                                    <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                    <td><input type="text" name="doctor_schedule[<?php echo $index; ?>][date]" value="<?php echo esc_attr( $row['date'] ); ?>" class="widefat"></td>
                                    <td><input type="text" name="doctor_schedule[<?php echo $index; ?>][branch]" value="<?php echo esc_attr( $row['branch'] ); ?>" class="widefat"></td>
                                    <td><span class="remove-schedule-row dashicons dashicons-no-alt"></span></td>
                                </tr>
                                <?php
                            }
                        } else {
                            // Default 1 row if empty
                            ?>
                            <tr class="doc-schedule-row">
                                <td class="row-index" style="cursor: move;"><span class="dashicons dashicons-menu" style="color: #ccc;"></span></td>
                                <td><input type="text" name="doctor_schedule[0][date]" value="" class="widefat"></td>
                                <td><input type="text" name="doctor_schedule[0][branch]" value="" class="widefat"></td>
                                <td><span class="remove-schedule-row dashicons dashicons-no-alt"></span></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <div class="text-right" style="margin-top: 10px;">
                    <button type="button" class="button button-primary" id="add-schedule-row">Add Row</button>
                </div>
            </div>
        </div>
        <hr>

        <!-- Case Reviews -->
        <div class="doc-field-row">
            <label><strong>Case Reviews</strong></label>
            <div class="doc-case-review-container">
                <!-- Hidden input to store selected IDs -->
                <input type="hidden" name="doctor_case_reviews" id="doctor_case_reviews" value="<?php echo esc_attr( $case_reviews ); ?>">
                
                <!-- Search Input -->
                <div class="doc-case-review-search" style="position: relative;">
                    <input type="text" id="doctor_search_case_review" class="widefat" placeholder="Search Case Reviews by Title..." autocomplete="off">
                    <div id="doctor_case_review_search_results" class="doc-search-results" style="display:none; position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid #ddd; max-height: 200px; overflow-y: auto; z-index: 999;"></div>
                </div>

                <!-- Selected Case Reviews List -->
                <div class="doc-case-review-selected" style="margin-top: 10px;">
                    <?php 
                    if ( ! empty( $case_reviews ) ) {
                        $ids = explode( ',', $case_reviews );
                        foreach ( $ids as $id ) {
                            $post_title = get_the_title( $id );
                            if ( $post_title ) {
                                echo '<div class="doc-case-review-item" data-id="' . esc_attr( $id ) . '" style="display: flex; align-items: center; padding: 5px; background: #f0f0f0; margin-bottom: 5px; border: 1px solid #ddd;">';
                                echo '<span class="dashicons dashicons-move" style="cursor: move; margin-right: 5px; color: #ccc;"></span>';
                                echo '<span class="doc-case-review-title" style="flex-grow: 1;">' . esc_html( $post_title ) . '</span>';
                                echo '<span class="doc-remove-case-review dashicons dashicons-no-alt" style="cursor: pointer; color: #d63638;"></span>';
                                echo '</div>';
                            }
                        }
                    }
                    ?>
                </div>
            </div>
        </div>

    </div>
    <?php
}

// 3. Save Data
function doctor_save_meta_data( $post_id ) {
    if ( ! isset( $_POST['doctor_meta_nonce'] ) || ! wp_verify_nonce( $_POST['doctor_meta_nonce'], 'doctor_save_data' ) ) {
        return;
    }

    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Save Doctor Thumbnail
    if ( isset( $_POST['doctor_thumbnail'] ) ) {
        update_post_meta( $post_id, '_doctor_thumbnail', sanitize_text_field( $_POST['doctor_thumbnail'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_thumbnail' );
    }

    // Save Doctor Thumbnail Name
    if ( isset( $_POST['doctor_thumbnail_name'] ) ) {
        update_post_meta( $post_id, '_doctor_thumbnail_name', sanitize_text_field( $_POST['doctor_thumbnail_name'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_thumbnail_name' );
    }

    // Save Doctor Image
    if ( isset( $_POST['doctor_image'] ) ) {
        update_post_meta( $post_id, '_doctor_image', sanitize_text_field( $_POST['doctor_image'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_image' );
    }

    // Save Nickname
    if ( isset( $_POST['doctor_nickname'] ) ) {
        update_post_meta( $post_id, '_doctor_nickname', sanitize_text_field( $_POST['doctor_nickname'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_nickname' );
    }

    // Save Full Name TH
    if ( isset( $_POST['doctor_fullname_th'] ) ) {
        update_post_meta( $post_id, '_doctor_fullname_th', sanitize_text_field( $_POST['doctor_fullname_th'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_fullname_th' );
    }

    // Save Full Name EN
    if ( isset( $_POST['doctor_fullname_en'] ) ) {
        update_post_meta( $post_id, '_doctor_fullname_en', sanitize_text_field( $_POST['doctor_fullname_en'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_fullname_en' );
    }

    // Save Medical License No.
    if ( isset( $_POST['doctor_medical_license_no'] ) ) {
        update_post_meta( $post_id, '_doctor_medical_license_no', sanitize_text_field( $_POST['doctor_medical_license_no'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_medical_license_no' );
    }

    // Save Editors (Allow HTML)
    $editors = array( 'doctor_education', 'doctor_experience', 'doctor_specialty', 'doctor_certificates' );
    foreach ( $editors as $field ) {
        if ( isset( $_POST[ $field ] ) ) {
            update_post_meta( $post_id, '_' . $field, wp_kses_post( $_POST[ $field ] ) );
        }
    }

    // Save Certificate Gallery
    if ( isset( $_POST['doctor_certificate_gallery'] ) ) {
        update_post_meta( $post_id, '_doctor_certificate_gallery', sanitize_text_field( $_POST['doctor_certificate_gallery'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_certificate_gallery' );
    }

    // Save Training Gallery
    if ( isset( $_POST['doctor_training_gallery'] ) ) {
        update_post_meta( $post_id, '_doctor_training_gallery', sanitize_text_field( $_POST['doctor_training_gallery'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_training_gallery' );
    }

    // Save Case Reviews
    if ( isset( $_POST['doctor_case_reviews'] ) ) {
        update_post_meta( $post_id, '_doctor_case_reviews', sanitize_text_field( $_POST['doctor_case_reviews'] ) );
    } else {
        delete_post_meta( $post_id, '_doctor_case_reviews' );
    }

    // Save Schedule Data
    if ( isset( $_POST['doctor_schedule'] ) && is_array( $_POST['doctor_schedule'] ) ) {
        $schedule = array();
        foreach ( $_POST['doctor_schedule'] as $row ) {
            if ( ! empty( $row['date'] ) || ! empty( $row['branch'] ) ) {
                $schedule[] = array(
                    'date'   => sanitize_text_field( $row['date'] ),
                    'branch' => sanitize_text_field( $row['branch'] ),
                );
            }
        }
        update_post_meta( $post_id, '_doctor_schedule', $schedule );
    } else {
        delete_post_meta( $post_id, '_doctor_schedule' );
    }
}
add_action( 'save_post', 'doctor_save_meta_data' );

// 4. Enqueue Scripts
function doctor_admin_enqueue_scripts( $hook ) {
    global $post;
    
    if ( ( $hook == 'post-new.php' || $hook == 'post.php' ) && 'page_doctor' === $post->post_type ) {
        wp_enqueue_media();
        
        wp_enqueue_style( 
            'doctor-admin-css', 
            get_stylesheet_directory_uri() . '/assets/css/admin/admin-doctor-single.css', 
            array(), 
            filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-doctor-single.css' ) 
        );

        wp_enqueue_script( 
            'doctor-admin-js', 
            get_stylesheet_directory_uri() . '/assets/js/admin/admin-doctor-single.js', 
            array( 'jquery', 'jquery-ui-sortable' ), 
            filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-doctor-single.js' ),
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'doctor_admin_enqueue_scripts' );


// 5. AJAX Search Case Reviews
function doctor_ajax_search_case_reviews() {
    $search = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';
    
    if ( empty( $search ) ) {
        wp_send_json_error();
    }

    $args = array(
        'post_type'      => 'page_case_review',
        'post_status'    => 'publish',
        's'              => $search,
        'posts_per_page' => 20,
        'fields'         => 'ids' // Only get IDs to be faster, then get title
    );

    $query = new WP_Query( $args );
    $results = array();

    if ( $query->have_posts() ) {
        foreach ( $query->posts as $post_id ) {
            $results[] = array(
                'id'    => $post_id,
                'title' => get_the_title( $post_id )
            );
        }
    }

    wp_send_json_success( $results );
}
add_action( 'wp_ajax_doctor_search_case_reviews', 'doctor_ajax_search_case_reviews' );


// 6. Shortcode for displaying all doctors [doctors_list]
function doctor_list_shortcode( $atts ) {
    $atts = shortcode_atts( array(
        'columns' => 4, 
    ), $atts );
    
    $args = array(
        'post_type'      => 'page_doctor',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    );
    
    $query = new WP_Query( $args );
    
    if ( ! $query->have_posts() ) {
        return '<p>ไม่พบข้อมูลแพทย์</p>';
    }
    
    ob_start();
    ?>
    <div class="doctor-list-container">
        <?php while ( $query->have_posts() ) : $query->the_post(); 
            $post_id = get_the_ID();
            
            // Get Meta Data
            $image_id = get_post_meta( $post_id, '_doctor_image', true );
            $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : ''; 
            
            $fullname_th = get_post_meta( $post_id, '_doctor_fullname_th', true );
            $nickname = get_post_meta( $post_id, '_doctor_nickname', true );
            $specialty = get_post_meta( $post_id, '_doctor_specialty', true );
            
            $permalink = get_permalink();
            
            // Fallback for name
            if ( empty( $fullname_th ) ) {
                $fullname_th = get_the_title();
            }
        ?>
            <div class="doctor-item">
                <a href="<?php echo esc_url( $permalink ); ?>" class="doctor-card-link">
                    <div class="doctor-card">
                        <div class="doctor-card-image">
                            <?php if ( $image_url ) : ?>
                                <img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $fullname_th ); ?>">
                            <?php else : ?>
                                <div class="no-image">No Image</div>
                            <?php endif; ?>
                        </div>
                        <div class="doctor-card-content">
                            <h3 class="doctor-card-name"><?php echo esc_html( $fullname_th ); ?></h3>
                            <?php if ( $nickname ) : ?>
                                <p class="doctor-card-nickname">(<?php echo esc_html( $nickname ); ?>)</p>
                            <?php endif; ?>
                            <?php if ( $specialty ) : ?>
                                <div class="doctor-card-specialty"><?php echo wp_kses_post( $specialty ); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>

    <style>
        .doctor-list-container {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -15px;
        }
        .doctor-item {
            width: 25%; /* 4 Columns */
            padding: 15px;
            box-sizing: border-box;
        }
        .doctor-card {
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            height: 100%;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }
        .doctor-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .doctor-card-link {
            text-decoration: none;
            color: inherit;
        }
        .doctor-card-image {
            height: 300px;
            overflow: hidden;
            background: #f9f9f9;
            position: relative;
        }
        .doctor-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .doctor-card:hover .doctor-card-image img {
            transform: scale(1.05);
        }
        .doctor-card-image .no-image {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #ccc;
        }
        .doctor-card-content {
            padding: 20px;
            text-align: center;
        }
        .doctor-card-name {
            margin: 0 0 5px;
            font-size: 1.2em;
            color: #333;
            line-height: 1.3;
        }
        .doctor-card-nickname {
            margin: 0 0 10px;
            font-size: 1em;
            color: #777;
        }
        .doctor-card-specialty {
            font-size: 0.9em;
            color: #d4af37;
            border-top: 1px solid #eee;
            padding-top: 10px;
            margin-top: 10px;
        }
        
        /* Responsive */
        @media (max-width: 992px) {
            .doctor-item { width: 33.33%; }
        }
        @media (max-width: 768px) {
            .doctor-item { width: 50%; }
            .doctor-card-image { height: 250px; }
        }
        @media (max-width: 480px) {
            .doctor-item { width: 100%; }
        }
    </style>
    <?php
    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode( 'doctors_list', 'doctor_list_shortcode' );