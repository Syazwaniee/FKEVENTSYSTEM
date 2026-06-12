<?php

include '../INCLUDE/db.php';

$search = "";

if(isset($_GET['search']) && $_GET['search'] != ""){

    $search = $_GET['search'];

    $query =

    "SELECT * FROM attendance

    WHERE student_id LIKE '%$search%'

    ORDER BY id DESC";

}

else{

    $query =

    "SELECT * FROM attendance

    ORDER BY id DESC";

}

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
content="width=device-width, initial-scale=1.0">

<title>Participation History</title>

<link rel="stylesheet" href="../CSS/style.css">
<link rel="stylesheet" href="../CSS/module4-dashboard.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="../CSS/adminHeader.css">

</head>

<body>

<?php include '../INCLUDE/AdminHeader.php'; ?>

<div class="container">

<!-- CONTENT -->

<div class="content">

<h1>Participation History</h1>

<!-- SEARCH -->

<div class="form-container">

<form method="GET"
class="modern-form">

<div class="form-row">

<div class="form-group">

<label>Search Student ID</label>

<input type="text"

name="search"

placeholder="Enter Student ID"

value="<?php
echo $search;
?>">

</div>

</div>

<div class="action-buttons">

<button type="submit"
class="submit-btn">

Search

</button>

<a href="../Module4/participation-history.php"
class="edit-btn">

Show All

</a>

</div>

</form>

</div>

<!-- TABLE -->

<div class="table-wrapper">

<table>

<tr>

<th>Student ID</th>
<th>Club Name</th>
<th>Event Name</th>
<th>Date</th>
<th>Status</th>
<th>Volunteer</th>
<th>Points</th>
<th>Recognition</th>

</tr>

<?php

while($row = mysqli_fetch_assoc($result)){

$recognition = "";

if($row['points'] < 20){

    $recognition = "Warning";

}

elseif($row['points'] >= 20
&& $row['points'] <= 49){

    $recognition =
    "Participation Certificate";

}

elseif($row['points'] >= 50
&& $row['points'] <= 79){

    $recognition =
    "Active Student";

}

else{

    $recognition =
    "Outstanding Participant";

}

?>

<tr>

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
<?php echo $row['attendance_status']; ?>
</td>

<td>
<?php echo $row['volunteer_status']; ?>
</td>

<td>
<?php echo $row['points']; ?>
</td>

<td>
<?php echo $recognition; ?>
</td>

</tr>

<?php } ?>

</table>

</div>

</div>

</div>

</body>
</html>