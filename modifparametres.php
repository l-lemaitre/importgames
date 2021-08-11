<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page modifparametres.php (ligne 23)
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
                // on charge la session de l'utilisateur retourné après l'authentification du cookie par la bdd,
                $_SESSION["user_id"] = htmlentities($user["id"]);
                $_SESSION["user"] = htmlspecialchars($user["username"]);
                $_SESSION["user_email"] = htmlspecialchars($user["email"]);
            }

            else {
                // sinon l'utilisateur est envoyé à la page connexion
                header("location:connexion");
                exit;
            }
        }

        else {
            // sinon on ne va pas sur cette page
            header("location:connexion");
            exit;
        }
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Modifier mes paramètres";
    }


    // On récupère les informations de l'utilisateur connecté
    $query = "SELECT * FROM `user` WHERE `id` = ?";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute([$_SESSION["user_id"]]);
    $user = $resultSet->fetch();


    // Si la variable POST "modifParam" est déclarée et différente de NULL
    if(isset($_POST["modifParam"])) {
        $username = htmlspecialchars(trim($_POST["username"])); // On récupère le nom d'utilisateur
        $email = htmlspecialchars(strtolower(trim($_POST["email"]))); // On récupère l'adresse e-mail
        $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
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

            if($usernVerif && $usernVerif["username"] <> $_SESSION["user"]) {
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

            if($mailVerif && $mailVerif["email"] <> $_SESSION["user_email"]) {
                $valid = false;
                $usedMail = true;
            }
        }

        // Si toutes les conditions sont remplies alors on passe au traitement
        if($valid) {
            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/modifparametres" OR $referer == "https://importgames.llemaitre.com/modifparam") { */
                        // On modifie le nom d'utilisateur et l'e-mail du client en mettant à jour nos données dans la table "user"
                        $query = "UPDATE `user` SET `username` = ?, `email` = ? WHERE `id` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$username, $email, $_SESSION["user_id"]]);

                        $_SESSION["user"] = $username;

                        // On récupère les informations de l'utilisateur connecté
                        $query = "SELECT * FROM `user` WHERE `email` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$email]);
                        $user = $resultSet->fetch();

                        // Si après update de la table "user" l'adresse e-mail de la session est différente de l'adresse e-mail actuelle de l'utilisateur,
                        if($_SESSION["user_email"] != $user["email"]) {
                            // on utilise les foncions bin2hex et random_bytes pour créer un jeton d'authentification de 12 caractères aléatoire en octets,
                            $token = bin2hex(random_bytes(12));

                            // on met à jour la colonne "token" dans la table "user"
                            $query = "UPDATE `user` SET `token` = ? WHERE `id` = ?";
                            $resultSet = $pdo->prepare($query);
                            $resultSet->execute([$token, $_SESSION["user_id"]]);

                            // Envoi de l'e-mail avec le lien permettant de valider le changement d'adresse e-mail du compte
                            $mailConf = $user["email"];

                            // Création du header de l'e-mail
                            $entete  = "MIME-Version: 1.0" . "\r\n";
                            $entete .= "Content-type: text/html; charset=utf-8" . "\r\n"; // On définit l'en-tête Content-type
                            $entete .= "From: no-reply@importgames.llemaitre.com" . "\r\n";

                            // Ajout du message au format HTML          
                            $message = "<!DOCTYPE html><html lang=\"fr\"><body>"
                                . nl2br("<a href=\"https://importgames.llemaitre.com\"><img src=\"https://importgames.llemaitre.com/images/divers/importgames.png\" alt=\"minLogo\" /></a>

                                <p>Bonjour " . htmlspecialchars($user["username"]) . ",

                                Veuillez valider votre changement d'adresse e-mail en cliquant sur ce lien : <a href=\"https://importgames.llemaitre.com/conf-email-" . htmlentities($user["id"]) . "-" . $token . "\">Valider</a>.
                                Si le lien ne s'affiche pas, copiez cette adresse dans votre navigateur : <a href=\"https://importgames.llemaitre.com/conf-email-" . htmlentities($user["id"]) . "-" . $token . "\">https://importgames.llemaitre.com/conf-email-" . htmlentities($user["id"]) . "-" . $token . "</a></p>

                                <p>Copyright 2020 <a style=\"color: #ff8b2b;\" href=\"#\">llemaitre.com</a>. Tous droits réservés</p>
                                </body></html>");

                            // Envoi de l'e-mail
                            mail($mailConf, "Validation de votre adresse e-mail ImportGames", $message, $entete);

                            // On se déconnecte de la session en cours
                            header("location:deconnexion");
                            exit;
                        }

                        // Sinon on retourne sur la page profil
                        else {
                            header("location:profil");
                            exit;
                        }
                    /* }

                    // La requête vient d'autre part donc on bloque
                    else {
                        $refError = true;
                    } */
                }

                else {
                    // Les tokens ne correspondent pas donc on ne modifie pas
                    $verifError = true;
                }
            }

            else {
                // Les tokens sont introuvables donc on ne modifie pas
                $verifError = true;
            }
        }
    }


    // Si la variable POST "modifMdp" est déclarée et différente de NULL
    if(isset($_POST["modifMdp"])) {
        $password = trim($_POST["password"]); // On récupère le mot de passe
        $newPass = trim($_POST["newPass"]); // On récupère le nouveau mot de passe
        $passConf = trim($_POST["passConf"]); // On récupère la confirmation du mot de passe
        $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
        $valid = true;

        // Vérification du mot de passe
        if(empty($password)) {
            $valid = false;
            $emptyPass = true;
        }

        // On vérifie si le mot de passe utilisé correspond bien au hash présent dans la bdd à l'aide de password_verify
        $correctPassword = password_verify($password, $user["password"]);

        if(!$correctPassword) {
            $passError = true;
        }

        // Vérification du nouveau mot de passe
        if(empty($newPass)) {
            $valid = false;
            $emptyNewPass = true;
        }

        // On vérifie si le mot de passe contient 8 caractères alphanumériques au minimum
        elseif(!preg_match("/^[0-9A-Za-z]{8,}$/", $newPass)) {
            $valid = false;
            $invalidNewPass = true;
        }

        // Vérification si le nouveau mot de passe correspond au champ "Confirmer le mot de passe"
        elseif($newPass != $passConf) {
            $valid = false;
            $invalidPassConf = true;
        }

        // Si toutes les conditions sont remplies alors on passe au traitement
        if($correctPassword AND $valid) {
            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/modifparametres" OR $referer == "https://importgames.llemaitre.com/modifparam") { */
                        // On utilise la fonction password_hash() pour haché notre nouveau mot de passe avec l'algorithme Argon2id
                        $hash = password_hash($newPass, PASSWORD_ARGON2I);

                        // On met à jour le mot de passe dans la table "user"
                        $query = "UPDATE `user` SET `password` = ? WHERE `id` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$hash, $_SESSION["user_id"]]);

                        header("location:profil-ok");
                        exit;
                    /* }

                    // La requête vient d'autre part donc on bloque
                    else {
                        $refPassError = true;
                    } */
                }

                else {
                    // Les tokens ne correspondent pas donc on ne modifie pas
                    $verifPassError = true;
                }
            }

            else {
                // Les tokens sont introuvables donc on ne modifie pas
                $verifPassError = true;
            }
        }
    }


    // Vérifie si la variable POST "formResetToken" est déclarée et différente de NULL
    if(isset($_POST["formResetToken"])) {
        $formToken = htmlspecialchars(trim($_POST["formResetToken"])); // On récupère le token de vérification (fichier modifparametres.phtml ligne 97)

        //On vérifie que les 2 jetons sont là
        if(!empty($_SESSION["token"]) AND !empty($formToken)) {
            // On vérifie que les deux correspondent
            if($_SESSION["token"] == $formToken) {
                // On enlève la vérification du Referer Header pour tester en localhost
                /* $referer = $_SERVER["HTTP_REFERER"];

                // On vérifie que la requête vient bien du formulaire
                if($referer == "https://importgames.llemaitre.com/modifparametres" OR $referer == "https://importgames.llemaitre.com/modifparam") { */
                    // On définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française
                    date_default_timezone_set("Europe/Paris");

                    // Au lieu de supprimer la ligne correspondant à l'utilisateur connecté avec la commande "DELETE" on efface les identifiants de connexion et on remet à zéro les paramètres d'activation et de récupération du compte client dans la table "user" afin d'éviter les "trous" dans l'incrémentation de la clé primaire "id"
                    $query = "UPDATE `user` SET `username` = ?, `email` = ?, `password` = ?, `token` = NULL, `token_stayco` = NULL, `new_pass` = NULL, `date_unsub` = ? WHERE `id` = ?";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute(["", "", "", date("Y-m-d H:i:s"), $_SESSION["user_id"]]);

                    // Voir fichier var.php ligne 40 et ajax.js ligne 32
                    $_SESSION["var"] = "resetAccount";
                /* }

                // La requête vient d'autre part donc on bloque
                else {
                    $refResetError = true;
                } */
            }

            else {
                // Les tokens ne correspondent pas donc on ne modifie pas
                $verifResetError = true;
            }
        }

        else {
            // Les tokens sont introuvables donc on ne modifie pas
            $verifResetError = true;
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page modifparametres.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans modifparametres.php
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