<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page produits.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier produits.php
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
        return "Liste des produits";
    }


    // Fonction pour afficher le titre de la catégorie dont l'id correspond à la valeur placée en argument (produits.phtml ligne 133)
    function getCatbyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `titre` FROM `categorie` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $cat = $resultSet->fetch();

        return $cat["titre"];
    }


    // Si la variable get page n'est pas déclarée ou ne contient pas au moins un chiffre,
    if(!isset($_GET["page"]) OR !preg_match("/^[0-9]+$/", $_GET["page"])) {
        // on retourne à la page 1
        header("location:/importgames/backoff/prods-page-1");
    }

    else {
        // On exécute un count() sur la table "produit" pour extraire son nombre total de lignes
        $query = "SELECT COUNT(*) FROM `produit`";
        $resultSet = $bdd->query($query);
        $nbrProds = $resultSet->fetch();


        // Si la variable post lignesProd est déclarée,
        if(isset($_POST["lignesProd"])) {
            // on affecte à l'index 6 du tableau de la variable de session lignes la valeur de la variable post lignesProd qui correspond à la valeur de l'envoi du nombre de lignes à afficher (voir fichiers varback.php ligne 72 et ajax.js ligne 31)
            $_SESSION["lignes"][6] = htmlspecialchars($_POST["lignesProd"]);
        }

        // ou bien si l'index 6 du tableau de la variable de session lignes est déclaré,
        elseif(isset($_SESSION["lignes"][6])) {
            // on lui affecte sa valeur actuelle
            $_SESSION["lignes"][6] = $_SESSION["lignes"][6];
        }

        // sinon l'index 6 du tableau de la variable de session lignes correspond à la valeur 5, 10 ou 25 selon le nombre de lignes à afficher
        else {
            if($nbrProds[0] <= 100) {
                $_SESSION["lignes"][6] = "5";
            }

            elseif($nbrProds[0] > 250) {
                $_SESSION["lignes"][6] = "25";
            }

            else {
                $_SESSION["lignes"][6] = "10";
            }
        }


        // La variable limite définit le nombre de lignes affichées par page
        $limite = $_SESSION["lignes"][6];

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page produits.php il faut ajouter au lien ?page=1
        $page = htmlspecialchars($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers varback.php ligne 73 et ajax.js ligne 35
        $_SESSION["page"] = $page;


        // La variable resultDebut retourne le numéro du résultat d'où commence la sélection de la page. On lui ajoute la valeur 1 pour correspondre avec l'Id du premier résultat affiché par tri ascendant (produits.phtml ligne 41)
        $resultDebut = (($page - 1) * $limite) + 1;

        // Si le numéro de page fois la selection de lignes est supérieur au nombre de produits,
        if(($page * $limite) > $nbrProds[0]) {
            // on affecte à la variable resultFin la valeur du nombre total de produits,
            $resultFin = $nbrProds[0];
        }

        else {
            // sinon on affiche la valeur de la page actuelle multiplié par celle de la limite des lignes à afficher
            $resultFin = $page * $limite;
        }


        // Calcul le nombre de pages
        $nbrPages = ceil($nbrProds[0] / $limite);

        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:/importgames/backoff/prods-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir produits.phtml lignes 47 et 303)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir produits.phtml lignes 44 et 300)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir produits.phtml lignes 49 et 305)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }


        // On affecte à l'index 1 du tableau de la variable de session selected la valeur "prod" pour identifier la page actuelle (voir fichiers varback.php ligne 82 et ajax.js ligne 128)
        $_SESSION["selected"][1] = "prod";


        // Si la clé "prod" de la variable de session choixTri n'est pas déclarée ou sa valeur égale à NULL,
        if(!isset($_SESSION["choixTri"]["prod"])) {
            // on affecte à la clé "prod" de la variable de session choixTri un tableau en attribuant à la clé "id" la valeur "triDesc" et aux clés suivantes la valeur spéciale null (voir ligne 357)
            $_SESSION["choixTri"]["prod"] = array("id" => "triDesc", "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            // on affecte à la clé "prod" de la variable de session idTriHidden la valeur "arrowUpId" (voir fichiers varback.php ligne 81 et ajax.js ligne 128)
            $_SESSION["idTriHidden"]["prod"] = "arrowUpId";
        }


        // Si la variable post triProdId est déclarée et sa valeur différente de NULL
        if(isset($_POST["triProdId"])) {
            // On réinitialise l'affichage en attribuant à la clé "id" du tableau affecté à la clé "prod" de la variable de session choixTri sa valeur actuelle et aux clés suivantes la valeur null
            $_SESSION["choixTri"]["prod"] = array("id" => $_SESSION["choixTri"]["prod"]["id"], "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            // Si la clé "id" du tableau affecté à la clé "prod" de la variable de session choixTri correspond à la valeur "triDesc",
            if($_SESSION["choixTri"]["prod"]["id"] == "triDesc") {
                // on lui affecte la valeur "triAsc",
                $_SESSION["choixTri"]["prod"]["id"] = "triAsc";

                // Voir fichiers varback.php ligne 81 et ajax.js ligne 128
                $_SESSION["idTriHidden"]["prod"] = "arrowDownId";
            }

            else {
                // sinon on lui affecte la valeur "triDesc"
                $_SESSION["choixTri"]["prod"]["id"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrowUpId";
            }
        }

        elseif(isset($_POST["triProdCat"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => $_SESSION["choixTri"]["prod"]["cat"], "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["cat"] == "triAsc") {
                $_SESSION["choixTri"]["prod"]["cat"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpCat";
            }

            else {
                $_SESSION["choixTri"]["prod"]["cat"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnCat";
            }
        }

        elseif(isset($_POST["triProdTitre"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => $_SESSION["choixTri"]["prod"]["titre"], "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["titre"] == "triAsc") {
                $_SESSION["choixTri"]["prod"]["titre"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpTitre";
            }

            else {
                $_SESSION["choixTri"]["prod"]["titre"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnTitre";
            }
        }

        elseif(isset($_POST["triProdEan13"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => $_SESSION["choixTri"]["prod"]["ean"], "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["ean"] == "triAsc") {
                $_SESSION["choixTri"]["prod"]["ean"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpEan";
            }

            else {
                $_SESSION["choixTri"]["prod"]["ean"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnEan";
            }
        }

        elseif(isset($_POST["triProdPrix"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => $_SESSION["choixTri"]["prod"]["prix"], "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["prix"] == "triDesc") {
                $_SESSION["choixTri"]["prod"]["prix"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnPrix";
            }

            else {
                $_SESSION["choixTri"]["prod"]["prix"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpPrix";
            }
        }

        elseif(isset($_POST["triProdQte"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => $_SESSION["choixTri"]["prod"]["qte"], "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["qte"] == "triDesc") {
                $_SESSION["choixTri"]["prod"]["qte"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnQte";
            }

            else {
                $_SESSION["choixTri"]["prod"]["qte"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpQte";
            }
        }

        elseif(isset($_POST["triProdDateSortie"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => $_SESSION["choixTri"]["prod"]["sortie"], "descr" => null, "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["sortie"] == "triDesc") {
                $_SESSION["choixTri"]["prod"]["sortie"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnSortie";
            }

            else {
                $_SESSION["choixTri"]["prod"]["sortie"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpSortie";
            }
        }

        elseif(isset($_POST["triProdDescript"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => $_SESSION["choixTri"]["prod"]["descr"], "img" => null, "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["descr"] == "triAsc") {
                $_SESSION["choixTri"]["prod"]["descr"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpDescr";
            }

            else {
                $_SESSION["choixTri"]["prod"]["descr"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnDescr";
            }
        }

        elseif(isset($_POST["triProdImg"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => $_SESSION["choixTri"]["prod"]["img"], "vid" => null, "crea" => null);

            if($_SESSION["choixTri"]["prod"]["img"] == "triAsc") {
                $_SESSION["choixTri"]["prod"]["img"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpImg";
            }

            else {
                $_SESSION["choixTri"]["prod"]["img"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnImg";
            }
        }

        elseif(isset($_POST["triProdVideo"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => $_SESSION["choixTri"]["prod"]["vid"], "crea" => null);

            if($_SESSION["choixTri"]["prod"]["vid"] == "triDesc") {
                $_SESSION["choixTri"]["prod"]["vid"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnVid";
            }

            else {
                $_SESSION["choixTri"]["prod"]["vid"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpVid";
            }
        }

        elseif(isset($_POST["triProdDateCrea"])) {
            $_SESSION["choixTri"]["prod"] = array("id" => null, "cat" => null, "titre" => null, "ean" => null, "prix" => null, "qte" => null, "sortie" => null, "descr" => null, "img" => null, "vid" => null, "crea" => $_SESSION["choixTri"]["prod"]["crea"]);

            if($_SESSION["choixTri"]["prod"]["crea"] == "triDesc") {
                $_SESSION["choixTri"]["prod"]["crea"] = "triAsc";

                $_SESSION["idTriHidden"]["prod"] = "arrDwnCrea";
            }

            else {
                $_SESSION["choixTri"]["prod"]["crea"] = "triDesc";

                $_SESSION["idTriHidden"]["prod"] = "arrUpCrea";
            }
        }


        // Requête de base pour afficher et trier les produits (produits.phtml lignes 128 et 238)
        $query = "SELECT * FROM `produit`";

        // Si la clé "id" du tableau affecté à la clé "prod" de la variable de session choixTri correspond à la valeur "triDesc",
        if($_SESSION["choixTri"]["prod"]["id"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `id` DESC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "produit" par tri descendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        // Ou bien si la clé "id" du tableau affecté à la clé "prod" de la variable de session choixTri correspond à la valeur "triAsc",
        elseif($_SESSION["choixTri"]["prod"]["id"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `id` ASC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "produit" par tri ascendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["cat"] == "triDesc") {
            $query .= " ORDER BY `cat_id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["cat"] == "triAsc") {
            $query .= " ORDER BY `cat_id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["titre"] == "triDesc") {
            $query .= " ORDER BY `titre` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["titre"] == "triAsc") {
            $query .= " ORDER BY `titre` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["ean"] == "triDesc") {
            $query .= " ORDER BY `ean13` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["ean"] == "triAsc") {
            $query .= " ORDER BY `ean13` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["prix"] == "triDesc") {
            $query .= " ORDER BY `prix` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["prix"] == "triAsc") {
            $query .= " ORDER BY `prix` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["qte"] == "triDesc") {
            $query .= " ORDER BY `qte` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["qte"] == "triAsc") {
            $query .= " ORDER BY `qte` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["sortie"] == "triDesc") {
            $query .= " ORDER BY `date_sortie` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["sortie"] == "triAsc") {
            $query .= " ORDER BY `date_sortie` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["descr"] == "triDesc") {
            $query .= " ORDER BY `description` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["descr"] == "triAsc") {
            $query .= " ORDER BY `description` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["img"] == "triDesc") {
            $query .= " ORDER BY `apercu_img` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["img"] == "triAsc") {
            $query .= " ORDER BY `apercu_img` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["vid"] == "triDesc") {
            $query .= " ORDER BY `video` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["vid"] == "triAsc") {
            $query .= " ORDER BY `video` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["crea"] == "triDesc") {
            $query .= " ORDER BY `date_creation` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["prod"]["crea"] == "triAsc") {
            $query .= " ORDER BY `date_creation` ASC LIMIT $debut, $limite";
        }

        $resultSet = $bdd->query($query);
        $prods = $resultSet->fetchAll();


        if($prods) {
            // On initialise la variable i avec la valeur 0
            $i = 0;

            foreach($prods as $prod) {
                // On stocke dans une variable de session la chaîne de caractères et la variable avec l'index id correspondants à l'attribut id de l'élément html button resetProd pour en récupérer la valeur dans le fichier varback.php (voir fichiers produits.phtml ligne 285, varback.php ligne 76 et ajax.js ligne 76)
                $_SESSION["loopElementId"][1][$i++] = "resetProd" . intval($prod["id"]);
            }

            $i = 0;

            foreach($prods as $prod) {
                // Voir fichiers varback.php ligne 76 et ajax.js ligne 97
                $_SESSION["loopElementId"][2][$i++] = "resetProdTop" . intval($prod["id"]);
            }

            // On stocke le message de confirmation dans une variable de session pour en récupérer la valeur dans le fichier varback.php (dossier backoff, fichiers varback.php ligne 77 et ajax.js ligne 80)
            $_SESSION["loopMsgConfirm"] = "Voulez-vous vraiment supprimer cette fiche produit ? Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";
        }


        // Si la variable post resetProd est déclarée et différente de NULL
        if(isset($_POST["resetProd"])) {
            $prodId = htmlspecialchars($_POST["resetProd"]);
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification (fichier produits.phtml lignes 180 ou 287)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/produits/produits?page=" . $page OR $referer == "https://importgames.llemaitre.com/backoff/prods-page-" . $page) { */
                        // On remet à zéro les colonnes contenant les informations du produit où l'Id correspond à celui de la variable prodId (fichier produits.phtml lignes 178 ou 285)
                        $query = "UPDATE `produit` SET `cat_id` = NULL, `titre` = NULL, `ean13` = NULL, `prix` = NULL, `qte` = NULL, `date_sortie` = NULL, `description` = NULL, `apercu_img` = NULL, `video` = NULL, `date_creation` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($prodId));

                        header("location:/importgames/backoff/prods-page-" . $page);
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page produits.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans produits.php
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