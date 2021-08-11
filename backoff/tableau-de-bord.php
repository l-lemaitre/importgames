<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page tableau-de-bord.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier tableau-de-bord.php
        if(file_exists(__DIR__ . "/application/" . $bdd)) {
           include __DIR__ . "/application/" . $bdd;
        }

        else {
            echo "Page inexistante.";
        }
    }


    // Si aucun administrateur n'est connecté alors on ne va pas sur cette page
    if(!isset($_SESSION["admin_id"])) {
        // L'utilisateur est envoyé à la page index/connexion
        header("location:/importgames/backoff/index");
        exit;
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (dossier backoff, fichier layout.phtml ligne 10)
    function headTitle() {
        return "Tableau de bord";
    }


    // On exécute un count() sur la table `user` pour extraire le nombre total de lignes où la valeur de la colonne `username` n'est pas égale à une chaîne vide (tableau-de-bord.phtml ligne 9)
    $query = "SELECT COUNT(*) FROM `user` WHERE `username` <> ?";
    $resultSet = $bdd->query($query, array(""));
    $nbrClients = $resultSet->fetch();


    // Nombre total de produits du site où la valeur de la colonne `cat_id` de la table `produit` n'est pas NULL (tableau-de-bord.phtml ligne 18)
    $query = "SELECT COUNT(*) FROM `produit` WHERE `cat_id` IS NOT NULL";
    $resultSet = $bdd->query($query);
    $nbrProds = $resultSet->fetch();


    // On exécute un sum() pour calculer la somme totale de la colonne "qte" de la table "achat" où la valeur de la colonne `produit_id` n'est pas NULL (tableau-de-bord.phtml ligne 27)
    $query = "SELECT SUM(`qte`) FROM `achat` WHERE `produit_id` IS NOT NULL";
    $resultSet = $bdd->query($query);
    $nbrAchats = $resultSet->fetch();


    // Nombre total des ventes sur le site (tableau-de-bord.phtml ligne 36)
    $query = "SELECT SUM(`qte`) FROM `detail_com` WHERE `commande_id` IS NOT NULL";
    $resultSet = $bdd->query($query);
    $nbrVentes = $resultSet->fetch();


    // Nombre total de visites sur la page produit du site (tableau-de-bord.phtml ligne 45)
    $query = "SELECT COUNT(*) FROM `visite_prod`";
    $resultSet = $bdd->query($query);
    $nbrVisitesProd = $resultSet->fetch();


    // Nnombre total de recherches sur le site (tableau-de-bord.phtml ligne 54)
    $query = "SELECT COUNT(*) FROM `recherche` WHERE `texte` IS NOT NULL";
    $resultSet = $bdd->query($query);
    $nbrSearchs = $resultSet->fetch();


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page tableau-de-bord.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans tableau-de-bord.php
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
        // On vérifie que la page est bien sur le "serveur" en utlisant la constante SITE_DIR du fichier dir.php inclut dans le fichier bdd_connection.php (dossier backoff, fichiers dir.php et bdd_connection.php ligne 43)
        if(file_exists(SITE_DIR . "/" . $layout) && $layout != "index.phtml") {
           include SITE_DIR . "/" . $layout;
        }

        else {
            echo "Page inexistante.";
        }
    }
?>