<?php
/*
 * Plugin Name: Katorymnd reaction process
 * Description: The plugin introduces a dynamic and interactive layer to your WordPress site, allowing users to express their feelings and thoughts on your content through a variety of reaction options.
 * Version: 1.2.1
 * Author: Katorymnd
 * Author URI: https://katorymnd.com
 * Requires at least: 6.0
 * Tested up to: 6.6.1
 * Requires PHP Version: 5.6.20 or higher 
 * License: GNU General Public License v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: katorymnd-reaction-process
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Ensure headers haven't been sent before continuing
if (headers_sent($filename, $linenum)) {
    die("Headers were already sent in $filename on line $linenum.");
}

/**
 * Clears various caches when the plugin is activated or deactivated.
 */
function katorymnd_clear_all_caches_on_plugin_change()
{
    // Clear WP Super Cache
    if (function_exists('wp_cache_clear_cache')) {
        wp_cache_clear_cache();
    }

    // Clear W3 Total Cache
    if (function_exists('w3tc_flush_all')) {
        w3tc_flush_all();
    }

    // Clear WP Rocket Cache
    if (function_exists('rocket_clean_domain')) {
        rocket_clean_domain();
    }

    // Clear WordPress Object Cache
    wp_cache_flush();
}

/**
 * Plugin activation hook.
 */
function katorymnd_reaction_process_activate()
{
    // Clear all caches
    katorymnd_clear_all_caches_on_plugin_change();
}
register_activation_hook(__FILE__, 'katorymnd_reaction_process_activate');

/**
 * Plugin deactivation hook.
 */
function katorymnd_reaction_process_deactivate()
{
    // Clear all caches
    katorymnd_clear_all_caches_on_plugin_change();
}
register_deactivation_hook(__FILE__, 'katorymnd_reaction_process_deactivate');


// Add the `katorymnd plugin` main menu icon
function custom_admin_style()
{
    echo '
        <style>
            .toplevel_page_katorymnd-plugins .wp-menu-image {
                background-image: url("data:image/svg+xml;charset=UTF8,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' viewBox=\'0 0 24 24\' fill=\'none\'%3E%3Cpath d=\'M2 6v10h20V6H2zm12 9H4v-2h10v2zm0-4H4V9h10v2zm6 0h-4V9h4v2z\' fill=\'%23a3a3a3\'/%3E%3Cpath d=\'M22 7H2v-.99C2 5.45 2.45 5 3 5h18c.55 0 1 .45 1 .99V7z\' fill=\'%235c5c5c\'/%3E%3Cpath d=\'M3 18h18v1H3z\' fill=\'%23a3a3a3\'/%3E%3Cpath d=\'M4 20h16v.01H4z\' fill=\'%235c5c5c\'/%3E%3Cpath d=\'M8 1.01L6 3l2 2 .59-.58L9 3l-1-1.99zM6.41 8L5 6.59 3.59 8 5 9.41 6.41 8zM11 .01l.59.58L13 3l-2 2L11 4.42 9.59 3zM5 15l1.41-1.41L5 12.17l-1.41 1.42L5 15zm-1.41-6L5 7.59 6.41 9 5 10.41 3.59 9z\' fill=\'%230073aa\'/%3E%3C/svg%3E");
                background-size: 20px 20px;
                background-repeat: no-repeat;
                background-position: center;
            }
        </style>
    ';
}
add_action('admin_head', 'custom_admin_style', 100);

// Create the top-level menu page for Katorymnd plugins
function katorymnd_add_plugins_menu_page()
{
    global $menu;

    // Check if main menu is available
    $main_menu_exists = false;
    foreach ($menu as $item) {
        if ($item[2] === 'katorymnd-plugins') {
            $main_menu_exists = true;
            break;
        }
    }

    if (!$main_menu_exists) {
        // Create the top-level menu
        add_menu_page(
            __('Katorymnd Plugins', 'katorymnd-reaction-process'),
            __('Katorymnd Plugins', 'katorymnd-reaction-process'),
            'manage_options',
            'katorymnd-plugins',
            'katorymnd_plugin_admin_page',
            'none',
            30
        );
    }
}
add_action('admin_menu', 'katorymnd_add_plugins_menu_page');

// rename the default sub menu for katorymnd plugins
function katorymnd_rename_submenu()
{
    global $submenu;

    if (isset($submenu['katorymnd-plugins'][0][0])) {
        $submenu['katorymnd-plugins'][0][0] = __('All Plugins', 'katorymnd-reaction-process');
    }
}
add_action('admin_init', 'katorymnd_rename_submenu');

// Add the submenu page for Katorymnd Reaction Process plugin
function katorymnd_add_reaction_plugin_submenu_page()
{
    // Check if the function to add the submenu page exists
    if (function_exists('add_submenu_page')) {
        $hook_suffix = add_submenu_page(
            'katorymnd-plugins',
            __('Katorymnd Reaction Settings', 'katorymnd-reaction-process'),
            __('Katorymnd Reaction', 'katorymnd-reaction-process'),
            'manage_options',
            'katorymnd-reaction-settings',
            'katorymnd_admin_page'
        );

        // Use the returned hook suffix to conditionally enqueue the Chart.js script
        add_action('admin_enqueue_scripts', function ($hook) use ($hook_suffix) {
            if ($hook === $hook_suffix) {
                wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '2.9.4', true);
            }
        });
    }
}
add_action('admin_menu', 'katorymnd_add_reaction_plugin_submenu_page');

/**
 * Add settings link to the plugins page.
 *
 * @param array $links Array of plugin action links.
 *
 * @return array Updated array of plugin action links.
 */
function katorymnd_add_settings_link($links)
{
    $settings_link = '<a href="' . admin_url('admin.php?page=katorymnd-reaction-settings') . '">' . __('Settings', 'katorymnd-reaction-process') . '</a>';
    array_unshift($links, $settings_link);

    return $links;
}

add_filter('plugin_action_links_katorymnd_reaction/katorymnd-reaction-process.php', 'katorymnd_add_settings_link');


function katorymnd_plugin_admin_page()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'katorymnd-reaction-process'));
    }

    $form_path = plugin_dir_path(__FILE__);
    include $form_path . 'katorymnd_plugin_admin_page.php';
}

// Create Katorymnd Reaction Process plugin tables
function katorymnd_create_tables()
{
    ob_start(); // Start output buffering
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    $base_table_name = $wpdb->prefix . 'katorymnd_';
    $comments_table_name = $base_table_name . 'kr_comments';
    $reactions_table_name = $base_table_name . 'kr_reactions';
    $user_details_table_name = $base_table_name . 'kr_user_details';
    $abuse_reports_table_name = $base_table_name . 'kr_abuse_reports';
    $custom_user_sessions_table_name = $base_table_name . 'kr_custom_user_sessions';
    $custom_comments_table_name = $base_table_name . 'kr_intialid_commentid_page'; // Note the different prefix usage here
    $user_ratings_table_name = $base_table_name . 'kr_user_ratings';

    // Adjust for MySQL and SQLite compatibility
    $autoincrement = $wpdb->use_mysqli ? 'AUTO_INCREMENT' : 'AUTOINCREMENT';
    $current_timestamp = $wpdb->use_mysqli ? 'CURRENT_TIMESTAMP' : "datetime('now','localtime')";

    // Enable foreign keys for SQLite
    if (!$wpdb->use_mysqli) {
        $wpdb->query("PRAGMA foreign_keys = ON;");
    }

    // Status field definition based on database type
    $status_field = $wpdb->use_mysqli ?
        "status ENUM('open', 'closed', 'reviewing') NOT NULL DEFAULT 'open'" :
        "status TEXT CHECK(status IN ('open', 'closed', 'reviewing')) NOT NULL DEFAULT 'open'";


    // Define tables and their SQL queries
    $tables = [
        [
            'name' => $user_details_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                user_id bigint(20) NOT NULL $autoincrement,
                username varchar(255) NOT NULL,
                email varchar(255) NOT NULL,
                full_name varchar(255) NOT NULL,
                avatar_url varchar(255),
                user_status VARCHAR(50) NOT NULL DEFAULT 'active',
                PRIMARY KEY (user_id)
            ) $charset_collate;"
        ],
        [
            'name' => $comments_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                id VARCHAR(255) PRIMARY KEY,
                userName VARCHAR(255) NOT NULL,
                content TEXT NOT NULL,
                parent_id VARCHAR(255),
                timestamp DATETIME NOT NULL DEFAULT $current_timestamp,
                FOREIGN KEY (parent_id) REFERENCES %s(id)
            ) $charset_collate;"
        ],
        [
            'name' => $reactions_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                comment_id VARCHAR(255),
                user_name VARCHAR(255),
                reaction_type VARCHAR(255),
                PRIMARY KEY (comment_id, user_name, reaction_type),
                FOREIGN KEY (comment_id) REFERENCES {$comments_table_name}(id)
            ) $charset_collate;"
        ],
        [
            'name' => $abuse_reports_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                id INTEGER PRIMARY KEY $autoincrement,
                comment_id VARCHAR(255) NOT NULL,
                reason VARCHAR(255) NOT NULL,
                details TEXT,
                user_name VARCHAR(255) NOT NULL,
                report_date DATETIME NOT NULL DEFAULT $current_timestamp,
                $status_field,
                handled_by VARCHAR(255),
                resolution TEXT,
                resolution_date DATETIME
            ) $charset_collate;"
        ],
        [
            'name' => $custom_user_sessions_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                id INTEGER PRIMARY KEY $autoincrement,
                user_id BIGINT(20) NOT NULL,
                session_token VARCHAR(255) NOT NULL,
                expires_at DATETIME NOT NULL,
                UNIQUE (session_token)
            ) $charset_collate;"
        ],
        [
            'name' => $custom_comments_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                id INTEGER PRIMARY KEY $autoincrement,
                comment_id VARCHAR(255) NOT NULL,
                page_id BIGINT(20) NOT NULL,
                page_slug VARCHAR(200) NOT NULL,
                page_title VARCHAR(255) NOT NULL,
                username VARCHAR(100) NOT NULL,
                created_at DATETIME NOT NULL DEFAULT $current_timestamp,
                UNIQUE (comment_id)
            ) $charset_collate;"
        ],
        [
            'name' => $user_ratings_table_name,
            'sql' => "CREATE TABLE IF NOT EXISTS %s (
                id INTEGER PRIMARY KEY $autoincrement,
                user_identifier VARCHAR(255) NOT NULL,
                page_id BIGINT(20) NOT NULL,
                rating TINYINT(3) NOT NULL,
                created_at DATETIME DEFAULT $current_timestamp
            ) $charset_collate;"
        ],
    ];

    // Execute table creation SQL queries using dbDelta, which checks and creates if not exists
    foreach ($tables as $table) {
        $sql = sprintf($table['sql'], $table['name'], $comments_table_name, $charset_collate);
        dbDelta($sql);
    }

    // Log errors for the last query executed
    if ($wpdb->last_error !== '') {
        error_log("Database error encountered: " . $wpdb->last_error);
    }

    // Update the plugin version option, only if not already set or updates are required
    if (!get_option('katorymnd_wlse_version') || get_option('katorymnd_wlse_version') != '1.2.0') {
        update_option('katorymnd_wlse_version', '1.2.0');
    }

    ob_get_clean();
}

register_activation_hook(__FILE__, 'katorymnd_create_tables');

/**
 * Delete the `katorymnd_feedback` for the previous version
 * as it's not needed any more for this  version
 */
register_activation_hook(__FILE__, 'katorymnd_wztjp7m_plugin_reactivate');
function katorymnd_wztjp7m_plugin_reactivate()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_feedback';

    // Check if the table exists before attempting to delete it
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
    }

    delete_option('katorymnd_wlse');
}

// set default rating option to star
register_activation_hook(__FILE__, 'katorymnd_set_default_rating_type');

function katorymnd_set_default_rating_type()
{
    if (false === get_option('katorymnd_rating_type')) {
        update_option('katorymnd_rating_type', 'star');
    }
}

//add a new role for the admin user for managing the abuse report
function katorymnd_kr_create_custom_user_roles()
{
    add_role('katorymnd_kr_moderator', 'Katorymnd Reaction Moderator', array(
        'read' => true,
        'manage_kr_abuse_reports' => true,
    ));
}
add_action('init', 'katorymnd_kr_create_custom_user_roles');

//attach capablities to other roles too
function add_kr_custom_capabilities()
{
    $roles = ['administrator', 'editor', 'author', 'contributor', 'katorymnd_kr_moderator'];

    foreach ($roles as $role_name) {
        // Retrieve the stored setting for each role
        $option_name = "wpkr_role_setting_{$role_name}";
        $can_manage_reports = get_option($option_name, '0') === '1';

        $role = get_role($role_name);
        if ($role) {
            // Ensure that the administrator always has the capability
            if ($can_manage_reports || $role_name === 'administrator' || $role_name === 'katorymnd_kr_moderator') {
                $role->add_cap('manage_kr_abuse_reports');
            } else {
                // Avoid removing the capability from the administrator
                if ($role_name !== 'administrator') {
                    $role->remove_cap('manage_kr_abuse_reports');
                }
            }
        }
    }
}

//On capabilities update - refresh the logic
function kr_refresh_capabilities_if_needed()
{
    // Check if the transient is set, indicating that we need to update capabilities
    if (get_transient('kr_update_roles_capabilities')) {
        add_kr_custom_capabilities(); // Updates the capabilities based on the latest settings

        // Once updated, delete the transient to avoid repeated updates
        delete_transient('kr_update_roles_capabilities');
    }
}
add_action('init', 'kr_refresh_capabilities_if_needed');

/**
 * Add the page for the assigned user role to manage the reaction abuse reports
 * if the user is granted, they will see this page and the  widget too
 * only applies to users from the  default WP user table
 */
function wpkr_add_report_abuse_page()
{
    add_menu_page(
        __('Manage Abuse Reports', 'katorymnd-reaction-process'),  // Page title
        __('Reaction Report Abuse', 'katorymnd-reaction-process'),  // Menu title
        'manage_kr_abuse_reports',                    // Capability required to see this option.
        'wpkr-report-abuse',                          // Menu slug
        'wpkr_report_abuse_page_content',             // Function to display the page content.
        'dashicons-flag',                             // Icon URL
        6                                             // Position
    );
}

add_action('admin_menu', 'wpkr_add_report_abuse_page');

/**
 * Add the admin submenu item for the generated poll/survey/feedback
 * under `Survey/Poll Tool` tab
 */
function kr_add_survey_poll_admin_menu()
{
    add_submenu_page(
        'katorymnd-plugins', // Parent slug: Use the slug of our main Katorymnd Plugins menu
        'Insight Pulse Preview', // Page title
        'Reaction Insight Pulse Preview', // Menu title
        'manage_options', // Capability: ensures that only those with the 'manage_options' capability can access this menu
        'kr-preview-survey-poll-page', // Menu slug: unique identifier for this submenu
        'kr_render_survey_poll_admin_page' // Function to display the page content
    );
}
add_action('admin_menu', 'kr_add_survey_poll_admin_menu');

function kr_render_survey_poll_admin_page()
{
    // Retrieve the preview HTML from the option
    $preview_html = get_option('kr_survey_preview_html');

    // CSS styles to be applied
    $custom_css = "
    <style>
    .form-check {
      display: flex;
      align-items: center; /* Vertically center the flex items */
      gap: 0.5rem; /* Optional: adds some space between the input and the label */
    }

    .form-check-label {
        display: flex;
        align-items: center; /* Vertically center the flex items */
        gap: 0.5rem;
      }
      
      @media (max-width: 768px) {
        .form-check-label {
          top: -2px; /* Adjust top position for smaller screens */
          position: relative; /* Keep the position as relative */
        }
      }
      
      @media (min-width: 769px) {
        .form-check-label {
          top: -4px; /* Default top position for larger screens */
          position: relative; /* Keep the position as relative */
        }
      }

      .button-container {
          display: flex;
          justify-content: space-between; /* Adjust this as needed */
          margin-top: 20px;
      }
    </style>
    ";

    if (!empty($preview_html) && !preg_match('/<form>\s*<\/form>/', $preview_html)) {
        // Start form
        echo '<form method="post" action="">';
        // Introduction and permalink base selection sections
        echo '<div class="introduction" style="margin-bottom: 20px; padding: 20px; background-color: #f9f9f9; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 5px 26px 5px 0;">
                <h4>Your Created Survey/Poll Preview</h4>
                <p>If you are satisfied with your creation, please add a title for your survey or poll below and proceed to review the preview. This helps in organizing and identifying your work easily.</p>
                <input type="text" class="form-control mb-3" name="kr_survey_title" placeholder="Enter title here..." style="margin-bottom: 15px;">
                <h5 class="card-title">Choose Permalink Base</h5>
                <p>Select a permalink base that best fits the content you\'re creating. This will determine the URL structure for your surveys or polls.</p>
                <select class="form-control mb-3" name="kr_survey_permalink_base">
                    <option value="">Choose...</option>
                    <option value="survey">Survey</option>
                    <option value="poll">Poll</option>
                    <option value="feedback">Feedback</option>
                </select>
              </div>';

        // Wrap div only contains the custom CSS and preview HTML
        echo '<div id="katorymnd_w3v2frb" class="wrap">' . $custom_css . $preview_html . '</div>';

        // Buttons container
        echo '<div class="button-container">';
        // Add "Add New Post" (Save) button within the form if preview is available
        echo '<button type="submit" id="katorymnd_oh0kdv4" class="button button-primary">Add New Post</button>';
        echo '</div>'; // Close buttons container

        // Close form
        echo '</form>';
    } else {
        // Display a message inside a wrap div when the preview is essentially an empty form or truly empty
        echo '<div class="wrap">' . $custom_css . '<p>No preview available. Please generate a preview first.</p></div>';
    }

    // The back button is placed outside the conditional logic, to be consistently available
    echo '<a href="admin.php?page=katorymnd-reaction-settings" class="button" style="margin-top: 20px;">Go Back</a>';
}

/**
 * Register Custom Post Type for survey/poll/feedback 
 * 
 * Our ` "Add New Post" (Save) button` logic will post to `kr_survey` dynamically
 * to view all the posts go to `Reaction Insight Pulse` under `katorymnd plugin submenu
 * 
 */

function kr_register_survey_cpt()
{
    $args = array(
        'public' => true,
        'label'  => 'Insight Pulse',
        'supports' => array('title'), // Define what features the CPT supports
        'has_archive' => true, // Enable archive pages
        'rewrite' => array('slug' => 'feedbackforge'),
        'show_in_menu' => false, // Do not show in the admin menu directly
    );

    register_post_type('kr_survey', $args);
}
add_action('init', 'kr_register_survey_cpt');

// Manually add a submenu item for the CPT under the desired parent menu
function kr_add_survey_cpt_to_menu()
{
    // Add a submenu item under "katorymnd-plugins" that points to the CPT's listing page
    add_submenu_page(
        'katorymnd-plugins', // Parent slug
        'Insight Pulse', // Page title
        'Reaction Insight Pulse', // Menu title
        'manage_options', // Capability required to view this submenu
        'edit.php?post_type=kr_survey' // Menu slug: link to the CPT's listing page
    );
}
add_action('admin_menu', 'kr_add_survey_cpt_to_menu');

// Ensure custom HTML/code is displayed when viewing a survey's permalink
function kr_survey_content_filter($content)
{
    if (is_singular('kr_survey') && in_the_loop() && is_main_query()) {
        $post_id = get_the_ID();
        $survey_html = get_post_meta($post_id, '_kr_survey_html', true);

        if (!empty($survey_html)) {
            return $survey_html; // Display custom HTML/code if available
        }
        // Fallback to default content if no custom HTML/code
    }
    return $content;
}
add_filter('the_content', 'kr_survey_content_filter');


