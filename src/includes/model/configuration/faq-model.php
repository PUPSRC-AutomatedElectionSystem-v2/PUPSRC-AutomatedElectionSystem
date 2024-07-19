<?php

include_once str_replace('/', DIRECTORY_SEPARATOR,  '../classes/file-utils.php');
require_once FileUtils::normalizeFilePath('../error-reporting.php');
require_once FileUtils::normalizeFilePath('../classes/db-config.php');
require_once FileUtils::normalizeFilePath('../classes/db-connector.php');
require_once FileUtils::normalizeFilePath('../classes/logger.php');

class FaqModel
{
    private static $connection;
    protected static $mode;
    protected static $query_data;
    protected static $query_message;
    protected static $status;

    public static function getData()
    {
        if (!self::$connection = DatabaseConnection::connect()) {
            throw new Exception("Failed to connect to the data source.");
        }

        $data = [];

        $sql = "SELECT * FROM faq";

        $stmt = self::$connection->prepare($sql);

        if ($stmt) {
            $stmt->execute();

            $faq_id = $seq = $description = $answer = '';

            $stmt->bind_result($faq_id, $seq, $description, $answer);

            while ($stmt->fetch()) {

                $answer = unserialize($answer);

                $item = [
                    'faq_id' => $faq_id,
                    'sequence' => $seq,
                    'description' => $description,
                    'answer' => $answer,
                ];

                $data[] = $item;
            }

            $stmt->close();
        } else {
            self::$query_message = "Failed to perform requested action: " . self::$connection->error;
        }

        return $data;
    }

    protected static function saveData()
    {
        try {


            if (!self::$connection = DatabaseConnection::connect()) {
                throw new Exception("Failed to connect to the data source.");
            }

            self::$connection->autocommit(FALSE);

            self::$connection->begin_transaction();
            $results = [];
            foreach (self::$query_data as $item) {

                if (self::$mode === 'update') {
                    $results = self::updateData($item);
                } else if (self::$mode === 'update_sequence') {
                    $results[] = self::updateSequence($item);
                } else if (self::$mode === 'delete') {
                    // $results[] = $item;
                    $results[] = self::deleteData($item);
                } else {
                    $results = self::setData($item);
                }
            }

            self::$connection->commit();

            return $results;
        } catch (Exception $e) {
            // self::$connection->rollback();
            self::$query_message = $e->getMessage();
            return $results;
        }
    }

    private static function setData($item)
    {
        try {


            $sql = "INSERT INTO faq (seq, question, answer) VALUES (?, ?, ?)";

            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to perform requested action: " . self::$connection->error);
            }

            $answer = serialize($item['answer']);

            $stmt->bind_param("sss", $item['sequence'], $item['description'], $answer);

            if (!$stmt->execute()) {
                throw new Exception("Failed to add requested action: " . $stmt->error);
            }


            $inserted_id = mysqli_insert_id(self::$connection);

            $inserted_item = [
                'faq_id' => $inserted_id,
                'sequence' => $item['sequence'],
                'description' => $item['description'],
                'answer' => $item['answer'],
            ];

            $logger = new Logger($_SESSION['role'], ADD_FAQ);
            $logger->logActivity();

            $stmt->close();

            return $inserted_item;
        } catch (Exception $e) {
            self::$query_message = 'set ' . $e->getMessage();
            return $item;
        }
    }


    private static function updateData($item)
    {
        try {

            $sql = "UPDATE faq SET seq = ?, question = ?, answer  = ? WHERE faq_id  = ?";

            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to perform requested action: " . self::$connection->error);
            }

            $answer = serialize($item['answer']);

            $stmt->bind_param("issi", $item['sequence'], $item['description'], $answer, $item['faq_id']);

            $stmt->execute();

            $stmt->close();

            $logger = new Logger($_SESSION['role'], UPDATE_FAQ);
            $logger->logActivity();

            return $item;
        } catch (Exception $e) {

            self::$query_message = 'update ' . $e->getMessage();
            return $item;
        }
    }

    private static function updateSequence($item)
    {
        try {

            $sql = "UPDATE faq SET seq = ?  WHERE faq_id = ?";

            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception("Failed to perform requested action: " . self::$connection->error);
            }

            $stmt->bind_param("ii", $item['sequence'], $item['faq_id']);

            $stmt->execute();

            $stmt->close();

            $logger = new Logger($_SESSION['role'], UPDATE_FAQ);
            $logger->logActivity();

            return $item;
        } catch (Exception $e) {

            self::$query_message = 'update ' . $e->getMessage();
            return $item;
        }
    }


    private static function deleteData($item)
    {
        try {

            $sql = "DELETE FROM faq WHERE faq_id = ?";

            $stmt = self::$connection->prepare($sql);

            if (!$stmt) {
                throw new Exception(self::$connection->error);
            }

            $stmt->bind_param("i", $item['faq_id']);

            $stmt->execute();

            $stmt->close();

            $logger = new Logger($_SESSION['role'], DELETE_FAQ);
            $logger->logActivity();

            return $item;
        } catch (Exception $e) {

            self::$query_message = 'Failed to delete selected resource: ' . $e->getMessage();
            return $item;
        }
    }
}
