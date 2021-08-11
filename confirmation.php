<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page confirmation.php (ligne 23)
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
        return "Confirmation compte";
    }


    if(isset($_GET["id"]) && isset($_GET["token"])) {
        $valid = true;
        // On récupère et sécurise le contenu des variables GET "id" et "token"
        $id = (int) htmlentities($_GET["id"]);
        $token = (String) htmlentities($_GET["token"]);
    }

    else {
        $valid = false;
        $linkError = true;
    }


    if($valid) {
        $query = "SELECT * FROM `user` WHERE `id` = ? AND `token` = ?";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$id, $token]);
        $user = $resultSet->fetch();

        if(!isset($user["id"])) {
            $linkError = true;
        }

        // On valide le compte de l'utilisateur,
        else {
            // on définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française,
            date_default_timezone_set("Europe/Paris");

            // on met à jour nos données dans la table "user",
            $query = "UPDATE `user` SET `token` = NULL, `conf_account` = ? WHERE `id` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([date("Y-m-d H:i:s"), $user["id"]]);

            // on affiche le message de bienvenue avec le nom de l'utilisateur (confirmation.phtml ligne 17)
            $confCompte = true;
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page confirmation.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans confirmation.php
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