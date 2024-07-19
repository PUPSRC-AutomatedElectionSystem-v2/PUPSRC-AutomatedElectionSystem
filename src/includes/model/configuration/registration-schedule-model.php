<?php

include_once str_replace('/', DIRECTORY_SEPARATOR,  '../classes/file-utils.php');
require_once FileUtils::normalizeFilePath('../error-reporting.php');
require_once FileUtils::normalizeFilePath('../classes/db-config.php');
require_once FileUtils::normalizeFilePath('../classes/db-connector.php');


class RegistrationSchedModel
{
    private static $connection;
    protected static $query_message;
    protected static $status;

    public static function getData()
    {
        if (!self::$connection = DatabaseConnection::connect()) {
            throw new Exception("Failed to connect to the database.");
        }

        $registration_schedules = [];

        $sql = "SELECT start, close FROM registration_schedule";

        $stmt = self::$connection->prepare($sql);

        if ($stmt) {
            $stmt->execute();

            $election_id = $start = $end = '';

            $stmt->bind_result($start, $end);

            while ($stmt->fetch()) {
                $registration_schedule = [
                    // 'schedule_id' => $election_id,
                    'registrationStart' => $start,
                    'registrationEnd' => $end
                ];

                $registration_schedules[] = $registration_schedule;
            }

            $stmt->close();
        } else {
            self::$query_message = "Error preparing statement: " . self::$connection->error;
        }

        return $registration_schedules;
    }

    protected static function saveData($data)
    {
        try {

            if (!self::$connection = DatabaseConnection::connect()) {
                throw new Exception("Failed to connect to the database.");
            }

            self::$connection->autocommit(false);

            $sql = "SELECT COUNT(*) FROM registration_schedule";
            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error preparing statement.");
            }

            $row_count = null;
            $stmt->execute();
            $stmt->bind_result($row_count);
            $stmt->fetch();
            $stmt->close();

            self::$connection->begin_transaction();
            $result = '';
            if ($row_count > 0) {
                $result = self::updateData($data);
            } else {
                $result = self::setData($data);
            }

            if (!empty(self::$query_message)) {
                throw new Exception(self::$query_message);
            }

            self::$connection->commit();

            // self::$connection->close();
            return $result;
        } catch (Exception $e) {

            self::$query_message = htmlspecialchars($e->getMessage());
            self::$connection->rollback();
            return $result;
        } finally {
            self::$connection->autocommit(true);
        }
    }



    private static function setData($data)
    {
        try {

            $registration_schedules = [];


            $sql = "INSERT INTO registration_schedule (start, close) VALUES (?, ?)";

            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error preparing statement: " . self::$connection->error);
            }

            $stmt->bind_param("ss", $data['registrationStart'], $data['registrationEnd']);

            $stmt->execute();

            $stmt->close();

            return $data;
        } catch (Exception $e) {
            self::$query_message = 'set ' . htmlspecialchars($e->getMessage());
            return $data;
        }
    }


    private static function updateData($data)
    {
        try {
            $sql = "UPDATE registration_schedule SET start = ?, close = ?";

            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Error preparing statement: " . self::$connection->error);
            }

            $stmt->bind_param("ss", $data['registrationStart'], $data['registrationEnd']);

            $stmt->execute();

            $stmt->close();

            return $data;
        } catch (Exception $e) {

            self::$query_message = 'update ' . htmlspecialchars($e->getMessage());
            return $data;
        }
    }
}