// Shortcode to display the survey, respecting display preferences
function kr_survey_shortcode($atts)
{
    $atts = shortcode_atts(array(
        'id' => '',
    ), $atts, 'kr_survey');

    $post_id = $atts['id'];

    // Early check for expiration
    $expiration_date = get_post_meta($post_id, '_kr_survey_expiration_date', true);
    $current_date = date('Y-m-d');
    if (!empty($expiration_date) && $current_date > $expiration_date) {
        // This survey/poll is no longer available.
        return;
    }

    if (!empty($post_id) && get_post_type($post_id) === 'kr_survey') {
        $display_preference = get_post_meta($post_id, '_kr_survey_display_preference', true);
        $survey_html = get_post_meta($post_id, '_kr_survey_html', true);

        if (!empty($survey_html)) {
            switch ($display_preference) {
                case 'embedded':
                    // For embedded, return as is or wrap as needed, including the post ID in a data attribute
                    return '<div class="kr-survey-embedded" data-survey-id="' . esc_attr($post_id) . '">' . $survey_html . '</div>';

                case 'popup':
                    $kr_permalink_base = get_post_meta($post_id, '_kr_survey_permalink_base', true);

                    // Determine the appropriate action text based on the type from permalink base
                    $action_text = 'Take our survey';
                    if ($kr_permalink_base === 'poll') {
                        $action_text = 'Take our survey poll';
                    } else if ($kr_permalink_base === 'feedback') {
                        $action_text = 'Give us your feedback';
                    }

                    $trigger_id = 'trigger_survey_' . $post_id;
                    // Add the post ID in a data attribute for later access by JavaScript
                    $html = '<button id="' . esc_attr($trigger_id) . '" class="survey-trigger-btn" data-survey-id="' . esc_attr($post_id) . '">' . esc_html($action_text) . '</button>';
                    return $html;

                case 'dedicated_page':
                    $kr_content_type = get_post_meta($post_id, '_kr_survey_permalink_base', true);
                    $kr_permalink_base = get_post_meta($post_id, '_kr_survey_permalink_base', true);

                    // Check and set the appropriate action text based on content type
                    $action_text = 'Take our surveys';
                    if ($kr_permalink_base === 'poll') {
                        $action_text = 'Take our survey polls';
                    } else if ($kr_permalink_base === 'feedback') {
                        $action_text = 'Give us your feedback';
                    }

                    $survey_link = esc_url(get_permalink($post_id) . '?' . esc_attr($kr_permalink_base) . '=' . $post_id);
                    return '<a id="katorymnd_edlzjw1" href="' . $survey_link . '" data-survey-id="' . esc_attr($post_id) . '" data-content-type="' . esc_attr($kr_content_type) . '">' . esc_html($action_text) . ' on its dedicated page</a>';

                default:
                    return '<div class="kr-survey-embedded" data-survey-id="' . esc_attr($post_id) . '">' . $survey_html . '</div>';
            }
        } else {
            $survey_post = get_post($post_id);
            // Include the post ID in a data attribute if displaying post content as a fallback
            return '<div class="kr-survey-fallback" data-survey-id="' . esc_attr($post_id) . '">' . apply_filters('the_content', $survey_post->post_content) . '</div>';
        }
    }

    return; //Survey not found.
}

add_shortcode('kr_survey', 'kr_survey_shortcode'); // shortcode for any New Post survey/poll/feedback creations

/**
 * Add Meta Boxs for our `Insight Pulse` paged as `Reaction Insight Pulse`
 * like
 * Survey Shortcode, 
 * Survey HTML/Code, 
 * Display Preference,
 * Survey/Poll Permalink Base,
 * Survey/Poll Expiration Date
 */
function kr_add_survey_meta_boxes()
{
    add_meta_box(
        'kr_survey_shortcode',
        'Survey/Poll Shortcode',
        'kr_display_survey_shortcode_meta_box',
        'kr_survey',
        'side',
        'default'
    );
    add_meta_box(
        'kr_survey_html_code',
        'Survey/Poll HTML/Code',
        'kr_display_survey_html_meta_box',
        'kr_survey',
        'normal',
        'high'
    );
    add_meta_box(
        'kr_survey_display_preference',
        'Display Preference',
        'kr_display_survey_display_preference_meta_box',
        'kr_survey',
        'side',
        'default'
    );
    add_meta_box(
        'kr_survey_permalink_base',
        'Survey/Poll Permalink Base',
        'kr_display_survey_permalink_base_meta_box',
        'kr_survey',
        'side',
        'default'
    );
    add_meta_box(
        'kr_survey_expiration_date',
        'Survey/Poll Expiration Date',
        'kr_display_survey_expiration_date_meta_box',
        'kr_survey',
        'side',
        'default'
    );
}
add_action('add_meta_boxes', 'kr_add_survey_meta_boxes');

// Display the Meta Box for Survey/poll/feedback Shortcode
function kr_display_survey_shortcode_meta_box($post)
{
    echo '<input id="kr_survey_shortcode_field" type="text" readonly="readonly" class="widefat"';
    echo ' value="[kr_survey id=&quot;' . esc_attr($post->ID) . '&quot;]">';
    echo '<p>Use this shortcode to embed the survey.</p>';
    echo '<button type="button" class="button" onclick="krCopySurveyShortcode()">Copy Shortcode</button>';
}

// Display the Meta Box for Survey HTML/Code
function kr_display_survey_html_meta_box($post)
{
    wp_nonce_field(basename(__FILE__), 'kr_survey_html_nonce');
    $survey_html = get_post_meta($post->ID, '_kr_survey_html', true);

    // Advisory note to the user
    echo '<p><strong>Note:</strong> The Survey/Poll HTML/Code below is critical for the functionality of your survey, poll or feedback. Modifying this code can lead to unexpected issues or disruptions in data collection. Please make changes with caution and only if you understand the implications of these modifications.</p>';

    // Textarea for the Survey/Poll HTML/Code
    echo '<textarea id="kr_survey_html" name="kr_survey_html" rows="10" style="width:100%;">' . esc_textarea($survey_html) . '</textarea>';
}

// Save Meta Box Content for Survey HTML/Code
function kr_save_survey_meta($post_id)
{
    if (!isset($_POST['kr_survey_html_nonce']) || !wp_verify_nonce($_POST['kr_survey_html_nonce'], basename(__FILE__)) || defined('DOING_AUTOSAVE') && DOING_AUTOSAVE || 'kr_survey' != $_POST['post_type'] || !current_user_can('edit_post', $post_id)) {
        return;
    }

    if (isset($_POST['kr_survey_html'])) {
        update_post_meta($post_id, '_kr_survey_html', $_POST['kr_survey_html']);
    }
}
add_action('save_post', 'kr_save_survey_meta');

// Hook into the action that fires before a post is deleted - to delete our survey/poll/feedback posts logic
function kr_survey_before_delete_post($post_id)
{
    if (get_post_type($post_id) === 'kr_survey') {

        $survey_responses = get_posts([
            'post_type' => 'kr_survey_response',
            'numberposts' => -1,
            'meta_query' => [
                [
                    'key' => '_kr_survey_id', // Meta key that stores the linked survey ID in survey responses
                    'value' => $post_id,
                    'compare' => '=',
                ],
            ],
            'fields' => 'ids', // We only need the IDs to delete them
        ]);

        // Proceed with deletion only if there are corresponding responses
        if (!empty($survey_responses)) {
            foreach ($survey_responses as $response_id) {
                wp_delete_post($response_id, true); // true to bypass trash and permanently delete
            }
        }
    }
}
add_action('before_delete_post', 'kr_survey_before_delete_post');


// Hook into the action that fires when a post is trashed - to trash our survey/poll/feedback logic
function kr_survey_wp_trash_post($post_id)
{
    // Check if the post is of our custom post type
    if (get_post_type($post_id) === 'kr_survey') {
        // Perform actions before the survey post is trashed
        // For example, you might want to add custom logging or move related custom data to a "trash" state
    }
}
add_action('wp_trash_post', 'kr_survey_wp_trash_post');

// JavaScript for Copy to Clipboard functionality - survey shortcode logic
function kr_admin_footer_scripts()
{
    $screen = get_current_screen();
    if ($screen->id != 'kr_survey') {
        return;
    }
    echo '<script>
        function krCopySurveyShortcode() {
            var copyText = document.getElementById("kr_survey_shortcode_field");
            copyText.select();
            document.execCommand("copy");
            alert("Shortcode copied to clipboard: " + copyText.value);
        }
    </script>';
}
add_action('admin_footer', 'kr_admin_footer_scripts');

// Add a custom action link to revert published surveys back to draft
function kr_add_revert_to_draft_action($actions, $post)
{
    if ($post->post_type == 'kr_survey' && $post->post_status == 'publish') {
        $actions['revert_to_draft'] = '<a href="' . wp_nonce_url(add_query_arg('revert_to_draft', $post->ID), 'revert_to_draft_' . $post->ID) . '">Revert to Draft</a>';
    }
    return $actions;
}
add_filter('post_row_actions', 'kr_add_revert_to_draft_action', 10, 2);

// Handle the revert to draft action
function kr_handle_revert_to_draft_action()
{
    if (isset($_GET['revert_to_draft'], $_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'revert_to_draft_' . $_GET['revert_to_draft'])) {
        $post_id = $_GET['revert_to_draft'];
        if (current_user_can('edit_post', $post_id)) {
            wp_update_post(array(
                'ID'          => $post_id,
                'post_status' => 'draft'
            ));
            wp_redirect(admin_url('edit.php?post_type=kr_survey'));
            exit;
        }
    }
}
add_action('admin_init', 'kr_handle_revert_to_draft_action');

function kr_survey_redirect_draft_posts()
{
    if (is_singular('kr_survey')) {
        global $post, $wp_query;

        // Check if this is a preview request by looking for the 'preview' query parameter
        $is_preview = isset($_GET['preview']) && $_GET['preview'] == 'true';

        // Proceed with setting the page to 404 only if it's not published and not a preview request
        if ($post && $post->post_status !== 'publish' && !$is_preview) {
            $wp_query->set_404();
            status_header(404);

            // If no specific action for 404, output a simple Page Not Found message.
            nocache_headers();
            include(get_query_template('header'));
            echo '<h1>Page Not Found</h1><p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>';
            include(get_query_template('footer'));
            exit;
        }
    }
}
add_action('template_redirect', 'kr_survey_redirect_draft_posts');

/**
 * Remove the 'Add New' submenu for the 'kr_survey' post type
 *
 * It's not needed as the survey posts are auto added.
 * at
 * `Reaction Insight Pulse Preview` page
 */
function kr_remove_add_new_survey_menu()
{
    global $submenu;
    // Remove 'Add New' submenu for 'kr_survey'
    if (isset($submenu['edit.php?post_type=kr_survey'])) {
        foreach ($submenu['edit.php?post_type=kr_survey'] as $key => $submenu_array) {
            if (in_array('post-new.php?post_type=kr_survey', $submenu_array)) {
                unset($submenu['edit.php?post_type=kr_survey'][$key]);
            }
        }
    }
}
add_action('admin_menu', 'kr_remove_add_new_survey_menu');

// Prevent direct access to the 'Add New' page for 'kr_survey' post type - and redirect
function kr_redirect_add_new_survey()
{
    global $pagenow;
    // Check if current page is 'post-new.php' for 'kr_survey' post type
    if ($pagenow === 'post-new.php' && isset($_GET['post_type']) && $_GET['post_type'] === 'kr_survey') {
        wp_redirect(admin_url('edit.php?post_type=kr_survey'));
        exit;
    }
}
add_action('admin_init', 'kr_redirect_add_new_survey');

function kr_hide_add_new_post_button_survey()
{
    global $pagenow;

    if (($pagenow == 'edit.php') && isset($_GET['post_type']) && ($_GET['post_type'] == 'kr_survey')) {
        echo '<style>
            .page-title-action { display: none; }
        </style>';
    }
}
add_action('admin_head', 'kr_hide_add_new_post_button_survey');

// how to display your survey/poll logic
function kr_display_survey_display_preference_meta_box($post)
{
    $display_preference = get_post_meta($post->ID, '_kr_survey_display_preference', true);
?>
<p>Select how you want the survey/poll to be displayed to users. Choose among embedding it within content, showing it in
    a popup modal, or dedicating a whole page to it. This helps in tailoring the
    survey's visibility to the context of your site and user behavior.</p>
<select name="kr_survey_display_preference" id="kr_survey_display_preference" style="margin-bottom: 20px;">
    <option value="embedded" <?php selected($display_preference, 'embedded'); ?>>Embedded in Content</option>
    <option value="popup" <?php selected($display_preference, 'popup'); ?>>Popup Modal</option>
    <option value="dedicated_page" <?php selected($display_preference, 'dedicated_page'); ?>>Dedicated Page</option>
</select>
<?php
}

// Hook into the 'save_post' action to save the Display Preference when the post is saved.
function kr_save_display_preference($post_id)
{
    // Check if the current user has the 'manage_options' capability, typically available to site owners/administrators.
    if (!current_user_can('manage_options')) {
        return;
    }

    // Check for the presence of our custom field in the $_POST array.
    if (isset($_POST['kr_survey_display_preference'])) {
        // Sanitize the input.
        $display_preference = sanitize_text_field($_POST['kr_survey_display_preference']);
        // Update the meta field in the database.
        update_post_meta($post_id, '_kr_survey_display_preference', $display_preference);
    }
}
add_action('save_post', 'kr_save_display_preference');

function kr_display_survey_permalink_base_meta_box($post)
{
    // Use nonce for verification
    wp_nonce_field(basename(__FILE__), 'kr_survey_nonce');

    // Retrieve the current value if it exists
    $kr_permalink_base = get_post_meta($post->ID, '_kr_survey_permalink_base', true);

    // Display the permalink base
    echo '<p>Permalink Base: <strong>' . esc_html($kr_permalink_base) . '</strong></p>';
}


// Add a custom `Expiration Status` column to the post list table - survey expire logic
function kr_add_expiration_column($columns)
{
    $columns['survey_expiration'] = 'Expiration Status';
    return $columns;
}
add_filter('manage_kr_survey_posts_columns', 'kr_add_expiration_column');

// Populate the custom column with the expiration status
function kr_custom_column_content($column, $post_id)
{
    if ('survey_expiration' === $column) {
        $expiration_date = get_post_meta($post_id, '_kr_survey_expiration_date', true);
        $current_date = date('Y-m-d');
        echo (!empty($expiration_date) && $current_date > $expiration_date) ? '<span style="color:red;">Expired</span>' : 'Active';
    }
}
add_action('manage_kr_survey_posts_custom_column', 'kr_custom_column_content', 10, 2);


// Display function for the expiration date meta box
function kr_display_survey_expiration_date_meta_box($post)
{
    // Use nonce for verification
    wp_nonce_field(basename(__FILE__), 'kr_survey_nonce');

    // Retrieve the current expiration date if it exists
    $expiration_date = get_post_meta($post->ID, '_kr_survey_expiration_date', true);

    // Meta box HTML content
    echo '<p>Select the date after which this survey/poll will no longer be active:</p>';
    echo '<input type="date" name="kr_survey_expiration_date" value="' . esc_attr($expiration_date) . '" />';
    echo '<p class="description">Leave blank if the survey/poll does not expire.</p>';
}

function kr_save_survey_expiration_date($post_id)
{
    // Check if our nonce is set and verify the user has permission to save data
    if (!isset($_POST['kr_survey_nonce']) || !wp_verify_nonce($_POST['kr_survey_nonce'], basename(__FILE__)) || !current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Update the meta field in the database
    if (isset($_POST['kr_survey_expiration_date'])) {
        $expiration_date = sanitize_text_field($_POST['kr_survey_expiration_date']);
        update_post_meta($post_id, '_kr_survey_expiration_date', $expiration_date);
    }
}
add_action('save_post', 'kr_save_survey_expiration_date');

/**
 * Add a custom `View Responses` column to the post list table
 *
 * for the responses view of the current survey - if any
 * 
 */
function kr_add_survey_responses_column($columns)
{
    $columns['survey_responses'] = 'Responses';
    return $columns;
}
add_filter('manage_kr_survey_posts_columns', 'kr_add_survey_responses_column');

function kr_custom_survey_responses_column_content($column, $post_id)
{
    if ('survey_responses' === $column) {

        // You can use add_query_arg() to append query parameters if needed
        $view_responses_url = admin_url('admin.php?page=insight_pulse_response&survey_id=' . $post_id); // Example URL - adjust according to your setup

        echo '<a href="' . esc_url($view_responses_url) . '">View Responses</a>';
    }
}
add_action('manage_kr_survey_posts_custom_column', 'kr_custom_survey_responses_column_content', 10, 2);

function kr_add_insight_pulse_response_submenu_page()
{
    // Add submenu page and capture the returned hook suffix
    $hook_suffix = add_submenu_page(
        'katorymnd-plugins', // Parent slug: This should be the slug of your main Katorymnd menu.
        'Insight Pulse Response', // Page title
        'Reaction Insight Pulse Response', // Menu title
        'manage_options', // Capability
        'insight_pulse_response', // Menu slug
        'kr_insight_pulse_page_content' // Callback function for the page content
    );

    // Use the returned hook suffix to conditionally enqueue the Chart.js script
    add_action('admin_enqueue_scripts', function ($hook) use ($hook_suffix) {
        if ($hook === $hook_suffix) {
            wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '2.9.4', true);
        }
    });
}
add_action('admin_menu', 'kr_add_insight_pulse_response_submenu_page');


