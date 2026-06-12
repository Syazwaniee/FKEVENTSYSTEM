<!-- editClub.php -->

<?php
require_once '../INCLUDE/db.php';

$clubId = (int) ($_GET['Club_id'] ?? $_POST['Club_id'] ?? 0);
$club = getClubById($clubId);

$update_success = null;
$update_message = '';
$redirect = false;

if (!$club) {
    $update_success = false;
    $update_message = 'Club not found.';
}

if ($club && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['update_club'])) {
    $clubName = trim($_POST['club_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $advisorName = trim($_POST['advisor_name'] ?? '');
    $clubStatus = (int) ($_POST['club_status'] ?? 1);
    $maxCapacity = trim($_POST['max_capacity'] ?? '');

    $result = updateClub($clubId, $clubName, $description, $advisorName, $clubStatus, $maxCapacity);
    $update_success = $result['success'];
    $update_message = $result['message'];

    if ($update_success) {
        $redirect = true;
    } else {
        $club = [
            'Club_id' => $clubId,
            'club_name' => $clubName,
            'description' => $description,
            'advisor_name' => $advisorName,
            'club_status' => (string) $clubStatus,
            'max_capacity' => $maxCapacity,
        ];
    }
}

$form = $club ?? [
    'Club_id' => $clubId,
    'club_name' => '',
    'description' => '',
    'advisor_name' => '',
    'club_status' => '1',
    'max_capacity' => '',
];
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Edit Club
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS -->
    <link rel="stylesheet"
        href="../CSS/style.css">

    <link rel="stylesheet"
        href="../CSS/adminHeader.css">

    <link rel="stylesheet"
        href="../CSS/addUser.css">

</head>

<body>

    <?php if ($redirect): ?>

        <div class="add-user-container mt-5">
            <div class="alert alert-success mb-4 text-center">
                <?= htmlspecialchars($update_message) ?>
                <br>
                Redirecting to club management…
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = '../Module2/clubManagement.php';
            }, 1800);
        </script>

    <?php else: ?>

        <?php include '../INCLUDE/AdminHeader.php'; ?>

        <div class="add-user-container">

            <h1 class="add-user-title">
                Edit Club
            </h1>

            <p class="add-user-subtitle">
                Update club information, advisor, capacity, and status.
            </p>

            <div class="add-user-box">

                <?php if ($update_success === false && $update_message !== ''): ?>
                    <div class="alert alert-danger"><?= htmlspecialchars($update_message) ?></div>
                <?php endif; ?>

                <?php if ($club): ?>

                    <form method="POST"
                        action="">

                        <input type="hidden"
                            name="Club_id"
                            value="<?= (int) $form['Club_id'] ?>">

                        <div class="row">

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Club name
                                </label>
                                <input type="text"
                                    name="club_name"
                                    class="form-input-custom"
                                    placeholder="Enter club name"
                                    value="<?= htmlspecialchars($form['club_name']) ?>"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Advisor name
                                </label>
                                <input type="text"
                                    name="advisor_name"
                                    class="form-input-custom"
                                    placeholder="Enter advisor name"
                                    value="<?= htmlspecialchars($form['advisor_name']) ?>"
                                    required>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label-custom">
                                    Description
                                </label>
                                <textarea name="description"
                                    class="form-input-custom"
                                    rows="4"
                                    placeholder="Enter club description"><?= htmlspecialchars($form['description']) ?></textarea>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Maximum capacity
                                </label>
                                <input type="number"
                                    name="max_capacity"
                                    class="form-input-custom"
                                    placeholder="e.g. 50"
                                    min="1"
                                    value="<?= htmlspecialchars($form['max_capacity']) ?>"
                                    required>
                            </div>

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Club status
                                </label>
                                <select class="form-input-custom"
                                    name="club_status"
                                    required>
                                    <option value="1"
                                        <?= ($form['club_status'] === '1') ? 'selected' : '' ?>>
                                        Active
                                    </option>
                                    <option value="0"
                                        <?= ($form['club_status'] === '0') ? 'selected' : '' ?>>
                                        Inactive
                                    </option>
                                </select>
                            </div>

                        </div>

                        <div class="submit-flex">
                            <button type="submit"
                                name="update_club"
                                class="save-btn">
                                <i class="bi bi-pencil-square"></i>
                                Update Club
                            </button>
                            <a href="../Module2/clubManagement.php"
                                class="cancel-btn text-decoration-none text-center">
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
