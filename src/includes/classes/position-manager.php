<?php

class PositionManager
{
    private $conn;

    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    public function getPositionData($searchQuery, $offset, $records_per_page)
    {
        $searchQuery = '%' . $this->conn->real_escape_string($searchQuery) . '%';
        $query = "SELECT p.position_id as id, p.title, COUNT(c.candidate_id) as total_count 
                  FROM position p
                  LEFT JOIN candidate c ON p.position_id = c.position_id
                  WHERE p.title LIKE ?
                  GROUP BY p.position_id
                  LIMIT ?, ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sii", $searchQuery, $offset, $records_per_page);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        return $result;
    }

    public function getTotalRecords($searchQuery)
    {
        $searchQuery = '%' . $this->conn->real_escape_string($searchQuery) . '%';
        $query = "SELECT COUNT(*) as total 
                  FROM position p 
                  WHERE p.title LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $searchQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $total_records = $result->fetch_assoc()['total'];
        $stmt->close();

        return $total_records;
    }
}
?>