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
        'popup_newyear_data',
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
            foreach ( $data as $key => $value ) {
                // Copy original value
                $new_data[$key] = $value;

                if ( is_array( $value ) ) {
                    $new_data[$key] = vsq_sync_enrich_payload_recursive( $value );
                } else {
                    // Check for Image IDs (keys ending in _id or strictly image_id)
                    if ( ( substr( $key, -3 ) === '_id' || $key === 'image_id' ) && is_numeric( $value ) && $value > 0 ) {
                        $url = wp_get_attachment_url( $value );
                        if ( $url ) {
                            $new_data[$key . '_source_url'] = $url;
                        }
                    }
                }
            }
            return $new_data;
        }
        return $data;
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
                update_option( $data['name'], $data['value'] );
                return rest_ensure_response( array( 'success' => true, 'message' => 'Option updated' ) );
            }
        } elseif ( $type === 'post' ) {
            if ( isset( $data['post_name'] ) && isset( $data['post_type'] ) ) {
                
                // Try to find existing post by slug
                $args = array(
                    'name'        => $data['post_name'],
                    'post_type'   => $data['post_type'],
                    'post_status' => 'any',
                    'numberposts' => 1
                );
                $existing = get_posts($args);
                
                $post_arr = array(
                    'post_title' => $data['post_title'],
                    'post_content' => $data['post_content'],
                    'post_excerpt' => $data['post_excerpt'],
                    'post_status' => $data['post_status'],
                    'post_type' => $data['post_type'],
                    'menu_order' => isset($data['menu_order']) ? $data['menu_order'] : 0,
                    'post_name' => $data['post_name']
                );

                if ( $existing ) {
                    $post_arr['ID'] = $existing[0]->ID;
                    $post_id = wp_update_post( $post_arr );
                } else {
                    $post_id = wp_insert_post( $post_arr );
                }

                if ( ! is_wp_error( $post_id ) ) {
                    // Update Meta
                    if ( isset( $data['meta'] ) && is_array( $data['meta'] ) ) {
                        foreach ( $data['meta'] as $key => $val ) {
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
                    if ( isset( $value['id'] ) && isset( $value['image'] ) && filter_var( $value['image'], FILTER_VALIDATE_URL ) ) {
                        vsq_sync_log( "Found Promotion Slide Image: " . $value['image'] );
                        $new_id = vsq_sync_sideload_image( $value['image'] );
                        if ( $new_id ) {
                            $value['id'] = $new_id;
                            $value['image'] = wp_get_attachment_url( $new_id );
                        }
                    }
                    // Recurse
                    $value = vsq_sync_process_incoming_images_recursive( $value );
                }
            }
            unset($value); // break reference

            // 2. Process Current Level for _source_url keys
            // Collect keys to process to avoid modifying array while iterating
            $source_keys = array();
            foreach ( $data as $key => $val ) {
                if ( !is_array($val) && substr( $key, -11 ) === '_source_url' ) {
                    $source_keys[] = $key;
                }
            }

            if ( ! empty( $source_keys ) ) {
                vsq_sync_log( "Found source_url keys: " . implode(', ', $source_keys) );
            }

            foreach ( $source_keys as $key ) {
                $target_key = substr( $key, 0, -11 ); // remove _source_url
                
                if ( isset( $data[$target_key] ) ) {
                    $image_url = $data[$key];
                    if ( $image_url ) {
                        vsq_sync_log( "Attempting sideload for key {$target_key}: {$image_url}" );
                        $new_id = vsq_sync_sideload_image( $image_url );
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
                            vsq_sync_log( "Sideload failed for {$image_url}" );
                        }
                    }
                }
                // Cleanup helper key
                unset( $data[$key] );
            }
        }
        return $data;
    }

    // Helper: Sideload Image
    function vsq_sync_sideload_image( $url ) {
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
            vsq_sync_log( "Image already exists. ID: " . $existing->posts[0] );
            return $existing->posts[0];
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

        // 5. Mark as Synced
        update_post_meta( $id, '_vsq_source_url', $url );
        vsq_sync_log( "Image downloaded successfully. ID: {$id}" );

        return $id;
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

}
