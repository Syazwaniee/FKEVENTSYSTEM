<?php

require_once __DIR__ . '/../INCLUDE/db.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'committee') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$eventId = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
$flashMessage = null;
$flashType = 'success';

if ($eventId <= 0 || !committeeCanManageEvent($userId, $eventId)) {
    header(
        'Location: ../Module3/clubEvents.php?msg='
        . urlencode('Invalid event or you are not allowed to manage attendance for this event.')
        . '&msg_type=danger'
    );
    exit;
}

$eventContext = getEventWithClub($eventId);
if (!$eventContext) {
    header(
        'Location: ../Module3/clubEvents.php?msg='
        . urlencode('Event not found.')
        . '&msg_type=danger'
    );
    exit;
}

$clubName = (string) $eventContext['Club_name'];
$eventName = (string) $eventContext['Event_Name'];
$eventDate = (string) $eventContext['Event_Date'];
$defaultDate = $eventDate ? date('Y-m-d', strtotime($eventDate)) : date('Y-m-d');
$defaultTime = $eventDate ? date('H:i', strtotime($eventDate)) : date('H:i');
$registrants = getRegisteredStudentsForEvent($eventId);

if (isset($_POST['save_attendance'])) {
    $studentId = trim((string) ($_POST['student_id'] ?? ''));
    $attendanceDate = trim((string) ($_POST['attendance_date'] ?? ''));
    $attendanceTime = trim((string) ($_POST['attendance_time'] ?? ''));
    $attendanceStatus = trim((string) ($_POST['attendance_status'] ?? 'Present'));
    $volunteerStatus = trim((string) ($_POST['volunteer_status'] ?? 'No'));

    $studentName = '';
    foreach ($registrants as $student) {
        if ($student['Student_id'] === $studentId) {
            $studentName = $student['display_name'];
            break;
        }
    }

    if ($studentName === '' || $studentId === '') {
        $flashMessage = 'Please select a registered student.';
        $flashType = 'danger';
    } elseif ($attendanceDate === '' || $attendanceTime === '') {
        $flashMessage = 'Attendance date and time are required.';
        $flashType = 'danger';
    } elseif (
        insertEventAttendanceRecord(
            $eventId,
            $studentName,
            $studentId,
            $clubName,
            $eventName,
            $attendanceDate,
            $attendanceTime,
            $attendanceStatus,
            $volunteerStatus
        )
    ) {
        header(
            'Location: manage-event-attendance.php?event_id='
            . $eventId
            . '&msg='
            . urlencode('Attendance saved successfully.')
            . '&msg_type=success'
        );
        exit;
    } else {
        $flashMessage = 'Could not save attendance. Please try again.';
        $flashType = 'danger';
    }
}

if (!$flashMessage && !empty($_GET['msg'])) {
    $flashMessage = (string) $_GET['msg'];
    $flashType = in_array($_GET['msg_type'] ?? '', ['success', 'danger'], true)
        ? (string) $_GET['msg_type']
        : 'success';
}

$attendanceResult = getAttendanceByEventId($eventId);
$navBase = '../';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Event Attendance</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/UserManagement.css">
    <link rel="stylesheet" href="../CSS/module4_style.css">
</head>

