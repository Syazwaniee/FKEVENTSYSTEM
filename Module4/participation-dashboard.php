<?php

include '../INCLUDE/db.php';

date_default_timezone_set("Asia/Kuala_Lumpur");

/* ATTENDANCE STATUS CHART */

$chart_query = mysqli_query(

$conn,

"SELECT attendance_status,
COUNT(*) AS total

FROM attendance

GROUP BY attendance_status"

);

$labels = [];
$data = [];

while($row = mysqli_fetch_assoc($chart_query)){

    $labels[] = $row['attendance_status'];

    $data[] = $row['total'];

}

/* CLUB PARTICIPATION CHART */

$club_chart_query = mysqli_query(

$conn,

"SELECT club_name,
COUNT(*) AS total

FROM attendance

GROUP BY club_name"

);

$club_labels = [];
$club_data = [];

while($row = mysqli_fetch_assoc($club_chart_query)){

    $club_labels[] = $row['club_name'];

    $club_data[] = $row['total'];

}

/* EVENT PARTICIPATION CHART */

$event_chart_query = mysqli_query(

$conn,

"SELECT event_name,
COUNT(*) AS total

FROM attendance

GROUP BY event_name"

);

$event_labels = [];
$event_data = [];

while($row = mysqli_fetch_assoc($event_chart_query)){

    $event_labels[] = $row['event_name'];

    $event_data[] = $row['total'];

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Participation Dashboard</title>

<link rel="stylesheet" href="../CSS/style.css">
<link rel="stylesheet" href="../CSS/module4-dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../CSS/adminHeader.css">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

</head>

<body>

<?php include '../INCLUDE/AdminHeader.php'; ?>

<div class="container">




<!-- CONTENT -->

<div class="content">

<h1>Participation Dashboard</h1>

<div class="chart-container">

<!-- PIE CHART -->

<div class="chart-box">

<h2>Attendance Distribution</h2>

<canvas id="pieChart">
</canvas>

</div>

<!-- BAR CHART -->

<div class="chart-box">

<h2>Attendance Bar Chart</h2>

<canvas id="barChart">
</canvas>

</div>

<!-- CLUB CHART -->

<div class="chart-box">

<h2>Club Participation</h2>

<canvas id="clubChart">
</canvas>

</div>

<!-- EVENT CHART -->

<div class="chart-box">

<h2>Event Participation</h2>

<canvas id="eventChart">
</canvas>

</div>

</div>

</div>

</div>

<!-- PIE CHART -->

<script>

const piectx =
document.getElementById(
'pieChart'
);

new Chart(piectx, {

type: 'pie',

data: {

labels:

<?php
echo json_encode($labels);
?>,

datasets: [{

data:

<?php
echo json_encode($data);
?>

}]

}

});

</script>

<!-- BAR CHART -->

<script>

const barctx =
document.getElementById(
'barChart'
);

new Chart(barctx, {

type: 'bar',

data: {

labels:

<?php
echo json_encode($labels);
?>,

datasets: [{

label: 'Total Attendance',

data:

<?php
echo json_encode($data);
?>

}]

},

options: {

responsive: true,

plugins: {

legend: {

display: true

}

}

}

});

</script>

<!-- CLUB CHART -->

<script>

const clubctx =
document.getElementById(
'clubChart'
);

new Chart(clubctx, {

type: 'bar',

data: {

labels:

<?php
echo json_encode($club_labels);
?>,

datasets: [{

label: 'Club Participation',

data:

<?php
echo json_encode($club_data);
?>

}]

},

options: {

responsive: true

}

});

</script>

<!-- EVENT CHART -->

<script>

const eventctx =
document.getElementById(
'eventChart'
);

new Chart(eventctx, {

type: 'bar',

data: {

labels:

<?php
echo json_encode($event_labels);
?>,

datasets: [{

label: 'Event Participation',

data:

<?php
echo json_encode($event_data);
?>

}]

},

options: {

responsive: true

}

});

</script>

</body>
</html>