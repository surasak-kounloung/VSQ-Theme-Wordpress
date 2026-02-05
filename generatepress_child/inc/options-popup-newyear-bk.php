<?php

/**
 * Popup Newyear Options Page
 */

if (! defined('ABSPATH')) {
    exit;
}

// 1. Add Menu
function popup_newyear_add_admin_menu()
{
    add_menu_page(
        'Popup Newyear',
        'Popup Newyear',
        'manage_options',
        'popup-newyear-settings',
        'popup_newyear_options_page_html',
        'dashicons-buddicons-tracking',
        51
    );
}
add_action('admin_menu', 'popup_newyear_add_admin_menu');

// 2. Register Setting
function popup_newyear_settings_init()
{
    register_setting('popup_newyear_option_group', 'popup_newyear_data');
}
add_action('admin_init', 'popup_newyear_settings_init');

// 3. Enqueue Assets
function popup_newyear_admin_assets($hook)
{
    if ('toplevel_page_popup-newyear-settings' !== $hook) {
        return;
    }

    wp_enqueue_media();

    wp_enqueue_style(
        'popup-newyear-admin-css',
        get_stylesheet_directory_uri() . '/assets/css/admin/admin-popup-newyear.css',
        array(),
        filemtime(get_stylesheet_directory() . '/assets/css/admin/admin-popup-newyear.css')
    );

    wp_enqueue_script(
        'popup-newyear-admin-js',
        get_stylesheet_directory_uri() . '/assets/js/admin/admin-popup-newyear.js',
        array('jquery'),
        filemtime(get_stylesheet_directory() . '/assets/js/admin/admin-popup-newyear.js'),
        true
    );
}
add_action('admin_enqueue_scripts', 'popup_newyear_admin_assets');

// 4. Render Page
function popup_newyear_options_page_html()
{
    if (! current_user_can('manage_options')) {
        return;
    }

    $data = get_option('popup_newyear_data', array());

    // Ensure data structure for each language
    $data_th = isset($data['th']) ? $data['th'] : array();
    $data_en = isset($data['en']) ? $data['en'] : array();
    $data_cn = isset($data['cn']) ? $data['cn'] : array();
?>
    <div class="wrap">
        <h1>Popup Newyear Options</h1>

        <?php
        if (isset($_GET['settings-updated'])) {
            add_settings_error('popup_newyear_data', 'popup_newyear_message', 'Settings Saved', 'updated');
        }
        settings_errors('popup_newyear_data');
        ?>

        <form action="options.php" method="post">
            <?php
            settings_fields('popup_newyear_option_group');
            do_settings_sections('popup-newyear-settings');
            ?>

            <div class="popup-newyear-container">
                <!-- TH Section -->
                <div class="popup-section">
                    <h2>Popup (TH)</h2>
                    <?php popup_newyear_render_fields($data_th, 'th'); ?>
                </div>

                <!-- EN Section -->
                <div class="popup-section">
                    <h2>Popup (EN)</h2>
                    <?php popup_newyear_render_fields($data_en, 'en'); ?>
                </div>

                <!-- CN Section -->
                <div class="popup-section">
                    <h2>Popup (CN)</h2>
                    <?php popup_newyear_render_fields($data_cn, 'cn'); ?>
                </div>
            </div>

            <?php submit_button(); ?>
        </form>
    </div>
<?php
}

/**
 * Render Fields Function
 * @param array $data
 * @param string $lang (th, en, cn)
 */
