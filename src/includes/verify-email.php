<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/db-connector.php');
require_once FileUtils::normalizeFilePath('classes/manage-ip-address.php');
require_once FileUtils::normalizeFilePath('mailer.php');
require_once FileUtils::normalizeFilePath('classes/email-sender.php');
// require_once FileUtils::normalizeFilePath('classes/csrf-token.php');
include_once FileUtils::normalizeFilePath('default-time-zone.php');
include_once FileUtils::normalizeFilePath('error-reporting.php');


ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);


const ATTEMPTS_LIMIT = 5;
const BLOCK_TIME = 1800; // 30 mins
$time = time() - BLOCK_TIME;

$response = ['success' => false, 'maxLimit' => false, 'message' => 'An error occurred'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['emailVal'];
    $voter_id = $_POST['voterIdVal'];

    $connection = DatabaseConnection::connect();

    $ip_manager = new IpAddress();
    $ip_address = IpAddress::getIpAddress();

    isIpAddressBlocked($ip_manager, $ip_address, $time);

    $sql = "SELECT voter_id FROM voter WHERE BINARY email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 0) {
        $ip_manager->storeIpAddress($ip_address, time());

        isIpAddressBlocked($ip_manager, $ip_address, $time);
        $count_attempts = $ip_manager->countIpAddressAttempt($ip_address, $time);

        $remaining_attempts = ATTEMPTS_LIMIT - $count_attempts;
        encodeJSONResponse(['message' => 'Incorrect email address. Attempts left: ' . $remaining_attempts]);
    }
    else {
        $ip_manager->deleteIpAddress($ip_address);
        $verification_token = bin2hex(random_bytes(32));
        // $hashed_verification_token = hash("sha256", $verification_token);

        // verification token available only in 30 mins
        $duration = time() + (60 * 30);
        $expiry = date("Y-m-d H:i:s", $duration);

        $sql = "UPDATE voter SET verification_token = ? WHERE BINARY email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ss", $verification_token, $email);
        $stmt->execute();

        if($stmt->affected_rows) {
            $send_verification_token = new EmailSender($mail);
            $send_verification_token->sendVerificationToken($email, $verification_token);
            encodeJSONResponse(['success' => true]);
        }
        else {
            encodeJSONResponse(['message' => 'Something went wrong. Please try again.']);
        }
    } 
}

// Check if a user of a certain ip address exceeds attempt limit within 30 minutes
function isIpAddressBlocked($ip_manager, $ip_address, $time) {
    $count_attempts = $ip_manager->countIpAddressAttempt($ip_address, $time);

    if($count_attempts >= ATTEMPTS_LIMIT) {
        $_SESSION['time'] = $time;
        $_SESSION['isBlocked'] = true;
        encodeJSONResponse(['maxLimit' => true]);
    }
}

function encodeJSONResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}