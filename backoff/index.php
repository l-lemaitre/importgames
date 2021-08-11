<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page index.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier index.php
        if(file_exists(__DIR__ . "/application/" . $bdd)) {
           include __DIR__ . "/application/" . $bdd;
        }

        else {
            echo "Page inexistante.";
        }
    }


    // Si un administrateur est connecté alors on ne retourne plus sur cette page
    if(isset($_SESSION["admin_id"])) {
        header("location:/importgames/backoff/tableau-de-bord");
        exit;
    }


    // Si la variable COOKIE "us_i" n'est pas vide (dossier backoff, fichier deconnexion.php ligne 7),
    if(!empty($_COOKIE["us_i"])) {
        // on restaure la session de l'utilisateur avant session_destroy,
        $_SESSION["user_id"] = $_COOKIE["us_i"];
        $_SESSION["user"] = $_COOKIE["us"];
        $_SESSION["user_email"] = $_COOKIE["eml"];

        // On supprime les cookies,
        setcookie("us_i");
        setcookie("us");
        setcookie("eml");
        // on supprime leur valeur présente dans le tableau $_COOKIE
        unset($_COOKIE["us_i"]);
        unset($_COOKIE["us"]);
        unset($_COOKIE["eml"]);
    }


    // Si la variable post login est déclarée et différente de NULL
    if(isset($_POST["login"])) {
        $name  = htmlspecialchars(trim($_POST["name"])); // On récupère le contenu du champ de saisie "name"
        $password = trim($_POST["password"]); // On récupère le mot de passe
        $valid = true;

        // Vérification du contenu de "name"
        if(empty($name)){
            $valid = false;
            $emptyName = true;
        }

        // Vérification du mot de passe
        if(empty($password)) {
            $valid = false;
            $emptyPass = true;
        }

        // On demande le hash pour cet utilisateur à notre base de données
        $query = "SELECT `password` FROM `admin` WHERE `name` = ?";
        $resultSet = $bdd->query($query, array($name));
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
            if(file_exists(SITE_DIR . "/../plugin/recaptcha/" . $captcha)) {
               require_once SITE_DIR . "/../plugin/recaptcha/" . $captcha;
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
                // On fait une requête pour savoir si le nom de l'admin existe car il est unique
                $query = "SELECT * FROM `admin` WHERE `name` = ?";
                $resultSet = $bdd->query($query, array($name));
                $admin = $resultSet->fetch();

                // S'il y a un résultat alors on charge la session de l'admin en utilisant les variables de session
                if($valid) {
                    $_SESSION["admin_id"] = htmlspecialchars($admin["id"]);
                    $_SESSION["admin"] = htmlspecialchars($admin["name"]);
                    $_SESSION["prv"] = htmlspecialchars($admin["prv"]);

                    // Envoie à la page d'accueil
                    header("location:/importgames/backoff/tableau-de-bord");
                }
            }

            // Ou si nous n'avons pas de résultat après la vérification avec password_verify() c'est qu'il n'y a pas d'utilisateur correspondant au couple nom d'utilisateur ou e-mail + mot de passe (index.phtml ligne 51)
            else {
                $loginError = true;
            }
        // }
    }


    // Inclut et exécute le fichier index.phtml qui hérite de la portée des variables présentes dans index.php
    if(empty($index)) {
        $index = "index";

        // On limite l'inclusion aux fichiers .phtml en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $index = trim($index . ".phtml");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $index = str_replace("../", "protect", $index);
    $index = str_replace(";", "protect", $index);
    $index = str_replace("%", "protect", $index);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $index)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le "serveur" en utlisant la constante SITE_DIR du fichier dir.php inclut dans le fichier bdd_connection.php (dossier backoff, fichiers dir.php et bdd_connection.php ligne 43)
        if(file_exists(SITE_DIR . "/" . $index)) {
           include SITE_DIR . "/" . $index;
        }

        else {
            echo "Page inexistante.";
        }
    }
?>