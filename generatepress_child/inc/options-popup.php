<?php
/**
 * Popup Options Page
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// 1. Add Menu
function popup_add_admin_menu() {
    add_menu_page(
        'Popup',
        'Popup',
        'manage_options',
        'popup-settings',
        'popup_options_page_html',
        'dashicons-buddicons-tracking',
        51
    );
}
add_action( 'admin_menu', 'popup_add_admin_menu' );

// 2. Register Setting
function popup_settings_init() {
    register_setting( 'popup_option_group', 'popup_data' );
}
add_action( 'admin_init', 'popup_settings_init' );

// 3. Enqueue Assets
function popup_admin_assets( $hook ) {
    if ( 'toplevel_page_popup-settings' !== $hook ) {
        return;
    }

    wp_enqueue_media();
    
    wp_enqueue_style( 
        'popup-admin-css', 
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-popup.css', 
        array(), 
        filemtime( get_stylesheet_directory() . '/assets/css/admin/admin-popup.css' ) 
    );

    wp_enqueue_script( 
        'popup-admin-js', 
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-popup.js', 
        array( 'jquery' ), 
        filemtime( get_stylesheet_directory() . '/assets/js/admin/admin-popup.js' ), 
        true 
    );
}
add_action( 'admin_enqueue_scripts', 'popup_admin_assets' );

// 4. Render Page
function popup_options_page_html() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $data = get_option( 'popup_data', array() );
    
    // Ensure data structure for each language
    $data_th = isset($data['th']) ? $data['th'] : array();
    $data_en = isset($data['en']) ? $data['en'] : array();
    $data_cn = isset($data['cn']) ? $data['cn'] : array();
    ?>
    <div class="wrap">
        <h1>Popup Options</h1>
        
        <?php 
        if ( isset( $_GET['settings-updated'] ) ) {
            add_settings_error( 'popup_data', 'popup_message', 'Settings Saved', 'updated' );
        }
        settings_errors( 'popup_data' );
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields( 'popup_option_group' );
            do_settings_sections( 'popup-settings' );
            ?>
            
            <div class="popup-container">
                <!-- TH Section -->
                <div class="popup-section">
                    <div class="popup-heading">
                        <h2>Popup (TH)</h2>
                        <p><strong>Shortcode Usage:</strong> <code>[popup_th]</code></p>
                    </div>
                    <?php popup_render_fields( $data_th, 'th' ); ?>
                </div>

                <!-- EN Section -->
                <div class="popup-section">
                    <div class="popup-heading">
                        <h2>Popup (EN)</h2>
                        <p><strong>Shortcode Usage:</strong> <code>[popup_en]</code></p>
                    </div>
                    <?php popup_render_fields( $data_en, 'en' ); ?>
                </div>

                <!-- CN Section -->
                <div class="popup-section">
                    <div class="popup-heading">
                        <h2>Popup (CN)</h2>
                        <p><strong>Shortcode Usage:</strong> <code>[popup_cn]</code></p>
                    </div>
                    <?php popup_render_fields( $data_cn, 'cn' ); ?>
                </div>
            </div>
            <?php 
            // Check if user is a sender
            $is_sender = false;
            if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
                $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
                $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
            } 

            if ( $is_sender ) { 
                submit_button();
            }
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render Fields Function
 * @param array $data
 * @param string $lang (th, en, cn)
 */
