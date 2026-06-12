<?php

include 'db_connect.php';

$data = json_decode(
    file_get_contents("php://input"),
    true
);

if($data){

    $student_id = $data['student_id'];
    $club_name = $data['club_name'];
    $event_name = $data['event_name'];
    $attendance_date = $data['attendance_date'];
    $attendance_status = $data['attendance_status'];
    $volunteer_status = $data['volunteer_status'];

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
    student_id,
    club_name,
    event_name,
    attendance_date,
    attendance_status,
    volunteer_status,
    points
    )

    VALUES

    (
    '$student_id',
    '$club_name',
    '$event_name',
    '$attendance_date',
    '$attendance_status',
    '$volunteer_status',
    '$points'
    )";

    mysqli_query($conn, $sql);

    echo "Success";

}

else{

    echo "API Running";

}

?>