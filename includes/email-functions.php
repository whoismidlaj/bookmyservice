<?php
function bms_generate_booking_email_content($booking_data) {
    $user_info = get_userdata($booking_data['user_id']);
    $user_name = esc_html($user_info->display_name);
    $user_email = esc_html($user_info->user_email);
    $service = esc_html($booking_data['service']);
    $booking_time = date('F j, Y, g:i A', strtotime($booking_data['booking_time']));
    $message = nl2br(esc_html($booking_data['message']));

    $email_content = "
    <div style='font-family: Arial, sans-serif;'>
        <h2 style='color: #333;'>New Booking Submission</h2>
        <p style='font-size: 16px; line-height: 1.5;'>A new booking has been submitted with the following details:</p>
        
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <th style='border: 1px solid #ccc; padding: 8px; background-color: #f9f9f9;'>Field</th>
                <th style='border: 1px solid #ccc; padding: 8px; background-color: #f9f9f9;'>Details</th>
            </tr>
            <tr>
                <td style='border: 1px solid #ccc; padding: 8px;'>User</td>
                <td style='border: 1px solid #ccc; padding: 8px;'>{$user_name}</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ccc; padding: 8px;'>Email</td>
                <td style='border: 1px solid #ccc; padding: 8px;'>{$user_email}</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ccc; padding: 8px;'>Service</td>
                <td style='border: 1px solid #ccc; padding: 8px;'>{$service}</td>
            </tr>
            <tr>
                <td style='border: 1px solid #ccc; padding: 8px;'>Booking Time</td>
                <td style='border: 1px solid #ccc; padding: 8px;'>{$booking_time}</td>
            </tr>
        </table>

        <h3 style='color: #333; margin-top: 20px;'>Additional Message:</h3>
        <p style='font-size: 16px; line-height: 1.5;'>{$message}</p>

        <hr style='border: 1px solid #ccc;'>
        <p style='font-size: 14px; color: #777;'>Thank you for using our service!</p>
    </div>
    ";

    return $email_content;
}

function bms_send_booking_email($booking_data) {
    $admin_email = get_option('bms_admin_email', get_option('admin_email'));
    $subject = "New Booking from " . get_bloginfo('name');
    $message = bms_generate_booking_email_content($booking_data);
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($admin_email, $subject, $message, $headers);
}
