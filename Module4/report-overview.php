<?php

include '../INCLUDE/db.php';

date_default_timezone_set("Asia/Kuala_Lumpur");

$total_students =
mysqli_num_rows(
mysqli_query($conn,
"SELECT DISTINCT student_id FROM attendance")
);

$total_records =
mysqli_num_rows(
mysqli_query($conn,
"SELECT * FROM attendance")
);

$total_events =
mysqli_num_rows(
mysqli_query($conn,
"SELECT DISTINCT event_name FROM attendance")
);

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

<title>Reports Overview</title>

<link rel="stylesheet" href="../CSS/style.css">
<link rel="stylesheet" href="../CSS/module4-dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../CSS/adminHeader.css">

</head>

<body>

<?php include '../INCLUDE/AdminHeader.php'; ?>

<div class="container">

<div class="content">

<h1>Reports Overview</h1>

<!-- REPORT CARDS -->

<div class="card-container">

<div class="card">

<h3>Total Students</h3>

<p>
<?php echo $total_students; ?>
</p>

</div>

<div class="card">

<h3>Total Attendance Records</h3>

<p>
<?php echo $total_records; ?>
</p>

</div>

<div class="card">

<h3>Total Events</h3>

<p>
<?php echo $total_events; ?>
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

<!-- EXPORT BUTTON -->

<div style="margin-top:30px; margin-bottom:25px;">

<a href="export-pdf.php"
style="

background:linear-gradient(135deg,#7b2ff7,#5f0fff);
color:white;
padding:14px 24px;
border-radius:12px;
text-decoration:none;
font-weight:bold;
display:inline-block;
box-shadow:0 4px 10px rgba(0,0,0,0.2);

">

Export PDF

</a>

</div>

<!-- REPORT TABLE -->

<div class="table-wrapper">

<table>

<tr>

<th>Student Name</th>
<th>Student ID</th>
<th>Club Name</th>
<th>Event Name</th>
<th>Date</th>
<th>Time</th>
<th>Status</th>
<th>Volunteer</th>
<th>Points</th>

</tr>

<?php

$query =
mysqli_query($conn,
"SELECT * FROM attendance
ORDER BY id DESC");

while($row = mysqli_fetch_assoc($query)){

?>

<tr>

<td>
<?php echo $row['student_name']; ?>
</td>

<td>
<?php echo $row['student_id']; ?>
</td>

<td>
<?php echo $row['club_name']; ?>
</td>

<td>
<?php echo $row['event_name']; ?>
</td>

<td>
<?php echo $row['attendance_date']; ?>
</td>

<td>
<?php echo $row['attendance_time']; ?>
</td>

<td>
<?php echo $row['attendance_status']; ?>
</td>

<td>
<?php echo $row['volunteer_status']; ?>
</td>

<td>
<?php echo $row['points']; ?>
</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</body>
</html>