function popup_render_fields( $data, $lang ) {
    $image_id = isset( $data['image_id'] ) ? $data['image_id'] : '';
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';
    $auto_close = isset( $data['auto_close'] ) ? $data['auto_close'] : '';
    $enable = isset( $data['enable'] ) ? $data['enable'] : 'disable';
    $start_date = isset( $data['start_date'] ) ? $data['start_date'] : '';
    $end_date = isset( $data['end_date'] ) ? $data['end_date'] : '';
    
    // Prefix: popup_data[th]
    $prefix = "popup_data[{$lang}]";
    $unique_id = $lang . '_popup';

    // Check if user is a sender
    $is_sender = false;
    if ( defined( 'VSQ_SYNC_OPTION_KEY' ) ) {
        $vsq_settings = get_option( VSQ_SYNC_OPTION_KEY, array() );
        $is_sender = isset( $vsq_settings['role'] ) && $vsq_settings['role'] === 'sender';
    } 
    ?>
    <div class="popup-fields">
        <!-- 1. Image -->
        <div class="popup-field popup-field-image">
            <label>Image</label>
            <div class="popup-image-preview">
                <?php if($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>">
                <?php else: ?>
                    <span class="dashicons dashicons-format-image" style="color:#ddd; font-size: 30px; width:30px; height:30px;"></span>
                <?php endif; ?>
            </div>
            <input type="hidden" name="<?php echo $prefix; ?>[image_id]" class="popup-image-id" value="<?php echo esc_attr($image_id); ?>">
            <?php if ( $is_sender ) { ?>
            <button type="button" class="button upload-popup-image" style="width:100%;"><?php echo $image_id ? 'Change Image' : 'Select Image'; ?></button>
            <?php } ?>
        </div>

        <!-- Schedule -->
        <div class="popup-field popup-field-schedule">
            <label>Schedule (Start - End)</label>
            <div class="popup-field-schedule-container">
                <div class="popup-field-schedule-item">
                    <span>Start Date</span>
                    <input type="datetime-local" name="<?php echo $prefix; ?>[start_date]" value="<?php echo esc_attr($start_date); ?>" lang="en-GB" step="60"<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>>
                </div>
                <div class="popup-field-schedule-item">
                    <span>End Date</span>
                    <input type="datetime-local" name="<?php echo $prefix; ?>[end_date]" value="<?php echo esc_attr($end_date); ?>" lang="en-GB" step="60"<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>>
                </div>
            </div>
            <p class="description" style="font-size: 11px; margin-top: 5px;">Leave empty to always show (if enabled).</p>
        </div>

        <div class="popup-field-wrapper">
            <div class="popup-field-column">
                <!-- Auto Close Delay -->
                <div class="popup-field">
                    <label>Auto Close Delay</label>
                    <div class="popup-field-auto-close">
                        <input type="number" name="<?php echo $prefix; ?>[auto_close]" value="<?php echo esc_attr($auto_close); ?>" min="0" max="10000"<?php if ( ! $is_sender ) { ?> class="hide-click" readonly<?php } ?>>
                        <span class="popup-field-auto-close-unit">Seconds</span>
                    </div>
                </div>
            </div>
            <div class="popup-field-column">
                <!-- Enable -->
                <div class="popup-field popup-field-switch">
                    <label>Status</label>
                    <div class="switch-field<?php if ( ! $is_sender ) { ?> hide-click<?php } ?>">
                        <input type="radio" id="enable_<?php echo $unique_id; ?>_on" name="<?php echo $prefix; ?>[enable]" value="enable" <?php checked( $enable, 'enable' ); ?><?php if ( ! $is_sender ) { ?> class="hide-click" onclick="return false;"<?php } ?> />
                        <label for="enable_<?php echo $unique_id; ?>_on">Enable</label>
                        <input type="radio" id="enable_<?php echo $unique_id; ?>_off" name="<?php echo $prefix; ?>[enable]" value="disable" <?php checked( $enable, 'disable' ); ?><?php if ( ! $is_sender ) { ?> class="hide-click" onclick="return false;"<?php } ?> />
                        <label for="enable_<?php echo $unique_id; ?>_off">Disable</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * 5. Shortcode Implementation
 * [popup_th]
 * [popup_en]
 * [popup_cn]
 */
function popup_shortcode( $atts, $content = null, $tag = '' ) {
    // Determine language from tag
    $lang = 'th';
    if ( $tag === 'popup_en' ) {
        $lang = 'en';
    } elseif ( $tag === 'popup_cn' ) {
        $lang = 'cn';
    }

    $data = get_option( 'popup_data', array() );
    $item = isset($data[$lang]) ? $data[$lang] : array();

    // Check Status
    $enable = isset( $item['enable'] ) ? $item['enable'] : 'disable';
    if ( $enable === 'disable' ) {
        return '';
    }

    // Timezone Setup
    try {
        $tz = new DateTimeZone('Asia/Bangkok');
    } catch (Exception $e) {
        $tz = new DateTimeZone('UTC');
    }

    $now = new DateTime('now', $tz);
    $current_timestamp = $now->getTimestamp() + $now->getOffset();
    
    // Start Time Logic
    $start_date = isset( $item['start_date'] ) ? $item['start_date'] : '';
    if ( $start_date ) {
        try {
            // datetime-local input sends format like 'Y-m-d\TH:i'
            $start_dt = new DateTime( $start_date, $tz );
            $start_ts = $start_dt->getTimestamp() + $now->getOffset();
            
            if ( $start_ts && $current_timestamp < $start_ts ) {
                return '';
            }
        } catch (Exception $e) { }
    }

    // End Time Logic
    $end_date = isset( $item['end_date'] ) ? $item['end_date'] : '';
    if ( $end_date ) {
        try {
            $end_dt = new DateTime( $end_date, $tz );
            $end_ts = $end_dt->getTimestamp() + $now->getOffset();
            
            if ( $end_ts && $current_timestamp > $end_ts ) {
                return '';
            }
        } catch (Exception $e) { }
    }

    // Get Image
    $image_id = isset( $item['image_id'] ) ? $item['image_id'] : '';
    $image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

    // Get Auto Close Delay
    $auto_close = isset( $item['auto_close'] ) ? intval($item['auto_close']) : 0;

    if ( ! $image_url ) {
        return '';
    }

    ob_start();
    ?>
    <div class="popup-content">
        <div class="popup-content-container">
            <button class="popup-content-close">
                <i class="fa-solid fa-xmark"></i>
            </button>
            <div class="popup-content-content">
                <div class="popup-content-img">
                    <?php echo wp_get_attachment_image( $image_id, 'full', false, array( 'class' => '' ) ); ?>
                </div>
            </div>
            <div class="popup-content-description">
                <div class="popup-content-checkbox">
                    <input type="checkbox" id="hidepopup" name="hidepopup" value="true">
                    <div class="popup-content-checkbox-detail">
                        <i class="popup-content-checkbox-icon"></i>
                        <p><?php echo $lang === 'en' ? 'Don\'t show again' : ($lang === 'cn' ? '不要再次显示' : 'ไม่ต้องแสดงอีก'); ?></p>
                    </div>
                </div>
                <?php if ( $auto_close > 0 ) : ?>
                    <div class="popup-content-countdown">
                        <?php echo $lang === 'en' ? 'Auto close in' : ($lang === 'cn' ? '自动关闭' : 'ปิดอัตโนมัติใน'); ?> 
                        <span id="popup-countdown-number"><?php echo $auto_close; ?></span>s
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            function getCookie(name) {
                var v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
                return v ? v[2] : null;
            }

            function setCookie(name, value, days) {
                var d = new Date();
                d.setTime(d.getTime() + 24 * 60 * 60 * 1000 * days); // 1 Day
                var domain = window.location.hostname;
                document.cookie = name + "=" + value + ";path=/;expires=" + d.toGMTString() + ";domain=" + domain + ";secure";
            }

            var cookieName = 'hidepopup';
            var cookieHidePopup = getCookie(cookieName);
            var popup = document.querySelector('.popup-content');
            var closeBtn = document.querySelector('.popup-content-close');
            var checkbox = document.getElementById('hidepopup');

            if (!cookieHidePopup && popup) {
                popup.classList.add('is-open');
            }

            if (checkbox) {
                checkbox.addEventListener('change', function() {
                    if (this.checked) {
                        setCookie(cookieName, 'true', 1);
                    } else {
                        setCookie(cookieName, '', -1);
                    }
                });
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (popup) {
                        popup.classList.remove('is-open');
                    }
                });
            }

            if (popup) {
                popup.addEventListener('click', function(e) {
                    if (!e.target.closest('.popup-content-container')) {
                         popup.classList.remove('is-open');
                    }
                });
            }

            <?php if (isset($auto_close) && $auto_close > 0): ?>
                if (!cookieHidePopup && popup) {
                    var timeLeft = <?php echo $auto_close; ?>;
                    var countdownElement = document.getElementById('popup-countdown-number');
                    
                    var countdownInterval = setInterval(function() {
                        timeLeft--;
                        if (countdownElement) {
                            countdownElement.textContent = timeLeft;
                        }
                        
                        if (timeLeft <= 0) {
                            clearInterval(countdownInterval);
                            popup.classList.remove('is-open');
                        }
                    }, 1000);
                }
            <?php endif; ?>
        });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode( 'popup_th', 'popup_shortcode' );
add_shortcode( 'popup_en', 'popup_shortcode' );
add_shortcode( 'popup_cn', 'popup_shortcode' );


// Enqueue Frontend Assets for Shortcode
function popup_frontend_assets()
{
    global $post;
    if (is_a($post, 'WP_Post') && has_shortcode($post->post_content, 'popup_th') || has_shortcode($post->post_content, 'popup_en') || has_shortcode($post->post_content, 'popup_cn')) {
        wp_enqueue_style('popup-style', get_stylesheet_directory_uri() . '/assets/css/popup.css', array(), filemtime( get_stylesheet_directory() . '/assets/css/popup.css' ));
    }
}
add_action('wp_enqueue_scripts', 'popup_frontend_assets', 99);