function kr_insight_pulse_page_content()
{
    echo '<div class="wrap">';
    // echo '<h1>Insight Pulse Response</h1>';

    $kr_survey_id = isset($_GET['survey_id']) ? intval($_GET['survey_id']) : 0;
    if ($kr_survey_id <= 0) {
        echo '<p>No responses found for this insight pulse.</p>';
        echo '</div>';
        return;
    }

    $kr_args = [
        'post_type' => 'kr_survey_response',
        'posts_per_page' => -1,
        'meta_query' => [['key' => '_kr_survey_id', 'value' => $kr_survey_id, 'compare' => '=']],
    ];
    $kr_query = new WP_Query($kr_args);
    $kr_questionsAndAnswers = []; // Aggregate question answers

    if ($kr_query->have_posts()) {
        $kr_questionsAndAnswers = [];
        while ($kr_query->have_posts()) {
            $kr_query->the_post();
            $kr_metas = get_post_meta(get_the_ID());

            foreach ($kr_metas as $kr_key => $kr_value) {
                if (!in_array($kr_key, ['_kr_linked_form_id', '_kr_survey_id'])) {
                    $kr_answers = json_decode($kr_value[0], true);
                    if (is_array($kr_answers)) {
                        foreach ($kr_answers as $kr_answer) {
                            if (!is_scalar($kr_answer)) continue;
                            $kr_questionsAndAnswers[$kr_key][$kr_answer] = ($kr_questionsAndAnswers[$kr_key][$kr_answer] ?? 0) + 1;
                        }
                    } else {
                        if (!is_scalar($kr_answers)) continue;
                        $kr_questionsAndAnswers[$kr_key][$kr_answers] = ($kr_questionsAndAnswers[$kr_key][$kr_answers] ?? 0) + 1;
                    }
                }
            }
        }
        // Inline style.
        echo '<style>
#kr_labelDialog {
    min-width: 300px; /* Minimum width of the dialog */
    max-width: 600px; /* Maximum width of the dialog */
}
</style>';

        echo '<div id="kr_charts" class="container-fluid"><div class="row">';
        $kr_chartDataScripts = [];
        $kr_identifier = 1;
        $kr_labelMapJS = [];

        foreach ($kr_questionsAndAnswers as $kr_question => $kr_answers) {
            $kr_chartId = sanitize_title($kr_question);
            $kr_labels = array_keys($kr_answers);
            $kr_data = array_values($kr_answers);
            $kr_truncatedLabels = [];
            $kr_labelMap = [];

            // Process labels for truncation and assign an identifier
            foreach ($kr_labels as $kr_label) {
                if (strlen($kr_label) > 20) { // Assuming 20 as the max length for truncation
                    $kr_truncatedLabels[] = (string)$kr_identifier;
                    $kr_labelMap[(string)$kr_identifier] = $kr_label;
                    $kr_identifier++;
                } else {
                    $kr_truncatedLabels[] = $kr_label;
                }
            }

            $kr_labelMapJS[$kr_chartId] = $kr_labelMap; // Store the mapping for JS

            echo '<div class="col-sm-12 col-md-6 mb-4">';
            echo '<h5>' . esc_html($kr_question) . '</h5>';
            echo '<div class="chart-container p-3 mb-5" style="height:400px; position: relative;">';
            echo '<canvas id="' . esc_attr($kr_chartId) . '" style="display: block; height: 100%; width: 100%;"></canvas></div></div>';

            $kr_chartDataScript = json_encode($kr_truncatedLabels);
            $kr_dataScript = json_encode($kr_data);

            $kr_chartDataScripts[] = <<<EOD
jQuery(document).ready(function(\$) {
    // Define a palette of colors for the chart.
    var colors = ["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#F77825", "#9966FF", "#C9CBCF", "#FF9F40"];
    
    // Dynamically add the dialog container to the body
    \$('body').append('<div id="kr_labelDialog" title="Full Label Detail" style="display:none;"></div>');
    
    var labelColors = {$kr_chartDataScript}.map(function(label, index) {
        return colors[index % colors.length]; // Cycle through the color palette
    });

    new Chart(document.getElementById("$kr_chartId").getContext("2d"), {
        type: "pie",
        data: {
            labels: {$kr_chartDataScript},
            datasets: [{
                data: {$kr_dataScript},
                backgroundColor: labelColors, // Use the dynamically assigned colors
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            onClick: function(evt, element) {
                if (element.length > 0) {
                    var index = element[0].index;
                    var identifier = this.data.labels[index];
                    var fullLabel = labelMap["$kr_chartId"][identifier] ? labelMap["$kr_chartId"][identifier] : identifier;
                    
                    // Update dialog content and open the dialog
                    \$("#kr_labelDialog").html(fullLabel.replace(/</g, "&lt;").replace(/>/g, "&gt;"));
                    \$("#kr_labelDialog").dialog({
                        modal: true,
                        buttons: {
                            Ok: function() {
                                \$(this).dialog("close");
                            }
                        },
                        width: 'auto',
                        open: function(event, ui) {
                            \$(this).dialog("option", "position", {my: "center", at: "center", of: window});
                        }
                    });
                }
            },
            tooltips: {
                enabled: true,
                mode: "index",
                intersect: false
            }
        }
    });
});
EOD;
        }

        echo '</div></div>'; // Close .row and .container-fluid

        if (!empty($kr_chartDataScripts)) {
            echo '<script>';
            echo 'var labelMap = ' . json_encode($kr_labelMapJS) . ';';
            echo 'document.addEventListener("DOMContentLoaded", function() {' . implode("\n", $kr_chartDataScripts) . '});';
            echo '</script>';
        }
    } else {
        echo '<p>No responses found for this insight pulse.</p>';
    }

    echo '</div>'; // Closing wrap div
    wp_reset_postdata();
}


// Function to insert  demo reports data on plugin activation
function krDemo_reports_activation()
{
    // Check if the demo sequences are not deactivated
    if (get_option('kr_deactivate_demo', '0') === '0') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

        $reports = [
            ['comment_id' => 'PC1', 'reason' => 'Offensive Language', 'details' => 'This is another sample for the logical set upd of the report', 'user_name' => 'UserY'],
            ['comment_id' => 'PC1', 'reason' => 'Spamming', 'details' => 'this is not goog and must be delt with', 'user_name' => 'demoUser'],
            ['comment_id' => '1234', 'reason' => 'Offensive Language', 'details' => 'Reported comment with ID 1234 containing profanity', 'user_name' => 'Tom'],
            ['comment_id' => '5678', 'reason' => 'Harassment and Bullying', 'details' => 'Reported comment with ID 5678 for repeatedly targeting others with abusive messages', 'user_name' => 'UserY'],
            ['comment_id' => '9012', 'reason' => 'Spamming', 'details' => 'Reported multiple spam messages with IDs 9012 promoting irrelevant products', 'user_name' => 'UserZ'],
            ['comment_id' => '3456', 'reason' => 'Misinformation and Fake News', 'details' => 'Reported post with ID 3456 spreading false information about recent events', 'user_name' => 'UserJ'],
            ['comment_id' => '7890', 'reason' => 'Hate Speech', 'details' => 'Reported comment with ID 7890 containing derogatory remarks against a particular group', 'user_name' => 'UserK'],
            ['comment_id' => '2345', 'reason' => 'Personal Attacks and Insults', 'details' => 'Reported user with ID 2345 for verbally attacking others in a discussion thread', 'user_name' => 'UserL'],
            ['comment_id' => '6789', 'reason' => 'Violence and Threats', 'details' => 'Reported message with ID 6789 containing threats of physical harm', 'user_name' => 'UserM'],
            ['comment_id' => '0123', 'reason' => 'Illegal Activities', 'details' => 'Reported post with ID 0123 promoting illicit drug sales', 'user_name' => 'UserN'],
            ['comment_id' => '4567', 'reason' => 'Invasion of Privacy', 'details' => 'Reported user with ID 4567 for sharing private information without consent', 'user_name' => 'UserO'],
            ['comment_id' => '8901', 'reason' => 'Sexually Explicit Content', 'details' => 'Reported post with ID 8901 containing explicit images', 'user_name' => 'UserC'],
            ['comment_id' => '4567', 'reason' => 'Invasion of Privacy', 'details' => 'Reported user with ID 4567 for sharing private information without consent', 'user_name' => 'UserF'],
            ['comment_id' => '7890', 'reason' => 'Hate Speech', 'details' => 'Reported comment with ID 7890 containing derogatory remarks against a particular group', 'user_name' => 'UserB'],
            ['comment_id' => '2345', 'reason' => 'Personal Attacks and Insults', 'details' => 'Reported user with ID 2345 for verbally attacking others in a discussion thread', 'user_name' => 'UserD'],
            ['comment_id' => '8901', 'reason' => 'Sexually Explicit Content', 'details' => 'Reported post with ID 8901 containing explicit images', 'user_name' => 'UserG'],
            ['comment_id' => '0123', 'reason' => 'Illegal Activities', 'details' => 'Reported post with ID 0123 promoting illicit drug sales', 'user_name' => 'UserH'],
            ['comment_id' => '5678', 'reason' => 'Harassment and Bullying', 'details' => 'Reported comment with ID 5678 for repeatedly targeting others with abusive messages', 'user_name' => 'UserI'],
            ['comment_id' => '3456', 'reason' => 'Misinformation and Fake News', 'details' => 'Reported post with ID 3456 spreading false information about recent events', 'user_name' => 'UserJ'],
            ['comment_id' => '9012', 'reason' => 'Spamming', 'details' => 'Reported multiple spam messages with IDs 9012 promoting irrelevant products', 'user_name' => 'UserK'],
            ['comment_id' => '6789', 'reason' => 'Violence and Threats', 'details' => 'Reported message with ID 6789 containing threats of physical harm', 'user_name' => 'UserL'],
            ['comment_id' => '1234', 'reason' => 'Offensive Language', 'details' => 'Reported comment with ID 1234 containing profanity', 'user_name' => 'UserM'],
            ['comment_id' => '7890', 'reason' => 'Hate Speech', 'details' => 'Reported comment with ID 7890 containing derogatory remarks against a particular group', 'user_name' => 'UserN'],
            ['comment_id' => '0123', 'reason' => 'Illegal Activities', 'details' => 'Reported post with ID 0123 promoting illicit drug sales', 'user_name' => 'UserO'],
            ['comment_id' => '4567', 'reason' => 'Invasion of Privacy', 'details' => 'Reported user with ID 4567 for sharing private information without consent', 'user_name' => 'UserP'],
            ['comment_id' => '5678', 'reason' => 'Harassment and Bullying', 'details' => 'Reported comment with ID 5678 for repeatedly targeting others with abusive messages', 'user_name' => 'UserQ'],
            ['comment_id' => '2345', 'reason' => 'Personal Attacks and Insults', 'details' => 'Reported user with ID 2345 for verbally attacking others in a discussion thread', 'user_name' => 'UserR'],
            ['comment_id' => '8901', 'reason' => 'Sexually Explicit Content', 'details' => 'Reported post with ID 8901 containing explicit images', 'user_name' => 'UserS'],
            ['comment_id' => '9012', 'reason' => 'Spamming', 'details' => 'Reported multiple spam messages with IDs 9012 promoting irrelevant products', 'user_name' => 'UserT'],
            ['comment_id' => '7890', 'reason' => 'Hate Speech', 'details' => 'Reported comment with ID 7890 containing derogatory remarks against a particular group', 'user_name' => 'UserU'],
            ['comment_id' => '7890', 'reason' => 'Hate Speech', 'details' => 'Reported comment with ID 7890 containing derogatory remarks against a particular group', 'user_name' => 'UserU']
        ];

        // Status options
        $status_options = ['open', 'closed', 'reviewing'];
        $status_index = 0; // Initialize index for cycling through statuses

        foreach ($reports as $report) {
            // Check if the report already exists
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table_name WHERE comment_id = %s AND reason = %s AND user_name = %s",
                $report['comment_id'],
                $report['reason'],
                $report['user_name']
            ));

            // If the report does not exist, insert it with dynamic status and report_date
            if ($exists == 0) {
                $data = [
                    'comment_id' => $report['comment_id'],
                    'reason' => $report['reason'],
                    'details' => $report['details'],
                    'user_name' => $report['user_name'],
                    'status' => $status_options[$status_index], // Set status dynamically
                    'report_date' => kr_getRandomReportDate(), // Generate a random report date
                ];

                $format = ['%s', '%s', '%s', '%s', '%s', '%s'];
                $wpdb->insert($table_name, $data, $format);

                // Move to the next status, cycling back to the start if necessary
                $status_index = ($status_index + 1) % count($status_options);
            }
        }
    }
}

function kr_getRandomReportDate()
{
    // Get the current date and year
    $currentDate = time();
    $year = date('Y');

    // Generate a random date between January 1st two years ago and the current date
    $start = strtotime(($year - 2) . '-01-01'); // January 1st, two years ago
    $end = $currentDate; // Use the current date and time as the upper limit

    // Generate a random timestamp between start and end
    $randomTimestamp = mt_rand($start, $end);

    // Format as MySQL date
    return date('Y-m-d H:i:s', $randomTimestamp);
}

register_activation_hook(__FILE__, 'krDemo_reports_activation');

/**
 * asigned user manage the  abuse reports logically 
 * 
 * this page will used to manage the abuse reports as by the  certified user
 * 
 */
function wpkr_report_abuse_page_content()
{
    require_once plugin_dir_path(__FILE__) . 'katorymnd_userManger_report_abuse.php';
}

// add demo comments  on the  activation  of the plugin
function add_kr_demo_Comment($id, $userName, $content, $parentId = null)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    // Check if the entry already exists
    $entryExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE id = %s", $id));

    if ($entryExists == 0) {
        // Entry does not exist, proceed to insert
        $wpdb->insert(
            $table_name,
            array(
                'id' => $id,
                'userName' => $userName,
                'content' => $content,
                'parent_id' => $parentId
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
}

function populate_kr_Comments()
{
    // Check if the demo sequences are not deactivated
    if (get_option('kr_deactivate_demo', '0') === '0') {
        // Primary Comments
        add_kr_demo_Comment('PC1', 'User1', "This is User1's primary comment.");
        add_kr_demo_Comment('PC2', 'User4', "This is User4's standalone comment.");

        // Replies to PC1
        add_kr_demo_Comment('PC1R1', 'User2A', 'This is a response from User2A to User1.', 'PC1');
        add_kr_demo_Comment('PC1R2', 'User6', 'This is a response from User6 to User1.', 'PC1');

        // Sub-replies to PC1R1
        add_kr_demo_Comment('PC1R1SR1', 'User3B', 'This is User3B also replying to User2A.', 'PC1R1');
        add_kr_demo_Comment('PC1R1SR2', 'User8', 'This is User8 also responding to User2A.', 'PC1R1');

        // Sub-replies to PC1R1SR1
        add_kr_demo_Comment('PC1R1SR1SSR1', 'User4C', 'User4C here, adding my thoughts to what User3B said.', 'PC1R1SR1');
        add_kr_demo_Comment('PC1R1SR1SSR2', 'User5D', "And this is User5D, chiming in after User4C's comments.", 'PC1R1SR1');

        // Sub-replies to PC1R1SR2
        add_kr_demo_Comment('PC1R1SR2SSR1', 'User7', 'User7 replying to User8 in PC1R1SR2.', 'PC1R1SR2');

        // Replies to PC2
        add_kr_demo_Comment('PC2R1', 'User5A', 'This is User5A responding to User4.', 'PC2');
    }
}

register_activation_hook(__FILE__, 'populate_kr_Comments');

// add demo reactions to logically on activation
function kr_addReaction($commentId, $userName, $reactionType)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_reactions';

    // Check if the reaction already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE comment_id = %s AND user_name = %s",
        $commentId,
        $userName
    ));

    // If the reaction does not exist, insert it
    if ($exists == 0) {
        $wpdb->insert(
            $table_name,
            array(
                'comment_id' => $commentId,
                'user_name' => $userName,
                'reaction_type' => $reactionType
            ),
            array('%s', '%s', '%s')
        );
    }
}


function kr_populateReactions()
{
    // Check if the demo sequences are not deactivated
    if (get_option('kr_deactivate_demo', '0') === '0') {
        $reactions = [
            ['PC1', 'Tom', 'like'],
            ['PC1', 'UserY', 'love'],
            ['PC1', 'UserZ', 'love'],
            ['PC1R1', 'UserJ', 'dislike'],
            ['PC1R1', 'UserK', 'cry'],
            ['PC1R1', 'UserL', 'cry'],
            ['PC1R1SR1', 'UserM', 'laugh'],
            ['PC1R1SR1', 'UserN', 'cry'],
            ['PC1R1SR1', 'UserO', 'cry'],
            ['PC1R1SR1SSR1', 'UserC', 'like'],
            ['PC1R1SR1SSR1', 'UserD', 'shock'],
            ['PC1R1SR1SSR1', 'UserE', 'shock'],
            ['PC1R1SR1SSR2', 'UserF', 'like'],
            ['PC1R1SR1SSR2', 'UserI', 'cry'],
            ['PC1R1SR1SSR2', 'UserJ', 'cry'],
            ['PC2', 'UserP', 'like'],
            ['PC2', 'UserQ', 'laugh'],
            ['PC2', 'UserR', 'laugh'],
            ['PC2R1', 'UserD', 'like'],
            ['PC2R1', 'UserE', 'smile'],
            ['PC2R1', 'UserA', 'smile'],
        ];


        foreach ($reactions as $reaction) {
            kr_addReaction($reaction[0], $reaction[1], $reaction[2]);
        }
    }
}

register_activation_hook(__FILE__, 'kr_populateReactions');


// Hook the autoloader setup function to the 'plugins_loaded' action
add_action('plugins_loaded', 'katorymnd_reaction_register_autoloader');

/**
 * Registers the autoloader for the Katorymnd Reaction plugin.
 */
function katorymnd_reaction_register_autoloader()
{
    require_once plugin_dir_path(__FILE__) . 'kr_autoloader.php';
}


function katorymnd_get_page_details()
{
    // Ensure to use the fully qualified name of the class
    $details = \Kr_page_details\Katorymnd_reaction\KatorymndUtility::get_kr_current_page_details();

    /**
     * Filters the details of the current page.
     *
     * This hook allows third-party developers to modify the page details, such as ID, slug, and title,
     * which can be particularly useful for integrating custom content types with the
     * Katorymnd Reactions plugin.
     *
     *
     * @param array $details An associative array of the current page details, including:
     *                       - 'id' (int|null) The ID of the current page, or null if not found.
     *                       - 'slug' (string|null) The slug of the current page, or null if not available.
     *                       - 'title' (string|null) The title of the current page, or null if not available.
     */
    $details = apply_filters('katorymnd_kr_page_details', $details);

    return $details;
}

function katorymnd_kr_is_user_not_active($username)
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'katorymnd_kr_user_details';
    $query = $wpdb->prepare("SELECT user_status FROM $table_name WHERE username = %s", $username);
    $user_status = $wpdb->get_var($query);

    // Check if the user's status is not 'active'
    if ($user_status !== 'active') {
        return true; // User is not active
    }
}


function kr_custom_get_current_user()
{
    if (!isset($_COOKIE['custom_user_session'])) {
        return false;
    }

    $token = $_COOKIE['custom_user_session'];

    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_custom_user_sessions';
    $session = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_name WHERE session_token = %s AND expires_at > CURRENT_TIMESTAMP",
        $token
    ));

    if (null === $session) {
        // Session not found or expired, clear the cookie
        if (!headers_sent()) {
            // It's safe to modify headers/cookies
            setcookie('custom_user_session', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        }
        return false;
    }

    // Fetch user details using the user ID from the session
    $user = kr_custom_get_user_by_id($session->user_id);
    if ($user) {
        return $user; // Returns an object with user details
    } else {
        ob_start();
        // User not found in custom user data table, possibly removed. Clear the session.
        $wpdb->delete($table_name, ['session_token' => $token], ['%s']); // Clean up the session from the database
        setcookie('custom_user_session', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true); // Expire the cookie
        ob_get_clean();
        return false; // Indicate the user needs to log in again
    }
}


// Before using the session management and user detail retrieval functionalities,
// configure your custom user table name as follows:
//this is the table that has the 'custom users` - not a default WP user table
set_custom_user_table_config_kr(get_option('katorymnd_kr_custom_table_name')); // the custom table  that  holds  the registerd users  that  we need  to use the details  for  our reaction

function kr_custom_get_user_by_id($user_id)
{
    global $wpdb, $customUserTableConfig;

    $table_name = isset($customUserTableConfig['user_table_name']) ? $customUserTableConfig['user_table_name'] : '';
    $mapping = get_option('kr_custom_user_table_mapping', []);
    $user_id_column = isset($mapping['user_id']) ? $mapping['user_id'] : 'user_id';

    if (empty($table_name)) {
        // Handle error: Table name not set
        return null;
    }

    $query = $wpdb->prepare("SELECT * FROM $table_name WHERE $user_id_column = %s", $user_id);
    $user = $wpdb->get_row($query);

    return $user;
}



function set_custom_user_table_mapping_kr($mapping)
{
    // Ensure the mapping array includes 'table_name' along with column mappings
    update_option('kr_custom_user_table_mapping', $mapping);
}

// Now update to include 'table_name':
set_custom_user_table_mapping_kr([
    'table_name' => get_option('katorymnd_kr_custom_table_name'), // Specify the custom table name here
    'user_id' => get_option('katorymnd_kr_user_id_column_name'),
    'username' => get_option('katorymnd_kr_username_column_name'),
    'email' => get_option('katorymnd_kr_email_column_name'),
    'full_names' => get_option('katorymnd_kr_full_names_column_name'),
    'avatar' => get_option('katorymnd_kr_avatar_column_name'),

]);

function check_if_table_exists_kr($tableName)
{
    global $wpdb;

    $prefixedTableName = $wpdb->prefix . $tableName;

    // Formulate the SQL to check for the table's existence
    $sql = $wpdb->prepare("SHOW TABLES LIKE %s", $prefixedTableName);

    // Execute the query and fetch the result
    $tableExists = $wpdb->get_var($sql);

    // Return true if the table exists, false otherwise
    return !empty($tableExists);
}


function set_custom_user_table_config_kr($tableNameWithoutPrefix)
{
    global $wpdb, $customUserTableConfig;

    // Automatically prepend the WordPress table prefix to the table name
    $fullTableName = $wpdb->prefix . $tableNameWithoutPrefix;

    $customUserTableConfig = [
        'user_table_name' => $fullTableName,
    ];
}



function katorymnd_reaction_initialize_user_session()
{
    // Admin logged in
    $current_user = wp_get_current_user();
    if ($current_user->exists()) {
        // Get user's current display name and email from their WP_User object
        $user_name = $current_user->display_name;
        $user_email = $current_user->user_email;

        // Retrieve the first and last name, set to null if not available
        $first_name = get_user_meta($current_user->ID, 'first_name', true);
        $last_name = get_user_meta($current_user->ID, 'last_name', true);
        $full_names = $first_name && $last_name ? $first_name . ' ' . $last_name : null;

        // Attempt to retrieve the current profile picture URL from Gravatar
        $profile_picture_url = get_avatar_url($current_user->ID);
        // Check if the Gravatar URL is the default one, and set to "no image" if so
        if (strpos($profile_picture_url, 'default') !== false) {
            $profile_picture_url = 'no image';
        }

        // Attempt to retrieve a custom user ID from user meta, fallback to WP user ID if not available
        $custom_user_id = get_user_meta($current_user->ID, 'custom_user_id', true);
        $user_id_to_use = !empty($custom_user_id) ? $custom_user_id : $current_user->ID;

        // Update user's name, email, full names, profile picture, and ID in user meta
        update_user_meta($current_user->ID, 'katorymnd_reaction_user_name', $user_name);
        update_user_meta($current_user->ID, 'katorymnd_reaction_user_email', $user_email);
        update_user_meta($current_user->ID, 'katorymnd_reaction_full_names', $full_names);
        update_user_meta($current_user->ID, 'katorymnd_reaction_profile_picture', $profile_picture_url);
        update_user_meta($current_user->ID, 'katorymnd_reaction_user_id', $user_id_to_use);
    }
}
add_action('init', 'katorymnd_reaction_initialize_user_session');

