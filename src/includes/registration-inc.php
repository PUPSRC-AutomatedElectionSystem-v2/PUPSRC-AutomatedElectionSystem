<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/registration-class.php');
require_once FileUtils::normalizeFilePath('classes/csrf-token.php');
require_once FileUtils::normalizeFilePath('error-reporting.php');

// Google reCaptcha credentials
$secret_key = '6LeIiRoqAAAAAKsCP5vR2Hds1-wQkFwzO5OyBJF0';
$api_url = 'https://www.google.com/recaptcha/api/siteverify';

if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["sign-up"])) {

    if(!CsrfToken::validateCSRFToken()) {
        $_SESSION['error_message'] = 'Something went wrong. Please reload the page.';
        header("Location: ../register.php");
        exit();
    }

    // recaptcha validation
    if(isset($_POST['g-recaptcha-response']) && !empty($_POST['g-recaptcha-response'])) {
        $recaptcha = $_POST['g-recaptcha-response'];    
        $request = file_get_contents($api_url . '?secret=' . $secret_key . '&response=' . $recaptcha);
        $response = json_decode($request);
        
        if($response->success == false) {
            $_SESSION['error_message'] = 'Captcha verification failed. Please try again.';
            header("Location: ../register.php");
            exit();
        }
    }

    // proceeds with registration credential processing
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
