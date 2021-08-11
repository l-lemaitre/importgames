<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page support.php (ligne 23)
    if(empty($bdd)) {
        $bdd = "bdd_connection";

        // On limite l'inclusion aux fichiers .php en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $bdd = trim($bdd . ".php");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $bdd = str_replace("../", "protect", $bdd);
    $bdd = str_replace(";", "protect", $bdd);
    $bdd = str_replace("%", "protect", $bdd);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $bdd)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le "serveur"
        if(file_exists("application/" . $bdd)) {
           include "application/" . $bdd;
        }

        else {
            echo "Page inexistante.";
        }
    }


    // Si aucun utilisateur n'est connecté,
    if(!isset($_SESSION["user_id"])) {
        // si le cookie "stayCo" n'est pas vide,
        if(!empty($_COOKIE["stayCo"])) {
            $query = "SELECT * FROM `user` WHERE `token_stayco` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$_COOKIE["stayCo"]]);
            $user = $resultSet->fetch();

            // si le token contenu dans le cookie correspond à une valeur de la colonnne "token_stayco" enregistrée dans la base de données,
            if(isset($user["token_stayco"])) {
                // on charge la session de l'utilisateur retourné après l'authentification du cookie par la bdd
                $_SESSION["user_id"] = htmlentities($user["id"]);
                $_SESSION["user"] = htmlspecialchars($user["username"]);
                $_SESSION["user_email"] = htmlspecialchars($user["email"]);
            }
        }
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Contactez-nous";
    }


    // Si la variable POST "message" est déclarée et différente de NULL
    if(isset($_POST["message"])) {
        $name  = htmlspecialchars(trim($_POST["name"])); // On récupère le nom
        $email = htmlspecialchars(strtolower(trim($_POST["email"]))); // On récupère l'email
        $sujet = strip_tags(trim($_POST["sujet"])); // On récupère le sujet du message
        $textMsg = strip_tags(trim($_POST["textMsg"])); //  On récupère le contenu du message
        $valid = true;

        // Vérification du nom
        if(empty($name)) {
            $valid = false;
            $emptyName = true;
        }

        // On vérifie que le nom est dans le bon format
        elseif(!preg_match("/^[0-9A-Za-zàäâçéèëêïîöôùüû\s_-]{3,}$/", $name)) {
            $valid = false;
            $invalidName = true;
        }

        // Vérification de l'adresse e-mail
        if(empty($email)) {
            $valid = false;
            $emptyMail = true;
        }

        // On vérifie que l'adresse e-mail est dans le bon format
        elseif(!preg_match("/^[0-9a-z\-_.]+@[0-9a-z]+\.[a-z]{2,3}$/i", $email)) {
            $valid = false;
            $invalidMail = true;
        }

        // Vérification du sujet du message
        if(empty($sujet)) {
            $valid = false;
            $emptySujet = true;
        }

        // Vérification du contenu du message
        if(empty($textMsg)) {
            $valid = false;
            $emptyMsg = true;
        }

        // Si toutes les conditions sont remplies alors on passe au traitement
        if($valid) {
            // On définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française
            date_default_timezone_set("Europe/Paris");

            // On encode le sujet du message en MIME base64 pour afficher correctement les accents et les caractères spéciaux de l'objet du courriel dans la messagerie
            $subject = "=?UTF-8?B?" . base64_encode($sujet) . "?=";

            // Création du header de l'e-mail
            $entete  = "MIME-Version: 1.0" . "\r\n";
            $entete .= "Content-type: text/html; charset=utf-8" . "\r\n"; // On définit l'en-tête Content-type
            $entete .= "From: " . $email . "\r\n";

            // Ajout du message au format HTML
            $message = "<!DOCTYPE html><html lang=\"fr\"><body>
                <h2 style=\"color: #ff8b2b;\">Message envoyé depuis la page \"Support\" du site <a href=\"https://importgames.llemaitre.com\" style=\"color: #ff8b2b; text-decoration:none;\">ImportGames</a></h2>"
                . nl2br("<p><b>Nom : </b>" . $name . "
                <b>E-mail : </b>" . $email . "
                <b>Date : </b>" . date("d-m-Y H:i:s") . "
                <b>Sujet : </b>" . $sujet . "
                <b>Message : </b>" . $textMsg . "</p>
                </body></html>");

            // On envoie à l'adresse "contact@llemaitre.com" l'objet de l'e-mail ainsi que les informations contenues dans "$message" et "$entete"
            $retour = mail("contact@llemaitre.com", $subject, $message, $entete);

            if($retour) {
                header("location:support-ok");
            }

            else{
                header("location:support-error");
            }
        }
    }


    // Si la variable GET "reply" est déclarée et différente de NULL
    if(isset($_GET["reply"])) {
        // On récupère et sécurise son contenu
        $reply = htmlspecialchars($_GET["reply"]);

        if($reply == "ok") {
            $replyOk = true; // Voir fichier support.phtml ligne 44
        }

        elseif($reply == "error") {
            $replyError = true; // Voir support.phtml ligne 48
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page support.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans support.php
    if(empty($layout)) {
        $layout = "layout";

        // On limite l'inclusion aux fichiers .phtml en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $layout = trim($layout . ".phtml");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $layout = str_replace("../", "protect", $layout);
    $layout = str_replace(";", "protect", $layout);
    $layout = str_replace("%", "protect", $layout);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $layout)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le "serveur"
        if(file_exists($layout) && $layout != "index.phtml") {
           include $layout;
        }

        else {
            echo "Page inexistante.";
        }
    }
?>