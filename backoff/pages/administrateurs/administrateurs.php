<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page administrateurs.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier administrateurs.php
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
        return "Liste des administrateurs";
    }


    // Fonction pour masquer le texte trop long du mot de passe hacher et le remplacer par "..." dans administrateurs.phtml ligne 117
    function couperTexte($contenu) {
        $length = 12; // On veut les 12 premiers caractères

        if(strlen($contenu) >= $length) {
            $texteCoupe = substr($contenu, 0, $length) . "...";
            return $texteCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Si la variable get page n'est pas déclarée ou ne contient pas au moins un chiffre,
    if(!isset($_GET["page"]) OR !preg_match("/^[0-9]+$/", $_GET["page"])) {
        // on retourne à la page 1
        header("location:/importgames/backoff/admins-page-1");
    }

    else {
        // On exécute un count() sur la table "admin" pour extraire son nombre total de lignes
        $query = "SELECT COUNT(*) FROM `admin`";
        $resultSet = $bdd->query($query);
        $nbrAdmins = $resultSet->fetch();


        // Si la variable post lignesAdmin est déclarée,
        if(isset($_POST["lignesAdmin"])) {
            // on affecte à l'index 1 du tableau de la variable de session lignes la valeur de la variable post lignesAdmin qui correspond à la valeur de l'envoi du nombre de lignes à afficher (voir fichiers varback.php ligne 72 et ajax.js ligne 31)
            $_SESSION["lignes"][1] = htmlspecialchars($_POST["lignesAdmin"]);
        }

        // ou bien si l'index 1 du tableau de la variable de session lignes est déclaré,
        elseif(isset($_SESSION["lignes"][1])) {
            // on lui affecte sa valeur actuelle
            $_SESSION["lignes"][1] = $_SESSION["lignes"][1];
        }

        // sinon l'index 1 du tableau de la variable de session lignes correspond à la valeur 5, 10 ou 25 selon le nombre de lignes à afficher
        else {
            if($nbrAdmins[0] <= 100) {
                $_SESSION["lignes"][1] = "5";
            }

            elseif($nbrAdmins[0] > 250) {
                $_SESSION["lignes"][1] = "25";
            }

            else {
                $_SESSION["lignes"][1] = "10";
            }
        }


        // La variable limite définit le nombre de lignes affichées par page
        $limite = $_SESSION["lignes"][1];

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page administrateurs.php il faut ajouter au lien ?page=1
        $page = htmlspecialchars($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers varback.php ligne 73 et ajax.js ligne 35
        $_SESSION["page"] = $page;


        // La variable resultDebut retourne le numéro du résultat d'où commence la sélection de la page. On lui ajoute la valeur 1 pour correspondre avec l'Id du premier résultat affiché par tri ascendant (administrateurs.phtml ligne 42)
        $resultDebut = (($page - 1) * $limite) + 1;

        // Si le numéro de page fois la selection de lignes est supérieur au nombre d'administrateurs,
        if(($page * $limite) > $nbrAdmins[0]) {
            // on affecte à la variable resultFin la valeur du nombre total des administrateurs,
            $resultFin = $nbrAdmins[0];
        }

        else {
            // sinon on affiche la valeur de la page actuelle multiplié par celle de la limite des lignes à afficher
            $resultFin = $page * $limite;
        }


        // Calcul le nombre de pages
        $nbrPages = ceil($nbrAdmins[0] / $limite);

        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:/importgames/backoff/admins-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir administrateurs.phtml lignes 48 et 153)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir administrateurs.phtml lignes 45 et 150)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir administrateurs.phtml lignes 50 et 155)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }


        // On affecte à l'index 1 du tableau de la variable de session selected la valeur "admin" pour identifier la page actuelle (voir fichiers varback.php ligne 82 et ajax.js ligne 128)
        $_SESSION["selected"][1] = "admin";


        // Si la clé "admin" de la variable de session choixTri n'est pas déclarée ou sa valeur égale à NULL,
        if(!isset($_SESSION["choixTri"]["admin"])) {
            // on affecte à la clé "admin" de la variable de session choixTri un tableau en attribuant à la clé "id" la valeur "triAsc" et aux clés suivantes la valeur spéciale null (voir ligne 270)
            $_SESSION["choixTri"]["admin"] = array("id" => "triAsc", "name" => null, "pass" => null, "prv" => null, "reg" => null);

            // on affecte à la clé "admin" de la variable de session idTriHidden la valeur "arrowDownId" (voir fichiers varback.php ligne 81 et ajax.js ligne 128)
            $_SESSION["idTriHidden"]["admin"] = "arrowDownId";
        }


        // Si la variable post triAdminId est déclarée et sa valeur différente de NULL
        if(isset($_POST["triAdminId"])) {
            // On réinitialise l'affichage en attribuant à la clé "id" du tableau affecté à la clé "admin" de la variable de session choixTri sa valeur actuelle et aux clés suivantes la valeur null
            $_SESSION["choixTri"]["admin"] = array("id" => $_SESSION["choixTri"]["admin"]["id"], "name" => null, "pass" => null, "prv" => null, "reg" => null);

            // Si la clé "id" du tableau affecté à la clé "admin" de la variable de session choixTri correspond à la valeur "triAsc",
            if($_SESSION["choixTri"]["admin"]["id"] == "triAsc") {
                // on lui affecte la valeur "triDesc",
                $_SESSION["choixTri"]["admin"]["id"] = "triDesc";

                // Voir fichiers varback.php ligne 81 et ajax.js ligne 128
                $_SESSION["idTriHidden"]["admin"] = "arrowUpId";
            }

            else {
                // sinon on lui affecte la valeur "triAsc"
                $_SESSION["choixTri"]["admin"]["id"] = "triAsc";

                $_SESSION["idTriHidden"]["admin"] = "arrowDownId";
            }
        }

        elseif(isset($_POST["triAdminName"])) {
            $_SESSION["choixTri"]["admin"] = array("id" => null, "name" => $_SESSION["choixTri"]["admin"]["name"], "pass" => null, "prv" => null, "reg" => null);

            if($_SESSION["choixTri"]["admin"]["name"] == "triAsc") {
                $_SESSION["choixTri"]["admin"]["name"] = "triDesc";

                $_SESSION["idTriHidden"]["admin"] = "arrUpName";
            }

            else {
                $_SESSION["choixTri"]["admin"]["name"] = "triAsc";

                $_SESSION["idTriHidden"]["admin"] = "arrDwnName";
            }
        }

        elseif(isset($_POST["triAdminPass"])) {
            $_SESSION["choixTri"]["admin"] = array("id" => null, "name" => null, "pass" => $_SESSION["choixTri"]["admin"]["pass"], "prv" => null, "reg" => null);

            if($_SESSION["choixTri"]["admin"]["pass"] == "triAsc") {
                $_SESSION["choixTri"]["admin"]["pass"] = "triDesc";

                $_SESSION["idTriHidden"]["admin"] = "arrUpPass";
            }

            else {
                $_SESSION["choixTri"]["admin"]["pass"] = "triAsc";

                $_SESSION["idTriHidden"]["admin"] = "arrDwnPass";
            }
        }

        elseif(isset($_POST["triAdminPrv"])) {
            $_SESSION["choixTri"]["admin"] = array("id" => null, "name" => null, "pass" => null, "prv" => $_SESSION["choixTri"]["admin"]["prv"], "reg" => null);

            if($_SESSION["choixTri"]["admin"]["prv"] == "triDesc") {
                $_SESSION["choixTri"]["admin"]["prv"] = "triAsc";

                $_SESSION["idTriHidden"]["admin"] = "arrDwnPrv";
            }

            else {
                $_SESSION["choixTri"]["admin"]["prv"] = "triDesc";

                $_SESSION["idTriHidden"]["admin"] = "arrUpPrv";
            }
        }

        elseif(isset($_POST["triAdminDateReg"])) {
            $_SESSION["choixTri"]["admin"] = array("id" => null, "name" => null, "pass" => null, "prv" => null, "reg" => $_SESSION["choixTri"]["admin"]["reg"]);

            if($_SESSION["choixTri"]["admin"]["reg"] == "triDesc") {
                $_SESSION["choixTri"]["admin"]["reg"] = "triAsc";

                $_SESSION["idTriHidden"]["admin"] = "arrDwnReg";
            }

            else {
                $_SESSION["choixTri"]["admin"]["reg"] = "triDesc";

                $_SESSION["idTriHidden"]["admin"] = "arrUpReg";
            }
        }


        // Requête de base pour afficher et trier les admins (administrateurs.phtml ligne 108)
        $query = "SELECT * FROM `admin`";

        // Si la clé "id" du tableau affecté à la clé "admin" de la variable de session choixTri correspond à la valeur "triDesc",
        if($_SESSION["choixTri"]["admin"]["id"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `id` DESC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "admin" par tri descendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        // Ou bien si la clé "id" du tableau affecté à la clé "admin" de la variable de session choixTri correspond à la valeur "triAsc",
        elseif($_SESSION["choixTri"]["admin"]["id"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `id` ASC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "admin" par tri ascendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["name"] == "triDesc") {
            $query .= " ORDER BY `name` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["name"] == "triAsc") {
            $query .= " ORDER BY `name` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["pass"] == "triDesc") {
            $query .= " ORDER BY `password` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["pass"] == "triAsc") {
            $query .= " ORDER BY `password` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["prv"] == "triDesc") {
            $query .= " ORDER BY `prv` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["prv"] == "triAsc") {
            $query .= " ORDER BY `prv` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["reg"] == "triDesc") {
            $query .= " ORDER BY `date_reg` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["admin"]["reg"] == "triAsc") {
            $query .= " ORDER BY `date_reg` ASC LIMIT $debut, $limite";
        }

        $resultSet = $bdd->query($query);
        $admins = $resultSet->fetchAll();


        if($admins) {
            // On initialise la variable i avec la valeur 0
            $i = 0;

            foreach($admins as $admin) {
                // On stocke dans une variable de session la chaîne de caractères et la variable avec l'index id correspondants à l'attribut id de l'élément html button resetAdmin pour en récupérer la valeur dans le fichier varback.php (voir fichiers administrateurs.phtml ligne 136, varback.php ligne 76 et ajax.js ligne 76)
                $_SESSION["loopElementId"][1][$i++] = "resetAdmin" . intval($admin["id"]);
            }

            // On stocke le message de confirmation dans une variable de session pour en récupérer la valeur dans le fichier varback.php (dossier backoff, fichiers varback.php ligne 77 et ajax.js ligne 80)
            $_SESSION["loopMsgConfirm"] = "Voulez-vous vraiment supprimer cet administrateur ? Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";
        }


        // Si la variable post resetAdmin est déclarée et différente de NULL
        if(isset($_POST["resetAdmin"])) {
            $adminId = htmlspecialchars($_POST["resetAdmin"]);
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification (fichier administrateurs.phtml ligne 138)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/administrateurs/administrateurs?page=" . $page OR $referer == "https://importgames.llemaitre.com/backoff/admins-page-" . $page) { */
                        // On remet à zéro les colonnes contenant les informations de l'administrateur
                        $query = "UPDATE `admin` SET `name` = NULL, `password` = NULL, `prv` = NULL, `date_reg` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($adminId));

                        header("location:/importgames/backoff/admins-page-" . $page);
                        exit;
                    /* }

                    else {
                        // La requête vient d'autre part donc on bloque (voir fichier varback.php ligne 79 et ajax.js ligne 118)
                        $_SESSION["refReset"] = "refResetError";
                    } */
                }

                else {
                    // Les tokens ne correspondent pas donc on ne modifie pas
                    $_SESSION["verifReset"] = "verifResetError";
                }
            }

            else {
                // Les tokens sont introuvables donc on ne modifie pas (voir fichier varback.php ligne 78 et ajax.js ligne 113)
                $_SESSION["verifReset"] = "verifResetError";
            }
        }
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page administrateurs.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans administrateurs.php
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