<?php

// this is  not needed any more
// Add AJAX actions
add_action('wp_ajax_katorymnd_save_feedback', 'katorymnd_handle_ajax_request');
add_action('wp_ajax_nopriv_katorymnd_save_feedback', 'katorymnd_handle_ajax_request');

function katorymnd_handle_ajax_request()
{
    // Check nonce for security
    check_ajax_referer('katorymnd-feedback-nonce', 'nonce');

    // Include validation class if not already included
    require_once plugin_dir_path(__FILE__).'valid_process/katorymnd_valid.php';
    $katorymnd_jdpi = new Katorymnd_Uraz();

    // Validation
    $katorymnd_jdpi->valid_name(sanitize_text_field($_POST['katorymnd_cukv']), __('First name'));
    $katorymnd_jdpi->valid_name(sanitize_text_field($_POST['katorymnd_vlkj']), __('Last name'));
    $katorymnd_jdpi->valid_email(sanitize_email($_POST['katorymnd_hewl']));
    $katorymnd_jdpi->valid_input(sanitize_text_field($_POST['katorymnd_tdjy']), __('Subject'));
    $katorymnd_jdpi->valid_input(stripslashes(sanitize_textarea_field($_POST['katorymnd_crpx'])), __('Message'));

    if (!$katorymnd_jdpi->save_data()) {
        wp_send_json_error($katorymnd_jdpi->valid_error());
        exit;
    }

    $result = katorymnd_save_feedback_to_db();
    if ($result) {
        wp_send_json_success($katorymnd_jdpi->valid_success());
    } else {
        wp_send_json_error(__('Error saving data', 'katorymnd-text-domain'));
    }
}

function katorymnd_generate_unique_function($length, $table_cryw, $column, $prefix = 'katorymnd_')
{
    global $wpdb;
    do {
        $random_string = substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, $length);
        $unique_value = $prefix.$random_string;
        $existing_value = $wpdb->get_var($wpdb->prepare("SELECT $column FROM $table_cryw WHERE $column = %s", $unique_value));
    } while ($existing_value);

    return $unique_value;
}

    function katorymnd_generate_unique_id($length, $table_cryw, $column)
    {
        return katorymnd_generate_unique_function($length, $table_cryw, $column, '');
    }

function katorymnd_save_feedback_to_db()
{
    global $wpdb;

    $table_cryw = $wpdb->prefix.'katorymnd_feeback';

    $trla = 4;
    $jloi = 3;
    $katorymnd_lvpb = katorymnd_generate_unique_function($trla, $table_cryw, 'feed_function');
    $katorymnd_gunt = katorymnd_generate_unique_id($jloi, $table_cryw, 'feed_div_id');

    return $wpdb->insert($table_cryw, [
        'first_name' => sanitize_text_field($_POST['katorymnd_cukv']),
        'second_name' => sanitize_text_field($_POST['katorymnd_vlkj']),
        'feed_email' => sanitize_email($_POST['katorymnd_hewl']),
        'feed_subject' => sanitize_text_field($_POST['katorymnd_tdjy']),
        'feed_message' => stripslashes(sanitize_textarea_field($_POST['katorymnd_crpx'])),
        'feed_div_id' => $katorymnd_gunt,
        'feed_function' => $katorymnd_lvpb,
    ]);
}


///////////////////////

