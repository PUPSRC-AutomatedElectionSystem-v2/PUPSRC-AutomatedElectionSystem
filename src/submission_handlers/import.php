<?php
// Include Composer's autoloader
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
} else {
    die("Composer autoloader not found. Please check your installation.");
}

require_once '../includes/classes/db-connector.php';
require_once '../includes/session-handler.php';
require_once '../includes/classes/session-manager.php';
require_once '../includes/classes/query-handler.php';
require_once '../includes/classes/logger.php';

// Check if PhpSpreadsheet is installed
if (!class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
    die("PhpSpreadsheet library not found. Please check your Composer installation and autoloader.");
}

use PhpOffice\PhpSpreadsheet\IOFactory;

function validateHeaders($headers) {
    $expected_headers = ['Student ID', 'Last Name', 'First Name', 'Middle Name', 'Suffix', 'Year Level', 'Section', 'Email'];
    return $headers === $expected_headers;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['voter_id']) && ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'head_admin')) {
    $file = $_FILES['file'];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    try {
        $conn = DatabaseConnection::connect();
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Database connection failed: " . $e->getMessage()]);
        exit;
    }

    if ($fileExtension == 'csv') {
        $result = importCSV($fileTmpName, $conn);
    } elseif ($fileExtension == 'xls' || $fileExtension == 'xlsx') {
        $result = importExcel($fileTmpName, $conn);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Invalid file format. Please upload a CSV or Excel file."]);
        exit;
    }

    // Log the activity after successful import
    if ($result['status'] === 'success') {
        $logger = new Logger($_SESSION['role'], IMPORT_MEMBER_LIST);
        $logger->logActivity();
    }

    $conn->close();
    echo json_encode($result);
} else {
    echo json_encode(['status' => 'error', 'message' => "Invalid request or insufficient permissions"]);
}

function generatePassword($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $password;
}

function validateData($data, $conn) {
    // Check if required fields are not empty
    if (empty($data[1]) || empty($data[2]) || empty($data[5]) || empty($data[6])) {
        return 'invalid_id';
    }

    // Validate Student ID format
    if (!preg_match('/^\d{4}-\d{5}-SR-0$/', $data[0])) {
        return 'invalid_id';
    }

    // Check if Student ID already exists
    $checkSql = "SELECT * FROM voter WHERE student_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $data[0]);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $checkStmt->close();
        return 'duplicate';
    }
    $checkStmt->close();

    // Extract the part of the email before @example.com
    $emailParts = explode('@', $data[7]);
    $emailPrefix = $emailParts[0];

    // Check if email prefix already exists
    $checkSql = "SELECT * FROM voter WHERE SUBSTRING(email, 1, LOCATE('@', email) - 1) = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("s", $emailPrefix);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        $checkStmt->close();
        return 'duplicate';
    }
    $checkStmt->close();

    return true;
}

function importCSV($filePath, $conn) {
    $fileDuplicates = checkForDuplicates($filePath, 'csv');
    error_log("File duplicates: " . json_encode($fileDuplicates));
    
    $file = fopen($filePath, 'r');
    $headers = fgetcsv($file);
    
    if (!validateHeaders($headers)) {
        fclose($file);
        return ['status' => 'error', 'message' => "Invalid headers. Please ensure the headers are in the correct order."];
    }
    
    $invalidIds = [];
    $databaseDuplicates = [];

    while (($data = fgetcsv($file)) !== FALSE) {
        $result = validateData($data, $conn);
        if ($result === 'invalid_id') {
            $invalidIds[] = $data[0];
        } elseif ($result === 'duplicate') {
            $databaseDuplicates[] = $data[0];
        }
    }

    fclose($file);
    
    $allDuplicates = array_unique(array_merge($fileDuplicates, $databaseDuplicates));
    
    if (!empty($invalidIds) || !empty($allDuplicates)) {
        return [
            'status' => 'error', 
            'errorType' => 'duplicates',
            'message' => "Import failed due to invalid or duplicate entries. Please Check your CSV/XLS file", 
            'invalidIds' => $invalidIds,
            'duplicates' => $allDuplicates
        ];
    }
    
    // If no issues, proceed with actual import
    $count = actualImport($filePath, $conn);
    return ['status' => 'success', 'message' => "CSV import completed. Rows imported: $count"];
}

