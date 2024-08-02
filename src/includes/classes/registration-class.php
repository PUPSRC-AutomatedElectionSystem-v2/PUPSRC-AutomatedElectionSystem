<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/db-config.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/../default-time-zone.php');

class Registration {
    private $student_number;
    private $first_name;
    private $last_name;
    private $middle_name;
    private $suffix;
    private $email;
    private $password;
    private $confirm_password;
    private $organization;
    private $connection;
    private $sco_connection;
    private const ACCOUNT_STATUS = 'for_verification';
    private const ROLE = 'student_voter';

    public function __construct($student_number, $first_name, $middle_name, $last_name, $suffix, $email, $password, $confirm_password, $organization) {
        $this->student_number = $student_number;
        $this->first_name = $first_name;
        $this->last_name = $last_name;
        $this->middle_name = $middle_name;
        $this->suffix = $suffix;
        $this->email = $email;
        $this->password = $password;
        $this->confirm_password = $confirm_password;
        $this->organization = $organization;

        $this->initializeDatabaseConnection();
        $this->initializeScoDatabaseConnection();
    }

    // Initialize database connection to organization db
    private function initializeDatabaseConnection() {
        $config = DatabaseConfig::getOrganizationDBConfig($this->organization);
        $this->connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    }

    // Initialize database connection to sco org db
    private function initializeScoDatabaseConnection() {
        $sco = 'sco';
        $config = DatabaseConfig::getOrganizationDBConfig($sco);
        $this->sco_connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
    }

    // Call every validation and insertion methods
    public function processRegistrationCredentials() {
        try {        
            unset($_SESSION['registration_success']);
            unset($_SESSION['error_message']);
            $this->validateStudentNumber();
            $this->validateEmailLength();
            $this->validateNamesLength();
            $this->validateNameFormat();
            $this->validateEmailFormat();
            $this->validateEmailNotExist();
            $this->validatePasswords();
            $this->beginTransaction();
            $this->insertIntoOrganizationDB();
            $this->insertIntoScoDB();
            $this->commitTransaction();
            $this->sendEmailNotice();
            $_SESSION['registration_success'] = true;
            header("Location: ../register.php");
            exit();
        }
        catch (Exception $e) {
            $this->rollbackTransaction();
            $error_message = $e->getMessage();
            $_SESSION['error_message'] = $error_message;
            header("Location: ../register.php");
            exit();
        }
    }

    
    // Validates student number
    private function validateStudentNumber() {
        $student_number_regex = '/^\d{4}-\d{5}-[A-Z]{2}-\d$/';

        if(empty($this->student_number)) {
            throw new Exception("Student number cannot be empty.");
        }

        $sql = "SELECT student_id FROM voter WHERE student_id = ?";
        $stmt = $this->sco_connection->prepare($sql);
        $stmt->bind_param("s", $this->student_number);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            throw new Exception("{$this->student_number} is already registered.");
        }
        $stmt->close();

