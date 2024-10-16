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
            <!-- <a href="?page=book-my-service&tab=user-list" class="nav-tab <?php // echo ( isset( $_GET['tab']) && $_GET['tab'] === 'user-list' ) ? 'nav-tab-active' : ''; ?>">User List</a> -->
            <a href="?page=book-my-service&tab=options" class="nav-tab <?php echo ( isset( $_GET['tab']) && $_GET['tab'] === 'options' ) ? 'nav-tab-active' : ''; ?>">Options</a>
        </h2>

        <?php
        // if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'user-list' ) {
        //     bms_user_list();
        // } else 
        if ( isset( $_GET['tab'] ) && $_GET['tab'] === 'options' ) {
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

    // Handle form submission and update status
    if (isset($_POST['save_service_changes']) && isset($_POST['status'])) {
        foreach ($_POST['status'] as $booking_id => $new_status) {
            $wpdb->update(
                $table_name,
                array('status' => sanitize_text_field($new_status)),
                array('id' => intval($booking_id)),
                array('%s'),
                array('%d')
            );
        }
        echo '<div class="updated notice"><p>Statuses updated successfully.</p></div>';
    }

    // Fetch bookings
    $bookings = $wpdb->get_results("SELECT b.id, b.service, b.booking_time, b.message, b.status, u.user_email, u.ID as user_id FROM $table_name b JOIN $wpdb->users u ON b.user_id = u.ID ORDER BY b.booking_time DESC", ARRAY_A);

    // Display bookings
    if (!empty($bookings)) {
        echo '<h2>All Bookings</h2>';
        echo '<form method="post">';
        echo '<div class="table-responsive">'; // Responsive container
        echo '<table class="widefat" cellspacing="0">';
        echo '<thead>';
        echo '<tr>';
        echo '<th scope="col">Name</th>';
        echo '<th scope="col">Email</th>';
        echo '<th scope="col">Phone</th>';
        echo '<th scope="col">Service Selected</th>';
        echo '<th scope="col">Booking Time</th>';
        echo '<th scope="col">Message</th>';
        echo '<th scope="col">Status</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        
        foreach ($bookings as $booking) {
            $first_name = !empty(get_user_meta($booking['user_id'], 'first_name', true)) 
            ? get_user_meta($booking['user_id'], 'first_name', true) 
            : (!empty(get_user_meta($booking['user_id'], 'nickname', true)) 
                ? get_user_meta($booking['user_id'], 'nickname', true) 
                : 'No Name');
    
            $phone = !empty(get_user_meta($booking['user_id'], 'phone', true)) ? get_user_meta($booking['user_id'], 'phone', true) : null;
            
            $status = !empty($booking['status']) ? $booking['status'] : 'Pending';
            
            echo '<tr>';
            echo '<td>' . esc_html($first_name) . '</td>';
            echo '<td>' . esc_html($booking['user_email']) . '</td>';
            echo '<td>' . esc_html($phone) . '</td>';
            echo '<td>' . esc_html($booking['service']) . '</td>';
            echo '<td>' . esc_html(date('F j, Y, g:i A', strtotime($booking['booking_time']))) . '</td>';
            echo '<td>' . esc_html($booking['message']) . '</td>';
    
            echo '<td>';
            echo '<select name="status[' . esc_attr($booking['id']) . ']">';
            echo '<option value="Pending"' . selected($status, 'Pending', false) . '>Pending</option>';
            echo '<option value="Completed"' . selected($status, 'Completed', false) . '>Completed</option>';
            echo '</select>';
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        echo '</div>'; // End responsive container
        echo '<p><input type="submit" name="save_service_changes" class="button-primary" value="Save Changes"></p>';
        echo '</form>';
    } else {
        echo '<h2>No bookings found.</h2>';
    }
    
}

function bms_other_options() {
    bms_add_service();
    bms_delete_service();
    bms_submission_email();

    $services = get_option('bms_services', array());
    $email = get_option('bms_admin_email', '');

    echo '<h1>Options</h1>';

    echo '<h2>Submission Email</h2>';
    echo '<p>Enter the email address where you want to receive booking notifications.</p>';
    echo '<form method="POST" action="" style="display:inline;">';
    echo '<input type="email" id="bms_email" name="bms_email" value="' . esc_attr($email) . '" required>';
    echo '<input type="submit" value="Save Email">';
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

    echo '<h3>Reset Booking Table</h3>';
    echo '<form method="POST" action="">';
    echo '<p><input type="submit" name="reset_booking_table" class="button-secondary" value="Reset Booking Table" onclick="return confirm(\'Are you sure you want to reset the booking table? All data will be lost.\');"></p>';
    echo '</form>';

    // Handle reset request
    if (isset($_POST['reset_booking_table'])) {
        reset_booking_table(); // Call the reset function
        echo '<div class="updated"><p>Booking table has been reset.</p></div>';
    }
}