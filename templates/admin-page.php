<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bms_admin_page() {
    ?>
    <div class="wrap bms_options">
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

    $bookings = $wpdb->get_results("SELECT b.id, b.service, b.booking_time, b.message,u.user_email, u.ID as user_id FROM $table_name b JOIN $wpdb->users u ON b.user_id = u.ID ORDER BY b.booking_time DESC", ARRAY_A);

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
        echo '<th scope="col">Message</th>';
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
            echo '<td>' . esc_html(date('F j, Y, g:i A', strtotime($booking['booking_time']))) . '</td>';
            echo '<td>' . esc_html($booking['message']) . '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
    } else {
        echo '<h2>No bookings found.</h2>';
    }
}

function bms_other_options() {
    bms_add_service();
    bms_delete_service();
    bms_submission_email();
	bms_save_google_oauth_credentials();

    $services = get_option('bms_services', array());
    $email = get_option('bms_admin_email', '');
	$bms_client_id = get_option('bms_client_id', '');
	$bms_client_secret = get_option('bms_client_secret', '');

    echo '<h1>Options</h1>';

    echo '<h2>Submission Email</h2>';
    echo '<p>Enter the email address where you want to receive booking notifications.</p>';
    echo '<form method="POST" action="" style="display:inline;">';
    echo '<input type="email" id="bms_email" name="bms_email" value="' . esc_attr($email) . '" required>';
    echo '<input type="submit" value="Save Email">';
    echo '</form>';
	
echo '<h2>Google OAuth Client</h2>';
echo '<form method="POST" action="" style="display:inline;">';

echo '<p>';
echo '<label for="bms_client_id">Google Client ID:</label><br>';
echo '<input type="text" id="bms_client_id" name="bms_client_id" value="' . esc_attr($bms_client_id) . '" required style="width: 100%; padding: 8px; margin: 5px 0;">';
echo '</p>';

echo '<p>';
echo '<label for="bms_client_secret">Google Client Secret:</label><br>';
echo '<input type="text" id="bms_client_secret" name="bms_client_secret" value="' . esc_attr($bms_client_secret) . '" required style="width: 100%; padding: 8px; margin: 5px 0;">';
echo '</p>';

echo '<p>';
echo '<input type="submit" value="Save Credentials" style="padding: 8px 16px; background-color: #0073aa; color: white; border: none; cursor: pointer;">';
echo '</p>';

echo '</form>';


    echo '<h2>Services List</h2>';
    if (!empty($services)) {
        echo '<table class="form-table bms_services_list">';
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
