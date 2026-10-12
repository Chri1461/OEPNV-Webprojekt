<?php
    session_start();

    header('Content-Type: application/json');
    $incoming_data = file_get_contents("php://input");
    $data = json_decode($incoming_data, true);

    // Datenbank Verbindungsaufbau
    include("database-login.php");
    $connection = mysqli_connect($host, $user, $password, $database);
    mysqli_set_charset($connection, "utf8mb4");

    $city = "berlin";
    $selected_transport = [];
    if (!isset($_SESSION["Networks"])) {
        $_SESSION["Networks"] = ["cityline"];
    }
    foreach($_SESSION["Networks"] as $i) {
        $selected_transport[] = "{$i}_{$city}";
    }

    $sub_queries = [];
    foreach($selected_transport as $i) {
        $sub_queries[] = "SELECT * FROM $i";
    }
    $union = implode(" UNION ALL ", $sub_queries);

    if ($data && isset($data["Station"])) {
        $sql = "SELECT Station
                FROM ($union) AS Vereinigung
                GROUP BY Station";
        $result = mysqli_query($connection, $sql);
        $arr = mysqli_fetch_all($result);
    } else {$arr = "ERROR";}
    
    mysqli_close($connection);
    echo json_encode($arr);
?>
