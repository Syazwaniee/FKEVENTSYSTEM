<?php

require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'committee') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$recordId = (int) ($_GET['id'] ?? 0);
$eventId = (int) ($_GET['event_id'] ?? 0);

if ($recordId > 0 && $eventId > 0 && committeeCanManageEvent($userId, $eventId)) {
    $stmt = $conn->prepare('DELETE FROM attendance WHERE id = ?');
    if ($stmt) {
        $stmt->bind_param('i', $recordId);
        $stmt->execute();
        $stmt->close();
    }
}

$redirect = $eventId > 0
    ? 'manage-event-attendance.php?event_id=' . $eventId
    : '../Module3/clubEvents.php';

header('Location: ' . $redirect);
exit;
