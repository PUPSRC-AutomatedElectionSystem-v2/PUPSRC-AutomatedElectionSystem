<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/logger.php');
//require_once FileUtils::normalizeFilePath(__DIR__ . '/../session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../error-reporting.php');

class FormHandler {
    private $conn;
    private $logger;

    public function __construct() {
        $this->conn = DatabaseConnection::connect();
    }

    public function processForm($postData) {
        // Retrieve form data
        $voter_id = $postData['voter_id'];

        // Fetch user data based on voter ID
        $row = $this->getUserData($voter_id);

        if ($row) {
            // Check if organization is selected
            if (isset($postData["org"]) && !empty($postData["org"])) {
                $org = $postData["org"];
                $this->processOrganization($org, $row, $voter_id);
            } else {
                // Organization is not selected, display an error message or handle it as needed
                // echo "Please select an organization.";
            }
        } else {
            // echo "Error: Voter ID not found.";
        }
    }

    protected function getUserData($voter_id) {
        $stmt = $this->conn->prepare("SELECT * FROM voter WHERE voter_id = ?");
        $stmt->bind_param('s', $voter_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();
        return $row;
    }

    private function processOrganization($org, $row, $voter_id) {
        $config = DatabaseConfig::getOrganizationDBConfig($org);
        $connection = new \mysqli($config['host'], $config['username'], $config['password'], $config['database']);

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        $this->insertVoterData($connection, $row);
        $this->invalidatePreviousVoterEntry($voter_id);

        $this->logger = new Logger(ROLE_STUDENT_VOTER, TRANSFER_ORG);
        $this->logger->logActivity();

        $connection->close();
    }

    private function insertVoterData($connection, $row) {
        $student_number = $row['student_number'] ?? null; 
        $last_name = $row['last_name'] ?? null;
        $first_name = $row['first_name'] ?? null;
        $middle_name = $row['middle_name'] ?? null;
        $suffix = $row['suffix'] ?? null;
        $year_level = $row['year_level'] ?? null;
        $section = $row['section'] ?? null;
        $email = $row['email'] ?? null;
        $password = $row['password'] ?? null;
        $role = $row['role'] ?? null;
        $voter_status = $row['voter_status'] ?? null;
        $vote_status = $row['vote_status'] ?? null;
        $vote_status_updated = $row['vote_status_updated'] ?? null;
        $account_status = 'for_verification';

        $sql = "INSERT INTO voter (student_number, last_name, first_name, middle_name, suffix, year_level, section, email, password, 
                role, voter_status, vote_status, vote_status_updated, account_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssssssssssssss", $student_number, $last_name, $first_name, $middle_name, $suffix, $year_level, $section, $email, $password, 
                          $role, $voter_status, $vote_status, $vote_status_updated, $account_status);

        $stmt->execute();
        $stmt->close();
    }

    private function invalidatePreviousVoterEntry($voter_id) {
        // Prepare the update statement to set account_status to 'invalid'
        $stmt_update = $this->conn->prepare("UPDATE voter SET account_status = 'invalid' WHERE voter_id = ?");
        $stmt_update->bind_param('s', $voter_id);
        $stmt_update->execute();
        $stmt_update->close();
    }
}

?>