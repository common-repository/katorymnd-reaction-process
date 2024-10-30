// Define the base URL for REST API requests
let apiUrl = wpApiSettings.rest_url + 'katorymnd/v1/';

function kr_fetchUserData() {

    fetch(apiUrl + 'fetch_user_data/', {
        method: 'GET',
        headers: {
            'X-WP-Nonce': wpApiSettings.nonce
        }
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            console.log('Success:', data);

            // Store the fetched user data in session storage
            sessionStorage.setItem('KrUserData', JSON.stringify(data));
        })
        .catch(error => {
            console.error('Error:', error);
        });
}

// Trigger the fetch operation after the page has loaded
document.addEventListener('DOMContentLoaded', kr_fetchUserData);

if (document.getElementById('kr-commentsChart')) {
    let kr_reportStatusChart;

    function kr_updateChartData(data) {
        let kaggregatedData = {};

        data.comments.forEach(comment => {
            const commentsCount = parseInt(data.total_comments_by_page[comment.page_id].total_comments, 10);
            const repliesCount = data.total_replies_by_page[comment.page_id] || 0;

            if (!kaggregatedData[comment.page_slug]) {
                kaggregatedData[comment.page_slug] = {
                    pageId: comment.page_id,
                    pageTitle: comment.page_title,
                    comments: commentsCount + repliesCount
                };
            }
        });

        const commentsDetails = Object.values(kaggregatedData);
        console.log("Aggregated Comments Details:", commentsDetails);

        updateChart_kr(commentsDetails);
    }

    function updateChart_kr(commentsDetails) {
        const statusCtx = document.getElementById('kr-commentsChart').getContext('2d');

        // Generate dynamic colors for each bar
        const backgroundColors = commentsDetails.map(() => `rgba(${kr_getRandomInt(0, 255)}, ${kr_getRandomInt(0, 255)}, ${kr_getRandomInt(0, 255)}, 0.5)`);
        const borderColors = backgroundColors.map(color => color.replace('0.5', '1'));

        const transformedData = {
            labels: commentsDetails.map(detail => detail.pageTitle),
            datasets: [{
                label: 'Number of Comments',
                data: commentsDetails.map(detail => detail.comments),
                backgroundColor: backgroundColors,
                borderColor: borderColors,
                borderWidth: 1
            }]
        };

        if (kr_reportStatusChart) {
            kr_reportStatusChart.data = transformedData;
            kr_reportStatusChart.update();
        } else {
            kr_reportStatusChart = new Chart(statusCtx, {
                type: 'bar',
                data: transformedData,
                options: getChartOptions()
            });
        }
    }

    function getChartOptions() {

        return {
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true,
                    beginAtZero: true
                }
            },
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                title: {
                    display: true,
                    text: 'User Engagement Metrics'
                }
            }
        };
    }

    // Helper function to generate random integers
    function kr_getRandomInt(min, max) {
        min = Math.ceil(min);
        max = Math.floor(max);
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }



    function kr_fetchAnalysisData() {
        fetch(apiUrl + 'fetch_comment_data_analysis/', {
            method: 'GET',
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // console.log('Metrics:', data);
                if (data.data.comments.length === 0) {
                    // Handle no data scenario, for example:
                    console.log('No data available');
                } else {
                    kr_updateChartData(data.data);
                }
            })
            .catch(error => {
                console.error('Error fetching data:', error);
            });
    }

    if (document.getElementById('kr-commentsChart')) {
        document.addEventListener('DOMContentLoaded', kr_fetchAnalysisData);
    }
}



document.addEventListener('DOMContentLoaded', function () {

    if (document.getElementById('kr_leave_comment')) {
        document.getElementById('kr_leave_comment').addEventListener('click', function () {
            var form = document.getElementById('katorymnd_lrdn');
            form.classList.toggle('katorymnd_63p1h9o');
            if (!form.classList.contains('katorymnd_63p1h9o')) {
                form.style.maxHeight = form.scrollHeight + 'px'; // Adjust height to fit content
            } else {
                form.style.maxHeight = '0';
            }
        });
    }


    // Send initial comment data  
    function kr_sendFormData() {
        // Get the comment value
        let commentValue = document.getElementById('tirq').value;

        // Get page details from hidden inputs
        let pageId = document.querySelector('input[name="page_id"]').value;
        let pageSlug = document.querySelector('input[name="page_slug"]').value;
        let pageTitle = document.querySelector('input[name="page_title"]').value;

        // Validate the comment before sending
        if (!commentValue || commentValue.length < 10) {
            toastr.error('Please enter a comment at least 10 characters long.'); // Display error message
            return; // Exit the function if validation fails
        }

        // Attempt to retrieve the stored user data
        const userData_kr = getStoredUserData_kr();

        // Check if user data is available
        if (!userData_kr || !userData_kr.data.userName) {
            toastr.error('Please log in.');
            return; // Exit the function if no user data is available
        }

        // Include the page details in the formData
        let formData = {
            'comment': commentValue,
            'pageId': pageId,
            'userName': userData_kr.data.userName,
            'pageSlug': pageSlug,
            'pageTitle': pageTitle
        };

        fetch(apiUrl + 'submit_comment/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce
            },
            body: JSON.stringify(formData)
        })
            .then(response => {
                if (!response.ok) {
                    // Handle HTTP errors, but still try to parse the response for a server-provided message
                    return response.json().then(err => {
                        throw new Error(err.message || 'Network response was not ok');
                    });
                }
                return response.json();
            })
            .then(data => {
                // Dynamically use the server's response message for success notifications
                console.log(data);
                toastr.success(data.message || 'Comment submitted successfully!');
            })
            .catch(error => {
                // Handle both network errors and server-side errors (like validation errors)
                console.error(error);
                // Dynamically display error messages, fallback to a generic error if message is not available
                toastr.error(error.message || 'Error submitting comment.');
            });

    }

    // Event handler for button click to submit comment
    if (document.getElementById('kr_submit_comment')) {
        document.getElementById('kr_submit_comment').addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default form submission
            kr_sendFormData(); // Call the function to send comment data
        });
    }




    // admin pg
    var tabs = document.querySelectorAll('.kr-nav-tab');

    tabs.forEach(function (tab) {
        tab.addEventListener('click', function (e) {
            e.preventDefault();

            // Remove active class from all tabs
            tabs.forEach(function (t) {
                t.classList.remove('nav-tab-active');
            });

            // Hide all tab content and remove 'flex' and 'block' classes
            var contents = document.querySelectorAll('.kr-tab-content');
            contents.forEach(function (content) {
                content.style.display = 'none';
                content.classList.remove('flex', 'block');
            });

            // Add active class to clicked tab
            this.classList.add('nav-tab-active');
            var tabContent = document.getElementById(this.getAttribute('data-tab'));

            // Check if the clicked tab is 'kr-home'
            if (this.getAttribute('data-tab') === 'kr-home') {
                if (tabContent) {
                    // Apply grid display directly without using flex
                    tabContent.style.display = 'grid';
                    tabContent.style.gridTemplateColumns = 'repeat(auto-fill, minmax(309px, 1fr))'; // Set grid columns
                    tabContent.style.gap = '1rem'; // Set gap between grid items
                    tabContent.style.marginTop = '6px'; // Set top margin
                }
            } else {
                if (tabContent) {
                    tabContent.style.display = 'block'; // Use block for other tabs
                    tabContent.classList.add('block');
                }
            }
        });
    });

    var katorymnd_b4w4pps = document.getElementsByClassName("kr-accordion-button");
    for (var i = 0; i < katorymnd_b4w4pps.length; i++) {
        katorymnd_b4w4pps[i].addEventListener("click", function () {
            this.classList.toggle("active");
            var panel = this.nextElementSibling;
            if (panel.style.display === "block" || panel.style.display === "grid") {
                panel.style.display = "none";
            } else {
                panel.style.display = "block";
            }
        });
    }

    // Set 'kr-home' tab as active on page load
    var krHomeTab = document.querySelector('.kr-nav-tab[data-tab="kr-home"]');
    if (krHomeTab) {
        krHomeTab.click();
    }




});


