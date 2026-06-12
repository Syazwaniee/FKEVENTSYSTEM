<?php

include '../INCLUDE/db.php';

$data = edit($_GET['User_id'] ?? null);

$user = $data['user'];
$success = $data['success'];
$error = $data['error'];

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Edit User
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

    <?php if ($success): ?>
        <div class="add-user-container mt-5">
            <div class="alert alert-success mb-4 text-center">
                <?= $success; ?>
                <br>
                Redirecting to user management...
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "../Module1/userManagement.php";
            }, 1800);
        </script>
    <?php else: ?>

        <div class="add-user-container">

            <!-- TITLE -->
            <h1 class="add-user-title">
                Edit User
            </h1>

            <p class="add-user-subtitle">
                Update registered user information.
            </p>

            <!-- ERROR MESSAGE -->
            <?php if ($error): ?>

                <div class="alert alert-danger mb-4">
                    <?= $error; ?>
                </div>

            <?php endif; ?>

            <?php if ($user): ?>

                <!-- FORM BOX -->
                <div class="add-user-box">

                    <form method="POST">

                        <!-- HIDDEN USER ID -->
                        <input type="hidden"
                            name="User_id"
                            value="<?= $user['User_id']; ?>">

                        <div class="row">

                            <!-- STUDENT ID -->
                            <div class="col-lg-6 mb-4">

                                <label class="form-label-custom">
                                    Student / Staff ID
                                </label>

                                <input type="text"
                                    name="Student_id"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['Student_id']); ?>"
                                    required>

                            </div>

                            <!-- FULL NAME -->
                            <div class="col-lg-6 mb-4">

                                <label class="form-label-custom">
                                    Full Name
                                </label>

                                <input type="text"
                                    name="FullName"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['FullName']); ?>"
                                    required>

                            </div>

                            <!-- PHONE -->
                            <div class="col-lg-6 mb-4">

                                <label class="form-label-custom">
                                    Phone Number
                                </label>

                                <input type="text"
                                    name="Phone_num"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['Phone_num']); ?>"
                                    required>

                            </div>

                            <!-- EMAIL -->
                            <div class="col-lg-6 mb-4">

                                <label class="form-label-custom">
                                    Email Address
                                </label>

                                <input type="email"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['Email']); ?>"
                                    disabled>

                            </div>

                            <!-- ROLE -->
                            <div class="col-lg-6 mb-4">

                                <label class="form-label-custom">
                                    Role
                                </label>

                                <select class="form-input-custom"
                                    id="roleSelect"
                                    name="role">

                                    <option value="admin"
                                        <?= ($user['role'] == 'admin') ? 'selected' : ''; ?>>
                                        Administrator
                                    </option>

                                    <option value="student"
                                        <?= ($user['role'] == 'student') ? 'selected' : ''; ?>>
                                        Student
                                    </option>

                                    <option value="committee"
                                        <?= ($user['role'] == 'committee') ? 'selected' : ''; ?>>
                                        Club Committee Member
                                    </option>

                                </select>

                            </div>

                            <!-- ACTIVE STATUS -->
                            <div class="col-lg-6 mb-4">

                                <label class="form-label-custom d-block mb-3">
                                    Account Status
                                </label>

                                <div class="form-check form-switch mt-2">

                                    <input class="form-check-input"
                                        type="checkbox"
                                        role="switch"
                                        id="Is_active"
                                        name="Is_active"
                                        <?= ($user['Is_active'] == 1) ? 'checked' : ''; ?>>

                                    <label class="form-check-label"
                                        for="Is_active">

                                        Active User

                                    </label>

                                </div>

                            </div>

                        </div>
                        <div class="submit-flex">
                            <button type="submit"
                                class="save-btn">
                                <i class="bi bi-person-fill-up"></i>
                                Update User
                            </button>
                            <a href="../Module1/userManagement.php"
                                class="cancel-btn">
                                <i class="bi bi-x-circle"></i>
                                Cancel
                            </a>
                        </div>
                    </form>

                </div>

            <?php endif; ?>

        </div>
    <?php endif; ?>

</body>

</html>