<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>
        Dashboard Analytics
    </title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- CSS -->
    <link rel="stylesheet"
        href="../CSS/style.css">

    <link rel="stylesheet"
        href="../CSS/adminHeader.css">

    <link rel="stylesheet"
        href="../CSS/dashboard.css">

</head>

<?php

require_once '../INCLUDE/db.php';

$userCounts = countUser($conn);
$clubStats = getClubDashboardStats();
$studentByClub = getStudentDistributionByClub();

$clubDistLabels = [];
$clubDistCounts = [];
foreach ($studentByClub as $row) {
    $clubDistLabels[] = $row['Club_name'];
    $clubDistCounts[] = $row['student_count'];
}

// YEAR
$yearLabels = [];
$yearCounts = [];

if (
    isset($userCounts['by_month'])
    &&
    count($userCounts['by_month'])
) {

    $yearlyData = [];

    foreach ($userCounts['by_month'] as $row) {

        $year = substr($row['month'], 0, 4);

        if (!isset($yearlyData[$year])) {

            $yearlyData[$year] = 0;
        }

        $yearlyData[$year] += intval(
            $row['user_count']
        );
    }

    foreach ($yearlyData as $year => $count) {

        $yearLabels[] = $year;

        $yearCounts[] = $count;
    }
}

// MONTH
$monthLabels = [];
$monthCounts = [];

if (
    isset($userCounts['by_month'])
    &&
    count($userCounts['by_month'])
) {

    foreach ($userCounts['by_month'] as $row) {

        $monthLabels[] = $row['month'];

        $monthCounts[] = intval(
            $row['user_count']
        );
    }
}

?>

