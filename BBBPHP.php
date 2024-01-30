<?php

// Configuration: BBB server base URL and secret
$bbbServerBaseUrl = "https://your-bbb-server/bigbluebutton/";
$bbbSecret = "your-secret-key"; // You can get this value from BBB server.

/**
 * Function to generate a checksum for a BBB API call
 * 
 * @param string $apiCall The API call
 * @param string $params The query string for the API call
 * @param string $secret The shared secret
 * 
 * @return string The checksum
 */
function getChecksum($apiCall, $params, $secret) {
    return sha1($apiCall . $params . $secret);
}

/**
 * Function to make a BigBlueButton API call
 * 
 * @param string $url The complete URL for the API call
 * 
 * @return SimpleXMLElement|false The response as SimpleXMLElement or false on failure
 */
function bbbApiCall($url) {
    if ($result = simplexml_load_file($url)) {
        return $result;
    }
    return false;
}

/**
 * Function to create a meeting
 * 
 * @param string $meetingID The meeting ID
 * @param string $meetingName The meeting name
 * @param string $attendeePW The attendee password
 * @param string $moderatorPW The moderator password
 * 
 * @global string $bbbServerBaseUrl
 * @global string $bbbSecret
 * 
 * @return SimpleXMLElement|false The response as SimpleXMLElement or false on failure
 */
function createMeeting($meetingID, $meetingName, $attendeePW, $moderatorPW) {
    global $bbbServerBaseUrl, $bbbSecret;

    // Prepare the string for checksum
    $params = "meetingID=$meetingID&name=$meetingName&attendeePW=$attendeePW&moderatorPW=$moderatorPW";

    // Add parameters for translation
     $params .= "&meta_translation-enabled=true&meta_translation-source-language=hi&meta_translation-target-languages=en";
    
    $checksum = getChecksum("create", $params, $bbbSecret);

    // Create meeting API call URL
    $createMeetingUrl = $bbbServerBaseUrl . "api/create?" . $params . "&checksum=" . $checksum;

    // Make the API call
    return bbbApiCall($createMeetingUrl);
}

/**
 * Function to get join meeting URL
 * 
 * @param string $meetingID The meeting ID
 * @param string $userName The user's name
 * @param string $password The user's password
 * 
 * @global string $bbbServerBaseUrl
 * @global string $bbbSecret
 * 
 * @return string The join meeting URL
 */
function getJoinMeetingURL($meetingID, $userName, $password) {
    global $bbbServerBaseUrl, $bbbSecret;

    // Prepare the string for checksum
    $params = "meetingID=$meetingID&fullName=$userName&password=$password";
    $checksum = getChecksum("join", $params, $bbbSecret);

    // Return the join meeting URL
    return $bbbServerBaseUrl . "api/join?" . $params . "&checksum=" . $checksum;
}

// Usage:
$meetingID = "testMeetingID";
$meetingName = "Test Meeting";
$attendeePW = "ap";
$moderatorPW = "mp";

// Create a meeting
$createResponse = createMeeting($meetingID, $meetingName, $attendeePW, $moderatorPW);
if ($createResponse && $createResponse->returncode == 'SUCCESS') {
    echo "Meeting created successfully!\n";

    // Get join URL for a moderator
    $joinUrlModerator = getJoinMeetingURL($meetingID, "John Doe", $moderatorPW);
    echo "Join URL for moderator: $joinUrlModerator\n";

    // Get join URL for an attendee
    $joinUrlAttendee = getJoinMeetingURL($meetingID, "Jane Doe", $attendeePW);
    echo "Join URL for attendee: $joinUrlAttendee\n";
} else {
    echo "Failed to create the meeting.\n";
}
?>
