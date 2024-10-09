<?php
/*
Plugin Name: Book My Service
Description: A plugin to allow users to book services and admins to manage bookings.
Version: 1.0
Author: Midlaj M
*/

require_once plugin_dir_path( __FILE__ ) . '/includes/admin-functions.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/user-functions.php';
require_once plugin_dir_path( __FILE__ ) . '/includes/booking-functions.php';


function bms_enqueue_assets()
{
    wp_enqueue_style('bms-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('bms-scripts', plugin_dir_url(__FILE__) . 'assets/js/script.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'bms_enqueue_assets');
