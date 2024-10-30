jQuery(document).ready(function ($) {
    $('#katorymnd-cleanup-data-btn').on('click', function () {
        if (!confirm('Are you sure you want to clean up all data and deactivate the plugin? This cannot be undone.')) {
            return;
        }

        $.ajax({
            url: katorymnd_ajax_obj.ajax_url,
            type: 'POST',
            data: {
                action: 'katorymnd_cleanup_data',
                nonce: katorymnd_ajax_obj.nonce
            },
            success: function (response) {
                console.log(response); // Log the response to the console

                if (response.success) {
                    kr_alert(response.custom_message, 'success');
                    window.location.href = response.redirect;
                } else {
                    kr_alert('Error: ' + response.custom_message, 'error');
                }
            },
            error: function (xhr, status, error) {
                console.log('AJAX error:', xhr, status, error); // Log AJAX errors to the console
                kr_alert('AJAX error: ' + error, 'error');
            }
        });
    });

    $('#katorymndSettingsForm').submit(function (e) {
        e.preventDefault();

        var formData = {
            'action': 'katorymnd_save_settings',
            'nonce': katorymnd_ajax_obj.nonce,
            'katorymnd_options': {
                'emoji_theme': $('#emojiThemeSelector').val()
            }
        };

        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            var notice = $('#katorymndAdminNotice');
            if (response.success) {
                notice.find('p').text(response.data.message);
                notice.removeClass('notice-error').addClass('notice-success').show();
            } else {
                notice.find('p').text('Error occurred');
                notice.removeClass('notice-success').addClass('notice-error').show();
            }

            // Set a timeout to hide the notice after 10 seconds (10000 milliseconds)
            setTimeout(function () {
                notice.hide();
            }, 10000);

        }).fail(function () {
            console.error('AJAX error');
        });


    });

    $('#katorymndKrEmojiSelectionForm').submit(function (e) {
        e.preventDefault();

        // Collect all checked emoji options
        var selectedEmojis = [];
        $('input[name="katorymnd_options[emoji_selection][]"]:checked').each(function () {
            selectedEmojis.push($(this).val());
        });

        var formData = {
            'action': 'katorymnd_save_settings',
            'nonce': katorymnd_ajax_obj.nonce,
            'katorymnd_options': {
                'emoji_selection': selectedEmojis
            }
        };

        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            var notice = $('#katorymndAdminNotice');
            if (response.success) {
                notice.find('p').text(response.data.message);
                notice.removeClass('notice-error').addClass('notice-success').show();
            } else {
                notice.find('p').text('Error occurred');
                notice.removeClass('notice-success').addClass('notice-error').show();
            }

            // Hide the notice after 10 seconds
            setTimeout(function () {
                notice.hide();
            }, 10000);

        }).fail(function () {
            console.error('AJAX error');
        });
    });

    // Initialize the Spectrum color picker on the header element
    $('#katorymnd_ysz2tcc').spectrum({
        showPalette: true,          // Show a palette of predefined colors
        showAlpha: true,            // Allow alpha transparency selection
        chooseText: "Choose",       // Text for the choose button
        cancelText: "Cancel",       // Text for the cancel button
        preferredFormat: "rgb",     // Preferred color format (hex, rgb, etc.)
        palette: [                  // Define a custom palette
            ['rgb(255, 255, 255)', 'rgb(0, 0, 0)', 'rgb(255, 0, 0)', 'rgb(0, 255, 0)', 'rgb(0, 0, 255)'],
            ['rgb(255, 255, 0)', 'rgb(255, 0, 255)', 'rgb(0, 255, 255)', 'rgb(128, 0, 0)', 'rgb(128, 128, 128)']
        ],
        change: function (color) {
            // Update the background color of the header element
            var newColor = color.toRgbString();
            $('#katorymnd_ysz2tcc').css('background-color', newColor);

            // Prepare the data to send to the server
            var formData = {
                'action': 'katorymnd_save_settings',
                'nonce': katorymnd_ajax_obj.nonce,
                'katorymnd_options': {
                    'header_color': newColor
                }

            };

            // Send the new color to the server using AJAX
            $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
                //console.log('Color updated: ', response);
            }).fail(function () {
                console.error('AJAX error while updating color');
            });
        }
    });

    // Reference to the header element
    var headerElement = $('#katorymnd_ysz2tcc');

    if (headerElement.length) {
        headerElement.click(function () {
            // Trigger the color picker when the header is clicked
            $('#katorymnd_ysz2tcc').spectrum('show');
        });
    }

    $('#katorymnd_security_settings').submit(function (e) {
        e.preventDefault();

        // Prepare formData object
        var formData = {
            'action': 'katorymnd_save_settings', // The WordPress AJAX action hook
            'nonce': katorymnd_ajax_obj.nonce, // Security nonce
            'filter_settings': {
                'filter_url': $('input[name="filter_url"]').is(':checked') ? '1' : '0',
                'filter_email': $('input[name="filter_email"]').is(':checked') ? '1' : '0',
                'filter_anchor': $('input[name="filter_anchor"]').is(':checked') ? '1' : '0',
                'filter_phone': $('input[name="filter_phone"]').is(':checked') ? '1' : '0',
                'filter_spam_keywords': $('input[name="filter_spam_keywords"]').is(':checked') ? '1' : '0',
                'filter_spam_patterns': $('input[name="filter_spam_patterns"]').is(':checked') ? '1' : '0',
            },
            'akismet_api_key': $('#akismet_api_key').val() // This will be an empty string if Akismet is not active
        };

        // AJAX POST request to the WordPress AJAX handler
        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            var notice = $('#katorymndAdminNotice');
            if (response.success) {
                notice.find('p').text(response.data.message);
                notice.removeClass('notice-error').addClass('notice-success').show();
            } else {
                notice.find('p').text(response.data.message || 'Error occurred');
                notice.removeClass('notice-success').addClass('notice-error').show();
            }

            // Optional: hide the notice after a delay
            setTimeout(function () {
                notice.hide();
            }, 10000);
        }).fail(function () {
            console.error('AJAX error');
        });
    });


    $('#katorymnd_27d8alg').click(function (e) {
        e.preventDefault();

        // Validate checkbox
        if (!$('#kr-deactivate-demo').is(':checked')) {
            // Display error message if checkbox is not checked
            var notice = $('#katorymndAdminNotice');
            notice.find('p').text('You must check the box to deactivate all demo sequences.'); // Error message
            notice.removeClass('notice-success').addClass('notice-error').show();

            // Optional: hide the notice after a delay
            setTimeout(function () {
                notice.hide();
            }, 5000); // Adjusted to 5 seconds for better user experience

            return; // Stop the function from proceeding further
        }

        // Prepare formData object if checkbox is checked
        var formData = {
            'action': 'katorymnd_7bnohza_deactivate_demo', // The WordPress AJAX action hook tailored for this form
            'nonce': katorymnd_ajax_obj.nonce, // Security nonce
            'deactivate_demo': '1', // Checkbox is checked, send '1'
        };

        // AJAX POST request to the WordPress AJAX handler
        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            var notice = $('#katorymndAdminNotice'); // Element to display notices to the admin
            if (response.success) {
                notice.find('p').text(response.data.message); // Display success message
                notice.removeClass('notice-error').addClass('notice-success').show();

                // Hide the notice after a delay, then reload the page
                setTimeout(function () {
                    notice.hide();
                    window.location.reload(); // Reload the page
                }, 5000); // Adjusted to 5 seconds for better user experience
            } else {
                notice.find('p').text(response.data.message || 'An error occurred');
                notice.removeClass('notice-success').addClass('notice-error').show();

                // Optional: hide the notice after a delay
                setTimeout(function () {
                    notice.hide();
                }, 5000); // Adjusted to 5 seconds for better user experience
            }
        }).fail(function () {
            console.error('AJAX error'); // Log AJAX errors to console for debugging
        });
    });




    $('#kr_saveCustomTableName').click(function (e) {
        e.preventDefault();

        var formData = {
            'action': 'katorymnd_save_settings',
            'nonce': katorymnd_ajax_obj.nonce,
            'katorymnd_options': {
                'custom_table_name': $('#kr-customTableName').val().trim(),
                'user_id_column_name': $('#kr-userIdColumnName').val().trim(),
                'username_column_name': $('#kr-usernameColumnName').val().trim(),
                'email_column_name': $('#kr-emailColumnName').val().trim(),
                'full_names_column_name': $('#kr-fullNamesColumnName').val().trim(),
                'avatar_column_name': $('#kr-avatarColumnName').val().trim()
            }
        };


        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            var notice = $('#katorymndAdminNotice');
            if (response.success) {
                notice.find('p').text(response.data.message);
                notice.removeClass('notice-error').addClass('notice-success').show();
            } else {
                notice.find('p').text(response.data.message || 'Error occurred');
                notice.removeClass('notice-success').addClass('notice-error').show();
            }

            // Set a timeout to hide the notice after 10 seconds (10000 milliseconds)
            setTimeout(function () {
                notice.hide();
            }, 10000);

        }).fail(function () {
            console.error('AJAX error');
        });
    });



    // Function to toggle slider appearance
    function toggleSlider_kr() {
        var Krcheckbox = $('#wpkr_demo_enabled');
        var Krslider = $('.wpkr_slider');
        var KrlabelOff = $('.wpkr_slider-label-off');
        var KrlabelOn = $('.wpkr_slider-label-on');

        // Invert the checkbox's state
        Krcheckbox.prop('checked', !Krcheckbox.prop('checked'));

        updateSliderUIKr(Krcheckbox, Krslider, KrlabelOff, KrlabelOn);
    }

    // Update the UI of the slider based on the checkbox state
    function updateSliderUIKr(checkbox_kr, slider_kr, labelOff_kr, labelOn_kr) {
        if (checkbox_kr.prop('checked')) {
            slider_kr.css('transform', 'translateX(60px)');
            slider_kr.parent().css('backgroundColor', '#4CAF50');
            labelOff_kr.css('opacity', 0);
            labelOn_kr.css('opacity', 1);
        } else {
            slider_kr.css('transform', 'translateX(0)');
            slider_kr.parent().css('backgroundColor', '#ccc');
            labelOff_kr.css('opacity', 1);
            labelOn_kr.css('opacity', 0);
        }
    }

    var katorymnd_a1m78ry = $('#wpkr_demo_enabled');
    var katorymnd_pa6kd4o = $('.wpkr_slider');
    var katorymnd_1pimdho = $('.wpkr_slider-label-off');
    var katorymnd_1ddvzjy = $('.wpkr_slider-label-on');

    // Initialize slider based on the current state of the checkbox
    updateSliderUIKr(katorymnd_a1m78ry, katorymnd_pa6kd4o, katorymnd_1pimdho, katorymnd_1ddvzjy);

    // Attach the event handler for toggle functionality
    katorymnd_pa6kd4o.click(toggleSlider_kr);


    // AJAX submission for the settings form
    $('#katorymnd_cmjan8b').click(function (e) {
        e.preventDefault();

        var formData = {
            'action': 'katorymnd_save_settings',
            'nonce': katorymnd_ajax_obj.nonce,
            'katorymnd_options': {
                'demo_enabled': $('#wpkr_demo_enabled').is(':checked') ? 1 : 0,
                'default_num_comments': $('#wpkr_default_num_comments').val().trim(),
                'num_comments': $('#wpkr_num_comments').val().trim()
            }
        };

        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            var notice = $('#katorymndAdminNotice');
            if (response.success) {
                notice.find('p').text(response.data.message);
                notice.removeClass('notice-error').addClass('notice-success').show();
            } else {
                notice.find('p').text(response.data.message || 'An error occurred');
                notice.removeClass('notice-success').addClass('notice-error').show();
            }

            setTimeout(function () {
                notice.hide();
            }, 10000);
        }).fail(function () {
            alert('AJAX error');
        });
    });


    $('#katorymnd_pn1fmwn').click(function () {
        var $form = $(this).closest('form');
        var rolesData = {};

        $form.find('input[type=checkbox]').each(function () {
            // Adjust the key to match PHP expected format
            var roleName = $(this).attr('name').match(/\[(.*?)\]/)[1];
            rolesData[roleName] = $(this).is(':checked') ? '1' : '0';
        });

        var formData = {
            'action': 'kr_admin_role_settings',
            'nonce': katorymnd_ajax_obj.nonce,
            'wpkr_role_settings': rolesData
        };

        $.post(katorymnd_ajax_obj.ajax_url, formData, function (response) {
            if (response.success) {
                // Use the custom alert for successful update
                kr_alert(response.data && response.data.message ? response.data.message : 'Role settings updated successfully.', 'success');
            } else {
                // Use the custom alert for failure to update
                kr_alert(response.data && response.data.message ? response.data.message : 'Failed to update role settings.', 'error');
            }
        }).fail(function () {
            kr_alert('AJAX error - Could not connect to server.', 'error');
        });
    });


    function initializeTooltipsForEmojiLabels() {
        const emojiLabels = document.querySelectorAll('.wpkr-emoji-selection [data-bs-toggle="tooltip"]');
        emojiLabels.forEach(function (emojiLabel) {
            new bootstrap.Tooltip(emojiLabel);
        });
    }


    initializeTooltipsForEmojiLabels();

    $(document).on('click', '#katorymnd_6mns7wd, #katorymnd_0lge2yc .qhol', function (e) {// Updated selector
        e.preventDefault();
        var reportId = $(this).data('report-id');

        // Updated formData to align with your security standards and action naming
        var formData = {
            'action': 'katorymnd_fetch_report_details', // Assuming this is the correct action for fetching report details
            'nonce': katorymnd_ajax_obj.nonce, // Use nonce from katorymnd_ajax_obj
            'report_id': reportId // Sending the report ID to fetch its details
        };

        $.ajax({
            url: katorymnd_ajax_obj.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: formData,
            success: function (response) {
                if (response.success) {
                    // Extracting all pieces of information from the response
                    var reportDetails = response.data.details; // The full report details
                    var commentContent = response.data.comment_content; // The actual comment content
                    var reportedBy = response.data.reported_by; // The username of the reporter
                    var reason = response.data.reason; // The reason for the report
                    var reportStatus = response.data.status; // The report status
                    var reportCommnetID = response.data.comment_id; // The current comment id the report is for
                    var userStatus = response.data.user_status; // The status of the user who made the comment

                    // Determine the button text based on the user status
                    var buttonText = userStatus === 'blocked' ? 'Unblock User' : 'Block User';
                    console.log(userStatus)
                    // Update the modal content to include all the new pieces of information
                    var modalContent = `
                                <div>
                                    <p><strong>Comment Content:</strong>
                                        <div id="kr_editableCommentContent" contenteditable="true" style="border: 1px solid #ccc; padding: 5px; margin-top: 5px;">
                                            ${commentContent}
                                        </div>
                                    </p>
                                    <p><strong>Report:</strong> ${reportDetails}</p>
                                    <p><strong>Reported By:</strong> ${reportedBy}
                                        <button id="toggleUserStatus" class="button" data-user-id="${reportedBy}">${buttonText}</button>
                                    </p>
                                    <p><strong>Reason:</strong> ${reason}</p>
                                    <p><strong>Current Status:</strong> 
                                        <select id="kr_reportStatusUpdate">
                                            <option value="open" ${reportStatus === 'open' ? 'selected' : ''}>Open</option>
                                            <option value="reviewing" ${reportStatus === 'reviewing' ? 'selected' : ''}>Reviewing</option>
                                            <option value="closed" ${reportStatus === 'closed' ? 'selected' : ''}>Closed</option>
                                        </select>
                                    </p>
                                </div>`;
                    $('#kr-report-details-dialog').html(modalContent).dialog({
                        title: 'Report Details',
                        modal: true,
                        width: 'auto',
                        buttons: {
                            Save: function () {
                                var updatedCommentContent = $('#kr_editableCommentContent').text();
                                var updatedReportStatus = $('#kr_reportStatusUpdate').val();

                                // AJAX call to save the updated content and report status
                                $.ajax({
                                    url: katorymnd_ajax_obj.ajax_url,
                                    type: 'POST',
                                    dataType: 'json',
                                    data: {
                                        action: 'katorymnd_kr_update_report_status',
                                        report_id: reportId, // Ensure this variable is defined with the report's ID
                                        status: updatedReportStatus,
                                        content: updatedCommentContent,
                                        reportedCommentID: reportCommnetID,
                                        nonce: katorymnd_ajax_obj.nonce,
                                    },
                                    success: function (updateResponse) {
                                        if (updateResponse.success) {
                                            // Use the custom alert for successful update
                                            kr_alert(updateResponse.data && updateResponse.data.message ? updateResponse.data.message : 'Report and comment updated successfully.', 'success');
                                        } else {
                                            // Use the custom alert for failure to update
                                            kr_alert(updateResponse.data && updateResponse.data.message ? updateResponse.data.message : 'Failed to update report and comment.', 'error');
                                        }
                                    }
                                });
                            },
                            Close: function () {
                                $(this).dialog('close');
                            }
                        }
                    });

                    // Optionally, log the full response data to the console for debugging
                    console.log('Report Details:', response.data);
                } else {
                    // Handle failure to fetch report details
                    kr_alert('Failed to fetch report details. Please try again.', 'error');

                }


            }
        }).fail(function () {
            // Handle AJAX errors
            kr_alert('AJAX error', 'error');
        });

    });

    $(document).on('click', '#toggleUserStatus', function () { // Ensure dynamic binding for dynamically created content
        var $this = $(this);
        var action = $this.text().trim() === 'Block User' ? 'block_user' : 'unblock_user';
        var userId = $this.data('user-id'); // Assuming you have the user ID stored in a data attribute

        $.ajax({
            url: katorymnd_ajax_obj.ajax_url,
            method: 'POST',
            dataType: 'json',
            data: {
                action: action, // Use the dynamic action based on the button's text
                nonce: katorymnd_ajax_obj.nonce, // Pass the nonce for security
                user_id: userId // Pass the user ID to the server
            },
            success: function (response) {
                if (response.success) {
                    kr_alert(response.data.message, 'success'); // Show success message
                    // Toggle the button text based on the current action
                    $this.text(action === 'block_user' ? 'Unblock User' : 'Block User');
                } else {
                    // Handle error
                    kr_alert('Failed to update user status. Please try again.', 'error');
                }
            },
            error: function () {
                // Handle AJAX error
                kr_alert('AJAX error occurred.', 'error');
            }
        });
    });



    if (document.getElementsByClassName('kr-pagination')) {
        // Event delegation for pagination links using the document
        $(document).on('click', '.kr-pagination a', function (e) {
            e.preventDefault(); // Prevent the default anchor click behavior

            var page = $(this).data('page'); // Extract the page number from the link
            if (page) {
                kr_fetchAbuseReports(page); // Fetch the reports for the clicked page
            }
        });


        function kr_fetchAbuseReports(page = 1) {
            $.ajax({
                url: katorymnd_ajax_obj.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'kr_get_abuse_reports',
                    page: page,
                    nonce: katorymnd_ajax_obj.nonce
                },
                success: function (response) {
                    if (response.success) {
                        // console.log('Report Details:', response.data);
                        // Call the function to populate the data into the DOM
                        populateReportsTable(response.data.content);
                    } else {
                        console.error('Failed to fetch report details. Please try again.');
                    }
                }
            }).fail(function (error) {
                console.error('AJAX error', error);
            });
        }

        // Initial fetch of abuse reports for the admin section
        kr_fetchAbuseReports();

        function populateReportsTable(contentHtml) {
            // Get the container where the reports table will be displayed
            var tableContainer = document.getElementById('abuse-reports-table-container');

            // Check if the container exists before attempting to modify it
            if (tableContainer) {
                // Set the inner HTML of the container to the content received from AJAX
                tableContainer.innerHTML = contentHtml;
            }
        }



    }

    if (document.getElementById('krFilterReports')) {
        function Kr_filterReports() {
            const selectedStatus = document.getElementById('kr-report-status-filter').value;

            // AJAX call to fetch reports based on the selected filter
            $.ajax({
                url: katorymnd_ajax_obj.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'kr_get_abuse_reports_filter',
                    nonce: katorymnd_ajax_obj.nonce,
                    selected_status: selectedStatus,
                },
                success: function (response) {
                    if (response.success) {
                        // Handle the successful response here
                        //console.log('Filter applied:', response.data);
                        kr_fetchAbuseReports();// reload to get the  results
                    } else {
                        // Handle the failure here
                        alert(response.data.message || 'An error occurred');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.log('AJAX error:', textStatus, 'Error:', errorThrown);

                }
            });
        }


        document.getElementById('krFilterReports').addEventListener('click', Kr_filterReports);

    }
});

