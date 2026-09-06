<?php
    session_start();

    header('Content-Type: application/json');
    $incoming_data = file_get_contents("php://input");
    $data = json_decode($incoming_data, true);

    if (isset($data["Request"])) {
        if ($data["Request"] == "All") {
            echo json_encode(["networks" => $_SESSION["Networks"]]);
        }
    } else {

        if (isset($_SESSION["Networks"])) {
            $networks = $_SESSION["Networks"];
        } else {$networks = [];}

        if ($data) {
            foreach($data as $key => $value) {
                if ($value) {
                    $networks[] = $key;
                } else {$networks = array_values(array_diff($networks, [$key]));}
            } 
            $_SESSION["Networks"] = $networks;
            unset($_SESSION["ziel_data"]);
        }
    }
?>