<?php
// Include necessary files using DIRECTORY_SEPARATOR for cross-platform compatibility
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/session-manager.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/session-exchange.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/default-time-zone.php');
require_once FileUtils::normalizeFilePath('classes/logger.php');

// Check if the request method is POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize and trim input data
    $password = trim($_POST['password']);
    $password_confirmation = trim($_POST['password_confirmation']);
    $voter_id = trim($_POST['voter_id']);

    // Check if password and password_confirmation match
    if ($password !== $password_confirmation) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: ../setting-password-reset");
        exit();
    }

    // Validate new password
    $error = newPasswordValidation($password);
    if ($error) {
        $_SESSION['error_message'] = $error;
        header("Location: ../setting-password-reset");
        exit();
    }

    // Fetch user data based on voter ID
    $userData = getUserData($voter_id);
    if (!$userData) {
        $_SESSION['error_message'] = "User not found.";
        header("Location: ../setting-password-reset");
        exit();
    }
    $email = $userData['email'];

    // Connect to database
    $connection = DatabaseConnection::connect();

    // Hash the new password
    $new_password = password_hash($password, PASSWORD_DEFAULT);

    // Update password in the database
    $sql = "UPDATE voter SET password = ? WHERE voter_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("si", $new_password, $voter_id);

    if ($stmt->execute()) {
        // Update password in another database
        updateAnotherDatabasePassword($email, $new_password);
        
        echo json_encode(['success' => true]);
        
        $logger = new Logger(ROLE_STUDENT_VOTER, CHANGE_PASSWORD);
        $logger->logActivity();
        exit();  
    } else {
        $_SESSION['error_message'] = "Failed to reset your password. Please try again.";
        header("Location: ../setting-password-reset");
        exit();
    }
}

function getUserData($voter_id) {
    // Connect to database
    $connection = DatabaseConnection::connect();

    $sql = "SELECT email FROM voter WHERE voter_id = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('i', $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    return $row;
}

// Function to update password in another database
function updateAnotherDatabasePassword($email, $new_password) {
    $sco_organization = 'sco';
    $config_sco = DatabaseConfig::getOrganizationDBConfig($sco_organization);
    $sco_connection = new \mysqli($config_sco['host'], $config_sco['username'], $config_sco['password'], $config_sco['database']);

    if ($sco_connection->connect_error) {
        die("Connection failed: " . $sco_connection->connect_error);
    }

    $sql = "UPDATE voter SET password = ? WHERE email = ?";
    $stmt = $sco_connection->prepare($sql);
    $stmt->bind_param('ss', $new_password, $email);

    if ($stmt->execute()) {
        // Success message or logging can be added here
    } else {
        // Error handling can be added here
    }

    $stmt->close();
    $sco_connection->close();
}

// Validate new password function
function newPasswordValidation($password) {
    if (strlen($password) < 8 || strlen($password) > 20) {
        return "Your password must be between 8 and 20 characters long.";
    }
    if (!preg_match("/\d/", $password)) {
        return "Your password must contain at least 1 number.";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        return "Your password must contain at least 1 uppercase letter.";
    }
    if (!preg_match("/[a-z]/", $password)) {
        return "Your password must contain at least 1 lowercase letter.";
    }
    if (!preg_match("/[\W_]/", $password)) {
        return "Your password must contain at least 1 special character.";
    }
    if (preg_match("/\s/", $password)) {
        return "Your password must not contain any spaces.";
    }
    return "";
}