<?php

include_once str_replace('/', DIRECTORY_SEPARATOR, '../includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('../includes/classes/db-connector.php');

$conn = DatabaseConnection::connect();
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$query = "SELECT title FROM position";
$result = $conn->query($query);

if ($result === false) {
    die("Query failed: " . $conn->error);
}

$positions = [];
while ($row = $result->fetch_assoc()) {
    $positions[] = $row;
}

echo json_encode(['positions' => $positions]);
?>
