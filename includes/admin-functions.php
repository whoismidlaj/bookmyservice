<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once plugin_dir_path( __FILE__ ) . '../templates/admin-page.php';

function bms_add_admin_menu() {
    add_menu_page(
        'Book My Service',
        'Book My Service',
        'manage_options',
        'book-my-service',
        'bms_admin_page',
        'dashicons-book',
        6
    );
}
add_action( 'admin_menu', 'bms_add_admin_menu' );

function bms_enqueue_admin_assets() {
    wp_enqueue_style( 'bms-admin-styles', plugin_dir_url( __FILE__ ) . '../assets/css/admin-style.css' );
}
add_action( 'admin_enqueue_scripts', 'bms_enqueue_admin_assets' );

function bms_user_list() {
    $users = get_users( array( 'role' => 'bms_user' ) );

    echo '<h2>User List</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Username</th><th>Email</th></tr></thead><tbody>';

    foreach ( $users as $user ) {
        echo '<tr>';
        echo '<td>' . esc_html( $user->user_login ) . '</td>';
        echo '<td>' . esc_html( $user->user_email ) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

function bms_add_service() {
    if (isset($_POST['add_service'])) {
        $new_service = sanitize_text_field($_POST['new_service']);
        if (!empty($new_service)) {
            $services = get_option('bms_services', array());
            $services[] = $new_service;
            update_option('bms_services', $services);
            add_settings_error('bms_service_settings', 'service_added', 'Service added successfully.', 'updated');
        }
    }
}

function bms_delete_service() {
    if (isset($_POST['delete_service'])) {
        $service_key = sanitize_text_field($_POST['service_key']);
        $services = get_option('bms_services', array());
        
        if (isset($services[$service_key])) {
            unset($services[$service_key]);
            $services = array_values($services);
            update_option('bms_services', $services);
            add_settings_error('bms_service_settings', 'service_deleted', 'Service deleted successfully.', 'updated');
        }
    }
}

function bms_submission_email() {
    if (isset($_POST['bms_email'])) {
        $email = sanitize_email($_POST['bms_email']);
        update_option('bms_admin_email', $email);
    
        echo '<p>Email saved successfully: ' . esc_html($email) . '</p>';
    }
}


function bms_save_google_oauth_credentials() {
    if (isset($_POST['bms_client_id']) && !empty($_POST['bms_client_id'])) {
        $bms_client_id = sanitize_text_field($_POST['bms_client_id']);
        update_option('bms_client_id', $bms_client_id);
        echo '<p>Client ID saved successfully: ' . esc_html($bms_client_id) . '</p>';
    }

    if (isset($_POST['bms_client_secret']) && !empty($_POST['bms_client_secret'])) {
        $bms_client_secret = sanitize_text_field($_POST['bms_client_secret']);
        update_option('bms_client_secret', $bms_client_secret);
        echo '<p>Client Secret saved successfully: ' . esc_html($bms_client_secret) . '</p>';
    }
}