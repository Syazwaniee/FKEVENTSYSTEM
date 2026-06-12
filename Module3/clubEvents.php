<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || ($_SESSION['user']['role'] ?? '') !== 'committee') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$club = getCommitteeClubForUser($userId);
$eventList = [];

if ($club) {
    $eventsResult = getEventsByClub((int) $club['Club_id']);
    if ($eventsResult) {
        while ($row = $eventsResult->fetch_assoc()) {
            $eventList[] = $row;
        }
    }
}

$navBase = '../';
$activeNav = 'events';
$flashMessage = $_GET['msg'] ?? null;
$flashType = in_array($_GET['msg_type'] ?? '', ['success', 'danger'], true)
    ? $_GET['msg_type']
    : 'success';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/UserManagement.css">
    <link rel="stylesheet" href="../CSS/clubEvents.css">
</head>

<body>

    <?php include __DIR__ . '/../INCLUDE/CommitteeHeader.php'; ?>

    <div class="add-user-container">

        <div class="top-flex">
            <div>
                <h1 class="add-user-title mb-2">Club Events</h1>
                <p class="add-user-subtitle mb-0">
                    <?php if ($club): ?>
                        Manage events for <?= htmlspecialchars($club['Club_name']) ?>
                    <?php else: ?>
                        You are not assigned to a club yet.
                    <?php endif; ?>
                </p>
            </div>
            <?php if ($club): ?>
                <a href="createEvent.php" class="save-btn text-decoration-none d-inline-flex align-items-center gap-2">
                    <i class="bi bi-plus-lg"></i>
                    Create new Event
                </a>
            <?php endif; ?>
        </div>

        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($flashType) ?> mt-3">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <?php if (!$club): ?>
            <div class="add-user-box">
                <div class="alert alert-warning mb-0">
                    Please contact an administrator to be assigned to a club before managing events.
                </div>
            </div>
        <?php else: ?>
            <div class="table-box mt-4">
                <h5 class="text-white mb-3">Your club events</h5>
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th>Event title</th>
                            <th>Date</th>
                            <th>Venue</th>
                            <th>Status</th>
                            <th>Reg / Max</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($eventList) > 0): ?>
                            <?php foreach ($eventList as $event): ?>
                                <?php $current = countRegistrations((int) $event['Event_id']); ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($event['Event_Name']) ?></strong>
                                        <?php if (!empty($event['EventDesc'])): ?>
                                            <br><span class="event-desc"><?= htmlspecialchars($event['EventDesc']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= date('d M Y, g:i A', strtotime($event['Event_Date'])) ?></td>
                                    <td><?= htmlspecialchars($event['Venue']) ?></td>
                                    <td><?= htmlspecialchars($event['Event_Status'] ?? 'Upcoming') ?></td>
                                    <td class="reg-max-cell">
                                        <span class="reg-max-count"><?= $current ?> / <?= (int) $event['Student_Capacity'] ?></span>
                                        <a href="../Module4/manage-event-attendance.php?event_id=<?= (int) $event['Event_id'] ?>"
                                            class="save-btn attendance-btn text-decoration-none d-inline-flex align-items-center justify-content-center gap-2"
                                            title="Manage attendance for this event">
                                            <i class="bi bi-clipboard-check-fill"></i>
                                            Attendance
                                        </a>
                                    </td>
                                    <td>
                                        <div class="action-flex justify-content-center">
                                            <a href="../Module3/editEvent.php?event_id=<?= (int) $event['Event_id'] ?>"
                                                class="edit-btn"
                                                title="Edit event">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <button type="button"
                                                class="delete-btn"
                                                title="Delete event"
                                                onclick="confirmDelete(<?= (int) $event['Event_id'] ?>)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">
                                    No events yet. Create your first event for this club.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

    <script>
        function confirmDelete(id) {
            if (confirm('Delete this event? All registrations will be lost.')) {
                window.location.href = '../Module3/deleteEvent.php?event_id=' + id;
            }
        }
    </script>

</body>

</html>
