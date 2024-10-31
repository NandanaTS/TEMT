<?php
/*
Plugin Name: Booking Confirmation
Description: Generates an HTML booking confirmation file, emails it to the user, and provides a downloadable link in the profile.
Version: 1.0
Author: Your Name
*/

function generate_booking_confirmation($booking_details) {
    $upload_dir = wp_upload_dir();
    $file_path = $upload_dir['basedir'] . '/booking_confirmation_' . $booking_details['id'] . '.html';

    // Load the HTML template
    $content = file_get_contents(plugin_dir_path(__FILE__) . 'booking_confirmation_template.html');

    // Set default values if not provided
    $current_date = date("Y-m-d");
    $current_time = date("H:i:s");
    $default_name = "Valued Guest";

    // Replace placeholders with booking details
    $content = str_replace('{booking_id}', esc_html($booking_details['id']), $content);
    $content = str_replace('{name}', esc_html($booking_details['name'] ?? $default_name), $content);
    $content = str_replace('{current_date}', esc_html($current_date), $content);
    $content = str_replace('{current_time}', esc_html($current_time), $content);
    $content = str_replace('{destination}', esc_html($booking_details['destination'] ?? "Unknown"), $content);
    $content = str_replace('{departure_date}', esc_html($booking_details['departure_date'] ?? "To be confirmed"), $content);
    $content = str_replace('{return_date}', esc_html($booking_details['return_date'] ?? "To be confirmed"), $content);

    // Save the content as an HTML file
    file_put_contents($file_path, $content);

    return $file_path;
}

// Send booking confirmation email
function send_booking_email($user_email, $booking_details) {
    $file_path = generate_booking_confirmation($booking_details);
    $upload_dir = wp_upload_dir();
    $file_url = $upload_dir['baseurl'] . '/booking_confirmation_' . $booking_details['id'] . '.html';

    // Email content
    $subject = 'Your Booking Confirmation';
    $message = '<p>Thank you for your booking! You can view and download your confirmation <a href="' . esc_url($file_url) . '">here</a>.</p>';
    $headers = array('Content-Type: text/html; charset=UTF-8');

    // Send the email
    wp_mail($user_email, $subject, $message, $headers);
}

// Add download link to user profile
add_action('show_user_profile', 'add_download_link_to_profile');
add_action('edit_user_profile', 'add_download_link_to_profile');

function add_download_link_to_profile($user) {
    $booking_id = get_user_meta($user->ID, 'booking_id', true);
    if ($booking_id) {
        $upload_dir = wp_upload_dir();
        $file_url = $upload_dir['baseurl'] . '/booking_confirmation_' . $booking_id . '.html';

        echo '<h3>Booking Confirmation</h3>';
        echo '<a href="' . esc_url($file_url) . '" download>Download your booking confirmation</a>';
    }
}

// Example function to trigger the confirmation email (for testing)
function trigger_confirmation_email() {
    $booking_details = array(
        'id' => '12345',
        'name' => 'John Doe',  // Default to this name
        'destination' => 'Paris',
        'departure_date' => '2024-12-25',
        'return_date' => '2025-01-05'
    );
    $user_email = 'user@example.com';

    send_booking_email($user_email, $booking_details);
}

// Uncomment below to trigger confirmation email for testing
// add_action('init', 'trigger_confirmation_email');
?>
