<?php

include '../INCLUDE/db.php';

$user_id = $_SESSION['user']['User_id'];
$registrations = getUserRegistrations($user_id);
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Registrations</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/addUser.css">
    <link rel="stylesheet" href="../CSS/myRegistrations.css">
</head>

<body>

    <?php include '../INCLUDE/StudentHeader.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">My Registration</h1>
        <table class="table table-bordered">
            <thead class="table-light">
                <tr>
                    <th>Event</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th>Check‑in</th>
                    <th>Points</th>
                    <th>Certificate</th>
                </tr>
            </thead>
            <tbody>

                <?php while ($reg = $registrations->fetch_assoc()):
                    $status = $reg['Reg_Status'];
                    $checked = $reg['Check_In_Time'] ? 'On‑time' : 'Not yet';
                    $points = $reg['Point_Value'] ?? 0;
                ?>

                    <tr>
                        <td><?= htmlspecialchars($reg['Event_Name']) ?></td>
                        <td><?= date('d M Y', strtotime($reg['Event_Date'])) ?></td>
                        <td><span class="badge bg-<?= $status == 'registered' ? 'success' : ($status == 'completed' ? 'secondary' : 'danger') ?>"><?= ucfirst($status) ?></span></td>
                        <td><?= $checked ?></td>
                        <td><?= $points ?></td>
                        <td><?= ($status == 'completed' && $points > 0) ? '<a href="#" class="btn btn-sm btn-outline-primary">view certificate</a>' : '—' ?></td>
                    </tr>

                <?php endwhile; ?>

            </tbody>
        </table>
        <a href="eventList.php" class="btn btn-secondary">← Back to Events</a>
    </div>
</body>

</html>