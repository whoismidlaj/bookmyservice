<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function bms_add_custom_user_role()
{
    add_role(
        'bms_user',
        'BMS User',
        array(
            'read' => true,
            'edit_posts' => false,
            'delete_posts' => false,
        )
    );
}
register_activation_hook(__FILE__, 'bms_add_custom_user_role');

function bms_remove_custom_user_role()
{
    remove_role('bms_user');
}
register_deactivation_hook(__FILE__, 'bms_remove_custom_user_role');

function bms_register_user()
{
    if (isset($_POST['register'])) {
        $name = sanitize_text_field($_POST['name']);
        $username = strtolower(str_replace(' ', '_', sanitize_text_field($_POST['name'])));
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $phone = sanitize_text_field($_POST['phone']);

        $existing_user_with_phone = get_users(array(
            'meta_key'   => 'phone',
            'meta_value' => $phone,
            'number'     => 1
        ));

        if (!empty($existing_user_with_phone)) {
            echo '<p class="error">This phone number is already registered. Please use a different phone number.</p>';
            return;
        }

        $user_id = wp_create_user($username, $password, $email);

        if (is_wp_error($user_id)) {
            echo '<p class="error">' . $user_id->get_error_message() . '</p>';
        } else {
            $user = new WP_User($user_id);
            $user->set_role('bms_user');

            update_user_meta($user_id, 'phone', $phone);
            update_user_meta($user_id, 'first_name', $name);

            wp_set_current_user($user_id);
            wp_set_auth_cookie($user_id);
            wp_redirect(home_url('/booking'));
            exit;
        }
    }
}
add_action('template_redirect', 'bms_register_user');

function restrict_bms_user_dashboard()
{
    if (is_user_logged_in() && current_user_can('bms_user')) {
        wp_redirect(home_url('/booking'));
        exit;
    }
}
add_action('admin_init', 'restrict_bms_user_dashboard');

function hide_admin_toolbar_for_bms_user()
{
    if (current_user_can('bms_user')) {
        show_admin_bar(false);
    }
}
add_action('after_setup_theme', 'hide_admin_toolbar_for_bms_user');