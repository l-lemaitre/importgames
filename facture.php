<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page facture.php (ligne 23)
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


    if(isset($_GET["id"])) {
        // On récupère et sécurise le contenu de la variable GET "id"
        $comId = htmlentities($_GET["id"]);

        // Requête SQL : Sélectionne et va chercher la valeur contenue dans la table "commande" de la base de données pour l'utilisateur connecté où l'id correspond à celui affiché dans le header
        $query = "SELECT * FROM `commande` WHERE (`user_id` = ? AND `id` = ?)";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$_SESSION["user_id"], $comId]);
        $com = $resultSet->fetch();

        if(isset($com["id"])) {
            // Sélectionne et va chercher la valeur contenue dans la table "user" de la base de données où l'id correspond à l'Id utilisateur dans la table "commande"
            $query = "SELECT * FROM `user` WHERE `id` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$com["user_id"]]);
            $user = $resultSet->fetch();


            // Sélectionne et va chercher toutes les valeurs contenues dans la table "detail_com" de la base de données où "commande_id" correspond à l'Id de la commande de l'utilisateur
            $query = "SELECT * FROM `detail_com` WHERE `commande_id` = ? ORDER BY `produit_id`";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$com["id"]]);
            $details = $resultSet->fetchAll();


            // On récupère le montant total de la facture pour l'afficher en Français (fichier facture.phtml ligne 88)
            $num = htmlentities($com["total"]);
            $exp = explode(".", $num);
            $nbrForm = new NumberFormatter("fr_FR", NumberFormatter::SPELLOUT);
            $totalFr = ucfirst($nbrForm->format($exp[0])) . " euros et " . ucfirst($nbrForm->format($exp[1])) . " centimes.";
        }
    }


    // Inclut et exécute le fichier facture.phtml qui hérite de la portée des variables présentes dans facture.php
    if(empty($fct)) {
        $fct = "facture";

        // On limite l'inclusion aux fichiers .phtml en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $fct = trim($fct . ".phtml");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $fct = str_replace("../", "protect", $fct);
    $fct = str_replace(";", "protect", $fct);
    $fct = str_replace("%", "protect", $fct);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $fct)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le "serveur"
        if(file_exists($fct) && $fct != "index.phtml") {
           include $fct;
        }

        else {
            echo "Page inexistante.";
        }
    }
?>