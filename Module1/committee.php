<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'committee') {
    header('Location: ../login.php');
    exit;
}

$navBase = '../';
$activeNav = 'club';
?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Administrator Dashboard
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
        href="../CSS/dashboard.css">

</head>

<body>

    <!-- HEADER -->
    <?php include '../INCLUDE/CommitteeHeader.php'; ?>

    <!-- ================= DASHBOARD ================= -->
    <div class="dashboard-container">

        <div class="row align-items-center">

            <!-- ================= LEFT ================= -->
            <div class="col-lg-6 dashboard-left">

                <h1>
                    Welcome back,<br>
                    <?php
                    // Output the logged-in committee user's Student_id (from login)
                    if (isset($_SESSION['user']) && !empty($_SESSION['user']['Student_id'])) {
                        echo htmlspecialchars($_SESSION['user']['Student_id']);
                    }
                    ?>!
                </h1>


                <h2>
                    CLUB COMMITTEE MEMBERS
                </h2>

                <button class="dashboard-btn">

                    View Upcoming Events

                </button>

            </div>

            <!-- ================= RIGHT ================= -->
            <div class="col-lg-6 text-center">

                <img src="../IMG/logo.png"
                    alt="FK Club Logo"
                    class="dashboard-logo">

            </div>

        </div>

    </div>

    <!-- FOOTER -->
    <?php include '../INCLUDE/footer.php'; ?>

</body>

</html>