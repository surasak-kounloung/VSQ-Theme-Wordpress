<?php
/**
 * VSQ Sync System
 * 
 * Handles synchronization of Options and Post/Metabox data between a Central Site and Client Sites.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Constants
define( 'VSQ_SYNC_OPTION_KEY', 'vsq_sync_settings' );
define( 'VSQ_SYNC_ENDPOINT_ROUTE', 'vsq-sync/v1/update' );

/**
 * 1. Admin Menu & Settings
 */
function vsq_sync_add_admin_menu() {
    add_menu_page(
        'VSQ Sync System',
        'VSQ Sync',
        'manage_options',
        'vsq-sync-settings',
        'vsq_sync_options_page_html',
        'dashicons-cloud',
        99
    );
}
add_action( 'admin_menu', 'vsq_sync_add_admin_menu' );

function vsq_sync_settings_init() {
    register_setting( 'vsq_sync_group', VSQ_SYNC_OPTION_KEY );
}
add_action( 'admin_init', 'vsq_sync_settings_init' );

function vsq_sync_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) return;

    $settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
    $role = isset( $settings['role'] ) ? $settings['role'] : 'none'; // none, sender, receiver
    $secret_key = isset( $settings['secret_key'] ) ? $settings['secret_key'] : wp_generate_password( 32, false );
    $client_sites = isset( $settings['client_sites'] ) ? $settings['client_sites'] : array();

    // Ensure we have at least one empty row for UI
    if ( empty( $client_sites ) ) {
        $client_sites[] = array('url' => '', 'key' => '');
    }
    ?>
    <div class="wrap">
        <h1>VSQ Sync System</h1>
        <form action="options.php" method="post">
            <?php settings_fields( 'vsq_sync_group' ); ?>
            
            <table class="form-table">
                <!-- Role Selection -->
                <tr valign="top">
                    <th scope="row">System Role</th>
                    <td>
                        <select name="<?php echo VSQ_SYNC_OPTION_KEY; ?>[role]" id="vsq_sync_role">
                            <option value="none" <?php selected( $role, 'none' ); ?>>Disable</option>
                            <option value="sender" <?php selected( $role, 'sender' ); ?>>Central Site (Sender)</option>
                            <option value="receiver" <?php selected( $role, 'receiver' ); ?>>Client Site (Receiver)</option>
                        </select>
                        <p class="description">Select 'Central Site' to send data, or 'Client Site' to receive data.</p>
                    </td>
                </tr>

                <!-- Receiver Settings -->
                <tr valign="top" class="vsq-receiver-field" style="<?php echo $role !== 'receiver' ? 'display:none;' : ''; ?>">
                    <th scope="row">Secret Key (Copy this to Central Site)</th>
                    <td>
                        <input type="text" name="<?php echo VSQ_SYNC_OPTION_KEY; ?>[secret_key]" value="<?php echo esc_attr( $secret_key ); ?>" class="regular-text" readonly>
                        <p class="description">This key validates incoming requests.</p>
                    </td>
                </tr>

                <!-- Sender Settings -->
                <tr valign="top" class="vsq-sender-field" style="<?php echo $role !== 'sender' ? 'display:none;' : ''; ?>">
                    <th scope="row">Client Sites</th>
                    <td>
                        <div id="vsq-clients-wrapper">
                            <?php foreach ( $client_sites as $index => $site ) : ?>
                                <div class="vsq-client-row" style="margin-bottom: 10px; display: flex; gap: 10px;">
                                    <input type="url" name="<?php echo VSQ_SYNC_OPTION_KEY; ?>[client_sites][<?php echo $index; ?>][url]" value="<?php echo esc_attr( $site['url'] ); ?>" placeholder="https://client-site.com" class="regular-text">
                                    <input type="text" name="<?php echo VSQ_SYNC_OPTION_KEY; ?>[client_sites][<?php echo $index; ?>][key]" value="<?php echo esc_attr( $site['key'] ); ?>" placeholder="Secret Key from Client" class="regular-text">
                                    <button type="button" class="button vsq-remove-row">Remove</button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="button" id="vsq-add-row">Add Site</button>
                    </td>
                </tr>
            </table>

            <?php submit_button(); ?>
        </form>

        <script>
            jQuery(document).ready(function($) {
                // Toggle Fields based on Role
                $('#vsq_sync_role').change(function() {
                    var role = $(this).val();
                    if (role === 'sender') {
                        $('.vsq-sender-field').show();
                        $('.vsq-receiver-field').hide();
                    } else if (role === 'receiver') {
                        $('.vsq-sender-field').hide();
                        $('.vsq-receiver-field').show();
                    } else {
                        $('.vsq-sender-field').hide();
                        $('.vsq-receiver-field').hide();
                    }
                });

                // Repeater for Client Sites
                $('#vsq-add-row').click(function() {
                    var count = $('.vsq-client-row').length;
                    var template = `
                        <div class="vsq-client-row" style="margin-bottom: 10px; display: flex; gap: 10px;">
                            <input type="url" name="<?php echo VSQ_SYNC_OPTION_KEY; ?>[client_sites][${count}][url]" placeholder="https://client-site.com" class="regular-text">
                            <input type="text" name="<?php echo VSQ_SYNC_OPTION_KEY; ?>[client_sites][${count}][key]" placeholder="Secret Key from Client" class="regular-text">
                            <button type="button" class="button vsq-remove-row">Remove</button>
                        </div>
                    `;
                    $('#vsq-clients-wrapper').append(template);
                });

                $(document).on('click', '.vsq-remove-row', function() {
                    $(this).parent().remove();
                });
            });
        </script>
    </div>
    <?php
}

/**
 * 2. Sender Logic (Central Site)
 */
$vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
if ( isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender' ) {

    // List of options to sync
    $vsq_sync_allowed_options = array(
        'prices_data',
        'slide_banner_data',
        'doctors_table_data',
        'promotion_data',
        'product_images_data',
        'cta_footer_data',
        'popup_data',
        'detail_branch_data',
        'services_list_data',
        'award_list_data',
    );

    // List of post types to sync
    $vsq_sync_allowed_post_types = array(
        'page_doctor',
        'page_case_review',
        'page_branch',
    );

    // Hook for Options
    // Use 'pre_update_option' filter to trigger sync.
    // This runs BEFORE the value is saved, so it catches the update even if the value hasn't changed.
    add_filter( 'pre_update_option', 'vsq_sync_on_pre_update_option', 10, 3 );

    function vsq_sync_on_pre_update_option( $value, $option, $old_value ) {
        global $vsq_sync_allowed_options;
        
        // Check if this option is in our allowed list
        if ( is_array( $vsq_sync_allowed_options ) && in_array( $option, $vsq_sync_allowed_options ) ) {
            // Trigger Sync
            vsq_sync_broadcast_data( 'option', array(
                'name' => $option,
                'value' => $value
            ) );
        }

        // Always return the value for the filter
        return $value;
    }

    /* 
    // Old Hook (Deprecated for this purpose as it doesn't fire on no-change)
    // add_action( 'updated_option', 'vsq_sync_on_option_update', 10, 3 );
    // function vsq_sync_on_option_update( $option_name, $old_value, $value ) { ... }
    */

    // Hook for Posts
    add_action( 'save_post', 'vsq_sync_on_save_post', 20, 3 );
    function vsq_sync_on_save_post( $post_id, $post, $update ) {
        global $vsq_sync_allowed_post_types;

        if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
        if ( wp_is_post_revision( $post_id ) ) return;
        if ( ! in_array( $post->post_type, $vsq_sync_allowed_post_types ) ) return;

        // Get all meta
        $meta = get_post_meta( $post_id );
        
        // Prepare Data
        $post_data = array(
            'post_title' => $post->post_title,
            'post_name' => $post->post_name, // Slug is key
            'post_content' => $post->post_content,
            'post_excerpt' => $post->post_excerpt,
            'post_status' => $post->post_status,
            'post_type' => $post->post_type,
            'menu_order' => $post->menu_order,
            'meta' => $meta,
        );
        
        // Handle Thumbnail (Send URL, receiver might download or just use URL if hotlinking allowed)
        // Ideally receiver downloads it. For simplicity now, we send URL.
        if ( has_post_thumbnail( $post_id ) ) {
             $thumb_url = get_the_post_thumbnail_url( $post_id, 'full' );
             $post_data['thumbnail_url'] = $thumb_url;
        }

        vsq_sync_broadcast_data( 'post', $post_data );
    }

    // Broadcast Function
    function vsq_sync_broadcast_data( $type, $payload ) {
        $settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
        if ( empty( $settings['client_sites'] ) ) return;

        // --- NEW: Handle Doctor Table Data (IDs to Slugs) ---
        if ( $type === 'option' && isset( $payload['name'] ) && $payload['name'] === 'doctors_table_data' ) {
             $payload['value'] = vsq_sync_convert_table_ids_to_slugs( $payload['value'] );
        }
        // ----------------------------------------------------

        // Enrich Payload with Image Source URLs
        $payload = vsq_sync_enrich_payload_recursive( $payload );

        foreach ( $settings['client_sites'] as $site ) {
            if ( empty( $site['url'] ) || empty( $site['key'] ) ) continue;

            $endpoint = trailingslashit( $site['url'] ) . 'wp-json/' . VSQ_SYNC_ENDPOINT_ROUTE;
            
            $body = array(
                'type' => $type,
                'data' => $payload,
                'secret_key' => $site['key']
            );

            // Use non-blocking request if possible, or short timeout
            wp_remote_post( $endpoint, array(
                'body' => json_encode( $body ),
                'headers' => array( 'Content-Type' => 'application/json' ),
                'timeout' => 5, // Short timeout to not block save process too long
                'blocking' => false, // Async
                'sslverify' => false // Depending on env
            ) );
        }
    }

    // Helper: Enrich Payload with Image URLs
    function vsq_sync_enrich_payload_recursive( $data ) {
        if ( is_array( $data ) ) {
            $new_data = array();
            
            // Define keys for Doctors
            $doctor_single_keys = array('_doctor_thumbnail', '_doctor_thumbnail_name', '_doctor_image');
            $doctor_gallery_keys = array('_doctor_certificate_gallery', '_doctor_training_gallery');
            
            // Define keys for Branches
            $branch_single_keys = array('_branch_thumbnail', '_branch_thumbnail_name', '_branch_image_360', '_branch_location_image');

            // Define keys for Case Reviews
            $case_review_single_keys = array('_case_review_thumbnail', '_case_review_image_before_after', '_case_review_image_before', '_case_review_image_after');

            // Merge single keys
            $all_single_keys = array_merge($doctor_single_keys, $branch_single_keys, $case_review_single_keys);

            // Keys that end with "_id" but are NOT image attachment IDs (business IDs).
            // These must NOT be treated as image keys, otherwise they will be overwritten
            // with a local attachment ID on the receiver site.
            $non_image_id_keys = array( 'item_id' );

            foreach ( $data as $key => $value ) {
                // Copy original value
                $new_data[$key] = $value;

                // --- NEW: Handle Promotion Slides (Array with id & image) ---
                if ( is_array( $value ) && isset( $value['id'] ) && isset( $value['image'] ) && is_numeric( $value['id'] ) ) {
                     $value['_image_meta'] = vsq_sync_get_image_meta( $value['id'] );
                }
                // --- NEW: Handle Promotion Slides (Nested in slides_promotion) ---
                if ( $key === 'slides_promotion' && is_array( $value ) ) {
                    foreach ( $value as $k => $slide ) {
                        if ( is_array( $slide ) && isset( $slide['id'] ) && is_numeric( $slide['id'] ) ) {
                            $value[$k]['_image_meta'] = vsq_sync_get_image_meta( $slide['id'] );
                        }
                    }
                }
                // ------------------------------------------------------------

                // --- NEW: Handle Doctor Schedule (Inject Branch Slug) ---
                if ( $key === '_doctor_schedule' && isset( $value[0] ) ) {
                    $schedule = maybe_unserialize( $value[0] );
                    if ( is_array( $schedule ) ) {
                        $modified = false;
                        foreach ( $schedule as $k => $item ) {
                            if ( isset( $item['branch'] ) && is_numeric( $item['branch'] ) ) {
                                $branch_post = get_post( $item['branch'] );
                                if ( $branch_post ) {
                                    $schedule[$k]['branch_slug'] = $branch_post->post_name;
                                    $modified = true;
                                }
                            }
                        }
                        if ( $modified ) {
                            // Replace serialized string with array for JSON transport
                            $new_data[$key] = array( $schedule );
                            // Skip default recursion for this key
                            continue;
                        }
                    }
                }
                // --------------------------------------------------------

                // --- NEW: Handle Doctor Case Reviews (Convert IDs to Slugs) ---
                if ( $key === '_doctor_case_reviews' && isset( $value[0] ) ) {
                    $ids_str = $value[0];
                    if ( ! empty( $ids_str ) ) {
                        $ids = explode( ',', $ids_str );
                        $slugs = array();
                        foreach ( $ids as $id ) {
                            if ( is_numeric( $id ) ) {
                                $post_obj = get_post( $id );
                                if ( $post_obj && $post_obj->post_type === 'page_case_review' ) {
                                    $slugs[] = $post_obj->post_name;
                                }
                            }
                        }
                        if ( ! empty( $slugs ) ) {
                            $new_data['_doctor_case_reviews_slugs'] = array( implode( ',', $slugs ) );
                        }
                    }
                }
                // --------------------------------------------------------------

                if ( is_array( $value ) ) {
                    // Check if this is a meta field (value is array) holding an image ID
                    if ( in_array( $key, $all_single_keys ) && isset( $value[0] ) && is_numeric( $value[0] ) && $value[0] > 0 ) {
                        $url = wp_get_attachment_url( $value[0] );
                        if ( $url ) {
                            $new_data[$key . '_source_url'] = $url;
                            $new_data[$key . '_image_meta'] = vsq_sync_get_image_meta( $value[0] );
                        }
                    }
                    
                    // Check if this is a meta field holding a gallery string
                    if ( in_array( $key, $doctor_gallery_keys ) && isset( $value[0] ) && ! empty( $value[0] ) ) {
                        $ids = explode( ',', $value[0] );
                        $urls = array();
                        $metas = array();
                        foreach ( $ids as $id ) {
                            if ( is_numeric( $id ) ) {
                                $u = wp_get_attachment_url( $id );
                                if ( $u ) {
                                    $urls[] = $u;
                                    $metas[] = vsq_sync_get_image_meta( $id );
                                }
                            }
                        }
                        if ( ! empty( $urls ) ) {
                            $new_data[$key . '_source_url'] = implode( ',', $urls );
                            $new_data[$key . '_image_meta'] = $metas;
                        }
                    }

                    $new_data[$key] = vsq_sync_enrich_payload_recursive( $value );
                } else {
                    // Scalar Value
                    $is_image_key = ( substr( $key, -3 ) === '_id' || $key === 'image_id' || in_array( $key, $all_single_keys ) );

                    // Skip business IDs that happen to end with "_id" (e.g. item_id)
                    if ( in_array( $key, $non_image_id_keys, true ) ) {
                        $is_image_key = false;
                    }

                    if ( $is_image_key && is_numeric( $value ) && $value > 0 ) {
                        $url = wp_get_attachment_url( $value );
                        if ( $url ) {
                            $new_data[$key . '_source_url'] = $url;
                            $new_data[$key . '_image_meta'] = vsq_sync_get_image_meta( $value );
                        }
                    }

                    // Check for Gallery keys as Scalar
                    if ( in_array( $key, $doctor_gallery_keys ) && ! empty( $value ) ) {
                        $ids = explode( ',', $value );
                        $urls = array();
                        $metas = array();
                        foreach ( $ids as $id ) {
                            if ( is_numeric( $id ) ) {
                                $u = wp_get_attachment_url( $id );
                                if ( $u ) {
                                    $urls[] = $u;
                                    $metas[] = vsq_sync_get_image_meta( $id );
                                }
                            }
                        }
                        if ( ! empty( $urls ) ) {
                            $new_data[$key . '_source_url'] = implode( ',', $urls );
                            $new_data[$key . '_image_meta'] = $metas;
                        }
                    }
                }
            }
            return $new_data;
        }
        return $data;
    }

    // Helper: Get Image Meta
    function vsq_sync_get_image_meta( $attachment_id ) {
        $post = get_post( $attachment_id );
        if ( ! $post ) return array();
        
        return array(
            'alt'         => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ),
            'title'       => $post->post_title,
            'caption'     => $post->post_excerpt,
            'description' => $post->post_content,
        );
    }

    // Helper: Convert Table IDs to Slugs
    function vsq_sync_convert_table_ids_to_slugs( $data ) {
        if ( ! is_array( $data ) || ! isset( $data['body_list'] ) || ! is_array( $data['body_list'] ) ) {
            return $data;
        }

        foreach ( $data['body_list'] as $index => $row ) {
            if ( isset( $row['days'] ) && is_array( $row['days'] ) ) {
                foreach ( $row['days'] as $day => $doctor_ids ) {
                    if ( is_array( $doctor_ids ) ) {
                        $slugs = array();
                        foreach ( $doctor_ids as $id ) {
                            if ( is_numeric( $id ) ) {
                                $post = get_post( $id );
                                if ( $post && $post->post_type === 'page_doctor' ) {
                                    $slugs[] = $post->post_name;
                                }
                            } else {
                                // Already a slug or name? Keep it.
                                $slugs[] = $id;
                            }
                        }
                        $data['body_list'][$index]['days'][$day] = $slugs;
                    }
                }
            }
        }
        return $data;
    }

    // Hook for Manual Sync All Doctors (Batch AJAX)
    add_action( 'wp_ajax_vsq_sync_batch_doctors', 'vsq_sync_batch_doctors_ajax_handler' );

    function vsq_sync_batch_doctors_ajax_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        
        check_admin_referer( 'vsq_sync_all_doctors_action' );

        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $limit  = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 5; // Process 5 at a time

        // Get total count first (only on first call)
        $total = isset( $_POST['total'] ) ? intval( $_POST['total'] ) : 0;
        if ( $offset === 0 && $total === 0 ) {
            $count_args = array(
                'post_type'      => 'page_doctor',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            );
            $all_posts = get_posts( $count_args );
            $total = count( $all_posts );
        }

        // Get batch
        $args = array(
            'post_type'      => 'page_doctor',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        );
        $doctors = get_posts( $args );

        if ( empty( $doctors ) ) {
            wp_send_json_success( array( 
                'done' => true, 
                'message' => 'All synced.',
                'offset' => $offset,
                'total' => $total
            ) );
        }

        $synced_count = 0;
        foreach ( $doctors as $doctor ) {
            // Re-use existing sync logic
            vsq_sync_on_save_post( $doctor->ID, $doctor, true );
            $synced_count++;
        }

        $next_offset = $offset + $synced_count;
        $is_done = ( $next_offset >= $total );

        wp_send_json_success( array(
            'done' => $is_done,
            'offset' => $next_offset,
            'total' => $total,
            'synced' => $synced_count,
            'message' => sprintf( 'Synced %d of %d doctors...', min($next_offset, $total), $total )
        ) );
    }

    // Hook for Manual Sync All Branches (Batch AJAX)
    add_action( 'wp_ajax_vsq_sync_batch_branches', 'vsq_sync_batch_branches_ajax_handler' );

    function vsq_sync_batch_branches_ajax_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        
        check_admin_referer( 'vsq_sync_all_branches_action' );

        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $limit  = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 5; 

        // Get total count first
        $total = isset( $_POST['total'] ) ? intval( $_POST['total'] ) : 0;
        if ( $offset === 0 && $total === 0 ) {
            $count_args = array(
                'post_type'      => 'page_branch',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            );
            $all_posts = get_posts( $count_args );
            $total = count( $all_posts );
        }

        // Get batch
        $args = array(
            'post_type'      => 'page_branch',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        );
        $branches = get_posts( $args );

        if ( empty( $branches ) ) {
            wp_send_json_success( array( 
                'done' => true, 
                'message' => 'All synced.',
                'offset' => $offset,
                'total' => $total
            ) );
        }

        $synced_count = 0;
        foreach ( $branches as $branch ) {
            vsq_sync_on_save_post( $branch->ID, $branch, true );
            $synced_count++;
        }

        $next_offset = $offset + $synced_count;
        $is_done = ( $next_offset >= $total );

        wp_send_json_success( array(
            'done' => $is_done,
            'offset' => $next_offset,
            'total' => $total,
            'synced' => $synced_count,
            'message' => sprintf( 'Synced %d of %d branches...', min($next_offset, $total), $total )
        ) );
    }

    // Hook for Manual Sync All Case Reviews (Batch AJAX)
    add_action( 'wp_ajax_vsq_sync_batch_case_reviews', 'vsq_sync_batch_case_reviews_ajax_handler' );

    function vsq_sync_batch_case_reviews_ajax_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }
        
        check_admin_referer( 'vsq_sync_all_case_reviews_action' );

        $offset = isset( $_POST['offset'] ) ? intval( $_POST['offset'] ) : 0;
        $limit  = isset( $_POST['limit'] ) ? intval( $_POST['limit'] ) : 5; 

        // Get total count first
        $total = isset( $_POST['total'] ) ? intval( $_POST['total'] ) : 0;
        if ( $offset === 0 && $total === 0 ) {
            $count_args = array(
                'post_type'      => 'page_case_review',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'fields'         => 'ids',
            );
            $all_posts = get_posts( $count_args );
            $total = count( $all_posts );
        }

        // Get batch
        $args = array(
            'post_type'      => 'page_case_review',
            'post_status'    => 'publish',
            'posts_per_page' => $limit,
            'offset'         => $offset,
            'orderby'        => 'ID',
            'order'          => 'ASC',
        );
        $reviews = get_posts( $args );

        if ( empty( $reviews ) ) {
            wp_send_json_success( array( 
                'done' => true, 
                'message' => 'All synced.',
                'offset' => $offset,
                'total' => $total
            ) );
        }

        $synced_count = 0;
        foreach ( $reviews as $review ) {
            vsq_sync_on_save_post( $review->ID, $review, true );
            $synced_count++;
        }

        $next_offset = $offset + $synced_count;
        $is_done = ( $next_offset >= $total );

        wp_send_json_success( array(
            'done' => $is_done,
            'offset' => $next_offset,
            'total' => $total,
            'synced' => $synced_count,
            'message' => sprintf( 'Synced %d of %d reviews...', min($next_offset, $total), $total )
        ) );
    }

    // Hook for Manual Sync All Doctors (Legacy - Keep for fallback if needed)
    add_action( 'admin_post_vsq_sync_all_doctors', 'vsq_sync_all_doctors_handler' );

    function vsq_sync_all_doctors_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        
        check_admin_referer( 'vsq_sync_all_doctors_action' );

        // Get all page_doctor posts
        $args = array(
            'post_type'      => 'page_doctor',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );
        $doctors = get_posts( $args );

        $count = 0;
        foreach ( $doctors as $doctor ) {
            // Re-use existing sync logic
            vsq_sync_on_save_post( $doctor->ID, $doctor, true );
            $count++;
        }

        // Redirect back
        $redirect_url = add_query_arg( 
            array( 
                'post_type' => 'page_doctor', 
                'vsq_synced' => $count 
            ), 
            admin_url( 'edit.php' ) 
        );
        wp_redirect( $redirect_url );
        exit;
    }

    // Hook for Manual Sync All Branches
    add_action( 'admin_post_vsq_sync_all_branches', 'vsq_sync_all_branches_handler' );

    function vsq_sync_all_branches_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        
        check_admin_referer( 'vsq_sync_all_branches_action' );

        // Get all page_branch posts
        $args = array(
            'post_type'      => 'page_branch',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );
        $branches = get_posts( $args );

        $count = 0;
        foreach ( $branches as $branch ) {
            // Re-use existing sync logic
            vsq_sync_on_save_post( $branch->ID, $branch, true );
            $count++;
        }

        // Redirect back
        $redirect_url = add_query_arg( 
            array( 
                'post_type' => 'page_branch', 
                'vsq_synced' => $count 
            ), 
            admin_url( 'edit.php' ) 
        );
        wp_redirect( $redirect_url );
        exit;
    }

    // Hook for Manual Sync All Case Reviews
    add_action( 'admin_post_vsq_sync_all_case_reviews', 'vsq_sync_all_case_reviews_handler' );

    function vsq_sync_all_case_reviews_handler() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized' );
        }
        
        check_admin_referer( 'vsq_sync_all_case_reviews_action' );

        // Get all page_case_review posts
        $args = array(
            'post_type'      => 'page_case_review',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
        );
        $reviews = get_posts( $args );

        $count = 0;
        foreach ( $reviews as $review ) {
            // Re-use existing sync logic
            vsq_sync_on_save_post( $review->ID, $review, true );
            $count++;
        }

        // Redirect back
        $redirect_url = add_query_arg( 
            array( 
                'post_type' => 'page_case_review', 
                'vsq_synced' => $count 
            ), 
            admin_url( 'edit.php' ) 
        );
        wp_redirect( $redirect_url );
        exit;
    }

    // Admin Notice
    add_action( 'admin_notices', 'vsq_sync_admin_notice' );
    function vsq_sync_admin_notice() {
        if ( isset( $_GET['vsq_synced'] ) ) {
            $count = intval( $_GET['vsq_synced'] );
            $post_type_label = 'รายการ';
            if ( isset( $_GET['post_type'] ) ) {
                if ( $_GET['post_type'] === 'page_doctor' ) $post_type_label = 'แพทย์';
                if ( $_GET['post_type'] === 'page_branch' ) $post_type_label = 'สาขา';
                if ( $_GET['post_type'] === 'page_case_review' ) $post_type_label = 'รีวิว';
            }
            ?>
            <div class="notice notice-success is-dismissible">
                <p><?php echo sprintf( 'ส่งข้อมูล%sจำนวน %d รายการ ไปยังเว็บลูกข่ายเรียบร้อยแล้ว', $post_type_label, $count ); ?></p>
            </div>
            <?php
        }
    }

}

