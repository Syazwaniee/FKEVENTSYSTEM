<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id'])) {
    header('Location: ../login.php');
    exit;
}

$user = $_SESSION['user'];
$role = $user['role'] ?? '';
$userId = (int) $user['User_id'];

if (!in_array($role, ['committee', 'admin'], true)) {
    header('Location: ../login.php');
    exit;
}

$eventId = (int) ($_GET['event_id'] ?? $_POST['event_id'] ?? 0);
$event = getEventById($eventId);

$update_success = null;
$update_message = '';
$redirect = false;

if (!$event) {
    $update_success = false;
    $update_message = 'Event not found.';
}

if ($event && $role === 'committee') {
    $clubId = getClubIdByCommittee($userId);
    if ($clubId === null || (int) $clubId !== (int) $event['Club_id']) {
        header('Location: clubEvents.php');
        exit;
    }
}

if ($event && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_event'])) {
    $name = trim($_POST['name'] ?? '');
    $desc = trim($_POST['desc'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $status = trim($_POST['status'] ?? 'Upcoming');

    if ($name === '' || $date === '' || $venue === '' || $capacity <= 0) {
        $update_success = false;
        $update_message = 'Please complete all required fields.';
    } elseif (updateEvent($eventId, $name, $desc, $date, $venue, $capacity, $status)) {
        $update_success = true;
        $update_message = 'Event updated successfully.';
        $redirect = true;
    } else {
        $update_success = false;
        $update_message = 'Update failed. Please try again.';
    }
}

$form = $event ?? null;
if ($form && $update_success === false && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $form = array_merge($form, [
        'Event_Name' => trim($_POST['name'] ?? $form['Event_Name']),
        'EventDesc' => trim($_POST['desc'] ?? $form['EventDesc']),
        'Event_Date' => trim($_POST['date'] ?? $form['Event_Date']),
        'Venue' => trim($_POST['venue'] ?? $form['Venue']),
        'Student_Capacity' => (int) ($_POST['capacity'] ?? $form['Student_Capacity']),
        'Event_Status' => trim($_POST['status'] ?? $form['Event_Status']),
    ]);
}

$navBase = '../';
$activeNav = 'events';
$useCommitteeHeader = $role === 'committee';

$statusOptions = ['Upcoming', 'Ongoing', 'Completed', 'Cancelled'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
</head>

<body>

    <?php if ($redirect): ?>

        <div class="add-user-container mt-5">
            <div class="alert alert-success mb-4 text-center">
                <?= htmlspecialchars($update_message) ?>
                <br>
                Redirecting to club events…
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = 'clubEvents.php?msg=<?= urlencode($update_message) ?>&msg_type=success';
            }, 1800);
        </script>

    <?php else: ?>

        <?php if ($useCommitteeHeader): ?>
            <?php include __DIR__ . '/../INCLUDE/CommitteeHeader.php'; ?>
        <?php else: ?>
            <?php include __DIR__ . '/../INCLUDE/AdminHeader.php'; ?>
        <?php endif; ?>

        <div class="add-user-container">

            <h1 class="add-user-title">Edit Event</h1>

            <p class="add-user-subtitle">
                Update event details, venue, capacity, and status.
            </p>

            <div class="add-user-box">

                <?php if ($update_success === false && $update_message !== ''): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($update_message) ?></div>
                <?php endif; ?>

                <?php if ($form): ?>

                    <form method="POST" action="">
                        <input type="hidden" name="event_id" value="<?= (int) $form['Event_id'] ?>">

                        <div class="row">
                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">Event title</label>
                                <input type="text"
                                    name="name"
                                    class="form-input-custom"
                                    placeholder="Enter event title"
                                    value="<?= htmlspecialchars($form['Event_Name']) ?>"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">Date & time</label>
                                <input type="datetime-local"
                                    name="date"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars(date('Y-m-d\TH:i', strtotime($form['Event_Date']))) ?>"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">Venue</label>
                                <input type="text"
                                    name="venue"
                                    class="form-input-custom"
                                    placeholder="Enter venue"
                                    value="<?= htmlspecialchars($form['Venue']) ?>"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">Max participants</label>
                                <input type="number"
                                    name="capacity"
                                    class="form-input-custom"
                                    placeholder="e.g. 50"
                                    min="1"
                                    value="<?= (int) $form['Student_Capacity'] ?>"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">Event status</label>
                                <select name="status" class="form-input-custom" required>
                                    <?php foreach ($statusOptions as $opt): ?>
                                        <option value="<?= htmlspecialchars($opt) ?>"
                                            <?= strcasecmp((string) $form['Event_Status'], $opt) === 0 ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($opt) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label-custom">Description</label>
                                <textarea name="desc"
                                    class="form-input-custom"
                                    rows="4"
                                    placeholder="Enter event description"><?= htmlspecialchars($form['EventDesc'] ?? '') ?></textarea>
                            </div>
                        </div>

                        <div class="submit-flex">
                            <button type="submit" name="update_event" class="save-btn">
                                <i class="bi bi-pencil-square"></i>
                                Update Event
                            </button>
                            <a href="clubEvents.php" class="cancel-btn text-decoration-none text-center">
                                <i class="bi bi-x-circle"></i>
                                Cancel
                            </a>
                        </div>
                    </form>

                <?php endif; ?>

            </div>

        </div>

    <?php endif; ?>

</body>

</html>
