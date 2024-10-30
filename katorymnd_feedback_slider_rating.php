<?php

// Retrieve the current page details first 
$pageDetails = \Kr_page_details\Katorymnd_reaction\KatorymndUtility::get_kr_current_page_details();

// Check if the page details were successfully retrieved by checking the 'id'
if ($pageDetails['id'] !== null) {
    // Echo out the page details for debugging or informational purposes
    //echo 'Page ID: ' . $pageDetails['id'] . '<br>';
   // echo 'Page Slug: ' . $pageDetails['slug'] . '<br>';
    // echo 'Page Title: ' . $pageDetails['title'] . '<br>';

    $page_id = $pageDetails['id'];

    // Retrieve the overall rating for this page
    $overall_rating = get_overall_rating($page_id);

    // Prepare the data as an associative array
    $ratingData = array(
        'page_id' => $page_id,
        'overall_rating' => $overall_rating,
    );

    // Encode the data as JSON
    $ratingDataJson = json_encode($ratingData);

    // Display the rating
   // echo 'Overall Rating: ' . esc_html($overall_rating);

    // Output the page details as a JavaScript variable
    echo "<script>";
    echo "var kr_pageDetails = " . json_encode($pageDetails) . ";";
    //echo "console.log(kr_pageDetails);";  // This will log the details to the browser console
    echo "var krRatingData = " . $ratingDataJson . ";";
    echo "</script>";

    // Render the form and related elements

}
?>
<div class="container mt-1 mx-3 my-3">
    <label for="ratingSliderKr" class="form-label"><span id="katorymnd_311kyyj"></span> <span
            id="sliderValueKr"></span></label>
    <div id="ratingSliderKr" class="slider-kr"></div> <!-- This is where noUiSlider will render the slider -->

    <button id="katorymnd_u998ypa" style="display: none;" class="btn btn-primary mt-3">Submit Rating</button>
    <!-- Securely adding the page details from $pageDetails -->
    <input type="hidden" name="page_id" value="<?php echo esc_attr($pageDetails['id']); ?>">
    <input type="hidden" name="page_slug" value="<?php echo esc_attr($pageDetails['slug']); ?>">
    <input type="hidden" name="page_title" value="<?php echo esc_attr($pageDetails['title']); ?>">

</div>