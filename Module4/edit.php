<?php

require_once __DIR__ . '/../INCLUDE/db.php';

date_default_timezone_set('Asia/Kuala_Lumpur');

if (empty($_SESSION['user']['User_id']) || strtolower((string) ($_SESSION['user']['role'] ?? '')) !== 'committee') {
    header('Location: ../login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$eventId = (int) ($_GET['event_id'] ?? 0);

$stmt = $conn->prepare('SELECT * FROM attendance WHERE id = ? LIMIT 1');
$stmt->bind_param('i', $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    header('Location: ' . ($eventId > 0 ? 'manage-event-attendance.php?event_id=' . $eventId : '../Module3/clubEvents.php'));
    exit;
}

if(isset($_POST['update_attendance'])){

    $student_name = $_POST['student_name'];

    $student_id = $_POST['student_id'];

    $club_name = $_POST['club_name'];

    $event_name = $_POST['event_name'];

    $attendance_date = $_POST['attendance_date'];

    $attendance_time = $_POST['attendance_time'];

    $attendance_status = $_POST['attendance_status'];

    $volunteer_status = $_POST['volunteer_status'];

    $points = calculateAttendancePoints($attendance_status, $volunteer_status);

    $updateStmt = $conn->prepare(
        'UPDATE attendance SET
         student_name = ?, student_id = ?, club_name = ?, event_name = ?,
         attendance_date = ?, attendance_time = ?, attendance_status = ?,
         volunteer_status = ?, points = ?
         WHERE id = ?'
    );
    if ($updateStmt) {
        $updateStmt->bind_param(
            'ssssssssii',
            $student_name,
            $student_id,
            $club_name,
            $event_name,
            $attendance_date,
            $attendance_time,
            $attendance_status,
            $volunteer_status,
            $points,
            $id
        );
        $updateStmt->execute();
        $updateStmt->close();
    }

    $redirectEventId = $eventId > 0 ? $eventId : (int) ($_POST['event_id'] ?? 0);
    header(
        'Location: manage-event-attendance.php'
        . ($redirectEventId > 0 ? '?event_id=' . $redirectEventId : '')
    );

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Edit Attendance</title>

<link rel="stylesheet"
href="style.css">

</head>

<body>

<div class="container">

<div class="navbar">

<div class="logo">
    <img src="logo.png" alt="Logo">
</div>

<div class="nav-menu">

<a href="index.php">
Dashboard
</a>

<a href="manage-event-attendance.php"
class="active">

Attendance

</a>

<a href="participation-history.php">
Participation History
</a>

<a href="top-student-ranking.php">
Ranking
</a>

<a href="report-overview.php">
Reports
</a>

<a href="participation-dashboard.php">
Dashboard Analytics
</a>

</div>

</div>

<div class="content">

<h1>Edit Attendance</h1>

<div class="form-container">

<form method="POST"
class="modern-form">

<div class="form-row">

<div class="form-group">

<label>Student Name</label>

<input type="text"
name="student_name"

value="<?php
echo $row['student_name'];
?>"

required>

</div>

<div class="form-group">

<label>Student ID</label>

<input type="text"
name="student_id"

value="<?php
echo $row['student_id'];
?>"

required>

</div>

</div>

<div class="form-row">

<div class="form-group">

<label>Club Name</label>

<select name="club_name">

<option
<?php
if($row['club_name']=="Programming & Coding Club")
echo "selected";
?>>

Programming & Coding Club

</option>

<option
<?php
if($row['club_name']=="Cyber Security Club")
echo "selected";
?>>

Cyber Security Club

</option>

<option
<?php
if($row['club_name']=="Data Science & AI Club")
echo "selected";
?>>

Data Science & AI Club

</option>

<option
<?php
if($row['club_name']=="Game Development Club")
echo "selected";
?>>

Game Development Club

</option>

<option
<?php
if($row['club_name']=="Cloud Computing Club")
echo "selected";
?>>

Cloud Computing Club

</option>

</select>

</div>

<div class="form-group">

<label>Event Name</label>

<input type="text"
name="event_name"

value="<?php
echo $row['event_name'];
?>"

required>

</div>

</div>

<div class="form-row">

<div class="form-group">

<label>Attendance Date</label>

<input type="date"
name="attendance_date"

value="<?php
echo $row['attendance_date'];
?>"

required>

</div>

<div class="form-group">

<label>Attendance Time</label>

<input type="time"
name="attendance_time"

value="<?php
echo $row['attendance_time'];
?>"

required>

</div>

</div>

<div class="form-row">

<div class="form-group">

<label>Attendance Status</label>

<select name="attendance_status">

<option
<?php
if($row['attendance_status']=="Present")
echo "selected";
?>>

Present

</option>

<option
<?php
if($row['attendance_status']=="Late")
echo "selected";
?>>

Late

</option>

<option
<?php
if($row['attendance_status']=="Absent")
echo "selected";
?>>

Absent

</option>

</select>

</div>

<div class="form-group">

<label>Volunteer / Helper</label>

<select name="volunteer_status">

<option
<?php
if($row['volunteer_status']=="No")
echo "selected";
?>>

No

</option>

<option
<?php
if($row['volunteer_status']=="Yes")
echo "selected";
?>>

Yes

</option>

</select>

</div>

</div>

<button type="submit"
name="update_attendance"
class="submit-btn">

Update Attendance

</button>

</form>

</div>

</div>

</div>

</body>
</html>