<?php

include_once str_replace('/', DIRECTORY_SEPARATOR, 'classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/session-manager.php');
require_once FileUtils::normalizeFilePath('classes/db-connector.php');
include_once FileUtils::normalizeFilePath('error-reporting.php');

// ini_set('error_reporting', E_ALL);
// ini_set('display_errors', 1);
// ini_set('log_errors', 1);

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $connection = DatabaseConnection::connect();
    $pending_setup_status = 'pending_setup';
    $student_voter = 'student_voter';
    $action = isset($_POST['actionVal']) ? $_POST['actionVal'] : '';

    if ($action === 'loadPagination') {
        $sql = "SELECT COUNT(*) AS total FROM voter WHERE role = ? AND account_status = ?";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ss", $student_voter, $pending_setup_status);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo json_encode(['total' => $row['total']]);
    }
    elseif ($action === 'searchName') {
        $search_query = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
        $page_start = isset($_POST['pageStartVal']) ? (int)$_POST['pageStartVal'] : 0;
        $page_limit = isset($_POST['pageLimitVal']) ? (int)$_POST['pageLimitVal'] : 5;
        $search_term = "%" . strtolower($search_query) . "%";

        $sql = "
            SELECT voter_id, CONCAT(
                IFNULL(first_name, ''), ' ',
                IFNULL(middle_name, ''), ' ',
                IFNULL(last_name, ''), ' ',
                IFNULL(suffix, '')
            ) AS full_name, verification_token
            FROM voter
            WHERE role = ? AND account_status = ? 
            AND LOWER(CONCAT(
                IFNULL(first_name, ''), ' ',
                IFNULL(middle_name, ''), ' ',
                IFNULL(last_name, ''), ' ',
                IFNULL(suffix, '')
            )) LIKE ?
            LIMIT ?, ?
        ";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sssii", $student_voter, $pending_setup_status, $search_term, $page_start, $page_limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    }
    elseif ($action === 'searchPagination') {
        $search_query = isset($_POST['searchQuery']) ? $_POST['searchQuery'] : '';
        $search_term = "%" . strtolower($search_query) . "%";

        $sql = "SELECT COUNT(*) AS total FROM voter WHERE role = ? AND account_status = ? AND LOWER(CONCAT(
            IFNULL(first_name, ''), ' ',
            IFNULL(middle_name, ''), ' ',
            IFNULL(last_name, ''), ' ',
            IFNULL(suffix, '')
        )) LIKE ?";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("sss", $student_voter, $pending_setup_status, $search_term);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        echo json_encode(['total' => $row['total']]);
    }
    elseif ($action === 'loadTableRows') {
        $page_start = isset($_POST['pageStartVal']) ? (int)$_POST['pageStartVal'] : 0;
        $page_limit = isset($_POST['pageLimitVal']) ? (int)$_POST['pageLimitVal'] : 5;

        $sql = "
            SELECT voter_id, CONCAT(
                IFNULL(first_name, ''), ' ',
                IFNULL(middle_name, ''), ' ',
                IFNULL(last_name, ''), ' ',
                IFNULL(suffix, '')
            ) AS full_name, verification_token
            FROM voter
            WHERE role = ? AND account_status = ?
            LIMIT ?, ?
        ";

        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssii", $student_voter, $pending_setup_status, $page_start, $page_limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
    }
}

