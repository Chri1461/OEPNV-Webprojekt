<?php
    session_start();

    header('Content-Type: application/json');
    $incoming_data = file_get_contents("php://input");
    $data = json_decode($incoming_data, true);

    // Datenbank Verbindungsaufbau und vorbereitete Queries laden
    include("database-login.php");
    include("sql_queries.php");
    $connection = mysqli_connect($host, $user, $password, $database);
    mysqli_set_charset($connection, "utf8mb4");

    if ($data && isset($data["Station"])) {
        $result = mysqli_query($connection, $sql_fetch);
        $arr = mysqli_fetch_all($result);
    } else {$arr = [];} //TODO: Sinnvolle Fehlerbehandlung überlegen
    
    mysqli_close($connection);
    echo json_encode($arr);
?>