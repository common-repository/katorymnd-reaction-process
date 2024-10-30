<?php

$katorymnd_plug = wp_get_current_user();
//print_r($katorymnd_plug);
//do_action('katorymnd_kr_add_user_details', 'johndoe', 'john@example.com', 'John Doe', 'https://example.com/avatar.jpg');



// Retrieve the current page details first 
$pageDetails = \Kr_page_details\Katorymnd_reaction\KatorymndUtility::get_kr_current_page_details();

// Check if the page details were successfully retrieved by checking the 'id'
if ($pageDetails['id'] !== null) {
    // Echo out the page details for debugging or informational purposes
   // echo 'Page ID: ' . $pageDetails['id'] . '<br>';
   // echo 'Page Slug: ' . $pageDetails['slug'] . '<br>';
    // echo 'Page Title: ' . $pageDetails['title'] . '<br>';

    // Output the page details as a JavaScript variable
    echo "<script>";
    echo "var kr_pageDetails = " . json_encode($pageDetails) . ";";
    echo "console.log(kr_pageDetails);";  // This will log the details to the browser console
    echo "</script>";

    // Render the form and related elements
?>
    <div class="wp-block-group">
        <h2 id="kr_leave_comment" style="cursor:pointer;"><?php echo esc_html__('Leave a Comment', 'katorymnd-reaction-process'); ?></h2>

        <form id="katorymnd_lrdn" method="post" class="wp-block-form form-hidden katorymnd_63p1h9o">
            <div class="wp-block-form-group">
                <label for="tirq"><?php echo esc_html__('Your Comment', 'katorymnd-reaction-process'); ?></label>
                <textarea id="tirq" name="katorymnd_habm" placeholder="<?php echo esc_attr__('Write your comment here...', 'katorymnd-reaction-process'); ?>" rows="5" class="wp-block-textarea"></textarea>
            </div>
            <!-- Securely adding the page details from $pageDetails -->
            <input type="hidden" name="page_id" value="<?php echo esc_attr($pageDetails['id']); ?>">
            <input type="hidden" name="page_slug" value="<?php echo esc_attr($pageDetails['slug']); ?>">
            <input type="hidden" name="page_title" value="<?php echo esc_attr($pageDetails['title']); ?>">
            <div class="wp-block-button" style="margin-top: 20px;">
                <button type="submit" id="kr_submit_comment" name="submit_comment" class="wp-block-button__link">
                    <?php esc_html_e('Submit Comment', 'katorymnd-reaction-process'); ?>
                </button>
            </div>
        </form>


        <!-- Logical space and heading to indicate the start of the comments section -->
        <div class="comments-section-start" style="margin-top: 50px;">
            <h2><?php echo esc_html__('Comments', 'katorymnd-reaction-process'); ?></h2>
            <!-- You can add a short line or description here if needed -->
        </div>
    </div>
<?php
}
?>