<?php

require_once '../INCLUDE/db.php';

$success = null;
$error = null;

// Ensure session is started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if the user is logged in
if (!isset($_SESSION['user']['User_id'])) {
    header("Location: ../Module1/login.php");
    exit();
}

$user_id = $_SESSION['user']['User_id'];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $current_password = trim($_POST['current_password'] ?? '');
    $new_password = trim($_POST['new_password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // CHECK EMPTY
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required.";
    }
    // CHECK MATCH
    else if ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    }
    // CHECK PASSWORD LENGTH
    else if (strlen($new_password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        // GET CURRENT PASSWORD HASH FROM DATABASE
        $sql = "SELECT Password_hash FROM user WHERE User_id = ?";

        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            $stmt->close();

            if ($user && password_verify($current_password, $user['Password_hash'])) {
                // HASH NEW PASSWORD
                $new_hash = password_hash($new_password, PASSWORD_DEFAULT);

                // UPDATE PASSWORD
                $update_sql = "UPDATE user SET Password_hash = ? WHERE User_id = ?";
                $update_stmt = $conn->prepare($update_sql);

                if ($update_stmt) {
                    $update_stmt->bind_param("si", $new_hash, $user_id);

                    if ($update_stmt->execute()) {
                        $success = "Password updated successfully.";
?>
                        <div class="add-user-container mt-5">
                            <div class="alert alert-success mb-4 text-center">
                                <?= $success; ?>
                                <br>
                                Redirecting to profile page...
                            </div>
                        </div>
                        <script>
                            setTimeout(function() {
                                window.location.href = "../Module1/profile.php";
                            }, 1800);
                        </script>
<?php
                    } else {
                        $error = "Failed to update password.";
                    }
                    $update_stmt->close();
                } else {
                    $error = "Database prepare failed.";
                }
            } else {
                $error = "Current password is incorrect.";
            }
        } else {
            $error = "Database error.";
        }
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
        Change Password
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
        href="../CSS/addUser.css">

</head>

<body>

    <div class="add-user-container">

        <!-- TITLE -->
        <h1 class="add-user-title">
            Change Password
        </h1>

        <p class="add-user-subtitle">
            Update your account password securely.
        </p>

        <!-- SUCCESS -->
        <?php if ($success): ?>

            <div class="alert alert-success mb-4">
                <?= $success; ?>
            </div>

        <?php endif; ?>

        <!-- ERROR -->
        <?php if ($error): ?>

            <div class="alert alert-danger mb-4">
                <?= $error; ?>
            </div>

        <?php endif; ?>

        <div class="add-user-box">

            <form method="POST">

                <!-- CURRENT PASSWORD -->
                <div class="mb-4">

                    <label class="form-label-custom">
                        Current Password
                    </label>

                    <input type="password"
                        name="current_password"
                        class="form-input-custom"
                        required>

                </div>

                <!-- NEW PASSWORD -->
                <div class="mb-4">

                    <label class="form-label-custom">
                        New Password
                    </label>

                    <input type="password"
                        name="new_password"
                        class="form-input-custom"
                        required>

                </div>

                <!-- CONFIRM PASSWORD -->
                <div class="mb-4">

                    <label class="form-label-custom">
                        Confirm New Password
                    </label>

                    <input type="password"
                        name="confirm_password"
                        class="form-input-custom"
                        required>

                </div>

                <!-- BUTTON -->
                <div class="submit-flex">

                    <button type="submit"
                        class="save-btn">

                        <i class="bi bi-key-fill"></i>

                        Change Password

                    </button>

                    <a href="../Module1/profile.php"
                        class="cancel-btn">

                        <i class="bi bi-arrow-left-circle"></i>

                        Back

                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>