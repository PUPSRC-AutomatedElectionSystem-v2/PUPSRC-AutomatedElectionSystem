<?php
include_once str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/classes/file-utils.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/classes/db-connector.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/session-handler.php');
require_once FileUtils::normalizeFilePath(__DIR__ . '/error-reporting.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/default-time-zone.php');
include_once FileUtils::normalizeFilePath(__DIR__ . '/constants.php');

function getActivityLogs($filter, $role, $voter_id, $limit, $offset) {
    $connection = DatabaseConnection::connect();
    $sql = "SELECT al.timestamp, al.ip_address, al.browser, v.email, v.role, v.first_name, al.action
            FROM activity_log al
            JOIN voter v ON al.voter_id = v.voter_id";

    if ($role !== ROLE_HEAD_ADMIN) {
        $sql .= " WHERE al.voter_id = ?";
    }

    if ($filter === 'adminActLogs') {
        $sql .= ($role !== ROLE_HEAD_ADMIN) ? " AND v.role = 'admin'" : " WHERE v.role = 'admin'";
    } 
    elseif ($filter === 'voterActLogs') {
        $sql .= ($role !== ROLE_HEAD_ADMIN) ? " AND v.role = 'student_voter'" : " WHERE v.role = 'student_voter'";
    }

    $sql .= " ORDER BY al.timestamp DESC LIMIT ? OFFSET ?";
    $stmt = $connection->prepare($sql);

    if ($role !== ROLE_HEAD_ADMIN) {
        $stmt->bind_param('iii', $voter_id, $limit, $offset);
    } 
    else {
        $stmt->bind_param('ii', $limit, $offset);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $formatted_date = date('F j, Y', strtotime($row['timestamp']));
        $formatted_time = date('g:i A', strtotime($row['timestamp']));

        switch ($row['role']) {
            case 'student_voter':
                $formatted_role = 'Student Voter';
                break;
            case 'admin':
                $formatted_role = 'Admin';
                break;
            case 'head_admin':
                $formatted_role = 'Head Admin';
                break;
        }

        if ($row['role'] === ROLE_HEAD_ADMIN) {
            $action = HEAD_ADMIN_ACTIONS[$row['action']] ?? $row['action'];
        } else {
            if ($row['role'] === ROLE_ADMIN) {
                $actions = ADMIN_ACTIONS;
            } else {
                $actions = STUDENT_VOTER_ACTIONS;
            }
            $action = $actions[$row['action']] ?? $row['action'];

            if ($role === ROLE_HEAD_ADMIN) {
                $action = str_replace(
                    ['You', 'your'],
                    [htmlspecialchars($row['first_name']) . ' (' . htmlspecialchars($formatted_role) . ') ', 'his'],
                    $action
                );
            }
        }
        $logs[] = [
            'date' => $formatted_date,
            'time' => $formatted_time,
            'action' => $action,
            'role' => $formatted_role,
            'email' => $row['email'],
            'ip_address' => $row['ip_address'],
            'browser' => $row['browser']
        ];
    }

    $stmt->close();
    $connection->close();

    return $logs;
}

if (isset($_POST['filter']) && isset($_POST['page'])) {
    $filter = $_POST['filter'];
    $page = (int)$_POST['page'];
    $voter_id = $_SESSION['voter_id'];
    $role = $_SESSION['role'];
    $limit = 5;
    $offset = ($page - 1) * $limit;

    $logs = getActivityLogs($filter, $role, $voter_id, $limit, $offset);
    $total_logs = count($logs);
    $hasMore = $total_logs === $limit;

    echo json_encode([
        'logs' => $logs,
        'hasMore' => $hasMore
    ]);
}