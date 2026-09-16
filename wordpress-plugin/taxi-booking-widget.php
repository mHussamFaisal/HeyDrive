<?php
/**
 * Plugin Name: Taxi Dispatch Booking Widget
 * Plugin URI:  https://taxisdispatch.com
 * Description: Embeds the Taxi Dispatch System booking form on any WordPress page or post using [taxi_booking_form] shortcode.
 * Version:     1.0.0
 * Author:      Taxi Dispatch System
 * License:     GPL-2.0+
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Register Plugin Settings Page in WordPress Admin
add_action('admin_menu', function() {
    add_options_page('Taxi Dispatch Widget Settings', 'Taxi Booking Widget', 'manage_options', 'taxi-booking-widget', function() {
        if (isset($_POST['taxi_widget_submit'])) {
            check_admin_referer('taxi_widget_settings_nonce');
            update_option('taxi_widget_api_url', sanitize_text_field($_POST['taxi_widget_api_url']));
            update_option('taxi_widget_height', sanitize_text_field($_POST['taxi_widget_height']));
            echo '<div class="updated"><p><strong>Settings saved successfully!</strong></p></div>';
        }
        $url = get_option('taxi_widget_api_url', 'https://taxisdispatch.com/widget/booking.php');
        $height = get_option('taxi_widget_height', '520');
        ?>
        <div class="wrap">
            <h1>🚖 Taxi Dispatch Booking Widget Settings</h1>
            <form method="post">
                <?php wp_nonce_field('taxi_widget_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">Dispatch System Widget URL</th>
                        <td>
                            <input type="url" name="taxi_widget_api_url" value="<?php echo esc_attr($url); ?>" class="regular-text" style="width:100%;max-width:500px;" required>
                            <p class="description">Enter the full URL to <code>widget/booking.php</code> on your dispatch server.</p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Widget Height (px)</th>
                        <td>
                            <input type="number" name="taxi_widget_height" value="<?php echo esc_attr($height); ?>" class="small-text">
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings', 'primary', 'taxi_widget_submit'); ?>
            </form>

            <hr>

            <h2>How to Use Shortcode</h2>
            <p>Paste the shortcode below into any Elementor widget, Gutenberg block, or Classic Editor page:</p>
            <p><code style="font-size:16px;padding:6px 12px;">[taxi_booking_form]</code></p>
        </div>
        <?php
    });
});

// Register Shortcode [taxi_booking_form]
add_shortcode('taxi_booking_form', function($atts) {
    $atts = shortcode_atts([
        'url'    => get_option('taxi_widget_api_url', 'https://taxisdispatch.com/widget/booking.php'),
        'height' => get_option('taxi_widget_height', '460'),
        'width'  => '100%',
    ], $atts, 'taxi_booking_form');

    $iframe_url = esc_url($atts['url']);
    $h = esc_attr($atts['height']);
    $w = esc_attr($atts['width']);

    return '<div style="display:flex;justify-content:center;width:100%;padding:10px 0;">
        <iframe src="' . $iframe_url . '" width="' . $w . '" height="' . $h . '" frameborder="0" style="border:none;max-width:480px;width:100%;border-radius:20px;overflow:hidden;box-shadow:0 12px 30px rgba(0,0,0,0.15);" allowtransparency="true"></iframe>
    </div>';
});
