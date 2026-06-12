<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'student') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$eventId = (int) ($_POST['event_id'] ?? 0);

if ($eventId <= 0) {
    header('Location: eventList.php?msg=' . urlencode('Invalid event.') . '&msg_type=danger');
    exit;
}

if (cancelRegistration($userId, $eventId)) {
    $msg = 'Registration cancelled.';
    $msgType = 'success';
} else {
    $msg = 'Could not cancel registration.';
    $msgType = 'danger';
}

header(
    'Location: eventList.php?tab=history&msg=' . urlencode($msg)
    . '&msg_type=' . urlencode($msgType)
);
exit;
