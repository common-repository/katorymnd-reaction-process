// Function to add a custom alert box to the DOM
function addKrAlert(message) {
    // Check if an alert box already exists
    if (!document.getElementById('kr_alert_box')) {
        var alertBox = document.createElement('div');
        alertBox.id = 'kr_alert_box';
        alertBox.style.display = 'none'; // Ensure it's not displayed by default

        var alertContent = document.createElement('div');
        alertContent.className = 'kr_alert_content';

        var alertMessage = document.createElement('div');
        alertMessage.id = 'kr_alert_message';
        alertMessage.className = 'kr_alert_message';
        alertMessage.textContent = message || 'Alert message!'; // Use parameter or default message

        var okButton = document.createElement('button');
        okButton.textContent = 'OK';
        okButton.className = 'kr_alert_button';
        okButton.onclick = function() { closeKrAlert(); }; // Updated for clarity

        alertContent.appendChild(alertMessage);
        alertContent.appendChild(okButton);
        alertBox.appendChild(alertContent);

        document.body.appendChild(alertBox);
    }

    // Directly show the alert box after adding it
    showAlertBox();
}

// Updated kr_alert function to handle dynamic addition
function kr_alert(htmlContent, type = 'notification') {
    let alertBox = document.getElementById("kr_alert_box");

    // Ensure the alert box exists before attempting to update it
    if (!alertBox) {
        addKrAlert(htmlContent); // Dynamically add the alert box if it doesn't exist
        alertBox = document.getElementById("kr_alert_box"); // Retrieve the newly added alert box
    }

    const messageElement = document.getElementById("kr_alert_message");
    messageElement.innerHTML = htmlContent; // Set HTML content

    const contentDiv = alertBox.querySelector('.kr_alert_content');
    contentDiv.className = 'kr_alert_content'; // Reset to default
    switch (type) {
        case 'success':
            contentDiv.classList.add('kr_alert_success');
            break;
        case 'error':
            contentDiv.classList.add('kr_alert_error');
            break;
        case 'notification':
        default:
            contentDiv.classList.add('kr_alert_notification');
    }

    alertBox.style.display = "flex"; // Ensure the alert box is visible
}

// Function to show the alert box
function showAlertBox() {
    var alertBox = document.getElementById('kr_alert_box');
    if (alertBox) {
        alertBox.style.display = 'flex'; // Change to flex to show it
    }
}

// Function to close and remove the custom alert box
function closeKrAlert() {
    var alertBox = document.getElementById('kr_alert_box');
    if (alertBox) {
        alertBox.parentNode.removeChild(alertBox);
    }
}
