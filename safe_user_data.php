<?php
    function safe_game() {
        include("database-login.php");
        $connection = mysqli_connect($host, $user, $password, $database);
        mysqli_set_charset($connection, "utf8mb4");
        date_default_timezone_set('Europe/Berlin');

        if (!isset($_COOKIE["uid"])) {
            $test = json_encode(["Hallo" => 0], JSON_UNESCAPED_UNICODE);

            $date = date("Y-d-m", time());
            mysqli_query($connection, "INSERT INTO user VALUES(NULL, '$date', '$test')");
            $uid = mysqli_insert_id($connection);   

            $key = hex2bin($encryption_key);
            $jsonPayload = json_encode($uid);
            $ivLength = openssl_get_cipher_methods(true) ? openssl_cipher_iv_length('aes-256-gcm') : 12;
            $iv = random_bytes($ivLength);
            $ciphertext = openssl_encrypt($jsonPayload, 'aes-256-gcm', $key, 1, $iv, $tag);
            $val = base64_encode($iv . $tag . $ciphertext);

            setcookie("uid", $val, time() + (86400 * 365), "/");

        } else {
            $key = hex2bin($encryption_key);
            $decryptedData = null;

            if (isset($_COOKIE["uid"])) {
                $decodedData = base64_decode($_COOKIE['uid'], true);
                
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
            } else {throw new Error("Hmm");}
        }
        
        $date = date("Y-d-m", time());
        $time = date("H:i:s", time());
        $city = "berlin";
        $gamemode = json_encode($_SESSION["Networks"], JSON_UNESCAPED_UNICODE);
        $guesses = json_encode($_SESSION["guessed_stations"], JSON_UNESCAPED_UNICODE);
        $guess_count = $_SESSION["guess_count"];

        $sql = "INSERT INTO games
                VALUES(NULL, $uid, '$date', '$time', '$city', '$gamemode', '$guesses', $guess_count)";
        mysqli_query($connection, $sql);

        mysqli_close($connection);
    }
?>