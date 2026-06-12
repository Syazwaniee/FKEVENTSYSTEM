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

$club = $role === 'committee' ? getCommitteeClubForUser($userId) : null;
$error = '';
$success = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $date = trim($_POST['date'] ?? '');
    $venue = trim($_POST['venue'] ?? '');
    $capacity = (int) ($_POST['capacity'] ?? 0);
    $desc = trim($_POST['desc'] ?? '');

    if ($role === 'committee') {
        $clubId = $club ? (int) $club['Club_id'] : 0;
    } else {
        $clubId = (int) ($_POST['club_id'] ?? 0);
    }

    if (empty($name) || empty($date) || empty($venue) || $capacity <= 0) {
        $error = 'All fields are required and capacity must be greater than 0.';
    } elseif ($clubId <= 0) {
        $error = $role === 'committee'
            ? 'You must be assigned to a club before creating events.'
            : 'Please select a club.';
    } else {
        if (createEvent($name, $desc, $date, $venue, $capacity, $clubId, $userId)) {
            header('Location: clubEvents.php?msg=' . urlencode('Event created successfully.') . '&msg_type=success');
            exit;
        }
        $error = 'Could not save event. Please try again.';
    }
}

$clubs = $role === 'admin' ? getAllClubs() : null;

$navBase = '../';
$activeNav = 'events';
$useCommitteeHeader = $role === 'committee';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
</head>

<body>
    <?php if ($useCommitteeHeader): ?>
        <?php include __DIR__ . '/../INCLUDE/CommitteeHeader.php'; ?>
    <?php else: ?>
        <?php include __DIR__ . '/../INCLUDE/AdminHeader.php'; ?>
    <?php endif; ?>

    <div class="add-user-container">
        <h1 class="add-user-title">Create New Event</h1>
        <p class="add-user-subtitle">
            <?php if ($role === 'committee' && $club): ?>
                Add an event for <?= htmlspecialchars($club['Club_name']) ?>
            <?php elseif ($role === 'committee'): ?>
                You need a club assignment to create events.
            <?php else: ?>
                Add a new club event.
            <?php endif; ?>
        </p>

        <div class="add-user-box">
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <?php if ($role === 'committee' && !$club): ?>
                <div class="alert alert-warning mb-0">
                    Please contact an administrator to be assigned to a club first.
                </div>
            <?php else: ?>
                <form method="POST" action="">
                    <div class="row">
                        <div class="col-lg-6 mb-4">
                            <label class="form-label-custom">Event title</label>
                            <input type="text" name="name" class="form-input-custom" required
                                value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                        </div>
                        <div class="col-lg-6 mb-4">
                            <label class="form-label-custom">Date & time</label>
                            <input type="datetime-local" name="date" class="form-input-custom" required
                                value="<?= htmlspecialchars($_POST['date'] ?? '') ?>">
                        </div>
                        <div class="col-lg-6 mb-4">
                            <label class="form-label-custom">Venue</label>
                            <input type="text" name="venue" class="form-input-custom" required
                                value="<?= htmlspecialchars($_POST['venue'] ?? '') ?>">
                        </div>
                        <div class="col-lg-6 mb-4">
                            <label class="form-label-custom">Max participants</label>
                            <input type="number" name="capacity" class="form-input-custom" min="1" required
                                value="<?= htmlspecialchars($_POST['capacity'] ?? '') ?>">
                        </div>
                        <?php if ($role === 'admin' && $clubs): ?>
                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">Club</label>
                                <select name="club_id" class="form-input-custom" required>
                                    <option value="">Select club</option>
                                    <?php while ($c = $clubs->fetch_assoc()): ?>
                                        <option value="<?= (int) $c['club_id'] ?>"
                                            <?= ((string) ($_POST['club_id'] ?? '') === (string) $c['club_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($c['Club_name']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        <?php endif; ?>
                        <div class="col-12 mb-4">
                            <label class="form-label-custom">Description</label>
                            <textarea name="desc" class="form-input-custom" rows="4"
                                placeholder="Optional event description"><?= htmlspecialchars($_POST['desc'] ?? '') ?></textarea>
                        </div>
                    </div>

                    <div class="submit-flex submit-flex-center">
                        <button type="submit" class="save-btn">
                            <i class="bi bi-check-circle-fill"></i>
                            Create Event
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
</body>

</html>