        if(!preg_match($student_number_regex, $this->student_number)) {
            throw new Exception("Please follow the proper format for student number.");
        }
    }

    // Validate email and password string length
    private function validateEmailLength() {
        if (strlen($this->email) > 255) {
            throw new Exception("Email address must not exceed 255 characters");
        }
    }

    // Validates names string length
    private function validateNamesLength() {
        $max_length = 100;
        $suffix_max_length = 10;

        if (strlen($this->first_name) > $max_length) {
            throw new Exception("First name must not exceed 100 characters.");
        }

        if ($this->middle_name && strlen($this->middle_name) > $max_length) {
            throw new Exception("Middle name must not exceed 100 characters.");
        }
        else {
            // Assigns NULL to middle name if it's empty
            $this->middle_name = empty($this->middle_name) ? NULL : $this->middle_name;
        }
    
        if (strlen($this->last_name) > $max_length) {
            throw new Exception("Last name must not exceed 100 characters.");
        }

        if ($this->suffix && strlen($this->suffix) > $suffix_max_length) {
            throw new Exception("Suffix must not exceed 10 characters.");
        }
        else {
            // Assigns NULL to suffix name if it's empty
            $this->suffix = empty($this->suffix) ? NULL : $this->suffix;
        }
    }

    // Validates name values
    private function validateNameFormat() {
        $name_regex =  '/^[a-zA-ZñÑ]+([ ,.\'-][a-zA-ZñÑ]+)*$/';
        $error_message = "Please use a valid name format.";

        if(!preg_match($name_regex, $this->first_name)) {
            throw new Exception($error_message);
        }

        if($this->middle_name && !preg_match($name_regex, $this->middle_name)) {
            throw new Exception($error_message);
        }

        if(!preg_match($name_regex, $this->last_name)) {
            throw new Exception($error_message);
        }

        if($this->suffix && !preg_match($name_regex, $this->suffix)) {
            throw new Exception($error_message);
        }
    }

    // Check for invalid email format
    private function validateEmailFormat() {
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please provide a valid email address.");
        }
    }

    // Additional to check if email address already exists
    private function validateEmailNotExist() {
        $this->initializeScoDatabaseConnection();
        
        $sql = "SELECT email FROM voter WHERE BINARY email = ?";
        $stmt = $this->sco_connection->prepare($sql);
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            throw new Exception("{$this->email} is already taken.");
        }

        $stmt->close();
    }

    // Check if password and retype password matches
    private function validatePasswords() {
        $password_regex = '/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[\W_])(?=.*[^\s]).{8,20}$/';

        if($this->password !== $this->confirm_password) {
            throw new Exception("Your passwords do not match.");
        }

        if(!preg_match($password_regex, $this->password)) {
            throw new Exception("Password must be 8-20 characters with a number, an uppercase letter, a lowercase letter, a special character, and with no whitespace/s.");
        }
    }

    // Insert from picked org from dropdown into its database
    private function insertIntoOrganizationDB() {
        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO voter (student_id, last_name, first_name, middle_name, suffix, email, password, account_status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);

        $account_status = self::ACCOUNT_STATUS;
        $role = self::ROLE;

        $stmt->bind_param("sssssssss", $this->student_number, $this->last_name, $this->first_name, $this->middle_name, $this->suffix, $this->email, $hashed_password, $account_status, $role);

        if(!$stmt->execute()) {
            throw new Exception("Error occurred during registration for " . strtoupper($this->organization) . ".");
        }

        $stmt->close();
    }

    // Insert another set of data into the db_sco
    private function insertIntoScoDB() {
        $this->initializeScoDatabaseConnection();

        $hashed_password = password_hash($this->password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO voter (student_id, last_name, first_name, middle_name, suffix, email, password, account_status, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->sco_connection->prepare($sql);

        $account_status = self::ACCOUNT_STATUS;
        $role = self::ROLE;

        $stmt->bind_param("sssssssss", $this->student_number, $this->last_name, $this->first_name, $this->middle_name, $this->suffix, $this->email, $hashed_password, $account_status, $role);

        if(!$stmt->execute()) {
            throw new Exception("Error occurred during registration.");
        }

        $stmt->close();
    }

    // Send an email notice to user
    private function sendEmailNotice() {
        include_once FileUtils::normalizeFilePath(__DIR__ . '/../mailer.php');
        include_once FileUtils::normalizeFilePath(__DIR__ . '/email-sender.php');
        $mailer = new EmailSender($mail);
        $mailer->sendForVerificationStatus($this->email);
    }

    // Start transaction into db but no insertion until commit is made or call
    private function beginTransaction() {
        $this->connection->begin_transaction();
        $this->sco_connection->begin_transaction();
    }

    // Make insertion permanent if there are no exceptions
    private function commitTransaction() {
        $this->connection->commit();
        $this->sco_connection->commit();
    }

    // Undo transaction if there are catched exceptions
    private function rollbackTransaction() {
        $this->connection->rollback();
        $this->sco_connection->rollback();
    }

    // Closed database connection when no longer referenced
    public function __destruct() {
        if($this->connection) {
            $this->connection->close();
        }
        if($this->sco_connection) {
            $this->sco_connection->close();
        }
    }
}