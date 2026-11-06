<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Guess the Station</title>
        <link rel="icon" href="Icons/favicon.ico">
        <link rel="stylesheet" href="style.css">
    </head>
    <body style="text-align: left;">
        <a href="index.html">Zurück</a>
    </body>
        <?php
            include("safe_user_data.php");
            include("database-login.php");
            $connection = mysqli_connect($host, $user, $password, $database);
            mysqli_set_charset($connection, "utf8mb4");

            $uid = cookie_handler();
            $sql = "SELECT gsc
                    FROM user
                    WHERE uid = $uid";

            $result = mysqli_query($connection, $sql);
            $row = mysqli_fetch_assoc($result);
            $gsc = json_decode($row["gsc"], true);

            arsort($gsc); 

            foreach ($gsc as $station => $count) {
                echo "<p style='text-align: center'> $station: $count </p>";
            }
        ?>
</html>