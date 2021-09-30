<!DOCTYPE html>
<html lang="de">

<head>
    <title>Homepage</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta charset="utf-8" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap');
    </style>
</head>

<body>
    <header>
        <div>
            <h1>
                <marquee scrollamount="10" scrolldelay="100">
                    Hier finden Sie die neusten Nachrichten!</marquee>
            </h1>
            <div class="headlink">
                <a href="edit.php">News Hinzufügen</a>
            </div>
        </div>
    </header>

    </div>
    <div class="layout">
        <div class="left">

            <?php

            //Click auf Button "Löschen" -> löscht News und Bild
            if (array_key_exists('delete_btn', $_POST)) {
                $error = null;
                $delFileName = $_POST['del_name'];

                if (isset($delFileName)) {
                    $file_path = "../pictures/" .  $delFileName . ".png";
                    if (file_exists($file_path)) {

                        if (is_file($file_path)) {
                            unlink($file_path);
                        } else {
                            $error = "Bild nicht vorhanden";
                        }

                        $file_path = "../text/" .  $delFileName . ".txt";

                        if (is_file($file_path)) {
                            unlink($file_path);
                        } else {
                            $error = "Newsdatei nicht vorhanden";
                        }
                    } else {
                        $error = "Datei/en nicht vorhanden";
                    }
                } else {
                    $error = "Kein Name vorhanden";
                }

                if ($error == null) {
                    echo "<p class='success deleteSuccess'>News gelöscht.</p>";
                } else {
                    echo "<p class='error deleteError'>" . $error . "</p>";
                }
            }

            /* Lese news von der Disk und schreibe die Dateinamen in ein Array
            *  Sortieren gemäss Datum und Uhrzeit
            */
            $newsArray = getArrayFromNews();

            //News und Bilder in HTML ausgeben
            renderNews($newsArray);
            ?>
        </div>
    </div>
</body>

</html>

<!----------------PHP_FUNCTIONS---------------->
<?php
//Erstellt die einzelnen News "Karten"
function create_news_card($title, $time, $subtext, $author, $name)
{
    echo "<div class='news'>
                <div class='content'>
                    <div class='title'>
                        <h2>$title</h2>
                    </div>
                    <div class='timestamp'>
                        <h4>$time</h4>
                    </div>
                    <div>
                    <img class='picture' src='../pictures/$name.png'>
                    </div>
                    <div class='text'>
                        <h3>$subtext</h3>
                    </div>
                </div>
                <div class='divButton'>
                    <form method='get' action='edit.php'> 
                        <input type='submit' class='button buttonEdit' value='Ändern' /> 
                        <input type='hidden' name='name' value='$name'>
                    </form>
                    <form method='post'>
                        <input type='submit' name='delete_btn' class='button buttonDelete'  value='Löschen' /> 
                        <input type='hidden' name='del_name' value='$name'>
                    </form>
                </div>
            </div>";
}

//Zerlegt die .txt Datei, speichert deren Inhalt und sortiert diese nach Datum
function getArrayFromNews()
{
    $fileName = "";
    $datum = null;
    $newsArray = array();
    foreach (glob("../text/*.txt") as $fileName) {
        if (is_readable($fileName)) {
            if (is_file($fileName)) {
                $content = file_get_contents($fileName);
                $exp_content = explode(">\/\>", $content);

                if (isset($exp_content) and count($exp_content) == 6) {
                    $datum = $exp_content[5];
                    $newsArray = $newsArray + array($datum => $fileName);
                }
            }
        }
    }

    krsort($newsArray);
    return $newsArray;
}

//Prüft ob .txt lesbar ist und ruft die Funktion auf welche die News "Karte" erstellt auf
function renderNews($newsArray)
{
    $fileName = "";

    foreach ($newsArray as $key => $val) {
        $fileName = $val;
        if (is_readable($fileName)) {
            if (is_file($fileName)) {
                $content = file_get_contents($fileName);
                $exp_content = explode(">\/\>", $content);

                for ($x = 0; $x <= 4; $x++) {
                    if (!isset($exp_content[$x])) {
                        $exp_content[$x] = "<p style='color:red'>EMPTY</p>";
                    }
                }

                //Pfad und Extension entfernen
                $exp_path = explode('/', $fileName);
                $name = explode('.', $exp_path[2])[0];
                create_news_card($exp_content[0], $exp_content[1], $exp_content[2], $exp_content[3], $name);
            }
        }
    }
}
?>