<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page motdepasse.php (ligne 23)
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
        return "Nouveau mot de passe";
    }


    // Si la variable POST "newPass" est déclarée et différente de NULL
    if(isset($_POST["newPass"])) {
        $email = htmlspecialchars(strtolower(trim($_POST["email"]))); // On récupère l'adresse e-mail 
        $valid = true;

        // Vérification de l'adresse e-mail
        if(empty($email)) {
            $valid = false;
            $emptyMail = true;
        }

        // On vérifie que l'adresse e-mail est dans le bon format (particulièrement important sur cette page pour se protéger contre la faille CRLF)
        elseif(!preg_match("/^[a-z0-9\-_.]+@[a-z]+\.[a-z]{2,3}$/i", $email)) {
            $valid = false;
            $invalidMail = true;
        }

        $query= "SELECT * FROM `user` WHERE `email` = ?";
        $resultSet= $pdo->prepare($query);
        $resultSet->execute([$email]);
        $user = $resultSet->fetch();

        // // Si le token d'activation n'est pas vide on ne remplace pas le mot de passe
        if(isset($user["token"])) {
            $valid = false;
            $tokenError = true;
        }

        if($valid) {
            if(isset($user["email"])) {
                // Si il n'y a pas de demande de changement de mot de passe en cours
                if(!$user["new_pass"]) {
                    // On génère un mot de passe à l'aide de la fonction RAND de PHP
                    $new_pass = rand();

                    // On utilise la fonction password_hash() pour haché notre nouveau mot de passe avec l'algorithme de hachage Argon2id
                    $hash = password_hash($new_pass, PASSWORD_ARGON2I);

                    $objet = "Changement du mot de passe de votre compte ImportGames";

                    // Création du header de l'e-mail
                    $entete  = "MIME-Version: 1.0" . "\r\n";
                    $entete .= "Content-type: text/html; charset=utf-8" . "\r\n"; // On définit l'en-tête Content-type
                    $entete .= "From: no-reply@importgames.llemaitre.com" . "\r\n";

                    // Contenu du message
                    $message =  "<!DOCTYPE html><html lang=\"fr\"><body>"
                        . nl2br("<a href=\"https://importgames.llemaitre.com\"><img src=\"https://importgames.llemaitre.com/images/divers/importgames.png\" alt=\"minLogo\" /></a>
                        
                        <p>Bonjour " . htmlspecialchars($user["username"]) . ",

                        Voici votre nouveau mot de passe : <b>" . htmlentities($new_pass) . "</b>

                        Vous pouvez le modifier à tout moment dans la partie <i>\"Mes informations\"</i> de votre espace personnel.</p>
                        
                        <p>Copyright 2020 <a style=\"color: #ff8b2b;\" href=\"#\">llemaitre.com</a>. Tous droits réservés</p>
                        </body></html>");

                    // Envoi de l'e-mail
                    $retour = mail($email, $objet, $message, $entete);

                    if($retour) {
                        // On met à jour le mot de passe et le paramètre qui permet de savoir si oui ou non on a fait une demande de changement du mdp
                        $query = "UPDATE `user` SET `password` = ?, `new_pass` = 1 WHERE `email` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$hash, $user["email"]]);
                        
                        header("location:pass-ok");
                    }

                    else {
                        header("location:pass-error");
                    }
                }

                // Ou alors si il y a une demande de changement de mot de passe en cours,
                else {
                    // on affiche un message dans le fichier motdepasse.phtml ligne 30
                    $notActivated = true;
                }
            }

            else {
                $noMail = true;
            }
        }
    }


    // Si la variable GET "reply" est déclarée et différente de NULL
    if(isset($_GET["reply"])) {
        // On récupère et sécurise son contenu
        $reply = htmlspecialchars($_GET["reply"]);

        if($reply == "ok") {
            $replyOk = true; // Voir motdepasse.phtml ligne 33
        }

        elseif($reply == "error") {
            $replyError = true; // Voir motdepasse.phtml ligne 37
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page motdepasse.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans motdepasse.php
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