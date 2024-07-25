<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath('session-handler.php');
require_once FileUtils::normalizeFilePath('classes/db-config.php');
include_once FileUtils::normalizeFilePath('error-reporting.php');
include_once FileUtils::normalizeFilePath('default-time-zone.php');

$organizations = array('sco', 'acap', 'aeces', 'elite', 'give', 'jehra', 'jmap', 'jpia', 'piie');
$response = array();

foreach ($organizations as $organization) {
    $config = DatabaseConfig::getOrganizationDBConfig($organization);
    
    $connection = new mysqli(
        $config['host'],
        $config['username'],
        $config['password'],
        $config['database']
    );

    if ($connection->connect_error) {
        die($connection->connect_error);
    }

    $sql = "SELECT start, close FROM registration_schedule ORDER BY schedule_id DESC LIMIT 1";
    $stmt = $connection->prepare($sql);	
    $stmt->execute();	
    $result = $stmt->get_result();	

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $today = new DateTime();	
        $start = new Datetime($row['start']);	
        $close = new DateTime($row['close']);

        if($today >= $start && $today <= $close) {                
            $response[$organization] = 'open';
        }
        else {
            $response[$organization] = 'closed';
        }		
    }
    else {
        $response[$organization] = 'closed';
    }

    $connection->close();
}

header('Content-Type: application/json');
echo json_encode($response);