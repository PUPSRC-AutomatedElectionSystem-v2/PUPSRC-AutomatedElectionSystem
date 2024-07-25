<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, '../includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('../includes/classes/db-connector.php');
require_once FileUtils::normalizeFilePath('../includes/session-handler.php');
require_once FileUtils::normalizeFilePath('../includes/classes/session-manager.php');
require_once FileUtils::normalizeFilePath('../includes/classes/query-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/classes/logger.php');

if (isset($_POST['ids']) && is_array($_POST['ids'])) {
    $voterManager = new VoterManager();
    $ids = $_POST['ids'];
    
    // Convert array to a comma-separated string and sanitize the values
    $idsString = implode(',', array_map('intval', $ids));

    // Update query to mark the candidates as invalid
    $inactive_query = "UPDATE candidate SET candidacy_status = 'Invalid' WHERE candidate_id IN ($idsString)";
    $stmt = $voterManager->prepare($inactive_query);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
        if (count($ids) === 1) {
            $logger = new Logger($_SESSION['role'], DELETE_CANDIDATE);
            $logger->logActivity();
        } else {
            $logger = new Logger($_SESSION['role'], DELETE_MULTIPLE_CANDIDATES);
            $logger->logActivity();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update records']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No valid candidate provided']);
}
?>
