<!DOCTYPE html>
<html lang="de">

<head>
    <title>Editpage</title>
    <link rel="stylesheet" type="text/css" href="../css/style.css">
    <meta charset="utf-8" />
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@300&display=swap');
    </style>
</head>

<body>
    <header>
        <h1>Editor</h1>
        <div class="headlink">
            <a href="index.php">Zurück</a>
        </div>
    </header>
    <div class='layout'>
        <?php
        //--------------Datei prüfen und speichern-----------------
        $error = null;

        if (isset($_POST['submit_btn'])) {

            if ($_POST['user_filename'] == "") {
                $fileNameNeu = substr($_POST['title'], 0, 15) . date("Y-m-d H:i:s");
            } else {
                $fileNameNeu = $_POST['user_filename'];
            }

            $mode = $_POST['mode'];
            $arryFiles = explode('.', $_FILES['file']['name']);
            $extension = end($arryFiles);
            $fileNameNeu = replaceSonderZeichen($fileNameNeu);
            $pictureZiel = "../pictures/" . $fileNameNeu . "." . $extension;

            //Bild speichern. Bei einem Fehler wird der Fehlertext zurückgegeben
            $error = savePicture($pictureZiel, $mode);

            if ($error == null) {
                $error = saveNews($fileNameNeu);

                if ($error != null) {
                    if (is_file($pictureZiel)) {
                        unlink($pictureZiel);
                    }
                }
            }

            if (isset($error)) {
                echo "<p class='error formError'>" . $error . "</p>";
            } else {

                echo "<p class='success formSuccess'>Ihre Nachricht wurde erfolgreich gespeichert.</p>";
            }
        }

        //----------------FORMULAR WIRD GELADEN----------------
        $exp_content = array("", "", "", "", "");
        $exp_fileName = "";
        $fileName = "";
        $modeEdit = false;

        if (isset($_GET['name'])) {
            $fileName = "../text/" . $_GET['name'] . ".txt";
            $modeEdit = true;
        }

        if (is_readable($fileName)) {
            if (is_file($fileName)) {

                $exp_fileName = substr($fileName, 8);
                $exp_content = exp_content_filler($fileName);
            }
        }

        //Funktion für das erstellen des Formulars wird aufgerufen
        renderForm($exp_content, $exp_fileName, $modeEdit);

        //----------------PHP_FUNCTIONS---------------->
        //Formular wird hier erstellt
        function renderForm($exp_content, $fileName, $modeEdit)
        {
            $readonly = "";
            $modeValue = "new";
            $pictureRequired = "required='required'";
            if ($modeEdit == true) {
                $readonly = "readonly";
                $fileName = str_replace(".txt", "", $fileName);
                $modeValue = "edit";
                $pictureRequired = "";
            }

        ?>
            <div class='formular'>
                <form enctype='multipart/form-data' action='' method='post'>
                    <div><label for='user_filename'>Benutzerdefinierter Dateiname</label>
                        <input id='user_filename' type='text' name='user_filename' maxlength='100' value='<?= $fileName ?>' placeholder='nicht notwendig, wird automatisch erstellt' <?= $readonly ?>>
                    </div>
                    <div><label for='title'>Titel der News</label>
                        <input id='title' type='text' name='title' value='<?= $exp_content[0] ?>' required='required' maxlength='80'>
                    </div>
                    <div><label for='message'>News Nachricht</label>
                        <textarea id='message' cols='50' rows='10' name='message' maxlength='2000' required='required'><?= $exp_content[2] ?></textarea>
                    </div>
                    <div><label for='file'>Wählen Sie ein PNG Bild</label>
                        <input type='hidden' name='MAX_FILE_SIZE' value='500000' />
                        <input id='file' type='file' name='file' accept='image/x-png' $pictureRequired />
                    </div>
                    <div><label for='author'>Autor Name</label>
                        <input id='author' type='text' name='author' maxlength='50' value='<?= $exp_content[3] ?>' required='required'>
                    </div>
                    <div><label for='comment'>Beschreibung für den Administrator</label>
                        <input id='comment' type='text' name='comment' maxlength='255' value='<?= $exp_content[4] ?>' placeholder='nicht notwendig, sieht man nur bei Bearbeitung' >
                    </div>
                    <div>
                        <input type='hidden' name='mode' value='<?= $modeValue ?>'>
                        <input id='submit' type='submit' value='Senden' name='submit_btn'>
                    </div>
                </form>
            </div>
        <?php
        }

        //Sonderzeichen entfernen wie Umlaute, welche die Windows nicht erlaubt 
        function replaceSonderZeichen($string)
        {
            $string = str_replace("ä", "ae", $string);
            $string = str_replace("ü", "ue", $string);
            $string = str_replace("ö", "oe", $string);
            $string = str_replace("Ä", "Ae", $string);
            $string = str_replace("Ü", "Ue", $string);
            $string = str_replace("Ö", "Oe", $string);
            $string = str_replace("ß", "ss", $string);
            $string = str_replace("´", "", $string);
            $string = str_replace(" ", "_", $string);
            $string = str_replace("/", "_", $string);
            $string = str_replace("\\", "_", $string);
            $string = str_replace(":", "_", $string);
            $string = str_replace("?", "_", $string);
            $string = str_replace("\"", "_", $string);
            $string = str_replace("<", "_", $string);
            $string = str_replace(">", "_", $string);
            $string = str_replace("|", "_", $string);
            return $string;
        }

        //.txt Datei wird gelesen und dessen Inhalt abgespeichert
        function exp_content_filler($fileName)
        {
            $content = file_get_contents($fileName);
            $exp_content = explode(">\/\>", $content);

            for ($x = 0; $x <= 5; $x++) {
                if (!isset($exp_content[$x])) {
                    $exp_content[$x] = "";
                }
            }
            return $exp_content;
        }

        //Kontrolle zu Fehlern (Serverseitig) und abspeichern der Dateien (Text und Bild)
        function saveNews($fileName)
        {
            $error = "";
            $title = trim($_POST['title']);
            $message =  trim($_POST['message']);
            $author = trim($_POST['author']);
            $comment = trim($_POST['comment']);

            if (isset($title) == false or strlen($title) < 1) {
                $error = "Bitte erfassen Sie einen Titel.<br/>";
            } else if (strlen($title) > 80) {
                $error = "Der Titel hat mehr als 80 Zeichen.<br/>";
            }

            if (isset($message) == false or strlen($message) < 1) {
                $error .= "Bitte erfassen Sie eine Nachricht.<br/>";
            } else if (strlen($message) > 2000) {
                $error .= "Die Nachricht hat mehr als 2000 Zeichen.<br/>";
            }

            if (isset($author) == false or strlen($author) < 1) {
                $error .= "Bitte erfassen Sie einen Autor.<br/>";
            } else if (strlen($author) > 50) {
                $error .= "Der Autor hat mehr als 50 Zeichen.<br/>";
            }

            if (strlen($comment) > 255) {
                $error .= "Der Kommentar für den Administrator hat mehr als 255 Zeichen.<br/>";
            }

            if ($error == "") {
                //Falls jemand die Zeichenfolge ">\/\>" zum trennen der Informationen in dem Formular verwendet hat...
                $title = str_replace(">\/\>", " ", $title);
                $message = str_replace(">\/\>", " ", $message);
                $author = str_replace(">\/\>", " ", $author);
                $comment = str_replace(">\/\>", " ", $comment);
                //Fügt die einzelnen Informationen zusammen in ein String
                $content = $title . ">\/\>" . date("d.m.Y, H:i") . ">\/\>" . $message . ">\/\>" . $author . ">\/\>" . $comment .  ">\/\>" . date("YmdHis");
                //Meldung speichern
                if (file_put_contents("../text/" . $fileName . ".txt", $content) == FALSE) {
                    $error = "News konnte nicht gepeichert werden.";
                }
            }

            if ($error == "") {
                $error = null;
            }

            return $error;
        }

        //Speichert das Bild
        //Bei einem Fehler wird statt null der Fehlertext zurückgegeben
        function savePicture($pictureZiel, $mode)
        {

            //--------------------ANGEPASSTER Code !ORIGINAL CODE VON HERRN LURATI!--------------------
            $error = null;
            
            // ---alle wichtigen Variablen aus dem superglobalen Array herauslesen ---------------
            $fileName = $_FILES['file']['name'];    // davon 'Name’
            $fileTmpName = $_FILES['file']['tmp_name']; // davon 'temporärer Name', damit getestet werden kann
            $fileGroesse = $_FILES['file']['size']; // davon 'Datei-Grösse’
            $fileFehler = $_FILES['file']['error']; // davon 'Fehler’


            //-------Vorbereitung der Datei-Endung
            $fileArt = explode('.', $fileName); // Art des Dateinamens nach dem Komma in Array extrahieren
            $fileActualExt = strtolower(end($fileArt)); // im letzten Array-Teil wird alles kleingeschrieben        

            //im Edit-Mode muss kein Bild erfasst werden
            if ($mode == "edit" and $_FILES['file']['name'] == "") {
                //kein neues Bild
                return;
            }

            //----------------Prüfen auf verschiedene Kriterien: ----------------------------------    
            if ($fileActualExt == "png") {                          // der File-Typ ist erlaubt
                if ($fileFehler === 0) {                            // Keine Fehlermeldung von PHP
                    if ($fileGroesse < 5000000) {                   // nur bis 500'000 Bytes Grösse
                        //----------------jetzt ist alles geprüft, es wird der UPLOAD vorbereitet: ------

                        
                        //----------------jetzt wird die Datei hochgeladen, wenn alle Kriterien erfüllt:
                        move_uploaded_file($fileTmpName, $pictureZiel); // jetzt wird die angegebene Datei verschoben
                        
                        //-------------------------------------------------------------------------------
                    } else {
                        $error =  "Datei ist zu gross";
                    }
                } else {
                    $error =  "Es gab ein Fehl beim Hochladen der Datei";
                }
            } else {
                $error = "Es sind nur PNG Dateien erlaubt.";
            }
            return $error;
        }
        ?>
    </div>
</body>

</html>