<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/session-manager.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/session-exchange.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/logger.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/default-time-zone.php');

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST['token'];
    $email = trim($_POST['email']);
    $password_confirmation = trim($_POST['password_confirmation']);
    $token_hash = hash("sha256", $token);

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email format.']);
        exit();
    }

    $connection = DatabaseConnection::connect();

    // Check if the reset token exists and is valid
    $sql = "SELECT * FROM voter WHERE reset_token_hash = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        echo json_encode(['status' => 'error', 'message' => 'Your email change link was not found.']);
        exit();
    }

    // Check if the reset token has expired
    $expiry_time = strtotime($row["reset_token_expires_at"]);
    $current_time = time();

    if ($expiry_time <= $current_time) {
        echo json_encode(['status' => 'error', 'message' => 'Your email change link has expired.']);
        exit();
    }

    // Retrieve the current password hash from the database
    $current_password_hash = $row['password'];

    // Verify if the password_confirmation matches the current password
    if (!password_verify($password_confirmation, $current_password_hash)) {
        echo json_encode(['status' => 'error', 'error' => 'incorrect_password', 'message' => 'Incorrect password. Please try again.']);
        exit();
    }

    // Check if the new email is already used by another user
    $sql_check_email = "SELECT * FROM voter WHERE email = ?";
    $stmt_check_email = $connection->prepare($sql_check_email);
    $stmt_check_email->bind_param("s", $email);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();
    $existing_user = $result_check_email->fetch_assoc();

    if ($existing_user) {
        echo json_encode(['status' => 'error', 'error' => 'email_in_use', 'message' => 'Email address is already in use. Please choose another one.']);
        exit();
    }

    // Update the email address in the current database
    $sql_update = "UPDATE voter SET email = ? WHERE reset_token_hash = ?";
    $stmt_update = $connection->prepare($sql_update);
    $stmt_update->bind_param('ss', $email, $token_hash);
    $success = $stmt_update->execute();

    $organization= 'sco';

    if ($success) {
        // Update the email address as well in the SCO's database
        $config = DatabaseConfig::getOrganizationDBConfig($organization);
        $org_connection = new \mysqli($config['host'], $config['username'], $config['password'], $config['database']);

        if ($org_connection->connect_error) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to connect to organization database.']);
            exit();
        }

        $sql_update_org = "UPDATE voter SET email = ? WHERE email = ?";
        $stmt_update_org = $org_connection->prepare($sql_update_org);
        $stmt_update_org->bind_param('ss', $email, $row['email']);
        $success_org = $stmt_update_org->execute();

        if ($success_org) {

            $logger = new Logger(ROLE_STUDENT_VOTER, UPDATE_EMAIL);
            $logger->logActivity();

            echo json_encode(['status' => 'success']);
            exit();
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update your email in the organization database.']);
            exit();
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update your email. Please try again.']);
        exit();
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}
?>
