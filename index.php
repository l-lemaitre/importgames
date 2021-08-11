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


    // Si la variable COOKIE "ad_i" n'est pas vide (voir fichier deconnexion.php ligne 7),
    if(!empty($_COOKIE["ad_i"])) {
        // on restaure la session de l'administrateur avant session_destroy,
        $_SESSION["admin_id"] = $_COOKIE["ad_i"];
        $_SESSION["admin"] = $_COOKIE["ad"];
        $_SESSION["prv"] = $_COOKIE["pv"];

        // On supprime les cookies,
        setcookie("ad_i");
        setcookie("ad");
        setcookie("pv");
        // on supprime leur valeur présente dans le tableau $_COOKIE
        unset($_COOKIE["ad_i"]);
        unset($_COOKIE["ad"]);
        unset($_COOKIE["pv"]);
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Votre solution pour l'import Japonais";
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans index.phtml ligne 32
    function couperTitre($contenu) {
        $length = 29; // On veut les 29 premiers caractères

        if(strlen($contenu) >= $length) { // Si la longueur de $contenu est plus grande ou égal à $length,
          $titreCoupe = substr($contenu, 0, $length) . "..."; // alors on garde $contenu à partir du début (0) jusqu'à $length (29) et tout ce qui vient ensuite est remplacé par "..."

          return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu; // Affiche le texte en entier si il contient moins de 29 caractères
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans index.phtml ligne 33
    function couperTitre600($contenu) {
        $length = 17; // On veut les 17 premiers caractères pour les appareils mobiles

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans index.phtml ligne 34
    function couperTitre300($contenu) {
        $length = 14; // On veut les 14 premiers caractères pour les smartphones

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Requêtes SQL : Sélectionne et va chercher toutes les valeurs contenues dans la table "slider" de la base de données où la valeur de la colonne "titre" n'est pas NULL
    $query = "SELECT * FROM `slider` WHERE `titre` IS NOT NULL";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute();
    $slids = $resultSet->fetchAll();


    // On sélectionne toutes les valeurs contenues dans la table "produit" où la valeur de la colonne "cat_id" n'est pas NULL pour les afficher par tri descendant de la colonne "id" dans la limite de 12 résultats maximum
    $query = "SELECT * FROM `produit` WHERE `cat_id` IS NOT NULL ORDER BY `id` DESC LIMIT 12";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute();
    $prods = $resultSet->fetchAll();


    $query = "SELECT * FROM `partenaire` WHERE `nom` IS NOT NULL";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute();
    $partners = $resultSet->fetchAll();


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page index.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans index.php
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
        // On vérifie que la page est bien sur le "serveur" en utlisant la constante SITE_DIR du fichier dir.php inclut dans le fichier bdd_connection.php (fichiers dir.php et bdd_connection.php ligne 43)
        if(file_exists(SITE_DIR . "/" . $layout) && $layout != "index.phtml") {
           include SITE_DIR . "/" . $layout;
        }

        else {
            echo "Page inexistante.";
        }
    }
?>