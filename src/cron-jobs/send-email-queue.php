<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/default-time-zone.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/mailer-test.php');
// require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/mailer.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/classes/email-sender.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/classes/email-queue.php');

$orgs = array('sco', 'acap', 'aeces', 'elite', 'give', 'jehra', 'jmap', 'jpia', 'piie');

// Check email queues on all organization
foreach ($orgs as $org) {


    try {
        $_SESSION['organization'] = $org;

        echo $org . "\n<br>\n<br>";

        // I dont know why I need to use this query to properly switch database
        // https://drive.google.com/file/d/1t-xp7BSRzGWEy5kzFq5wvE67syL1DUJk/view?usp=sharing
        $databaseName = 'db_' . $_SESSION['organization'];
        $connection = DatabaseConnection::connect();

        $sql = "USE " . $databaseName;
        if (!mysqli_query($connection, $sql)) {
            throw new Exception("Error selecting database: " . mysqli_error(self::$connection));
        }

        // $sql = "SELECT DATABASE()";
        // $result = mysqli_query(self::$connection, $sql);
        // $row = mysqli_fetch_row($result);
        // $current_db = $row[0];

        // echo "Currently connected database: " . $current_db;

        $emails = EmailQueue::getCurrQueue();

        // print_r($emails);

        // Check pending emails for the current organization
        foreach ($emails as $email) {
            $send_email = new EmailSender($mail);

            $is_send_success = $send_email->sendQueuedMail($email['content']);
            // sleep(3);

            if ($is_send_success) {
                EmailQueue::updateEmailStatus($email['email_id'], 'sent');
            } else {
                EmailQueue::updateEmailStatus($email['email_id'], 'failed');
            }
            // sleep(1);
        }

        unset($_SESSION['organization']);
    } catch (Exception $e) {
        echo $e->getMessage() . "\n<br>\n<br>";
    }
}


?>

a