<body>

    <!-- HEADER -->
    <?php include '../INCLUDE/AdminHeader.php'; ?>

    <!-- ================= DASHBOARD ================= -->
    <div class="analytics-container">

        <!-- TITLE -->
        <div class="analytics-title">

            <h1>
                Dashboard Analytics
            </h1>

            <p>
                Overview of users, clubs,
                events, and attendance records.
            </p>

        </div>

        <!-- ================= SUMMARY ================= -->
        <div class="analytics-section-title" id="user-summary">

            <span>
                User Summary
            </span>

        </div>

        <div class="analytics-wrapper">

            <div class="row g-4">

                <!-- STUDENT CHART -->
                <div class="col-lg-6">

                    <div class="chart-box">

                        <div class="chart-top">

                            <div>

                                <h3>
                                    Registered Students
                                </h3>

                                <p>
                                    Total students registered:
                                    <?php
                                    // Query user table for total row count
                                    $userCountResult = $conn->query("SELECT COUNT(*) AS total FROM user");
                                    $userCountRow = $userCountResult ? $userCountResult->fetch_assoc() : ['total' => 0];
                                    echo $userCountRow['total'];
                                    ?>
                                </p>
                                <br><br><br>
                            </div>

                            <div class="chart-icon">

                                <i class="bi bi-bar-chart-fill"></i>

                            </div>

                        </div>

                        <canvas id="studentChart"></canvas>

                    </div>

                </div>

                <!-- ================= WEEKLY REGISTRATION ================= -->
                <div class="col-lg-6">

                    <div class="chart-box">

                        <div class="chart-top">

                            <div>

                                <h3>
                                    Weekly Registration
                                </h3>

                                <p>

                                    <?php

                                    $currentYear =
                                        date('Y');

                                    $currentMonth =
                                        date('n');

                                    $weeklySql = " SELECT FLOOR((DAY(Created_at) - 1) / 7) + 1 AS week_number, COUNT(*) AS total FROM user WHERE YEAR(Created_at) = ? AND MONTH(Created_at) = ? GROUP BY week_number ORDER BY week_number ASC ";

                                    $stmt =
                                        $conn->prepare(
                                            $weeklySql
                                        );

                                    $stmt->bind_param(
                                        "ii",
                                        $currentYear,
                                        $currentMonth
                                    );

                                    $stmt->execute();

                                    $weeklyResult =
                                        $stmt->get_result();

                                    $weeks = [];

                                    $weekLabels = [];

                                    $weekCounts = [];

                                    while (
                                        $row =
                                        $weeklyResult->fetch_assoc()
                                    ) {

                                        $weeks[] =

                                            "Week "
                                            . $row['week_number']
                                            . ": "
                                            . $row['total'];

                                        // CHART LABEL
                                        $weekLabels[] =

                                            "Week "
                                            . $row['week_number'];

                                        // CHART DATA
                                        $weekCounts[] =

                                            intval(
                                                $row['total']
                                            );
                                    }

                                    // DISPLAY TEXT
                                    echo
                                    "Registered users each week for "
                                        . date('F')
                                        . " "
                                        . $currentYear
                                        . ":<br>";

                                    if ($weeks) {

                                        echo implode(
                                            '<br>',
                                            $weeks
                                        );
                                    } else {

                                        echo
                                        "No registrations yet this month.";
                                    }

                                    $stmt->close();

                                    ?>

                                </p>

                            </div>

                            <div class="chart-icon">

                                <i class="bi bi-person-plus-fill"></i>

                            </div>

                        </div>

                        <!-- CHART -->
                        <canvas id="recentUserChart"></canvas>

                        <!-- LABEL -->
                        <div class="chart-label text-center mt-2"
                            style="
                font-size: 0.95rem;
                color: #888;
            ">

                            <?=
                            date('F')
                                . ' '
                                . $currentYear;
                            ?>

                        </div>

                    </div>

                </div>

            </div>

        </div>

        <!-- ================= CLUB ================= -->
        <div class="analytics-section-title mt-5" id="club-activities">

            <span>
                Club Activities
            </span>

        </div>

        <div class="analytics-wrapper">

            <div class="chart-box club-stats-container mb-4">
                <div class="chart-top mb-3">
                    <div>
                        <h3>Club Overview</h3>
                        <p>Students, club status, and membership totals</p>
                    </div>
                    <div class="chart-icon">
                        <i class="bi bi-grid-1x2-fill"></i>
                    </div>
                </div>

                <div class="row g-4 club-stats-row">
                    <div class="col-lg-3 col-md-6">
                    <div class="stat-box stat-box-purple">
                        <div class="stat-box-top">
                            <p class="stat-box-label">Total Students</p>
                            <div class="stat-box-icon">
                                <i class="bi bi-mortarboard-fill"></i>
                            </div>
                        </div>
                        <p class="stat-box-value"><?= (int) $clubStats['total_students'] ?></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-box stat-box-active">
                        <div class="stat-box-top">
                            <p class="stat-box-label">Active Clubs</p>
                            <div class="stat-box-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                        </div>
                        <p class="stat-box-value"><?= (int) $clubStats['active_clubs'] ?></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-box stat-box-inactive">
                        <div class="stat-box-top">
                            <p class="stat-box-label">Inactive Clubs</p>
                            <div class="stat-box-icon">
                                <i class="bi bi-x-circle-fill"></i>
                            </div>
                        </div>
                        <p class="stat-box-value"><?= (int) $clubStats['inactive_clubs'] ?></p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="stat-box stat-box-blue">
                        <div class="stat-box-top">
                            <p class="stat-box-label">Total Members</p>
                            <div class="stat-box-icon">
                                <i class="bi bi-people-fill"></i>
                            </div>
                        </div>
                        <p class="stat-box-value"><?= (int) $clubStats['total_members'] ?></p>
                    </div>
                </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12">
                    <div class="chart-box">
                        <div class="chart-top">
                            <div>
                                <h3>Student Distribution Across Clubs</h3>
                                <p>Number of students enrolled in each club</p>
                            </div>
                            <div class="chart-icon">
                                <i class="bi bi-bar-chart-fill"></i>
                            </div>
                        </div>
                        <canvas id="clubStudentChart"></canvas>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <script>
        new Chart(
            document.getElementById('studentChart'), {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($yearLabels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($yearCounts); ?>,
                        backgroundColor: '#8b5cf6',
                        borderRadius: 10
                    }]
                },
                options: {
                    plugins: {
                        legend: {
                            display: false
                        }
                    }
                }
            }
        );

        new Chart(

            document.getElementById(
                'recentUserChart'
            ),

            {

                type: 'line',

                data: {

                    labels: <?= json_encode($weekLabels); ?>,

                    datasets: [{

                        label: 'Weekly Registration',

                        data: <?= json_encode($weekCounts); ?>,

                        borderColor: '#6d28ff',

                        backgroundColor: 'rgba(109,40,255,0.15)',

                        fill: true,

                        tension: 0.4,

                        borderWidth: 3

                    }]
                },

                options: {

                    responsive: true,

                    plugins: {

                        legend: {

                            display: false

                        }
                    },

                    scales: {

                        y: {

                            beginAtZero: true,

                            ticks: {

                                stepSize: 1

                            }
                        }
                    }
                }
            }

        );

        const clubDistLabels = <?= json_encode($clubDistLabels) ?>;
        const clubDistCounts = <?= json_encode($clubDistCounts) ?>;
        const clubChartColors = [
            '#8b5cf6', '#6d28ff', '#3b82f6', '#06b6d4', '#10b981',
            '#f59e0b', '#ef4444', '#ec4899', '#84cc16', '#6366f1'
        ];

        new Chart(document.getElementById('clubStudentChart'), {
            type: 'bar',
            data: {
                labels: clubDistLabels.length ? clubDistLabels : ['No clubs'],
                datasets: [{
                    label: 'Students',
                    data: clubDistCounts.length ? clubDistCounts : [0],
                    backgroundColor: clubDistLabels.map((_, i) =>
                        clubChartColors[i % clubChartColors.length]
                    ),
                    borderRadius: 10,
                    maxBarThickness: 56
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 }
                    },
                    x: {
                        ticks: {
                            maxRotation: 45,
                            minRotation: 0
                        }
                    }
                }
            }
        });

        (function scrollToDashboardSection() {
            const hash = window.location.hash;
            if (!hash) {
                return;
            }
            const target = document.querySelector(hash);
            if (target) {
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        })();
    </script>

</body>

</html>