<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/db-connector.php');
require_once FileUtils::normalizeFilePath('classes/manage-ip-address.php');
include_once FileUtils::normalizeFilePath('error-reporting.php');
include_once FileUtils::normalizeFilePath('default-time-zone.php');

const ATTEMPTS_LIMIT = 5;
const BLOCK_TIME = 1800; // 30 mins
$time = time() - BLOCK_TIME;

$response = ['success' => false, 'maxLimit' => false, 'message' => 'An error occurred'];

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $verification_token = $_POST['verificationTokenVal'];
    $email = $_POST['emailVal'];
    $voter_id = $_POST['voterIdVal'];
    
    $connection = DatabaseConnection::connect();

    // $ip_manager = new IpAddress();
    // $ip_address = IpAddress::getIpAddress();

    // isIpAddressBlocked($ip_manager, $ip_address, $time);

    $sql = "SELECT verification_token FROM voter WHERE voter_id = ? AND BINARY email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("is", $voter_id, $email);
    $stmt->execute();
    $stmt->bind_result($fetched_verified_token);
    $stmt->fetch();
    $stmt->close();

    if($fetched_verified_token === $verification_token) {
        // $ip_manager->deleteIpAddress($ip_address);

        $sql = "UPDATE voter SET verification_token = NULL WHERE voter_id = ? AND BINARY email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("is", $voter_id, $email);
        $stmt->execute(); 
        $stmt->close();
        encodeJSONResponse(['success' => true]);
    }
    else {
        // $ip_manager->storeIpAddress($ip_address, time());

        // isIpAddressBlocked($ip_manager, $ip_address, $time);
        // $count_attempts = $ip_manager->countIpAddressAttempt($ip_address, $time);

        // $remaining_attempts = ATTEMPTS_LIMIT - $count_attempts;
        // encodeJSONResponse(['message' => 'Incorrect token. Attempts left: ' . $remaining_attempts]);
        encodeJSONResponse(['message' => 'Incorrect token. Try again']);      
    }
}

// Check if a user of a certain ip address exceeds attempt limit within 30 minutes
// function isIpAddressBlocked($ip_manager, $ip_address, $time) {
//     $count_attempts = $ip_manager->countIpAddressAttempt($ip_address, $time);

//     if($count_attempts >= ATTEMPTS_LIMIT) {
//         $_SESSION['time'] = $time;
//         $_SESSION['isBlocked'] = true;
//         encodeJSONResponse(['maxLimit' => true]);
//     }
// }

function encodeJSONResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}