<!-- assignClubCommittee.php -->
<?php
require_once __DIR__ . '/../INCLUDE/db.php';

$assign_success = null;
$assign_message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['delete_assignment'])) {
    $result = deleteClubCommittee((int) ($_POST['Club_committee_id'] ?? 0));
    header(
        'Location: assignClubCommittee.php?msg=' . urlencode($result['message'])
        . '&msg_type=' . ($result['success'] ? 'success' : 'danger')
    );
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['assign_committee'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $clubId = (int) ($_POST['club_id'] ?? 0);
    $committeeRoleId = (int) ($_POST['committee_role'] ?? 0);
    $startDate = trim($_POST['start_date'] ?? '');

    $result = assignClubCommittee($userId, $clubId, $committeeRoleId, $startDate);
    $assign_success = $result['success'];
    $assign_message = $result['message'];

    if ($assign_success) {
        $_POST = [];
    }
}

$flashMessage = $_GET['msg'] ?? null;
$flashType = in_array($_GET['msg_type'] ?? '', ['success', 'danger'], true)
    ? $_GET['msg_type']
    : 'danger';

$assignments = getClubCommitteeAssignments();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assign Club Committee</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/UserManagement.css">
</head>

<body>

    <?php include '../INCLUDE/AdminHeader.php'; ?>

    <div class="add-user-container">

        <h1 class="add-user-title">Assign Club Committee</h1>
        <p class="add-user-subtitle">
            Assign committee members to clubs with a role and start date.
        </p>

        <div class="add-user-box">

            <?php if ($assign_success === true): ?>
                <div class="alert alert-success"><?= htmlspecialchars($assign_message) ?></div>
            <?php elseif ($assign_success === false): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($assign_message) ?></div>
            <?php elseif ($flashMessage): ?>
                <div class="alert alert-<?= htmlspecialchars($flashType) ?>">
                    <?= htmlspecialchars($flashMessage) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">

                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Club name</label>
                        <select class="form-input-custom" name="club_id" required>
                            <option value="">Select club</option>
                            <?php
                            $clubs = getClubs();
                            if ($clubs && $clubs->num_rows > 0):
                                while ($club = $clubs->fetch_assoc()):
                                    ?>
                                    <option value="<?= (int) $club['Club_id'] ?>"
                                        <?= (($_POST['club_id'] ?? '') == $club['Club_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($club['Club_name']) ?>
                                    </option>
                                    <?php
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Member name</label>
                        <select class="form-input-custom" name="user_id" required>
                            <option value="">Select committee member</option>
                            <?php
                            $committeeUsers = getCommitteeUsers();
                            if ($committeeUsers && $committeeUsers->num_rows > 0):
                                while ($u = $committeeUsers->fetch_assoc()):
                                    ?>
                                    <option value="<?= (int) $u['User_id'] ?>"
                                        <?= (($_POST['user_id'] ?? '') == $u['User_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['FullName']) ?>
                                        (<?= htmlspecialchars($u['Student_id']) ?>)
                                    </option>
                                    <?php
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Committee role</label>
                        <select class="form-input-custom" name="committee_role" required>
                            <option value="">Select role</option>
                            <?php
                            $roles = getAssignedRoles();
                            if ($roles && $roles->num_rows > 0):
                                while ($role = $roles->fetch_assoc()):
                                    ?>
                                    <option value="<?= (int) $role['Committee_role_id'] ?>"
                                        <?= (($_POST['committee_role'] ?? '') == $role['Committee_role_id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($role['Role_name']) ?>
                                    </option>
                                    <?php
                                endwhile;
                            endif;
                            ?>
                        </select>
                    </div>

                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">Start date</label>
                        <input type="date"
                            class="form-input-custom"
                            name="start_date"
                            value="<?= htmlspecialchars($_POST['start_date'] ?? date('Y-m-d')) ?>"
                            required>
                    </div>

                </div>

                <div class="submit-flex submit-flex-center">
                    <button type="submit" name="assign_committee" class="save-btn">
                        <i class="bi bi-person-check-fill"></i>
                        Assign Committee
                    </button>
                </div>
            </form>
        </div>

        <div class="table-box mt-4">
            <h5 class="text-white mb-3">Assigned club committees</h5>
            <table class="table custom-table align-middle">
                <thead>
                    <tr>
                        <th>Club name</th>
                        <th>Member name</th>
                        <th>Student ID</th>
                        <th>Committee role</th>
                        <th>Start date</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($assignments) > 0): ?>
                        <?php foreach ($assignments as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['Club_name']) ?></td>
                                <td><?= htmlspecialchars($row['FullName']) ?></td>
                                <td><?= htmlspecialchars($row['Student_id']) ?></td>
                                <td><?= htmlspecialchars($row['Role_name']) ?></td>
                                <td><?= htmlspecialchars($row['Assigned_date']) ?></td>
                                <td>
                                    <div class="action-flex justify-content-center">
                                        <a href="editAssignClubCommittee.php?Club_committee_id=<?= (int) $row['Club_committee_id'] ?>"
                                            class="edit-btn"
                                            title="Edit assignment">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <form method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Remove this committee assignment?');">
                                            <input type="hidden" name="delete_assignment" value="1">
                                            <input type="hidden"
                                                name="Club_committee_id"
                                                value="<?= (int) $row['Club_committee_id'] ?>">
                                            <button type="submit" class="delete-btn" title="Delete assignment">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No committee assignments yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>

</html>
