<?php
require_once __DIR__ . '/../INCLUDE/db.php';

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'student') {
    header('Location: ../login.php');
    exit;
}

$navBase = '../';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/dashboard.css">
</head>

<body>

    <?php include __DIR__ . '/../INCLUDE/StudentHeader.php'; ?>

    <div class="dashboard-container">
        <div class="row align-items-center">
            <div class="col-lg-6 dashboard-left">
                <h1>
                    Welcome back,<br>
                    <?= htmlspecialchars($_SESSION['user']['FullName'] ?? $_SESSION['user']['Student_id'] ?? 'Student') ?>!
                </h1>
                <h2>STUDENT</h2>
                <a href="<?= htmlspecialchars($navBase) ?>Module3/eventList.php" class="dashboard-btn text-decoration-none d-inline-block">
                    View Upcoming Events
                </a>
            </div>
            <div class="col-lg-6 text-center">
                <img src="../IMG/logo.png" alt="FK Club Logo" class="dashboard-logo">
            </div>
        </div>
    </div>

    <?php include __DIR__ . '/../INCLUDE/footer.php'; ?>

</body>

</html>
