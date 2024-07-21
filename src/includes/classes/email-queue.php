<?php
include_once str_replace('/', DIRECTORY_SEPARATOR,  'file-utils.php');
require_once FileUtils::normalizeFilePath('db-config.php');
require_once FileUtils::normalizeFilePath('db-connector.php');

class EmailQueue
{
    private static $connection;
    protected static $query_message;
    private static $default_status = 'pending';

    public static function generateHash()
    {
        return hash('sha256', bin2hex(random_bytes(16)) . date('Y-m-d H:i'));
    }

    private static function ensureConnection()
    {
        if (self::$connection === null) {
            self::$connection = DatabaseConnection::connect();
        }
    }

    public static function insertQueue($emails, $schedule, $hash)
    {
        self::ensureConnection();

        if (self::$connection) {
            $baseSchedule = date('Y-m-d H:i:s', strtotime($schedule));

            foreach ($emails as $i => $email) {
                $sql = "INSERT INTO email_queue (status, schedule, push_id, content) VALUES (?, ?, ?, ?)";
                $stmt = self::$connection->prepare($sql);

                $delay = $i * 30; // add delay in minutes for each email
                $adjustedSchedule = date('Y-m-d H:i:s', strtotime("$baseSchedule + $delay minutes"));

                if ($stmt) {
                    $stmt->bind_param("ssss", self::$default_status, $adjustedSchedule, $hash, $email);
                    $stmt->execute();

                    if ($stmt->affected_rows <= 0) {
                        // No rows were affected
                    }

                    $stmt->close();
                } else {
                    self::$query_message = "Error preparing statement: " . self::$connection->error;
                }
            }
        }
    }

    public static function getCurrQueue($db_conn)
    {
        self::$connection = $db_conn;
        $emails = [];

        if (self::$connection) {
            $currentDatetime = date('Y-m-d H:i:s');

            $sql = "SELECT email_id, status, schedule, content, push_id FROM email_queue WHERE schedule <= ? AND status = 'pending'";
            $stmt = self::$connection->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('s', $currentDatetime);
                $stmt->execute();
                $email_id = $status =  $schedule = $push_id = $content = '';
                $stmt->bind_result($email_id, $status, $schedule, $content, $push_id);

                while ($stmt->fetch()) {
                    $emails[] = [
                        'email_id' => $email_id,
                        'status' => $status,
                        'schedule' => $schedule,
                        'content' => $content,
                        'push_id' => $push_id
                    ];
                }

                $stmt->close();
            } else {
                self::$query_message = "Error preparing statement: " . self::$connection->error;
            }
        }

        // self::$connection->close();
        return $emails;
    }

    public static function updateEmailStatus($email_id, $new_status)
    {
        self::ensureConnection();

        if (self::$connection) {
            $allowed_status = ['pending', 'failed', 'sent'];
            if (!in_array($new_status, $allowed_status)) {
                self::$query_message = "Email status not allowed.";
                return false;
            }

            $sql = "UPDATE email_queue SET status = ? WHERE email_id = ?";
            $stmt = self::$connection->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('ss', $new_status, $email_id);
                $stmt->execute();
                $stmt->close();
                return true;
            } else {
                self::$query_message = "Error preparing statement: " . self::$connection->error;
                return false;
            }
        }

        // self::$connection->close();
        return false;
    }


    protected static function deleteQueues($push_id)
    {
        self::ensureConnection();

        if (self::$connection) {

            $sql = "DELETE FROM email_queue WHERE push_id = ? AND status = 'pending'";
            $stmt = self::$connection->prepare($sql);

            if ($stmt) {
                $stmt->bind_param('s', $push_id);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    // echo "Email with push_id: $push_id deleted successfully.";
                } else {
                    // echo "No email found with push_id: $push_id.";
                }

                $stmt->close();
            } else {
                self::$query_message = "Error preparing statement: " . self::$connection->error;
            }
        }
    }

    protected static function deleteQueue($schedule = null, $email_id = null)
    {
        self::ensureConnection();

        if (self::$connection) {
            $sql = "DELETE FROM email_queue WHERE status = 'pending'";
            $params = [];

            // Build additional filter conditions based on parameters
            if ($schedule !== null) {
                $sql .= " AND schedule = ?";
                $params[] = $schedule;
            }

            if ($email_id !== null) {
                $sql .= " AND email_id = ?";
                $params[] = $email_id;
            }

            if (empty($params)) {
                self::$query_message = "Error: No deletion criteria provided (schedule or email_id). ";
                return;
            }

            $stmt = self::$connection->prepare($sql);

            if ($stmt) {
                $stmt->bind_param(str_repeat('i', count($params)), ...$params);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $deletedCount = $stmt->affected_rows;
                    // echo "Successfully deleted $deletedCount emails.";
                } else {
                    self::$query_message = "No emails found matching the criteria.";
                }

                $stmt->close();
            } else {
                self::$query_message = "Error preparing statement: " . self::$connection->error;
            }
        }
    }
}
