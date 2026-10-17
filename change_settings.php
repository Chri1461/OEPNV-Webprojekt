<?php
    session_start();

    header('Content-Type: application/json');
    $incoming_data = file_get_contents("php://input");
    $data = json_decode($incoming_data, true);

    if (isset($data["Request"])) { // Send setting-values
        echo json_encode(["networks" => $_SESSION["Networks"], "states" => $_SESSION["States"]]);
        
    } else { // Change setting-vaules
        $networks = $_SESSION["Networks"] ?? [];
        $states = $_SESSION["States"] ?? [];

        if ($data) {// data: setting => Changed-Setting, change => [changed_option, changed_to_val ("" / 1)]

            $mode = ["Networks" => $networks, "States" => $states];
            if ($data["change"][1]) {// Checked
                $mode[$data["setting"]][] = $data["change"][0];
            } else { // Unchecked
                $mode[$data["setting"]] = array_values(array_diff($mode[$data["setting"]], [$data["change"][0]]));
            }

            $_SESSION[$data["setting"]] = $mode[$data["setting"]];
            unset($_SESSION["ziel_data"]);
            $_SESSION["guessed_stations"] = [];
            $_SESSION["guess_count"] = 0;
        }
    }
?>