function katorymnd_kr_fetch_user_data()
{
    global $wpdb;
    $userDetailsTable = $wpdb->prefix . 'katorymnd_kr_user_details';

    // Define a function to update user data
    $updateUserData = function ($userData) use ($wpdb, $userDetailsTable) {
        // Check if user exists in the database by username
        $existingUser = $wpdb->get_row($wpdb->prepare("SELECT * FROM $userDetailsTable WHERE username = %s", $userData['username']));

        // If the user doesn't exist, insert new user data
        if (!$existingUser) {
            $wpdb->insert($userDetailsTable, $userData);
        } else {
            // Check if any user details have changed
            $needsUpdate = false;
            foreach ($userData as $key => $value) {
                if ($existingUser->$key != $value) {
                    $needsUpdate = true;
                    break;
                }
            }

            // If details have changed, update the user data
            if ($needsUpdate) {
                $wpdb->update($userDetailsTable, $userData, ['username' => $userData['username']]);
            }
        }
    };

    $custom_user = kr_custom_get_current_user();
    if ($custom_user) {
        // Prepare user data from the custom session management
        $userData = [
            'username' => $custom_user->username,
            'email' => $custom_user->email,
            'full_name' => $custom_user->full_names,
            'avatar_url' => $custom_user->avatar,
        ];
        // Update or insert user data
        $updateUserData($userData);

        wp_send_json_success([
            'userID' => $custom_user->user_id,
            'userName' => $custom_user->username,
            'fullNames' => $custom_user->full_names,
            'userEmail' => $custom_user->email,
            'avatar' => $custom_user->avatar,
        ]);
    } else {
        $current_user = wp_get_current_user();
        if ($current_user->exists()) {
            // Retrieve WordPress user details
            $first_name = get_user_meta($current_user->ID, 'first_name', true);
            $last_name = get_user_meta($current_user->ID, 'last_name', true);
            $full_names = (!empty($first_name) && !empty($last_name)) ? "{$first_name} {$last_name}" : "no names";

            $userData = [
                'username' => $current_user->user_login,
                'email' => $current_user->user_email,
                'full_name' => $full_names,
                'avatar_url' => get_avatar_url($current_user->ID),
            ];
            // Update or insert user data
            $updateUserData($userData);

            wp_send_json_success([
                'userID' => $current_user->ID,
                'userName' => $userData['username'],
                'userEmail' => $userData['email'],
                'fullNames' => $userData['full_name'],
                'profilePicture' => $userData['avatar_url'],
            ]);
        } else {
            wp_send_json_error(['message' => 'User not found.']);
        }
    }
}


function katorymnd_kr_add_user_details($username, $email, $fullname, $avatar_url)
{
    do_action('katorymnd_kr_add_user_details', $username, $email, $fullname, $avatar_url);
}

/**
 * Hook to add user details to the katorymnd reaction plugin. Developers can use this
 * for both single and batch user details addition.
 *
 * @param mixed $userDetails Single user details or an array of multiple user details.
 */
function add_custom_user_to_katorymnd_kr($userDetails)
{
    // Directly pass $userDetails to handle both single and batch processing
    $result = Kr_user_details\Katorymnd_reaction\KatorymndUserDetails::processIncomingUserDetails($userDetails);

    if (is_array($result) && !empty($result)) { // If errors were returned
        // Store the errors in a transient, to show them later in an admin notice
        set_transient('katorymnd_user_details_errors', $result, 45);
    }
}

// Dynamically handle the action's arguments for both individual and batch inputs
add_action('katorymnd_kr_add_user_details', function () {
    $args = func_get_args();

    // Determine if the action is called with individual user details or a batch array
    if (isset($args[0]) && is_array($args[0]) && !isset($args[0]['username'])) {
        // Batch processing (array of arrays) - directly pass the argument
        add_custom_user_to_katorymnd_kr($args[0]);
    } else {
        // Individual user details - construct an associative array from the arguments
        $userDetails = [
            'username' => $args[0] ?? null,
            'email' => $args[1] ?? null,
            'fullname' => $args[2] ?? null,
            'avatar_url' => $args[3] ?? null,
        ];
        add_custom_user_to_katorymnd_kr($userDetails);
    }
}, 10, 4);


function katorymnd_display_user_details_errors()
{
    // Check if there are any errors stored in the transient
    if ($errors = get_transient('katorymnd_user_details_errors')) {
        // Display each error in an admin notice
        foreach ($errors as $error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html(is_array($error) ? implode(', ', $error) : $error) . '</p></div>';
        }
        // Delete the transient to avoid showing the same notice multiple times
        delete_transient('katorymnd_user_details_errors');
    }
}
add_action('admin_notices', 'katorymnd_display_user_details_errors');


// Do nothing on plugin deactivation
function katorymnd_delete_reaction_table()
{
    // Code to delete data has been removed to prevent data loss on deactivation.
}

register_deactivation_hook(__FILE__, 'katorymnd_delete_reaction_table');

function katorymnd_reaction_plugin_enqueue_resources()
{
    // Paths to scripts and styles within the plugin
    $base_url = plugin_dir_url(__FILE__);
    $script_path = $base_url . 'js/katorymnd_qjsk.js';
    $alert_script_path = $base_url . 'js/kr-alert.js'; // Path to the custom alert script
    $style_path = $base_url . 'css/katorymnd_wdsn.css';
    $alert_style_path = $base_url . 'css/kr-alert.css'; // Path to the custom alert stylesheet

    // Enqueue noUiSlider JavaScript and CSS
    wp_enqueue_script('nouislider_js', 'https://cdn.jsdelivr.net/npm/nouislider/distribute/nouislider.min.js', [], '14.7.0', true);
    wp_enqueue_style('nouislider_css', 'https://cdn.jsdelivr.net/npm/nouislider/distribute/nouislider.min.css', [], '14.7.0');


    // Enqueue the main plugin style
    wp_enqueue_style('katorymnd_vubc_css', $style_path, [], '1.2.0');

    // Enqueue WordPress dialog script and style for front-end use
    wp_enqueue_script('jquery-ui-dialog');
    wp_enqueue_style('wp-jquery-ui-dialog');

    // Register and enqueue the main plugin script with dependencies
    wp_register_script('katorymnd_vubc_js', $script_path, ['jquery', 'wp-api', 'jquery-ui-dialog'], '1.2.0', true);
    wp_enqueue_script('katorymnd_vubc_js');

    // Register and enqueue the custom alert script
    wp_register_script('katorymnd_alert_js', $alert_script_path, ['jquery'], '1.2.0', true);
    wp_enqueue_script('katorymnd_alert_js');

    // Register and enqueue the custom alert stylesheet
    wp_register_style('katorymnd_alert_css', $alert_style_path, [], '1.2.0');
    wp_enqueue_style('katorymnd_alert_css');


    // Inline script settings
    $username = is_user_logged_in() && current_user_can('manage_options') ? wp_get_current_user()->user_login : 'not admin';
    $inline_script = sprintf(
        'let jmby = %s; let pwy = %s; let savedEmojiTheme = %s; let userChosenEmojis = %s; let savedHeaderBgColor = %s; let katorymnd_98lq9yd = %s; let wpkrNumComments = %s;',
        json_encode('Katorymnd Freelancer'),
        json_encode($base_url),
        json_encode(get_option('katorymnd_emoji_theme', 'default')),
        json_encode(get_option('katorymnd_emoji_selection', ['like', 'dislike', 'love', 'smile', 'laugh', 'angry', 'cry', 'shock'])),
        json_encode(get_option('katorymnd_header_bg_color', 'rgb(0, 123, 255)')),
        json_encode($username),
        json_encode(get_option('wpkr_num_comments', '1')) // Default to '1' if not set
    );
    wp_add_inline_script('katorymnd_vubc_js', $inline_script, 'before');

    // Localize script with nonce and other settings for AJAX calls
    $script_settings = [
        'nonce' => wp_create_nonce('wp_rest'),
        'ajax_url' => admin_url('admin-ajax.php'),
        'rest_url' => rest_url(),
    ];
    wp_localize_script('katorymnd_vubc_js', 'wpApiSettings', $script_settings);
}

add_action('wp_enqueue_scripts', 'katorymnd_reaction_plugin_enqueue_resources');
add_action('admin_enqueue_scripts', 'katorymnd_reaction_plugin_enqueue_resources');
add_action('login_enqueue_scripts', 'katorymnd_reaction_plugin_enqueue_resources');

add_action('rest_api_init', 'register_katorymnd_kr_routes');

// Register API to post the form data
function register_katorymnd_kr_routes()
{
    register_rest_route('katorymnd/v1', '/submit_comment/', [
        'methods' => 'POST',
        'callback' => 'process_submit_comment',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('katorymnd/v1', '/fetch_data/', [
        'methods' => 'GET',
        'callback' => 'katorymnd_kr_fetch_data',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('katorymnd/v1', '/update_reaction/', [
        'methods' => 'POST',
        'callback' => 'sendReactionUpdate_kr',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('katorymnd/v1', '/report_abuse/', [
        'methods' => 'POST',
        'callback' => 'sendReportAbuse_kr',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('katorymnd/v1', '/delete_comment/', [
        'methods' => 'POST',
        'callback' => 'katorymnd_kr_delete_comment',
        'permission_callback' => '__return_true',
    ]);
    // route for saving or updating comments
    register_rest_route('katorymnd/v1', '/save_comment/', [
        'methods' => 'POST',
        'callback' => 'katorymnd_kr_save_comment',
        'permission_callback' => '__return_true',
    ]);
    // Route for updating a comment
    register_rest_route('katorymnd/v1', '/update_comment/', [
        'methods' => 'POST',
        'callback' => 'katorymnd_kr_update_comment',
        'permission_callback' => '__return_true',
    ]);

    // get logged in user data
    register_rest_route('katorymnd/v1', '/fetch_user_data/', [
        'methods' => 'GET',
        'callback' => 'katorymnd_kr_fetch_user_data',
        'permission_callback' => '__return_true',
    ]);

    // get comment Metrics data
    register_rest_route('katorymnd/v1', '/fetch_comment_data_analysis/', [
        'methods' => 'GET',
        'callback' => 'katorymnd_kr_fetch_metric_data',
        'permission_callback' => '__return_true',
    ]);

    // save the rating details
    register_rest_route('katorymnd/v1', '/submit_rating/', [
        'methods' => 'POST',
        'callback' => 'katorymnd_kr_submit_rating',
        'permission_callback' => '__return_true',
    ]);

    register_rest_route('katorymnd/v1', '/fetch_InsightPulse_data/', [
        'methods' => 'POST',
        'callback' => 'katorymnd_kr_fetch_InsightPulse_data',
        'permission_callback' => '__return_true',
    ]);

    // save the survey details
    register_rest_route('katorymnd/v1', '/save_InsightPulse_user_data/', [
        'methods' => 'POST',
        'callback' => 'katorymnd_kr_save_InsightPulse_user_data',
        'permission_callback' => '__return_true',
    ]);
}

// Callback function for saving the survey details
function katorymnd_kr_save_InsightPulse_user_data(WP_REST_Request $request)
{
    $params = $request->get_json_params();

    // Check for the necessary parameters
    if (!isset($params['formId'], $params['user'], $params['questionsAndAnswers'])) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Missing required survey details.',
        ], 400); // Bad Request
    }

    // Validate that 'surveyId' is a number
    if (!isset($params['surveyId']) || !is_numeric($params['surveyId'])) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Invalid survey ID. Survey ID must be a number.',
        ], 400); // Bad Request
    }

    // Check if the survey post exists and is published
    $surveyPostStatus = get_post_status($params['surveyId']);
    if (!$surveyPostStatus) {
        // If the survey doesn't exist, first check if there are responses linked to this survey ID
        $responses = get_posts([
            'post_type' => 'kr_survey_response',
            'numberposts' => -1,
            'fields' => 'ids',
            'meta_query' => [
                [
                    'key' => '_kr_survey_id',
                    'value' => $params['surveyId'],
                    'compare' => '='
                ]
            ]
        ]);

        if (!empty($responses)) { // Check if there are any responses
            foreach ($responses as $responseId) {
                wp_delete_post($responseId, true); // Proceed with deletion
            }

            return new WP_REST_Response(array(
                'status' => 'error',
                'message' => 'The survey with the provided ID does not exist. All linked responses have been deleted.',
            ), 404); // Not Found
        } else {
            // If there are no responses, just return the error
            return new WP_REST_Response(array(
                'status' => 'error',
                'message' => 'The survey with the provided ID does not exist.',
            ), 404); // Not Found
        }
    }


    // Check for existing response from this user for the current survey
    $existingResponse = get_posts([
        'post_type' => 'kr_survey_response',
        'meta_query' => [
            ['key' => '_kr_survey_id', 'value' => $params['surveyId']],
            ['key' => '_kr_username', 'value' => $params['user']],
        ],
        'fields' => 'ids',
        'numberposts' => 1,
    ]);

    if (!empty($existingResponse)) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'User has already submitted a response to this survey.',
        ], 403); // Forbidden
    }

    // Create a new survey response post
    $responsePostId = wp_insert_post([
        'post_type' => 'kr_survey_response',
        'post_status' => 'publish',
        'post_title' => sprintf('Survey Response from User %s', $params['user']),
    ]);

    if (is_wp_error($responsePostId)) {
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Failed to save response.',
        ], 500); // Internal Server Error
    }

    // Link this response to the survey and store the user identifier
    update_post_meta($responsePostId, '_kr_linked_form_id', $params['formId']);
    update_post_meta($responsePostId, '_kr_survey_id', $params['surveyId']);
    update_post_meta($responsePostId, '_kr_username', $params['user']); // Store the username for tracking

    // Save each question and answer pair
    foreach ($params['questionsAndAnswers'] as $qa) {
        $answers = is_array($qa['answers']) ? $qa['answers'] : [$qa['answers']];
        update_post_meta($responsePostId, sanitize_text_field($qa['question']), sanitize_text_field(json_encode($answers)));
    }

    return new WP_REST_Response([
        'status' => 'success',
        'message' => 'Received the data successfully.',
        'data' => [
            'user' => sprintf('User: %s has submitted the form ID: %s', $params['user'], $params['formId']),
            'formId' => $params['formId'],
            'surveyId' => $params['surveyId'],
            'questionsAndAnswers' => $params['questionsAndAnswers'],
        ]
    ], 200);
}


function katorymnd_kr_fetch_InsightPulse_data(WP_REST_Request $request)
{
    // The survey ID is sent in the request body and accessed via 'surveyId'
    $survey_id = $request->get_param('surveyId');

    if (!empty($survey_id) && get_post_type($survey_id) === 'kr_survey') {
        // Retrieve the survey HTML from post meta
        $survey_html = get_post_meta($survey_id, '_kr_survey_html', true);

        // Retrieve the permalink base, default to 'survey' if not found
        $kr_permalink_base = get_post_meta($survey_id, '_kr_survey_permalink_base', true) ?: 'survey';

        // Prepare response
        if (!empty($survey_html)) {
            // Success: Return the survey HTML and permalink base
            return new WP_REST_Response(array('html' => $survey_html, 'permalinkBase' => $kr_permalink_base), 200);
        } else {
            // No survey HTML found, return an error message
            return new WP_REST_Response(array('message' => 'Survey HTML not found.', 'permalinkBase' => $kr_permalink_base), 404);
        }
    }

    // Invalid survey ID or survey not found
    return new WP_REST_Response(array('message' => 'Survey not found or invalid ID.', 'permalinkBase' => 'survey'), 404);
}


function katorymnd_kr_submit_rating(WP_REST_Request $request)
{
    // Extract parameters from the request
    $rating = $request->get_param('rating');
    $userName = $request->get_param('userName');
    $pageId = $request->get_param('pageId');

    // Verify that all required parameters are available and non-empty
    if (empty($rating) || empty($userName) || empty($pageId)) {
        return new WP_REST_Response(array('message' => 'Missing required parameter(s).'), 400);
    }

    // Check if the user is active or not
    if (katorymnd_kr_is_user_not_active($userName)) {
        // Return an error if the user is not active
        return new WP_Error(
            'banned_user', // Error code
            'You are banned from using this service.', // Error message
            array('status' => 400) // Data with HTTP status code
        );
    }

    // Process the rating data
    $save_status = save_rating_to_database($rating, $userName, $pageId);

    if ($save_status) {
        // If saving was successful, send a success response
        return new WP_REST_Response(array('message' => 'Rating submitted successfully!'), 200);
    } else {
        // If there was an error, send an error response
        return new WP_REST_Response(array('message' => 'Failed to submit rating.'), 500);
    }
}

function save_rating_to_database($rating, $userName, $pageId)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_user_ratings';

    // Check if the user has already rated this page
    $existingRating = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM $table_name WHERE user_identifier = %s AND page_id = %d",
            $userName,
            $pageId
        )
    );

    if (is_null($existingRating)) {
        // Attempt to insert a new rating
        $result = $wpdb->insert($table_name, array(
            'user_identifier' => $userName,
            'page_id'         => $pageId,
            'rating'          => $rating,
            'created_at'      => current_time('mysql', 1)
        ), array('%s', '%d', '%d', '%s'));

        // Check for successful insertion
        if ($result === false) {
            error_log("Error inserting rating: " . $wpdb->last_error);
            return false;
        }
    } else {
        // Attempt to update the existing rating
        $result = $wpdb->update($table_name, array(
            'rating' => $rating,
            'created_at' => current_time('mysql', 1)
        ), array('id' => $existingRating->id), array('%d', '%s'), array('%d'));

        // Check for errors in the update operation
        if ($result === false) {
            error_log("Error updating rating: " . $wpdb->last_error);
            return false;
        }
    }

    // Operation was successful, clear the transient
    delete_transient('kr_overall_rating_' . $pageId);
    return true; // Explicitly indicate success
}


function get_overall_rating($pageId)
{
    $transient_key = 'kr_overall_rating_' . $pageId;
    $overall_rating = get_transient($transient_key);

    if (false === $overall_rating) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'katorymnd_kr_user_ratings';
        $results = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT AVG(rating) AS average_rating FROM $table_name WHERE page_id = %d",
                $pageId
            )
        );

        // Ensure $results->average_rating is not null before rounding
        $average_rating = !empty($results) && null !== $results->average_rating ? $results->average_rating : 0;
        $overall_rating = round($average_rating, 2);

        // Cache for a shorter period, e.g., 5 minutes
        set_transient($transient_key, $overall_rating, 5 * MINUTE_IN_SECONDS);
    }

    return $overall_rating;
}

function getTotalRepliesCount($commentId)
{
    global $wpdb;
    $comments_table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    if ($commentId === 0) {
        $commentId = null;
    }

    $totalReplies = 0;
    $toProcess = [$commentId];
    $processed = [];

    while (!empty($toProcess)) {
        $currentParentId = array_shift($toProcess);

        if (in_array($currentParentId, $processed)) {
            continue;
        }

        $processed[] = $currentParentId;

        $query = $wpdb->prepare("SELECT id FROM $comments_table_name WHERE parent_id = %s", $currentParentId);
        $childComments = $wpdb->get_col($query);

        $totalReplies += count($childComments);

        foreach ($childComments as $childId) {
            $toProcess[] = $childId;
        }
    }

    return $totalReplies;
}


function katorymnd_kr_fetch_metric_data()
{
    global $wpdb;
    $tableName = $wpdb->prefix . "katorymnd_kr_intialid_commentid_page";

    // Fetch initial comment data
    $query = "SELECT comment_id, page_id, page_slug, page_title, username, created_at FROM {$tableName}";
    $results = $wpdb->get_results($query);

    // Count the total comments grouped by page_id
    $countQuery = "SELECT page_id, COUNT(*) as total_comments FROM {$tableName} GROUP BY page_id";
    $comments_count_by_page = $wpdb->get_results($countQuery, OBJECT_K);

    // Initialize an array to hold the total replies count by page
    $total_replies_by_page = [];

    foreach ($results as &$result) {
        $totalReplies = getTotalRepliesCount($result->comment_id);
        $result->total_replies = $totalReplies;

        // Accumulate total replies count by page
        if (!isset($total_replies_by_page[$result->page_id])) {
            $total_replies_by_page[$result->page_id] = 0;
        }
        $total_replies_by_page[$result->page_id] += $totalReplies;
    }

    // Proceed if results are not empty
    if (!empty($results)) {
        // Calculate overall totals for comments and replies
        $overall_total_comments = array_sum(array_map(function ($item) {
            return $item->total_comments;
        }, $comments_count_by_page));

        $overall_total_replies = array_sum($total_replies_by_page);

        // Sum of all comments and replies
        $grand_total = $overall_total_comments + $overall_total_replies;

        // Prepare the data array including the grand total
        $data = [
            'comments' => $results,
            'total_comments_by_page' => $comments_count_by_page,
            'total_replies_by_page' => $total_replies_by_page,
            'overall_total_comments' => $overall_total_comments,
            'overall_total_replies' => $overall_total_replies,
            'grand_total' => $grand_total
        ];

        wp_send_json_success($data);
    } else {
        $data = [
            'comments' => [],
            'total_comments_by_page' => [],
            'total_replies_by_page' => [],
            'overall_total_comments' => 0,
            'overall_total_replies' => 0,
            'grand_total' => 0
        ];
        wp_send_json_success($data);
    }
}

// add comment reply to the database 
$spam_keywords = [
    // Sales-oriented language
    'buy now', 'free offer', 'guaranteed', 'no risk',

    // Sensitive keywords
    'pharmaceuticals', 'adult content', 'financial offer',
];

$spam_patterns = [
    // Patterns for detecting poorly written content might include excessive use of exclamation marks, etc.
    '/\b(?:win|won|winner|claim|free|prize|selected|chosen|congratulations|congrats)\b/i',

];


