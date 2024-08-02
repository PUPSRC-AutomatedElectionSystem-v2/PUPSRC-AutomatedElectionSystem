<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/file-utils.php');
require_once FileUtils::normalizeFilePath('classes/db-connector.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/logger.php');

header('Content-Type: application/json');

// Establish database connection
$connection = DatabaseConnection::connect();

if (!$connection) {
    echo json_encode(['error' => 'Database connection failed']);
    exit();
}

try {
    // Query for fetching the voters
    $votersQuery = "SELECT 
                        student_id,
                        last_name,
                        first_name,
                        middle_name,
                        email,
                        suffix
                    FROM voter
                    WHERE account_status = 'verified'
                    AND role = 'student_voter'";

    $stmt = $connection->prepare($votersQuery);

    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'Student ID' => htmlspecialchars($row['student_id']),
                'Last Name' => htmlspecialchars($row['last_name']),
                'First Name' => htmlspecialchars($row['first_name']),
                'Middle Name' => htmlspecialchars($row['middle_name']),
                'Suffix' => htmlspecialchars($row['suffix']),
                'Email' => htmlspecialchars($row['email']),
            ];
        }

        $logger = new Logger($_SESSION['role'], EXPORT_MEMBER_LIST);
        $logger->logActivity();

        echo json_encode($data);
    } else {
        throw new Exception('Error preparing voters statement');
    }
} catch (Exception $e) {
    error_log($e->getMessage()); // Log error to the server's error log
    echo json_encode(['error' => $e->getMessage()]);
}
?>
