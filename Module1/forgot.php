<?php

require_once '../INCLUDE/db.php';

$success = null;
$error = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $icNum =
        trim($_POST['icNum']);

    $email =
        trim($_POST['Email']);

    if (
        empty($icNum)
        ||
        empty($email)
    ) {

        $error =
            "All fields are required.";
    } else {

        // CHECK USER
        $sql = "

            SELECT
                User_id,
                Student_id,
                Email

            FROM user

            WHERE icNum = ?
            AND Email = ?

        ";

        $stmt =
            $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $icNum,
                $email
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            if (
                $user =
                $result->fetch_assoc()
            ) {

                // RESET PASSWORD
                $new_password =
                    $user['Student_id'];

                $password_hash =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );

                // UPDATE PASSWORD
                $update_sql = "

                    UPDATE user

                    SET Password_hash = ?

                    WHERE User_id = ?

                ";

                $update_stmt =
                    $conn->prepare(
                        $update_sql
                    );

                if ($update_stmt) {

                    $update_stmt->bind_param(
                        "si",
                        $password_hash,
                        $user['User_id']
                    );

                    if (
                        $update_stmt->execute()
                    ) {

                        $success =
                            "Password successfully reset. Your temporary password is your Student/Staff ID.";
                    } else {

                        $error =
                            "Failed to reset password.";
                    }

                    $update_stmt->close();
                } else {

                    $error =
                        "Database update failed.";
                }
            } else {

                $error =
                    "IC Number or Email not found.";
            }

            $stmt->close();
        } else {

            $error =
                "Database error.";
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
        Forgot Password
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
            Forgot Password
        </h1>

        <p class="add-user-subtitle">
            Reset your password using IC Number and Email.
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

                <!-- IC -->
                <div class="mb-4">

                    <label class="form-label-custom">
                        IC Number
                    </label>

                    <input type="text"
                        name="icNum"
                        class="form-input-custom"
                        required>

                </div>

                <!-- EMAIL -->
                <div class="mb-4">

                    <label class="form-label-custom">
                        Email Address
                    </label>

                    <input type="email"
                        name="Email"
                        class="form-input-custom"
                        required>

                </div>

                <!-- BUTTON -->
                <div class="submit-flex">

                    <button type="submit"
                        class="save-btn">

                        <i class="bi bi-key-fill"></i>

                        Reset Password

                    </button>

                    <a href="../Module1/login.php"
                        class="cancel-btn">

                        <i class="bi bi-arrow-left-circle"></i>

                        Back to Login

                    </a>

                </div>

            </form>

        </div>

    </div>

</body>

</html>