function katorymnd_kr_is_pii_content($content)
{
    // Fetch the current filter settings
    $filter_settings = get_option('katorymnd_filter_settings', [
        'filter_url' => '1',
        'filter_email' => '1',
        'filter_anchor' => '1',
        'filter_phone' => '1',
        'filter_spam_keywords' => '1',
        'filter_spam_patterns' => '1',
    ]);

    // Define patterns conditionally based on settings
    $pii_patterns = [];
    if ($filter_settings['filter_email'] === '1') {
        $pii_patterns[] = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}/'; // Email addresses
    }
    if ($filter_settings['filter_phone'] === '1') {
        $pii_patterns[] = '/\b\d{3}[-.]?\d{3}[-.]?\d{4}\b/'; // Phone numbers
    }
    if ($filter_settings['filter_url'] === '1') {
        $pii_patterns[] = '/\bhttps?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}(\/\S*)?/'; // URLs
    }
    if ($filter_settings['filter_anchor'] === '1') {
        $pii_patterns[] = '/<a\s+href="[^"]*"[^>]*>(.*?)<\/a>/'; // HTML anchor tags
    }

    // Check content against enabled patterns
    foreach ($pii_patterns as $pattern) {
        if (preg_match($pattern, $content)) {
            return true; // PII match found
        }
    }

    return false; // No PII detected
}

function katorymnd_kr_is_spam_content($content)
{
    $filter_settings = get_option('katorymnd_filter_settings', [
        'filter_url' => '1',
        'filter_email' => '1',
        'filter_anchor' => '1',
        'filter_phone' => '1',
        'filter_spam_keywords' => '1',
        'filter_spam_patterns' => '1',
    ]);

    global $spam_keywords, $spam_patterns;

    // Spam Keywords Check
    if ($filter_settings['filter_spam_keywords'] === '1') {
        foreach ($spam_keywords as $keyword) {
            if (stripos($content, $keyword) !== false) return true;
        }
    }

    // Check against regular expression patterns
    if ($filter_settings['filter_spam_patterns'] === '1') {
        foreach ($spam_patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                // Contextual check: If 'congrats' or 'congratulations' is found, perform additional checks
                if (in_array(strtolower($matches[0]), ['congrats', 'congratulations', 'win', 'won', 'claim'])) {
                    // For example, check if the message also contains URLs
                    if (preg_match('/https?:\/\/\S+/i', $content)) {
                        return true; // Likely spam due to combination of congratulatory word and URL
                    }
                    // Add other contextual checks as needed
                } else {
                    return true; // Pattern match found, likely spam
                }
            }
        }
    }
    return false;
}

function sendReportAbuse_kr(WP_REST_Request $request)
{
    global $wpdb;
    // Retrieve parameters from the request
    $params = $request->get_json_params();

    // These; 'commentId', 'reason', 'details', and 'userName' are required
    if (!isset($params['commentId'], $params['reason'], $params['details'], $params['userName'])) {
        // If required parameters are missing, send an error response
        return new WP_REST_Response(array(
            'error' => 'Missing required fields',
            'message' => 'Missing required fields',
        ), 200);
    }

    // Sanitize each parameter
    $commentId = (string) sanitize_text_field($params['commentId']);
    $reason = sanitize_text_field($params['reason']);
    $details =  stripslashes(sanitize_textarea_field($params['details'])); // Specifically for textarea inputs
    $userName = sanitize_text_field($params['userName']);

    // Validate comment content length
    if (empty($details) || strlen(trim($details)) < 10) {
        return new WP_REST_Response(array('error' => 'Report is too short', 'message' => 'Report is too short. Please provide more detail.'), 400);
    }

    // Check for PII in the comment content
    if (katorymnd_kr_is_pii_content($details)) {
        // If potential PII is found, return an error response
        return new WP_REST_Response(array('error' => 'PII detected', 'message' => 'Please do not include personal information such as email addresses or phone numbers in your Report.'), 400);
    }


    // Perform the spam content check
    if (katorymnd_kr_is_spam_content($details)) {
        // If content is likely spam, return an error response
        return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Warning. Your Report is not sent.'), 400);
    }

    if (katorymnd_kr_is_user_not_active($userName)) {
        // Handle the case where the user is not active
        return new WP_REST_Response([
            'error' => 'Banned User',
            'message' => 'You are banned from using this service.'
        ], 400); // Using HTTP status code 403 to indicate that the action is forbidden
    }

    // Akismet spam check
    $akismet_api_key = get_option('akismet_api_key');
    if (!empty($akismet_api_key)) {
        $akismet_data = array(
            'blog' => get_home_url(),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'comment_type' => 'report',
            'comment_author' => $userName,
            'comment_author_email' => '',
            'comment_content' => $details,
        );

        $response = wp_remote_post("https://{$akismet_api_key}.rest.akismet.com/1.1/comment-check", array(
            'body' => $akismet_data,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        ));

        if (is_wp_error($response)) {
            error_log('Akismet request failed: ' . $response->get_error_message());
        } else {
            $akismet_response = wp_remote_retrieve_body($response);
            if ('true' === $akismet_response) {
                return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Your Report looks like spam.'), 400);
            }
        }
    }

    // Load disallowed words from bad_words.txt and simple profanity filter
    $bad_words_path = plugin_dir_path(__FILE__) . 'bad_words.txt';
    $disallowed_words = file_exists($bad_words_path) ? file($bad_words_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    foreach ($disallowed_words as $word) {
        if (stripos($details, $word) !== false) {
            return new WP_REST_Response(array('error' => 'Inappropriate language', 'message' => 'Please avoid using inappropriate language in Report.'), 400);
        }
    }

    // Sanitize the report content without allowing <a> tags
    $report_content = wp_kses($details, array(
        'br' => array(),
        'em' => array(),
        'strong' => array(),
    ));

    // Define the table name
    $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

    // Check if a report by this user for this comment ID already exists
    $existing_report = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE comment_id = %s AND user_name = %s",
        $commentId,
        $userName
    ));

    // If an existing report is found, return an error response
    if ($existing_report > 0) {
        return new WP_REST_Response(array(
            'error' => 'Duplicate report',
            'message' => 'You have already submitted a report for this comment.',
        ), 400); // Use HTTP status code 400 for client-side errors
    }

    // Prepare data for insertion if no duplicate was found
    $data = array(
        'comment_id' => $commentId,
        'reason' => $reason,
        'details' => $report_content,
        'user_name' => $userName,
        'report_date' => current_time('mysql', 1),
        'status' => 'open',
    );

    // Define the format of the data to insert
    $format = array('%s', '%s', '%s', '%s', '%s', '%s');

    // Insert the data into the database
    $success = $wpdb->insert($table_name, $data, $format);

    // Check if the insert was successful
    if ($success) {
        return new WP_REST_Response(array(
            'success' => true,
            'message' => 'Report submitted successfully.',
        ), 200);
    } else {
        // Log the error. You can use error_log() or any other logging mechanism
        error_log('Failed to insert abuse report: ' . $wpdb->last_error);

        // Return an error response
        return new WP_REST_Response(array(
            'error' => 'Database error',
            'message' => 'Could not submit the report due to a database error.',
        ), 500); // Use HTTP status code 500 for server errors
    }
}

function katorymnd_kr_save_comment(WP_REST_Request $request)
{
    global $wpdb;
    $data = $request->get_json_params();
    $table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    // Validate required fields
    if (empty($data['userName']) || empty($data['content']) || empty($data['id']) || empty($data['parentId'])) {
        return new WP_REST_Response([
            'error' => 'Missing required fields',
            'message' => 'Missing required comment fields.'
        ], 400);
    }

    // Perform the spam content check
    if (katorymnd_kr_is_spam_content($data['content'])) {
        // If content is likely spam, return an error response and do not save to the database
        return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Spam detected. Your comment is pending review.'), 400);
    }

    if (katorymnd_kr_is_user_not_active($data['userName'])) {
        // Handle the case where the user is not active by returning a WP_Error
        return new WP_Error(
            'banned_user', // Error code
            'You are banned from using this service.', // Error message
            array('status' => 400) // Data with HTTP status code
        );
    }

    // Akismet spam check
    $akismet_api_key = get_option('akismet_api_key');
    if (!empty($akismet_api_key)) {
        $akismet_data = array(
            'blog' => get_home_url(),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'comment_type' => 'comment',
            'comment_author' => '',
            'comment_author_email' => '',
            'comment_content' => $data['content'],
        );

        $response = wp_remote_post("https://{$akismet_api_key}.rest.akismet.com/1.1/comment-check", array(
            'body' => $akismet_data,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        ));

        if (is_wp_error($response)) {
            error_log('Akismet request failed: ' . $response->get_error_message());
        } else {
            $akismet_response = wp_remote_retrieve_body($response);
            if ('true' === $akismet_response) {
                return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Your comment looks like spam.'), 400);
            }
        }
    }

    // Validate comment content length
    if (empty($data['content']) || strlen(trim($data['content'])) < 10) {
        return new WP_REST_Response(array('error' => 'Comment is too short', 'message' => 'Comment is too short. Please provide more detail.'), 400);
    }

    // Load disallowed words from bad_words.txt and simple profanity filter
    $bad_words_path = plugin_dir_path(__FILE__) . 'bad_words.txt';
    $disallowed_words = file_exists($bad_words_path) ? file($bad_words_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    foreach ($disallowed_words as $word) {
        if (stripos($data['content'], $word) !== false) {
            return new WP_REST_Response(array('error' => 'Inappropriate language', 'message' => 'Please avoid using inappropriate language in comments.'), 400);
        }
    }

    // Time Frame for Checking Duplicates
    $current_time = current_time('mysql');
    $duplication_window = 5 * MINUTE_IN_SECONDS; // Checking for duplicates within the last 5 minutes
    $time_limit = date('Y-m-d H:i:s', strtotime($current_time) - $duplication_window);

    // Query for recent comments with the same content and userName
    $duplicate_check = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE userName = %s AND content = %s AND timestamp > %s",
        sanitize_text_field($data['userName']),
        stripslashes(sanitize_textarea_field($data['content'])),
        $time_limit
    ));

    if ($duplicate_check > 0) {
        // Duplicate comment found within the time frame
        return new WP_REST_Response(array('error' => 'Duplicate comment', 'message' => 'Duplicate comment detected. Please wait before posting a similar comment again.'), 400);
    }

    // Check for PII in the comment content
    if (katorymnd_kr_is_pii_content($data['content'])) {
        // If potential PII is found, return an error response
        return new WP_REST_Response(array('error' => 'PII detected', 'message' => 'Please do not include personal information such as email addresses or phone numbers in your comments.'), 400);
    }

    // Sanitize the comment content
    $comment_content = wp_kses($data['content'], array(
        'a' => array('href' => array(), 'title' => array()),
        'br' => array(),
        'em' => array(),
        'strong' => array(),
    ));

    // Generate a unique ID for the new comment
    $newId = katorymnd_generate_unique_comment_id($data['id'] ?? '', $wpdb, $table_name);

    // Insert the comment into the database
    $result = $wpdb->insert(
        $table_name,
        array(
            'id' => $newId,
            'userName' => sanitize_text_field($data['userName']),
            'content' => $comment_content,
            'parent_id' => sanitize_text_field($data['parentId'] ?? '')
        ),
        array('%s', '%s', '%s', '%s')
    );

    if ($result) {
        // If insert was successful
        return new WP_REST_Response(array('message' => 'Comment saved successfully', 'id' => $newId), 200);
    } else {
        // If insert failed
        return new WP_REST_Response(array('error' => 'Failed to save comment', 'message' => 'Failed to save comment. There might be a problem with the server or the database.'), 500); // Changed status code to 500 to indicate server error
    }
}


function katorymnd_generate_unique_comment_id($commentId, $wpdb, $table_name)
{
    // First, check if the parentId itself already exists in the database
    $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE id = %s", $commentId));

    if ($exists == 0) {
        // If the parentId does not exist, its unique and can be used directly
        return $commentId;
    }

    // If the parentId exists, proceed with determining the base part and the numeric suffix
    if (preg_match('/^(.*?)(\d+)$/', $commentId, $matches)) {
        $basePart = $matches[1]; // The alphanumeric part before the numeric suffix
        $numericSuffix = intval($matches[2]); // The numeric suffix
    } else {
        $basePart = $commentId;
        $numericSuffix = 1; // No numeric suffix found, start from 1
    }

    // Increment the numeric suffix until a unique ID is found
    do {
        $numericSuffix++;
        $newId = $basePart . ($numericSuffix > 0 ? $numericSuffix : '');
        $exists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE id = %s", $newId));
    } while ($exists > 0);

    return $newId;
}

function kr_findNextAvailableCommentId($commentIds)
{
    $initialIds = [];
    $pattern = '/^PC(\d+)/';

    foreach ($commentIds as $id) {
        if (preg_match($pattern, $id, $matches)) {
            $initialIds[] = (int) $matches[1];
        }
    }

    if (count($initialIds) == 0) {
        return 'PC1'; // Return 'PC1' if no initial IDs are found
    }

    $maxId = max($initialIds);
    $nextId = 'PC' . ($maxId + 1);

    return $nextId;
}

// Callback function for processing the submitted comment
function process_submit_comment(WP_REST_Request $request)
{
    global $wpdb; // Access the WordPress database object

    // Retrieve comment and page details from the request
    $comment = $request->get_param('comment');
    $pageId = $request->get_param('pageId');
    $pageSlug = $request->get_param('pageSlug');
    $pageTitle = $request->get_param('pageTitle');
    $loggedinUsername = $request->get_param('userName');


    // Perform necessary security checks, e.g., sanitize inputs
    $comment =  stripslashes(sanitize_textarea_field($comment));
    $pageId = intval($pageId);
    $pageSlug = sanitize_title($pageSlug);
    $pageTitle = sanitize_text_field($pageTitle);
    $loggedinUsername = sanitize_text_field($loggedinUsername);

    // Validate that all required parameters are present and not empty
    if (empty($comment) || empty($pageId) || empty($pageSlug) || empty($pageTitle) || empty($loggedinUsername)) {
        // If any parameter is missing, return an error response
        return new WP_REST_Response([
            'status' => 'error',
            'message' => 'Missing required parameter(s). Please ensure all fields are filled.'
        ], 400); // HTTP status code 400 for Bad Request
    }

    // Perform the spam content check
    if (katorymnd_kr_is_spam_content($comment)) {
        // If content is likely spam, return an error response and do not save to the database
        return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Spam detected. Your comment is pending review.'), 400);
    }

    // Akismet spam check
    $akismet_api_key = get_option('akismet_api_key');
    if (!empty($akismet_api_key)) {
        $akismet_data = array(
            'blog' => get_home_url(),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'comment_type' => 'comment',
            'comment_author' => '',
            'comment_author_email' => '',
            'comment_content' => $comment,
        );

        $response = wp_remote_post("https://{$akismet_api_key}.rest.akismet.com/1.1/comment-check", array(
            'body' => $akismet_data,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        ));

        if (is_wp_error($response)) {
            error_log('Akismet request failed: ' . $response->get_error_message());
        } else {
            $akismet_response = wp_remote_retrieve_body($response);
            if ('true' === $akismet_response) {
                return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Your comment looks like spam.'), 400);
            }
        }
    }

    // Validate comment content length
    if (empty($comment) || strlen(trim($comment)) < 10) {
        return new WP_REST_Response(array('error' => 'Comment is too short', 'message' => 'Comment is too short. Please provide more detail.'), 400);
    }

    // Load disallowed words from bad_words.txt and simple profanity filter
    $bad_words_path = plugin_dir_path(__FILE__) . 'bad_words.txt';
    $disallowed_words = file_exists($bad_words_path) ? file($bad_words_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    foreach ($disallowed_words as $word) {
        if (stripos($comment, $word) !== false) {
            return new WP_REST_Response(array('error' => 'Inappropriate language', 'message' => 'Please avoid using inappropriate language in comments.'), 400);
        }
    }

    // Check for PII in the comment content
    if (katorymnd_kr_is_pii_content($comment)) {
        // If potential PII is found, return an error response
        return new WP_REST_Response(array('error' => 'PII detected', 'message' => 'Please do not include personal information such as email addresses or phone numbers in your comments.'), 400);
    }

    // Sanitize the comment content
    $sanitized_content = wp_kses($comment, array(
        'br' => array(),
        'em' => array(),
        'strong' => array(),
    ));

    $katorymnd_g1sxcbq = $wpdb->prefix . 'katorymnd_kr_comments';

    // Retrieve all comment IDs from the katorymnd_kr_comments table
    $query = $wpdb->prepare("SELECT id FROM $katorymnd_g1sxcbq");
    $commentIds = $wpdb->get_col($query);

    // Get the next available comment ID using the retrieved IDs
    $nextId = kr_findNextAvailableCommentId($commentIds);

    // Check if the nextId already exists in the katorymnd_kr_comments table to prevent duplicate entries
    $idExistsInCommentsTable = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $katorymnd_g1sxcbq WHERE id = %s", $nextId));

    if ($idExistsInCommentsTable > 0) {
        // Return error response if duplicate ID is found in the comments table
        return new WP_REST_Response([
            'message' => 'Duplicate ID detected in comments table. Cannot save duplicate comment data.',
        ], 400); // Use HTTP status code 400 for Bad Request
    }

    // Define the table name dynamically using the WordPress database prefix for other details
    $tableName = $wpdb->prefix . 'katorymnd_kr_intialid_commentid_page';

    // Check if the commentId already exists in the other custom table to prevent duplicate entries
    $existsInOtherTable = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $tableName WHERE comment_id = %s", $nextId));

    if ($existsInOtherTable > 0) {
        // Return error response if duplicate comment_id is found in the other table
        return new WP_REST_Response([
            'message' => 'Duplicate comment ID detected in other table. Cannot save duplicate comment data.',
        ], 400); // Use HTTP status code 400 for Bad Request
    }

    if (katorymnd_kr_is_user_not_active($loggedinUsername)) {
        // Handle the case where the user is not active
        return new WP_REST_Response([
            'error' => 'Banned User',
            'message' => 'You are banned from using this service.',
        ], 403); // Using HTTP status code 403 to indicate that the action is forbidden
    }
    // Proceed with inserting data into both tables since the ID is unique across them

    // Insert into the custom table
    $result = $wpdb->insert(
        $tableName,
        [
            'comment_id' => $nextId,
            'page_id' => $pageId,
            'page_slug' => $pageSlug,
            'page_title' => $pageTitle,
            'username' => $loggedinUsername,
        ],
        [
            '%s', // comment_id
            '%d', // page_id
            '%s', // page_slug
            '%s', // page_title
            '%s', // username
        ]
    );

    // Insert into the katorymnd_kr_comments table
    $resultCommentsTable = $wpdb->insert(
        $katorymnd_g1sxcbq,
        [
            'id' => $nextId,
            'userName' => $loggedinUsername,
            'content' => $sanitized_content,
            'parent_id' => null, // Explicitly setting parent_id to NULL
        ],
        [
            '%s', // id
            '%s', // userName
            '%s', // content
            '%s', // parent_id
        ]
    );

    // Check if the inserts were successful
    if ($result === false || $resultCommentsTable === false) {
        // Handle the error, perhaps return an error response
        return new WP_REST_Response([
            'message' => 'Error saving comment data.',
        ], 500); // Use HTTP status code 500 for Internal Server Error
    }

    // Return success response
    return new WP_REST_Response([
        'message' => 'Received and saved comment data successfully!',
        'nextCommentId' => $nextId, // Include the next available comment ID in the response
        'receivedData' => [
            'comment' => $comment,
            'pageId' => $pageId,
            'pageSlug' => $pageSlug,
            'pageTitle' => $pageTitle,
            'userName' => $loggedinUsername,
        ]
    ], 200); // Use HTTP status code 200 for OK
}

// update the comment details
function katorymnd_kr_update_comment(WP_REST_Request $request)
{
    global $wpdb;
    $data = $request->get_json_params();
    $table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    // Ensure 'commentId' and 'newContent' are provided and not empty
    if (empty($data['commentId']) || empty($data['newContent'])) {
        return new WP_Error('missing_data', 'Missing comment ID or new content.', ['status' => 400]);
    }

    $commentId = sanitize_text_field($data['commentId']);
    $newContent =  stripslashes(sanitize_textarea_field($data['newContent']));

    // Perform the spam content check
    if (katorymnd_kr_is_spam_content($newContent)) {
        // If content is likely spam, return an error response and do not save to the database
        return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Spam detected. Your comment is pending review.'), 400);
    }

    // Akismet spam check
    $akismet_api_key = get_option('akismet_api_key');
    if (!empty($akismet_api_key)) {
        $akismet_data = array(
            'blog' => get_home_url(),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'comment_type' => 'comment',
            'comment_author' => '',
            'comment_author_email' => '',
            'comment_content' => $newContent,
        );

        $response = wp_remote_post("https://{$akismet_api_key}.rest.akismet.com/1.1/comment-check", array(
            'body' => $akismet_data,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        ));

        if (is_wp_error($response)) {
            error_log('Akismet request failed: ' . $response->get_error_message());
        } else {
            $akismet_response = wp_remote_retrieve_body($response);
            if ('true' === $akismet_response) {
                return new WP_REST_Response(array('error' => 'Spam detected', 'message' => 'Your comment looks like spam.'), 400);
            }
        }
    }

    // Validate comment content length
    if (empty($newContent) || strlen(trim($newContent)) < 10) {
        return new WP_REST_Response(array('error' => 'Comment is too short', 'message' => 'Comment is too short. Please provide more detail.'), 400);
    }

    // Load disallowed words from bad_words.txt and simple profanity filter
    $bad_words_path = plugin_dir_path(__FILE__) . 'bad_words.txt';
    $disallowed_words = file_exists($bad_words_path) ? file($bad_words_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    foreach ($disallowed_words as $word) {
        if (stripos($newContent, $word) !== false) {
            return new WP_REST_Response(array('error' => 'Inappropriate language', 'message' => 'Please avoid using inappropriate language in comments.'), 400);
        }
    }


    // Check for PII in the comment content
    if (katorymnd_kr_is_pii_content($newContent)) {
        // If potential PII is found, return an error response
        return new WP_REST_Response(array('error' => 'PII detected', 'message' => 'Please do not include personal information such as email addresses or phone numbers in your comments.'), 400);
    }

    // Sanitize the comment content
    $sanitized_content = wp_kses($newContent, array(
        'br' => array(),
        'em' => array(),
        'strong' => array(),
    ));

    // Check if the comment exists
    $commentExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE id = %s", $commentId));
    if (!$commentExists) {
        return new WP_Error('comment_not_found', 'The comment to update was not found.', ['status' => 404]);
    }

    // Update the comment in the database
    $result = $wpdb->update(
        $table_name,
        ['content' => $sanitized_content],
        ['id' => $commentId]
    );

    if ($result !== false) {
        // If update was successful
        return new WP_REST_Response(['message' => 'Comment updated successfully', 'id' => $commentId], 200);
    } else {
        // If update failed
        return new WP_Error('comment_update_error', 'Failed to update comment.', ['status' => 500]);
    }
}


//delete the comment
function katorymnd_kr_delete_comment(WP_REST_Request $request)
{
    global $wpdb;
    $data = $request->get_json_params();
    $comments_table_name = $wpdb->prefix . 'katorymnd_kr_comments';
    $reactions_table_name = $wpdb->prefix . 'katorymnd_kr_reactions';
    $initial_ids_table = $wpdb->prefix . 'katorymnd_kr_intialid_commentid_page'; // Reference to the initial IDs table
    $report_abuse_table = $wpdb->prefix . 'katorymnd_kr_abuse_reports'; // Reference to the report abuse table

    if (empty($data['idsToDelete']) || !is_array($data['idsToDelete'])) {
        return new WP_Error('invalid_request', 'Invalid or missing IDs to delete.', ['status' => 400]);
    }

    $commentIdsToDelete = array_map('sanitize_text_field', $data['idsToDelete']);

    $deletedComments = [];
    $deletedReactions = [];
    $notFoundComments = [];
    $deletionErrors = []; // Array to store potential deletion errors

    foreach ($commentIdsToDelete as $id) {
        // First, remove the parent_id from any child comments
        $wpdb->update(
            $comments_table_name,
            ['parent_id' => NULL], // Set parent_id to NULL
            ['parent_id' => $id], // Where current comment ID is a parent
            ['%s'], // Value format
            ['%s']  // Where format
        );

        // Then, delete any reactions associated with this comment ID
        $reactionsDeleted = $wpdb->delete($reactions_table_name, ['comment_id' => $id], ['%s']);
        $deletedReactions[$id] = $reactionsDeleted;

        // Attempt to delete the comment itself
        $commentDeleted = $wpdb->delete($comments_table_name, ['id' => $id], ['%s']);
        if ($commentDeleted) {
            $deletedComments[] = $id;
            // Additionally, check and delete from initial IDs table if present
            $initialIdDeleted = $wpdb->delete($initial_ids_table, ['comment_id' => $id], ['%s']);
            if ($initialIdDeleted) {
                $deletedInitialIds[] = $id; // Log the deleted initial ID
            }

            // Additionally, check and delete from reports table if present
            $ReportDeleted = $wpdb->delete($report_abuse_table, ['comment_id' => $id], ['%s']);
            if ($ReportDeleted) {
                $deletedreport[] = $id; // Log the deleted report ID
            }
        } else {
            // Log the error or add it to the deletionErrors array
            $lastError = $wpdb->last_error;
            $deletionErrors[$id] = $lastError;
            error_log("Failed to delete comment with ID $id: $lastError");
        }
    }

    $response = [
        'message' => 'Deletion process completed.',
        'deletedComments' => $deletedComments,
        'notFoundComments' => $notFoundComments,
        'deletedReactionsDetails' => $deletedReactions,
        'deletionErrors' => $deletionErrors, // Include any errors encountered during deletion
    ];

    if (empty($deletedComments) && empty($deletionErrors)) {
        return new WP_Error('no_comments_deleted', 'No comments were deleted.', ['status' => 404, 'data' => $response]);
    }

    return new WP_REST_Response($response, 200);
}


//handle reaction updates
function sendReactionUpdate_kr(WP_REST_Request $request)
{
    global $wpdb;
    $data = $request->get_json_params();

    $commentId = sanitize_text_field($data['commentId']);
    $userName = sanitize_text_field($data['userId']);
    $reactionType = sanitize_text_field($data['newEmoji']);

    // Check if all required fields are provided and not null
    if (empty($commentId) || empty($userName) || empty($reactionType)) {
        return new WP_Error('missing_data', 'Missing required field(s).', ['status' => 400]);
    }

    if (katorymnd_kr_is_user_not_active($userName)) {
        // Handle the case where the user is not active by returning a WP_Error
        return new WP_Error(
            'banned_user', // Error code
            'You are banned from using this service.', // Error message
            array('status' => 400) // Data with HTTP status code
        );
    }


    // Check if the username exists in katorymnd_kr_user_details
    $userDetailsTable = $wpdb->prefix . 'katorymnd_kr_user_details';
    $userExists = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $userDetailsTable WHERE username = %s", $userName));

    if ($userExists == 0) {
        // User does not exist, return an error
        return new WP_Error('user_not_found', 'The specified user does not exist.', ['status' => 404]);
    }

    // Proceed with handling reaction updates
    $reactionsTable = $wpdb->prefix . 'katorymnd_kr_reactions';
    $existingReaction = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $reactionsTable WHERE comment_id = %s AND user_name = %s",
        $commentId,
        $userName
    ));

    if ($existingReaction) {
        // Update existing reaction
        $result = $wpdb->update(
            $reactionsTable,
            ['reaction_type' => $reactionType],
            ['comment_id' => $commentId, 'user_name' => $userName]
        );
    } else {
        // Insert new reaction
        $result = $wpdb->insert(
            $reactionsTable,
            [
                'comment_id' => $commentId,
                'user_name' => $userName,
                'reaction_type' => $reactionType
            ],
            ['%s', '%s', '%s']
        );
    }

    // Check the outcome of the operation
    if ($result !== false) {
        // Success
        return new WP_REST_Response([
            'message' => 'Reaction updated successfully',
            'commentId' => $commentId,
            'userName' => $userName,
            'reactionType' => $reactionType
        ], 200);
    } else {
        // Error in operation
        return new WP_Error('reaction_update_error', 'Failed to update reaction.', ['status' => 500]);
    }
}



