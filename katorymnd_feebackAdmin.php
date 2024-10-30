<?php
function katorymnd_admin_page()
{
    // Ensure user has the appropriate capability
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to access this page.', 'katorymnd-reaction-process'));
    }


    function katorymnd_get_report_reason_counts()
    {
        global $wpdb;
        $abuse_reports_table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

        $reason_counts = $wpdb->get_results("
            SELECT reason, COUNT(*) as count
            FROM $abuse_reports_table_name
            GROUP BY reason
            ORDER BY count DESC
        ", ARRAY_A);

        return $reason_counts;
    }


    function katorymnd_get_report_status_distribution()
    {
        global $wpdb;
        $abuse_reports_table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

        $query = "
        SELECT status, reason, COUNT(*) as count
        FROM $abuse_reports_table_name
        GROUP BY status, reason
        ORDER BY status, reason
    ";
        $status_and_reason_distribution = $wpdb->get_results($query, ARRAY_A);

        return $status_and_reason_distribution;
    }

    function katorymnd_get_monthly_report_trends()
    {
        global $wpdb;
        $abuse_reports_table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

        $monthly_trends = $wpdb->get_results("
            SELECT DATE_FORMAT(report_date, '%Y-%m') as month, COUNT(*) as count
            FROM $abuse_reports_table_name
            GROUP BY month
            ORDER BY month
        ", ARRAY_A);

        return $monthly_trends;
    }

    function katorymnd_get_report_reason_counts_by_month()
    {
        global $wpdb;
        $abuse_reports_table_name = $wpdb->prefix . 'katorymnd_kr_abuse_reports';

        $reason_counts = $wpdb->get_results("
        SELECT DATE_FORMAT(report_date, '%Y-%m') as month, reason, COUNT(*) as count
        FROM $abuse_reports_table_name
        GROUP BY month, reason
        ORDER BY month, count DESC
    ", ARRAY_A);

        // Group the reason counts by month
        $reason_counts_by_month = [];
        foreach ($reason_counts as $reason) {
            $month = $reason['month'];
            $reason_name = $reason['reason'];
            $count = $reason['count'];

            if (!isset($reason_counts_by_month[$month])) {
                $reason_counts_by_month[$month] = [];
            }

            $reason_counts_by_month[$month][$reason_name] = $count;
        }

        return $reason_counts_by_month;
    }

    function katorymnd_render_analytics_contents()
    {
        // Fetch aggregated data
        $reason_counts_by_month = katorymnd_get_report_reason_counts_by_month();
        $monthly_trends = katorymnd_get_monthly_report_trends();

        // Combine reason counts and monthly trends into a single array
        $combined_data = array(
            'reason_counts_by_month' => $reason_counts_by_month,
            'monthly_trends' => $monthly_trends
        );


        // Convert PHP array to JSON for JavaScript usage
        $json_data = json_encode($combined_data);

        // Pass combined data to JavaScript
        $content = "<script type=\"text/javascript\">
                var combinedData = $json_data;
            </script>";

        return $content;
    }

    // Check if the demo sequences are not deactivated
    if (get_option('kr_deactivate_demo', '0') === '0') {
        // Define a function to generate demo email, full name, and avatar URL
        function generateDemo_kr_UserInfo($username)
        {
            // Email remains the same
            $email = $username . '@example.com';

            // A simple mapping of username to a more realistic full name
            $nameMappings = [
                'Tom' => 'Tom Hanks',
                'UserY' => 'Yvonne Strahovski',
                'UserZ' => 'Zachary Levi',
                'UserJ' => 'John Doe',
                'UserK' => 'Kara Thrace',
                'UserL' => 'Luke Skywalker',
                'UserM' => 'Michael Scott',
                'UserN' => 'Nancy Drew',
                'UserO' => 'Oscar Isaac',
                'UserC' => 'Clark Kent',
                'UserD' => 'Diana Prince',
                'UserE' => 'Ethan Hunt',
                'UserF' => 'Frodo Baggins',
                'UserI' => 'Isaac Newton',
                'UserP' => 'Peter Parker',
                'UserQ' => 'Quentin Tarantino',
                'UserR' => 'Rachel Green',
                'UserA' => 'Arthur Dent',
            ];

            //simple mapping for demonstration, with fallback for undefined names
            $fullName = isset($nameMappings[$username]) ? $nameMappings[$username] : ucwords(str_replace('_', ' ', $username));

            $avatarUrl = 'https://example.com/avatars/' . $username . '.jpg';

            return [
                'username' => $username,
                'email' => $email,
                'fullname' => $fullName,
                'avatar_url' => $avatarUrl
            ];
        }

        // Given array of usernames
        $usernames = [
            'Tom', 'UserY', 'UserZ', 'UserJ', 'UserK',
            'UserL', 'UserM', 'UserN', 'UserO', 'UserC',
            'UserD', 'UserE', 'UserF', 'UserI', 'UserJ',
            'UserP', 'UserQ', 'UserR', 'UserD', 'UserE',
            'UserA'
        ];

        // Generate user info for each username
        $userDetailsBatch = array_map(function ($username) {
            return generateDemo_kr_UserInfo($username);
        }, $usernames);

        // Now, use the generated $userDetailsBatch with the custom action
        do_action('katorymnd_kr_add_user_details', $userDetailsBatch);
    }

    // Admin Page Layout
?>
<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    <nav class="nav-tab-wrapper">
        <a href="#kr-home" class="nav-tab kr-nav-tab" data-tab="kr-home">Home</a>
        <a href="#kr-analytics" class="nav-tab kr-nav-tab" data-tab="kr-analytics">Analytics</a>
        <a href="#kr-setup" class="nav-tab kr-nav-tab" data-tab="kr-setup">Setup & Instructions</a>
        <a href="#kr-reports" class="nav-tab kr-nav-tab" data-tab="kr-reports">Abuse Reports</a>
        <a href="#kr-reports-managers" class="nav-tab kr-nav-tab" data-tab="kr-reports-managers">Capability
            Management</a>
        <a href="#kr-questionnaire-voting-tool" class="nav-tab kr-nav-tab"
            data-tab="kr-questionnaire-voting-tool">Survey/Poll Tool</a>

        <!-- Add more tabs with 'kr-' prefix if needed -->
    </nav>

    <div id="kr-home" class="kr-tab-content grid">
        <!-- Card for Form -->
        <div class="wpkr-card">
            <div class="wpkr-card-header">
                <h3>Emoji Theme Settings</h3>
            </div>
            <div class="wpkr-card-body">
                <p>
                    Customize the appearance of reaction emojis in your comment section with our Emoji Theme Settings.
                    By default, standard emojis are displayed. However, you can tailor the emoji set to better fit the
                    theme or tone of your website. Whether you're looking for a more academic feel or something festive
                    like Halloween, simply select your preferred theme from the dropdown below. The corresponding emoji
                    set will be dynamically loaded to align with your selection.
                </p>
                <form id="katorymndSettingsForm" method="post">
                    <label for="emojiThemeSelector">Select Emoji Theme:</label>
                    <select id="emojiThemeSelector" name="katorymnd_options[emoji_theme]" class="wpkr-select">
                        <?php
                            $saved_emoji_theme = get_option('katorymnd_emoji_theme', 'default');
                            $themes = array('default' => 'Default', 'academic' => 'Academic', 'halloween' => 'Halloween');

                            foreach ($themes as $value => $label) {
                                echo '<option value="' . esc_attr($value) . '"' . selected($value, $saved_emoji_theme, false) . '>' . esc_html($label) . '</option>';
                            }
                            ?>
                    </select>
                    <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
                        <p></p>
                    </div>
                    <?php submit_button('Save Settings'); ?>
                </form>
            </div>
        </div>
        <div class="wpkr-card">
            <div class="wpkr-card-header">
                <h3>Emoji Selection</h3>
            </div>
            <div class="wpkr-card-body">
                <p>
                    Customize the emojis displayed in your comment section. Select the emojis you want to include from
                    the options below. Your chosen set of emojis will be used for user reactions.
                </p>
                <form id="katorymndKrEmojiSelectionForm" method="post">
                    <div class="wpkr-emoji-selection">
                        <?php
                            $allEmojis = ['like', 'dislike', 'love', 'smile', 'laugh', 'angry', 'cry', 'shock'];
                            $savedEmojiSelection = get_option('katorymnd_emoji_selection', $allEmojis); // fetch saved selection if any

                            foreach ($allEmojis as $emoji) {
                                $isSelected = in_array($emoji, $savedEmojiSelection);
                                $tooltipText = ucfirst($emoji); // Tooltip text is the capitalized emoji name
                                echo '<label class="wpkr-emoji-label" data-bs-toggle="tooltip" data-bs-placement="top" title="' . esc_attr($tooltipText) . '">';
                                echo '<input type="checkbox" name="katorymnd_options[emoji_selection][]" value="' . esc_attr($emoji) . '"' . ($isSelected ? ' checked' : '') . '>';
                                echo '<img src="' . esc_url(plugin_dir_url(__FILE__)) . '/img/emojis/default/' . $emoji . '.png" alt="' . esc_attr($emoji) . '">';
                                echo '</label>';
                            }
                            ?>
                    </div>
                    <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
                        <p></p>
                    </div>

                    <?php submit_button('Save Emoji Selection'); ?>
                </form>
            </div>
        </div>

        <div class="wpkr-card ">
            <div class="wpkr-card-header">
                <h3>Customize Comment Reactions</h3>
            </div>
            <div class="wpkr-card-body">
                <p class="customize-instruction">
                    Customize the background color of the comment card header to match your template design. Click on
                    the header below to select a new color. Your changes will be saved automatically.
                </p>

                <div class="card mb-3 shadow" id="katorymnd_0b1e5jp">
                    <div id="katorymnd_ysz2tcc" class="card-header text-white d-flex align-items-center"
                        style="background-color: <?php echo get_option('katorymnd_header_bg_color', 'rgb(0, 123, 255)'); ?>;">
                        <div class="kr-avatar">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="50" fill="#bbb"></circle>
                            </svg>
                        </div>
                        <div id="katorymnd_axcpgch" class="kr-comment-text ms-2">
                            <strong>User</strong>
                        </div>
                    </div>
                    <input type="text" id="headerColorPicker" style="display: none;">

                    <div class="card-body">
                        <p class="card-text">This is a sample comment.</p>
                        <div class="kr-actions">
                            <a>React</a>
                            <a>Reply</a>
                            <span>2 min ago</span>
                            <a>Edit</a>
                            <a>Delete</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php

            // Fetch current filter settings
            $filter_settings = get_option('katorymnd_filter_settings');

            // Set default values for new filter settings if they are not set
            if (!is_array($filter_settings)) {
                $filter_settings = []; // Ensure $filter_settings is an array if it's not already
            }
            $filter_settings = array_merge([
                'filter_url' => '1',
                'filter_email' => '1',
                'filter_anchor' => '1',
                'filter_phone' => '1',
                'filter_spam_keywords' => '1',
                'filter_spam_patterns' => '1',
            ], $filter_settings);
            // Start the form
            $formHtml = '<form id="katorymnd_security_settings" method="post" class="container">
<fieldset>
    <legend>Automatic Content Filters:</legend>
    <p><small>Toggle these options to enable automatic filtering of specific types of content in comments. Selected by default.</small></p>';

            $filters = [
                'filter_url' => 'Filter URLs',
                'filter_email' => 'Filter Emails',
                'filter_anchor' => 'Filter Anchors',
                'filter_phone' => 'Filter Phone Numbers',
                'filter_spam_keywords' => 'Filter Spam Keywords',
                'filter_spam_patterns' => 'Filter Spam Patterns',
            ];

            foreach ($filters as $filter_name => $filter_label) {
                $formHtml .= '
<div class="form-check" style="display: flex; align-items: center;">
    <input class="form-check-input" type="checkbox" name="' . $filter_name . '" value="1" ' . checked('1', $filter_settings[$filter_name], false) . ' id="' . $filter_name . '">
    <label class="form-check-label custom-form-check-label" for="' . $filter_name . '" style="margin-left: 10px;">' . $filter_label . '</label>
</div>';
            }

            $formHtml .= '</fieldset>';

            // Akismet Status
            $akismetActive = is_plugin_active('akismet/akismet.php');
            $akismet_api_key = get_option('akismet_api_key', '');

            if ($akismetActive) {
                $formHtml .= '
<fieldset>
    <legend>Akismet Spam Protection:</legend>
    <p>Enhance spam protection by integrating with Akismet. Enter your API Key below.</p>
    <div class="mb-3">
        <label for="akismet_api_key" class="form-label">Akismet API Key:</label>
        <input type="text" class="form-control" id="akismet_api_key" name="akismet_api_key" value="' . esc_attr($akismet_api_key) . '" required>
    </div>
</fieldset>';
            } else {
                $formHtml .= '
<fieldset>
    <legend>Akismet Spam Protection:</legend>
    <p><strong>Akismet spam protection is not enabled.</strong> For enhanced spam filtering, <a href="' . admin_url('plugins.php') . '">activate the Akismet plugin</a> and provide your API Key.</p>
</fieldset>';
            }

            // Closing form with a Bootstrap-styled submit button
            $formHtml .= '<div class="d-grid gap-2"><button type="submit" name="save_security_settings" class="btn btn-primary">Save Settings</button></div></form>';

            echo '<div class="wpkr-card">
<div class="wpkr-card-header">
    <h3>Comment Security Settings</h3>
</div>
<div class="wpkr-card-body">' . $formHtml . '</div>
</div>';
            ?>

        <div class="wpkr-card ">
            <div class="wpkr-card-header">
                <h3>User Data Management for Comment Reactions</h3>
            </div>
            <div class="wpkr-card-body">
                <p class="customize-instruction">
                    To enhance user engagement and streamline the management of user interactions, such as comments and
                    reactions, you may specify a custom database table name and its column mappings below. This is
                    necessary if you have stored registered user details outside the default WordPress users table, to
                    ensure that the Katorymnd Reaction Plugin can correctly interpret and utilize your custom user data.
                    Please provide the name of your custom table and map the expected fields (user_id, username, email,
                    full_names, and avatar) to your table's corresponding column names. If your registered users are
                    stored in the default WordPress users table, there's no need for customization; the plugin will
                    automatically use the WordPress default. Using a custom table ensures optimized data handling and
                    can improve site performance. Changes made here are applied immediately and should be carefully
                    considered to align with your data architecture strategy.
                </p>

                <!-- Custom Table Name Input -->
                <div class="d-grid gap-2 mb-3">
                    <label for="kr-customTableName" class="form-label">Specify Custom Table Name:</label>
                    <input type="text" class="form-control" id="kr-customTableName" name="kr_custom_table_name"
                        placeholder="Enter custom table name"
                        value="<?php echo esc_attr(get_option('katorymnd_kr_custom_table_name', '')); ?>">
                </div>

                <!-- Custom Table Column Mapping -->
                <div class="d-grid gap-2 mb-3">
                    <p>Please map your custom table columns to the following expected fields:</p>
                    <label for="kr-userIdColumnName" class="form-label">User ID Column Name:</label>
                    <input type="text" class="form-control" id="kr-userIdColumnName" name="kr_user_id_column_name"
                        placeholder="e.g., user_id"
                        value="<?php echo esc_attr(get_option('katorymnd_kr_user_id_column_name', '')); ?>">

                    <label for="kr-usernameColumnName" class="form-label">Username Column Name:</label>
                    <input type="text" class="form-control" id="kr-usernameColumnName" name="kr_username_column_name"
                        placeholder="e.g., username"
                        value="<?php echo esc_attr(get_option('katorymnd_kr_username_column_name', '')); ?>">

                    <label for="kr-emailColumnName" class="form-label">Email Column Name:</label>
                    <input type="text" class="form-control" id="kr-emailColumnName" name="kr_email_column_name"
                        placeholder="e.g., email"
                        value="<?php echo esc_attr(get_option('katorymnd_kr_email_column_name', '')); ?>">

                    <label for="kr-fullNamesColumnName" class="form-label">Full Names Column Name:</label>
                    <input type="text" class="form-control" id="kr-fullNamesColumnName" name="kr_full_names_column_name"
                        placeholder="e.g., full_names"
                        value="<?php echo esc_attr(get_option('katorymnd_kr_full_names_column_name', '')); ?>">

                    <label for="kr-avatarColumnName" class="form-label">Avatar Column Name:</label>
                    <input type="text" class="form-control" id="kr-avatarColumnName" name="kr_avatar_column_name"
                        placeholder="e.g., avatar"
                        value="<?php echo esc_attr(get_option('katorymnd_kr_avatar_column_name', '')); ?>">
                </div>
                <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
                    <p></p>
                </div>
                <button id="kr_saveCustomTableName" class="btn btn-primary mt-2">Save Configuration</button>
            </div>
        </div>

        <div class="wpkr-card">
            <div class="wpkr-card-header">
                <h3><?php esc_html_e('Display Comments Settings', 'katorymnd-reaction-process'); ?></h3>
            </div>
            <div class="wpkr-card-body">

                <p><?php esc_html_e('The "Enable Display All Comments" feature allows you to toggle the visibility of all comments on your site. When activated, it showcases the operational logic of the commenting system, specifically designed to assist site owners in understanding the comment functionality. This feature is instrumental for demonstration purposes, illustrating how comments are associated with their respective pages.', 'katorymnd-reaction-process'); ?>
                </p>

                <p><?php esc_html_e('With this feature enabled, if a page lacks comments, a default set of all comments is displayed, simulating how actual user comments would appear once the page becomes active with user interactions. Conversely, for pages with existing comments, those comments are presented, offering a realistic view of how the system manages and displays user feedback. Importantly, when the demo mode is on, all saved comments in the database are displayed for pages without comments, providing a comprehensive preview. However, turning the demo off ensures that each page only displays its actual comments, maintaining the integrity of the user-generated content.', 'katorymnd-reaction-process'); ?>
                </p>

                <p><?php esc_html_e('This functionality extends to all demonstrations within the plugin, serving as a versatile tool for site owners to preview and understand the comment system&rsquo;s behavior and logic.', 'katorymnd-reaction-process'); ?>
                </p>


                <form method="post" class="wpkr-settings-form">
                    <div class="wpkr-form-group">
                        <label for="wpkr_toggleAll_OffON">Toggle Demo Display:</label><br>
                        <div class="slider-container"
                            style="position: relative; width: 100px; height: 40px; background-color: #ccc; border-radius: 20px; transition: background-color 0.4s;">
                            <input type="checkbox" id="wpkr_demo_enabled" name="wpkr_demo_enabled" value="1"
                                <?php echo checked(1, get_option('wpkr_demo_enabled'), false); ?>
                                style="display:none;" />
                            <div class="wpkr_slider"
                                style="position: absolute; width: 40px; height: 40px; left: 0; background-color: white; border-radius: 50%; transition: transform 0.4s, background-color 0.4s; cursor: pointer;">
                            </div>
                            <span class="wpkr_slider-label-off"
                                style="position: absolute; color: white; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none;">OFF</span>
                            <span class="wpkr_slider-label-on"
                                style="position: absolute; color: white; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; opacity: 0;">ON</span>
                        </div>
                    </div>
                    <!-- New field for setting the default number of comments -->
                    <div class="mb-3">
                        <label for="wpkr_default_num_comments" class="form-label">Default Number of Comments:</label>
                        <input type="number" class="form-control" id="wpkr_default_num_comments"
                            name="wpkr_default_num_comments"
                            value="<?php echo esc_attr(get_option('wpkr_default_num_comments', 10)); ?>" min="1">
                    </div>
                    <!-- New field for setting the number of comments to display -->
                    <div class="mb-3">
                        <label for="wpkr_num_comments" class="form-label">Number of Comments to Display:</label>
                        <input type="number" class="form-control" id="wpkr_num_comments" name="wpkr_num_comments"
                            value="<?php echo esc_attr(get_option('wpkr_num_comments', 1)); ?>" min="1">
                    </div>
                    <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
                        <p></p>
                    </div>
                    <button type="button" id="katorymnd_cmjan8b" class="btn btn-primary" name="submit"
                        value="save_changes">Save Changes</button>

                </form>
            </div>
        </div>

        <div class="wpkr-card">
            <div class="wpkr-card-header">
                <h3><?php esc_html_e('Rating System Configuration', 'katorymnd-reaction-process'); ?></h3>
            </div>
            <div class="wpkr-card-body">
                <p><?php esc_html_e('Select the preferred rating system to be used across your site. You can choose between a Star Rating system, which allows users to rate content by selecting a number of stars, and a Slider Rating system, which provides a slider for users to choose their rating value dynamically.', 'katorymnd-reaction-process'); ?>
                </p>

                <form id="katorymnd_rating_form" method="post" action="">
                    <?php $current_rating_system = get_option('katorymnd_rating_type', 'star'); ?>
                    <div class="form-check mb-3" style="display: flex; align-items: center;">
                        <input class="form-check-input" type="radio" name="katorymnd_rating_type" id="ratingStar"
                            value="star" <?php checked($current_rating_system, 'star'); ?>>
                        <label class="form-check-label" for="ratingStar">
                            <?php esc_html_e('Star Rating', 'katorymnd-reaction-process'); ?>
                        </label>
                    </div>
                    <div class="form-check mb-4" style="display: flex; align-items: center;">
                        <input class="form-check-input" type="radio" name="katorymnd_rating_type" id="ratingSlider"
                            value="slider" <?php checked($current_rating_system, 'slider'); ?>>
                        <label class="form-check-label" for="ratingSlider">
                            <?php esc_html_e('Slider Rating', 'katorymnd-reaction-process'); ?>
                        </label>
                    </div>
                    <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
                        <p></p>
                    </div>
                    <div class="mt-2">
                        <button type="button" id="katorymnd_3lcx36a" class="btn btn-primary" name="submit"
                            value="save_changes">
                            <?php echo esc_html__('Save Changes', 'katorymnd-reaction-process'); ?>
                        </button>
                    </div>
                </form>


            </div>

        </div>

        <!-- Cards more -->
    </div>

    <div id="kr-analytics" class="kr-tab-content" style="display: none;">
        <!-- Content for Analytics Section -->
        <div class="kr-accordion">
            <div class="kr-accordion-item">
                <button class="kr-accordion-button">User Engagement Metrics</button>
                <div class="kr-accordion-content">
                    <h3>User Engagement Metrics</h3>
                    <p>This chart provides a visual representation of user interactions across different pages of the
                        platform. Each bar signifies the aggregate number of user comments and replies, reflecting the
                        overall engagement level. These insights are instrumental in understanding user preferences,
                        enhancing content relevance, and making informed decisions to improve user experience and
                        engagement.</p>
                    <canvas id="kr-commentsChart"></canvas>
                </div>

            </div>
        </div>

    </div>

    <div id="kr-setup" class="kr-tab-content" style="display: none;">
        <h2>Plugin Setup and Usage</h2>
        <p>Welcome to the Katorymnd Reaction plugin! Follow these instructions to set up and use the plugin effectively.
        </p>

        <div class="kr-accordion">
            <div class="kr-accordion-item">
                <button class="kr-accordion-button">Initial Setup Information</button>
                <div class="kr-accordion-content">
                    <p>Upon activating the plugin, all necessary components, including a dedicated database table, are
                        automatically set up for you.</p>
                    <strong>Note:</strong> Deactivating the plugin will remove all associated data and tables to keep
                    your database clean.

                    <!-- Explanatory Text -->
                    <p style="margin-top: 20px;">
                        If you wish to clean up all data created by the plugin and deactivate it, you can do so by
                        clicking the button below.<br>
                        <strong>Warning:</strong> This action is irreversible and will permanently delete all plugin
                        data.
                    </p>

                    <!-- Clean Up Data Button -->
                    <button id="katorymnd-cleanup-data-btn" class="button button-primary"
                        style="margin-top: 10px; margin-bottom: 10px;">
                        <?php esc_html_e('Clean Up Data and Deactivate Plugin', 'katorymnd-reaction-process'); ?>
                    </button>
                </div>
            </div>
            <div class="kr-accordion-item">
                <button class="kr-accordion-button">Integrating Feedback Features</button>
                <div class="kr-accordion-content">
                    <p>To integrate both the feedback form and feedback messages on your pages, use the following:</p>
                    <ul>
                        <li>Use the shortcode: <code class="kr-code">[katorymnd_feedback]</code> for the feedback
                            system.</li>
                        <li>Or, insert the following PHP code in your template for feedback: <code
                                class="kr-code">&lt;?php echo do_shortcode('[katorymnd_feedback]'); ?&gt;</code></li>
                    </ul>
                    <p>This will display the feedback form and list the feedback messages wherever you include it in
                        your site.</p>

                </div>

            </div>
            <div class="kr-accordion-item">
                <button class="kr-accordion-button">Integrating Rating System</button>
                <div class="kr-accordion-content">
                    <p>To integrate the rating system based on your selection (Star or Slider) on your pages, use the
                        following methods:</p>
                    <ul>
                        <li>Use the shortcode: <code class="kr-code">[katorymnd_rating]</code></li>
                        <li>Or, insert the following PHP code in your template: <code
                                class="kr-code">&lt;?php echo do_shortcode('[katorymnd_rating]'); ?&gt;</code></li>
                    </ul>
                    <p>This will display the chosen rating form (Star Rating or Slider Rating) wherever you include it
                        on your site, based on the current setting in your Rating System Configuration area.</p>
                </div>
            </div>
            <?php
                // Fetch the option value; default to '0' if not set
                $kr_deactivate_demo = get_option('kr_deactivate_demo', '0');

                // Display the block only if kr_deactivate_demo is '0'
                if ($kr_deactivate_demo === '0') :
                ?>
            <div class="kr-accordion-item"
                style="font-family: Arial, sans-serif; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 5px; overflow: hidden;">
                <button class="kr-accordion-button"
                    style="background-color: #B01515; color: white; padding: 10px 15px; width: 100%; text-align: left; border: none; outline: none; transition: background-color 0.3s;">
                    Deactivate Demo Sequences
                </button>
                <div class="kr-accordion-content" style="padding: 20px; display: none; background-color: #f9f9f9;">
                    <p>To ensure that your site remains professional and tailored to your audience, the Katorymnd
                        Reaction Plugin provides demo sequences as examples. If you're ready to move on from these
                        examples and use your customized settings exclusively, you can deactivate the demo sequences
                        below. This action will hide all default demo content, allowing you to showcase only your
                        original content or configurations.</p>
                    <form id="kr-deactivate-demo-form"
                        style="background-color: #fff; padding: 15px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <h4 style="margin-top: 0;">Deactivate Demo Sequences</h4>
                        <label for="kr-deactivate-demo" style="display: block; margin-bottom: 10px;">Check this box to
                            deactivate all demo sequences for the Katorymnd Reaction Plugin:</label>
                        <input type="checkbox" id="kr-deactivate-demo" name="kr-deactivate-demo"
                            style="margin-right: 10px;" <?php echo $kr_deactivate_demo === '1' ? 'checked' : ''; ?>>
                        <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
                            <p></p>
                        </div>
                        <input type="button" id="katorymnd_27d8alg" value="Save Changes"
                            style="background-color: #28a745; color: white; padding: 10px 15px; border: none; border-radius: 5px; cursor: pointer;">
                    </form>

                </div>
            </div>

            <?php
                endif;
                ?>
        </div>
    </div>

    <div id="kr-reports" class="kr-tab-content" style="display: none;">
        <h2>Manage Abuse Reports</h2>
        <p>Welcome to the Abuse Reports management section. Here, you can view, analyze, and take action on reports
            submitted by users. Use the tools provided to maintain a safe and respectful community.</p>

        <div class="kr-accordion">
            <div class="kr-accordion-item">
                <button class="kr-accordion-button">Viewing Reports</button>
                <div class="kr-accordion-content">
                    <p>This section lists all the abuse reports submitted by users. You can review each report's
                        details, including the reason for the report, the user who reported it, and the reported content
                        or comment ID.</p>

                    <!-- Filter Form with Bootstrap 5.3 Styling -->
                    <div class="kr-report-filters mt-3 mb-3">
                        <form id="kr-report-filter-form" class="row g-3 align-items-center">
                            <div class="col-auto">
                                <label for="report-status-filter" class="col-form-label">Filter reports:</label>
                            </div>
                            <div class="col-auto">
                                <select id="kr-report-status-filter" class="form-select">
                                    <option value="all">All</option>
                                    <option value="open">Open</option>
                                    <option value="reviewing">Reviewing</option>
                                    <option value="closed">Closed</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <button type="button" id="krFilterReports" class="btn btn-primary">Apply Filter</button>
                            </div>
                        </form>
                    </div>


                    <!-- Placeholder for Dynamic Table -->
                    <div id="abuse-reports-table-container">
                        <!-- The table will be dynamically inserted here -->
                    </div>

                    <p>After reviewing a report, you can take appropriate action. Actions include marking the report as
                        reviewed, editing the comment content, or banning the user from future postings.</p>
                    <strong>Note:</strong> Please ensure to review reports carefully before taking any action to avoid
                    mistakenly penalizing innocent users.

                </div>
            </div>
            <div id="kr-report-details-dialog" title="Report Details" style="display:none;">
                <!-- Details will be dynamically filled based on the clicked "Review" button -->
            </div>
            <div class="kr-accordion-item">
                <button class="kr-accordion-button">Report Analytics</button>
                <div class="kr-accordion-content">
                    <p>Use the Analytics tab to gain insights into the types of reports being submitted, frequency, and
                        trends over time. This information can help you identify areas for community guidelines
                        improvement or increased moderation.</p>
                    <?php
                        echo  katorymnd_render_analytics_contents();

                        $reportStatusDistribution = katorymnd_get_report_status_distribution();

                        // Initialize arrays to hold the structured data
                        $statusLabels = []; // For unique statuses
                        $statusReasonsData = []; // For detailed data including reasons

                        // Process each entry in the distribution
                        foreach ($reportStatusDistribution as $item) {
                            // Add status to labels if it hasn't already been added
                            if (!in_array($item['status'], $statusLabels)) {
                                $statusLabels[] = $item['status'];
                            }

                            // Key to group by status and reason
                            $key = $item['status'] . ' - ' . $item['reason'];

                            // Initialize or add count for each unique status-reason combination
                            if (!isset($statusReasonsData[$key])) {
                                $statusReasonsData[$key] = 0;
                            }
                            $statusReasonsData[$key] += $item['count'];
                        }

                        // Prepare structured data for JavaScript
                        $statusData = []; // Reset to use for detailed count (reasons under statuses)
                        foreach ($statusReasonsData as $statusReason => $count) {
                            list($status, $reason) = explode(' - ', $statusReason, 2); // Separate status and reason
                            if (!isset($statusData[$status])) {
                                $statusData[$status] = [];
                            }
                            $statusData[$status][] = ['reason' => $reason, 'count' => $count];
                        }

                        ?>
                    <script type="text/javascript">
                    var statusChartData = {
                        labels: <?php echo json_encode($statusLabels); ?>,
                        data: <?php echo json_encode($statusData); ?>
                    };

                    // console.log('Status Chart Data:', statusChartData);
                    </script>
                    <div class="kr_charts-container">
                        <!-- Container for Report Status Chart -->
                        <div class="kr_chart-container">
                            <canvas id="kr_reportStatusChart"></canvas>
                        </div>

                        <div id="katorymnd_uj2spxx">
                            <!-- Dropdown for selecting Year -->
                            <div class="kr_filter-container">
                                <label for="yearFilter">Select Year:</label>
                                <select id="yearFilter">
                                    <option value="all">All Years</option>
                                    <!-- Populate with years from data -->
                                </select>
                            </div>

                            <!-- Dropdown for selecting Month -->
                            <div class="kr_filter-container">
                                <label for="monthFilter">Select Month:</label>
                                <select id="monthFilter">
                                    <option value="all">All Months</option>
                                    <!-- Populate with months from data -->
                                </select>
                            </div>
                        </div>
                        <!-- Container for Monthly Trends Chart -->
                        <div class="kr_chart-container">
                            <canvas id="kr_combinedChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="kr-reports-managers" class="kr-tab-content" style="display: none;">
        <h2>Manage Abuse Reports Capability</h2>
        <p class="description">Use the checkboxes below to assign or revoke the ability for specific roles to manage
            abuse reports. Enabling this capability allows users with the selected roles to view, moderate, and manage
            all abuse reports submitted by users.</p>

        <form method="post" action="" class="form-horizontal">
            <fieldset>
                <?php
                    $roles = ['administrator', 'editor', 'author', 'contributor'];

                    foreach ($roles as $role) {
                        // Construct the option name
                        $option_name = "wpkr_role_setting_{$role}";

                        // Retrieve the setting from WordPress database, defaulting to '0' if not set
                        $setting_value = get_option($option_name, '0'); // Assume '0' as default, meaning unchecked

                        // Determine if the checkbox should be checked
                        $checked = $setting_value === '1' ? 'checked' : '';
                    ?>
                <div class="form-check" style="display: flex; align-items: center;">
                    <input type="checkbox" class="form-check-input" id="wpkrc_<?php echo $role; ?>"
                        name="wpkr_kr_report_manage_settings[<?php echo $role; ?>]" value="1" <?php echo $checked; ?>>
                    <label class="form-check-label"
                        for="wpkrc_<?php echo $role; ?>"><?php echo ucfirst($role); ?></label>
                </div>
                <?php
                    }
                    ?>
            </fieldset>

            <button type="button" id="katorymnd_pn1fmwn" class="btn btn-primary">Save Changes</button>
        </form>

        <div id="katorymndAdminNotice" class="notice is-dismissible" style="display:none;">
            <p></p>
        </div>
    </div>

    <div id="kr-questionnaire-voting-tool" class="kr-tab-content" style="display: none;">
        <h2>Create a Survey/Poll Tool</h2>
        <p class="description">
            Welcome to the Survey/Poll Creation Tool, where you can design, customize, and deploy engaging surveys and
            polls for your audience. This intuitive platform allows you to gather valuable feedback, insights, and
            opinions from your target group with ease. Here's how to get started and what to expect:

            <strong>Getting Started:</strong>
        <ol>
            <li><strong>Add Questions:</strong> Begin by selecting the type of question you want to add to your survey
                or poll. Choose from various question types, including Radio Buttons, Checkboxes, Dropdowns, Likert
                Scales, and Text Input, to suit the kind of response you're seeking.</li>
            <li><strong>Customize Questions:</strong> Once a question is added, you can customize its text and add or
                modify the response options. For Likert scales and multiple-choice questions, options can be reordered
                or deleted as needed.</li>
            <li><strong>Organize Your Survey:</strong> Drag and drop to rearrange the order of the questions. This
                flexibility allows you to design your survey logically and ensure a smooth flow for respondents.</li>
            <li><strong>Preview & Edit:</strong> Utilize the 'Preview Survey' feature to see how your survey or poll
                looks from a respondent's perspective. Return to edit or rearrange questions anytime based on the
                preview.</li>
        </ol>

        <strong>What to Expect:</strong>
        <ul>
            <li>Seamless Integration: Once created, your survey or poll can be easily integrated into your website,
                emails, or shared via social media to reach your audience.</li>
            <li>Engagement: Engage your audience with a visually appealing and straightforward survey format that
                encourages completion.</li>
            <li>Insights: Collect and analyze responses to gain actionable insights, understand preferences, and make
                informed decisions.</li>
        </ul>

        Ready to create your survey or poll? Begin by adding your first question and discover the simplicity and power
        of our Survey/Poll Creation Tool. Your feedback journey starts here!
        </p>
        <div class="container my-5">
            <h2 class="text-center">Admin: Create a Survey/Poll</h2>
            <div id="krActionButtonsContainer" class="d-flex flex-wrap">
                <!-- Make sure this container allows for the flexible arrangement of buttons -->
                <button type="button" class="btn btn-info m-2" onclick="krAddQuestion('radio')">Add Radio Button
                    Question</button>
                <button type="button" class="btn btn-info m-2" onclick="krAddQuestion('checkbox')">Add Checkbox
                    Question</button>
                <button type="button" class="btn btn-info m-2" onclick="krAddQuestion('dropdown')">Add Dropdown
                    Question</button>
                <button type="button" class="btn btn-info m-2" onclick="krAddQuestion('likert')">Add Likert Scale
                    Question</button>
                <button type="button" class="btn btn-info m-2" onclick="krAddQuestion('text')">Add Text Input
                    Question</button>
            </div>

            <form id="krSurveyPollForm" class="my-4"></form>

            <button type="button" id="krPreviewSurveyPoll" class="btn btn-primary">Preview Survey</button>
        </div>
        <!-- More sections with 'kr-' prefix -->

    </div>
    <?php
}