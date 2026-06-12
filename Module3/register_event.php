<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'student') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$eventId = (int) ($_POST['event_id'] ?? 0);
$action = $_POST['action'] ?? 'register';

if ($eventId <= 0) {
    header('Location: ../Module3/eventList.php?msg=' . urlencode('Invalid event.') . '&msg_type=danger');
    exit;
}

if ($action === 'register') {
    if (registerUser($userId, $eventId)) {
        $msg = 'Successfully registered!';
        $msgType = 'success';
    } elseif (isRegistered($userId, $eventId)) {
        $msg = 'You are already registered for this event.';
        $msgType = 'danger';
    } elseif (isWaiting($userId, $eventId)) {
        $msg = 'You are already on the waiting list for this event.';
        $msgType = 'danger';
    } else {
        $msg = 'Event is full. Try the waiting list.';
        $msgType = 'danger';
    }
} else {
    if (addToWaiting($userId, $eventId)) {
        $msg = 'Added to waiting list.';
        $msgType = 'success';
    } elseif (isWaiting($userId, $eventId)) {
        $msg = 'You are already on the waiting list.';
        $msgType = 'danger';
    } elseif (isRegistered($userId, $eventId)) {
        $msg = 'You are already registered for this event.';
        $msgType = 'danger';
    } else {
        $msg = 'Could not add you to the waiting list.';
        $msgType = 'danger';
    }
}

header(
    'Location: eventList.php?tab=history&msg=' . urlencode($msg)
    . '&msg_type=' . urlencode($msgType)
);
exit;
