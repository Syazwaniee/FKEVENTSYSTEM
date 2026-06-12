<?php
include '../INCLUDE/db.php';

$event_id = $_GET['event_id'] ?? 0;
$event = getEventById($event_id);

if (!$event) {
    header("Location: ../Module3/clubEvents.php");
    exit();
}

$user = $_SESSION['user'];
$role = $user['role'];
$user_id = $user['User_id'];

if ($role == 'committee') {
    $club_id = getClubIdByCommittee($user_id);
    if ($club_id != $event['Club_id']) {
        header("Location: ../Module3/clubEvents.php");
        exit();
    }
}

deleteEvent($event_id);
$_SESSION['flash'] = "Event deleted.";
header("Location: ../Module3/clubEvents.php");
