<?php

include_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/file-utils.php');
require_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/db-connector.php');
include_once str_replace('/', DIRECTORY_SEPARATOR, 'error-reporting.php');
require_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/feedback-manager.php');

try {
    // Establish database connection
    $connection = DatabaseConnection::connect();
    if ($connection->connect_error) {
        throw new Exception('Database connection error: ' . $connection->connect_error);
    }

    // Initialize FeedbackManager
    $feedbackManager = new FeedbackManager($connection);

    // Retrieve sort and pagination parameters
    $sort = isset($_POST['sort']) ? $_POST['sort'] : 'timestamp';
    $order = isset($_POST['order']) ? $_POST['order'] : 'DESC';
    $current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
    $records_per_page = 5;
    $offset = ($current_page - 1) * $records_per_page;

    // Fetch feedback data and total record count
    $feedback_tbl = $feedbackManager->getFeedbackData($sort, $order, $offset, $records_per_page);
    $total_records = $feedbackManager->getTotalRecords();
    $total_pages = ceil($total_records / $records_per_page);

    // Prepare feedback data array
    $feedback_data = [];
    while ($row = $feedback_tbl->fetch_assoc()) {
        $feedback_data[] = $row;
    }

    // Prepare JSON response
    $response = [
        'feedback_data' => $feedback_data,
        'total_pages' => $total_pages,
        'current_page' => $current_page
    ];

    // Set content type and return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    // Handle errors and return error response
    http_response_code(500);
    $error_response = ['error' => $e->getMessage()];
    echo json_encode($error_response);
}