if (document.getElementById('kr_reportStatusChart')) {
    document.addEventListener('DOMContentLoaded', function () {
        var statusCtx = document.getElementById('kr_reportStatusChart').getContext('2d');

        // Transforming the structured data for Chart.js
        var transformedData = transformStatusChartData(statusChartData);

        var reportStatusChart = new Chart(statusCtx, {
            type: 'bar', // Changed to a bar chart
            data: transformedData,
            options: {
                scales: {
                    x: {
                        stacked: true, // Stacking reasons within each status
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true,
                    }
                },
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true, // Adjust based on your preference
                        position: 'top',
                    },
                    title: {
                        display: true,
                        text: 'Report Status and Reason Distribution'
                    }
                }
            }
        });
    });

    // Function to transform the data into a format suitable for Chart.js
    function transformStatusChartData(statusChartData) {
        var datasets = [];
        var backgroundColors = ['rgba(255, 99, 132, 0.6)', 'rgba(54, 162, 235, 0.6)', 'rgba(255, 206, 86, 0.6)']; // Example colors
        var borderColor = ['rgba(255,99,132,1)', 'rgba(54, 162, 235, 1)', 'rgba(255, 206, 86, 1)']; // Example border colors
        var statusLabels = statusChartData.labels; // Status labels as x-axis categories
        var allReasons = {}; // Object to hold all reasons across statuses for unified x-axis labels

        // First, collect all reasons across all statuses to ensure a unified x-axis
        Object.keys(statusChartData.data).forEach(function (status) {
            var reasons = statusChartData.data[status];
            reasons.forEach(function (reason) {
                if (!allReasons.hasOwnProperty(reason.reason)) {
                    allReasons[reason.reason] = true;
                }
            });
        });

        // Convert reasons object keys into an array for labels
        var reasonLabels = Object.keys(allReasons);

        // Generate datasets
        statusLabels.forEach(function (status, index) {
            var data = [];
            reasonLabels.forEach(function (reasonLabel) {
                var reasonData = statusChartData.data[status].find(function (reason) {
                    return reason.reason === reasonLabel;
                });
                data.push(reasonData ? reasonData.count : 0);
            });

            datasets.push({
                label: status,
                data: data,
                backgroundColor: backgroundColors[index % backgroundColors.length],
                borderColor: borderColor[index % borderColor.length],
                borderWidth: 1
            });
        });

        return {
            labels: reasonLabels,
            datasets: datasets
        };
    }


    document.addEventListener('DOMContentLoaded', function () {

        // Extracting data from combinedData
        var labels = combinedData.monthly_trends.map(obj => obj.month);

        // Extract unique years and months
        var uniqueYears = [...new Set(labels.map(month => month.split('-')[0]))];
        var uniqueMonths = [...new Set(labels.map(month => month.split('-')[1]))];

        // Sort uniqueMonths in ascending order
        uniqueMonths.sort((a, b) => parseInt(a) - parseInt(b));

        // Populate the Year dropdown
        var yearFilter = document.getElementById('yearFilter');
        uniqueYears.forEach(year => {
            var option = document.createElement('option');
            option.value = year;
            option.textContent = year;
            yearFilter.appendChild(option);
        });

        // Populate the Month dropdown
        var monthFilter = document.getElementById('monthFilter');
        var monthsNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        uniqueMonths.forEach(month => {
            var option = document.createElement('option');
            option.value = month;
            option.textContent = monthsNames[parseInt(month) - 1]; // Months are 1-indexed
            monthFilter.appendChild(option);
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        var combinedCtx = document.getElementById('kr_combinedChart').getContext('2d');
        var originalData = JSON.parse(JSON.stringify(combinedData)); // Deep copy to retain original data
        var currentYear = 'all'; // Default year filter
        var currentMonth = 'all'; // Default month filter
        var combinedChart;

        // Function to generate line datasets from the filtered data
        function generateLineDatasets(filteredData) {
            var lineDatasets = [];
            var reasonColors = {}; // Keep track of colors for consistency

            Object.entries(filteredData.reason_counts_by_month).forEach(([month, reasons]) => {
                Object.entries(reasons).forEach(([reason, count]) => {
                    if (!reasonColors[reason]) {
                        // Assign a unique color to each reason
                        reasonColors[reason] = 'rgba(' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ',' + Math.floor(Math.random() * 256) + ', 1)';
                    }
                    if (!lineDatasets.some(ds => ds.label === reason)) {
                        // Initialize dataset for new reason
                        lineDatasets.push({
                            label: reason,
                            data: Array(filteredData.monthly_trends.length).fill(null), // Initialize with null for gaps
                            borderColor: reasonColors[reason],
                            borderWidth: 2, // Default thickness
                            fill: false,
                        });
                    }
                    var index = filteredData.monthly_trends.findIndex(trend => trend.month === month);
                    if (index !== -1) {
                        lineDatasets.find(ds => ds.label === reason).data[index] = count; // Assign count
                    }
                });
            });

            return lineDatasets;
        }

        // Function to filter data based on the selected year and month
        function filterData(year, month) {
            var filteredData = JSON.parse(JSON.stringify(originalData)); // Deep copy to start with original data

            if (year !== 'all') {
                filteredData.monthly_trends = filteredData.monthly_trends.filter(item => item.month.startsWith(year));
                Object.keys(filteredData.reason_counts_by_month).forEach(key => {
                    if (!key.startsWith(year)) {
                        delete filteredData.reason_counts_by_month[key];
                    }
                });
            }

            if (month !== 'all') {
                filteredData.monthly_trends = filteredData.monthly_trends.filter(item => item.month.endsWith(month));
                Object.keys(filteredData.reason_counts_by_month).forEach(key => {
                    if (!key.endsWith(month)) {
                        delete filteredData.reason_counts_by_month[key];
                    }
                });
            }

            return filteredData;
        }

        // Function to update the chart
        function updateChart(year, month) {
            var filteredData = filterData(year, month);
            var labels = filteredData.monthly_trends.map(obj => obj.month);
            var lineDatasets = generateLineDatasets(filteredData);

            var totalReportsDataset = {
                label: 'Total Monthly Reports',
                data: filteredData.monthly_trends.map(obj => obj.count),
                borderColor: '#FF6384',
                borderWidth: 5, // Make this line thicker to stand out
                fill: false,
            };

            // Check if a chart instance already exists, and if it is indeed a chart instance
            if (combinedChart && typeof combinedChart.destroy === 'function') {
                combinedChart.destroy(); // Destroy the old chart instance before creating a new one
            }

            // Create a new chart instance
            combinedChart = new Chart(combinedCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [...lineDatasets, totalReportsDataset] // Include all reason datasets and total reports
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: { // Single y-axis for all lines
                            beginAtZero: true,
                        }
                    },
                    plugins: {
                        legend: { display: true },
                        title: {
                            display: true,
                            text: 'Monthly Report Trends by Reason'
                        }
                    },
                    elements: {
                        line: {
                            tension: 0.4 // Optional: to have a slight curve in the lines
                        },
                        point: {
                            radius: 3 // Optional: to display points on the line
                        }
                    }
                }
            });
        }


        // Event listeners for filter controls
        document.getElementById('yearFilter').addEventListener('change', function (event) {
            currentYear = event.target.value;
            updateChart(currentYear, currentMonth);
        });

        document.getElementById('monthFilter').addEventListener('change', function (event) {
            currentMonth = event.target.value;
            updateChart(currentYear, currentMonth);
        });

        // Initialize chart with all data
        updateChart(currentYear, currentMonth);
    });

}

