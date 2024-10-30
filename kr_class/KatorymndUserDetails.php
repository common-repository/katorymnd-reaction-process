<?php

namespace Kr_user_details\Katorymnd_reaction;

class KatorymndUserDetails
{
    /**
     * Processes incoming user details or an array of user details for batch processing.
     * Validates the provided details, checks for the existence of the user in the database,
     * and updates or creates user details accordingly. In batch mode, if any user details fail validation,
     * the entire batch is rejected.
     * 
     * @param mixed $userDetails Single user details or an array of user details for batch processing.
     * @return true|array True on success, or an array of error messages on failure.
     */
    public static function processIncomingUserDetails($userDetails)
    {
        // Check if handling a batch of user details
        if (is_array($userDetails) && isset($userDetails[0]) && is_array($userDetails[0])) {
            return self::processBatchUserDetails($userDetails);
        } else {
            // Process individual user details
            return self::processSingleUserDetails($userDetails);
        }
    }

    protected static function processBatchUserDetails($usersDetails)
    {
        $globalErrors = [];
        foreach ($usersDetails as $index => $userDetails) {
            $errors = self::validateUserDetails($userDetails);
            if (!empty($errors)) {
                $globalErrors[$index] = $errors;
            }
        }

        // If any user details in the batch have errors, return all errors and do not proceed
        if (!empty($globalErrors)) {
            return $globalErrors;
        }

        // All user details have passed validation, proceed to insert/update each
        foreach ($usersDetails as $userDetails) {
            $result = self::processSingleUserDetails($userDetails);
            if ($result !== true) {
                // Ideally, this should not happen as we validated everything beforehand
                // Handle unexpected error during database operation
                return ['Unexpected error during batch processing.'];
            }
        }

        return true; // Indicate success for the whole batch
    }

    protected static function processSingleUserDetails($userDetails)
    {
        global $wpdb;
        $user_details_table_name = $wpdb->prefix . 'katorymnd_kr_user_details';

        // Extract user details
        $username = $userDetails['username'] ?? '';
        $email = $userDetails['email'] ?? '';
        $fullname = $userDetails['fullname'] ?? '';
        $avatar_url = $userDetails['avatar_url'] ?? null;

        // Validate individual user details
        $errors = self::validateUserDetails($userDetails);
        if (!empty($errors)) {
            return $errors;
        }

        // Database interaction logic, including existing user check and update/insert
        return self::updateOrInsertUserDetails($username, $email, $fullname, $avatar_url, $user_details_table_name);
    }

    protected static function validateUserDetails($userDetails)
    {
        $errors = [];
        $username = $userDetails['username'] ?? '';
        $email = $userDetails['email'] ?? '';
        $avatar_url = $userDetails['avatar_url'] ?? null;

        if (empty($username)) {
            $errors[] = 'Username is required.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format.';
        }
        if (!empty($avatar_url) && !filter_var($avatar_url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Invalid avatar URL.';
        }

        return $errors;
    }

    protected static function updateOrInsertUserDetails($username, $email, $fullname, $avatar_url, $table_name)
    {
        global $wpdb;

        // Check if the user already exists based on email or username
        $existing_user = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE email = %s OR username = %s",
            $email,
            $username
        ));

        if ($existing_user) {
            // Update existing user details
            $result = $wpdb->update(
                $table_name,
                ['email' => $email, 'full_name' => $fullname, 'avatar_url' => $avatar_url],
                ['user_id' => $existing_user->user_id]
            );

            // Check for errors during update
            if ($result === false) {
                return ['Failed to update user details.'];
            }
        } else {
            // Insert new user details
            $result = $wpdb->insert(
                $table_name,
                ['username' => $username, 'email' => $email, 'full_name' => $fullname, 'avatar_url' => $avatar_url]
            );

            // Check for errors during insert
            if ($result === false) {
                return ['Failed to insert new user details.'];
            }
        }

        return true; // Indicate success
    }
}
