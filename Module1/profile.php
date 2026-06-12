<!-- profile.php -->

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        My Profile
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
        href="../CSS/profile.css">

</head>

<body>
    <?php
    require_once '../INCLUDE/db.php';

    // Determine header by role
    if (isset($_SESSION['user']) && isset($_SESSION['user']['role']) && isset($_SESSION['user']['User_id'])) {
        $role = $_SESSION['user']['role'];
        $user_id = $_SESSION['user']['User_id'];
        if ($role === 'admin' || $role === 'administrator') {
            include '../INCLUDE/AdminHeader.php';
        } else if ($role === 'committee') {
            include '../INCLUDE/CommitteeHeader.php';
        } else {
            include '../INCLUDE/StudentHeader.php';
        }
    } else {
        // Not logged in, redirect?
        echo "<script>window.location.href='../Module1/login.php';</script>";
        exit;
    }

    // Fetch profile data
    $profile = profile($conn, $user_id);

    function getDisplayRole($role)
    {
        if ($role === 'admin') return 'Administrator';
        else if ($role === 'committee') return 'Club Committee Members';
        else if ($role === 'student') return 'Student';
        else return ucfirst($role);
    }

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
    ?>

    <!-- ================= PROFILE ================= -->
    <div class="profile-container">

        <!-- TITLE -->
        <h1 class="profile-title">
            My Profile
        </h1>

        <p class="profile-subtitle">
            Manage your account information.
        </p>

        <!-- ================= PROFILE CARD ================= -->
        <div class="profile-card">

            <!-- ================= LEFT ================= -->
            <div class="profile-left">

                <!-- PROFILE IMAGE -->
                <div class="profile-image">
                    <?php if (!empty($profile['Profile_photo'])):
                        $photoPath = normalizeProfilePhotoPath($profile['Profile_photo']); ?>
                        <img src="<?= htmlspecialchars($photoPath) ?>" alt="Profile Photo" style="width:206px;height:250px;border-radius:50%;object-fit:cover;">
                    <?php else: ?>
                        <i class="bi bi-person-fill"></i>
                    <?php endif; ?>
                </div>

                <!-- NAME -->
                <h3>
                    <?= htmlspecialchars($profile['FullName']) ?>
                </h3>

                <!-- ROLE -->
                <p>
                    <?= htmlspecialchars(getDisplayRole($profile['role'])) ?>
                </p>

                <!-- STATUS -->
                <span class="profile-status">
                    <i class="bi bi-circle-fill"></i>
                    Active
                </span>

            </div>

            <!-- ================= RIGHT ================= -->
            <div class="profile-right">

                <div class="row">

                    <!-- STAFF ID -->
                    <div class="col-lg-6 mb-4">
                        <label>
                            <?= ($profile['role'] === 'admin' || $profile['role'] === 'administrator') ? 'Staff ID' : 'Student ID'; ?>
                        </label>

                        <div class="profile-input">
                            <?= htmlspecialchars(
                                $profile['Student_id']
                            ) ?>
                        </div>
                    </div>

                    <!-- USERNAME (Full Name) -->
                    <div class="col-lg-6 mb-4">
                        <label>
                            Full Name
                        </label>
                        <div class="profile-input">
                            <?= htmlspecialchars($profile['FullName']) ?>
                        </div>
                    </div>

                    <!-- EMAIL -->
                    <div class="col-lg-6 mb-4">
                        <label>
                            Email Address
                        </label>
                        <div class="profile-input">
                            <?= htmlspecialchars($profile['Email']) ?>
                        </div>
                    </div>

                    <!-- PHONE -->
                    <div class="col-lg-6 mb-4">
                        <label>
                            Phone Number
                        </label>
                        <div class="profile-input">
                            <?= htmlspecialchars($profile['Phone_num']) ?>
                        </div>
                    </div>

                    <!-- ROLE -->
                    <div class="col-lg-6 mb-4">
                        <label>
                            Role
                        </label>
                        <div class="profile-input">
                            <?= htmlspecialchars(getDisplayRole($profile['role'])) ?>
                        </div>
                    </div>

                    <!-- IC NUMBER -->
                    <div class="col-lg-6 mb-4">
                        <label>
                            IC Number
                        </label>
                        <div class="profile-input">
                            <?= htmlspecialchars($profile['icNum']) ?>
                        </div>
                    </div>

                </div>

                <!-- ================= BUTTON ================= -->
                <div class="profile-btn-flex">

                    <!-- EDIT -->
                    <a href="../Module1/editProfile.php?User_id=<?= urlencode($profile['User_id']); ?>" class="edit-profile-btn">
                        <i class="bi bi-pencil-square"></i>
                        Edit Profile
                    </a>



                    <!-- CHANGE PASSWORD -->
                    <a href="../Module1/change.php" class="change-password-btn">
                        <i class="bi bi-key-fill"></i>
                        Change Password
                    </a>


                </div>

            </div>

        </div>

    </div>
</body>

</html>