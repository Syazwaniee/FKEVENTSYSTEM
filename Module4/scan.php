<?php

include '../INCLUDE/db.php';

date_default_timezone_set("Asia/Kuala_Lumpur");

if(isset($_POST['submit_attendance'])){

    $student_name = $_POST['student_name'];

    $student_id = $_POST['student_id'];

    $club_name = $_POST['club_name'];

    $event_name = $_POST['event_name'];

    $attendance_status = $_POST['attendance_status'];

    $volunteer_status = $_POST['volunteer_status'];

    $attendance_date = date("Y-m-d");

    $attendance_time = date("H:i:s");

    $points = 0;

    if($attendance_status == "Present"){
        $points += 10;
    }

    elseif($attendance_status == "Late"){
        $points += 5;
    }

    elseif($attendance_status == "Absent"){
        $points -= 10;
    }

    if($volunteer_status == "Yes"){
        $points += 5;
    }

    $sql = "INSERT INTO attendance

    (
    student_name,
    student_id,
    club_name,
    event_name,
    attendance_date,
    attendance_time,
    attendance_status,
    volunteer_status,
    points
    )

    VALUES

    (
    '$student_name',
    '$student_id',
    '$club_name',
    '$event_name',
    '$attendance_date',
    '$attendance_time',
    '$attendance_status',
    '$volunteer_status',
    '$points'
    )";

    mysqli_query($conn, $sql);

    echo "<script>

    alert('Attendance Recorded Successfully');

    window.location='../Module4/scan.php';

    </script>";

}

?>

<!DOCTYPE html>
<html>

<head>

<title>QR Attendance Form</title>

<link rel="stylesheet" href="../CSS/module4_style.css">

</head>

<body>

<div class="container">


<div class="content">

<h1>QR Attendance Form</h1>

<div class="form-container">

<form method="POST" class="modern-form">

<div class="form-row">

<div class="form-group">

<label>Student Name</label>

<input type="text"
name="student_name"
placeholder="Enter Student Name"
required>

</div>

<div class="form-group">

<label>Student ID</label>

<input type="text"
name="student_id"
placeholder="Enter Student ID"
required>

</div>

</div>

<div class="form-row">

<div class="form-group">

<label>Club Name</label>

<select name="club_name">

<option>Programming & Coding Club</option>
<option>Cyber Security Club</option>
<option>Data Science & AI Club</option>
<option>Game Development Club</option>
<option>Cloud Computing Club</option>

</select>

</div>

<div class="form-group">

<label>Event Name</label>

<input type="text"
name="event_name"
placeholder="Enter Event Name"
required>

</div>

</div>

<div class="form-row">

<div class="form-group">

<label>Attendance Status</label>

<select name="attendance_status">

<option>Present</option>
<option>Late</option>
<option>Absent</option>

</select>

</div>

<div class="form-group">

<label>Volunteer / Helper</label>

<select name="volunteer_status">

<option>No</option>
<option>Yes</option>

</select>

</div>

</div>

<button type="submit"
name="submit_attendance"
class="submit-btn">

Submit Attendance

</button>

</form>

</div>

</div>

</div>

</body>
</html>