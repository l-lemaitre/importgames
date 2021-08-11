<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page inscription.php (ligne 23)
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

                // et il est envoyé à la page d'accueil
                header("location:index");
                exit;
            }
        }
    }

    // sinon on ne retourne plus sur cette page
    else {
        header("location:index");
        exit;
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Inscription";
    }


    // Si la variable POST "creationCte" est déclarée et différente de NULL
    if(isset($_POST["creationCte"])) {
        $username = htmlspecialchars(trim($_POST["username"])); // On récupère le nom d'utilisateur
        $email = htmlspecialchars(strtolower(trim($_POST["email"]))); // On récupère l'adresse e-mail
        $password = trim($_POST["password"]); // On récupère le mot de passe 
        $passConf = trim($_POST["passConf"]); // On récupère la confirmation du mot de passe
        $valid = true;

        // Vérification du nom d'utilisateur
        if(empty($username)) {
            $valid = false;
            $emptyUsern = true;
        }

        // On vérifie que le nom d'utilisateur est dans le bon format
        elseif(!preg_match("/^[0-9A-Za-zàäâçéèëêïîöôùüû_-]{3,16}$/", $username)) {
            $valid = false;
            $invalidUsern = true;
        }

        else {
            // On vérifie que le nom d'utilisateur est disponible
            $query = "SELECT `username` FROM `user` WHERE `username` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$username]);
            $usernVerif = $resultSet->fetch();

            if(isset($usernVerif["username"])) {
                $valid = false;
                $usedUsern = true;
            }
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

        else {
            // On vérifie que l'e-mail est disponible
            $query = "SELECT `email` FROM `user` WHERE `email` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$email]);
            $mailVerif = $resultSet->fetch();

            if(isset($mailVerif["email"])) {
                $valid = false;
                $usedMail = true;
            }
        }

        // Vérification du mot de passe
        if(empty($password)) {
            $valid = false;
            $emptyPass = true;
        }

        // On vérifie si le mot de passe contient 8 caractères alphanumériques au minimum
        elseif(!preg_match("/^[0-9A-Za-z]{8,}$/", $password)) {
            $valid = false;
            $invalidPass = true;
        }

        // Vérification si le mot de passe correspond au champ "Confirmer le mot de passe"
        elseif($password != $passConf) {
            $valid = false;
            $invalidPassConf = true;
        }

        // Si toutes les conditions sont remplies alors on crée le compte client
        if($valid) {
            // On utilise la fonction password_hash() pour haché notre mot de passe avec l'algorithme de hachage Argon2id
            $hash = password_hash($password, PASSWORD_ARGON2I);

            // On utilise les fonctions bin2hex et random_bytes pour créer un jeton d'authentification de 12 caractères aléatoire en octets
            $token = bin2hex(random_bytes(12));

            // On insert nos données dans la table "user"
            $query = "INSERT INTO `user` (`username`, `email`, `password`, `date_reg`, `token`) VALUES (?, ?, ?, NOW(), ?)";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$username, $email, $hash, $token]);

            // Envoi de l'e-mail qui contiendra le lien permettant de valider le compte
            $query = "SELECT * FROM `user` WHERE `email` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$email]);
            $user = $resultSet->fetch();

            $mailConf = $user["email"];

            // Création du header de l'e-mail
            $entete  = "MIME-Version: 1.0" . "\r\n";
            $entete .= "Content-type: text/html; charset=utf-8" . "\r\n"; // On définit l'en-tête Content-type
            $entete .= "From: no-reply@importgames.llemaitre.com" . "\r\n";

            // Ajout du message au format HTML          
            $message = "<!DOCTYPE html><html lang=\"fr\"><body>"
                . nl2br("<a href=\"https://importgames.llemaitre.com\"><img src=\"https://importgames.llemaitre.com/images/divers/importgames.png\" alt=\"minLogo\" /></a>

                <p>Bonjour " . htmlspecialchars($user["username"]) . ",

                Veuillez activer votre compte en cliquant sur ce lien : <a href=\"https://importgames.llemaitre.com/conf-cte-" . htmlentities($user["id"]) . "-" . $token . "\">Valider</a>.
                Si le lien ne s'affiche pas, copiez cette adresse dans votre navigateur : <a href=\"https://importgames.llemaitre.com/conf-cte-" . htmlentities($user["id"]) . "-" . $token . "\">https://importgames.llemaitre.com/conf-cte-" . htmlentities($user["id"]) . "-" . $token . "</a>

                Si vous n’avez pas demandé à recevoir cet e-mail, vous pouvez simplement l’ignorer.</p>

                <p>Copyright 2020 <a style=\"color: #ff8b2b;\" href=\"#\">llemaitre.com</a>. Tous droits réservés</p>
                </body></html>");

            // Envoi de l'e-mail
            $retour = mail($mailConf, "Activation de votre compte ImportGames", $message, $entete);

            if($retour) {
                header("location:inscription-ok");
            }

            else {
                header("location:inscription-error");
            }
        }
    }


    // Si la variable GET "reply" est déclarée et différente de NULL
    if(isset($_GET["reply"])) {
        // On récupère et sécurise son contenu
        $reply = htmlspecialchars($_GET["reply"]);

        if($reply == "ok") {
            $okReply = true; // Voir fichier inscription.phtml ligne 60
        }

        elseif($reply == "error") {
            $errorReply = true; // Voir inscription.phtml ligne 64
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page inscription.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans inscription.php
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