<?php

include '../INCLUDE/db.php';

date_default_timezone_set("Asia/Kuala_Lumpur");

$total_students =
mysqli_num_rows(
mysqli_query($conn,
"SELECT DISTINCT student_id FROM attendance")
);

$total_participation =
mysqli_num_rows(
mysqli_query($conn,
"SELECT * FROM attendance")
);

$total_events =
mysqli_num_rows(
mysqli_query($conn,
"SELECT DISTINCT event_name FROM attendance")
);

$present_query =
mysqli_query($conn,
"SELECT * FROM attendance
WHERE attendance_status='Present'");

$total_present =
mysqli_num_rows($present_query);

$attendance_rate = 0;

if($total_participation > 0){

    $attendance_rate =
    round(($total_present /
    $total_participation) * 100);

}

/* MOST ACTIVE CLUB */

$club_query = mysqli_query(

$conn,

"SELECT club_name,
COUNT(*) AS total

FROM attendance

GROUP BY club_name

ORDER BY total DESC

LIMIT 1"

);

$club = mysqli_fetch_assoc($club_query);

/* MOST ACTIVE STUDENT */

$student_query = mysqli_query(

$conn,

"SELECT student_name,
SUM(points) AS total_points

FROM attendance

GROUP BY student_name

ORDER BY total_points DESC

LIMIT 1"

);

$top_student = mysqli_fetch_assoc($student_query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link rel="stylesheet"
href="../CSS/module4-dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../CSS/adminHeader.css">

</head>

<body>

<?php include '../INCLUDE/AdminHeader.php'; ?>

<div class="container">

<!-- CONTENT -->

<div class="content">

<!-- DASHBOARD CARDS -->

<div class="card-container">

<div class="card">

<h3>Total Students</h3>

<p>

<?php
echo $total_students;
?>

</p>

</div>

<div class="card">

<h3>Total Participation</h3>

<p>

<?php
echo $total_participation;
?>

</p>

</div>

<div class="card">

<h3>Total Events</h3>

<p>

<?php
echo $total_events;
?>

</p>

</div>

<div class="card">

<h3>Attendance Rate</h3>

<p>

<?php
echo $attendance_rate;
?>%

</p>

</div>

<div class="card">

<h3>Most Active Club</h3>

<p>

<?php

if($club){

    echo $club['club_name'];

}

else{

    echo "-";

}

?>

</p>

</div>

<div class="card">

<h3>Most Active Student</h3>

<p>

<?php

if($top_student){

    echo $top_student['student_name'];

}

else{

    echo "-";

}

?>

</p>

</div>

</div>

</div>

</div>

<script>
    function toggleReportsNavDropdown(event) {
        if (event) {
            event.stopPropagation();
        }

        document
            .getElementById("reportsNavDropdown")
            .classList.toggle("is-open");
    }

    window.addEventListener("click", function(e) {
        let reportsDropdown = document.getElementById("reportsNavDropdown");
        if (reportsDropdown && !reportsDropdown.contains(e.target)) {
            reportsDropdown.classList.remove("is-open");
        }
    });
</script>

</body>
</html>