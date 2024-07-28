<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');

$connection = DatabaseConnection::connect();

$sql = $connection->prepare("SELECT * FROM faq ORDER BY seq ASC");
$sql->execute();
$result = $sql->get_result();

$faqs = [];
while ($row = $result->fetch_assoc()) {
    $answer = $row['answer'];
    $decoded_answer = @unserialize($answer);
    if ($decoded_answer === false && $answer !== 'b:0;') {
        $decoded_answer = $answer;
    }
    $faqs[] = [
        'question' => $row['question'],
        'answer' => $decoded_answer
    ];
}

header('Content-Type: application/json');
echo json_encode(['faqs' => $faqs]);
