<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/db-connector.php');
require_once FileUtils::normalizeFilePath('classes/manage-ip-address.php');
include_once FileUtils::normalizeFilePath('error-reporting.php');
include_once FileUtils::normalizeFilePath('default-time-zone.php');

$response = ['tokenExist' => false];


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $voter_id = $_POST['voterIdVal'];

    $connection = DatabaseConnection::connect();

    $sql = "SELECT verification_token FROM voter WHERE voter_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $tokenExist = false;

    if ($row = $result->fetch_assoc()) {
        if (!empty($row['verification_token'])) {
            $tokenExist = true;
        }
    }

    $stmt->close();
    encodeJSONResponse(['tokenExist' => $tokenExist]);
}


function encodeJSONResponse($response) {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}