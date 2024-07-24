<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-config.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/default-time-zone.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/session-exchange.php');

if($_SERVER["REQUEST_METHOD"] === "POST") {

    $verification_token = $_POST['verificationToken'];
    $password = trim($_POST['password']);
    $password_confirmation = trim($_POST['passwordConfirmation']);
    $verification_token_hash = hash("sha256", $verification_token);

    $error = newPasswordValidation($password);

    if ($error) {
        setErrorAndRedirect($error, $verification_token, $org_name);
    }

    if ($password !== $password_confirmation) {
        setErrorAndRedirect('Your passwords do not match.', $verification_token, $org_name);
    }

    $connection = DatabaseConnection::connect();

    $sql = "SELECT * FROM voter WHERE verification_token = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $verification_token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        setErrorAndRedirect('Your account setup link was not found.', NULL, NULL, '../member-masterlist.php');
    }

    $new_password = password_hash($password, PASSWORD_DEFAULT);

    $success = updatePassword($connection, $new_password, $row['email']);

    if($_SESSION['organization'] !== 'sco') {
        $sco = 'sco';
        $config = DatabaseConfig::getOrganizationDBConfig($sco);
        $sco_connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

        $success &= updatePassword($sco_connection, $new_password, $row['email']);        
    }

    if ($success) {
        echo json_encode(['success' => true]);
    } 
    else {
        setErrorAndRedirect("Failed to create your password. Please try again.", $verification_token, $org_name);
    }

    exit();
}

// Function to validate new password
function newPasswordValidation($password) {
    $password_regex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[^\s]).{8,20}$/';

    if (!preg_match($password_regex, $password)) {
        return "Password must be 8-20 characters with a number, an uppercase letter, a lowercase letter, a special character, and with no whitespace/s.";
    }
    return "";
}


function updatePassword($connection, $new_password, $email) {
    $verified_status = 'verified';
    $sql = "UPDATE voter SET password = ?, account_status = ?, verification_token = NULL WHERE BINARY email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('sss', $new_password, $verified_status, $email);
    return $stmt->execute();
}


function setErrorAndRedirect($message, $verification_token, $org_name, $redirect_url = '../create-password.php') {
    $_SESSION['error_message'] = $message;
    if ($verification_token) {
        $redirect_url .= "?token=" . urlencode($verification_token) . "&orgName=" . urlencode($org_name);
    }
    header("Location: $redirect_url");
    exit();
}