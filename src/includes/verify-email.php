<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/db-connector.php');
require_once FileUtils::normalizeFilePath('classes/manage-ip-address.php');
require_once FileUtils::normalizeFilePath('mailer.php');
require_once FileUtils::normalizeFilePath('classes/email-sender.php');
include_once FileUtils::normalizeFilePath('default-time-zone.php');
include_once FileUtils::normalizeFilePath('error-reporting.php');


$response = ['success' => false, 'message' => 'An error occurred'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['emailVal'];
    $voter_id = $_POST['voterIdVal'];

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        encodeJSONResponse(['message' => 'Please provide a valid email address.']);
    }

    $connection = DatabaseConnection::connect();

    // $ip_manager = new IpAddress();
    // $ip_address = IpAddress::getIpAddress();
    // isIpAddressBlocked($ip_manager, $ip_address, $time);

    $sql = "SELECT voter_id FROM voter WHERE voter_id = ? AND BINARY email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("is", $voter_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if($result->num_rows === 0) {
        encodeJSONResponse(['message' => 'Incorrect email address.']);
    }
    else {
        $verification_token = bin2hex(random_bytes(16));
        $verification_token_hash = hash("sha256", $verification_token);    

        $sql = "UPDATE voter SET verification_token = ? WHERE BINARY email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param('ss', $verification_token_hash, $email);
        $stmt->execute();

        if($stmt->affected_rows) {
            $send_setup_link = new EmailSender($mail);
            $send_setup_link->sendAccountSetupLink($email, $verification_token, $_SESSION['organization']);
            encodeJSONResponse(['success' => true]);
        }
        else {
            encodeJSONResponse(['message' => 'Something went wrong. Please try again.']);
        }
    } 
}

function encodeJSONResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}