function importExcel($filePath, $conn) {
    $fileDuplicates = checkForDuplicates($filePath, 'excel');

    $spreadsheet = IOFactory::load($filePath);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();
    
    $headers = $rows[0];
    if (!validateHeaders($headers)) {
        return ['status' => 'error', 'message' => "Invalid headers. Please ensure the headers are in the correct order."];
    }
    
    array_shift($rows); // Remove header row
    $invalidIds = [];
    $databaseDuplicates = [];

    foreach ($rows as $row) {
        $result = validateData($row, $conn);
        if ($result === 'invalid_id') {
            $invalidIds[] = $row[0];
        } elseif ($result === 'duplicate') {
            $databaseDuplicates[] = $row[0];
        }
    }

    $allDuplicates = array_unique(array_merge($fileDuplicates, $databaseDuplicates));

    if (!empty($invalidIds) || !empty($allDuplicates)) {
        return [
            'status' => 'error', 
            'errorType' => 'duplicates',
            'message' => "Import failed due to invalid or duplicate entries. Please Check your CSV/XLS file", 
            'invalidIds' => $invalidIds,
            'duplicates' => $allDuplicates
        ];
    }
    
    // If no issues, proceed with actual import
    $count = actualImport($filePath, $conn, 'excel');
    return ['status' => 'success', 'message' => "Excel import completed. Rows imported: $count"];
}

function checkForDuplicates($filePath, $type = 'csv') {
    $studentIds = [];
    $emailPrefixes = [];
    $duplicates = [];

    if ($type === 'csv') {
        $file = fopen($filePath, 'r');
        fgetcsv($file); // Skip header
        while (($data = fgetcsv($file)) !== FALSE) {
            $studentId = $data[0];
            $emailParts = explode('@', $data[7]);
            $emailPrefix = $emailParts[0];
            
            if (in_array($studentId, $studentIds) || in_array($emailPrefix, $emailPrefixes)) {
                $duplicates[] = $studentId;
            } else {
                $studentIds[] = $studentId;
                $emailPrefixes[] = $emailPrefix;
            }
        }
        fclose($file);
    } else {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        array_shift($rows); // Remove header row
        foreach ($rows as $row) {
            $studentId = $row[0];
            $emailParts = explode('@', $row[7]);
            $emailPrefix = $emailParts[0];
            
            if (in_array($studentId, $studentIds) || in_array($emailPrefix, $emailPrefixes)) {
                $duplicates[] = $studentId;
            } else {
                $studentIds[] = $studentId;
                $emailPrefixes[] = $emailPrefix;
            }
        }
    }

    return $duplicates;
}

function actualImport($filePath, $conn, $type = 'csv') {
    $count = 0;
    if ($type === 'csv') {
        $file = fopen($filePath, 'r');
        fgetcsv($file); // Skip header
        while (($data = fgetcsv($file)) !== FALSE) {
            if (insertData($data, $conn)) {
                $count++;
            }
        }
        fclose($file);
    } else {
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        array_shift($rows); // Remove header row
        foreach ($rows as $row) {
            if (insertData($row, $conn)) {
                $count++;
            }
        }
    }
    return $count;
}

function insertData($data, $conn) {
    $role = 'student_voter';
    $accountStatus = 'for_verification';
    $voterStatus = 'active';
    $voteStatus = NULL;
    
    $password = generatePassword();
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Use empty string instead of NULL for middle_name and suffix if they're empty
    $middleName = empty($data[3]) ? '' : $data[3];
    $suffix = empty($data[4]) ? '' : $data[4];

    $sql = "INSERT INTO voter (student_id, last_name, first_name, middle_name, suffix, year_level, section, email, password, role, account_status, voter_status, vote_status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssssssss", $data[0], $data[1], $data[2], $middleName, $suffix, $data[5], $data[6], $data[7], $hashedPassword, $role, $accountStatus, $voterStatus, $voteStatus);
        $result = $stmt->execute();
        $stmt->close();
        
        return $result;
    } catch (Exception $e) {
        return false;
    }
}