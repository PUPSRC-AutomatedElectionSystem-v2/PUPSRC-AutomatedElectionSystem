<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../includes/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/default-time-zone.php');
// require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/mailer-test.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/mailer.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/classes/email-sender.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/../includes/classes/email-queue.php');

$orgs = array('sco', 'acap', 'aeces', 'elite', 'give', 'jehra', 'jmap', 'jpia', 'piie');

// Check email queues on all organization
foreach ($orgs as $org) {


    try {
        echo $org . "\n<br>\n<br>";

        //   creating a separate connection as the existing only set connection if not existing 
        // but this one aims to establish a new connection every iteration

        // Retrieves database configuration based on the organization name
        $config = DatabaseConfig::getOrganizationDBConfig($org);

        $connection = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);

        if ($connection->connect_error) {
            throw new Exception("Connection failed: " . $connection->connect_error);
        }


        // $sql = "SELECT DATABASE()";
        // $result = mysqli_query($connection, $sql);
        // $row = mysqli_fetch_row($result);
        // $current_db = $row[0];

        // echo "Currently connected database: " . $current_db;

        $emails = EmailQueue::getCurrQueue($connection);

        // print_r($emails);

        // Check pending emails for the current organization
        foreach ($emails as $email) {
            $send_email = new EmailSender($mail);

            $is_send_success = $send_email->sendQueuedMail($email['content']);
            sleep(3);

            if ($is_send_success) {
                EmailQueue::updateEmailStatus($email['email_id'], 'sent');
            } else {
                EmailQueue::updateEmailStatus($email['email_id'], 'failed');
            }
            sleep(1);
        }

        // $connection->close();

    } catch (Exception $e) {
        echo $e->getMessage() . "\<br>\n<br>";
    }
}
