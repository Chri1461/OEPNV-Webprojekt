document.addEventListener("DOMContentLoaded", function() {
get_stations();

const input_field = document.getElementById("station_input");
const out = document.getElementById("out");
const next = document.getElementById("next_round");
const suggest_div = document.getElementById("suggest");
const settings = document.getElementById("settings");
const overlay_container = document.getElementById("overlay_container");

input_field.addEventListener("input", suggest);
suggest_div.addEventListener("click", use_input);
next.addEventListener("click", reset_html);
settings.addEventListener("click", open_settings);
overlay_container.addEventListener("click", close_settings);

let html_content = "";
async function open_settings() {
    try {
        const resp = await fetch("change_settings.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({Request: "All"})
        });

        const data = await resp.json();
        for (let key in data) {
            data[key].forEach(element => {
            var checkbox = document.getElementById(element);
            checkbox.checked = true; 
        })};
    } catch (e) {}

    overlay_container.style.display = "block"; 
    const overlay = document.getElementById("overlay");
    overlay.addEventListener("change", change_settings);
}

function close_settings(event) {
    if (event.target.id == "overlay_container") {        
        overlay_container.style.display = "none";  
    }
}

async function change_settings(event) {
    var amount = document.querySelectorAll('input[type="checkbox"]:checked').length;
    if (amount == 0) {
        event.target.checked = true;
        return;
    }

    if (event.target.type === "checkbox") {
        var sent_body = JSON.stringify({setting: event.target.className, change: [event.target.id, event.target.checked]})
        try {
            const resp = await fetch("change_settings.php", {
                method: "POST",
                headers: {"Content-Type": "application/json"},
                body: sent_body
            });
            get_stations();
            reset_html();

        } catch (e) {}
    }
}

async function use_input(event, s="") {
    let station;
    if (event) {
        if (event.target.id != "suggestion") {return -1;} // Verhindert, dass Klick auf <DIV> einen Fehler gibt, da nur Inhalt eines <p> erwartet wird
        station = event.target.textContent.replace(" " + count_only_letters(event.target.textContent), "");
    } else {station = s;}

    input_field.value = "";
    suggest_div.innerHTML = "";
    try {
        const resp = await fetch("game_logic.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({Station: station})
        });

        if (!resp.ok) {
            throw new Error(`Netzwerkfehler: ${resp.status}`);
        }

        const data = await resp.json();
        if (data.Error) {
            html_content += "<p> FEHLER! </p>"
        } else {
            if (data.direction != "") {
                html_content += "<div id=guess><p> Die gesuchte Station liegt im <b>" + data.direction + "</b> von <i><b>" + data.guess + "</b> " + count_only_letters(data.guess) + "</i> </p>";
            } else {
                html_content += "<div id=guess><p> Die gesuchte Station wurde gefunden: <i><b>" + data.guess + "</b> " + count_only_letters(data.guess) + "</i> </p>";
                next.innerHTML = "<p id=suggestion>Nächste Runde</p>";
                input_field.setAttribute("disabled", "disabled");
            }
            html_content += "<div id=" + data.correct_lines + ">";
            let last_network = data.lines[0];
            data.lines.split(", ").forEach(line => {
                if (last_network != line[0]) {
                    html_content += "<br>";
                    last_network = line[0];
                };
                html_content += "<img id='icon' src=Icons/" + line + ".svg>";
            });
            html_content += "</div>";
            html_content += "<p id=" + data.correct_district + ">" + data.district + "</p>";
            html_content += "<p id='" + data.correct_len + "'> Anzahl Buchstaben: <i>" + count_only_letters(data.guess) + "</i></p></div>";

        }
    } catch(e) {
        html_content = "<p>" + e + "</p>";
    }

    out.innerHTML = html_content;
    input_field.focus();
}

let stationsliste = [];
async function get_stations() {
    try {
        const resp = await fetch("fetch_database.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({Station: "All"})
        });

        if (!resp.ok) {
            throw new Error(`Netzwerkfehler: ${resp.status}`);
        }

        const data = await resp.json();
        stationsliste = data;
        
        get_last_game();

    } catch (e) {alert(e);}
}

async function get_last_game() {
    try {
        const resp = await fetch("request_last_game.php", {
            method: "POST",
            headers: {"Content-Type": "application/json"},
            body: JSON.stringify({Request: "All"})
        });

        if (!resp.ok) {
            throw new Error(`Netzwerkfehler: ${resp.status}`);
        }

        const data = await resp.json();
        for (const element of data.stations) {
            await use_input(false, s=element);
        }

    } catch (e) {}
}

let html_suggest = "";
function suggest(event) {
    html_suggest = "";
    let limit = 3;
    for (let i = 0; i < stationsliste.length; i++) {
        if (stationsliste[i][0].toLowerCase().startsWith(event.target.value.toLowerCase()) && limit > 0 && event.target.value != "") {
            html_suggest += "<p id='suggestion'>" + stationsliste[i][0] + " <i>" + count_only_letters(stationsliste[i][0]) + "</i></p>";
            limit -= 1;
        }
    }
    suggest_div.innerHTML = html_suggest;
}

function count_only_letters(text) {
    let letter_text = text.replaceAll(new RegExp("[^A-Za-zäöüÄÖÜß]", "g"), "");
    return "(" + letter_text.length + ")"
}

function reset_html() {
    html_content = "";
    out.innerHTML = "";
    suggest_div.innerHTML = "";
    next.innerHTML = "";
    input_field.removeAttribute("disabled");
    input_field.value = "";
}
});