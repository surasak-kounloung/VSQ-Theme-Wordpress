<?php
/**
 * Header and Footer Code Meta Box
 *
 * Adds "Head Code" and "Footer Code" fields to Pages and Posts.
 */

// Register Meta Fields for REST API support
add_action( 'init', 'register_header_footer_meta' );
function register_header_footer_meta() {
    $screens = array( 'post', 'page' );
    foreach ( $screens as $type ) {
        register_post_meta( $type, '_head_code', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
            'sanitize_callback' => function( $value ) {
                // Allow admins to save raw code (including scripts)
                if ( current_user_can( 'unfiltered_html' ) ) {
                    return $value;
                }
                // Sanitize for others
                return wp_kses_post( $value );
            }
        ) );
        register_post_meta( $type, '_footer_code', array(
            'show_in_rest' => true,
            'single' => true,
            'type' => 'string',
            'auth_callback' => function() {
                return current_user_can( 'edit_posts' );
            },
            'sanitize_callback' => function( $value ) {
                if ( current_user_can( 'unfiltered_html' ) ) {
                    return $value;
                }
                return wp_kses_post( $value );
            }
        ) );
    }
}

// Add Meta Box
add_action( 'add_meta_boxes', 'add_header_footer_meta_boxes' );
function add_header_footer_meta_boxes() {
    $screens = array( 'post', 'page' );
    foreach ( $screens as $screen ) {
        add_meta_box(
            'header_footer_code_meta_box',
            __( 'Header & Footer Code', 'vsquareclinic' ),
            'render_header_footer_meta_box',
            $screen,
            'normal',
            'high'
        );
    }
}

// Render Meta Box
function render_header_footer_meta_box( $post ) {
    // Add nonce for security and authentication.
    wp_nonce_field( 'save_header_footer_code', 'header_footer_code_nonce' );

    // Retrieve existing values from the database.
    $head_code = get_post_meta( $post->ID, '_head_code', true );
    $footer_code = get_post_meta( $post->ID, '_footer_code', true );
    ?>
    <div style="margin-bottom: 20px;">
        <label for="head_code" style="display:block; margin-bottom:5px; font-weight:600;">
            <?php _e( 'Head Code', 'vsquareclinic' ); ?>
        </label>
        <p class="description" style="margin-bottom:5px;">
            <?php _e( 'Code to be added to the &lt;head&gt; section.', 'vsquareclinic' ); ?>
        </p>
        <textarea id="head_code" name="head_code" rows="5" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $head_code ); ?></textarea>
    </div>

    <div>
        <label for="footer_code" style="display:block; margin-bottom:5px; font-weight:600;">
            <?php _e( 'Footer Code', 'vsquareclinic' ); ?>
        </label>
        <p class="description" style="margin-bottom:5px;">
            <?php _e( 'Code to be added before the closing &lt;/body&gt; tag.', 'vsquareclinic' ); ?>
        </p>
        <textarea id="footer_code" name="footer_code" rows="5" style="width:100%; font-family:monospace;"><?php echo esc_textarea( $footer_code ); ?></textarea>
    </div>
    <?php
}

// Save Meta Box Data
add_action( 'save_post', 'save_header_footer_code' );
function save_header_footer_code( $post_id ) {
    // If this is an autosave, our form has not been submitted.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check nonce availability
    if ( ! isset( $_POST['header_footer_code_nonce'] ) ) {
        return;
    }

    // Verify nonce
    if ( ! wp_verify_nonce( $_POST['header_footer_code_nonce'], 'save_header_footer_code' ) ) {
        return;
    }

    // Check permissions
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }
    } else {
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    // Save fields
    if ( isset( $_POST['head_code'] ) ) {
        $head_code = $_POST['head_code'];
        // Use the same logic as register_post_meta sanitizer if possible, or just strict check
        if ( current_user_can( 'unfiltered_html' ) ) {
             update_post_meta( $post_id, '_head_code', $head_code );
        } else {
             update_post_meta( $post_id, '_head_code', wp_kses_post( $head_code ) );
        }
    }

    if ( isset( $_POST['footer_code'] ) ) {
        $footer_code = $_POST['footer_code'];
        if ( current_user_can( 'unfiltered_html' ) ) {
             update_post_meta( $post_id, '_footer_code', $footer_code );
        } else {
             update_post_meta( $post_id, '_footer_code', wp_kses_post( $footer_code ) );
        }
    }
}


// Output Head Code
add_action( 'wp_head', 'insert_head_code', 999 );
function insert_head_code() {
    if ( is_singular() ) {
        $post_id = get_the_ID();
        $head_code = get_post_meta( $post_id, '_head_code', true );
        if ( ! empty( $head_code ) ) {
            echo "\n<!-- Custom head code for this page -->\n";
            echo $head_code;
            echo "\n<!-- End custom head code for this page -->\n";
        }
    }
}

// Output Footer Code
add_action( 'wp_footer', 'insert_footer_code', 999 );
function insert_footer_code() {
    if ( is_singular() ) {
        $post_id = get_the_ID();
        $footer_code = get_post_meta( $post_id, '_footer_code', true );
        if ( ! empty( $footer_code ) ) {
            echo "\n<!-- Custom footer code for this page -->\n";
            echo $footer_code;
            echo "\n<!-- End custom footer code for this page -->\n";
        }
    }
}