// Function to retrieve stored user data from session storage
function getStoredUserData_kr() {
    const userDataStr = sessionStorage.getItem('KrUserData');
    if (userDataStr) {
        return JSON.parse(userDataStr);
    } else {
        console.log('No user data stored in session storage.');
        return null;
    }
}

// Fetch feedback details
let comments;
let currentPage = 0; // Tracks the current page for pagination
let totalComments = 0; // Variable to store the total number of comments
let commentsPerPage = wpkrNumComments; // Match this with the default limit in your PHP code

function handleNoCommentsMessage_kr(data) {
    if (data && data.message) {
        // console.log(data.message);
        return data.message;
    }
    return null;
}
function displayMessage_kr(message) {
    const katorymnd_1ddvzjy = document.getElementById('ulto');
    const messageElement = document.createElement('p');
    messageElement.textContent = message;
    messageElement.className = 'no-comments-message'; // Assign a class for potential styling

    if (katorymnd_1ddvzjy) {
        katorymnd_1ddvzjy.innerHTML = ''; // Clear the container
        katorymnd_1ddvzjy.appendChild(messageElement); // Add the message to the container
    }
}

function kr_fetchFeedbackDetails() {
    if (typeof kr_pageDetails !== 'undefined' && kr_pageDetails.id !== null && kr_pageDetails.slug !== null) {
        // Construct the URL with query parameters
        const url = new URL(apiUrl + 'fetch_data/');
        url.searchParams.append('page_id', kr_pageDetails.id);
        url.searchParams.append('page_slug', kr_pageDetails.slug);
        url.searchParams.append('page', currentPage); // Add the current page to the request
        url.searchParams.append('limit', commentsPerPage); // display comments
        fetch(url, {
            method: 'GET',
            headers: {
                'X-WP-Nonce': wpApiSettings.nonce
            }
        })
            .then(response => response.json())
            .then(data => {
                console.log('Success:', data);
                const message = handleNoCommentsMessage_kr(data);
                if (!message) {
                    comments = data.comments;
                    totalComments = data.total_comments; // Update the total comments
                    populateComments(); // Populate the comments with the new data
                    currentPage++; // Increment the page for the next load
                } else {
                    // Handle the scenario when there's a message instead of comments
                    comments = {}; // Reset or clear comments
                    totalComments = 0; // Reset total comments
                    displayMessage_kr(message); // Display the message from the server
                    populateComments(); // This will now hide the load more button

                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    }

}

// Initial fetch of feedback details
kr_fetchFeedbackDetails();


function calculateTotalReactions(commentOrReply) {
    if (!commentOrReply.reactions) {
        return 0;
    }
    return Object.values(commentOrReply.reactions).reduce((total, reactionList) => total + reactionList.length, 0);
}

// Add a function to initialize tooltips for a specific comment
function initializeTooltipsForComment(commentId) {
    const tooltipTriggerEls = document.querySelectorAll(`#${commentId} [data-bs-toggle="tooltip"]`);
    tooltipTriggerEls.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function timeSince_kr(date) {
    const seconds = Math.floor((new Date() - new Date(date)) / 1000);
    let interval = seconds / 31536000;

    if (interval > 1) {
        return Math.floor(interval) + " years ago";
    }
    interval = seconds / 2592000;
    if (interval > 1) {
        return Math.floor(interval) + " months ago";
    }
    interval = seconds / 86400;
    if (interval > 1) {
        return Math.floor(interval) + " days ago";
    }
    interval = seconds / 3600;
    if (interval > 1) {
        return Math.floor(interval) + " hours ago";
    }
    interval = seconds / 60;
    if (interval > 1) {
        return Math.floor(interval) + " minutes ago";
    }
    return Math.floor(seconds) + " seconds ago";
}

function generateCommentHTML(id, commentData, level = 1) {
    const uniqueId = `${id}`;
    let marginLeft = `${level * 20}px`;
    let replyLinkHTML = level < 5 ? `<a onclick="showReplyForm('replyForm_${uniqueId}'); return false;">Reply</a>` : '';
    // Calculate total reactions for the comment
    let totalReactions = calculateTotalReactions(commentData);

    // Attempt to retrieve the stored user data
    const userData = getStoredUserData_kr();

    // Determine if the "Edit" and "Delete" links should be shown
    let editDeleteLinksHTML = '&nbsp;';
    if (userData && userData.data && userData.data.userName === commentData.userName || katorymnd_98lq9yd !== "not admin") {
        // User matches the comment's user, show "Edit" and "Delete" links
        editDeleteLinksHTML = `<a  onclick="editComment('${uniqueId}')">Edit</a>
                            <a  onclick="deleteComment('${uniqueId}')">Delete</a>`;
    }

    // Generate the "Report Abuse" link HTML with a custom class and minimal inline styling
    let reportAbuseLinkHTML = `<a  class="wp-report-abuse-link" onclick="reportAbuse_kr('${uniqueId}'); return false;" style="color: #aa4000; cursor: pointer; text-decoration: none;">Report Abuse</a>`;

    // Use the `timeSince_kr` function to calculate how long ago the comment was posted
    let timePosted_kr = timeSince_kr(commentData.timestamp);

    let html = `
<div class="card mb-3 shadow" style="margin-left:${marginLeft}" id="${uniqueId}">
<div class="card-header text-white d-flex align-items-center" style="background-color: ${savedHeaderBgColor};">
    <div class="kr-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" fill="#bbb" />
        </svg>
    </div>
    <div class="kr-comment-text ms-2">
        <strong>${commentData.userName}</strong>
    </div>
</div>
<div class="card-body">
    <p class="card-text">${commentData.content}</p>
    <div class="kr-actions">
    <a>React (${formatReactionCount(totalReactions)})</a>
      ${replyLinkHTML}
       <span>${timePosted_kr}</span>&nbsp;
      ${editDeleteLinksHTML}
      ${reportAbuseLinkHTML}
    </div>
</div>
${generateEmojiReactionsHTML(uniqueId, savedEmojiTheme, userChosenEmojis)}
${level < 5 ? generateReplyFormHTML(uniqueId, level) : ''}
</div>
<div id="replies_${uniqueId}" class="ms-3 mt-2 replies-container">
<!-- Dynamically added replies will appear here -->
</div>`;

    // Initialize tooltips for the current comment
    setTimeout(function () {
        initializeTooltipsForComment(uniqueId);
    }, 0);

    // Function to recursively generate replies, sub_replies, and sub_sub_replies
    function generateNestedReplies(nestedReplies, level) {
        let nestedHTML = "";
        for (let replyId in nestedReplies) {
            nestedHTML += generateCommentHTML(replyId, nestedReplies[replyId], level + 1);
        }
        return nestedHTML;
    }

    // Generate replies and sub_replies
    if (commentData.replies) {
        html += generateNestedReplies(commentData.replies, level);
    }
    if (commentData.sub_reply) {
        html += generateNestedReplies(commentData.sub_reply, level + 1);
    }

    // Always generate sub_sub_replies, but the reply option is already disabled above
    if (commentData.sub_sub_reply) {
        html += generateNestedReplies(commentData.sub_sub_reply, level + 1);
    }

    return html;
}

/**
 * the new comment generation id  is  triggered by the  search of  the 
 * current  generated  id from   {const comments} details
 * 
 * later use the  database for that  also 
 */
function generateNewCommentId(parentId, isReply = false, isSubReply = false, isSubSubReply = false) {
    let count = 1;
    let newId;

    const idExists = (id) => {
        // Recursive function to check if an ID exists in the comments structure
        function searchIdInComments(commentsObj) {
            for (let key in commentsObj) {
                if (commentsObj[key].id === id) {
                    return true;
                }
                // Search in replies, sub_replies, and sub_sub_replies
                if (commentsObj[key].replies && searchIdInComments(commentsObj[key].replies)) {
                    return true;
                }
                if (commentsObj[key].sub_reply && searchIdInComments(commentsObj[key].sub_reply)) {
                    return true;
                }
                if (commentsObj[key].sub_sub_reply && searchIdInComments(commentsObj[key].sub_sub_reply)) {
                    return true;
                }
            }
            return false;
        }
        return searchIdInComments(comments);
    };

    do {
        if (isReply) {
            // Generating ID for a reply to a main comment
            newId = `${parentId}R${count}`;
        } else if (isSubReply) {
            // Generating ID for a sub-reply to a reply
            newId = `${parentId}SR${count}`;
        } else if (isSubSubReply) {
            // Generating ID for a sub-sub-reply
            newId = `${parentId}SSR${count}`;
        } else {
            // Generating ID for a primary comment
            newId = `${parentId}R${count}`;
        }
        count++;
    } while (idExists(newId));

    return newId;
}


function formatReactionCount(count) {
    if (count < 1000) {
        return count; // Return the original number if less than 1000
    } else if (count < 1000000) {
        return (count / 1000).toFixed(1) + 'K'; // Convert to 'K' for thousands
    } else {
        return (count / 1000000).toFixed(1) + 'M'; // Convert to 'M' for millions
    }
}

//savedEmojiSelection = userChosenEmojis ;
function generateEmojiReactionsHTML(commentId, theme, userChosenEmojis) {
    let emojis = ['like', 'dislike', 'love', 'smile', 'laugh', 'angry', 'cry', 'shock'];

    // Find the comment or reply by id
    let commentOrReply = findCommentOrReplyById(commentId);
    let initialCounts = calculateInitialReactionCounts(commentOrReply, emojis);

    // If the user does not specify any emojis, use all emojis
    let emojisToDisplay = userChosenEmojis && userChosenEmojis.length > 0 ? userChosenEmojis : emojis;


    let emojiHTML = emojisToDisplay.map(emoji => `
        <span class="kr-emoji" data-emoji="${emoji}" data-comment-id="${commentId}" data-bs-toggle="tooltip" data-bs-placement="top" title="${capitalizeFirstLetter(emoji)}" onclick="handleEmojiClick('${emoji}', '${commentId}')">
            <img src="${pwy}img/emojis/${theme}/${emoji}.png" alt="${emoji}" style="width: 29px; height: 29px;">
            <span class="kr-reaction-count" id="reaction-${emoji}-${commentId}" style="font-size: 0.8rem; margin-left: 5px;">${formatReactionCount(initialCounts[emoji])}</span>
        </span>
    `).join('');

    return `
        <div class="card-footer text-muted" style="display: flex; align-items: center; justify-content: start; gap: 10px;">
            <div id="katorymnd_vs76gy2" class="d-flex flex-wrap align-items-center justify-content-start">
                ${emojiHTML}
            </div>
        </div>`;
}

function calculateInitialReactionCounts(commentOrReply, emojis) {
    let counts = {};
    if (commentOrReply && commentOrReply.reactions) {
        emojis.forEach(emoji => {
            counts[emoji] = commentOrReply.reactions[emoji] ? commentOrReply.reactions[emoji].length : 0;
        });
    } else {
        // Initialize counts to 0 if commentOrReply or reactions are undefined
        emojis.forEach(emoji => {
            counts[emoji] = 0;
        });
    }
    return counts;
}


function findCommentOrReplyById(commentId, commentData = comments) {
    // Check if the current level has the comment
    if (commentData[commentId]) {
        return commentData[commentId];
    }

    // Recursively search in replies, sub_replies, and sub_sub_replies
    for (let key in commentData) {
        if (commentData[key].replies) {
            let result = findCommentOrReplyById(commentId, commentData[key].replies);
            if (result) return result;
        }
        if (commentData[key].sub_reply) {
            let result = findCommentOrReplyById(commentId, commentData[key].sub_reply);
            if (result) return result;
        }
        if (commentData[key].sub_sub_reply) {
            let result = findCommentOrReplyById(commentId, commentData[key].sub_sub_reply);
            if (result) return result;
        }
    }

    // Return undefined if not found
    return undefined;
}




function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function handleEmojiClick(newEmoji, commentId) {
    // Attempt to retrieve the stored user data
    const userData = getStoredUserData_kr();

    // Check if user data is available and has the 'data' property
    if (!userData || !userData.data.userName) {
        toastr.error('Please log in.', 'Error'); // Display an error message using toastr
        return; // Exit the function if no user data is available
    }


    // Correctly access the userName from the userData object
    let userId = userData.data.userName; // Correctly access the userName

    let previousEmoji = getUserPreviousReaction(commentId, userId);

    if (previousEmoji === newEmoji) {
        // User is removing their current reaction
        updateReactionCount(commentId, previousEmoji, -1);
        setUserReaction(commentId, userId, null);
    } else {
        if (previousEmoji) {
            // User is changing their reaction from a different emoji
            updateReactionCount(commentId, previousEmoji, -1);
        }
        // User is adding a new reaction or changing to a different one
        updateReactionCount(commentId, newEmoji, 1);
        setUserReaction(commentId, userId, newEmoji);
    }

    // Prepare data to be sent to the server
    let data = {
        commentId: commentId,
        userId: userId,
        newEmoji: newEmoji,
        previousEmoji: previousEmoji
    };

    sendReactionUpdateToServer(data);
}

function updateReactionCount(commentId, emoji, change) {
    let reactionCountElement = document.getElementById(`reaction-${emoji}-${commentId}`);
    let currentCount = parseInt(reactionCountElement.textContent) || 0;
    reactionCountElement.textContent = formatReactionCount(currentCount + change);
}

function clearKrReactionLocalStorage() {
    for (let key in localStorage) {
        if (key.startsWith('reaction-')) {
            localStorage.removeItem(key);
        }
    }
}
document.addEventListener('DOMContentLoaded', clearKrReactionLocalStorage);


function getUserPreviousReaction(commentId, userId) {
    // Example logic with localStorage, replace with your implementation
    return localStorage.getItem(`reaction-${commentId}-${userId}`);
}

function setUserReaction(commentId, userId, emoji) {
    // Example logic with localStorage, replace with your implementation
    if (emoji) {
        localStorage.setItem(`reaction-${commentId}-${userId}`, emoji);
    } else {
        localStorage.removeItem(`reaction-${commentId}-${userId}`);
    }
}

function sendReactionUpdateToServer(data) {
    fetch(apiUrl + 'update_reaction/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce
        },
        body: JSON.stringify(data),
    })
        .then(response => {
            if (!response.ok) {
                // If the server response was not ok, it means an error was returned.
                // Parse the response as JSON to get the error message
                return response.json().then(data => {
                    throw new Error(data.message || 'An unknown error occurred');
                });
            }
            // If the response was ok, parse it as JSON
            return response.json();
        })
        .then(data => {
            // Handle successful response
            if (data.error) {
                // If the response JSON contains an 'error' key, display it as an error message
                toastr.error(data.message || 'An error occurred. Please try again.');
            } else {
                // If there's no 'error' key, it's a successful operation, display the success message
                toastr.success(data.message || 'Reaction updated successfully.');
            }
        })
        .catch(error => {
            // Handle network errors or errors thrown from the response handling block
            console.error('Error:', error);
            toastr.error(error.message || 'An error occurred. Please try again.');
        });
}



function generateReplyFormHTML(uniqueId, level = 1) {
    let marginLeft = `${level * 20}px`;
    return `
<div id="replyForm_${uniqueId}" class="reply-form p-3" style="display:none;">
<textarea id="replyText_${uniqueId}" class="form-control" placeholder="Your reply..."></textarea>
<button class="btn btn-success btn-sm mt-2" onclick="postReply('replyText_${uniqueId}', 'replies_${uniqueId}', ${level + 1})">Post</button>
</div>`;
}


function populateComments() {
    const container_kr = document.querySelector('.katorymnd_df8yvh7');
    const loadMoreBtn = document.getElementById('kr_loadMoreComments');
    // Ensure comments is always an object
    comments = comments || {};
    if (container_kr) {
        container_kr.innerHTML = ''; // Clear existing comments

        if (Object.keys(comments).length === 0) {
            loadMoreBtn.style.display = 'none'; // Hide the load more button if no comments
        } else {
            // Iterate over the comments and append them to the container
            for (let commentId in comments) {
                container_kr.innerHTML += generateCommentHTML(commentId, comments[commentId]);
                initializeTooltipsForComment(commentId);
            }

            // Determine if we need to show or hide the load more button
            if ((currentPage + 1) * commentsPerPage >= totalComments) {
                loadMoreBtn.style.display = 'none'; // Hide the button if there are no more comments to load
            } else {
                loadMoreBtn.style.display = 'block'; // Show the button if there are more comments to load
            }
        }
    }
}
// Invoke the populateComments function to display the comments
populateComments();

if (document.getElementById('kr_loadMoreComments')) {
    document.getElementById('kr_loadMoreComments').addEventListener('click', function () {
        kr_fetchFeedbackDetails(); // Fetch and update the comments on click
    });
}

function showReplyForm(formId) {
    // Hide all reply forms except the clicked form
    var allReplyForms = document.querySelectorAll('.reply-form');
    allReplyForms.forEach(form => {
        if (form.id !== formId) {
            form.style.display = 'none';
        }
    });

    // Toggle display of the clicked form
    var form = document.getElementById(formId);
    form.style.display = form.style.display === 'none' ? '' : 'none';
}

function postReply(textareaId, repliesDivId, level) {
    var text = document.getElementById(textareaId).value;
    var repliesDiv = document.getElementById(repliesDivId);

    if (text.trim() === '') {
        // alert('Reply cannot be empty!');
        toastr.error('Reply cannot be empty!');
        return;
    }

    // Attempt to retrieve the stored user data
    const userData_kr = getStoredUserData_kr();

    // Check if user data is available
    if (!userData_kr || !userData_kr.data.userName) {
        toastr.error('Please log in.');
        return; // Exit the function if no user data is available
    }

    // Additional client-side validation for content length
    if (text.trim().length < 10) {
        toastr.error('Comment is too short. Please provide more detail.');
        return;
    }

    var marginLeft = `${level * 20}px`; // Incremental left margin for nested replies

    // Determine the parentId for the new comment
    var parentId, isReply, isSubReply, isSubSubReply;
    isReply = isSubReply = isSubSubReply = false; // Initialize variables

    if (level === 2) {
        parentId = repliesDivId.split('replies_')[1];
        isReply = true;
    } else if (level === 3) {
        parentId = findParentIdForSubReply(repliesDivId);
        isSubReply = true;
    } else if (level === 4) {
        parentId = findParentIdForSubSubReply(repliesDivId);
        isSubSubReply = true;
    } else if (level === 5) {
        parentId = findParentIdForSubSubReply(repliesDivId);
        isSubSubReply = true;
    }

    // Log the level and parent ID for debugging
    // console.log("Level: ", level);
    // console.log("Parent ID: ", parentId);

    // Generate a new ID for the comment

    var newId = generateNewCommentId(parentId, isReply, isSubReply, isSubSubReply);
    const userData = getStoredUserData_kr();
    // Insert the new reply into the local comments structure
    let newReply = {
        id: newId,
        userName: userData.data.userName, //logged username
        content: text,
        parentId: parentId,
        reactions: {}, // Initialize without reactions
        replies: {} // Prepare for potential future replies
    };

    // Send the new comment or reply to the server
    fetch(apiUrl + 'save_comment/', {
        method: 'POST',
        headers: {
            'X-WP-Nonce': wpApiSettings.nonce,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(newReply)
    })
        .then(response => {
            if (!response.ok) {
                // If the server response was not ok, it means an error was returned.
                // Parse the response as JSON to get the error message
                return response.json().then(data => {
                    throw new Error(data.message || 'An unknown error occurred');
                });
            }
            // If the response was ok, parse it as JSON
            return response.json();
        })
        .then(data => {
            // Handle successful response
            if (data.error) {
                // If the response JSON contains an 'error' key, display it as an error message
                toastr.error(data.message);
            } else {
                // If there's no 'error' key, it's a successful operation, display the success message
                toastr.success(data.message);
            }
        })
        .catch(error => {
            // Handle network errors or errors thrown from the response handling block
            console.error('Error:', error);
            toastr.error(error.message || 'An error occurred. Please try again.');
        });


    insertNewReplyIntoComments(parentId, newReply, level);
    simulateAddComment(newId, newReply.userName, newReply.content, parentId)
    // Generate the HTML for the new reply and append it
    var newCommentHTML = generateReplyHTML(newId, newReply, marginLeft, level);
    repliesDiv.insertAdjacentHTML('beforeend', newCommentHTML); // Append the new comment HTML to the replies div

    // Post-insertion actions
    initializeTooltips(newId);
    clearAndHideForm(textareaId, newId);

    // Clear the textarea after posting
    document.getElementById(textareaId).value = '';
}


function simulateAddComment(newId, userName, content, parentId = null) {
    // Simulate saving a comment or reply
    if (parentId) {
        console.log(
            `Saving reply with ID: ${newId}, by: ${userName}, content: "${content}", as a reply to: ${parentId}`
        );
    } else {
        console.log(`Saving primary comment with ID: ${newId}, by: ${userName}, content: "${content}"`);
    }
}

function insertNewReplyIntoComments(parentId, newReply, level) {
    let parentCommentOrReply = findCommentOrReplyById(parentId);

    if (!parentCommentOrReply.replies) {
        parentCommentOrReply.replies = {};
    }
    parentCommentOrReply.replies[newReply.id] = newReply;
}
function generateReplyHTML(newId, newReply, marginLeft, level) {
    // Generate the HTML block for the new reply, similar to the one in your postReply function
    return `
<div class="card mb-3 shadow mt-3" style="margin-left:${marginLeft}" id="${newId}">
<div class="card-header text-white d-flex align-items-center" style="background-color: ${savedHeaderBgColor};">
    <div class="kr-avatar">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="50" fill="#bbb" />
        </svg>
    </div>
    <div class="kr-comment-text ms-2">
        <strong>${newReply.userName}</strong>
    </div>
</div>
<div class="card-body">
    <p class="card-text">${newReply.content}</p>
    <div class="kr-actions">
        <a>React</a>
        ${level < 4 ? `<a onclick="showReplyForm('replyForm_${newId}')">Reply</a>` : ''}
        <span>Just now</span>
        <a onclick="editComment('${newId}')">Edit</a>
        <a onclick="deleteComment('${newId}')">Delete</a>
    </div>
</div>
${generateEmojiReactionsHTML(newId, savedEmojiTheme, userChosenEmojis)}
${level < 4 ? generateReplyFormHTML(newId, level) : ''}
</div>
<div id="replies_${newId}" class="ms-3 mt-2 replies-container">
<!-- Dynamically added replies will appear here -->
</div>
`;
}

function initializeTooltips(newId) {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll(`#${newId} [data-bs-toggle="tooltip"]`));
    tooltipTriggerList.forEach(function (tooltipTriggerEl) {
        new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function clearAndHideForm(textareaId, newId) {
    // Clear the textarea
    document.getElementById(textareaId).value = '';

    // Hide the reply form
    let formId = `replyForm_${textareaId.split('replyText_')[1]}`;
    showReplyForm(formId);
}


// to indentify the logical comment ids to remove by the comment structure
function debugComment(commentId, comments, parentIdPath = "", level = 0, idsToDelete = []) {
    const comment = findComment(commentId, comments);
    if (!comment) {
        console.log("Comment not found:", commentId);
        return idsToDelete;
    }

    const commentIdPath = parentIdPath + (parentIdPath ? " -> " : "") + comment.id;
    const lastId = commentIdPath.split(" -> ").pop(); // Extracts the last ID

    idsToDelete.push(lastId);

    if (comment.replies) {
        Object.values(comment.replies).forEach(reply => {
            debugComment(reply.id, comments, commentIdPath, level + 1, idsToDelete);
        });
    }
    if (comment.sub_reply) {
        Object.values(comment.sub_reply).forEach(subReply => {
            debugComment(subReply.id, comments, commentIdPath, level + 2, idsToDelete);
        });
    }
    if (comment.sub_sub_reply) {
        Object.values(comment.sub_sub_reply).forEach(subSubReply => {
            debugComment(subSubReply.id, comments, commentIdPath, level + 3, idsToDelete);
        });
    }

    return idsToDelete;
}

function findComment(commentId, comments) {
    for (const id in comments) {
        if (id === commentId) {
            return comments[id];
        }
        if (comments[id].replies) {
            const reply = findComment(commentId, comments[id].replies);
            if (reply) return reply;
        }
        if (comments[id].sub_reply) {
            const subReply = findComment(commentId, comments[id].sub_reply);
            if (subReply) return subReply;
        }
        if (comments[id].sub_sub_reply) {
            const subSubReply = findComment(commentId, comments[id].sub_sub_reply);
            if (subSubReply) return subSubReply;
        }
    }
    return null; // Comment not found
}


function deleteComment(commentId) {
    console.log(`Delete clicked for comment/reply/sub-reply/sub-sub-reply with ID: ${commentId}`);

    const idsToDelete = debugComment(commentId, comments);

    // Prepare data to send
    const data = {
        'idsToDelete': idsToDelete
    };

    // Send the request to your new WordPress REST API endpoint
    fetch(apiUrl + 'delete_comment/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce // Include nonce for security
        },
        body: JSON.stringify(data)
    })
        .then(response => response.json())
        .then(data => {
            console.log('Success:', data);
            // Display success message with Toastr
            toastr.success(data.message);

            // Here you can also remove the comments from the DOM, if the deletion was successful
            idsToDelete.forEach(id => {
                const elementToDelete = document.getElementById(id);
                if (elementToDelete) {
                    elementToDelete.parentNode.removeChild(elementToDelete);
                }
            });
        })
        .catch((error) => {
            console.error('Error:', error);
            // Display error message with Toastr
            toastr.error('Error: ' + error);
        });

}


// edit the  current comment
function editComment(commentId) {
    const commentElement = document.getElementById(commentId);
    if (!commentElement) {
        console.error('Comment element not found:', commentId);
        return;
    }

    // Targeting specifically the card text area within this comment
    const commentTextElement = commentElement.querySelector('.card-text');
    if (!commentTextElement) {
        console.error('Comment text element not found within:', commentId);
        return;
    }

    // Check if a textarea already exists within the comment's text element
    const existingTextarea = commentTextElement.querySelector('textarea');
    if (existingTextarea) {
        console.log('Edit mode is already active for comment:', commentId);
        return; // Exit the function early if edit mode is active
    }

    // Capture and store the original content
    const originalContent = commentTextElement.innerText;
    // Use encodeHtml to safely encode the original content for HTML display
    const encodedContent = encodeHtml(originalContent);
    commentElement.setAttribute('data-original-content', encodedContent);

    // Replace the content with a textarea and buttons
    commentTextElement.innerHTML = `
    <textarea class="form-control" id="editText_${commentId}">${originalContent}</textarea>
    <button class="btn btn-success btn-sm mt-2" onclick="saveEdit('${commentId}')">Save</button>
    <button class="btn btn-secondary btn-sm mt-2" onclick="cancelEdit('${commentId}')">Cancel</button>
`;
}


function encodeHtml(str) {
    return str.replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;"); // Encode single quotes
}


function saveEdit(commentId) {
    const editTextarea = document.getElementById(`editText_${commentId}`);
    const newContent = editTextarea.value;

    // Update the comment in the data structure
    updateCommentData(commentId, newContent);

    // Update the comment display
    const commentTextElement = document.getElementById(commentId).querySelector('.card-text');
    commentTextElement.innerHTML = newContent;
}

function cancelEdit(commentId) {
    const commentElement = document.getElementById(commentId);
    if (commentElement) {
        // Retrieve and decode the original content from the data attribute
        const encodedContent = commentElement.getAttribute('data-original-content');
        const originalContent = decodeHtml(encodedContent);

        const commentTextElement = commentElement.querySelector('.card-text');
        commentTextElement.innerText = originalContent;
    } else {
        console.error(`Comment with ID ${commentId} not found.`);
    }
}

function decodeHtml(encodedStr) {
    var textArea = document.createElement('textarea');
    textArea.innerHTML = encodedStr;
    return textArea.value;
}


// update  comment data here 
function updateCommentData(commentId, newContent) {
    // Recursive function to update the comment content
    function updateCommentRecursively(commentsObj, idToUpdate, newContent) {
        for (let key in commentsObj) {
            if (key === idToUpdate) {
                commentsObj[key].content = newContent;
                console.log(`Updated comment with ID: ${commentId}, New Content: ${newContent}`);
                return true; // Comment found and updated
            }

            // Recursively check in replies
            if (commentsObj[key].replies && updateCommentRecursively(commentsObj[key].replies, idToUpdate,
                newContent)) {
                return true;
            }

            // Recursively check in sub_replies
            if (commentsObj[key].sub_reply && updateCommentRecursively(commentsObj[key].sub_reply, idToUpdate,
                newContent)) {
                return true;
            }

            // Recursively check in sub_sub_replies
            if (commentsObj[key].sub_sub_reply && updateCommentRecursively(commentsObj[key].sub_sub_reply,
                idToUpdate, newContent)) {
                return true;
            }
        }
        return false; // Comment not found in this branch
    }

    // Update the comment in the local data structure
    if (!updateCommentRecursively(comments, commentId, newContent)) {
        console.error("Comment not found in data structure.");
        return; // Exit the function if comment not found
    }
    // Prepare data to be sent to the server
    const data = { commentId, newContent };

    // Send the updated comment data to the server using fetch API
    fetch(apiUrl + 'update_comment/', {
        method: 'POST',
        headers: {
            'X-WP-Nonce': wpApiSettings.nonce,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(response => {
            if (!response.ok) {
                // If the server response was not ok, it means an error was returned.
                return response.json().then(data => {
                    throw new Error(data.message || 'An unknown error occurred');
                });
            }
            // If the response was ok, parse it as JSON
            return response.json();
        })
        .then(data => {
            // Handle successful response
            if (data.error) {
                // If the response JSON contains an 'error' key, display it as an error message
                toastr.error(data.message);
            } else {
                // If there's no 'error' key, it's a successful operation, display the success message
                toastr.success(data.message);
            }
        })
        .catch(error => {
            // Handle network errors or errors thrown from the response handling block
            console.error('Error:', error);
            toastr.error(error.message || 'An error occurred. Please try again.');
        });
}


function findParentIdForSubReply(repliesDivId) {
    // Extracts the parent ID for a sub_reply    
    return repliesDivId.substring(repliesDivId.lastIndexOf('_') + 1);
}

function findParentIdForSubSubReply(repliesDivId) {
    // Extracts the parent ID for a sub_sub_reply   
    return repliesDivId.substring(repliesDivId.lastIndexOf('_') + 1);
}


function reportAbuse_kr(commentId) {
    const $ = jQuery; // Ensure jQuery is available as $

    // Attempt to retrieve the stored user data
    const userData = getStoredUserData_kr();

    // Check if user data is available and has the 'data' property
    if (!userData || !userData.data || !userData.data.userName) {
        toastr.error('Please log in.', 'Error'); // Display an error message using toastr
        return; // Exit the function if no user data is available
    }

    // Correctly access the userName from the userData object
    const userName = userData.data.userName;

    // Abuse report reasons
    const reportReasons = [
        "Offensive Language",
        "Harassment and Bullying",
        "Spamming",
        "Misinformation and Fake News",
        "Hate Speech",
        "Personal Attacks and Insults",
        "Violence and Threats",
        "Illegal Activities",
        "Invasion of Privacy",
        "Sexually Explicit Content",
        "Off-topic Posts"
    ];

    // Generate options for the select dropdown
    const optionsHtml = reportReasons.map(reason =>
        `<option value="${reason}">${reason}</option>`
    ).join('');

    // Dialog content creation or update
    const createOrUpdateDialog = () => {
        if ($("#reportAbuseModal").length) {
            // Update the dialog's content for the new comment ID
            $("#reportAbuseModal .report-abuse-comment-id").text(`Report abuse for comment ID: ${commentId}`);
        } else {
            // Create the dialog content with the form and append it to the body
            const dialogContent = `
                <div id="reportAbuseModal" title="Report Abuse">
                    <p class="report-abuse-comment-id">Report abuse for comment ID: ${commentId}</p>
                    <form id="reportAbuseForm">
                        <div class="form-group">
                            <label for="kr_reportReason">Reason for reporting:</label>
                            <select id="kr_reportReason" name="kr_reportReason" class="form-control">
                                ${optionsHtml}
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="kr_reportDetails">Additional details:</label>
                            <textarea id="kr_reportDetails" name="kr_reportDetails" rows="4" class="form-control"></textarea>
                        </div>
                    </form>
                </div>`;
            $("body").append(dialogContent);
        }
    };

    // Dialog initialization or reinitialization
    const initializeDialog = () => {
        $("#reportAbuseModal").dialog({
            autoOpen: false,
            modal: true,
            width: 'auto',
            minWidth: 500, // Ensures form elements are well-adjusted
            maxHeight: $(window).height() - 100,
            buttons: {
                "Report": function () {
                    const reason = $("#kr_reportReason").val();
                    const details = $("#kr_reportDetails").val();

                    // Construct the data object to send to the server
                    const reportData = {
                        commentId: commentId,
                        reason: reason,
                        details: details,
                        userName: userName // Include the userName in the report
                    };


                    // Send the report to the server using fetch
                    fetch(apiUrl + 'report_abuse/', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-WP-Nonce': wpApiSettings.nonce // Make sure wpApiSettings.nonce is correctly defined
                        },
                        body: JSON.stringify(reportData),
                    })
                        .then(response => {
                            if (!response.ok) {
                                // If the server response was not ok, an error was returned.
                                return response.json().then(data => {
                                    throw new Error(data.message || 'An unknown error occurred');
                                });
                            }
                            // If the response was ok, parse it as JSON
                            return response.json();
                        })
                        .then(data => {
                            if (data.error) {
                                // If the response JSON contains an 'error' key, display it as an error message
                                toastr.error(data.message || 'An error occurred. Please try again.');
                            } else {
                                // If there's no 'error' key, it's a successful operation, display the success message
                                toastr.success(data.message || 'Report submitted successfully.');
                                $("#kr_reportDetails").val('');
                            }
                        })
                        .catch(error => {
                            // Handle network errors or errors thrown from the response handling block
                            console.error('Error:', error);
                            toastr.error(error.message || 'An error occurred. Please try again.');
                        });

                    $(this).dialog("close");
                },
                "Cancel": function () {
                    $(this).dialog("close");
                }
            }
        }).dialog("open");
    };

    createOrUpdateDialog();
    initializeDialog();
}

//page  rating  logic
if (document.querySelectorAll('.kr_star').length) {
    document.addEventListener('DOMContentLoaded', function () {
        let starsKr = document.querySelectorAll('.kr_star');
        let kr_sTmodal = jQuery("#kr_rating_modal"); // jQuery for modal
        let dialogStarsKr = document.querySelectorAll('.kr_star_dialog');
        let selectedRating_kr = 0;
        let overallRating_kr = krRatingData.overall_rating; // Access the overall rating from the JSON data
        let userHasClicked_kr = false; // Track if the user has clicked a star

        function highlightStarsKr(value, targetStarsKr) {
            targetStarsKr.forEach((star, index) => {
                star.classList[index < value ? 'add' : 'remove']('selected');
            });
        }

        function resetStarColors_kr(uptoValue, targetStarsKr) {
            targetStarsKr.forEach((star, index) => {
                star.classList.toggle('selected', index < uptoValue);
            });
        }

        function setRating(value) {
            // Attempt to retrieve the stored user data
            const userData_kr = getStoredUserData_kr();
            if (!userData_kr || !userData_kr.data.userName) {
                toastr.error('Please log in.');
                return;
            }
            let pageId = document.querySelector('input[name="page_id"]').value;
            let formData = {
                rating: value,
                userName: userData_kr.data.userName,
                pageId: pageId,
            };
            fetch(apiUrl + 'submit_rating/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify(formData)
            })
                .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
                .then(data => {
                    console.log(data);
                    toastr.success(data.message || 'Rating submitted successfully!');
                })
                .catch(error => {
                    console.error(error);
                    toastr.error('Error submitting rating.');
                });
            resetStarColors_kr(value, starsKr);
        }

        function highlightStars(upToIndex) {
            starsKr.forEach(function (star, index) {
                star.classList.toggle('preview', index <= upToIndex);
            });
        }

        function kr_clearPreview() {
            starsKr.forEach(function (star) {
                star.classList.remove('preview');
                star.classList.toggle('selected', parseInt(star.getAttribute('data-value'), 10) <= overallRating_kr && !userHasClicked_kr);
            });
        }

        function setUserRating(rating) {
            userHasClicked_kr = true;
            overallRating_kr = rating;
            starsKr.forEach(function (star) {
                star.classList.remove('preview');
                star.classList.toggle('selected', parseInt(star.getAttribute('data-value'), 10) <= rating);
            });
        }

        starsKr.forEach(star => {
            star.addEventListener('mouseover', function () {
                highlightStarsKr(this.getAttribute('data-value'), starsKr);
            });

            star.addEventListener('mouseout', function () {
                resetStarColors_kr(selectedRating_kr, starsKr);
            });

            star.addEventListener('click', function () {
                selectedRating_kr = this.getAttribute('data-value');
                resetStarColors_kr(selectedRating_kr, dialogStarsKr);
                kr_sTmodal.dialog({
                    resizable: false,
                    height: "auto",
                    width: 400,
                    modal: true,
                    open: function () {
                        highlightStarsKr(selectedRating_kr, dialogStarsKr);
                    },
                    buttons: {
                        "Confirm": function () {
                            setRating(selectedRating_kr);
                            jQuery(this).dialog("close");
                        },
                        Cancel: function () {
                            jQuery(this).dialog("close");
                        }
                    }
                });
            });
        });

        dialogStarsKr.forEach(star => {
            star.addEventListener('click', function () {
                selectedRating_kr = this.getAttribute('data-value');
                highlightStarsKr(selectedRating_kr, dialogStarsKr);
            });
        });

        starsKr.forEach(function (star, index) {
            star.addEventListener('mouseover', function () {
                highlightStars(index);
            });

            star.addEventListener('click', function () {
                var rating = parseInt(star.getAttribute('data-value'), 10);
                setUserRating(rating);
            });
        });

        var starRatingContainer = document.querySelector('.kr_star-rating');
        starRatingContainer.addEventListener('mouseleave', kr_clearPreview);
        kr_clearPreview();
    });

}

if (document.getElementById('ratingSliderKr')) {
    function kr_slider_rating() {
        var kr_sliderElement = document.getElementById('ratingSliderKr');
        var katorymnd_ifbsred = document.getElementById('katorymnd_311kyyj');
        var kr_sliderValueElement = document.getElementById('sliderValueKr');
        var kr_submitButton = document.getElementById('katorymnd_u998ypa');
        let overallRating_kr = krRatingData && krRatingData.overall_rating ? krRatingData.overall_rating : 0;

        var userHasInteracted = false; // Flag to detect user interaction

        // Determine the initial slider start value
        var kr_startValue = overallRating_kr > 0 ? (overallRating_kr / 5) * 100 : (1.5 / 5) * 100;
        kr_submitButton.style.display = 'none'; // Initially hide the submit button

        // Initialize the slider
        noUiSlider.create(kr_sliderElement, {
            start: kr_startValue,
            connect: [true, false],
            range: {
                'min': 1,
                'max': 100
            }
        });

        // Use the 'slide' event for real-time slider value updates
        kr_sliderElement.noUiSlider.on('slide', function (kr_values, kr_handle) {
            var kr_value = kr_values[kr_handle];
            var kr_starRating = Math.round((kr_value / 100) * 5 * 10) / 10;

            // Update the display to show only the current slider value without "Overall Rating"
            kr_sliderValueElement.innerHTML = kr_starRating;

            userHasInteracted = true; // Set the flag as the user interacts
        });

        // Show the submit button upon slider change by the user
        kr_sliderElement.noUiSlider.on('change', function () {
            kr_submitButton.style.display = 'block'; // Show the button after user interaction
        });

        // The 'update' event to adjust the slider's visual feedback
        kr_sliderElement.noUiSlider.on('update', function (kr_values, kr_handle) {
            if (!userHasInteracted) { // Only if the user hasn't started interacting, show "Overall Rating"
                var kr_value = kr_values[kr_handle];
                var kr_starRating = Math.round((kr_value / 100) * 5 * 10) / 10;
                kr_sliderValueElement.innerHTML = `Overall Rating: ${kr_starRating}`;
                katorymnd_ifbsred.innerHTML = '';
            } else {
                katorymnd_ifbsred.innerHTML = `Rating: `;

            }

            var kr_percentage = (kr_values[kr_handle] - 1) / 99 * 100;
            kr_sliderElement.querySelector('.noUi-connect').style.background =
                'linear-gradient(to right, #0d6efd ' + kr_percentage + '%, #d3d3d3 ' + kr_percentage + '%)';
        });

        function kr_storeValueKr() {
            if (typeof toastr === 'undefined' || typeof getStoredUserData_kr !== 'function') {
                console.error('Required libraries or functions are not defined.');
                return;
            }

            const userData_kr = getStoredUserData_kr();
            if (!userData_kr || !userData_kr.data.userName) {
                toastr.error('Please log in.');
                return;
            }

            var kr_selectedValue = kr_sliderElement.noUiSlider.get();
            var kr_starRating = Math.round((kr_selectedValue / 100) * 5 * 10) / 10;
            let pageId = document.querySelector('input[name="page_id"]').value;
            let formData = {
                rating: kr_starRating,
                userName: userData_kr.data.userName,
                pageId: pageId,
            };

            if (typeof apiUrl === 'undefined') {
                console.error('API URL is not defined.');
                return;
            }

            fetch(apiUrl + 'submit_rating/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': wpApiSettings.nonce
                },
                body: JSON.stringify(formData)
            })
                .then(response => response.ok ? response.json() : Promise.reject('Network response was not ok'))
                .then(data => {
                    console.log(data);
                    toastr.success(data.message || 'Rating submitted successfully!');
                    kr_submitButton.style.display = 'none'; // Hide the button after saving
                })
                .catch(error => {
                    console.error(error);
                    toastr.error('Error submitting rating.');
                });
        }

        kr_submitButton.addEventListener('click', kr_storeValueKr);
    }

    kr_slider_rating();
}

document.querySelectorAll('.survey-trigger-btn').forEach(function (button) {
    button.addEventListener('click', function (e) {
        e.preventDefault(); // Prevent the default action

        // Use a regex to extract the last numbers from the button's ID
        let regex = /(\d+)$/;
        let matches = this.id.match(regex);
        let surveyId = matches ? matches[0] : null;
        // console.log('Button clicked, survey ID:', surveyId);

        // Check if surveyId was successfully extracted
        if (surveyId === null) {
            console.error('Failed to extract survey ID from button ID:', this.id);
            return; // Exit the function if no ID was extracted
        }

        // Prepare the request body with the survey ID
        let requestBody = {
            surveyId: surveyId
        };

        fetch(apiUrl + 'fetch_InsightPulse_data/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-WP-Nonce': wpApiSettings.nonce // Ensure wpApiSettings.nonce is properly defined
            },
            body: JSON.stringify(requestBody)
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                // Check if a modal already exists and remove it
                const existingModal = document.getElementById('surveyModal');
                if (existingModal) {
                    existingModal.remove();
                }
                const surveyTypeToColor = {
                    'survey': '#5cb85c', // Green
                    'poll': '#f0ad4e',   // Orange
                    'feedback': '#d9534f' // Red
                };

                const permalinkBaseCapitalizedKr = data.permalinkBase.charAt(0).toUpperCase() + data.permalinkBase.slice(1);

                // Determine the background color based on the survey type
                const backgroundColorKr = surveyTypeToColor[data.permalinkBase.toLowerCase()] || '#5cb85c';

                // Create the modal structure with the fetched data
                const modalHTML = `
            <div class="modal fade" id="surveyModal" tabindex="-1" aria-labelledby="surveyModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                          <div class="modal-header" style="background-color: ${backgroundColorKr}; color: white;">
                            <h5 class="modal-title" id="surveyModalLabel"> ${permalinkBaseCapitalizedKr} Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" id="surveyModalBody">
                        <div class="kr-survey-embedded" data-survey-id="${surveyId}">
                            ${data.html} 
                            </div>
                        </div>
                       <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                             <button type="button" class="btn btn-primary krsubmit-survey">Submit</button>
                        </div>
                    </div>
                </div>
            </div>
        `;

                // Append the modal to the body
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                // Show the modal using Bootstrap's API
                var modalElement = document.getElementById('surveyModal');
                var surveyModal = new bootstrap.Modal(modalElement);
                surveyModal.show();

                // Attach the event listener to the "Submit" button after the modal is shown
                modalElement.querySelector('.krsubmit-survey').addEventListener('click', function () {
                    const surveyModalBody = document.getElementById('surveyModalBody');
                    const container = surveyModalBody.querySelector('.container');

                    if (!container) {
                        console.error('No container found within the modal.');
                        return;
                    }

                    const formIdKr = container.getAttribute('id');
                    if (!formIdKr) {
                        console.error('Container within the modal does not have an ID.');
                        return;
                    }

                    // Attempt to retrieve the stored user data
                    const katorymnd_haixin0 = getStoredUserData_kr();

                    // Check if user data is available and has the 'data' property
                    if (!katorymnd_haixin0 || !katorymnd_haixin0.data || !katorymnd_haixin0.data.userName) {
                        toastr.error('Please log in.', 'Error'); // Display an error message using toastr
                        return; // Exit the function if no user data is available
                    }
                    // Call the validateAndProcessForm function with the dynamically obtained form ID
                    validateAndProcessForm(formIdKr);
                });
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});


// Function to validate and process form details based on the form ID
function validateAndProcessForm(formIdKr) {
    const krFormContainer = document.querySelector('#' + formIdKr);
    if (!krFormContainer) {
        console.error('Form with ID ' + formIdKr + ' not found.');
        return;
    }

    let katorymnd_n2r2cqq = krFormContainer.closest('.kr-survey-embedded');
    let katorymnd_ao5xrnj = katorymnd_n2r2cqq ? katorymnd_n2r2cqq.getAttribute('data-survey-id') : null;

    // If the survey ID was not found in a data attribute, try to get it dynamically from the URL
    if (!katorymnd_ao5xrnj) {
        const urlParams = new URLSearchParams(window.location.search);
        // Define an array of possible query parameter names
        const possibleParams = ['poll', 'survey', 'feedback', 'surveya4ffe0ab'];
        // Iterate through possibleParams to find the parameter that is present
        for (let paramName of possibleParams) {
            if (urlParams.has(paramName)) {
                katorymnd_ao5xrnj = urlParams.get(paramName);
                break;  // Exit the loop once a match is found
            }
        }
        // Set to 'Unknown Survey ID' if none of the parameters are found
        katorymnd_ao5xrnj = katorymnd_ao5xrnj || 'Unknown Survey ID';
    }

    // First, check if the user is available
    const katorymnd_3cy0mee = getStoredUserData_kr();
    if (!katorymnd_3cy0mee || !katorymnd_3cy0mee.data || !katorymnd_3cy0mee.data.userName) {
        toastr.error('Please log in.', 'Error'); // Display an error message using toastr
        return; // Exit the function if no user data is available
    }

    let isValid = true; // Assume the form is valid initially
    let missingQuestions = []; // To keep track of missing question texts
    let questionAnswers = []; // To keep track of questions and their answers

    const krQuestionContainers = krFormContainer.querySelectorAll('.card.mb-3[kr-data]');
    krQuestionContainers.forEach(container => {
        const krQuestionText = container.querySelector('h5.card-title').innerText;
        const krInputElements = container.querySelectorAll('input, textarea, select');

        let questionAnswered = false;
        let answers = []; // To keep track of answers for the current question

        Array.from(krInputElements).forEach(element => {
            if (element.tagName.toLowerCase() === 'select') {
                if (element.value.trim() !== '' && element.value.trim() !== 'Choose') {
                    questionAnswered = true;
                    answers.push(element.value.trim());
                }
            } else if (element.type === 'checkbox') {
                if (element.checked) {
                    questionAnswered = true;
                    answers.push(element.nextElementSibling.innerText); // Use the label text for checkboxes
                }
            } else if (element.type === 'radio') {
                if (element.checked) {
                    questionAnswered = true;
                    const radioLabel = element.nextElementSibling.innerText; // Use the label text for radio buttons
                    answers.push(radioLabel);
                }
            } else {
                if (element.value.trim() !== '') {
                    questionAnswered = true;
                    answers.push(element.value.trim());
                }
            }
        });

        if (!questionAnswered) {
            isValid = false; // Mark form as invalid if any question is unanswered
            missingQuestions.push(krQuestionText); // Add the question text to the list of missing questions
        } else {
            // Log the question and its answers if answered
            questionAnswers.push({
                question: krQuestionText,
                answers: answers
            });
        }
    });

    if (!isValid) {
        toastr.warning('Please answer the following questions:\n' + missingQuestions.join('\n'), 'Form Incomplete'); // Use toastr for feedback
        return;
    }

    // Log the user interacting with the form
    console.log('User:', katorymnd_3cy0mee.data.userName, 'has submitted the form ID:', formIdKr);
    //console.log(katorymnd_ao5xrnj)
    // Log each question and its answers
    console.log('Form ID:', formIdKr, 'is valid. Here are the answers:');
    questionAnswers.forEach(qa => {
        console.log(qa.question + ': ' + qa.answers.join(', '));
    });

    // Here, you can process the form further as needed
    // Prepare the request body
    const requestBody = {
        formId: formIdKr,
        surveyId: katorymnd_ao5xrnj,
        user: katorymnd_3cy0mee.data.userName,
        questionsAndAnswers: questionAnswers
    };


    // Send the data using fetch
    fetch(apiUrl + 'save_InsightPulse_user_data/', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-WP-Nonce': wpApiSettings.nonce
        },
        body: JSON.stringify(requestBody)
    })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            toastr.success('Your response has been submitted successfully!', 'Submission Successful');
            console.log('Form submission successful:', data);
        })
        .catch(error => {
            toastr.error('There was a problem submitting your form. Please try again.', 'Submission Error');
            console.error('Error submitting form:', error);
        });
}


// Function to extract and log input details
document.addEventListener('DOMContentLoaded', function () {

    // Delegate click event to dynamically handle form submission, regardless of where the form is located
    document.addEventListener('click', function (e) {
        if (e.target && e.target.matches('.kr-form-submit-btn')) {
            e.preventDefault(); // Prevent the default form submission

            const formIdKr = e.target.getAttribute('data-form-id');
            console.log(formIdKr)
            if (formIdKr) {

                validateAndProcessForm(formIdKr);
            }
        }
    });


    if (!document.getElementById('katorymnd_w3v2frb')) {
        document.querySelectorAll('[id^="kr_katorymnd_"]').forEach((formContainer, index) => {
            const formIdKr = formContainer.id;
            const krSubmitButton = document.createElement('button');
            krSubmitButton.textContent = 'Submit';
            krSubmitButton.type = 'button';
            krSubmitButton.className = 'btn btn-primary mt-3';
            formContainer.appendChild(krSubmitButton);

            krSubmitButton.addEventListener('click', function () {
                validateAndProcessForm(formIdKr);
            });
        });
    }
});


