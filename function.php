<?php
// Register the REST route: http://yourdomain.com/wp-json/katorymnd/v1/get_file/
add_action('rest_api_init', 'katorymnd_register_route');

function katorymnd_register_route() {
    register_rest_route('katorymnd/v1', '/get_file/', array(
        'methods' => 'GET',
        'callback' => 'katorymnd_get_file_callback',
        'permission_callback' => 'katorymnd_permission_check',  // Include permission check
    ));
}

// Permission callback to restrict access
function katorymnd_permission_check() {
    // For demonstration: restrict this API to admins. Adjust as per your needs.
    return current_user_can('manage_options');
}

function katorymnd_get_file_callback($data) {
    // Define the plugin directory path.
    $plugin_dir_path = plugin_dir_path(__FILE__);

    // Ensure the main class is loaded.
    if (!class_exists('Katorymnd_Get_File_Path')) {
        require_once $plugin_dir_path . 'katorymnd_callme.php';
    }

    $katorymnd_get_file = new Katorymnd_Get_File_Path();

    // Include necessary files.
    include_once $plugin_dir_path . 'katorymnd_reaction_details.php';

    // Get the cookie value and call the method from the class.
    if (isset($_COOKIE['wp_katorymnd_reaction_fl'])) {
        $katorymnd_cdav = sanitize_text_field($_COOKIE['wp_katorymnd_reaction_fl']); // Sanitize input
        $katorymnd_bwkx = substr($katorymnd_cdav, 14, -17);
        return $katorymnd_get_file->katorymnd_file($katorymnd_bwkx); 
    } else {
        return new WP_Error('no_cookie', 'No cookie detected', array('status' => 403));
    }
}
