<?php
    session_start();

    header('Content-Type: application/json');
    $incoming_data = file_get_contents("php://input");
    $data = json_decode($incoming_data, true);

    if (isset($data["Request"])) {
        if ($data["Request"] == "All") {
            echo json_encode(["networks" => $_SESSION["Networks"], "states" => $_SESSION["States"]]);
        }
    } else {

        if (isset($_SESSION["Networks"])) {
            $networks = $_SESSION["Networks"];
        } else {$networks = [];}

        if (isset($_SESSION["States"])) {
            $states = $_SESSION["States"];
        } else {$states = [];}

        if ($data) {
            if ($data["setting"] == "network") {
                foreach($data as $key => $value) {
                    if ($value && $key != "setting") {
                        $networks[] = $key;
                    } else {$networks = array_values(array_diff($networks, [$key]));}
                } 
                $_SESSION["Networks"] = $networks;
                unset($_SESSION["ziel_data"]);
                $_SESSION["guessed_stations"] = [];
                $_SESSION["guess_count"] = 0;

            } else if ($data["setting"] == "state") {
                if ($data["setting"] == "state") {
                    foreach($data as $key => $value) {
                        if ($value && $key != "setting") {
                            $states[] = $key;
                        } else {$states = array_values(array_diff($states, [$key]));}
                    } 
                    $_SESSION["States"] = $states;
                    unset($_SESSION["ziel_data"]);
                    $_SESSION["guessed_stations"] = [];
                    $_SESSION["guess_count"] = 0;
                }
            }
        }
    }
?>