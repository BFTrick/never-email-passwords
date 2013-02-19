<?php
/*
 * Plugin Name: NeverEmailPasswords
 * Plugin URI: http://zippykid.com/plugins/never-email-passwords
 * Version: 0.1
 * Author: John Gray <john@zippykid.com>
 * Description: Send new users a reset password link when their account is created.
 */

add_action( 'user_register', 'nep_user_register' );
add_action( 'admin_print_scripts', 'nep_remove_email_checkbox' );

function nep_user_register( $user_id ) {
    global $wpdb;

    $user_data = get_userdata( $user_id );

    if ( is_wp_error($user_data) ) {
        error_log( sprintf("NeverEmailPasswords:nep_user_register error from get_userdata( %s ): %s", $user_id, $user_data->get_error_message()) );
        return false;
    }

    # code lifted from wp-login.php:retrieve_password()

    $key = wp_generate_password(20, false);

    $wpdb->update($wpdb->users, array('user_activation_key' => $key), array('user_login' => $user_data->user_login));

    $blog_name = get_bloginfo('name');
    $subject = "Please set your $blog_name password";
    $body = nep_message_body( $blog_name ) .
        network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_data->user_login), 'login');

    if ( ! wp_mail($user_data->user_email, $subject, $body) ) {
        error_log( sprintf("Failed sending email to <$user_data->user_email>:\n$subject\n$body") );
        return false;
    }

    error_log( sprintf("Sent password reset link to %s", $user_data->user_login) );
    return true;
}

function nep_remove_email_checkbox() {
    $password = wp_generate_password( 64, false );
    wp_enqueue_script( 'nep_remove_email_checkbox', plugins_url('/js/nep_remove_email_checkbox.js', __FILE__), array(), false, true );
    wp_localize_script( 'nep_remove_email_checkbox', 'NeverEmailPasswords', array('password' => $password) );
}

function nep_message_body( $blog_name ) {
    return <<<EOB
An account has been created for you at $blog_name, you need to set a password
for this account before it can be used.

Click here to set this password, otherwise ignore this message:
EOB;
}