function getReactionsForComment($commentId)
{
    global $wpdb;
    $reactions_table_name = $wpdb->prefix . 'katorymnd_kr_reactions';

    $reactionsRaw = $wpdb->get_results(
        $wpdb->prepare("SELECT user_name, reaction_type FROM $reactions_table_name WHERE comment_id = %s", $commentId),
        ARRAY_A
    );

    $reactions = [];
    foreach ($reactionsRaw as $reaction) {
        $reactions[$reaction['reaction_type']][] = $reaction['user_name'];
    }

    return $reactions;
}


function getFormattedComments($commentIds = [], $parentId = null, $level = 0, $limit = null, $offset = 0)
{
    global $wpdb;
    $comments_table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    // Initialize the query and parameters
    $query = "";
    $queryParams = [];

    if (!empty($commentIds) && $level === 0) {
        // Apply the limit and offset only for top-level comments
        $placeholders = implode(',', array_fill(0, count($commentIds), '%s'));
        $query = "SELECT * FROM $comments_table_name WHERE id IN ($placeholders)";
        $queryParams = $commentIds;

        if ($limit !== null) {
            $query .= " LIMIT %d OFFSET %d";
            array_push($queryParams, $limit, $offset);
        }
    } elseif ($parentId !== null) {
        $query = "SELECT * FROM $comments_table_name WHERE parent_id = %s";
        $queryParams[] = $parentId;
    } else {
        // Fetching all top-level comments without applying the page id but pagination limit, (demo usage) - will  load all the comments
        // set demo  updates 
        if (get_option('wpkr_demo_enabled', '1') == '1') {
            $query = "SELECT * FROM $comments_table_name WHERE parent_id IS NULL";
            if ($limit !== null && $level === 0) {
                $query .= " LIMIT %d OFFSET %d";
                array_push($queryParams, $limit, $offset);
            }
        }
    }

    // Execute the query with the prepared parameters
    // Ensure the query is not empty before executing
    $comments = !empty($query) ? $wpdb->get_results($wpdb->prepare($query, $queryParams), ARRAY_A) : [];

    $formattedComments = [];
    foreach ($comments as $comment) {
        $commentId = $comment['id'];
        $formattedComment = $comment;

        // Retrieve and assign reactions
        $formattedComment['reactions'] = getReactionsForComment($commentId);

        // Recursively get replies without applying the limit to nested comments
        $replies = getFormattedComments([], $commentId, $level + 1);
        if (!empty($replies)) {
            $replyKey = getReplyKey($level);
            $formattedComment[$replyKey] = $replies;
        }

        // Assign the formatted comment to the array
        $formattedComments[$commentId] = $formattedComment;
    }

    return $formattedComments;
}

function getReplyKey($level)
{
    switch ($level) {
        case 0:
            return 'replies';
        case 1:
            return 'sub_reply';
        case 2:
            return 'sub_sub_reply';
        default:
            return 'replies'; // Fallback for deeper levels
    }
}

function kr_fetch_comments_with_empty_parent_id()
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    // Direct SQL query to select comments with empty or null parent_id
    $sql_query = "SELECT * FROM `{$table_name}` WHERE parent_id IS NULL OR parent_id = ''";

    // Fetching results
    $comments = $wpdb->get_results($sql_query, OBJECT);

    // Initialize an array to hold the comment IDs
    $comment_ids = [];

    // Output to debug log and collect comment IDs
    if (!empty($comments)) {
        foreach ($comments as $comment) {
            // Add the comment ID to the array
            $comment_ids[] = $comment->id;
        }
    } else {
        //  error_log('No comments found with empty or null parent_id.');
    }

    // Prepare the result as an associative array
    $result = [
        'total_comments' => count($comment_ids),
        'comment_ids' => $comment_ids
    ];
    return $result;
}

function Kr_get_intialComment_ids_by_page_id($page_id)
{
    global $wpdb;

    $comment_ids = [];
    $total_comments = 0;

    if ($page_id) {
        $page_id = intval($page_id);
        $initial_ids_table = $wpdb->prefix . 'katorymnd_kr_intialid_commentid_page';
        $sql = $wpdb->prepare("SELECT comment_id FROM $initial_ids_table WHERE page_id = %d", $page_id);
        $results = $wpdb->get_results($sql, ARRAY_A);

        if (!empty($results)) {
            foreach ($results as $item) {
                $comment_ids[] = $item['comment_id'];
            }
        }
    }

    if (empty($comment_ids)) {
        // If no comment IDs were found for the page, or no page_id was provided,
        // fetch comments with empty or null parent_id.
        // set demo  updates 
        if (get_option('wpkr_demo_enabled', '1') == '1') {
            $result = kr_fetch_comments_with_empty_parent_id();
            $comment_ids = $result['comment_ids']; // Use the comment IDs from the result
        }
    }

    $total_comments = count($comment_ids);

    // Check for any errors during the query
    if (!empty($wpdb->last_error)) {
        error_log('Database error: ' . $wpdb->last_error);
    }

    return [
        'comment_ids' => $comment_ids,
        'count' => $total_comments
    ];
}

// Fetch the feedback data based on page details
function katorymnd_kr_fetch_data(WP_REST_Request $request)
{
    // Retrieve page details and pagination parameters from the request
    $page_id = $request->get_param('page_id');
    $page_slug = $request->get_param('page_slug');
    $page = $request->get_param('page') ? intval($request->get_param('page')) : 0;
    $limit = $request->get_param('limit') ? intval($request->get_param('limit')) : intval(get_option('wpkr_num_comments', 10));

    // Ensure the page ID or slug is not empty
    if (!empty($page_id) || !empty($page_slug)) {
        // Retrieve initial comment IDs and their count by page ID
        $result = Kr_get_intialComment_ids_by_page_id($page_id);
        $comment_ids = $result['comment_ids'];
        $total_comments = $result['count'];

        // Calculate the offset for the SQL query
        $offset = $page * $limit;

        // Fetch formatted comments based on the page ID or slug with pagination
        $comments = getFormattedComments($comment_ids, null, 0, $limit, $offset);

        // Prepare the response data
        $response_data = [
            'comments' => $comments,
            'total_comments' => $total_comments, // Include the total count of initial comments
            'comment_ids' => $comment_ids, // This could be omitted if unnecessary
            'page' => $page, // Current page of the pagination
            'limit' => $limit // Number of comments per page
        ];

        // Check if any data is retrieved
        if (!empty($comments) || $total_comments > 0) {
            // Data found, return it
            return new WP_REST_Response($response_data, 200);
        } else {
            // No data found, return a notice message
            return new WP_REST_Response(['message' => 'No comments found for the specified page'], 200);
        }
    }
}


function enqueue_bootstrap()
{
    // Ensure we don't enqueue our scripts if they're already included
    if (!is_bootstrap_enqueued_or_registered()) {
        // Enqueue Bootstrap 5.3.0-alpha1 CSS
        wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css', [], null);

        // Enqueue Font Awesome
        wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css', [], null);

        // Enqueue Popper.js
        wp_enqueue_script('popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js', [], null, true);

        // Enqueue Bootstrap 5.3.0-alpha1 JS
        wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js', ['jquery', 'popper-js'], null, true);
    }
}

// Hook the function into `wp_enqueue_scripts`
add_action('wp_enqueue_scripts', 'enqueue_bootstrap');

function katorymnd_kr_enqueue_toastr()
{
    $toastr_css = 'toastr-css';
    $toastr_js = 'toastr-js';

    // Check if Toastr CSS is already enqueued
    if (!wp_style_is($toastr_css, 'enqueued')) {
        // Enqueue Toastr CSS
        wp_enqueue_style($toastr_css, 'https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.css');
    }

    // Check if Toastr JavaScript is already enqueued
    if (!wp_script_is($toastr_js, 'enqueued')) {
        // Enqueue Toastr JavaScript
        wp_enqueue_script($toastr_js, 'https://cdn.jsdelivr.net/npm/toastr@2.1.4/build/toastr.min.js', array('jquery'), null, true);
    }
}
add_action('wp_enqueue_scripts', 'katorymnd_kr_enqueue_toastr');



/**
 * Check whether Bootstrap or Font Awesome is enqueued or registered by another plugin or theme.
 *
 * @return bool
 */
function is_bootstrap_enqueued_or_registered()
{
    // List of common handles used to register/bootstrap
    $common_bootstrap_handles = [
        'bootstrap-css',
        'bootstrap-js',
        'bootstrap',
        'bs-css',
        'bs-js',
        'bs4-css',
        'bs4-js',
        'font-awesome',
        //... other handles
    ];

    foreach ($common_bootstrap_handles as $handle) {
        // Check if style is enqueued or registered
        if (wp_style_is($handle, 'enqueued') || wp_style_is($handle, 'registered')) {
            return true;
        }

        // Check if script is enqueued or registered
        if (wp_script_is($handle, 'enqueued') || wp_script_is($handle, 'registered')) {
            return true;
        }
    }

    return false;
}

//katorymnd reaction process deactivation logic -clean up
function katorymnd_kr_cleanup_data_ajax_handler()
{
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    global $wpdb;

    // Base table name
    $base_table_name = $wpdb->prefix . 'katorymnd_';

    // Define table names
    $feedback_table_name = $base_table_name . 'feedback';
    $comments_table_name = $base_table_name . 'kr_comments';
    $reactions_table_name = $base_table_name . 'kr_reactions';
    $user_details_table_name = $base_table_name . 'kr_user_details';
    $user_session_table_name = $base_table_name . 'kr_custom_user_sessions';
    $reports_table_name = $base_table_name . 'kr_abuse_reports';
    $intialComment_table_name = $base_table_name . 'kr_intialid_commentid_page';
    $commentRating_table_name = $base_table_name . 'kr_user_ratings';

    // Initialize variable to capture any errors
    $error_messages = '';

    // Function to delete all posts of specific post types
    $delete_posts_by_type = function ($post_type) use (&$error_messages, &$wpdb) {
        $posts = get_posts([
            'post_type' => $post_type,
            'post_status' => 'any',
            'numberposts' => -1,
            'fields' => 'ids',
        ]);

        foreach ($posts as $post_id) {
            if (!wp_delete_post($post_id, true)) { // true to bypass Trash
                $error_messages .= "Failed to delete post $post_id of type $post_type. ";
            }
        }
    };

    // Delete all posts of custom post types
    $delete_posts_by_type('kr_survey');
    $delete_posts_by_type('kr_survey_response');

    // Function to drop table and append any errors
    $drop_table = function ($table_name) use ($wpdb, &$error_messages) {
        $wpdb->query("DROP TABLE IF EXISTS $table_name");
        if ($wpdb->last_error) {
            $error_messages .= "Error dropping table $table_name: " . $wpdb->last_error . ' ';
        }
    };

    // Drop Tables considering potential foreign key constraints
    $drop_table($user_details_table_name);
    $drop_table($reactions_table_name);
    $drop_table($comments_table_name);
    $drop_table($feedback_table_name);
    $drop_table($user_session_table_name);
    $drop_table($reports_table_name);
    $drop_table($intialComment_table_name);
    $drop_table($commentRating_table_name);

    // Delete plugin options
    $options_to_delete = [
        'katorymnd_wlse', 'katorymnd_emoji_theme', 'katorymnd_emoji_selection',
        'katorymnd_header_bg_color', 'akismet_api_key', 'katorymnd_oxnfue2',
        'katorymnd_filter_settings', 'katorymnd_kr_custom_table_name',
        'katorymnd_kr_user_id_column_name', 'katorymnd_kr_username_column_name',
        'katorymnd_kr_email_column_name', 'katorymnd_kr_full_names_column_name',
        'katorymnd_kr_avatar_column_name', 'kr_deactivate_demo'
    ];

    foreach ($options_to_delete as $option) {
        delete_option($option);
    }

    // Deactivate the plugin
    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugin = plugin_basename(__FILE__);
    deactivate_plugins($plugin);

    // Prepare custom message
    $custom_message = __('Data cleaned up and plugin deactivated successfully.', 'katorymnd-reaction-process');
    if (!empty($error_messages)) {
        $custom_message .= ' ' . __('However, there were errors: ', 'katorymnd-reaction-process') . $error_messages;
    }

    // Manually prepare response data
    $response_data = [
        'success' => true,
        'custom_message' => $custom_message,
        'redirect' => admin_url('plugins.php?deactivate=true'),
    ];

    wp_send_json($response_data);
}


add_action('wp_ajax_katorymnd_cleanup_data', 'katorymnd_kr_cleanup_data_ajax_handler');

