<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page visitesproduit.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier visitesproduit.php
        if(file_exists(__DIR__ . "/../../application/" . $bdd)) {
           include __DIR__ . "/../../application/" . $bdd;
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
        return "Liste des visites page produit";
    }


    // Fonction pour récupérer la date de création de la fiche produit où l'Id correspond à la valeur placée en argument (visitesproduit.phtml ligne 100)
    function getDatebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `date_creation` FROM `produit` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $prod = $resultSet->fetch();

        return $prod["date_creation"];
    }


    // Fonction pour afficher le titre du produit dont l'Id correspond à la valeur placée en argument (visitesproduit.phtml ligne 105)
    function getTitrebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `titre` FROM `produit` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $prod = $resultSet->fetch();

        return $prod["titre"];
    }


    // Fonction pour afficher la date d'inscription de l'utilisateur où l'Id correspond à la valeur placée en argument (visitesproduit.phtml ligne 110)
    function getDateUser($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `date_reg` FROM `user` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $user = $resultSet->fetch();

        return $user["date_reg"];
    }


    // Fonction pour afficher le nom d'utilisateur dont l'Id correspond à la valeur placée en argument (visitesproduit.phtml ligne 114)
    function getNamebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `username` FROM `user` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $user = $resultSet->fetch();

        return $user["username"];
    }


    // Si la variable get page n'est pas déclarée ou ne contient pas au moins un chiffre,
    if(!isset($_GET["page"]) OR !preg_match("/^[0-9]+$/", $_GET["page"])) {
        // on retourne à la page 1
        header("location:/importgames/backoff/visitesprod-page-1");
    }

    else {
        // On exécute un count() sur la table "visite_prod" pour extraire son nombre total de lignes
        $query = "SELECT COUNT(*) FROM `visite_prod`";
        $resultSet = $bdd->query($query);
        $nbrVisits = $resultSet->fetch();


        // Si le nombre total de lignes est supérieur à 500 on efface les 100 premières lignes de la table "visite_prod"
        if($nbrVisits[0] > 500) {
            $query = "DELETE FROM `visite_prod` ORDER BY `id` ASC LIMIT 100";
            $bdd->insert($query);

            header("location:/importgames/backoff/visitesprod-page-1");
            exit;
        }


        // Si la variable post lignesVisit est déclarée,
        if(isset($_POST["lignesVisit"])) {
            // on affecte à l'index 7 du tableau de la variable de session lignes la valeur de la variable post lignesVisit qui correspond à la valeur de l'envoi du nombre de lignes à afficher (voir fichiers varback.php ligne 72 et ajax.js ligne 31)
            $_SESSION["lignes"][7] = htmlspecialchars($_POST["lignesVisit"]);
        }

        // ou bien si l'index 7 du tableau de la variable de session lignes est déclaré,
        elseif(isset($_SESSION["lignes"][7])) {
            // on lui affecte sa valeur actuelle
            $_SESSION["lignes"][7] = $_SESSION["lignes"][7];
        }

        // sinon l'index 7 du tableau de la variable de session lignes correspond à la valeur 5, 10 ou 25 selon le nombre de lignes à afficher
        else {
            if($nbrVisits[0] <= 100) {
                $_SESSION["lignes"][7] = "5";
            }

            elseif($nbrVisits[0] > 250) {
                $_SESSION["lignes"][7] = "25";
            }

            else {
                $_SESSION["lignes"][7] = "10";
            }
        }


        // La variable limite définit le nombre de lignes affichées par page
        $limite = $_SESSION["lignes"][7];

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page visitesproduit.php il faut ajouter au lien ?page=1
        $page = htmlspecialchars($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers varback.php ligne 73 et ajax.js ligne 35
        $_SESSION["page"] = $page;


        // La variable resultDebut retourne le numéro du résultat d'où commence la sélection de la page. On lui ajoute la valeur 1 pour correspondre avec l'Id du premier résultat affiché par tri ascendant (visitesproduit.phtml ligne 39)
        $resultDebut = (($page - 1) * $limite) + 1;

        // Si le numéro de page fois la selection de lignes est supérieur au nombre de visites page produit,
        if(($page * $limite) > $nbrVisits[0]) {
            // on affecte à la variable resultFin la valeur du nombre total de visites page produit,
            $resultFin = $nbrVisits[0];
        }

        else {
            // sinon on affiche la valeur de la page actuelle multiplié par celle de la limite des lignes à afficher
            $resultFin = $page * $limite;
        }


        // Calcul le nombre de pages
        $nbrPages = ceil($nbrVisits[0] / $limite);

        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:/importgames/backoff/visitesprod-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir visitesproduit.phtml lignes 45 et 131)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir visitesproduit.phtml lignes 42 et 128)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir visitesproduit.phtml lignes 47 et 133)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }


        // On affecte à l'index 1 du tableau de la variable de session selected la valeur "visit" pour identifier la page actuelle (voir fichiers varback.php ligne 82 et ajax.js ligne 128)
        $_SESSION["selected"][1] = "visit";


        // Si la clé "visit" de la variable de session choixTri n'est pas déclarée ou sa valeur égale à NULL,
        if(!isset($_SESSION["choixTri"]["visit"])) {
            // on affecte à la clé "visit" de la variable de session choixTri un tableau en attribuant à la clé "id" la valeur "triDesc" et aux clés suivantes la valeur spéciale null (voir ligne 291)
            $_SESSION["choixTri"]["visit"] = array("id" => "triDesc", "prod" => null, "user" => null, "date" => null);

            // on affecte à la clé "visit" de la variable de session idTriHidden la valeur "arrowUpId" (voir fichiers varback.php ligne 81 et ajax.js ligne 128)
            $_SESSION["idTriHidden"]["visit"] = "arrowUpId";
        }


        // Si la variable post triVisitId est déclarée et sa valeur différente de NULL
        if(isset($_POST["triVisitId"])) {
            // On réinitialise l'affichage en attribuant à la clé "id" du tableau affecté à la clé "visit" de la variable de session choixTri sa valeur actuelle et aux clés suivantes la valeur null
            $_SESSION["choixTri"]["visit"] = array("id" => $_SESSION["choixTri"]["visit"]["id"], "prod" => null, "user" => null, "date" => null);

            // Si la clé "id" du tableau affecté à la clé "visit" de la variable de session choixTri correspond à la valeur "triDesc",
            if($_SESSION["choixTri"]["visit"]["id"] == "triDesc") {
                // on lui affecte la valeur "triAsc",
                $_SESSION["choixTri"]["visit"]["id"] = "triAsc";

                // Voir fichiers varback.php ligne 81 et ajax.js ligne 128
                $_SESSION["idTriHidden"]["visit"] = "arrowDownId";
            }

            else {
                // sinon on lui affecte la valeur "triDesc"
                $_SESSION["choixTri"]["visit"]["id"] = "triDesc";

                $_SESSION["idTriHidden"]["visit"] = "arrowUpId";
            }
        }

        elseif(isset($_POST["triVisitProd"])) {
            $_SESSION["choixTri"]["visit"] = array("id" => null, "prod" => $_SESSION["choixTri"]["visit"]["prod"], "user" => null, "date" => null);

            if($_SESSION["choixTri"]["visit"]["prod"] == "triDesc") {
                $_SESSION["choixTri"]["visit"]["prod"] = "triAsc";

                $_SESSION["idTriHidden"]["visit"] = "arrDwnProd";
            }

            else {
                $_SESSION["choixTri"]["visit"]["prod"] = "triDesc";

                $_SESSION["idTriHidden"]["visit"] = "arrUpProd";
            }
        }

        elseif(isset($_POST["triVisitUser"])) {
            $_SESSION["choixTri"]["visit"] = array("id" => null, "prod" => null, "user" => $_SESSION["choixTri"]["visit"]["user"], "date" => null);

            if($_SESSION["choixTri"]["visit"]["user"] == "triAsc") {
                $_SESSION["choixTri"]["visit"]["user"] = "triDesc";

                $_SESSION["idTriHidden"]["visit"] = "arrUpUser";
            }

            else {
                $_SESSION["choixTri"]["visit"]["user"] = "triAsc";

                $_SESSION["idTriHidden"]["visit"] = "arrDwnUser";
            }
        }

        elseif(isset($_POST["triVisitDate"])) {
            $_SESSION["choixTri"]["visit"] = array("id" => null, "prod" => null, "user" => null, "date" => $_SESSION["choixTri"]["visit"]["date"]);

            if($_SESSION["choixTri"]["visit"]["date"] == "triDesc") {
                $_SESSION["choixTri"]["visit"]["date"] = "triAsc";

                $_SESSION["idTriHidden"]["visit"] = "arrDwnDate";
            }

            else {
                $_SESSION["choixTri"]["visit"]["date"] = "triDesc";

                $_SESSION["idTriHidden"]["visit"] = "arrUpDate";
            }
        }


        // Requête de base pour afficher et trier les visites page produit (visitesproduit.phtml ligne 95)
        $query = "SELECT * FROM `visite_prod`";

        // Si la clé "id" du tableau affecté à la clé "visit" de la variable de session choixTri correspond à la valeur "triDesc",
        if($_SESSION["choixTri"]["visit"]["id"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `id` DESC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "visite_prod" par tri descendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        // Ou bien si la clé "id" du tableau affecté à la clé "visit" de la variable de session choixTri correspond à la valeur "triAsc",
        elseif($_SESSION["choixTri"]["visit"]["id"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `id` ASC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "visite_prod" par tri ascendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["visit"]["prod"] == "triDesc") {
            $query .= " ORDER BY `produit_id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["visit"]["prod"] == "triAsc") {
            $query .= " ORDER BY `produit_id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["visit"]["user"] == "triDesc") {
            $query .= " ORDER BY `user_id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["visit"]["user"] == "triAsc") {
            $query .= " ORDER BY `user_id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["visit"]["date"] == "triDesc") {
            $query .= " ORDER BY `date_v` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["visit"]["date"] == "triAsc") {
            $query .= " ORDER BY `date_v` ASC LIMIT $debut, $limite";
        }

        $resultSet = $bdd->query($query);
        $visits = $resultSet->fetchAll();
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page visitesproduit.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans visitesproduit.php
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