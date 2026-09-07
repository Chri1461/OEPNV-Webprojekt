<?php
    session_start();

    header('Content-Type: application/json');
    $incoming_data = file_get_contents("php://input");
    $data = json_decode($incoming_data, true);

    if (isset($data) && isset($_SESSION["guessed_stations"])) {
        echo json_encode(["stations" => $_SESSION["guessed_stations"]]);
    }
?>