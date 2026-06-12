<!-- editAssignClubCommittee.php -->
<?php
require_once '../INCLUDE/db.php';

$clubCommitteeId = (int) ($_GET['Club_committee_id'] ?? $_POST['Club_committee_id'] ?? 0);
$assignment = getClubCommitteeById($clubCommitteeId);

$update_success = null;
$update_message = '';

if (!$assignment) {
    $update_success = false;
    $update_message = 'Assignment not found.';
}

if ($assignment && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_assignment'])) {
    $userId = (int) ($_POST['user_id'] ?? 0);
    $clubId = (int) ($_POST['club_id'] ?? 0);
    $committeeRoleId = (int) ($_POST['committee_role'] ?? 0);
    $startDate = trim($_POST['start_date'] ?? '');

    $result = updateClubCommittee(
        $clubCommitteeId,
        $userId,
        $clubId,
        $committeeRoleId,
        $startDate
    );
    $update_success = $result['success'];
    $update_message = $result['message'];

    if ($update_success) {
        header(
            'Location: assignClubCommittee.php?msg=' . urlencode($update_message) . '&msg_type=success'
        );
        exit;
    }

    $assignment = [
        'Club_committee_id' => $clubCommitteeId,
        'User_id' => $userId,
        'Club_id' => $clubId,
        'Committee_role_id' => $committeeRoleId,
        'Assigned_date' => $startDate,
    ];
}

$form = $assignment ?? [
    'Club_committee_id' => $clubCommitteeId,
    'User_id' => '',
    'Club_id' => '',
    'Committee_role_id' => '',
    'Assigned_date' => date('Y-m-d'),
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Club Committee Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
</head>

<body>

    <?php include '../INCLUDE/AdminHeader.php'; ?>

    <div class="add-user-container">

        <h1 class="add-user-title">Edit Committee Assignment</h1>
        <p class="add-user-subtitle">Update club, member, role, or start date.</p>

        <div class="add-user-box">

            <?php if ($update_success === false && $update_message !== ''): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($update_message) ?></div>
            <?php endif; ?>

            <?php if ($assignment): ?>
                <form method="POST" action="">
                    <input type="hidden" name="Club_committee_id" value="<?= (int) $form['Club_committee_id'] ?>">

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
                                            <?= ((string) ($form['Club_id'] ?? '') === (string) $club['Club_id']) ? 'selected' : '' ?>>
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
                                            <?= ((string) ($form['User_id'] ?? '') === (string) $u['User_id']) ? 'selected' : '' ?>>
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
                                            <?= ((string) ($form['Committee_role_id'] ?? '') === (string) $role['Committee_role_id']) ? 'selected' : '' ?>>
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
                                value="<?= htmlspecialchars($form['Assigned_date'] ?? '') ?>"
                                required>
                        </div>
                    </div>

                    <div class="submit-flex submit-flex-center">
                        <button type="submit" name="update_assignment" class="save-btn">
                            <i class="bi bi-check-circle-fill"></i>
                            Save Changes
                        </button>
                        <a href="../Module2/assignClubCommittee.php" class="cancel-btn text-decoration-none text-center">
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
