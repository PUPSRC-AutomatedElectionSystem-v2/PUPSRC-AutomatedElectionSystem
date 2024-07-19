<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/file-utils.php');
require_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/db-connector.php');
include_once str_replace('/', DIRECTORY_SEPARATOR, 'error-reporting.php');
require_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/position-manager.php');

// ------ SESSION EXCHANGE ------
include FileUtils::normalizeFilePath('session-exchange.php');
// ------ END OF SESSION EXCHANGE -----

// Fetch organization name
$org_name = $_SESSION['organization'];

header('Content-Type: application/json');

try {
    // Establish database connection
    $connection = DatabaseConnection::connect();
    if ($connection->connect_error) {
        throw new Exception('Database connection error: ' . $connection->connect_error);
    }

    // Initialize PositionManager
    $positionManager = new PositionManager($connection);

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['positionId'])) {
        // Fetch position details and candidates for a specific position
        $positionId = $_GET['positionId'];

        // Fetch position details
        $stmt = $connection->prepare("SELECT title, description FROM position WHERE position_id = ?");
        $stmt->bind_param("i", $positionId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $position = $result->fetch_assoc();

            // Fetch candidates related to position_id
            $stmt = $connection->prepare("SELECT first_name, last_name, photo_url FROM candidate WHERE position_id = ?");
            $stmt->bind_param("i", $positionId);
            $stmt->execute();
            $candidates_result = $stmt->get_result();
            $candidates = [];
            while ($row = $candidates_result->fetch_assoc()) {
                $candidates[] = $row;
            }

            // Prepare JSON response
            $response = [
                'title' => $position['title'],
                'description' => $position['description'],
                'candidates' => $candidates,
                'org_name' => $org_name,
            ];
        } else {
            // No data found for the position id
            $response = ['error' => 'No data found for position id ' . $positionId];
        }

        $stmt->close();
        echo json_encode($response);
        exit;

    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Retrieve search and pagination parameters
        $searchQuery = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
        $current_page = isset($_POST['page']) ? (int)$_POST['page'] : 1;
        $records_per_page = 5;
        $offset = ($current_page - 1) * $records_per_page;

        // Fetch position data and total record count using PositionManager methods
        $positions_tbl = $positionManager->getPositionData($searchQuery, $offset, $records_per_page);
        $total_records = $positionManager->getTotalRecords($searchQuery);
        $total_pages = ceil($total_records / $records_per_page);

        // Prepare position data array
        $position_data = [];
        while ($row = $positions_tbl->fetch_assoc()) {
            $position_data[] = $row;
        }

        // Check if position_data is empty
        if (empty($position_data)) {
            echo json_encode(['empty_state' => true]);
            exit;
        }

        // Prepare JSON response
        $response = [
            'position_data' => $position_data,
            'total_pages' => $total_pages,
            'current_page' => $current_page
        ];

        echo json_encode($response);
        exit;
    } else {
        echo json_encode(['error' => 'Invalid request']);
        exit;
    }

} catch (Exception $e) {
    // Handle errors and return error response
    http_response_code(500);
    $error_response = ['error' => $e->getMessage()];
    echo json_encode($error_response);
}
?>
