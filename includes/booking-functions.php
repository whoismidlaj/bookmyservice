<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function create_booking_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'bookings';

    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) NOT NULL,
        service VARCHAR(255) NOT NULL,
        booking_time DATETIME NOT NULL,
        message  TEXT NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

register_activation_hook(__FILE__, 'create_booking_table');


function bms_booking_page_shortcode()
{
    ob_start();
    include plugin_dir_path(__FILE__) . '../templates/booking-page.php';
    return ob_get_clean();
}
add_shortcode('book_my_service', 'bms_booking_page_shortcode');

function bms_get_user_booked_services($user_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bookings';

    // Query to fetch bookings for the logged-in user
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT service, booking_time, message
        FROM $table_name
        WHERE user_id = %d
        ORDER BY booking_time DESC
    ", $user_id));

    return $results;
}
