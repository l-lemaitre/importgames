<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page connexion.php (ligne 23)
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
        return "Connexion";
    }


    // Si la variable POST "login" est déclarée et différente de NULL
    if(isset($_POST["login"])) {
        $mailUsername  = htmlspecialchars(trim($_POST["mailUsername"])); // On récupère le contenu du champ de saisie "mailUsername"
        $password = trim($_POST["password"]); // On récupère le mot de passe
        $valid = true;

        //  Vérification du contenu de "mailUsername"
        if(empty($mailUsername)){
            $valid = false;
            $emptyMailName = true;
        }

        // Vérification du mot de passe
        if(empty($password)) {
            $valid = false;
            $emptyPass = true;
        }

        // On demande le hash pour cet utilisateur à notre base de données
        $query= "SELECT `password` FROM `user` WHERE (`username` = ? OR `email` = ?)";
        $resultSet= $pdo->prepare($query);
        $resultSet->execute([$mailUsername, $mailUsername]);
        $hash = $resultSet->fetch();

        if($hash) {
            // Nous vérifions si le mot de passe utilisé correspond bien à ce hash à l'aide de password_verify
            $correctPassword = password_verify($password, $hash["password"]);
        }

        else {
            $correctPassword = false;
        }

        // On désactive le service reCAPTCHA de google pour travailler en localhost
        /* if(empty($captcha)) {
            $captcha = "recaptchalib";

            // On limite l'inclusion aux fichiers .php en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
            $captcha = trim($captcha . ".php");
        }

        // On évite les caractères qui permettent de naviguer dans les répertoires
        $captcha = str_replace("../", "protect", $captcha);
        $captcha = str_replace(";", "protect", $captcha);
        $captcha = str_replace("%", "protect", $captcha);

        // On interdit l'inclusion de dossiers protégés par htaccess
        if(preg_match("/backoff/", $captcha)) {
            echo "Vous n'avez pas accès à ce répertoire.";
         }

        else {
            // On vérifie que la page est bien sur le "serveur"
            if(file_exists("plugin/recaptcha/" . $captcha)) {
               require_once "plugin/recaptcha/" . $captcha;
            }

            else {
                echo "Page inexistante.";
            }
        }

        $secret = "6LfGCe8UAAAAAIszOXMgPEqxzHCv9Xxn6fXxdhDT";
        $response = false;
        $reCaptcha = new ReCaptcha($secret);

        if($_POST["g-recaptcha-response"]) {
            $response = $reCaptcha->verifyResponse(
                $_SERVER["REMOTE_ADDR"],
                $_POST["g-recaptcha-response"]
            );
        }
        
        // Si la variable response est égale à FALSE
        if(!$response) {
            $noCaptcha = true;
        }

        if($response && $response->success) { */
            if($correctPassword) {
                // On fait une requête pour savoir si le nom d'utilisateur ou l'e-mail existent bien car ils sont uniques
                $query= "SELECT * FROM `user` WHERE (`username` = ? OR `email` = ?)";
                $resultSet= $pdo->prepare($query);
                $resultSet->execute([$mailUsername, $mailUsername]);
                $user = $resultSet->fetch();

                // Si le token n'est pas vide alors on ne l'autorise pas à accéder au site
                if($user["token"]) {
                    $valid = false;
                    $tokenError = true;
                }

                // S'il y a un couple nom d'utilisateur ou e-mail + mot de passe et une demande de changement de mot de passe en cours, on remet à zéro la demande
                if($user["new_pass"] == 1) {
                    $query = "UPDATE `user` SET `new_pass` = 0 WHERE `id` = ?";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute([$user["id"]]);
                }

                // S'il y a un résultat alors on charge la session de l'utilisateur en utilisant les variables de SESSION
                if($valid) {
                    $_SESSION["user_id"] = htmlentities($user["id"]);
                    $_SESSION["user"] = htmlspecialchars($user["username"]);
                    $_SESSION["user_email"] = htmlspecialchars($user["email"]);

                    // Si l'utilisateur a coché la case "Rester connecté" on crée un cookie contenant un token qu'on enregistre dans la base de données
                    if(isset($_POST["resterCo"])) {
                        // On sécurise le contenu de la variable POST "resterCo" pour se protéger contre les injections de code HTML ou JavaScript
                        $resterCo = htmlspecialchars($_POST["resterCo"]);

                        // On utilise les fonctions bin2hex et random_bytes pour créer un jeton d'authentification de 12 caractères aléatoire en octets
                        $tokenStayco = bin2hex(random_bytes(12));

                        // Le cookie expire dans 365 jours et le paramètre httponly vaut TRUE
                        setcookie("stayCo", $tokenStayco, time()+60*60*24*365, FALSE, FALSE, FALSE, TRUE);

                        // On met à jour la colonne "token_stayco" dans la table "user"
                        $query = "UPDATE `user` SET `token_stayco` = ? WHERE `id` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$tokenStayco, $_SESSION["user_id"]]);
                    }

                    // Envoie à la page d'accueil
                    header("location:index");
                }
            }

            // Ou si nous n'avons pas de résultat après la vérification avec password_verify() c'est qu'il n'y a pas d'utilisateur correspondant au couple nom d'utilisateur ou e-mail + mot de passe (connexion.phtml ligne 43)
            else {
                $loginError = true;
            }
        // }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page connexion.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans connexion.php
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