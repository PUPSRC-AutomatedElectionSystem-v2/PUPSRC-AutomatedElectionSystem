<?php

include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/logger.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/manage-ip-address.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/../default-time-zone.php');

class FormHandler {
    private $conn;
    private $logger;

    public function __construct() {
        $this->conn = DatabaseConnection::connect();
    }

    public function processForm($postData, $fileData) {
        // Retrieve form data
        $voter_id = $postData['voter_id'];

        // Fetch user data based on voter ID
        $row = $this->getUserData($voter_id);

        if ($row) {
            // Check if organization is selected
            if (isset($postData["org"]) && !empty($postData["org"])) {
                $org = $postData["org"];
                $this->processOrganization($org, $fileData, $row, $voter_id);
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

    private function processOrganization($org, $fileData, $row, $voter_id) {
        $config = DatabaseConfig::getOrganizationDBConfig($org);
        $connection = new \mysqli($config['host'], $config['username'], $config['password'], $config['database']);

        if ($connection->connect_error) {
            die("Connection failed: " . $connection->connect_error);
        }

        if (isset($fileData["cor"]) && $fileData["cor"]["error"] == UPLOAD_ERR_OK) {
            $cor_file = $fileData["cor"];
            $upload_directory = "../user_data/$org/cor/";
            $filename = basename($cor_file["name"]);
            $target_file = $upload_directory . $filename;

            if (move_uploaded_file($cor_file["tmp_name"], $target_file)) {
                $this->insertVoterData($connection, $filename, $row);
                $this->updateScoDatabase($filename, $target_file, $row['email']);
                $this->invalidatePreviousVoterEntry($voter_id);

                $this->logger = new Logger(ROLE_STUDENT_VOTER, TRANSFER_ORG);
                $this->logger->logActivity();
            } else {
                // echo "Error: Failed to move uploaded file.";
            }
        } else {
            // echo "Error: File upload failed with error code " . $fileData["cor"]["error"];
        }

        $connection->close();
    }

    private function insertVoterData($connection, $filename, $row) {
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

        $sql = "INSERT INTO voter (last_name, first_name, middle_name, suffix, year_level, section, email, password, 
                role, voter_status, vote_status, vote_status_updated, cor, account_status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("ssssssssssssss", $last_name, $first_name, $middle_name, $suffix, $year_level, $section, $email, $password, 
                          $role, $voter_status, $vote_status, $vote_status_updated, $filename, $account_status);

        if ($stmt->execute()) {
            // echo "Success: Data inserted successfully for voter ID $voter_id.";
        } else {
            // echo "Error: Data insertion failed for voter ID $voter_id.";
        }

        $stmt->close();
    }

    private function invalidatePreviousVoterEntry($voter_id) {
        // Prepare the update statement to set account_status to 'invalid'
        $stmt_update = $this->conn->prepare("UPDATE voter SET account_status = 'invalid' WHERE voter_id = ?");
        $stmt_update->bind_param('s', $voter_id);

        if ($stmt_update->execute()) {
            // echo "Success: Account status set to 'invalid' for voter ID $voter_id.";
        } else {
            // echo "Error: Failed to set account status to 'invalid' for voter ID $voter_id.";
        }

        $stmt_update->close();
    }

    private function updateScoDatabase($filename, $target_file, $email) {
        $sco_organization = 'sco';
        $config_sco = DatabaseConfig::getOrganizationDBConfig($sco_organization);
        $org_connection = new \mysqli($config_sco['host'], $config_sco['username'], $config_sco['password'], $config_sco['database']);

        if ($org_connection->connect_error) {
            die("Connection failed: " . $org_connection->connect_error);
        }

        // also need to upload the file in the sco
        $upload_directory_sco = "../user_data/$sco_organization/cor/";
        $target_file_sco = $upload_directory_sco . $filename;

        if (copy($target_file, $target_file_sco)) {
            $sql = "UPDATE voter SET cor = ? WHERE email = ?";
            $stmt = $org_connection->prepare($sql);
            $stmt->bind_param('ss', $filename, $email);

            if ($stmt->execute()) {
                // echo "Success: cor updated in sco database for email $email.";
            } else {
                // echo "Error: Failed to update cor in sco database for email $email.";
            }

            $stmt->close();
        } else {
            // echo "Error: Failed to copy uploaded file to SCO directory.";
        }

        $org_connection->close();
    }
}

?>
