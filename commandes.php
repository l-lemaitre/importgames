<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page commandes.php (ligne 23)
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
        return "Mes commandes";
    }


    // Si la variable GET "page" n'est pas déclarée ou ne contient pas un ou plusieurs chiffres,
    if(!isset($_GET["page"]) OR !preg_match("/^[0-9]+$/", $_GET["page"])) {
        // on retourne à la page 1
        header("location:commandes-page-1");
    }

    else {
        // On exécute un count() sur la table `commande` pour extraire le nombre total de lignes de l'utilisateur connecté
        $query = "SELECT count(*) FROM `commande` WHERE `user_id` IN (SELECT `id` FROM `user` WHERE `date_reg` < commande.date_co) AND `user_id` = ?";
        $resultSet = $pdo->prepare($query);
        $resultSet ->execute([$_SESSION["user_id"]]);
        $nbrComsUser = $resultSet->fetch();


        // Si le nombre de commandes de l'utilisateur connecté est égal à 1
        if($nbrComsUser[0] == 1) {
            // Voir fichier var.php ligne 46 et ajax.js ligne 130
            $_SESSION["var6"] = "singleOrder";
        }


        // Si la variable POST "affichageLignes" est déclarée,
        if(isset($_POST["affichageLignes"])) {
            // on affecte à la variable de SESSION "var2" la valeur de $_POST["affichageLignes"] pour en récupérer la valeur dans le fichier var.php (voir fichiers var.php ligne 42 et ajax.js ligne 84)
            $_SESSION["var2"] = htmlentities($_POST["affichageLignes"]);

            // La variable selection correspond à la valeur de l'envoi du nombre de lignes à afficher (fichier commandes.phtml ligne 21)
            $selection = $_SESSION["var2"];
        }

        // ou bien si la variable de SESSION "var2" est déclarée,
        elseif(isset($_SESSION["var2"])) {
            $selection = $_SESSION["var2"];
        }

        // sinon la variable selection correspond à la valeur 5
        else {
            $selection = "5";

            $_SESSION["var2"] = $selection;
        }


        // La variable limite définit le nombre de lignes affichées par page
        $limite = $selection;

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page commandes.php il faut ajouter au lien ?page=1
        $page = htmlentities($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers var.php ligne 40 et ajax.js ligne 57
        $_SESSION["var"] = $page;


        // Si la variable de SESSION "var3" n'est pas déclarée,
        if(!isset($_SESSION["var3"])) {
            // on lui affecte la valeur "triDesc" (voir fichier var.php ligne 43 et ajax.js ligne 90)
            $_SESSION["var3"] = "triDesc";
            // et aux variables de SESSION "var4" et "var5" la valeur "noSort" (voir fichiers var.php lignes 44 et 45 et ajax.js lignes 106 et 118)
            $_SESSION["var4"] = "noSort";
            $_SESSION["var5"] = "noSort";
        }


        // Si la variable POST "triNum" est déclarée
        if(isset($_POST["triNum"])) {
            // On affecte aux variables de SESSION "var4" et "var5" la valeur "noSort"
            $_SESSION["var4"] = "noSort";
            $_SESSION["var5"] = "noSort";

            // Si la variable de SESSION "var3" est égale à la valeur "triDesc",
            if($_SESSION["var3"] == "triDesc") {
                // on affecte à la variable de SESSION "var3" la valeur "triAsc",
                $_SESSION["var3"] = "triAsc";
            }

            else {
                // sinon on affecte à la variable de SESSION "var3" la valeur "triDesc"
                $_SESSION["var3"] = "triDesc";
            }
        }

        elseif(isset($_POST["triDate"])) {
            $_SESSION["var3"] = "noSort";
            $_SESSION["var5"] = "noSort";

            if($_SESSION["var4"] == "triDesc") {
                $_SESSION["var4"] = "triAsc";
            }

            else {
                $_SESSION["var4"] = "triDesc";
            }
        }

        elseif(isset($_POST["triTotal"])) {
            $_SESSION["var3"] = "noSort";
            $_SESSION["var4"] = "noSort";

            if($_SESSION["var5"] == "triDesc") {
                $_SESSION["var5"] = "triAsc";
            }

            else {
                $_SESSION["var5"] = "triDesc";
            }
        }


        // Requête de base pour afficher et trier les commandes de l'utilisateur connecté (commandes.phtml ligne 54)
        $query = "SELECT `id`, `user_id`, `numero`, `total`, DATE_FORMAT(`date_co`, \"%d/%m/%Y\") AS `date_co` FROM `commande` WHERE `user_id` IN (SELECT `id` FROM `user` WHERE `date_reg` < commande.date_co) AND `user_id` = :sessionUserId";

        // Si la variable de SESSION "var3" est égale à la valeur "triDesc",
        if($_SESSION["var3"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `numero` DESC LIMIT :debut, :limite". On sélectionne les valeurs contenues dans la table "commande" par tri descendant de la colonne "numero" pour l'utilisateur connecté et on les affichent dans la limite définie par les variables "debut" et "limite"
            $query .= " ORDER BY `numero` DESC LIMIT :debut, :limite";
        }

        // Ou bien si la variable de SESSION "var3" est égale à la valeur "triAsc",
        elseif($_SESSION["var3"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `numero` ASC LIMIT :debut, :limite". On sélectionne les valeurs contenues dans la table "commande" par tri ascendant de la colonne "numero" pour l'utilisateur connecté et on les affichent dans la limite définie par les variables "debut" et "limite"
            $query .= " ORDER BY `numero` ASC LIMIT :debut, :limite";
        }

        elseif($_SESSION["var4"] == "triDesc") {
            $query .= " ORDER BY `id` DESC LIMIT :debut, :limite";
        }

        elseif($_SESSION["var4"] == "triAsc") {
            $query .= " ORDER BY `id` ASC LIMIT :debut, :limite";
        }

        elseif($_SESSION["var5"] == "triDesc") {
            $query .= " ORDER BY `total` DESC LIMIT :debut, :limite";
        }

        elseif($_SESSION["var5"] == "triAsc") {
            $query .= " ORDER BY `total` ASC LIMIT :debut, :limite";
        }

        $resultSet = $pdo->prepare($query);
        $resultSet->bindParam(":sessionUserId", $_SESSION["user_id"], PDO::PARAM_INT);
        $resultSet->bindParam(":debut", $debut, PDO::PARAM_INT);
        $resultSet->bindParam(":limite", $limite, PDO::PARAM_INT);
        $resultSet->execute();
        $coms = $resultSet->fetchAll();


        // Calcul le nombre de pages
        $nbrPages = ceil($nbrComsUser[0] / $limite);

        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:commandes-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir commandes.phtml ligne 74)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir commandes.phtml ligne 68)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir commandes.phtml ligne 79)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page commandes.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans commandes.php
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