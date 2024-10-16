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
        status VARCHAR(255) NOT NULL DEFAULT 'pending',
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

    $results = $wpdb->get_results($wpdb->prepare("
        SELECT service, booking_time, message, status
        FROM $table_name
        WHERE user_id = %d
        ORDER BY booking_time DESC
    ", $user_id));

    return $results;
}

add_action('wp_ajax_your_ajax_action', 'form_success'); // For logged-in users
add_action('wp_ajax_nopriv_your_ajax_action', 'form_success'); // For logged-out users

function form_success() {
    // Validate your input and process data here
    $selected_service = isset($_POST['service']) ? sanitize_text_field($_POST['service']) : '';

    if (empty($selected_service)) {
        wp_send_json_error(['message' => 'Service is required.']);
        exit;
    }

    // Process booking, send email, etc.
    // Assuming you have code here to insert into the database
    $output = ob_get_clean();
    // After processing, send a JSON response
    wp_send_json_success(['message' => 'Your booking for "' . esc_html($selected_service) . '" has been successfully submitted.']);
}

function reset_booking_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'bookings';

    // Drop the existing table
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

    // Recreate the table
    create_booking_table(); // Call the existing function to recreate the table
}