function katorymnd_admin_scripts()
{
    // Enqueue existing admin styles and scripts
    wp_enqueue_style('katorymnd-admin-style', plugin_dir_url(__FILE__) . 'css/admin-style.css');
    wp_enqueue_script('katorymnd-admin-script', plugin_dir_url(__FILE__) . 'js/admin-script.js', ['jquery'], '1.2.0', true);
    wp_enqueue_script('katorymnd-kr-admin-ajax-js', plugin_dir_url(__FILE__) . 'js/katorymnd-kr-admin.js', ['jquery'], '1.2.0', true);

    // Enqueue Spectrum Colorpicker plugin
    wp_enqueue_style('spectrum-css', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css');
    wp_enqueue_script('spectrum-js', 'https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js', ['jquery'], '', true);
    // Enqueue SortableJS
    wp_enqueue_script('sortablejs', 'https://cdn.jsdelivr.net/npm/sortablejs@1.14.0/Sortable.min.js', [], '', true);

    // Localize the script with new data for AJAX
    wp_localize_script('katorymnd-kr-admin-ajax-js', 'katorymnd_ajax_obj', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('katorymnd_kr_cleanup_nonce'),
    ]);

    // Common handles for Bootstrap and Font Awesome
    $common_bootstrap_handles = [
        'bootstrap-css',
        'bootstrap-js',
        'bootstrap',
        'bs-css',
        'bs-js',
        'bs4-css',
        'bs4-js',
        'font-awesome',
        // ... other handles
    ];

    // Check if Bootstrap and Font Awesome are already enqueued
    $bootstrap_css_enqueued = false;
    $bootstrap_js_enqueued = false;
    $font_awesome_enqueued = false;

    foreach ($common_bootstrap_handles as $handle) {
        if (wp_style_is($handle, 'enqueued')) {
            if (strpos($handle, 'bootstrap') !== false) {
                $bootstrap_css_enqueued = true;
            } else if (strpos($handle, 'font-awesome') !== false) {
                $font_awesome_enqueued = true;
            }
        }
        if (wp_script_is($handle, 'enqueued')) {
            $bootstrap_js_enqueued = true;
        }
    }

    // Enqueue Bootstrap and Font Awesome if not already enqueued
    if (!$bootstrap_css_enqueued) {
        wp_enqueue_style('katorymnd-bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css');
    }
    if (!$bootstrap_js_enqueued) {
        wp_enqueue_script('katorymnd-popper-js', 'https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js', [], '', true);
        wp_enqueue_script('katorymnd-bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js', ['jquery', 'katorymnd-popper-js'], '', true);
    }
    if (!$font_awesome_enqueued) {
        wp_enqueue_style('katorymnd-font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');
    }
}

add_action('admin_enqueue_scripts', 'katorymnd_admin_scripts');


// call Admin Page Content
include plugin_dir_path(__FILE__) . 'katorymnd_feebackAdmin.php';

// save settings choice  - admin 
add_action('wp_ajax_katorymnd_save_settings', 'katorymnd_save_settings_ajax_handler');

function katorymnd_save_settings_ajax_handler()
{
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    // Initialize a variable to track if any setting update fails
    $anySettingFailed = false;
    $settingsUpdated = false;

    // Check if emoji theme setting is set and save it
    if (isset($_POST['katorymnd_options']['emoji_theme'])) {
        $emoji_theme = sanitize_text_field($_POST['katorymnd_options']['emoji_theme']);
        update_option('katorymnd_emoji_theme', $emoji_theme);
        $settingsUpdated = true;
    }

    // Check if emoji selection setting is set and save it
    if (isset($_POST['katorymnd_options']['emoji_selection'])) {
        $emoji_selection = array_map('sanitize_text_field', $_POST['katorymnd_options']['emoji_selection']);
        update_option('katorymnd_emoji_selection', $emoji_selection);
        $settingsUpdated = true;
    }

    // Check if header color setting is set and save it
    if (isset($_POST['katorymnd_options']['header_color'])) {
        $header_color = sanitize_text_field($_POST['katorymnd_options']['header_color']);
        update_option('katorymnd_header_bg_color', $header_color);
        $settingsUpdated = true;
    }

    // Check if rating setting is set and save it
    if (isset($_POST['katorymnd_options']['rating_type'])) {
        $kr_ratingType = sanitize_text_field($_POST['katorymnd_options']['rating_type']);
        update_option('katorymnd_rating_type', $kr_ratingType);
        $settingsUpdated = true;
    }

    // Process filter settings
    // Retrieve existing settings or initialize with defaults if not previously set
    $current_settings = get_option('katorymnd_filter_settings', [
        'filter_url' => '1', // Assuming '1' as default checked state
        'filter_email' => '1',
        'filter_anchor' => '1',
        'filter_phone' => '1',
        'filter_spam_keywords' => '1',
        'filter_spam_patterns' => '1',
    ]);

    // Check if filter settings were submitted
    if (isset($_POST['filter_settings']) && is_array($_POST['filter_settings'])) {
        $filter_settings_submitted = $_POST['filter_settings'];
        foreach ($filter_settings_submitted as $key => $value) {
            // Ensure the submitted value is either '1' or '0'
            // Update only the settings that were submitted
            $current_settings[$key] = $value === '1' ? '1' : '0';
            $settingsUpdated = true;
        }
    }

    // Update the combined settings
    update_option('katorymnd_filter_settings', $current_settings);



    function all_columns_exist($table_name, $column_names)
    {
        global $wpdb;

        // Initialize an array to hold non-existent columns
        $non_existent_columns = [];

        // Directly use the table name after ensuring it's valid and exists       
        $safe_table_name = $wpdb->prefix . $table_name;

        // Fetch the column names using a verified table name
        // Note: The table existence should be verified before this step to avoid SQL injection
        $query = "DESCRIBE `{$safe_table_name}`"; // Direct insertion of the table name
        $columns_detail = $wpdb->get_results($query, ARRAY_A);

        // Extract just the column names from the detailed schema
        $existing_columns = array_map(function ($column) {
            return strtolower($column['Field']); // Convert to lowercase for case-insensitive comparison
        }, $columns_detail);

        // Check if each specified column exists in the table
        foreach ($column_names as $column) {
            if (!in_array(strtolower($column), $existing_columns)) {
                $non_existent_columns[] = $column; // Add to non-existent columns list
            }
        }

        if (!empty($non_existent_columns)) {
            return $non_existent_columns; // Return the list of non-existent columns
        }

        return true; // Return true if all columns exist
    }



    // Processing the custom table name and column mappings
    if (
        isset($_POST['katorymnd_options']['custom_table_name']) &&
        !empty(trim($_POST['katorymnd_options']['custom_table_name'])) &&
        isset(
            $_POST['katorymnd_options']['user_id_column_name'],
            $_POST['katorymnd_options']['username_column_name'],
            $_POST['katorymnd_options']['email_column_name'],
            $_POST['katorymnd_options']['full_names_column_name'],
            $_POST['katorymnd_options']['avatar_column_name']
        ) &&
        !empty(trim($_POST['katorymnd_options']['user_id_column_name'])) &&
        !empty(trim($_POST['katorymnd_options']['username_column_name'])) &&
        !empty(trim($_POST['katorymnd_options']['email_column_name'])) &&
        !empty(trim($_POST['katorymnd_options']['full_names_column_name'])) &&
        !empty(trim($_POST['katorymnd_options']['avatar_column_name']))
    ) {
        $custom_table_name = sanitize_text_field($_POST['katorymnd_options']['custom_table_name']);
        $settings = [
            'custom_table_name' => $custom_table_name,
            'user_id_column_name' => sanitize_text_field($_POST['katorymnd_options']['user_id_column_name']),
            'username_column_name' => sanitize_text_field($_POST['katorymnd_options']['username_column_name']),
            'email_column_name' => sanitize_text_field($_POST['katorymnd_options']['email_column_name']),
            'full_names_column_name' => sanitize_text_field($_POST['katorymnd_options']['full_names_column_name']),
            'avatar_column_name' => sanitize_text_field($_POST['katorymnd_options']['avatar_column_name'])
        ];

        $column_names = array_values($settings);
        array_shift($column_names); // Remove the custom_table_name from the column names list

        // Verify if the table exists
        if (check_if_table_exists_kr($custom_table_name)) {
            // Check if all specified columns exist in the table
            $columns_check_result = all_columns_exist($custom_table_name, $column_names);
            if ($columns_check_result === true) {
                // All columns exist, save the settings
                foreach ($settings as $option_name => $value) {
                    update_option('katorymnd_kr_' . $option_name, $value);
                }
                wp_send_json_success([
                    'message' => __('All settings have been validated and saved. Column names verified.', 'katorymnd-reaction-process'),
                    'settings' => $settings
                ]);
            } else {
                // Not all columns exist, include specific non-existent columns in the error response
                wp_send_json_error([
                    'message' => __('Invalid custom table name or the following column names do not exist in the specified table: ', 'katorymnd-reaction-process') . implode(', ', $columns_check_result),
                    'settings' => $settings,
                    'non_existent_columns' => $columns_check_result
                ]);
            }
        } else {
            // Table does not exist
            wp_send_json_error([
                'message' => __('The specified custom table name does not exist. Please verify your settings.', 'katorymnd-reaction-process'),
                'settings' => $settings
            ]);
        }
    }


    // Process the Akismet API key if provided
    if (!empty($_POST['akismet_api_key'])) {
        $akismet_api_key = sanitize_text_field($_POST['akismet_api_key']);
        $is_valid = katorymnd_verify_akismet_api_key($akismet_api_key);

        if ($is_valid) {
            update_option('akismet_api_key', $akismet_api_key);
            wp_send_json_success(['message' => __('Akismet API key validated and saved. All settings have been updated.', 'katorymnd-reaction-process')]);
            $settingsUpdated = true;
        } else {
            wp_send_json_error(['message' => __('Invalid Akismet API key. Other settings have been saved.', 'katorymnd-reaction-process')]);
            $anySettingFailed = true;
        }
    }

    if (
        isset($_POST['katorymnd_options']['demo_enabled']) &&
        isset($_POST['katorymnd_options']['default_num_comments']) &&
        isset($_POST['katorymnd_options']['num_comments'])
    ) {
        // Sanitize and update 'demo_enabled' option
        $demo_enabled = sanitize_text_field($_POST['katorymnd_options']['demo_enabled']);
        update_option('wpkr_demo_enabled', $demo_enabled === '1' ? '1' : '0');

        // Sanitize and update 'default_num_comments' option
        $default_num_comments = intval(sanitize_text_field($_POST['katorymnd_options']['default_num_comments']));
        update_option('wpkr_default_num_comments', $default_num_comments);

        // Sanitize and update 'num_comments' option
        $num_comments = intval(sanitize_text_field($_POST['katorymnd_options']['num_comments']));
        update_option('wpkr_num_comments', $num_comments);

        $settingsUpdated = true;
    }


    // Determine the final response
    if (!$anySettingFailed && $settingsUpdated) {
        wp_send_json_success([
            'message' => __('All settings saved.', 'katorymnd-reaction-process')

        ]);
    } elseif (!$settingsUpdated && !$anySettingFailed) {
        wp_send_json_success(['message' => __('No changes made to settings.', 'katorymnd-reaction-process')]);
    }
}

function katorymnd_kr_fetch_report_details_callback()
{
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;

    if ($report_id <= 0) {
        wp_send_json_error(['message' => 'Invalid report ID.']);
        return;
    }

    global $wpdb;
    $reports_table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';
    $comments_table_name = $wpdb->prefix . 'katorymnd_kr_comments';
    $user_details_table_name = $wpdb->prefix . 'katorymnd_kr_user_details';

    // Refactored query to use JOIN to fetch report details, comment content, and user status in a single query
    $query = $wpdb->prepare("
        SELECT r.details, r.user_name, r.reason, r.status, r.comment_id, c.content AS comment_content, u.user_status
        FROM $reports_table_name r
        LEFT JOIN $comments_table_name c ON r.comment_id = c.id
        LEFT JOIN $user_details_table_name u ON r.user_name = u.username
        WHERE r.id = %d
    ", $report_id);

    $result = $wpdb->get_row($query);

    if (null === $result) {
        wp_send_json_error(['message' => 'Report, comment, or user details not found.']);
        return;
    }

    // Prepare the data to send back, including the comment content and user status
    $data = [
        'details' => $result->details,
        'reported_by' => $result->user_name,
        'reason' => $result->reason,
        'status' => $result->status,
        'comment_id' => $result->comment_id,
        'comment_content' => $result->comment_content,
        'user_status' => $result->user_status,
    ];

    wp_send_json_success($data);
}
// Hook the above function to the wp_ajax_ action
add_action('wp_ajax_katorymnd_fetch_report_details', 'katorymnd_kr_fetch_report_details_callback');

function katorymnd_kr_block_user_callback()
{
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';

    if (empty($user_id)) {
        wp_send_json_error(['message' => 'User ID is invalid.']);
        return;
    }

    global $wpdb;
    $user_details_table_name = $wpdb->prefix . 'katorymnd_kr_user_details';

    // Manually constructing the SQL update query
    $query = $wpdb->prepare(
        "UPDATE $user_details_table_name SET user_status = %s WHERE username = %s",
        'blocked', // Desired user_status
        $user_id // Where clause condition
    );

    $result = $wpdb->query($query);

    if ($result !== false) {
        wp_send_json_success(['message' => 'User successfully blocked.']);
    } else {
        $error_message = $wpdb->last_error ? $wpdb->last_error : 'Failed to execute the block user operation.';
        wp_send_json_error(['message' => $error_message]);
    }
}



function katorymnd_kr_unblock_user_callback()
{
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    $user_id = isset($_POST['user_id']) ? sanitize_text_field($_POST['user_id']) : '';

    if (empty($user_id)) {
        wp_send_json_error(['message' => 'User ID is invalid.']);
        return;
    }

    global $wpdb;
    $user_details_table_name = $wpdb->prefix . 'katorymnd_kr_user_details';

    // Manually constructing the SQL update query
    $query = $wpdb->prepare(
        "UPDATE $user_details_table_name SET user_status = %s WHERE username = %s",
        'active', // Setting user_status to 'active'
        $user_id
    );

    $result = $wpdb->query($query);

    if ($result !== false) {
        wp_send_json_success(['message' => 'User successfully unblocked.']);
    } else {
        $error_message = $wpdb->last_error ? $wpdb->last_error : 'Failed to execute the unblock user operation.';
        wp_send_json_error(['message' => 'Failed to unblock user.', 'error_detail' => $error_message, 'last_query' => $wpdb->last_query]);
    }
}
add_action('wp_ajax_block_user', 'katorymnd_kr_block_user_callback');
add_action('wp_ajax_unblock_user', 'katorymnd_kr_unblock_user_callback');

/**
 * Handles the AJAX request to update the status of a report and optionally update the content of a reported comment.
 */
function katorymnd_kr_update_report_status_callback()
{
    // Verify the AJAX nonce for security
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    global $wpdb;
    // Sanitize and validate input
    $report_id = isset($_POST['report_id']) ? intval($_POST['report_id']) : 0;
    $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';
    $comment_id = isset($_POST['reportedCommentID']) ? sanitize_text_field($_POST['reportedCommentID']) : '';
    $new_content = isset($_POST['content']) ? sanitize_textarea_field($_POST['content']) : '';
    $new_content = stripslashes($new_content);

    if ($report_id <= 0) {
        wp_send_json_error(['message' => 'Invalid report ID provided.']);
        return;
    }

    // Ensure $report_id is numeric
    if (!is_numeric($report_id)) {
        wp_send_json_error(['message' => 'Report ID must be a number.']);
        return;
    }

    // Convert $report_id to an integer
    $report_id = intval($report_id);

    // Validate inputs: none should be empty and report_id should not be 0
    if ($report_id <= 0 || empty($status) || empty($comment_id) || empty($new_content)) {
        wp_send_json_error(['message' => 'Invalid or missing data. All fields are required and report ID must be a positive number.']);
        return;
    }

    // Perform the spam content check
    if (katorymnd_kr_is_spam_content($new_content)) {
        // If content is likely spam, return an error response and do not save to the database
        wp_send_json_error(array('message' => 'Spam detected. Your comment content.'));
        return; // Make sure to return after sending the error to stop execution
    }

    // Akismet spam check
    $akismet_api_key = get_option('akismet_api_key');
    if (!empty($akismet_api_key)) {
        $akismet_data = array(
            'blog' => get_home_url(),
            'user_ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'],
            'referrer' => $_SERVER['HTTP_REFERER'],
            'comment_type' => 'comment',
            'comment_author' => '',
            'comment_author_email' => '',
            'comment_content' => $new_content,
        );

        $response = wp_remote_post("https://{$akismet_api_key}.rest.akismet.com/1.1/comment-check", array(
            'body' => $akismet_data,
            'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
        ));

        if (is_wp_error($response)) {
            error_log('Akismet request failed: ' . $response->get_error_message());
        } else {
            $akismet_response = wp_remote_retrieve_body($response);
            if ('true' === $akismet_response) {
                wp_send_json_error(array('message' => 'Your comment content looks like spam.'));
                return;
            }
        }
    }

    // Validate comment content length
    if (empty($new_content) || strlen(trim($new_content)) < 10) {
        wp_send_json_error(array('message' => 'Comment content is too short. Please provide more detail.'));
        return;
    }

    // Load disallowed words from bad_words.txt and simple profanity filter
    $bad_words_path = plugin_dir_path(__FILE__) . 'bad_words.txt';
    $disallowed_words = file_exists($bad_words_path) ? file($bad_words_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];
    foreach ($disallowed_words as $word) {
        if (stripos($new_content, $word) !== false) {
            wp_send_json_error(array('message' => 'Please avoid using inappropriate language in comment content.'));
            return;
        }
    }

    // Check for PII in the comment content
    if (katorymnd_kr_is_pii_content($new_content)) {
        // If potential PII is found, return an error response
        wp_send_json_error(array('message' => 'Please do not include personal information such as email addresses or phone numbers in the comment content.'));
        return;
    }


    $abuse_reports_table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';
    $comments_table_name = $wpdb->prefix . 'katorymnd_kr_comments';

    // Attempt to fetch the current comment to verify existence
    $current_comment = $wpdb->get_row($wpdb->prepare("SELECT content FROM $comments_table_name WHERE id = %s", $comment_id));
    if (null === $current_comment) {
        wp_send_json_error(['message' => 'Comment not found with the provided ID.']);
        return;
    }

    // Update the comment's content if it has been changed
    if ($new_content !== $current_comment->content) {
        $update_comment_result = $wpdb->update(
            $comments_table_name,
            ['content' => $new_content],
            ['id' => $comment_id]
        );

        // Check for errors in updating the comment
        if ($update_comment_result === false) {
            wp_send_json_error(['message' => 'Failed to update the content of the comment.']);
            return;
        }
    }

    // Fetch the current report status to check if an update is necessary
    $current_report = $wpdb->get_row($wpdb->prepare("SELECT status FROM $abuse_reports_table_name WHERE id = %d", $report_id));
    if (null === $current_report) {
        wp_send_json_error(['message' => 'Report not found with the provided ID.']);
        return;
    }

    // Update the report status if it differs from the current status
    // Determine the resolution text based on the new status
    switch ($status) {
        case 'open':
            $resolution = "Report opened for review.";
            break;
        case 'reviewing':
            $resolution = "Report currently under review.";
            break;
        case 'closed':
            $resolution = "Report has been closed after review.";
            break;
        default:
            $resolution = "Report status updated."; // Default case, just in case
    }

    $current_user = wp_get_current_user();
    $handled_by = $current_user->user_login; // Get the current admin's username
    $resolution_date = current_time('mysql'); // Get the current date and time in MySQL format


    if ($status !== $current_report->status) {
        $update_report_result = $wpdb->update(
            $abuse_reports_table_name,
            [
                'status' => $status,
                'handled_by' => $handled_by,
                'resolution' => $resolution,
                'resolution_date' => $resolution_date
            ],
            ['id' => $report_id]
        );
        if ($update_report_result !== false) {
            wp_send_json_success(['message' => 'Report and comment updated successfully.']);
        } else {
            wp_send_json_error(['message' => 'Failed to update the status of the report.']);
        }
    } else {
        wp_send_json_success(['message' => 'No update needed for the report status. Comment update processed.']);
    }
}

// Register the AJAX action for authenticated users
add_action('wp_ajax_katorymnd_kr_update_report_status', 'katorymnd_kr_update_report_status_callback');


/** Display notifications to the  admin user */
function kr_display_multiple_notifications()
{
    global $kr_admin_notifications;

    if (is_admin()) {
        // Check if there are any stored notifications and display them
        if (!empty($kr_admin_notifications)) {
            foreach ($kr_admin_notifications as $notification) {
                kr_render_notification($notification);
            }
        }
        // You can still manually add other notifications here
        //kr_render_notification('First custom notification message.');
        // kr_render_notification('Second custom notification message.');
        // kr_render_notification('Third custom notification message.');
    }
}
add_action('admin_notices', 'kr_display_multiple_notifications', 10);


function kr_render_notification($message)
{
    static $notification_count = 0; // Static variable to keep track of the count
    $notification_count++; // Increment the count each time the function is called

    // Assign a unique class based on the notification count
    $class = "kr_notification kr_notification_{$notification_count}";

    echo "<div class='{$class}'>{$message}</div>";
}

/** check if there is a `open` on `reviewing` reports
 *   to show the  notification  logically
 */