/**
 * 3. Receiver Logic (Client Site)
 */
if ( isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'receiver' ) {

    add_action( 'rest_api_init', function () {
        register_rest_route( 'vsq-sync/v1', '/update', array(
            'methods' => 'POST',
            'callback' => 'vsq_sync_receive_callback',
            'permission_callback' => '__return_true', // Validation inside callback
        ) );
    } );

    // Helper: Logging
    function vsq_sync_log( $message ) {
        $log_file = WP_CONTENT_DIR . '/vsq-sync-debug.log';
        $time = current_time( 'mysql' );
        $formatted_message = "[{$time}] {$message}\n";
        file_put_contents( $log_file, $formatted_message, FILE_APPEND );
    }

    function vsq_sync_receive_callback( WP_REST_Request $request ) {
        $settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
        $my_secret = isset( $settings['secret_key'] ) ? $settings['secret_key'] : '';
        
        $params = $request->get_json_params();
        $incoming_key = isset( $params['secret_key'] ) ? $params['secret_key'] : '';

        // Log Incoming Request
        vsq_sync_log( "Incoming Request from " . $_SERVER['REMOTE_ADDR'] );
        vsq_sync_log( "Type: " . ( isset( $params['type'] ) ? $params['type'] : 'unknown' ) );

        if ( empty( $my_secret ) || $incoming_key !== $my_secret ) {
            vsq_sync_log( "Error: Invalid Secret Key. Expected: {$my_secret}, Received: {$incoming_key}" );
            return new WP_Error( 'forbidden', 'Invalid Secret Key', array( 'status' => 403 ) );
        }

        $type = isset( $params['type'] ) ? $params['type'] : '';
        $data = isset( $params['data'] ) ? $params['data'] : array();

        vsq_sync_log( "Data received: " . print_r( $data, true ) );

        // --- PROCESS IMAGES (Auto Download) ---
        // Require media functions
        if ( ! function_exists( 'media_handle_sideload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
        }
        $data = vsq_sync_process_incoming_images_recursive( $data );
        // ---------------------------------------

        if ( $type === 'option' ) {
            if ( isset( $data['name'] ) && isset( $data['value'] ) ) {
                
                // --- NEW: Handle Doctor Table Data (Slugs to IDs) ---
                if ( $data['name'] === 'doctors_table_data' ) {
                    $data['value'] = vsq_sync_convert_table_slugs_to_ids( $data['value'] );
                }
                // ----------------------------------------------------

                update_option( $data['name'], $data['value'] );
                return rest_ensure_response( array( 'success' => true, 'message' => 'Option updated' ) );
            }
        } elseif ( $type === 'post' ) {
            if ( isset( $data['post_name'] ) && isset( $data['post_type'] ) ) {
                
                // Validate slug
                if ( empty( $data['post_name'] ) ) {
                     // Fallback: Generate slug from title
                     $data['post_name'] = sanitize_title( $data['post_title'] );
                }

                // Try to find existing post by slug
                $existing_id = 0;
                if ( ! empty( $data['post_name'] ) ) {
                    $args = array(
                        'name'        => $data['post_name'],
                        'post_type'   => $data['post_type'],
                        'post_status' => 'any',
                        'numberposts' => 1,
                        'fields'      => 'ids'
                    );
                    $existing = get_posts($args);
                    if ( ! empty( $existing ) ) {
                        $existing_id = $existing[0];
                    }
                }
                
                $post_arr = array(
                    'post_title' => $data['post_title'],
                    'post_content' => $data['post_content'],
                    'post_excerpt' => $data['post_excerpt'],
                    'post_status' => $data['post_status'],
                    'post_type' => $data['post_type'],
                    'menu_order' => isset($data['menu_order']) ? $data['menu_order'] : 0,
                    'post_name' => $data['post_name']
                );

                if ( $existing_id ) {
                    $post_arr['ID'] = $existing_id;
                    $post_id = wp_update_post( $post_arr );
                } else {
                    $post_id = wp_insert_post( $post_arr );
                }

                if ( ! is_wp_error( $post_id ) ) {
                    // Update Meta
                    if ( isset( $data['meta'] ) && is_array( $data['meta'] ) ) {
                        // Define keys that should be single values (images)
                        $single_image_keys = array( 
                            '_doctor_thumbnail', '_doctor_thumbnail_name', '_doctor_image',
                            '_branch_thumbnail', '_branch_thumbnail_name', '_branch_image_360', '_branch_location_image',
                            '_case_review_thumbnail', '_case_review_image_before_after', '_case_review_image_before', '_case_review_image_after'
                        );

                        foreach ( $data['meta'] as $key => $val ) {
                            
                            // Skip helper keys ending in _slugs
                            if ( substr( $key, -6 ) === '_slugs' ) {
                                continue;
                            }

                            // --- NEW: Handle Doctor Schedule (Resolve Branch ID) ---
                            if ( $key === '_doctor_schedule' && isset( $val[0] ) && is_array( $val[0] ) ) {
                                $schedule = $val[0];
                                $modified = false;
                                foreach ( $schedule as $k => $item ) {
                                    if ( ! empty( $item['branch_slug'] ) ) {
                                        // Find ID by Slug
                                        $args = array(
                                            'name'        => $item['branch_slug'],
                                            'post_type'   => 'page_branch',
                                            'post_status' => 'any',
                                            'numberposts' => 1,
                                            'fields'      => 'ids'
                                        );
                                        $found_branch = get_posts($args);
                                        if ( ! empty( $found_branch ) ) {
                                            $schedule[$k]['branch'] = $found_branch[0];
                                            $modified = true;
                                        }
                                        // Remove slug to keep DB clean
                                        unset( $schedule[$k]['branch_slug'] );
                                    }
                                }
                                if ( $modified ) {
                                    $val = array( $schedule );
                                }
                            }
                            // -------------------------------------------------------

                            // --- NEW: Handle Doctor Case Reviews (Resolve Slugs to IDs) ---
                            if ( $key === '_doctor_case_reviews' && isset( $data['meta']['_doctor_case_reviews_slugs'][0] ) ) {
                                $slugs_str = $data['meta']['_doctor_case_reviews_slugs'][0];
                                if ( ! empty( $slugs_str ) ) {
                                    $slugs = explode( ',', $slugs_str );
                                    $local_ids = array();
                                    
                                    foreach ( $slugs as $slug ) {
                                        $args = array(
                                            'name'        => $slug,
                                            'post_type'   => 'page_case_review',
                                            'post_status' => 'any',
                                            'numberposts' => 1,
                                            'fields'      => 'ids'
                                        );
                                        $found_posts = get_posts( $args );
                                        if ( ! empty( $found_posts ) ) {
                                            $local_ids[] = $found_posts[0];
                                        }
                                    }
                                    
                                    // Replace value with local IDs string
                                    if ( ! empty( $local_ids ) ) {
                                        $val = array( implode( ',', $local_ids ) );
                                    }
                                }
                            }
                            // --------------------------------------------------------------

                            // Special handling for Image Keys to ensure clean update
                            if ( in_array( $key, $single_image_keys ) ) {
                                // If val is not an array, it means it was replaced by a new ID (int/string)
                                if ( ! is_array( $val ) ) {
                                    delete_post_meta( $post_id, $key );
                                    update_post_meta( $post_id, $key, $val );
                                    continue; 
                                }
                            }

                            // $val is array because get_post_meta returns array
                            if ( is_array( $val ) && count( $val ) === 1 ) {
                                update_post_meta( $post_id, $key, $val[0] );
                            } elseif ( is_array( $val ) ) {
                                // Serialized or multiple? Usually delete and re-add
                                delete_post_meta($post_id, $key);
                                foreach($val as $v) {
                                    add_post_meta($post_id, $key, $v);
                                }
                            } else {
                                update_post_meta( $post_id, $key, $val );
                            }
                        }
                    }
                    
                    // Handle Thumbnail if sent (Legacy support)
                    if ( isset( $data['thumbnail_url'] ) && ! empty( $data['thumbnail_url'] ) ) {
                        $thumb_id = vsq_sync_sideload_image( $data['thumbnail_url'] );
                        if ( $thumb_id ) {
                            set_post_thumbnail( $post_id, $thumb_id );
                        }
                    }
                    
                    return rest_ensure_response( array( 'success' => true, 'message' => 'Post synced', 'id' => $post_id ) );
                }
            }
        }

        return new WP_Error( 'invalid_data', 'Invalid data format', array( 'status' => 400 ) );
    }

    // Helper: Process Incoming Images
    function vsq_sync_process_incoming_images_recursive( $data ) {
        if ( is_array( $data ) ) {
            // 1. Process Child Arrays First
            foreach ( $data as $key => &$value ) {
                if ( is_array( $value ) ) {
                    // Special Case: Promotion Slides (id + image) in child array
                    if ( isset( $value['id'] ) && isset( $value['image'] ) ) {
                        // Encode URL before validating to support Thai characters
                        $check_url = vsq_sync_encode_url( $value['image'] );
                        
                        if ( filter_var( $check_url, FILTER_VALIDATE_URL ) ) {
                            vsq_sync_log( "Found Promotion Slide Image: " . $value['image'] );
                            $meta = isset( $value['_image_meta'] ) ? $value['_image_meta'] : array();
                            $new_id = vsq_sync_sideload_image( $value['image'], $meta );
                            if ( $new_id ) {
                                $value['id'] = $new_id;
                                $value['image'] = wp_get_attachment_url( $new_id );
                            }
                        }
                    }
                    // Recurse
                    $value = vsq_sync_process_incoming_images_recursive( $value );
                }
            }
            unset($value); // break reference

            // Business ID keys that must NEVER be replaced with a local attachment ID.
            // Their values from the master site must be preserved as-is.
            $non_image_id_keys = array( 'item_id' );

            // 2. Process Current Level for _source_url keys
            // Collect keys to process to avoid modifying array while iterating
            $source_keys = array();
            foreach ( $data as $key => $val ) {
                if ( !is_array($val) && substr( $key, -11 ) === '_source_url' ) {
                    // Skip source_url that belongs to a business ID key (e.g. item_id_source_url)
                    $base_key = substr( $key, 0, -11 );
                    if ( in_array( $base_key, $non_image_id_keys, true ) ) {
                        // Drop the helper keys without touching the business ID value
                        unset( $data[ $key ] );
                        $meta_key = $base_key . '_image_meta';
                        if ( isset( $data[ $meta_key ] ) ) {
                            unset( $data[ $meta_key ] );
                        }
                        continue;
                    }
                    $source_keys[] = $key;
                }
            }

            if ( ! empty( $source_keys ) ) {
                vsq_sync_log( "Found source_url keys: " . implode(', ', $source_keys) );
            }

            foreach ( $source_keys as $key ) {
                $target_key = substr( $key, 0, -11 ); // remove _source_url
                
                // Get Meta
                $meta_key = $target_key . '_image_meta';
                $meta_data = isset( $data[$meta_key] ) ? $data[$meta_key] : array();

                if ( isset( $data[$target_key] ) ) {
                    $image_url_val = $data[$key]; // Value can be Single URL or Comma-separated
                    
                    if ( $image_url_val ) {
                        // Check for Gallery (Multiple URLs separated by comma)
                        if ( strpos( $image_url_val, ',' ) !== false ) {
                             $urls = explode( ',', $image_url_val );
                             $new_ids = array();
                             
                             foreach ( $urls as $i => $u ) {
                                 $u = trim( $u );
                                 if ( $u ) {
                                     // Get meta for this index if exists
                                     $m = isset( $meta_data[$i] ) ? $meta_data[$i] : array();
                                     $nid = vsq_sync_sideload_image( $u, $m );
                                     if ( $nid ) $new_ids[] = $nid;
                                 }
                             }
                             
                             if ( ! empty( $new_ids ) ) {
                                 $data[$target_key] = implode( ',', $new_ids );
                                 vsq_sync_log( "Gallery synced for {$target_key}. IDs: " . implode(',', $new_ids) );
                             }
                             
                        } else {
                            // Single Image
                            vsq_sync_log( "Attempting sideload for key {$target_key}: {$image_url_val}" );
                            
                            // Fix: Handle case where meta is wrapped in array (single item gallery)
                            $current_meta = $meta_data;
                            if ( isset( $meta_data[0] ) && is_array( $meta_data[0] ) ) {
                                $current_meta = $meta_data[0];
                            }

                            $new_id = vsq_sync_sideload_image( $image_url_val, $current_meta );
                            if ( $new_id ) {
                                vsq_sync_log( "Sideload success. New ID: {$new_id}" );
                                // Update ID
                                $data[$target_key] = $new_id;
                                
                                // Also Update Neighbor URL field if exists (Naming Convention Check)
                                $target_key_url = str_replace( '_id', '', $target_key ); 
                                if ( isset( $data[$target_key_url] ) ) {
                                    $data[$target_key_url] = wp_get_attachment_url( $new_id );
                                }
                                
                                // Special case for image_id -> image_url pair
                                if ( $target_key === 'image_id' && isset( $data['image_url'] ) ) {
                                    $data['image_url'] = wp_get_attachment_url( $new_id );
                                }
                            } else {
                                vsq_sync_log( "Sideload failed for {$image_url_val}" );
                            }
                        }
                    }
                }
                // Cleanup helper key
                unset( $data[$key] );
                unset( $data[$meta_key] ); // Also cleanup meta key
            }
        }
        return $data;
    }

    // Helper: Sideload Image
    function vsq_sync_sideload_image( $url, $meta = array() ) {
        vsq_sync_log( "Start Sideload: {$url}" );
        // Fix protocol-relative URLs
        if ( substr( $url, 0, 2 ) === '//' ) {
            $url = 'https:' . $url;
        }

        // Encode URL to handle Thai characters
        $url = vsq_sync_encode_url( $url );
        vsq_sync_log( "Encoded URL: {$url}" );
        
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            vsq_sync_log( "Invalid URL: {$url}" );
            return 0;
        }

        // 0. Include required files if not loaded
        if ( ! function_exists( 'media_handle_sideload' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/image.php' );
            require_once( ABSPATH . 'wp-admin/includes/file.php' );
            require_once( ABSPATH . 'wp-admin/includes/media.php' );
        }

        // 1. Check if already downloaded (Prevent Duplicates)
        $existing = new WP_Query( array(
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'meta_query' => array(
                array(
                    'key' => '_vsq_source_url',
                    'value' => $url,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1,
            'fields' => 'ids'
        ) );

        if ( $existing->have_posts() ) {
            $existing_id = $existing->posts[0];
            vsq_sync_log( "Image already exists. ID: " . $existing_id );
            // Update Meta for existing image too
            vsq_sync_update_attachment_meta( $existing_id, $meta );
            return $existing_id;
        }

        // 2. Download
        // Suppress SSL verification for download_url if needed (e.g. local dev)
        add_filter('https_ssl_verify', '__return_false');
        $tmp = download_url( $url );
        remove_filter('https_ssl_verify', '__return_false');

        if ( is_wp_error( $tmp ) ) {
            vsq_sync_log( "download_url Error: " . $tmp->get_error_message() );
            return 0;
        }

        $file_array = array(
            'name' => urldecode( basename( $url ) ), // Decode to Thai
            'tmp_name' => $tmp
        );

        // 3. Insert into Media
        $id = media_handle_sideload( $file_array, 0 );

        // 4. Clean up tmp
        @unlink( $file_array['tmp_name'] );

        if ( is_wp_error( $id ) ) {
            vsq_sync_log( "media_handle_sideload Error: " . $id->get_error_message() );
            return 0;
        }

        // 5. Mark as Synced & Update Meta
        update_post_meta( $id, '_vsq_source_url', $url );
        vsq_sync_update_attachment_meta( $id, $meta );
        vsq_sync_log( "Image downloaded successfully. ID: {$id}" );

        return $id;
    }

    // Helper: Update Attachment Meta
    function vsq_sync_update_attachment_meta( $attachment_id, $meta ) {
        if ( empty( $meta ) ) return;
        
        $post_data = array( 'ID' => $attachment_id );
        $update = false;

        if ( isset( $meta['title'] ) && ! empty( $meta['title'] ) ) {
            $post_data['post_title'] = $meta['title'];
            $update = true;
        }
        if ( isset( $meta['caption'] ) ) {
            // Caption can be empty string, so we check isset
            $post_data['post_excerpt'] = $meta['caption'];
            $update = true;
        }
        if ( isset( $meta['description'] ) ) {
            // Description can be empty string
            $post_data['post_content'] = $meta['description'];
            $update = true;
        }
        
        if ( $update ) {
            $updated = wp_update_post( $post_data );
            if ( is_wp_error( $updated ) ) {
                vsq_sync_log( "Error updating attachment meta for ID {$attachment_id}: " . $updated->get_error_message() );
            } else {
                vsq_sync_log( "Updated attachment meta for ID {$attachment_id}" );
            }
        }

        if ( isset( $meta['alt'] ) ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', $meta['alt'] );
            vsq_sync_log( "Updated alt text for ID {$attachment_id}: " . $meta['alt'] );
        }
    }

    // Helper: Encode URL with Thai characters support
    function vsq_sync_encode_url( $url ) {
        // If URL is already valid (encoded), return it
        if ( filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return $url;
        }

        // Parse URL parts
        $parts = parse_url( $url );
        if ( ! $parts ) {
            return $url; // Return original if parse failed
        }

        $encoded_url = '';
        if ( isset( $parts['scheme'] ) ) $encoded_url .= $parts['scheme'] . '://';
        if ( isset( $parts['host'] ) )   $encoded_url .= $parts['host'];
        if ( isset( $parts['port'] ) )   $encoded_url .= ':' . $parts['port'];
        
        // Encode Path (handling Thai characters)
        if ( isset( $parts['path'] ) ) {
            // Split path segments
            $path_segments = explode( '/', $parts['path'] );
            $encoded_segments = array_map( 'rawurlencode', $path_segments );
            $encoded_url .= implode( '/', $encoded_segments );
        }

        if ( isset( $parts['query'] ) ) {
            // Encode Query Parameters if needed (simple check)
            $encoded_url .= '?' . $parts['query'];
        }

        if ( isset( $parts['fragment'] ) ) $encoded_url .= '#' . $parts['fragment'];

        return $encoded_url;
    }

    // Helper: Convert Table Slugs to IDs
    function vsq_sync_convert_table_slugs_to_ids( $data ) {
        if ( ! is_array( $data ) || ! isset( $data['body_list'] ) || ! is_array( $data['body_list'] ) ) {
            return $data;
        }

        foreach ( $data['body_list'] as $index => $row ) {
            if ( isset( $row['days'] ) && is_array( $row['days'] ) ) {
                foreach ( $row['days'] as $day => $slugs ) {
                    if ( is_array( $slugs ) ) {
                        $ids = array();
                        foreach ( $slugs as $slug ) {
                            $args = array(
                                'name'        => $slug,
                                'post_type'   => 'page_doctor',
                                'post_status' => 'any',
                                'numberposts' => 1,
                                'fields'      => 'ids'
                            );
                            $posts = get_posts( $args );
                            if ( ! empty( $posts ) ) {
                                $ids[] = $posts[0];
                            } else {
                                // Not found? Keep slug or skip?
                                // If we keep slug, frontend might break if it expects ID.
                                // But frontend code handles title fallback.
                                $ids[] = $slug; 
                            }
                        }
                        $data['body_list'][$index]['days'][$day] = $ids;
                    }
                }
            }
        }
        return $data;
    }

}
