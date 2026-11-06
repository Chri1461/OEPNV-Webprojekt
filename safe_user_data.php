<?php
    function cookie_handler() {
        include("database-login.php");
        $connection = mysqli_connect($host, $user, $password, $database);
        mysqli_set_charset($connection, "utf8mb4");

        if (!isset($_COOKIE["uid"])) {
            $guessed_station_count = json_encode([$_SESSION["guessed_stations"][0] ??= "Hauptbahnhof" => 0], JSON_UNESCAPED_UNICODE);

            mysqli_query($connection, "INSERT INTO user VALUES(NULL, '$date', '$guessed_station_count')");
            $uid = mysqli_insert_id($connection);   

            // Cookie verschlüsseln
            $key = hex2bin($encryption_key);
            $jsonPayload = json_encode($uid);
            $ivLength = openssl_get_cipher_methods(true) ? openssl_cipher_iv_length('aes-256-gcm') : 12;
            $iv = random_bytes($ivLength);
            $ciphertext = openssl_encrypt($jsonPayload, 'aes-256-gcm', $key, 1, $iv, $tag);
            $val = base64_encode($iv . $tag . $ciphertext);

            setcookie("uid", $val, time() + (86400 * 7), "/");

        } else {
            $key = hex2bin($encryption_key);
            $decryptedData = null;

            if (isset($_COOKIE["uid"])) {
                $decodedData = base64_decode($_COOKIE['uid'], true);
                // Cookie entschlüsseln
                if ($decodedData !== false) {
                    $ivLength = 12;
                    $tagLength = 16;
                    
                    if (mb_strlen($decodedData, '8bit') > ($ivLength + $tagLength)) {
                        $iv = mb_substr($decodedData, 0, $ivLength, '8bit');
                        $tag = mb_substr($decodedData, $ivLength, $tagLength, '8bit');
                        $ciphertext = mb_substr($decodedData, $ivLength + $tagLength, null, '8bit');
                        
                        $jsonPayload = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, 1, $iv, $tag);
                        
                        if ($jsonPayload !== false) {
                            $decryptedData = json_decode($jsonPayload, true);
                        }
                    }
                }
            }

            if ($decryptedData) {
                $uid = $decryptedData;
            } else {throw new Error("Cookie konnte nicht entschlüsselt werden");}
        }
        return $uid ?? NULL;
    }

    function safe_game() {
        include("database-login.php");
        $connection = mysqli_connect($host, $user, $password, $database);
        mysqli_set_charset($connection, "utf8mb4");

        date_default_timezone_set('Europe/Berlin');
        $date = date("Y-m-d", time());
        $time = date("H:i:s", time());
        $city = "berlin";

        $uid = cookie_handler();

        $gamemode = json_encode($_SESSION["Networks"], JSON_UNESCAPED_UNICODE);
        $guesses = json_encode($_SESSION["guessed_stations"], JSON_UNESCAPED_UNICODE);
        $guess_count = $_SESSION["guess_count"];

        $sql = "INSERT INTO games
                VALUES(NULL, $uid, '$date', '$time', '$city', '$gamemode', '$guesses', $guess_count)";
        mysqli_query($connection, $sql);

        $appends = [];
        foreach ($_SESSION["guessed_stations"] as $station) {
            $json_path = "'$.\"" . $station . "\"'";
            $appends[] = $json_path;
            $appends[] = "IFNULL(JSON_UNQUOTE(JSON_EXTRACT(gsc, $json_path)), 0) + 1";
        }
        $json_update = implode(", ", $appends);

        $sql = "UPDATE user 
                SET gsc = JSON_SET(COALESCE(gsc, '{}'), $json_update)
                WHERE uid = $uid";
        mysqli_query($connection, $sql);

        mysqli_close($connection);
    }
?>