<body>

    <?php include __DIR__ . '/../INCLUDE/CommitteeHeader.php'; ?>

    <div class="add-user-container attendance-page">

        <div class="top-flex">
            <div>
                <h1 class="add-user-title mb-2">Manage Event Attendance</h1>
                <p class="add-user-subtitle mb-0">
                    <?= htmlspecialchars($eventName) ?>
                    &middot;
                    <?= htmlspecialchars($clubName) ?>
                    &middot;
                    <?= $eventDate ? date('d M Y, g:i A', strtotime($eventDate)) : '—' ?>
                </p>
            </div>
            <a href="../Module3/clubEvents.php"
                class="save-btn text-decoration-none d-inline-flex align-items-center gap-2"
                style="height: auto; padding: 10px 18px; font-size: 14px; background: #3f4654;">
                <i class="bi bi-arrow-left"></i>
                Back to Club Events
            </a>
        </div>

        <?php if ($flashMessage): ?>
            <div class="alert alert-<?= htmlspecialchars($flashType) ?> mt-3">
                <?= htmlspecialchars($flashMessage) ?>
            </div>
        <?php endif; ?>

        <div class="add-user-box mt-4">
            <h5 class="text-white mb-3">Record attendance</h5>

            <?php if (count($registrants) === 0): ?>
                <div class="alert alert-warning mb-0">
                    No registered students for this event yet. Students must register before you can record attendance.
                </div>
            <?php else: ?>
               
                <form method="POST" class="modern-form">
                    <input type="hidden" name="event_id" value="<?= $eventId ?>">

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label-custom">Club</label>
                            <input type="text" class="form-input-custom" value="<?= htmlspecialchars($clubName) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Event</label>
                            <input type="text" class="form-input-custom" value="<?= htmlspecialchars($eventName) ?>" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Student</label>
                            <select name="student_id" class="form-input-custom" required>
                                <option value="">Select registered student</option>
                                <?php foreach ($registrants as $student): ?>
                                    <option value="<?= htmlspecialchars($student['Student_id']) ?>">
                                        <?= htmlspecialchars($student['display_name']) ?>
                                        (<?= htmlspecialchars($student['Student_id']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Date</label>
                            <input type="date" name="attendance_date" class="form-input-custom"
                                value="<?= htmlspecialchars($defaultDate) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label-custom">Time</label>
                            <input type="time" name="attendance_time" class="form-input-custom"
                                value="<?= htmlspecialchars($defaultTime) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Attendance status</label>
                            <select name="attendance_status" class="form-input-custom">
                                <option>Present</option>
                                <option>Late</option>
                                <option>Absent</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label-custom">Volunteer / helper</label>
                            <select name="volunteer_status" class="form-input-custom">
                                <option>No</option>
                                <option>Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="submit-flex mt-3">
                        <button type="submit" name="save_attendance" class="save-btn">
                            <i class="bi bi-check-lg"></i>
                            Save attendance
                        </button>
                    </div>

                     <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <button type="button" class="save-btn" onclick="toggleQRCode()">
                            <i class="bi bi-qr-code"></i>
                            Show QR Code
                        </button>
                    </div>
                </div>

                <div id="qrCodeDisplay" style="display:none;">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="qr-code-display text-center" style="background: #2a2e39; padding: 20px; border-radius: 8px;">
                                <img src="../Module4/qrcode.png" alt="Event QR Code" style="max-width: 100%; width: 200px; height: auto; border: 2px solid #3f4654;">
                                <p class="text-white-50 mt-2 mb-0" style="font-size: 12px;">Scan this QR code to register</p>
                            </div>
                        </div>
                    </div>
                </div>

                </form>
            <?php endif; ?>
        </div>

        <div class="table-box mt-4">
            <h5 class="text-white mb-3">Attendance records for this event</h5>
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Volunteer</th>
                        <th>Points</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($attendanceResult && $attendanceResult->num_rows > 0): ?>
                        <?php while ($row = $attendanceResult->fetch_assoc()): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['student_name']) ?></td>
                                <td><?= htmlspecialchars($row['student_id']) ?></td>
                                <td><?= htmlspecialchars($row['attendance_date']) ?></td>
                                <td><?= htmlspecialchars($row['attendance_time']) ?></td>
                                <td><?= htmlspecialchars($row['attendance_status']) ?></td>
                                <td><?= htmlspecialchars($row['volunteer_status']) ?></td>
                                <td><?= (int) $row['points'] ?></td>
                                <td>
                                    <div class="action-flex justify-content-center">
                                        <a href="../Module4/edit.php?id=<?= (int) $row['id'] ?>&event_id=<?= $eventId ?>"
                                            class="edit-btn" title="Edit">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <a href="../Module4/delete.php?id=<?= (int) $row['id'] ?>&event_id=<?= $eventId ?>"
                                            class="delete-btn" title="Delete"
                                            onclick="return confirm('Delete this attendance record?');">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">No attendance recorded for this event yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="add-user-box mt-4 point-rules-box">
            <h5 class="text-white mb-3">Attendance point rules</h5>
            <ul class="text-white-50 mb-0">
                <li>Present on time = +10 points</li>
                <li>Late arrival = +5 points</li>
                <li>Absent without notice = -10 points</li>
                <li>Volunteer / helper = +5 bonus points</li>
            </ul>
        </div>

    </div>

    <script>
        function toggleQRCode() {
            const qrDisplay = document.getElementById('qrCodeDisplay');
            if (qrDisplay.style.display === 'none') {
                qrDisplay.style.display = 'block';
            } else {
                qrDisplay.style.display = 'none';
            }
        }
    </script>

</body>

</html>
