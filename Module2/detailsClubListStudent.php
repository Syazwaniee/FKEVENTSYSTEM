<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || ($_SESSION['user']['role'] ?? '') !== 'student') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$clubId = (int) ($_GET['Club_id'] ?? $_POST['Club_id'] ?? 0);
$join_success = null;
$join_message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['join_club'])) {
    $result = joinClubAsStudent($userId, $clubId);
    $join_success = $result['success'];
    $join_message = $result['message'];

    if ($join_success) {
        header(
            'Location: detailsClubListStudent.php?Club_id=' . $clubId
            . '&msg=' . urlencode($join_message)
            . '&msg_type=success'
        );
        exit;
    }
}

$flashMessage = $_GET['msg'] ?? null;
$flashType = in_array($_GET['msg_type'] ?? '', ['success', 'danger'], true)
    ? $_GET['msg_type']
    : 'danger';

$club = getClubDetailsForStudent($clubId, $userId);
$navBase = '../';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/UserManagement.css">
</head>

<body>

    <?php include __DIR__ . '/../INCLUDE/StudentHeader.php'; ?>

    <div class="add-user-container">

        <h1 class="add-user-title">Club Details</h1>
        <p class="add-user-subtitle">View club information and join if slots are available.</p>

        <a href="viewClubListStudent.php" class="cancel-btn text-decoration-none d-inline-flex align-items-center gap-2 mb-4">
            <i class="bi bi-arrow-left"></i>
            Back to club list
        </a>

        <?php if (!$club): ?>
            <div class="add-user-box">
                <div class="alert alert-warning mb-0">Club not found.</div>
            </div>
        <?php else: ?>
            <?php if ($join_success === false && $join_message !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($join_message) ?></div>
            <?php elseif ($flashMessage): ?>
                <div class="alert alert-<?= htmlspecialchars($flashType) ?>">
                    <?= htmlspecialchars($flashMessage) ?>
                </div>
            <?php endif; ?>

            <div class="add-user-box mb-4">
                <h5 class="text-white mb-4">Club information</h5>
                <div class="row">
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Club name</label>
                        <p class="text-white mb-0"><?= htmlspecialchars($club['Club_name']) ?></p>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Advisor name</label>
                        <p class="text-white mb-0">
                            <?= htmlspecialchars($club['Advisor_name'] !== '' ? $club['Advisor_name'] : '—') ?>
                        </p>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Club status</label>
                        <?php $isActive = (int) $club['Is_active'] === 1; ?>
                        <p class="mb-0">
                            <span class="status-badge <?= $isActive ? 'active-status' : 'inactive-status' ?>">
                                <i class="bi bi-circle-fill"></i>
                                <?= htmlspecialchars($isActive ? 'Active' : 'Inactive') ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Available slots</label>
                        <p class="text-white mb-0">
                            <?php
                            $cap = $club['maxCapacity'];
                            echo $cap !== null ? htmlspecialchars((string) $cap) : '—';
                            ?>
                        </p>
                    </div>
                    <div class="col-12 mb-0">
                        <label class="form-label-custom">Description</label>
                        <p class="text-white mb-0">
                            <?= htmlspecialchars($club['Description'] !== '' ? $club['Description'] : '—') ?>
                        </p>
                    </div>
                </div>

                <div class="submit-flex submit-flex-center mt-4">
                    <?php if ($club['is_member']): ?>
                        <span class="status-badge active-status">
                            <i class="bi bi-check-circle-fill"></i>
                            You are already a member
                        </span>
                    <?php elseif (!$isActive): ?>
                        <span class="status-badge inactive-status">
                            <i class="bi bi-x-circle-fill"></i>
                            Club is inactive
                        </span>
                    <?php elseif ($club['maxCapacity'] === null || $club['maxCapacity'] <= 0): ?>
                        <span class="status-badge inactive-status">
                            <i class="bi bi-x-circle-fill"></i>
                            No slots available
                        </span>
                    <?php else: ?>
                        <form method="POST" action="" class="d-inline"
                            onsubmit="return confirm('Join this club? One slot will be reserved for you.');">
                            <input type="hidden" name="Club_id" value="<?= (int) $club['Club_id'] ?>">
                            <button type="submit" name="join_club" class="save-btn">
                                <i class="bi bi-person-plus-fill"></i>
                                Join Club
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>
