<?php

require_once '../INCLUDE/db.php';

$success = null;
$error = null;

// GET USER DATA
$user_id =
    $_SESSION['user']['User_id'];

$user =
    profile($conn, $user_id);

function normalizeProfilePhotoPath($path)
{
    if (empty($path)) {
        return '';
    }

    if (str_starts_with($path, '/') || str_starts_with($path, '../')) {
        return $path;
    }

    $direct = __DIR__ . '/' . $path;
    $upOne = __DIR__ . '/../' . $path;
    $upTwo = __DIR__ . '/../../' . $path;

    if (file_exists($direct)) {
        return $path;
    }

    if (file_exists($upOne)) {
        return '../' . $path;
    }

    if (file_exists($upTwo)) {
        return '../../' . $path;
    }

    return $path;
}

// WHEN FORM SUBMITTED
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {

    $result = editProfile(

        $conn,

        $_SESSION['user']['User_id'],

        $_POST,

        $_FILES['Profile_photo']

    );

    if ($result['success']) {

        $success =
            $result['message'];

        // REFRESH PROFILE DATA
        $user =
            profile(
                $conn,
                $user_id
            );
    } else {

        $error =
            $result['message'];
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
        Edit Profile
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
                Redirecting to user Profile...
            </div>
        </div>
        <script>
            setTimeout(function() {
                window.location.href = "../Module1/profile.php";
            }, 1800);
        </script>
    <?php else: ?>

        <div class="add-user-container">

            <!-- TITLE -->
            <h1 class="add-user-title">
                Edit User Profile
            </h1>

            <p class="add-user-subtitle">
                Update user profile information.
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

                    <form method="POST"
                        enctype="multipart/form-data">

                        <!-- HIDDEN USER ID -->
                        <input type="hidden"
                            name="User_id"
                            value="<?= $user['User_id']; ?>">

                        <div class="row">

                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Profile Photo
                                </label>
                                <input type="file"
                                    name="Profile_photo"
                                    accept="image/*">
                                <?php if (!empty($user['Profile_photo'])):
                                    $displayPhoto = normalizeProfilePhotoPath($user['Profile_photo']); ?>
                                    <div class="mt-2">
                                        <img src="<?= htmlspecialchars($displayPhoto); ?>"
                                            alt="Current Profile Photo"
                                            style="max-width: 70px; max-height: 70px; border-radius: 6px; border: 1px solid #ccc;">
                                        <small class="form-text text-muted d-block">Current photo</small>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text text-muted">
                                    Upload an image file (jpg, png, etc) for your profile photo.
                                </small>
                            </div>

                            <!-- STUDENT ID -->
                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Student / Staff ID
                                </label>
                                <input type="text"
                                    name="Student_id"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['Student_id']); ?>"
                                    disabled>
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
                                    name="Email"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['Email']); ?>"
                                    required>
                            </div>

                            <!-- EMAIL -->
                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    IC Number
                                </label>
                                <input type="icNum"
                                    name="icNum"
                                    class="form-input-custom"
                                    value="<?= htmlspecialchars($user['icNum']); ?>"
                                    disabled>
                            </div>


                            <div class="col-lg-6 mb-4">
                                <label class="form-label-custom">
                                    Role
                                </label>
                                <input type="text"
                                    class="form-input-custom"
                                    value="<?php
                                            if ($user['role'] == 'admin') {
                                                echo 'Administrator';
                                            } elseif ($user['role'] == 'student') {
                                                echo 'Student';
                                            } elseif ($user['role'] == 'committee') {
                                                echo 'Club Committee Member';
                                            } else {
                                                echo htmlspecialchars($user['role']);
                                            }
                                            ?>"
                                    disabled>
                            </div>

                            <?php if ($user['role'] === 'committee'): ?>
                                <?php
                                // Fetch club_id and Committee_role_id from clubcommittee by user_id
                                $club_id = null;
                                $committee_role_id = null;
                                $club_name = '';
                                $committee_role_name = '';

                                if (isset($user['User_id'])) {
                                    // Query clubcommittee
                                    $stmt = $conn->prepare("SELECT club_id, Committee_role_id FROM clubcommitee WHERE User_id = ?");
                                    if ($stmt) {
                                        $stmt->bind_param("i", $user['User_id']);
                                        $stmt->execute();
                                        $stmt->bind_result($club_id, $committee_role_id);
                                        if ($stmt->fetch()) {
                                            $stmt->close();

                                            // Query club name
                                            $stmt2 = $conn->prepare("SELECT Club_name FROM club WHERE club_id = ?");
                                            if ($stmt2) {
                                                $stmt2->bind_param("i", $club_id);
                                                $stmt2->execute();
                                                $stmt2->bind_result($club_name);
                                                $stmt2->fetch();
                                                $stmt2->close();
                                            } else {
                                                $club_name = '';
                                            }

                                            // Query committee role name
                                            $stmt3 = $conn->prepare("SELECT Role_name FROM commiteerole WHERE Committee_role_id = ?");
                                            if ($stmt3) {
                                                $stmt3->bind_param("i", $committee_role_id);
                                                $stmt3->execute();
                                                $stmt3->bind_result($committee_role_name);
                                                $stmt3->fetch();
                                                $stmt3->close();
                                            } else {
                                                $committee_role_name = '';
                                            }
                                        } else {
                                            $club_name = '';
                                            $committee_role_name = '';
                                            $stmt->close();
                                        }
                                    } else {
                                        $club_name = '';
                                        $committee_role_name = '';
                                    }
                                }
                                ?>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label-custom">
                                        Club Name
                                    </label>
                                    <input type="text"
                                        class="form-input-custom"
                                        value="<?= htmlspecialchars($club_name); ?>"
                                        disabled>
                                </div>
                                <div class="col-lg-6 mb-4">
                                    <label class="form-label-custom">
                                        Assigned Role in Club
                                    </label>
                                    <input type="text"
                                        class="form-input-custom"
                                        value="<?= htmlspecialchars($committee_role_name); ?>"
                                        disabled>
                                </div>
                            <?php endif; ?>

                        </div>
                        <div class="submit-flex">
                            <button type="submit"
                                class="save-btn">
                                <i class="bi bi-person-fill-up"></i>
                                Update User
                            </button>
                            <a href="../Module1/profile.php"
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