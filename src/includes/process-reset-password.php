<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-config.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/default-time-zone.php');

if($_SERVER["REQUEST_METHOD"] === "POST") {

    $token = $_POST['token'];
    $password = trim($_POST['password']);
    $password_confirmation = trim($_POST['password_confirmation']);
    $token_hash = hash("sha256", $token);

    $error = newPasswordValidation($password);

    if ($error) {
        setErrorAndRedirect($error, $token, $org_name);
    }

    if ($password !== $password_confirmation) {
        setErrorAndRedirect('Your passwords do not match.', $token, $org_name);
    }

    $connection = DatabaseConnection::connect();

    $sql = "SELECT * FROM voter WHERE reset_token_hash = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $token_hash);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if (!$row) {
        setErrorAndRedirect('Your password reset link was not found.', NULL, NULL, '../voter-login.php');
    }

    $expiry_time = strtotime($row["reset_token_expires_at"]);
    $current_time = time();

    if ($expiry_time <= $current_time) {
        setErrorAndRedirect('Your password reset link has expired.', NULL, NULL, '../voter-login.php');
    }

    $new_password = password_hash($password_confirmation, PASSWORD_DEFAULT);

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
        setErrorAndRedirect("Failed to reset your password. Please try again.", $token, $org_name);
    }

    exit();
}

// Function to validate new password
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


function updatePassword($connection, $new_password, $email) {
    $sql = "UPDATE voter SET password = ?, reset_token_hash = NULL, reset_token_expires_at = NULL WHERE BINARY email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('ss', $new_password, $email);
    return $stmt->execute();
}


function setErrorAndRedirect($message, $token, $org_name, $redirect_url = '../reset-password.php') {
    $_SESSION['error_message'] = $message;
    if ($token) {
        $redirect_url .= "?token=" . urlencode($token) . "&orgName=" . urlencode($org_name);
    }
    header("Location: $redirect_url");
    exit();
}