function popup_newyear_render_fields($data, $lang)
{
    $image_id = isset($data['image_id']) ? $data['image_id'] : '';
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';
    $enable = isset($data['enable']) ? $data['enable'] : 'disable';
    $start_date = isset($data['start_date']) ? $data['start_date'] : '';
    $end_date = isset($data['end_date']) ? $data['end_date'] : '';

    // Prefix: popup_newyear_data[th]
    $prefix = "popup_newyear_data[{$lang}]";
    $unique_id = $lang . '_popup';
?>
    <div class="popup-fields">
        <!-- 1. Image -->
        <div class="popup-field popup-field-image">
            <label>Image</label>
            <div class="popup-image-preview">
                <?php if ($image_url): ?>
                    <img src="<?php echo esc_url($image_url); ?>">
                <?php else: ?>
                    <span class="dashicons dashicons-format-image" style="color:#ddd; font-size: 30px; width:30px; height:30px;"></span>
                <?php endif; ?>
            </div>
            <input type="hidden" name="<?php echo $prefix; ?>[image_id]" class="popup-image-id" value="<?php echo esc_attr($image_id); ?>">
            <button type="button" class="button upload-popup-image" style="width:100%;"><?php echo $image_id ? 'Change Image' : 'Select Image'; ?></button>
        </div>

        <!-- 2. Enable -->
        <div class="popup-field popup-field-switch">
            <label>Status</label>
            <div class="switch-field">
                <input type="radio" id="enable_<?php echo $unique_id; ?>_on" name="<?php echo $prefix; ?>[enable]" value="enable" <?php checked($enable, 'enable'); ?> />
                <label for="enable_<?php echo $unique_id; ?>_on">Enable</label>
                <input type="radio" id="enable_<?php echo $unique_id; ?>_off" name="<?php echo $prefix; ?>[enable]" value="disable" <?php checked($enable, 'disable'); ?> />
                <label for="enable_<?php echo $unique_id; ?>_off">Disable</label>
            </div>
        </div>

        <!-- 3. Schedule -->
        <div class="popup-field popup-field-schedule">
            <label>Schedule (Start - End)</label>
            <div class="popup-field-schedule-container">
                <div class="popup-field-schedule-item">
                    <span>Start Date</span>
                    <input type="datetime-local" name="<?php echo $prefix; ?>[start_date]" value="<?php echo esc_attr($start_date); ?>" lang="en-GB" step="60">
                </div>
                <div class="popup-field-schedule-item">
                    <span>End Date</span>
                    <input type="datetime-local" name="<?php echo $prefix; ?>[end_date]" value="<?php echo esc_attr($end_date); ?>" lang="en-GB" step="60">
                </div>
            </div>
            <p class="description" style="font-size: 11px; margin-top: 5px;">Leave empty to always show (if enabled).</p>
        </div>
    </div>
<?php
}

/**
 * 5. Shortcode Implementation
 * [popup_newyear_th]
 * [popup_newyear_en]
 * [popup_newyear_cn]
 */
