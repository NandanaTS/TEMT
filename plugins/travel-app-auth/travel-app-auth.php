<?php
/*
Plugin Name: Travel App User Management
Description: Handles user registration, login, profile management, and password change
Version: 1.3
Author: Your Name
*/

// Process Registration, Login, Profile Update, and Logout
function travel_app_process_forms() {
    // Handle User Registration
    if (isset($_POST['register_submit'])) {
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        if (!email_exists($email)) {
            $user_id = wp_create_user($email, $password, $email);
            wp_update_user(array('ID' => $user_id, 'display_name' => $name));
            echo "<script>alert('Registration successful! You can now log in.');</script>";
        } else {
            echo "<script>alert('Error: Email already registered.');</script>";
        }
    }

    // Handle User Login
    if (isset($_POST['login_submit'])) {
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];
        $user = wp_signon(array('user_login' => $email, 'user_password' => $password, 'remember' => true));

        if (!is_wp_error($user)) {
            echo "<script>alert('Login successful!');</script>";
        } else {
            echo "<script>alert('Error: Incorrect email or password.');</script>";
        }
    }

    // Handle Profile Update and Password Change
    if (isset($_POST['update_profile'])) {
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();

            // Update Name
            if (isset($_POST['update_name']) && !empty($_POST['name'])) {
                $name = sanitize_text_field($_POST['name']);
                wp_update_user(array('ID' => $user_id, 'display_name' => $name));
            }

            // Update Email
            if (isset($_POST['update_email']) && !empty($_POST['email'])) {
                $email = sanitize_email($_POST['email']);
                if (is_email($email) && !email_exists($email)) {
                    wp_update_user(array('ID' => $user_id, 'user_email' => $email));
                } else {
                    echo "<script>alert('Error: Email is invalid or already in use.');</script>";
                }
            }

            // Change Password
            if (isset($_POST['change_password']) && !empty($_POST['current_password']) && !empty($_POST['new_password']) && !empty($_POST['confirm_password'])) {
                $current_password = $_POST['current_password'];
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];
                $user = get_user_by('ID', $user_id);

                if (wp_check_password($current_password, $user->user_pass, $user_id)) {
                    if ($new_password === $confirm_password) {
                        wp_set_password($new_password, $user_id);
                        echo "<script>alert('Password updated successfully. Please log in again.');</script>";
                        wp_logout();
                    } else {
                        echo "<script>alert('Error: New passwords do not match.');</script>";
                    }
                } else {
                    echo "<script>alert('Error: Current password is incorrect.');</script>";
                }
            }

            echo "<script>alert('Profile updated successfully.');</script>";
        } else {
            echo "<script>alert('Error: You need to log in to update your profile.');</script>";
        }
    }

    // Handle User Logout
    if (isset($_POST['logout_submit'])) {
        wp_logout();
        echo "<script>alert('Logged out successfully.');</script>";
    }
}
add_action('init', 'travel_app_process_forms');
