<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || ($_SESSION['user']['role'] ?? '') !== 'committee') {
    header('Location: ../login.php');
    exit;
}

$userId = (int) $_SESSION['user']['User_id'];
$update_success = null;
$update_message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_club_description'])) {
    $clubId = (int) ($_POST['club_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');

    $result = updateClubDescriptionByCommittee($userId, $clubId, $description);
    $update_success = $result['success'];
    $update_message = $result['message'];
}

$club = getCommitteeClubForUser($userId);
$clubMembers = $club ? getClubMembersByClubId((int) $club['Club_id']) : [];

$navBase = '../';
$activeNav = 'club';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/UserManagement.css">
</head>

<body>

    <?php include __DIR__ . '/../INCLUDE/CommitteeHeader.php'; ?>

    <div class="add-user-container">

        <h1 class="add-user-title">Club Management</h1>
        <p class="add-user-subtitle">View your club details and members.</p>

        <?php if (!$club): ?>
            <div class="add-user-box">
                <div class="alert alert-warning mb-0">
                    You are not assigned to a club yet. Please contact an administrator.
                </div>
            </div>
        <?php else: ?>
            <?php if ($update_success === true): ?>
                <div class="alert alert-success"><?= htmlspecialchars($update_message) ?></div>
            <?php elseif ($update_success === false): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($update_message) ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="add-user-box mb-4" id="clubInfoForm">
                <input type="hidden" name="club_id" value="<?= (int) $club['Club_id'] ?>">

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
                        <?php
                        $isActive = strcasecmp(trim($club['club_status']), 'inactive') !== 0
                            && $club['club_status'] !== '0';
                        ?>
                        <p class="mb-0">
                            <span class="status-badge <?= $isActive ? 'active-status' : 'inactive-status' ?>">
                                <i class="bi bi-circle-fill"></i>
                                <?= htmlspecialchars($isActive ? 'Active' : 'Inactive') ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Your committee role</label>
                        <p class="text-white mb-0">
                            <?= htmlspecialchars($club['Role_name'] !== '' ? $club['Role_name'] : '—') ?>
                        </p>
                    </div>
                    <div class="col-12 mb-0">
                        <label class="form-label-custom" for="description">Description</label>
                        <textarea
                            id="description"
                            name="description"
                            class="form-input-custom"
                            rows="4"
                            placeholder="Enter club description"><?= htmlspecialchars($club['Description']) ?></textarea>
                    </div>
                </div>

                <div class="submit-flex submit-flex-center">
                    <button type="submit" name="update_club_description" class="save-btn">
                        <i class="bi bi-check-circle-fill"></i>
                        Update
                    </button>
                    <button type="reset" class="cancel-btn">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </button>
                </div>
            </form>

            <div class="table-box">
                <h5 class="text-white mb-3">Club members</h5>
                <table class="table custom-table align-middle">
                    <thead>
                        <tr>
                            <th>Member name</th>
                            <th>Student ID</th>
                            <th>Role</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Joined date</th>
                            <th>Member status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($clubMembers) > 0): ?>
                            <?php foreach ($clubMembers as $member): ?>
                                <?php
                                $role = strtolower((string) ($member['role'] ?? ''));
                                $roleClass = $role === 'committee'
                                    ? 'committee-role'
                                    : ($role === 'admin' ? 'admin-role' : 'student-role');
                                $memberStatus = trim((string) ($member['Member_status'] ?? ''));
                                $memberActive = $memberStatus === ''
                                    || strcasecmp($memberStatus, 'inactive') !== 0;
                                ?>
                                <tr>
                                    <td><?= htmlspecialchars($member['FullName']) ?></td>
                                    <td><?= htmlspecialchars($member['Student_id']) ?></td>
                                    <td>
                                        <span class="role-badge <?= htmlspecialchars($roleClass) ?>">
                                            <?= htmlspecialchars(ucfirst($role ?: 'member')) ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($member['Email'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($member['Phone_num'] ?? '—') ?></td>
                                    <td><?= htmlspecialchars($member['Joined_date'] ?? '—') ?></td>
                                    <td>
                                        <span class="status-badge <?= $memberActive ? 'active-status' : 'inactive-status' ?>">
                                            <i class="bi bi-circle-fill"></i>
                                            <?= htmlspecialchars($memberActive ? 'Active' : 'Inactive') ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">No members found for this club.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    </div>

</body>

</html>