function popup_newyear_shortcode($atts, $content = null, $tag = '')
{
    // Determine language from tag
    $lang = 'th';
    if ($tag === 'popup_newyear_en') {
        $lang = 'en';
    } elseif ($tag === 'popup_newyear_cn') {
        $lang = 'cn';
    }

    $data = get_option('popup_newyear_data', array());
    $item = isset($data[$lang]) ? $data[$lang] : array();

    // Check Status
    $enable = isset($item['enable']) ? $item['enable'] : 'disable';
    if ($enable === 'disable') {
        return '';
    }

    // Timezone Setup
    try {
        $tz = new DateTimeZone('Asia/Bangkok');
    } catch (Exception $e) {
        $tz = new DateTimeZone('UTC');
    }

    $now = new DateTime('now', $tz);
    $current_timestamp = $now->getTimestamp();

    // Start Time Logic
    $start_date = isset($item['start_date']) ? $item['start_date'] : '';
    if ($start_date) {
        try {
            // datetime-local input sends format like 'Y-m-d\TH:i'
            $start_dt = new DateTime($start_date, $tz);
            $start_ts = $start_dt->getTimestamp();

            if ($start_ts && $current_timestamp < $start_ts) {
                return '';
            }
        } catch (Exception $e) {
        }
    }

    // End Time Logic
    $end_date = isset($item['end_date']) ? $item['end_date'] : '';
    if ($end_date) {
        try {
            $end_dt = new DateTime($end_date, $tz);
            $end_ts = $end_dt->getTimestamp();

            if ($end_ts && $current_timestamp > $end_ts) {
                return '';
            }
        } catch (Exception $e) {
        }
    }

    // Get Image
    $image_id = isset($item['image_id']) ? $item['image_id'] : '';
    $image_url = $image_id ? wp_get_attachment_image_url($image_id, 'full') : '';

    if (! $image_url) {
        return '';
    }

    // Unique ID for this popup instance
    $popup_id = 'vsq-popup-newyear-' . $lang;

    ob_start();
?>
    <div id="<?php echo esc_attr($popup_id); ?>" class="vsq-popup-overlay" style="display:none;">
        <div class="vsq-popup-content">
            <span class="vsq-popup-close">&times;</span>
            <div><?php echo '$current_time: ' . date('Y-m-d H:i:s', $current_time); ?></div>
            <div><?php echo '$start_date: ' . date('Y-m-d H:i:s', strtotime($start_date)); ?></div>
            <div><?php echo '$end_date: ' . date('Y-m-d H:i:s', strtotime($end_date)); ?></div>
            <img src="<?php echo esc_url($image_url); ?>" alt="New Year Popup">
        </div>
    </div>

    <div class="popup-new-year">
        <div class="popup-new-year-container">
            <button class="popup-new-year-close"></button>
            <div class="popup-new-year-content">
                <div class="popup-new-year-img">
                    <img width="1200" height="1200" src="https://vsq-injector.com/wp-content/uploads/2025/12/สาขาเปิดปีใหม่_2569_size1-1-TH.jpg" class="attachment-full size-full" alt="" decoding="async" loading="lazy" srcset="https://vsq-injector.com/wp-content/uploads/2025/12/สาขาเปิดปีใหม่_2569_size1-1-TH.jpg 1200w, https://vsq-injector.com/wp-content/uploads/2025/12/สาขาเปิดปีใหม่_2569_size1-1-TH-150x150.jpg 150w, https://vsq-injector.com/wp-content/uploads/2025/12/สาขาเปิดปีใหม่_2569_size1-1-TH-768x768.jpg 768w" sizes="auto, (max-width: 1200px) 100vw, 1200px">
                </div>
            </div>
            <div class="popup-new-year-description">
                <div class="popup-new-year-checkbox">
                    <input type="checkbox" id="hidepopupnewyear" name="hidepopupnewyear" value="1">
                    <div class="popup-new-year-checkbox-detail">
                        <i class="popup-new-year-checkbox-icon"></i>
                        <p>ไม่ต้องแสดงอีก</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        jQuery(document).ready(function($) {
            var popupId = '#<?php echo $popup_id; ?>';
            var storageKey = 'vsq_popup_closed_' + '<?php echo $lang; ?>';

            // Function to get cookie/storage
            if (!sessionStorage.getItem(storageKey)) {
                $(popupId).fadeIn();
            }

            // Close button
            $(popupId + ' .vsq-popup-close, ' + popupId).on('click', function(e) {
                if (e.target !== this && !$(e.target).hasClass('vsq-popup-close')) return;
                if ($(e.target).closest('.vsq-popup-content').length && !$(e.target).hasClass('vsq-popup-close')) {
                    return;
                }

                $(popupId).fadeOut();
                sessionStorage.setItem(storageKey, 'true');
            });
        });
    </script>

    <style>
        /* Popup New Year */
        .popup-new-year {
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
            height: 100dvh;
            opacity: 0;
            visibility: hidden;
            pointer-events: none;
            z-index: 9999;
            background-color: rgba(0, 0, 0, 0.7);
            transition: all 0.6s ease;
        }

        .popup-new-year.is-open {
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        .popup-new-year-container {
            position: relative;
            top: -4vh;
            margin: 0 auto;
            background-color: #fff;
            transition: all 0.6s ease;
        }

        .popup-new-year.is-open .popup-new-year-container {
            top: 0;
        }

        .popup-new-year-close {
            position: absolute;
            top: 0;
            right: -37px;
            width: 24px;
            height: 24px;
            padding: 0;
            margin: 0;
            border: 0;
            cursor: pointer;
            z-index: 9;
            background-color: transparent;
        }

        .popup-new-year-close:before,
        .popup-new-year-close:after {
            content: "";
            position: absolute;
            top: 0;
            width: 3px;
            height: 100%;
            border-radius: 50px;
            background-color: #fff;
            transition: background-color 0.25s ease;
        }

        .popup-new-year-close:before {
            left: 50%;
            transform: rotate(45deg) translateX(-50%);
        }

        .popup-new-year-close:after {
            right: 50%;
            transform: rotate(-45deg) translateX(50%);
        }

        .popup-new-year-close:hover:before,
        .popup-new-year-close:hover:after {
            background-color: #435C9E;
        }

        .popup-new-year-content {
            width: auto;
            max-width: 75vw;
            height: 75dvh;
        }

        .popup-new-year-img,
        .popup-new-year-img picture {
            width: auto;
            height: 100%;
            margin: 0 auto;
        }

        .popup-new-year-img img {
            width: auto;
            height: 100%;
            margin: 0;
        }

        .popup-new-year-description {
            position: relative;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 12px 16px;
            background-color: #222;
        }

        .popup-new-year-checkbox {
            position: relative;
        }

        .popup-new-year-checkbox input[type=checkbox] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
            opacity: 0;
            z-index: 9;
        }

        .popup-new-year-checkbox-detail {
            position: relative;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            font-family: "kanit", sans-serif !important;
            font-size: 16px;
            color: #fff;
            font-weight: 400;
            line-height: 1;
        }

        .popup-new-year-checkbox-icon {
            position: relative;
            width: 20px;
            height: 20px;
            margin-right: 10px;
            border-radius: 4px;
            border: 1px solid #999;
            background-color: #fff;
        }

        .popup-new-year-checkbox-icon:before {
            content: "";
            position: absolute;
            top: 3px;
            left: 2px;
            width: 14px;
            height: 8px;
            opacity: 0;
            border: 2px solid #5badff;
            border-width: 0 0 3px 3px;
            transform: rotate(-45deg);
            transition: all 0.25s ease;
        }

        .popup-new-year-checkbox input[type=checkbox]:checked+.popup-new-year-checkbox-detail .popup-new-year-checkbox-icon:before {
            opacity: 1;
        }

        .popup-new-year-checkbox-detail p {
            font-size: inherit;
            color: inherit;
            font-weight: inherit;
            line-height: inherit;
            letter-spacing: normal;
            margin: 0;
        }

        @media (max-width: 1919px) {
            .popup-new-year-close {
                right: -1.9270833333vw;
                width: 1.25vw;
                height: 1.25vw;
            }

            .popup-new-year-close:before,
            .popup-new-year-close:after {
                width: 0.15625vw;
                border-radius: 2.6041666667vw;
            }

            .popup-new-year-description {
                padding: 0.625vw 0.8333333333vw;
            }

            .popup-new-year-checkbox-detail {
                font-size: 0.8333333333vw;
            }

            .popup-new-year-checkbox-icon {
                width: 1.0416666667vw;
                height: 1.0416666667vw;
                margin-right: 0.5208333333vw;
                border-radius: 0.2083333333vw;
            }

            .popup-new-year-checkbox-icon:before {
                top: 0.15625vw;
                left: 0.1041666667vw;
                width: 0.7291666667vw;
                height: 0.4166666667vw;
                border-width: 0 0 0.15625vw 0.15625vw;
            }
        }

        @media (max-width: 768px) {
            .popup-new-year-close {
                top: -6.25vw;
                right: -0.5208333333vw;
                width: 4.4270833333vw;
                height: 4.4270833333vw;
            }

            .popup-new-year-close:before,
            .popup-new-year-close:after {
                width: 0.390625vw;
                border-radius: 6.5104166667vw;
            }

            .popup-new-year-close:hover:before,
            .popup-new-year-close:hover:after {
                background-color: #fff;
            }

            .popup-new-year-close:active:before,
            .popup-new-year-close:active:after {
                background-color: #435C9E;
            }

            .popup-new-year-content {
                width: auto;
                max-width: 90vw;
                height: auto;
            }

            .popup-new-year-img,
            .popup-new-year-img picture {
                width: 100%;
                height: auto;
            }

            .popup-new-year-img img {
                width: 100%;
                height: auto;
            }

            .popup-new-year-description {
                padding: 1.953125vw 2.34375vw;
            }

            .popup-new-year-checkbox-detail {
                font-size: 2.6041666667vw;
            }

            .popup-new-year-checkbox-icon {
                width: 3.125vw;
                height: 3.125vw;
                margin-right: 1.5625vw;
                border-radius: 0.5208333333vw;
            }

            .popup-new-year-checkbox-icon:before {
                top: 0.5208333333vw;
                left: 0.390625vw;
                width: 2.0833333333vw;
                height: 1.3020833333vw;
                border-width: 0 0 0.390625vw 0.390625vw;
            }
        }
        /* END Popup New Year */
    </style>
<?php
    return ob_get_clean();
}
add_shortcode('popup_newyear_th', 'popup_newyear_shortcode');
add_shortcode('popup_newyear_en', 'popup_newyear_shortcode');
add_shortcode('popup_newyear_cn', 'popup_newyear_shortcode');