if (document.getElementById('katorymnd_3lcx36a')) {
    document.addEventListener('DOMContentLoaded', function () {
        var submitButton = document.getElementById('katorymnd_3lcx36a');

        submitButton.addEventListener('click', function (e) {
            e.preventDefault();

            var selectedRatingType = document.querySelector('input[name="katorymnd_rating_type"]:checked').value;
            var formData = new FormData();
            formData.append('action', 'katorymnd_save_settings');
            formData.append('nonce', katorymnd_ajax_obj.nonce);
            formData.append('katorymnd_options[rating_type]', selectedRatingType);

            fetch(katorymnd_ajax_obj.ajax_url, {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
                .then(response => response.json())
                .then(data => {
                    var notice = document.getElementById('katorymndAdminNotice');
                    var p = notice.querySelector('p') || notice.appendChild(document.createElement('p'));

                    if (data.success) {
                        p.textContent = data.data.message;
                        notice.classList.remove('notice-error');
                        notice.classList.add('notice-success');
                        notice.style.display = 'block';
                    } else {
                        p.textContent = data.data.message || 'Error occurred';
                        notice.classList.remove('notice-success');
                        notice.classList.add('notice-error');
                        notice.style.display = 'block';
                    }

                    setTimeout(function () {
                        notice.style.display = 'none';
                    }, 10000);
                })
                .catch(error => {
                    console.error('AJAX error', error);
                });
        });
    });
}

if (document.getElementById('krActionButtonsContainer')) {
    let krQuestionCount = 0;

    function krAddQuestion(type) {
        krQuestionCount++;
        const krSurveyForm = document.getElementById('krSurveyPollForm');

        // Generate a unique identifier for this question
        const krDataAttribute = 'kr-' + Date.now() + '-' + Math.floor(Math.random() * 1000);

        let krQuestionHtml = `<div class="mb-3 kr-question-container" data-kr-question-type="${type}" kr-data="${krDataAttribute}">
<div class="kr-delete-question" onclick="krDeleteQuestion(this)"><i class="fas fa-trash"></i></div>
<label class="form-label">${type.charAt(0).toUpperCase() + type.slice(1)} Question:</label>
<input type="text" class="form-control mb-2 kr-question-text" placeholder="Question text" kr-data="${krDataAttribute}">`;

        if (type === 'likert') {
            krQuestionHtml += krGenerateLikertScale(krDataAttribute);
        } else if (type !== 'text') {
            krQuestionHtml += `<div class="kr-option-container" kr-data="${krDataAttribute}">
    ${krGenerateOptionInput(krDataAttribute)}
    ${krGenerateOptionInput(krDataAttribute)}
</div>
<button type="button" class="btn btn-secondary btn-sm kr-add-option" onclick="krAddOption(this, '${type}', '${krDataAttribute}')" kr-data="${krDataAttribute}">Add Option</button>`;
        }

        krQuestionHtml += `</div>`;
        const newQuestion = document.createElement('div');
        newQuestion.innerHTML = krQuestionHtml;
        krSurveyForm.appendChild(newQuestion.firstChild);
        kr_initializeSortable();
    }

    function kr_initializeSortable() {
        new Sortable(krSurveyPollForm, {
            animation: 150,
            draggable: '.kr-question-container',
        });

        document.querySelectorAll('.kr-option-container, .kr-likert-container').forEach(container => {
            new Sortable(container, {
                animation: 150,
                draggable: '.input-group',
            });
        });

        // Attempt to make action buttons sortable
        const kr_actionButtonsContainer = document.getElementById('krActionButtonsContainer');
        if (kr_actionButtonsContainer) {
            new Sortable(kr_actionButtonsContainer, {
                animation: 150,
                draggable: 'button',
            });
        } else {
            console.error('Action buttons container not found.');
        }
    }


    function krAddOption(button, type, krDataAttribute) {
        if (type !== 'likert') {
            const krOptionContainer = button.previousElementSibling;
            krOptionContainer.insertAdjacentHTML('beforeend', krGenerateOptionInput(krDataAttribute));
        }
    }

    function krGenerateOptionInput(krDataAttribute) {
        return `<div class="input-group mb-3" kr-data="${krDataAttribute}">
<input type="text" class="form-control kr-option-input" placeholder="Option" kr-data="${krDataAttribute}">
<button class="btn btn-outline-secondary kr-delete-option" type="button" onclick="krDeleteOption(this)" kr-data="${krDataAttribute}">
<i class="fas fa-trash"></i>
</button>
</div>`;
    }

    function krGenerateLikertScale(krDataAttribute) {
        let krLikertHtml = `<div class="kr-likert-container" kr-data="${krDataAttribute}">`;
        for (let i = 1; i <= 5; i++) {
            krLikertHtml += `<div class="input-group mb-2" kr-data="${krDataAttribute}">
<input type="text" class="form-control kr-option-input" value="${i} - Option" kr-data="${krDataAttribute}">
<button class="btn btn-outline-secondary kr-delete-option" type="button" onclick="krDeleteOption(this)" kr-data="${krDataAttribute}">
    <i class="fas fa-trash"></i>
</button>
</div>`;
        }
        krLikertHtml += `</div>`;
        return krLikertHtml;
    }

    function krDeleteOption(element) {
        element.closest('.input-group').remove();
    }

    function krDeleteQuestion(element) {
        element.closest('.kr-question-container').remove();
    }

    document.getElementById('krPreviewSurveyPoll').addEventListener('click', function () {
        // Function to generate a random string of a given length
        function krGenerateRandomString(krLength) {
            const krCharacters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            const krRandomValues = window.crypto.getRandomValues(new Uint32Array(krLength));
            let krResult = '';
            krRandomValues.forEach(krVal => {
                krResult += krCharacters.charAt(krVal % krCharacters.length);
            });
            return krResult;
        }

        // Function to generate a unique string by appending a random suffix to a constant prefix
        function krGenerateUniqueString() {
            const krConstantPrefix = 'kr_katorymnd_';
            const krRandomSuffix = krGenerateRandomString(8); // Adjust the length of the random suffix as needed
            return `${krConstantPrefix}${krRandomSuffix}`;
        }

        // Generate a unique ID for the container div
        const krUniqueId = krGenerateUniqueString();

        let krPreviewHtml = `<div class="container" id="${krUniqueId}"><form>`;

        const krQuestionContainers = document.querySelectorAll('.kr-question-container');
        krQuestionContainers.forEach((container, index) => {
            const krQuestionType = container.getAttribute('data-kr-question-type');
            const krQuestionText = container.querySelector('.kr-question-text').value;
            const krDataAttribute = container.getAttribute('kr-data'); // Retrieve the unique identifier

            // Bootstrap's card component for each question
            krPreviewHtml += `<div class="card mb-3" kr-data="${krDataAttribute}"><div class="card-body">`;
            krPreviewHtml += `<h5 class="card-title">${krQuestionText}</h5>`;

            if (krQuestionType === 'text') {
                krPreviewHtml += `<textarea class="form-control" placeholder="Your answer here..." rows="3" kr-data="${krDataAttribute}"></textarea>`;
            } else if (krQuestionType === 'likert') {
                const krLikertOptions = container.querySelectorAll('.kr-option-input');
                krPreviewHtml += `<div class="d-flex flex-column" kr-data="${krDataAttribute}">`;
                krLikertOptions.forEach((option, optionIndex) => {
                    const krOptionValue = option.value;
                    krPreviewHtml += `<div class="form-check" style="display: flex; align-items: center;" kr-data="${krDataAttribute}">
                                  <input class="form-check-input" type="radio" name="kr-likert-${index}" id="kr-likert-${index}-${optionIndex}" value="${optionIndex + 1}" kr-data="${krDataAttribute}">
                                  <label class="form-check-label" for="kr-likert-${index}-${optionIndex}" kr-data="${krDataAttribute}">${krOptionValue}</label>
                              </div>`;
                });
                krPreviewHtml += `</div>`;
            } else {
                const krOptions = container.querySelectorAll('.kr-option-input');
                if (krQuestionType === 'dropdown') {
                    krPreviewHtml += `<select class="form-control mb-2" kr-data="${krDataAttribute}">`;
                    krPreviewHtml += `<option value="Choose">Choose...</option>`; // Default option
                }
                krOptions.forEach((option, optionIndex) => {
                    switch (krQuestionType) {
                        case 'radio':
                        case 'checkbox':
                            krPreviewHtml += `<div class="form-check" style="display: flex; align-items: center;" kr-data="${krDataAttribute}"><input class="form-check-input" type="${krQuestionType}" name="kr-question${index}" id="kr-question-${index}-option-${optionIndex}" value="${option.value}" kr-data="${krDataAttribute}">
                                          <label class="form-check-label" for="kr-question-${index}-option-${optionIndex}" kr-data="${krDataAttribute}">${option.value}</label></div>`;
                            break;
                        case 'dropdown':
                            krPreviewHtml += `<option value="${option.value}" kr-data="${krDataAttribute}">${option.value}</option>`;
                            break;
                    }
                });
                if (krQuestionType === 'dropdown') {
                    krPreviewHtml += `</select>`;
                }
            }
            krPreviewHtml += `</div></div>`; // Close card-body and card
        });

        krPreviewHtml += '</form></div>'; // Close container
        krDisplayPreview(krPreviewHtml);
    });


    function krDisplayPreview(previewHtml) {
        // Use $.ajax for the request
        jQuery.ajax({
            url: katorymnd_ajax_obj.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'kr_store_survey_preview', // Updated action
                nonce: katorymnd_ajax_obj.nonce,
                preview_html: previewHtml // The HTML content or data to store
            },
            success: function (response) {
                if (response.success) {
                    // Redirect to the preview page if success
                    window.location.href = 'admin.php?page=kr-preview-survey-poll-page';
                } else {
                    // Handle the failure here
                    alert(response.data.message || 'An error occurred while saving the preview.');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Log or handle the error
                console.error('AJAX error:', textStatus, 'Error:', errorThrown);
            }
        });
    }



    document.addEventListener('DOMContentLoaded', function () {
        kr_initializeSortable();

        const formChecks = document.querySelectorAll('.form-check');

        formChecks.forEach(function (formCheck, index) {
            // Find the form-check-input and form-check-label within the current .form-check
            const input = formCheck.querySelector('.form-check-input');
            const label = formCheck.querySelector('.form-check-label');

            // Get the height of the input
            const inputHeight = input.offsetHeight;
            // Adjust the label's line height to match input height for vertical centering
            label.style.lineHeight = inputHeight + 'px';
        });
    });
}


document.addEventListener('DOMContentLoaded', function () {
    var submitButton = document.getElementById('katorymnd_oh0kdv4');
    if (submitButton) {
        submitButton.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent the default form submission

            var surveyTitleInput = document.querySelector('input[name="kr_survey_title"]');
            var surveyTitle = surveyTitleInput.value.trim().replace(/\s+/g, ' '); // Trim and replace multiple spaces with a single space
            surveyTitleInput.value = surveyTitle; // Optionally update the input field to reflect the cleaned title

            var permalinkBase = document.querySelector('select[name="kr_survey_permalink_base"]').value;

            // Validate surveyTitle is not empty or just whitespace
            if (!surveyTitle) {
                kr_alert('Please provide a title for your survey or poll.', 'error');
                return; // Stop the function if validation fails
            }

            // Validate permalinkBase is not empty
            if (permalinkBase === '') {
                kr_alert('Please choose a permalink base.', 'error');
                return; // Stop the function if validation fails
            }

            // Proceed with the AJAX call if validation passes
            jQuery.ajax({
                url: katorymnd_ajax_obj.ajax_url,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'kr_save_surveryPoll', // Updated WordPress AJAX action
                    nonce: katorymnd_ajax_obj.nonce, // The nonce for security
                    kr_survey_title: surveyTitle, // The survey or poll title
                    kr_survey_permalink_base: permalinkBase // The selected permalink base
                },
                success: function (response) {
                    // Logic to process response more effectively
                    if (response.success) {
                        console.log(response.data.message);
                        kr_alert(response.data.message || 'Your details have been saved successfully!', 'success');

                        setTimeout(function () {
                            window.location.href = 'admin.php?page=katorymnd-reaction-settings';
                        }, 5000); // 5000 milliseconds = 5 seconds
                    } else {
                        // More detailed error handling
                        var errorMessage = response.data && response.data.message ? response.data.message : 'Failed to save the details.';
                        kr_alert(errorMessage, 'error'); // Show failure message with more specific error if available
                    }
                },
                error: function (xhr, status, error) {
                    // Handle HTTP errors or other AJAX errors
                    var errorMessage = "Error: " + error;
                    kr_alert(errorMessage, 'error'); // Show AJAX error message
                    console.error("AJAX error: ", status, error);
                }
            });
        });
    }
});

