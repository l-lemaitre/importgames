<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page detailcommande.php (ligne 23)
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
        return "Détail commande";
    }


    if(isset($_GET["id"])) {
        // On récupère et sécurise le contenu de la variable GET "id"
        $comId = htmlentities($_GET["id"]);

        // Requête SQL : Sélectionne et va chercher la valeur contenue dans la table "commande" de la base de données pour l'utilisateur connecté où l'Id correspond à celui affiché dans le header
        $query = "SELECT * FROM `commande` WHERE (`user_id` = ? AND `id` = ?)";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$_SESSION["user_id"], $comId]);
        $com = $resultSet->fetch();

        if(isset($com["id"])) {
            // Sélectionne et va chercher toutes les valeurs contenues dans la table "detail_com" de la base de données où "commande_id" correspond à l'Id de la commande de l'utilisateur
            $query = "SELECT * FROM `detail_com` WHERE `commande_id` = ? ORDER BY `produit_id`";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$com["id"]]);

            // On initialise 4 tableaux pour la table "detail_com"
            $prodId = array();
            $titreCo = array();
            $qteCo = array();
            $prixCo = array();

            // On effectue une boucle à l'aide d'un while qui va stocker les données issues de la base dans les tableaux pour afficher le détail des produits de la commande
            while($detail = $resultSet->fetch()) {
                $prodId[] = $detail["produit_id"];
                $titreCo[] = $detail["titre"];
                $qteCo[] = $detail["qte"];
                $prixCo[] = $detail["prix"];
            }

            // On va se servir des tableaux pour afficher le nombre de lignes de la commande (fichier detailcommande.phtml ligne 39)
            $nbrProdsCo = $resultSet->rowCount();

            // Sélectionne et va chercher la valeur contenue dans la table "produit" de la base de données où l'Id correspond à "produit_id" de la table "detail_com"
            $query = "SELECT * FROM `produit` WHERE `id` IN (" . implode(',', array_map([$pdo, "quote"], $prodId)) . ")";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute();

            // On initialise 5 tableaux pour la table "produit"
            $titre = array();
            $prix = array();
            $qte = array();
            $apercuImg = array();
            $dateCrea = array();

            while($prod = $resultSet->fetch()) {
                $titre[] = $prod["titre"];
                $prix[] = $prod["prix"];
                $qte[] = $prod["qte"];
                $apercuImg[] = $prod["apercu_img"];
                $dateCrea[] = $prod["date_creation"];
            }
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page detailcommande.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans detailcommande.php
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