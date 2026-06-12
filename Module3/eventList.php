<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'student') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$events = getUpcomingEvents();
$registrationHistory = getStudentEventRegistrationHistory($userId);
$navBase = '../';
$activeTab = ($_GET['tab'] ?? '') === 'history' ? 'history' : 'events';
$flashMessage = $_GET['msg'] ?? null;
$flashType = in_array($_GET['msg_type'] ?? '', ['success', 'danger'], true)
    ? $_GET['msg_type']
    : 'info';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/eventList.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/UserManagement.css">
</head>

<body>

    <?php include __DIR__ . '/../INCLUDE/StudentHeader.php'; ?>

    <div class="user-container">
        <div class="top-flex">
            <div>
                <h1>Events</h1>
                <p>Browse upcoming events or view your registration history.</p>
            </div>
        </div>

        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($flashType) ?> mb-3" role="alert">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs event-tabs mb-0" role="tablist">
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'events' ? 'active' : '' ?>"
                    id="events-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#events"
                    type="button"
                    role="tab">
                    Upcoming Events
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link <?= $activeTab === 'history' ? 'active' : '' ?>"
                    id="history-tab"
                    data-bs-toggle="tab"
                    data-bs-target="#history"
                    type="button"
                    role="tab">
                    Event History
                </button>
            </li>
        </ul>

        <div class="tab-content mt-4">
            <div class="tab-pane fade <?= $activeTab === 'events' ? 'show active' : '' ?>" id="events" role="tabpanel">
                <div class="row">
                    <?php
                    $hasUpcoming = $events && $events->num_rows > 0;
                    if ($hasUpcoming):
                        while ($event = $events->fetch_assoc()):
                            $current = countRegistrations((int) $event['Event_id']);
                            $full = ($current >= (int) $event['Student_Capacity']);
                            $registered = isRegistered($userId, (int) $event['Event_id']);
                            $waiting = isWaiting($userId, (int) $event['Event_id']);
                            ?>
                            <div class="col-md-4 mb-4">
                                <div class="event-card">
                                    <h4><?= htmlspecialchars($event['Event_Name']) ?></h4>
                                    <div class="organiser">
                                        <i class="bi bi-building"></i>
                                        Organiser: <?= htmlspecialchars($event['Club_name']) ?>
                                    </div>
                                    <div class="date">
                                        <i class="bi bi-calendar"></i>
                                        <?= date('j F Y, g:i A', strtotime($event['Event_Date'])) ?>
                                    </div>
                                    <div class="venue">
                                        <i class="bi bi-geo-alt"></i>
                                        Venue: <?= htmlspecialchars($event['Venue']) ?>
                                    </div>
                                    <div class="slots">
                                        Slots available: <?= $current ?> / <?= (int) $event['Student_Capacity'] ?>
                                    </div>
                                    <div class="actions mt-3">
                                        <?php if ($registered): ?>
                                            <button type="button" class="btn btn-secondary" disabled>Registered</button>
                                        <?php elseif ($waiting): ?>
                                            <button type="button" class="btn btn-warning" disabled>On Waiting List</button>
                                        <?php elseif ($full): ?>
                                            <form method="post" action="register_event.php">
                                                <input type="hidden" name="event_id" value="<?= (int) $event['Event_id'] ?>">
                                                <input type="hidden" name="action" value="waiting">
                                                <button type="submit" class="btn btn-outline-warning">Join Waiting List</button>
                                            </form>
                                        <?php else: ?>
                                            <form method="post" action="register_event.php">
                                                <input type="hidden" name="event_id" value="<?= (int) $event['Event_id'] ?>">
                                                <input type="hidden" name="action" value="register">
                                                <button type="submit" class="btn btn-primary">Register</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        endwhile;
                    else:
                        ?>
                        <div class="col-12">
                            <div class="add-user-box">
                                <p class="text-white mb-0">No upcoming events at the moment.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="tab-pane fade <?= $activeTab === 'history' ? 'show active' : '' ?>" id="history" role="tabpanel">
                <div class="table-box">
                    <h5 class="text-white mb-3">My event registration history</h5>
                    <table class="table custom-table align-middle">
                        <thead>
                            <tr>
                                <th>Event name</th>
                                <th>Club</th>
                                <th>Date</th>
                                <th>Venue</th>
                                <th>Points</th>
                                <th>Registration status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($registrationHistory) > 0): ?>
                                <?php foreach ($registrationHistory as $reg): ?>
                                    <?php
                                    $status = strtolower(trim((string) ($reg['Reg_Status'] ?? '')));
                                    $eventTs = strtotime($reg['Event_Date'] ?? '');
                                    $canCancel = $status === 'registered'
                                        && $eventTs !== false
                                        && $eventTs >= time();
                                    $statusClass = $status === 'registered'
                                        ? 'active-status'
                                        : ($status === 'completed' ? 'active-status' : 'inactive-status');
                                    $statusLabel = ucfirst($status ?: 'unknown');
                                    $points = isset($reg['Point_Value']) ? (int) $reg['Point_Value'] : 0;
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($reg['Event_Name']) ?></td>
                                        <td><?= htmlspecialchars($reg['Club_name'] ?? '—') ?></td>
                                        <td><?= $eventTs ? date('d M Y, g:i A', $eventTs) : '—' ?></td>
                                        <td><?= htmlspecialchars($reg['Venue'] ?? '—') ?></td>
                                        <td><?= $points ?></td>
                                        <td>
                                            <span class="status-badge <?= htmlspecialchars($statusClass) ?>">
                                                <i class="bi bi-circle-fill"></i>
                                                <?= htmlspecialchars($statusLabel) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-flex justify-content-center">
                                                <?php if ($canCancel): ?>
                                                    <form method="post"
                                                        action="cancel_registration.php"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Cancel your registration for this event?');">
                                                        <input type="hidden" name="event_id" value="<?= (int) $reg['Event_id'] ?>">
                                                        <button type="submit"
                                                            class="save-btn d-inline-flex align-items-center gap-2"
                                                            style="height: auto; padding: 10px 18px; font-size: 14px; background: linear-gradient(to right, #ef4444, #dc2626);"
                                                            title="Cancel registration">
                                                            <i class="bi bi-x-circle"></i>
                                                            Cancel
                                                        </button>
                                                    </form>
                                                <?php elseif ($status === 'cancelled'): ?>
                                                    <span class="text-white-50 small">Cancelled</span>
                                                <?php elseif ($eventTs && $eventTs < time()): ?>
                                                    <span class="text-white-50 small">Event ended</span>
                                                <?php else: ?>
                                                    <span class="text-white-50 small">—</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">
                                        You have not registered for any events yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
