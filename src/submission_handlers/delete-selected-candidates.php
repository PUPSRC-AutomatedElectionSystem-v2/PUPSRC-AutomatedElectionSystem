<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, '../includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('../includes/classes/db-connector.php');
require_once FileUtils::normalizeFilePath('../includes/session-handler.php');
require_once FileUtils::normalizeFilePath('../includes/classes/logger.php'); // Add logger
require_once FileUtils::normalizeFilePath('../includes/classes/query-handler.php');

// Check if the request is POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if the 'ids' parameter is set in the POST request
    if (isset($_POST['ids']) && is_array($_POST['ids'])) {
        $ids = $_POST['ids'];
        
        // Establish database connection
        $conn = DatabaseConnection::connect();
        if ($conn->connect_error) {
            echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
            exit;
        }

        // Initialize logger based on the number of IDs
        if (count($ids) == 1) {
            $logger = new Logger($_SESSION['role'], PERMANENT_DELETE_CANDIDATE);
        } else {
            $logger = new Logger($_SESSION['role'], PERMANENT_DELETE_MULTIPLE_CANDIDATES);
        }
        $logger->logActivity();
        
        // Initialize the query executor
        $queryExecutor = new QueryExecutor($conn);
        
        // Prepare the SQL statement to delete votes for selected items
        $deleteVotesQuery = "DELETE FROM vote WHERE candidate_id IN (";
        $params = array();
        foreach ($ids as $id) {
            if (is_numeric($id)) { // Ensure the ID is numeric
                $deleteVotesQuery .= "?, ";
                $params[] = (int)$id;
            }
        }
        // Replace the last comma and space with a closing parenthesis
        $deleteVotesQuery = rtrim($deleteVotesQuery, ", ") . ")";
        
        if (empty($params)) {
            echo json_encode(['error' => 'No valid IDs provided for deletion']);
            exit;
        }

        // Execute the delete votes query
        $stmt = $conn->prepare($deleteVotesQuery);
        if ($stmt === false) {
            echo json_encode(['error' => 'Failed to prepare delete votes statement: ' . $conn->error]);
            exit;
        }

        $stmt->bind_param(str_repeat("i", count($params)), ...$params); // Bind parameters dynamically
        if ($stmt->execute()) {
            // Prepare the SQL statement to delete selected items
            $deleteCandidatesQuery = "DELETE FROM candidate WHERE candidate_id IN (" . implode(", ", array_fill(0, count($params), "?")) . ")";
            
            // Execute the delete candidates query
            $stmt = $conn->prepare($deleteCandidatesQuery);
            if ($stmt === false) {
                echo json_encode(['error' => 'Failed to prepare delete candidates statement: ' . $conn->error]);
                exit;
            }

            $stmt->bind_param(str_repeat("i", count($params)), ...$params); // Bind parameters dynamically
            if ($stmt->execute()) {
                // Check if any rows were affected
                if ($stmt->affected_rows > 0) {
                    echo json_encode(['success' => true, 'message' => 'Selected items deleted successfully']);
                } else {
                    echo json_encode(['error' => 'No items deleted']);
                }
            } else {
                echo json_encode(['error' => 'Failed to execute delete candidates statement: ' . $stmt->error]);
            }
        } else {
            echo json_encode(['error' => 'Failed to execute delete votes statement: ' . $stmt->error]);
        }
        
        // Close statement and connection
        $stmt->close();
        $conn->close();
    } else {
        // Return an error response if 'ids' parameter is not set
        echo json_encode(['error' => 'No IDs provided for deletion or invalid format']);
    }
} else {
    // Return an error response if the request method is not POST
    echo json_encode(['error' => 'Invalid request method']);
}
?>
