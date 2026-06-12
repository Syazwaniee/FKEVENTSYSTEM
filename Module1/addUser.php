<!-- addUser.php -->

<?php
include '../INCLUDE/db.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Add User
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

        <!-- TITLE -->
        <h1 class="add-user-title">
            Add User
        </h1>

        <p class="add-user-subtitle">
            Register a new student, administrator,
            or committee member account.
        </p>

        <!-- ================= FORM BOX ================= -->
        <div class="add-user-box">

            <?php
            // Handle form submission and call insertUser directly if submitted
            include_once "../INCLUDE/db.php";

            $insert_success = null;
            $insert_message = "";

            if (($_SERVER["REQUEST_METHOD"] ?? '') == "POST" && isset($_POST['register_user'])) {
                $student_id = trim($_POST['student_id'] ?? '');
                $fullname = trim($_POST['fullname'] ?? '');
                $ic_number = trim($_POST['ic_number'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $role = $_POST['role'] ?? '';

                // Use lowercase key as per HTML input name!
                if ($role === 'committee') {
                    $club_id = !empty($_POST['club_id']) ? $_POST['club_id'] : null;
                } else {
                    $club_id = null;
                }
                $committee_role = null;

                // Basic validation
                if (empty($student_id) || empty($fullname) || empty($ic_number) || empty($phone) || empty($email) || empty($role)) {
                    $insert_success = false;
                    $insert_message = "All fields are required.";
                } else if ($role === 'committee' && empty($club_id)) {
                    $insert_success = false;
                    $insert_message = "Please select a club for the committee member.";
                } else {
                    $insert_success = insertUser($student_id, $fullname, $ic_number, $phone, $email, $role, $club_id, $committee_role);
                    if ($insert_success === true) {
                        $insert_message = "User registered successfully!";
                        // Clear values post-successful register
                        $_POST = [];
                    } else {
                        $insert_message = "Failed to register user. Please try again.";
                    }
                }
            }
            ?>

            <?php if ($insert_success === true): ?>
                <div class="alert alert-success"><?= $insert_message ?></div>
            <?php elseif ($insert_success === false): ?>
                <div class="alert alert-danger"><?= $insert_message ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="row">

                    <!-- STUDENT ID -->
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            Student / Staff ID
                        </label>
                        <input type="text"
                            name="student_id"
                            class="form-input-custom"
                            placeholder="Enter ID"
                            value="<?= htmlspecialchars($_POST['student_id'] ?? '') ?>">
                    </div>

                    <!-- FULL NAME -->
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            Full Name
                        </label>
                        <input type="text"
                            name="fullname"
                            class="form-input-custom"
                            placeholder="Enter full name"
                            value="<?= htmlspecialchars($_POST['fullname'] ?? '') ?>">
                    </div>

                    <!-- IC NUMBER -->
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            IC Number
                        </label>
                        <input type="text"
                            name="ic_number"
                            class="form-input-custom"
                            placeholder="Enter IC Number"
                            value="<?= htmlspecialchars($_POST['ic_number'] ?? '') ?>">
                    </div>

                    <!-- PHONE -->
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            Phone Number
                        </label>
                        <input type="text"
                            name="phone"
                            class="form-input-custom"
                            placeholder="Enter phone number"
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
                    </div>

                    <!-- EMAIL -->
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            Email Address
                        </label>
                        <input type="email"
                            name="email"
                            class="form-input-custom"
                            placeholder="Enter email"
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                    </div>

                    <!-- ROLE -->
                    <div class="col-lg-6 mb-4">
                        <label class="form-label-custom">
                            Role
                        </label>
                        <select class="form-input-custom"
                            id="roleSelect"
                            name="role">
                            <option value="">
                                Select Role
                            </option>
                            <option value="admin" <?= (($_POST['role'] ?? "") == "admin") ? "selected" : "" ?>>
                                Administrator
                            </option>
                            <option value="student" <?= (($_POST['role'] ?? "") == "student") ? "selected" : "" ?>>
                                Student
                            </option>
                            <option value="committee" <?= (($_POST['role'] ?? "") == "committee") ? "selected" : "" ?>>
                                Club Committee Member
                            </option>
                        </select>
                    </div>
                </div>

                <div class="committee-box"
                    id="committeeSection"
                    style="<?= (isset($_POST['role']) && $_POST['role'] == 'committee') ? 'display:block;' : 'display:none;' ?>">

                    <div class="row">

                        <!-- CLUB (committee position is assigned under Club → Assign Club Committee) -->
                        <div class="col-lg-12 mb-4">
                            <label class="form-label-custom">
                                Club Name
                            </label>
                            <select class="form-input-custom"
                                name="club_id">
                                <option value="">
                                    Select Club
                                </option>
                                <?php
                                $clubs = getClubs();
                                if ($clubs && $clubs->num_rows > 0) {
                                    while ($row = $clubs->fetch_assoc()) {
                                ?>
                                        <option value="<?= htmlspecialchars($row['Club_id']); ?>" <?= (isset($_POST['club_id']) && $_POST['club_id'] == $row['Club_id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($row['Club_name']); ?>
                                        </option>
                                <?php }
                                } ?>
                            </select>
                        </div>

                    </div>

                </div>

                <div class="submit-flex">
                    <button type="submit"
                        name="register_user"
                        class="save-btn">
                        <i class="bi bi-person-plus-fill"></i>
                        Register User
                    </button>
                    <a href="../Module1/userManagement.php"
                        class="cancel-btn text-decoration-none text-center">
                        <i class="bi bi-x-circle"></i>
                        Cancel
                    </a>

                </div>
            </form>

        </div>

    </div>

    <!-- ================= JAVASCRIPT ================= -->
    <script>
        window.onload = function() {
            document.getElementById("roleSelect")
                .addEventListener(
                    "change",
                    handleRoleChange
                );
        }

        function handleRoleChange() {
            let role =
                document.getElementById("roleSelect").value;

            let committeeSection =
                document.getElementById("committeeSection");

            if (role === "committee") {
                committeeSection.style.display = "block";
            } else {
                committeeSection.style.display = "none";
            }
        }
    </script>

</body>

</html>