function kr_check_for_open_or_reviewing_reports()
{
    if (!current_user_can('manage_kr_abuse_reports') && !current_user_can('administrator')) {
        // Exit if the user does not have the capability to manage reports or isn't an administrator
        return;
    }

    global $wpdb, $kr_admin_notifications;
    $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

    $query = "SELECT COUNT(*) FROM {$table_name} WHERE status IN ('open', 'reviewing')";
    $num_reports = $wpdb->get_var($query);

    if ($num_reports > 0) {
        // Determine the appropriate link based on the user role
        $page_slug = current_user_can('administrator') ? 'katorymnd-reaction-settings' : 'wpkr-report-abuse';
        $settings_link = '<a href="' . admin_url("admin.php?page={$page_slug}") . '">' . __('Moderate Reports', 'katorymnd-reaction-process') . '</a>';

        // Prepare the message with the link included
        $message = sprintf('Attention needed: There are currently %d abuse reports awaiting moderation that are either open or under review. Please click on %s to review and moderate these reports as soon as possible.', $num_reports, $settings_link);

        // Store the message in a global variable
        $kr_admin_notifications[] = $message;
    }
}

add_action('admin_init', 'kr_check_for_open_or_reviewing_reports');

// Register the AJAX action for logged-in users (admins) only
add_action('wp_ajax_kr_get_abuse_reports', 'kr_ajax_get_abuse_reports');

function kr_ajax_get_abuse_reports()
{
    // Verify the AJAX nonce for security
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    if (!current_user_can('manage_kr_abuse_reports') && !current_user_can('administrator')) {
        wp_send_json_error(array('message' => 'You do not have sufficient permissions to access this data.'));
        return;
    }

    // Get the current page and filter from the AJAX request
    $page_number = isset($_POST['page']) ? intval($_POST['page']) : 1;
    // Retrieve the saved selected status from the transient
    $selected_status = get_transient('kr_selected_status_transient');
    // Check if the selected status is 'all' and set it to an empty string for the query
    if ($selected_status === 'all') {
        $selected_status = '';
    }

    $reports_per_page = 10; // Adjust as needed

    // Modify the query based on the selected status
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';
    $where_clause = $selected_status ? $wpdb->prepare(" WHERE status = %s", $selected_status) : " WHERE status IN ('open', 'reviewing', 'closed')";

    $query = "SELECT COUNT(*) FROM {$table_name}{$where_clause}";
    $total_reports = $wpdb->get_var($query);
    $total_pages = ceil($total_reports / $reports_per_page);

    // Adjust the data fetching based on the page number and selected status
    $reports = katorymnds_kr_get_abuse_reports($page_number, $reports_per_page, $selected_status);


    // Initialize content with table structure
    $content = '<table class="wp-list-table widefat fixed striped">';
    $content .= '<thead>
                <tr>
                    <th>Report ID</th>
                    <th>Comment ID</th>
                    <th>Reason</th>
                    <th>Details</th>
                    <th>User Name</th>
                    <th>Report Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
             </thead>
             <tbody>';

    // Populate rows with reports data
    foreach ($reports as $report) {
        $content .= sprintf(
            '<tr>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td>%s</td>
            <td id="katorymnd_0lge2yc" class="actions-cell"><a href="#" id="katorymnd_6mns7wd" data-report-id="%s" class="button action btn btn-primary qhol">Review</a></td>
         </tr>',
            esc_html($report->id),
            esc_html($report->comment_id),
            esc_html($report->reason),
            esc_html(wp_trim_words($report->details, 10, '...')),
            esc_html($report->user_name),
            esc_html($report->report_date),
            esc_html($report->status),
            esc_attr($report->id)
        );
    }

    // Check for empty reports
    if (empty($reports)) {
        $content .= '<tr><td colspan="8">No reports found.</td></tr>';
    }

    $content .= '</tbody></table>';

    // Build pagination
    // Define how many page numbers to show before and after the current page
    $range = 5; // Adjust as needed

    // Build pagination with styling
    $pagination = '<nav class="kr-pagination" style="text-align:center; margin-top: 20px;"><ul class="pagination">';

    // Previous Page Link
    if ($page_number > 1) {
        $pagination .= sprintf('<li class="page-item"><a class="page-link" href="#" data-page="%d">&laquo; Previous</a></li>', $page_number - 1);
    }

    // Page Numbers & Ellipses Logic
    if ($total_pages > 1) {
        if ($total_pages <= (1 + ($range * 2))) {
            for ($i = 1; $i <= $total_pages; $i++) {
                $activeClass = $i === $page_number ? 'class="active page-item"' : 'class="page-item"';
                $pagination .= sprintf('<li %s><a class="page-link" href="#" data-page="%d">%d</a></li>', $activeClass, $i, $i);
            }
        } else {
            if ($page_number < 1 + ($range * 2)) {
                for ($i = 1; $i < 4 + ($range * 2); $i++) {
                    $activeClass = $i === $page_number ? 'class="active page-item"' : 'class="page-item"';
                    $pagination .= sprintf('<li %s><a class="page-link" href="#" data-page="%d">%d</a></li>', $activeClass, $i, $i);
                }
                $pagination .= '<li class="page-item"><span class="page-link">...</span></li>';
                $pagination .= sprintf('<li class="page-item"><a class="page-link" href="#" data-page="%d">%d</a></li>', $total_pages, $total_pages);
            } elseif ($total_pages - ($range * 2) > $page_number && $page_number > ($range * 2)) {
                $pagination .= sprintf('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
                $pagination .= '<li class="page-item"><span class="page-link">...</span></li>';
                for ($i = $page_number - $range; $i <= $page_number + $range; $i++) {
                    $activeClass = $i === $page_number ? 'class="active page-item"' : 'class="page-item"';
                    $pagination .= sprintf('<li %s><a class="page-link" href="#" data-page="%d">%d</a></li>', $activeClass, $i, $i);
                }
                $pagination .= '<li class="page-item"><span class="page-link">...</span></li>';
                $pagination .= sprintf('<li class="page-item"><a class="page-link" href="#" data-page="%d">%d</a></li>', $total_pages, $total_pages);
            } else {
                $pagination .= sprintf('<li class="page-item"><a class="page-link" href="#" data-page="1">1</a></li>');
                $pagination .= '<li class="page-item"><span class="page-link">...</span></li>';
                for ($i = $total_pages - (2 + ($range * 2)); $i <= $total_pages; $i++) {
                    $activeClass = $i === $page_number ? 'class="active page-item"' : 'class="page-item"';
                    $pagination .= sprintf('<li %s><a class="page-link" href="#" data-page="%d">%d</a></li>', $activeClass, $i, $i);
                }
            }
        }
    }

    // Next Page Link
    if ($page_number < $total_pages) {
        $pagination .= sprintf('<li class="page-item"><a class="page-link" href="#" data-page="%d">Next &raquo;</a></li>', $page_number + 1);
    }

    $pagination .= '</ul></nav>';

    // Return the assembled content and pagination
    wp_send_json_success([
        'content' => $content . $pagination,
        'total_pages' => $total_pages,
        'current_page' => $page_number
    ]);
}



function katorymnds_kr_get_abuse_reports($page_number, $reports_per_page, $selected_status = '')
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';
    $offset = ($page_number - 1) * $reports_per_page;

    $where_clause = $selected_status ? $wpdb->prepare(" WHERE status = %s", $selected_status) : " WHERE status IN ('open', 'reviewing', 'closed')";

    $query = "SELECT * FROM {$table_name}{$where_clause} LIMIT %d, %d";
    $prepared_query = $wpdb->prepare($query, $offset, $reports_per_page);

    return $wpdb->get_results($prepared_query);
}


function katorymnds_kr_get_total_report_count()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
    return $total;
}

add_action('wp_ajax_kr_get_abuse_reports_filter', 'kr_handle_filtered_abuse_reports');

function kr_handle_filtered_abuse_reports()
{
    // Verify the AJAX nonce for security
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    if (!current_user_can('manage_kr_abuse_reports') && !current_user_can('administrator')) {
        wp_send_json_error(array('message' => 'You do not have sufficient permissions to access this data.'));
        return;
    }


    // Get the selected status from the AJAX request
    $selected_status = isset($_POST['selected_status']) ? sanitize_text_field($_POST['selected_status']) : 'all';

    // Save the selected status in a transient for later use
    set_transient('kr_selected_status_transient', $selected_status, HOUR_IN_SECONDS);

    // Respond success or error based on whether the selected status was saved successfully
    if (get_transient('kr_selected_status_transient') === $selected_status) {
        wp_send_json_success(array('message' => 'Selected status saved successfully.'));
    } else {
        wp_send_json_error(array('message' => 'Failed to save selected status.'));
    }
}

function kr_store_survey_preview()
{
    // Security check
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    // Permission check
    if (!current_user_can('administrator')) {
        wp_send_json_error(array('message' => 'You do not have sufficient permissions.'));
        return;
    }

    // Define allowed HTML for sanitizing the form preview content
    $allowed_html = array(
        'div' => array(
            'class' => true,
            'id' => true,
            'style' => true,
            'kr-data' => true, // Permitting the kr-data attribute
        ),
        'h3' => array(
            'class' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'h5' => array(
            'class' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'form' => array(
            'class' => true,
            'action' => true,
            'method' => true,
            'id' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'input' => array(
            'class' => true,
            'type' => true,
            'name' => true,
            'value' => true,
            'id' => true,
            'placeholder' => true,
            'checked' => true,
            'required' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'label' => array(
            'class' => true,
            'for' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'textarea' => array(
            'class' => true,
            'name' => true,
            'id' => true,
            'rows' => true,
            'cols' => true,
            'placeholder' => true,
            'required' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'select' => array(
            'class' => true,
            'name' => true,
            'id' => true,
            'required' => true,
            'style' => true,
            'kr-data' => true,
        ),
        'option' => array(
            'value' => true,
            'selected' => true,
            'kr-data' => true,
        ),
        'button' => array(
            'type' => true,
            'class' => true,
            'id' => true,
            'style' => true,
            'kr-data' => true,
        ),
        // Additional elements and attributes as needed, ensuring 'kr-data' is included where applicable
    );

    // Sanitize the HTML content
    $preview_html = wp_kses($_POST['preview_html'], $allowed_html);

    // Use stripslashes() to remove any slashes added to escape characters
    $preview_html = stripslashes($preview_html);

    // Store the sanitized HTML in an option
    update_option('kr_survey_preview_html', $preview_html);

    // Send a success response
    wp_send_json_success();
}
add_action('wp_ajax_kr_store_survey_preview', 'kr_store_survey_preview');

// Delete all preinstalled demos
add_action('wp_ajax_katorymnd_7bnohza_deactivate_demo', 'katorymnd_7bnohza_deactivate_demo');
function katorymnd_7bnohza_deactivate_demo()
{
    global $wpdb; // Global WordPress database access

    // Security check
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    // Ensure current user has the capability to update options and manage the database
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'Unauthorized user'));
        wp_die();
    }

    // Get the deactivate_demo value from AJAX request
    $deactivate_demo = isset($_POST['deactivate_demo']) ? sanitize_text_field($_POST['deactivate_demo']) : '0';

    // Proceed only if deactivation is requested
    if ($deactivate_demo === '1') {
        // Define tables
        $tables = [
            'katorymnd_kr_reactions',
            'katorymnd_kr_intialid_commentid_page',
            'katorymnd_kr_abuse_reports',
            'katorymnd_kr_comments', // Comments must be deleted after reactions due to FK constraint
            'katorymnd_kr_user_details',
            'katorymnd_kr_user_ratings',
            'katorymnd_kr_custom_user_sessions',
        ];

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;

            // Nullify parent_id in comments to avoid foreign key constraint violations
            if ($table === 'katorymnd_kr_comments') {
                $wpdb->query("UPDATE $table_name SET parent_id = NULL WHERE parent_id IS NOT NULL");
            }

            // Delete entries from tables
            $wpdb->query("DELETE FROM $table_name");
            if (!empty($wpdb->last_error)) {
                error_log("Deletion error in $table_name: " . $wpdb->last_error);
            }

            // Handle auto-increment reset for MySQL only
            if ($wpdb->use_mysqli) {
                $wpdb->query("ALTER TABLE $table_name AUTO_INCREMENT = 1");
            } else {
                // For SQLite, we skip resetting the auto-increment as it requires modifying internal SQLite sequences or recreating tables
            }
        }
    }

    // Update the option in the WordPress database
    update_option('kr_deactivate_demo', $deactivate_demo);

    // Send success response
    wp_send_json_success(array('message' => 'Demo sequences and related data have been successfully updated.'));

    wp_die(); // Required to terminate immediately and return a proper response
}

add_action('wp_ajax_kr_save_surveryPoll', 'kr_save_surveryPoll_handler');

function kr_save_surveryPoll_handler()
{
    // Security check
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    $kr_survey_title = isset($_POST['kr_survey_title']) ? sanitize_text_field($_POST['kr_survey_title']) : '';
    $kr_permalink_base = isset($_POST['kr_survey_permalink_base']) ? sanitize_text_field($_POST['kr_survey_permalink_base']) : 'surveys'; // Default to 'surveys'
    $preview_html = get_option('kr_survey_preview_html', ''); // Retrieve the preview HTML with a default fallback

    // Validate inputs
    if (empty($kr_survey_title)) {
        wp_send_json_error(['message' => 'Please provide a title for your survey or poll.']);
        wp_die();
    }

    if ($kr_permalink_base !== 'poll' && $kr_permalink_base !== 'survey' && $kr_permalink_base !== 'feedback') {
        wp_send_json_error(['message' => 'Please select a valid permalink base.']);
        wp_die();
    }

    // Format the post name (slug) to include the permalink base
    $post_name = $kr_permalink_base . '-' . sanitize_title($kr_survey_title);

    // Create or update the survey post
    $post_data = array(
        'post_title'   => $kr_survey_title,
        'post_name'    => $post_name, // Set the formatted post name including the base
        'post_content' => $preview_html,
        'post_status'  => 'draft',
        'post_type'    => 'kr_survey',
    );

    // Insert the post into the database
    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        wp_send_json_error(['message' => 'Failed to create the survey/poll.']);
        wp_die();
    }

    // Update the post meta with specific survey HTML/code
    update_post_meta($post_id, '_kr_survey_html', $preview_html);
    // Update the post meta with the permalink base
    update_post_meta($post_id, '_kr_survey_permalink_base', $kr_permalink_base);


    // Now that the survey/poll has been successfully saved, delete the preview HTML option
    delete_option('kr_survey_preview_html');

    // Success response
    wp_send_json_success(['message' => 'Survey/Poll details saved successfully!', 'postID' => $post_id]);

    wp_die(); // Terminate properly
}

// Hook for logged-in users
add_action('wp_ajax_kr_admin_role_settings', 'kr_handle_role_settings_update');

function kr_handle_role_settings_update()
{
    // Verify the AJAX nonce for security
    check_ajax_referer('katorymnd_kr_cleanup_nonce', 'nonce');

    // Check if the user has the capability to perform this action
    if (!current_user_can('manage_options')) {
        wp_send_json_error(array('message' => 'You do not have sufficient permissions to access this feature.'));
        return;
    }

    // Retrieve the roles settings from the posted data
    $rolesData = isset($_POST['wpkr_role_settings']) ? $_POST['wpkr_role_settings'] : array();

    // Initialize an array to store the updated settings for the response
    $updated_settings = [];

    // Process and update the role settings
    foreach ($rolesData as $role => $value) {
        // Sanitize the role and value
        $role = sanitize_text_field($role);
        $value = $value === '1' ? '1' : '0';

        // Update the option in WordPress; create a unique option name for each role
        update_option("wpkr_role_setting_{$role}", $value);

        // Add the updated setting to the array
        $updated_settings[$role] = get_option("wpkr_role_setting_{$role}");
    }

    // After updating the settings, set a transient flag
    set_transient('kr_update_roles_capabilities', true, DAY_IN_SECONDS); // Expires in 1 day

    // Send a success message back to the front end along with the updated settings
    wp_send_json_success(array('message' => 'Role settings updated successfully.', 'updated_settings' => $updated_settings));
}

function katorymnd_verify_akismet_api_key($akismetApiKey)
{
    $response = wp_remote_post('http://rest.akismet.com/1.1/verify-key', array(
        'body' => array(
            'key' => $akismetApiKey,
            'blog' => get_home_url()
        )
    ));

    if (is_wp_error($response)) {
        return false;
    }

    $responseBody = wp_remote_retrieve_body($response);
    return trim($responseBody) === 'valid';
}


function kr_add_abuse_reports_dashboard_widget()
{
    // Check if the current user has the 'manage_abuse_reports' capability or is an administrator.
    if (current_user_can('manage_kr_abuse_reports') || current_user_can('manage_options')) {
        wp_add_dashboard_widget(
            'kr_abuse_reports_dashboard_widget',         // Widget slug.
            'Reaction Manage Abuse Reports',                   // Title.
            'kr_abuse_reports_dashboard_widget_function' // Display function.
        );
    }
}

add_action('wp_dashboard_setup', 'kr_add_abuse_reports_dashboard_widget');

function kr_abuse_reports_dashboard_widget_function()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';
    // Determine the appropriate link based on the user role
    $page_slug = current_user_can('administrator') ? 'katorymnd-reaction-settings' : 'wpkr-report-abuse';
    $moderate_link = '<a href="' . esc_url(admin_url("admin.php?page={$page_slug}")) . '">' . esc_html__('Moderate Reports', 'katorymnd-reaction-process') . '</a>';

    // Query to fetch the latest 3 reports
    $query = "SELECT * FROM {$table_name} WHERE status IN ('open', 'reviewing') ORDER BY id DESC LIMIT 3";
    $latest_reports = $wpdb->get_results($query);

    // Start of the widget content
    echo '<div class="kr-widget-content">';
    echo '<h3>' . esc_html__('Latest Abuse Reports', 'katorymnd-reaction-process') . '</h3>';

    // Iterate through each report and create a card
    foreach ($latest_reports as $report) {
        $status_class = $report->status == 'Open' ? 'status-open' : 'status-reviewing';
        echo '<div class="kr-report-card ' . $status_class . '">';
        echo '<div class="kr-report-id">' . esc_html__('ID:', 'katorymnd-reaction-process') . ' ' . esc_html($report->id) . '</div>';
        echo '<div class="kr-report-user">' . esc_html__('User:', 'katorymnd-reaction-process') . ' ' . esc_html($report->user_name) . '</div>';
        echo '<div class="kr-report-reason">' . esc_html__('Reason:', 'katorymnd-reaction-process') . ' ' . esc_html($report->reason) . '</div>';
        echo '<div class="kr-report-status">' . esc_html__('Status:', 'katorymnd-reaction-process') . ' ' . esc_html($report->status) . '</div>';
        // Display the moderation link based on the user's role
        echo '<div class="kr-moderation-action">' . $moderate_link . '</div>';
        echo '</div>';
    }

    // Dynamic note based on fetched reports
    if (!empty($latest_reports)) {
        $katorymnd_ogwxcwk = sprintf(esc_html__('Above are the latest %d abuse reports. Please click on %s to review and moderate these reports as soon as possible.', 'katorymnd-reaction-process'), count($latest_reports), $moderate_link);
    } else {
        $katorymnd_ogwxcwk = esc_html__('There are no abuse reports awaiting moderation.', 'katorymnd-reaction-process');
    }
    echo '<p>' . $katorymnd_ogwxcwk . '</p>';
    echo '</div>'; // End of the widget content
}

/**
 * Function to display both feedback form and feedback messages.
 */
function katorymnd_kr_display_feedback()
{
    // Display feedback form
    include plugin_dir_path(__FILE__) . 'katorymnd_feebackForm.php';

    // Display feedback messages
    include plugin_dir_path(__FILE__) . 'katorymnd_Showfeeback.php';
}

/** 
 * Shortcode function to display both feedback form and feedback messages.
 */
function kr_feedback_shortcode()
{
    ob_start();
    katorymnd_kr_display_feedback();
    return ob_get_clean();
}
add_shortcode('katorymnd_feedback', 'kr_feedback_shortcode');

/**
 * Function to display the appropriate rating form based on the saved option or default to star rating.
 */
function katorymnd_kr_display_rating_form()
{
    // Get the saved rating type option, defaulting to 'star' if not set
    $rating_type = get_option('katorymnd_rating_type', 'star');

    // Define the path to the form files
    $form_path = plugin_dir_path(__FILE__);

    // Check the saved rating type and include the appropriate form file
    // Defaults to star rating if the option is not 'slider'
    if ($rating_type === 'slider') {
        include $form_path . 'katorymnd_feedback_slider_rating.php';
    } else { // Default to star rating
        include $form_path . 'katorymnd_feedback_star_rating.php';
    }
}

/**
 * Shortcode function to display the chosen rating form or default to star rating.
 */
function kr_rating_shortcode()
{
    ob_start();
    katorymnd_kr_display_rating_form();
    return ob_get_clean();
}
add_shortcode('katorymnd_rating', 'kr_rating_shortcode');