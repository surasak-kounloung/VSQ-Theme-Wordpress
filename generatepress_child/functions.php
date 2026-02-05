<?php
/**
 * GeneratePress child theme functions and definitions.
 *
 * Add your custom PHP in this file.
 * Only edit this file if you have direct access to it on your server (to fix errors if they happen).
 */

/**
 * Include Options Pages
 */
/** Meta Boxes */
require_once get_stylesheet_directory() . '/inc/metabox-header-footer.php';
require_once get_stylesheet_directory() . '/inc/metabox-doctor.php';
require_once get_stylesheet_directory() . '/inc/metabox-case-review.php';
require_once get_stylesheet_directory() . '/inc/metabox-branch.php';
/** Options Pages */
require_once get_stylesheet_directory() . '/inc/options-slide-banner.php';
require_once get_stylesheet_directory() . '/inc/options-doctors-table.php';
require_once get_stylesheet_directory() . '/inc/options-promotion.php';
require_once get_stylesheet_directory() . '/inc/options-prices.php';
require_once get_stylesheet_directory() . '/inc/options-product-images.php';
require_once get_stylesheet_directory() . '/inc/options-cta-footer.php';
require_once get_stylesheet_directory() . '/inc/options-popup-newyear.php';


 /**
 * Load Google Fonts
 */
add_action( 'wp_head', 'load_google_fonts' );
function load_google_fonts() {
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Sarabun:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">
    <?php
}


 /**
 * Load all style and script
 */
add_action( 'wp_enqueue_scripts', 'load_all_style', 90 );
function load_all_style() {
	// CSS
    wp_enqueue_style( 'fonts-style', get_stylesheet_directory_uri() . '/assets/css/fonts.css', false, '1.0', 'all' );
    wp_enqueue_style( 'font-awesome-free-style', 'https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css', array(), '6.7.2' );
    wp_enqueue_style( 'gutenberg-style', get_stylesheet_directory_uri() . '/assets/css/gutenberg.css', false, '1.0', 'all' );
    wp_enqueue_style( 'pxtovw-style', get_stylesheet_directory_uri() . '/assets/css/pxtovw.css', false, '1.0', 'all' );
    wp_enqueue_style( 'main-style', get_stylesheet_directory_uri() . '/assets/css/main.css', false, '1.0', 'all' );
    // if ( is_singular( 'post' ) || is_singular( 'posts_en' ) ) {
    //     wp_enqueue_style( 'blogs-style', get_stylesheet_directory_uri() . '/assets/css/blogs.css', false, '1.0', 'all' );
    // }
	// JS
    wp_enqueue_script( 'main-script', get_stylesheet_directory_uri() . '/assets/js/main.js', array(), '1.0.0', true );
}


/**
 * Disable big image size threshold
 */
add_filter( 'big_image_size_threshold', '__return_false' );


/**
 * Set default layout for Posts and Pages to No Sidebars
 */
add_filter( 'generate_sidebar_layout', 'vsq_default_sidebar_layout_posts_pages' );
function vsq_default_sidebar_layout_posts_pages( $layout ) {
    // Check if we are on a single post or page
    if ( is_singular( array( 'post', 'page' ) ) ) {
        $layout_meta = get_post_meta( get_the_ID(), '_generate-sidebar-layout-meta', true );
        
        // If meta is empty or 'default', force no-sidebar
        // This makes the 'Default' dropdown option behave as 'No Sidebar'
        if ( empty( $layout_meta ) || 'default' === $layout_meta ) {
            return 'no-sidebar';
        }
    }
    return $layout;
}


/**
 * Register Custom Post Type: Doctor
 */
