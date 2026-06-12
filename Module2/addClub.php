<!-- addClub.php -->

<?php
require_once '../INCLUDE/db.php';

$insert_success = null;
$insert_message = '';

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && isset($_POST['register_club'])) {
    $clubName = trim($_POST['club_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $advisorName = trim($_POST['advisor_name'] ?? '');
    $clubStatus = (int) ($_POST['club_status'] ?? 1);
    $maxCapacity = trim($_POST['max_capacity'] ?? '');

    $result = insertClub($clubName, $description, $advisorName, $clubStatus, $maxCapacity);
    $insert_success = $result['success'];
    $insert_message = $result['message'];

    if ($insert_success) {
        $_POST = [];
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Add Club
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

    <!-- HEADER -->
    <?php include '../INCLUDE/AdminHeader.php'; ?>

    <div class="add-user-container">

        <h1 class="add-user-title">
            Add Club
        </h1>

        <p class="add-user-subtitle">
            Register a new student club with advisor and status details.
        </p>

        <div class="add-user-box">

            <?php if ($insert_success === true): ?>
                <div class="alert alert-success"><?= htmlspecialchars($insert_message) ?></div>
            <?php elseif ($insert_success === false): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($insert_message) ?></div>
            <?php endif; ?>

            <form method="POST"
                action="">

                <div class="row">

                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            Club name
                        </label>
                        <input type="text"
                            name="club_name"
                            class="form-input-custom"
                            placeholder="Enter club name"
                            value="<?= htmlspecialchars($_POST['club_name'] ?? '') ?>"
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
                            value="<?= htmlspecialchars($_POST['advisor_name'] ?? '') ?>"
                            required>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label-custom">
                            Description
                        </label>
                        <textarea name="description"
                            class="form-input-custom"
                            rows="4"
                            placeholder="Enter club description"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
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
                            value="<?= htmlspecialchars($_POST['max_capacity'] ?? '') ?>"
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
                                <?= (($_POST['club_status'] ?? '1') === '1') ? 'selected' : '' ?>>
                                Active
                            </option>
                            <option value="0"
                                <?= (($_POST['club_status'] ?? '') === '0') ? 'selected' : '' ?>>
                                Inactive
                            </option>
                        </select>
                    </div>

                </div>

                <div class="submit-flex">
                    <button type="submit"
                        name="register_club"
                        class="save-btn">
                        <i class="bi bi-plus-circle-fill"></i>
                        Add Club
                    </button>
                    <a href="clubManagement.php"
                        class="cancel-btn text-decoration-none text-center">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </a>
                </div>

            </form>

        </div>

    </div>

</body>

</html>
