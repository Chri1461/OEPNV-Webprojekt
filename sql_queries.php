<?php
    $city = "berlin";
    $_SESSION["Networks"] ??= ["cityline"];
    $_SESSION["States"] ??= ["Brandenburg", "Berlin"];

    // Auswahl aller Tabellen (Netzwerke), mit denen gespielt werden soll
    $tables = [];
    foreach($_SESSION["Networks"] as $i) {$tables[] = "{$i}_{$city}";}
    
    $temp = [];
    foreach($tables as $i) {$temp[] = "SELECT * FROM $i";}
    $network_union = implode(" UNION ALL ", $temp);

    // States, die zum Spielen ausgewählt wurden
    $temp = [];
    foreach($_SESSION["States"] as $i) {$temp[] = "county.state = '$i'";}
    $where_states = implode(" OR ", $temp);
    if (!$where_states) {$where_states = "True";} // Alle States auswählen, falls keine angeklickt

    $sql_fetch = "SELECT Station
                  FROM ($network_union) AS Vereinigung, county
                  WHERE county.name = Bezirk AND ($where_states)
                  GROUP BY Station";

    $sql_get_target = "SELECT Station, GROUP_CONCAT(Linie SEPARATOR ', ') AS Linie, Bezirk, Latitude, Longitude
                       FROM ($network_union) AS Vereinigung, county
                       WHERE county.name = Bezirk AND ($where_states)
                       GROUP BY Station
                       ORDER BY RAND() 
                       LIMIT 1";

    $guessed_name = $_SESSION["last_guess"] ?? Null;
    $sql_get_guess = "SELECT Station, GROUP_CONCAT(Linie SEPARATOR ', ') AS Linie, Bezirk, Latitude, Longitude
                      FROM ($network_union) AS Vereinigung, county
                      WHERE Station = '$guessed_name' AND county.name = Bezirk AND ($where_states)
                      GROUP BY Station
                      LIMIT 1";
?>