function register_page_doctor() {

    $labels = array(
        'name'                  => _x( 'Doctor', 'Post Type General Name', 'vsquareclinic' ),
        'singular_name'         => _x( 'Doctor', 'Post Type Singular Name', 'vsquareclinic' ),
        'menu_name'             => __( 'Doctor', 'vsquareclinic' ),
        'name_admin_bar'        => __( 'Doctor', 'vsquareclinic' ),
        'archives'              => __( 'Page Archives', 'vsquareclinic' ),
        'attributes'            => __( 'Page Attributes', 'vsquareclinic' ),
        'parent_item_colon'     => __( 'Parent Page:', 'vsquareclinic' ),
        'all_items'             => __( 'All Doctor', 'vsquareclinic' ),
        'add_new_item'          => __( 'Add New Doctor', 'vsquareclinic' ),
        'add_new'               => __( 'Add New', 'vsquareclinic' ),
        'new_item'              => __( 'New Doctor', 'vsquareclinic' ),
        'edit_item'             => __( 'Edit Doctor', 'vsquareclinic' ),
        'update_item'           => __( 'Update Doctor', 'vsquareclinic' ),
        'view_item'             => __( 'View Doctor', 'vsquareclinic' ),
        'view_items'            => __( 'View Doctors', 'vsquareclinic' ),
        'search_items'          => __( 'Search Doctor', 'vsquareclinic' ),
        'not_found'             => __( 'Not found', 'vsquareclinic' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vsquareclinic' ),
        'featured_image'        => __( 'Featured Image', 'vsquareclinic' ),
        'set_featured_image'    => __( 'Set featured image', 'vsquareclinic' ),
        'remove_featured_image' => __( 'Remove featured image', 'vsquareclinic' ),
        'use_featured_image'    => __( 'Use as featured image', 'vsquareclinic' ),
        'insert_into_item'      => __( 'Insert into item', 'vsquareclinic' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'vsquareclinic' ),
        'items_list'            => __( 'Doctors list', 'vsquareclinic' ),
        'items_list_navigation' => __( 'Doctors list navigation', 'vsquareclinic' ),
        'filter_items_list'     => __( 'Filter items list', 'vsquareclinic' ),
    );
    $args = array(
        'label'                 => __( 'Doctor', 'vsquareclinic' ),
        'description'           => __( 'Doctor Description', 'vsquareclinic' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields', 'revisions', 'author' ),
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 42,
        'menu_icon'             => 'dashicons-groups',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
        'rewrite'               => array( 'slug' => 'doctor', 'with_front' => false ),
        'query_var'             => true,
    );
    register_post_type( 'page_doctor', $args );

}
add_action( 'init', 'register_page_doctor', 0 );


/**
 * Remove GeneratePress Layout Meta Box for Page Doctor
 */
add_action( 'do_meta_boxes', 'remove_doctor_layout_meta_box' );
function remove_doctor_layout_meta_box() {
	remove_meta_box( 'generate_layout_options_meta_box', 'page_doctor', 'side' );
}


/**
 * Set default layout for Page Doctor to No Sidebars
 */
add_filter( 'generate_sidebar_layout', 'doctor_default_sidebar_layout' );
function doctor_default_sidebar_layout( $layout ) {
    // Check if we are on a single 'page_doctor' post
    if ( is_singular( 'page_doctor' ) ) {
        return 'no-sidebar';
    }
    return $layout;
}


/**
 * Register Custom Post Type: Review
 */
function register_page_case_review() {

    $labels = array(
        'name'                  => _x( 'Case Review', 'Post Type General Name', 'vsquareclinic' ),
        'singular_name'         => _x( 'Case Review', 'Post Type Singular Name', 'vsquareclinic' ),
        'menu_name'             => __( 'Case Review', 'vsquareclinic' ),
        'name_admin_bar'        => __( 'Case Review', 'vsquareclinic' ),
        'archives'              => __( 'Page Archives', 'vsquareclinic' ),
        'attributes'            => __( 'Page Attributes', 'vsquareclinic' ),
        'parent_item_colon'     => __( 'Parent Page:', 'vsquareclinic' ),
        'all_items'             => __( 'All Case Review', 'vsquareclinic' ),
        'add_new_item'          => __( 'Add New Case Review', 'vsquareclinic' ),
        'add_new'               => __( 'Add New', 'vsquareclinic' ),
        'new_item'              => __( 'New Case Review', 'vsquareclinic' ),
        'edit_item'             => __( 'Edit Case Review', 'vsquareclinic' ),
        'update_item'           => __( 'Update Case Review', 'vsquareclinic' ),
        'view_item'             => __( 'View Case Review', 'vsquareclinic' ),
        'view_items'            => __( 'View Case Reviews', 'vsquareclinic' ),
        'search_items'          => __( 'Search Case Review', 'vsquareclinic' ),
        'not_found'             => __( 'Not found', 'vsquareclinic' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vsquareclinic' ),
        'featured_image'        => __( 'Featured Image', 'vsquareclinic' ),
        'set_featured_image'    => __( 'Set featured image', 'vsquareclinic' ),
        'remove_featured_image' => __( 'Remove featured image', 'vsquareclinic' ),
        'use_featured_image'    => __( 'Use as featured image', 'vsquareclinic' ),
        'insert_into_item'      => __( 'Insert into item', 'vsquareclinic' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'vsquareclinic' ),
        'items_list'            => __( 'Case Reviews list', 'vsquareclinic' ),
        'items_list_navigation' => __( 'Case Reviews list navigation', 'vsquareclinic' ),
        'filter_items_list'     => __( 'Filter items list', 'vsquareclinic' ),
    );
    $args = array(
        'label'                 => __( 'Case Review', 'vsquareclinic' ),
        'description'           => __( 'Case Review Description', 'vsquareclinic' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields', 'revisions', 'author' ),
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 43,
        'menu_icon'             => 'dashicons-format-gallery',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
        'rewrite'               => array( 'slug' => 'review', 'with_front' => false ),
        'query_var'             => true,
    );
    register_post_type( 'page_case_review', $args );

}
add_action( 'init', 'register_page_case_review', 0 );


/**
 * Remove GeneratePress Layout Meta Box for Page Case Review
 */
add_action( 'do_meta_boxes', 'remove_case_review_layout_meta_box' );
function remove_case_review_layout_meta_box() {
	remove_meta_box( 'generate_layout_options_meta_box', 'page_case_review', 'side' );
}


/**
 * Set default layout for Page Case Review to No Sidebars
 */
add_filter( 'generate_sidebar_layout', 'case_review_default_sidebar_layout' );
function case_review_default_sidebar_layout( $layout ) {
    // Check if we are on a single 'page_case_review' post
    if ( is_singular( 'page_case_review' ) ) {
        return 'no-sidebar';
    }
    return $layout;
}


/**
 * Register Custom Post Type: Branch
 */
function register_page_branch() {

    $labels = array(
        'name'                  => _x( 'Branch', 'Post Type General Name', 'vsquareclinic' ),
        'singular_name'         => _x( 'Branch', 'Post Type Singular Name', 'vsquareclinic' ),
        'menu_name'             => __( 'Branch', 'vsquareclinic' ),
        'name_admin_bar'        => __( 'Branch', 'vsquareclinic' ),
        'archives'              => __( 'Page Archives', 'vsquareclinic' ),
        'attributes'            => __( 'Page Attributes', 'vsquareclinic' ),
        'parent_item_colon'     => __( 'Parent Page:', 'vsquareclinic' ),
        'all_items'             => __( 'All Branch', 'vsquareclinic' ),
        'add_new_item'          => __( 'Add New Branch', 'vsquareclinic' ),
        'add_new'               => __( 'Add New', 'vsquareclinic' ),
        'new_item'              => __( 'New Branch', 'vsquareclinic' ),
        'edit_item'             => __( 'Edit Branch', 'vsquareclinic' ),
        'update_item'           => __( 'Update Branch', 'vsquareclinic' ),
        'view_item'             => __( 'View Branch', 'vsquareclinic' ),
        'view_items'            => __( 'View Branches', 'vsquareclinic' ),
        'search_items'          => __( 'Search Branch', 'vsquareclinic' ),
        'not_found'             => __( 'Not found', 'vsquareclinic' ),
        'not_found_in_trash'    => __( 'Not found in Trash', 'vsquareclinic' ),
        'featured_image'        => __( 'Featured Image', 'vsquareclinic' ),
        'set_featured_image'    => __( 'Set featured image', 'vsquareclinic' ),
        'remove_featured_image' => __( 'Remove featured image', 'vsquareclinic' ),
        'use_featured_image'    => __( 'Use as featured image', 'vsquareclinic' ),
        'insert_into_item'      => __( 'Insert into item', 'vsquareclinic' ),
        'uploaded_to_this_item' => __( 'Uploaded to this item', 'vsquareclinic' ),
        'items_list'            => __( 'Branches list', 'vsquareclinic' ),
        'items_list_navigation' => __( 'Branches list navigation', 'vsquareclinic' ),
        'filter_items_list'     => __( 'Filter items list', 'vsquareclinic' ),
    );
    $args = array(
        'label'                 => __( 'Branch', 'vsquareclinic' ),
        'description'           => __( 'Branch Description', 'vsquareclinic' ),
        'labels'                => $labels,
        'supports'              => array( 'title', 'editor', 'thumbnail', 'page-attributes', 'custom-fields', 'revisions', 'author' ),
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 44,
        'menu_icon'             => 'dashicons-admin-multisite',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => false,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'page',
        'show_in_rest'          => true,
        'rewrite'               => array( 'slug' => 'branch', 'with_front' => false ),
        'query_var'             => true,
    );
    register_post_type( 'page_branch', $args );

}
add_action( 'init', 'register_page_branch', 0 );


/**
 * Remove GeneratePress Layout Meta Box for Page Branch
 */
add_action( 'do_meta_boxes', 'remove_branch_layout_meta_box' );
function remove_branch_layout_meta_box() {
	remove_meta_box( 'generate_layout_options_meta_box', 'page_branch', 'side' );
}


/**
 * Set default layout for Page Branch to No Sidebars
 */
add_filter( 'generate_sidebar_layout', 'branch_default_sidebar_layout' );
function branch_default_sidebar_layout( $layout ) {
    // Check if we are on a single 'page_branch' post
    if ( is_singular( 'page_branch' ) ) {
        return 'no-sidebar';
    }
    return $layout;
}


/** 
 * Shortcode Display All Branches [branch_list] or [branch_list floor="yes"]
 */
function branch_list_shortcode( $atts ) {
    // Attributes
    $atts = shortcode_atts( array(
        'floor' => 'no',
        'thumbnail_name' => 'yes',
    ), $atts );

    $args = array(
        'post_type'      => 'page_branch',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'menu_order title',
        'order'          => 'ASC',
    );

    $query = new WP_Query( $args );

    if ( ! $query->have_posts() ) {
        return '<p>No branches found.</p>';
    }

    ob_start();
    ?>
    <div class="vsq-branch-grid">
        <?php while ( $query->have_posts() ) : $query->the_post(); 
            $post_id = get_the_ID();
            $thumbnail_id = get_post_meta( $post_id, '_branch_thumbnail', true );
            $thumbnail_url = $thumbnail_id ? wp_get_attachment_image_url( $thumbnail_id, 'full' ) : ''; 
            if ( ! $thumbnail_url && has_post_thumbnail() ) {
                $thumbnail_url = get_the_post_thumbnail_url( $post_id, 'full' );
            }
            $thumbnail_id_name = get_post_meta( $post_id, '_branch_thumbnail_name', true );
            $thumbnail_url_name = $thumbnail_id_name ? wp_get_attachment_image_url( $thumbnail_id_name , 'full' ) : ''; 
            if ( ! $thumbnail_url_name && has_post_thumbnail() ) {
                $thumbnail_url_name = get_the_post_thumbnail_url( $post_id, 'full' );
            }

            $title = get_post_meta( $post_id, '_branch_title', true );
            if ( ! $title ) $title = get_the_title();

            $floor = get_post_meta( $post_id, '_branch_title_floor', true );
            
            // Logic to swap title with floor if parameter is set
            $display_title = $title;
            if ( in_array( $atts['floor'], array('yes', 'true', '1') ) && ! empty( $floor ) ) {
                $display_title = $floor;
            }
            $thumbnail = $thumbnail_url_name;
            if ( in_array( $atts['thumbnail_name'], array('yes', 'true', '1') ) && ! empty( $thumbnail_url_name ) ) {
                $thumbnail = $thumbnail_url_name;
            } else {
                $thumbnail = $thumbnail_url;
            }

            $tel = get_post_meta( $post_id, '_branch_telephone', true );
            $line_id = get_post_meta( $post_id, '_branch_id_line', true );
            $line_url = get_post_meta( $post_id, '_branch_url_line', true );
            $map_url = get_post_meta( $post_id, '_branch_google_map', true );
            $opening_time = get_post_meta( $post_id, '_branch_opening_time', true );
            $first_opening_time = '';
            if ( ! empty( $opening_time ) && is_array( $opening_time ) && isset( $opening_time[0] ) ) {
                $time = isset( $opening_time[0]['time'] ) ? $opening_time[0]['time'] : '';
                if ( $time ) {
                    $first_opening_time = trim( $time );
                }
            }
            $link = get_permalink();
        ?>
        <div class="vsq-branch-card">
            <a href="<?php echo esc_url( $link ); ?>" class="vsq-branch-thumb">
                <?php if ( $thumbnail ) : ?>
                    <img src="<?php echo esc_url( $thumbnail ); ?>" alt="<?php echo esc_attr( $title ); ?>">
                <?php else : ?>
                    <div class="vsq-no-image">No Image</div>
                <?php endif; ?>
            </a>
            <div class="vsq-branch-info">
                <h3 class="vsq-branch-title">
                    <a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $display_title ); ?></a>
                </h3>

                <div class="vsq-branch-contact">
                    <?php if ( $first_opening_time ) : ?>
                        <div class="vsq-contact-item">
                            <span class="dashicons dashicons-clock"></span>
                            <span><?php echo esc_html( $first_opening_time ); ?></span>
                        </div>
                    <?php endif; ?>

                    <?php if ( $tel ) : ?>
                        <div class="vsq-contact-item">
                            <span class="dashicons dashicons-phone"></span>
                            <a href="tel:<?php echo esc_attr( $tel ); ?>"><?php echo esc_html( $tel ); ?></a>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ( $line_id ) : ?>
                        <div class="vsq-contact-item">
                            <span class="dashicons dashicons-admin-comments"></span>
                            <?php if ( $line_url ) : ?>
                                <a href="<?php echo esc_url( $line_url ); ?>" target="_blank"><?php echo esc_html( $line_id ); ?></a>
                            <?php else : ?>
                                <?php echo esc_html( $line_id ); ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="vsq-branch-actions">
                    <a href="<?php echo esc_url( $link ); ?>" class="button">รายละเอียด</a>
                    <?php if ( $map_url ) : ?>
                        <a href="<?php echo esc_url( $map_url ); ?>" target="_blank" class="button button-outline">แผนที่</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endwhile; wp_reset_postdata(); ?>
    </div>
    
    <style>
        .vsq-branch-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 30px;
            margin: 40px 0;
        }
        .vsq-branch-card {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            display: flex;
            flex-direction: column;
        }
        .vsq-branch-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .vsq-branch-thumb {
            display: block;
            width: 100%;
            height: 200px;
            background: #f9f9f9;
            overflow: hidden;
            position: relative;
        }
        .vsq-branch-thumb img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }
        .vsq-branch-card:hover .vsq-branch-thumb img {
            transform: scale(1.05);
        }
        .vsq-branch-info {
            padding: 20px;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        .vsq-branch-title {
            font-size: 1.3em;
            margin: 0 0 5px;
            line-height: 1.3;
            font-weight: 600;
        }
        .vsq-branch-title a {
            text-decoration: none;
            color: #333;
        }
        .vsq-branch-title a:hover {
            color: #d4af37;
        }
        .vsq-branch-floor {
            color: #777;
            font-size: 0.95em;
            margin-bottom: 15px;
        }
        .vsq-branch-contact {
            margin-bottom: 20px;
            font-size: 0.95em;
        }
        .vsq-contact-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            color: #555;
        }
        .vsq-contact-item .dashicons {
            color: #ccc;
        }
        .vsq-contact-item a {
            color: #555;
            text-decoration: none;
        }
        .vsq-contact-item a:hover {
            color: #d4af37;
        }
        .vsq-branch-actions {
            margin-top: auto;
            display: flex;
            gap: 10px;
        }
        .vsq-branch-actions .button {
            flex: 1;
            text-align: center;
            justify-content: center;
            font-size: 0.9em;
            padding: 8px 15px;
            border-radius: 4px;
        }
        .vsq-branch-actions .button-outline {
            background: transparent;
            border: 1px solid #ccc;
            color: #555;
        }
        .vsq-branch-actions .button-outline:hover {
            background: #f0f0f0;
            color: #333;
        }
        .vsq-no-image {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #999;
            background: #eee;
        }
        @media(max-width: 600px) {
            .vsq-branch-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    <?php
    return ob_get_clean();
}
add_shortcode( 'branch_list', 'branch_list_shortcode' );







/**
 * Create Shortcode [current_month]
 */
function wpb_current_month_shortcode() {
    $thai_months = array(
        '01' => 'มกราคม',
        '02' => 'กุมภาพันธ์',
        '03' => 'มีนาคม',
        '04' => 'เมษายน',
        '05' => 'พฤษภาคม',
        '06' => 'มิถุนายน',
        '07' => 'กรกฎาคม',
        '08' => 'สิงหาคม',
        '09' => 'กันยายน',
        '10' => 'ตุลาคม',
        '11' => 'พฤศจิกายน',
        '12' => 'ธันวาคม',
    );
    $current_month = date('m');
    return isset($thai_months[$current_month]) ? $thai_months[$current_month] : $current_month;
}
add_shortcode('current_month', 'wpb_current_month_shortcode');


/**
 * Create Shortcode [current_month_en]
 */
function wpb_current_month_en_shortcode() {
    $thai_months = array(
        '01' => 'January',
        '02' => 'February',
        '03' => 'March',
        '04' => 'April',
        '05' => 'May',
        '06' => 'June',
        '07' => 'July',
        '08' => 'August',
        '09' => 'September',
        '10' => 'October',
        '11' => 'November',
        '12' => 'December',
    );
    $current_month = date('m');
    return isset($thai_months[$current_month]) ? $thai_months[$current_month] : $current_month;
}
add_shortcode('current_month_en', 'wpb_current_month_en_shortcode');


/**
 * Create Shortcode [current_year]
 */
function wpb_current_year_shortcode() {
    return date('Y');
}
add_shortcode('current_year', 'wpb_current_year_shortcode');


/**
 * Create Shortcode [current_year_bracket] displaying year with brackets e.g. [2026]
 */
function wpb_current_year_bracket_shortcode() {
    return '[' . date('Y') . ']';
}
add_shortcode('current_year_bracket', 'wpb_current_year_bracket_shortcode');


/**
 * Allow Shortcodes in Titles
 */
function process_shortcode_in_title( $title ) {
    static $processing_shortcode = false;

    if ( $processing_shortcode ) {
        return $title;
    }

    if ( is_admin() ) {
        return $title;
    }

    $processing_shortcode = true;
    $title = do_shortcode( $title );
    $processing_shortcode = false;

    return $title;
}
add_filter('the_title', 'process_shortcode_in_title');


/**
 * Process shortcodes in Yoast Breadcrumbs
 */
function process_shortcodes_in_yoast_breadcrumbs( $links ) {
    if ( is_array( $links ) ) {
        foreach ( $links as $index => $link ) {
            if ( isset( $link['text'] ) ) {
                $links[$index]['text'] = do_shortcode( $link['text'] );
            }
        }
    }
    return $links;
}
add_filter( 'wpseo_breadcrumb_links', 'process_shortcodes_in_yoast_breadcrumbs' );


/**
 * Process shortcodes in Rank Math Breadcrumbs
 */
function process_shortcodes_in_rankmath_breadcrumbs( $crumbs, $class ) {
    if ( is_array( $crumbs ) ) {
        foreach ( $crumbs as $index => $crumb ) {
            // Rank Math usually stores text in element 0
            if ( isset( $crumb[0] ) ) {
                $crumbs[$index][0] = do_shortcode( $crumb[0] );
            }
        }
    }
    return $crumbs;
}
add_filter( 'rank_math/frontend/breadcrumb/items', 'process_shortcodes_in_rankmath_breadcrumbs', 10, 2 );


/**
 * Allow Shortcodes in ACF Fields (Text & Textarea)
 */
add_filter('acf/format_value/type=text', 'do_shortcode');
add_filter('acf/format_value/type=textarea', 'do_shortcode');
