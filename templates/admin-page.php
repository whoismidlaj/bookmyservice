<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bms_admin_page() {
    ?>
    <div class="wrap">
        <h1>Book My Service Admin</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=book-my-service&tab=bookings" class="nav-tab <?php echo ( isset( $_GET['tab']) && $_GET['tab'] === 'bookings' ) ? 'nav-tab-active' : ''; ?>">Bookings</a>
            <a href="?page=book-my-service&tab=user-list" class="nav-tab <?php echo ( isset( $_GET['tab']) && $_GET['tab'] === 'user-list' ) ? 'nav-tab-active' : ''; ?>">User List</a>
            <a href="?page=book-my-service&tab=options" class="nav-tab <?php echo ( isset( $_GET['tab']) && $_GET['tab'] === 'options' ) ? 'nav-tab-active' : ''; ?>">Options</a>
        </h2>

        <?php
        if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'user-list' ) {
            bms_user_list();
        } else if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'options' ) {
            bms_other_options();
        } else {
            display_bookings();
        }
        ?>
    </div>
    <?php
}

function display_bookings() {
    if (!is_user_logged_in() || !current_user_can('manage_options')) {
        return;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'bookings';

    $bookings = $wpdb->get_results("SELECT b.id, b.service, b.booking_time, u.user_email, u.ID as user_id FROM $table_name b JOIN $wpdb->users u ON b.user_id = u.ID ORDER BY b.booking_time DESC", ARRAY_A);

    if (!empty($bookings)) {
        echo '<h2>All Bookings</h2>';
        echo '<table class="widefat fixed" cellspacing="0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Name</th>';
        echo '<th scope="col">Email</th>';
        echo '<th scope="col">Phone</th>';
        echo '<th scope="col">Service Selected</th>';
        echo '<th scope="col">Booking Time</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($bookings as $booking) {
            $first_name = get_user_meta($booking['user_id'], 'first_name', true);
            $phone = get_user_meta($booking['user_id'], 'phone', true);

            echo '<tr>';
            echo '<td>' . esc_html($first_name) . '</td>';
            echo '<td>' . esc_html($booking['user_email']) . '</td>';
            echo '<td>' . esc_html($phone) . '</td>';
            echo '<td>' . esc_html($booking['service']) . '</td>';
            echo '<td>' . esc_html(date('Y-m-d H:i', strtotime($booking['booking_time']))) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<h2>No bookings found.</h2>';
    }
}

function bms_other_options() {
    bms_save_mailerlite_settings();

    bms_add_service();

    bms_delete_service();

    $mailerlite_api_key = get_option('mailerlite_api_key', '');
    $mailerlite_group_id = get_option('mailerlite_group_id', '');
    $services = get_option('bms_services', array());

    echo '<h2>Options</h2>';
    echo '<p>This is where other settings or options can be managed.</p>';
    
    echo '<form method="POST" action="">';
    echo '<table class="form-table">';
    
    echo '<tr>';
    echo '<th scope="row"><label for="mailerlite_api_key">MailerLite API Key</label></th>';
    echo '<td><input type="text" id="mailerlite_api_key" name="mailerlite_api_key" value="' . esc_attr($mailerlite_api_key) . '" class="regular-text"></td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<th scope="row"><label for="mailerlite_group_id">MailerLite Group ID</label></th>';
    echo '<td><input type="text" id="mailerlite_group_id" name="mailerlite_group_id" value="' . esc_attr($mailerlite_group_id) . '" class="regular-text"></td>';
    echo '</tr>';
    
    echo '</table>';
    echo '<p><input type="submit" name="submit" class="button-primary" value="Save Settings"></p>';
    echo '</form>';

    echo '<h2>Services List</h2>';
    if (!empty($services)) {
        echo '<table class="form-table" style="width:100%;">';
        echo '<thead><tr><th>Service</th><th>Action</th></tr></thead>';
        echo '<tbody>';
        foreach ($services as $key => $service) {
            echo '<tr>';
            echo '<td>' . esc_html($service) . '</td>';
            echo '<td>';
            echo '<form method="POST" action="" style="display:inline;">';
            echo '<input type="hidden" name="service_key" value="' . esc_attr($key) . '">';
            echo '<input type="submit" name="delete_service" class="button-secondary" value="Delete" onclick="return confirm(\'Are you sure you want to delete this service?\');">';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<p>No services added yet.</p>';
    }


    echo '<h3>Add New Service</h3>';
    echo '<form method="POST" action="">';
    echo '<table class="form-table">';
    
    echo '<tr>';
    echo '<th scope="row"><label for="new_service">New Service</label></th>';
    echo '<td><input type="text" id="new_service" name="new_service" value="" class="regular-text"></td>';
    echo '</tr>';
    
    echo '</table>';
    echo '<p><input type="submit" name="add_service" class="button-primary" value="Add Service"></p>';
    echo '</form>';
}
