<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/registration-class.php');
require_once FileUtils::normalizeFilePath('classes/csrf-token.php');
require_once FileUtils::normalizeFilePath('error-reporting.php');

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["sign-up"])) {

    if(!CsrfToken::validateCSRFToken()) {
        $_SESSION['error_message'] = 'Something went wrong. Please reload the page.';
        header("Location: ../register.php");
        exit();
    }

    $student_number = trim($_POST['student_number']);
    $first_name = trim($_POST['first_name']);
    $middle_name = trim($_POST['middle_name']);
    $last_name = trim($_POST['last_name']);
    $suffix = trim($_POST['suffix']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $retype_password = trim($_POST['retype-pass']);
    $organization = $_POST['org'];

    $process_registration = new Registration($student_number, $first_name, $middle_name, $last_name, $suffix, $email, $password, $retype_password, $organization);
    $process_registration->processRegistrationCredentials();
}
