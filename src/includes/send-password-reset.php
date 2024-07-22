<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/mailer.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/email-sender.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');
// include_once FileUtils::normalizeFilePath(__DIR__. '/session-exchange.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/default-time-zone.php');

// Set initial value of success key to false
$response = ['success' => false, 'message' => 'An error occurred'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'] ?? NULL;

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please provide a valid email address';
        echo json_encode($response);
        exit();
    }

    $connection = DatabaseConnection::connect();

    $sql = "";
    $head_admin = 'head_admin';
    $admin = 'admin';

    if ($_SESSION['organization'] !== 'sco') {
        $sql = "SELECT email, account_status FROM voter WHERE BINARY email = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("s", $email);
    } else {
        $sql = "SELECT email, account_status FROM voter WHERE BINARY EMAIL = ? AND (role = ? OR role = ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sss", $email, $head_admin, $admin);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response['message'] = "We couldn't find your email address.";
        echo json_encode($response);
        exit();
    }

    $row = $result->fetch_assoc();

    if ($row['account_status'] == 'invalid') {
        $response['message'] = "We couldn't find your email address.";
        echo json_encode($response);
        exit();
    }

    $token = bin2hex(random_bytes(16));
    $token_hash = hash("sha256", $token);

    // Password reset link available only in 30 mins
    $duration = time() + (60 * 30);
    $expiry = date("Y-m-d H:i:s", $duration);

    $sql = "UPDATE voter SET reset_token_hash = ?, reset_token_expires_at = ? WHERE BINARY email = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param('sss', $token_hash, $expiry, $email);
    $stmt->execute();

    if ($stmt->affected_rows) {

        // Create an instance of EmailSender
        $send_email = new EmailSender($mail);
        $send_email->sendPasswordResetEmail($email, $token, $_SESSION['organization']);

        // Set value of success key to true
        $response['success'] = true;
        echo json_encode($response);
        exit();
    } else {
        $response['message'] = 'Something went wrong. Please try again.';
        echo json_encode($response);
        exit();
    }
}
