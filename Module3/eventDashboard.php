<?php

include '../INCLUDE/db.php';

$clubData = eventsPerClub();
$participantData = participantsPerEvent();
$monthlyTrend = monthlyTrend();

$clubLabels = [];
$clubCounts = [];

while ($row = $clubData->fetch_assoc()) {
    $clubLabels[] = $row['Club_name'];
    $clubCounts[] = $row['total'];
}
$eventLabels = [];
$eventCounts = [];

while ($row = $participantData->fetch_assoc()) {
    $eventLabels[] = $row['Event_Name'];
    $eventCounts[] = $row['participants'];
}
$monthLabels = [];
$monthCounts = [];

while ($row = $monthlyTrend->fetch_assoc()) {
    $monthLabels[] = $row['month'];
    $monthCounts[] = $row['total'];
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Event Analytics</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../CSS/style.css">
    <link rel="stylesheet" href="../CSS/adminHeader.css">
    <link rel="stylesheet" href="../CSS/eventDashboard.css">
</head>

<body>

    <?php include '../INCLUDE/AdminHeader.php'; ?>
    <div class="container mt-4">
        <div class="row mb-4">
            <div class="col-md-8">
                <h1>Event Analytics</h1>
            </div>
            <div class="col-md-4">
                <form method="GET" class="d-flex gap-2">
                    <select name="semester" class="form-select">
                        <option>2024/2025</option>
                        <option selected>2025/2026</option>
                        <option>2026/2027</option>
                    </select>
                    <input type="text" name="search" class="form-control" placeholder="Search...">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-4">
                <div class="chart-card">
                    <h3>Event per Club</h3><canvas id="clubChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-card">
                    <h3>Participants per Club</h3><canvas id="participantClubChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-card">
                    <h3>Popular Event</h3><canvas id="popularEventChart"></canvas>
                </div>
            </div>
            <div class="col-md-6 mb-4">
                <div class="chart-card">
                    <h3>Monthly Event Trends</h3><canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
        <a href="../Module1/dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
    </div>

    <script>
        new Chart(document.getElementById('clubChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($clubLabels) ?>,
                datasets: [{
                    label: 'Events',
                    data: <?= json_encode($clubCounts) ?>,
                    backgroundColor: '#8b5cf6'
                }]
            }
        });
        new Chart(document.getElementById('participantClubChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($clubLabels) ?>,
                datasets: [{
                    label: 'Participants',
                    data: <?= json_encode($clubCounts) ?>,
                    backgroundColor: '#f59e0b'
                }]
            }
        });
        new Chart(document.getElementById('popularEventChart'), {
            type: 'bar',
            data: {
                labels: <?= json_encode($eventLabels) ?>,
                datasets: [{
                    label: 'Registrations',
                    data: <?= json_encode($eventCounts) ?>,
                    backgroundColor: '#10b981'
                }]
            }
        });
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: <?= json_encode($monthLabels) ?>,
                datasets: [{
                    label: 'Events Created',
                    data: <?= json_encode($monthCounts) ?>,
                    borderColor: '#3b82f6',
                    fill: false
                }]
            }
        });
    </script>
</body>

</html>