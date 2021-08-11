<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page achats-magasin.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier achats-magasin.php
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
        return "Liste des achats magasin";
    }


    // Fonction pour récupérer la date de création de la fiche produit où l'Id correspond à la valeur placée en argument (achats-magasin.phtml ligne 139)
    function getDatebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `date_creation` FROM `produit` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $prod = $resultSet->fetch();

        return $prod["date_creation"];
    }


    // Fonction pour récupérer le titre du produit où l'Id correspond à la valeur placée en argument (achats-magasin.phtml ligne 139)
    function getTitrebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `titre` FROM `produit` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $prod = $resultSet->fetch();

        return $prod["titre"];
    }


    // Si la variable get page n'est pas déclarée ou ne contient pas au moins un chiffre,
    if(!isset($_GET["page"]) OR !preg_match("/^[0-9]+$/", $_GET["page"])) {
        // on retourne à la page 1
        header("location:/importgames/backoff/achatsmag-page-1");
    }

    else {
        // On sélectionne toutes les valeurs contenues dans la table "produit" où la valeur de la colonne "cat_id" n'est pas NULL pour afficher l'ensemble des résultats par tri ascendant de la colonne "titre" (achats-magasin.phtml ligne 24)
        $query = "SELECT * FROM `produit` WHERE `cat_id` IS NOT NULL ORDER BY `titre`";
        $resultSet = $bdd->query($query);
        $prods = $resultSet->fetchAll();


        // On exécute un count() sur la table "achat" pour extraire le nombre total de lignes où la valeur de la colonne "produit_id" n'est pas NULL
        $query = "SELECT COUNT(*) FROM `achat` WHERE `produit_id` IS NOT NULL";
        $resultSet = $bdd->query($query);
        $nbrAchats = $resultSet->fetch();


        // Si la variable post affichageProd est déclarée,
        if(isset($_POST["affichageProd"])) {
            // on affecte à l'index achat du tableau de la variable de session affichageProd la valeur de la variable post affichageProd pour afficher la description correspondant à la valeur de l'option de l'élément htlml select (voir fichiers achats-magasin.phtml ligne 21, varback.php ligne 83 et ajax.js ligne 143)
            $_SESSION["affichageProd"]["achat"] = htmlspecialchars($_POST["affichageProd"]);
        }

        // ou bien si la variable post lignesAchat est déclarée,
        elseif(isset($_POST["lignesAchat"])) {
            // on affecte à l'index 8 du tableau de la variable de session lignes la valeur de la variable post lignesAchat qui correspond à la valeur de l'envoi du nombre de lignes à afficher (voir fichiers varback.php ligne 72 et ajax.js ligne 31)
            $_SESSION["lignes"][8] = htmlspecialchars($_POST["lignesAchat"]);
        }

        // ou bien si l'index 8 du tableau de la variable de session lignes est déclaré (pour afficher le nombre de lignes sélectionnées précédemment quand on revient sur la page),
        elseif(isset($_SESSION["lignes"][8])) {
            // on lui affecte sa valeur actuelle
            $_SESSION["lignes"][8] = $_SESSION["lignes"][8];
        }

        // sinon l'index 8 du tableau de la variable de session lignes correspond à la valeur 5, 10 ou 25 selon le nombre total de produits achetés
        else {
            if($nbrAchats[0] <= 100) {
                $_SESSION["lignes"][8] = "5";
            }

            elseif($nbrAchats[0] > 250) {
                $_SESSION["lignes"][8] = "25";
            }

            else {
                $_SESSION["lignes"][8] = "10";
            }
        }


        // La variable limite définit le nombre de lignes affichées par page
        $limite = $_SESSION["lignes"][8];

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page achats-magasin.php il faut ajouter au lien ?page=1
        $page = htmlspecialchars($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers varback.php ligne 73 et ajax.js ligne 35
        $_SESSION["page"] = $page;


        // La variable resultDebut retourne le numéro du résultat d'où commence la sélection de la page. On lui ajoute la valeur 1 pour commencer le compte à partir de 1 et non 0 (achats-magasin.phtml ligne 50 ou 52)
        $resultDebut = (($page - 1) * $limite) + 1;


        // Si l'index achat du tableau de la variable de session affichageProd n'est pas déclaré ou égal à la valeur "defaut"
        if(!isset($_SESSION["affichageProd"]["achat"]) || $_SESSION["affichageProd"]["achat"] == "defaut") {
            // Si le numéro de page fois la selection de lignes est supérieur au nombre d'achats,
            if(($page * $limite) > $nbrAchats[0]) {
                // on affecte à la variable resultFin la valeur du nombre de produits achetés,
                $resultFin = $nbrAchats[0];
            }

            else {
                // sinon on affiche la valeur de la page actuelle multiplié par celle de la limite des lignes à afficher
                $resultFin = $page * $limite;
            }

            // Calcul le nombre de pages
            $nbrPages = ceil($nbrAchats[0] / $limite);
        }

        else {
            // Si l'index achat du tableau de la variable de session affichageProd correspond à "removed",
            if($_SESSION["affichageProd"]["achat"] == "removed") {
                // on exécute un count() sur la table "achat" pour extraire le nombre total de lignes où la valeur de la colonne "date_a" est inférieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la colonne "produit_id" et la colonne "titre" n'est pas égale à la colonne "titre" de la table "achat" ou quand la colonne "cat_id" de la table "produit" est NULL,
                $query = "SELECT count(*) FROM `achat` WHERE `date_a` <= (SELECT `date_creation` FROM `produit` WHERE `id` = achat.produit_id AND `titre` != achat.titre) OR `produit_id` IN (SELECT `id` FROM `produit` WHERE `cat_id` IS NULL)";
                $resultSet = $bdd->query($query);
                $nbrAProd = $resultSet->fetch();
            }

            else {
                // sinon on exécute un count() sur la table "achat" pour extraire le nombre total de lignes où la valeur de la colonne "date_a" est supérieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la variable de session affichageProd et la colonne "titre" est égale à la colonne "titre" de la table "achat" et où la colonne "produit_id" correspond à affichageProd
                $query = "SELECT count(*) FROM `achat` WHERE `date_a` >= (SELECT `date_creation` FROM `produit` WHERE `id` = ? AND `titre` = achat.titre) AND `produit_id` = ?";
                $resultSet = $bdd->query($query, array($_SESSION["affichageProd"]["achat"], $_SESSION["affichageProd"]["achat"]));
                $nbrAProd = $resultSet->fetch();
            }

            // Si le numéro de page fois la selection de lignes est supérieur au nombre d'achats du produit on affiche ce dernier comme valeur de fin des résultats
            if(($page * $limite) > $nbrAProd[0]) {
                $resultFin = $nbrAProd[0];
            }

            else {
                $resultFin = $page * $limite;
            }

            $nbrPages = ceil($nbrAProd[0] / $limite);
        }


        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:/importgames/backoff/achatsmag-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir achats-magasin.phtml lignes 59 et 182)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir achats-magasin.phtml lignes 56 et 179)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir achats-magasin.phtml lignes 61 et 184)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }


        // On affecte à la variable total la valeur 0 (voir achats-magasin.phtml ligne 160)
        $total = 0;


        // On affecte à l'index 1 du tableau de la variable de session selected la valeur "achat" pour identifier la page actuelle (voir fichiers varback.php ligne 82 et ajax.js ligne 128)
        $_SESSION["selected"][1] = "achat";


        // Si la clé "achat" de la variable de session choixTri n'est pas déclarée ou sa valeur égale à NULL,
        if(!isset($_SESSION["choixTri"]["achat"])) {
            // on affecte à la clé "achat" de la variable de session choixTri un tableau en attribuant à la clé "id" la valeur "triDesc" et aux clés suivantes la valeur spéciale null (voir ligne 377)
            $_SESSION["choixTri"]["achat"] = array("id" => "triDesc", "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            // on affecte à la clé "achat" de la variable de session idTriHidden la valeur "arrowUpId" (voir fichiers varback.php ligne 81 et ajax.js ligne 128)
            $_SESSION["idTriHidden"]["achat"] = "arrowUpId";
        }


        // Si la variable post triAchatId est déclarée et sa valeur différente de NULL
        if(isset($_POST["triAchatId"])) {
            // On réinitialise l'affichage en attribuant à la clé "id" du tableau affecté à la clé "achat" de la variable de session choixTri sa valeur actuelle et aux clés suivantes la valeur null
            $_SESSION["choixTri"]["achat"] = array("id" => $_SESSION["choixTri"]["achat"]["id"], "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            // Si la clé "id" du tableau affecté à la clé "achat" de la variable de session choixTri correspond à la valeur "triDesc",
            if($_SESSION["choixTri"]["achat"]["id"] == "triDesc") {
                // on lui affecte la valeur "triAsc",
                $_SESSION["choixTri"]["achat"]["id"] = "triAsc";

                // Voir fichiers varback.php ligne 81 et ajax.js ligne 128
                $_SESSION["idTriHidden"]["achat"] = "arrowDownId";
            }

            else {
                // sinon on lui affecte la valeur "triDesc"
                $_SESSION["choixTri"]["achat"]["id"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrowUpId";
            }
        }

        elseif(isset($_POST["triAchatProdId"])) {
            $_SESSION["choixTri"]["achat"] = array("id" => null, "prod" => $_SESSION["choixTri"]["achat"]["prod"], "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["achat"]["prod"] == "triDesc") {
                $_SESSION["choixTri"]["achat"]["prod"] = "triAsc";

                $_SESSION["idTriHidden"]["achat"] = "arrDwnProd";
            }

            else {
                $_SESSION["choixTri"]["achat"]["prod"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrUpProd";
            }
        }

        elseif(isset($_POST["triAchatTitre"])) {
            $_SESSION["choixTri"]["achat"] = array("id" => null, "prod" => null, "titre" => $_SESSION["choixTri"]["achat"]["titre"], "date" => null, "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["achat"]["titre"] == "triAsc") {
                $_SESSION["choixTri"]["achat"]["titre"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrUpTitre";
            }

            else {
                $_SESSION["choixTri"]["achat"]["titre"] = "triAsc";

                $_SESSION["idTriHidden"]["achat"] = "arrDwnTitre";
            }
        }

        elseif(isset($_POST["triAchatDate"])) {
            $_SESSION["choixTri"]["achat"] = array("id" => null, "prod" => null, "titre" => null, "date" => $_SESSION["choixTri"]["achat"]["date"], "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["achat"]["date"] == "triDesc") {
                $_SESSION["choixTri"]["achat"]["date"] = "triAsc";

                $_SESSION["idTriHidden"]["achat"] = "arrDwnDate";
            }

            else {
                $_SESSION["choixTri"]["achat"]["date"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrUpDate";
            }
        }

        elseif(isset($_POST["triAchatQte"])) {
            $_SESSION["choixTri"]["achat"] = array("id" => null, "prod" => null, "titre" => null, "date" => null, "qte" => $_SESSION["choixTri"]["achat"]["qte"], "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["achat"]["qte"] == "triDesc") {
                $_SESSION["choixTri"]["achat"]["qte"] = "triAsc";

                $_SESSION["idTriHidden"]["achat"] = "arrDwnQte";
            }

            else {
                $_SESSION["choixTri"]["achat"]["qte"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrUpQte";
            }
        }

        elseif(isset($_POST["triAchatPrix"])) {
            $_SESSION["choixTri"]["achat"] = array("id" => null, "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => $_SESSION["choixTri"]["achat"]["prix"], "total" => null);

            if($_SESSION["choixTri"]["achat"]["prix"] == "triDesc") {
                $_SESSION["choixTri"]["achat"]["prix"] = "triAsc";

                $_SESSION["idTriHidden"]["achat"] = "arrDwnPrix";
            }

            else {
                $_SESSION["choixTri"]["achat"]["prix"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrUpPrix";
            }
        }

        elseif(isset($_POST["triAchatTotal"])) {
            $_SESSION["choixTri"]["achat"] = array("id" => null, "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => $_SESSION["choixTri"]["achat"]["total"]);

            if($_SESSION["choixTri"]["achat"]["total"] == "triDesc") {
                $_SESSION["choixTri"]["achat"]["total"] = "triAsc";

                $_SESSION["idTriHidden"]["achat"] = "arrDwnTotal";
            }

            else {
                $_SESSION["choixTri"]["achat"]["total"] = "triDesc";

                $_SESSION["idTriHidden"]["achat"] = "arrUpTotal";
            }
        }


        if(isset($_SESSION["affichageProd"]["achat"])) {
            // Si l'expression $_SESSION["affichageProd"]["achat"] égal "defaut" est true,
            if($_SESSION["affichageProd"]["achat"] == "defaut") {
                // on sélectionne toutes les lignes de la table "achat" où la colonne "produit_id" n'est pas NULL
                $query = "SELECT * FROM `achat` WHERE `produit_id` IS NOT NULL";
            }

            // ou bien si sa valeur correspond à "removed",
            elseif($_SESSION["affichageProd"]["achat"] == "removed") {
                // on sélectionne toutes les lignes de la table "achat" où la valeur de la colonne "date_a" est inférieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la colonne "produit_id" et la colonne "titre" n'est pas égale à la colonne "titre" de la table "achat" ou quand la colonne "cat_id" de la table "produit" est NULL
                $query = "SELECT * FROM `achat` WHERE `date_a` <= (SELECT `date_creation` FROM `produit` WHERE `id` = achat.produit_id AND `titre` != achat.titre) OR `produit_id` IN (SELECT `id` FROM `produit` WHERE `cat_id` IS NULL)";
            }

            else {
                // sinon on sélectionne toutes les lignes de la table "achat" où la valeur de la colonne "date_a" est supérieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la variable de session affichageProd et la colonne "titre" est égale à la colonne "titre" de la table "achat" et où la colonne "produit_id" correspond à affichageProd
                $query = "SELECT * FROM `achat` WHERE `date_a` >= (SELECT `date_creation` FROM `produit` WHERE `id` = ? AND `titre` = achat.titre) AND `produit_id` = ?";
            }
        }

        else {
            $query = "SELECT * FROM `achat` WHERE `produit_id` IS NOT NULL";

            // L'index achat du tableau de la variable de session affichageProd correspond à la valeur de l'envoi du choix de produit et la valeur "defaut" à tout les résultats de la table "achat" où la valeur de la colonne "produit_id" n'est pas NULL
            $_SESSION["affichageProd"]["achat"] = "defaut";
        }


        // Si la clé "id" du tableau affecté à la clé "achat" de la variable de session choixTri correspond à la valeur "triDesc",
        if($_SESSION["choixTri"]["achat"]["id"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `id` DESC LIMIT $debut, $limite" pour afficher les résultats de la requête par tri descendant de la colonne "id" dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        // Ou bien si la clé "id" du tableau affecté à la clé "achat" de la variable de session choixTri correspond à la valeur "triAsc",
        elseif($_SESSION["choixTri"]["achat"]["id"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `id` ASC LIMIT $debut, $limite" pour afficher les résultats de la requête par tri ascendant de la colonne "id" dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["prod"] == "triDesc") {
            $query .= " ORDER BY `produit_id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["prod"] == "triAsc") {
            $query .= " ORDER BY `produit_id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["titre"] == "triDesc") {
            $query .= " ORDER BY `titre` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["titre"] == "triAsc") {
            $query .= " ORDER BY `titre` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["date"] == "triDesc") {
            $query .= " ORDER BY `date_a` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["date"] == "triAsc") {
            $query .= " ORDER BY `date_a` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["qte"] == "triDesc") {
            $query .= " ORDER BY `qte` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["qte"] == "triAsc") {
            $query .= " ORDER BY `qte` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["prix"] == "triDesc") {
            $query .= " ORDER BY `prix` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["prix"] == "triAsc") {
            $query .= " ORDER BY `prix` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["total"] == "triDesc") {
            $query .= " ORDER BY `qte` * `prix` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["achat"]["total"] == "triAsc") {
            $query .= " ORDER BY `qte` * `prix` ASC LIMIT $debut, $limite";
        }


        if($_SESSION["affichageProd"]["achat"] != "defaut" && $_SESSION["affichageProd"]["achat"] != "removed") {
            $resultSet = $bdd->query($query, array($_SESSION["affichageProd"]["achat"], $_SESSION["affichageProd"]["achat"]));
            $achats = $resultSet->fetchAll();
        }

        else {
            $resultSet = $bdd->query($query);
            $achats = $resultSet->fetchAll();
        }


        if($achats) {
            // On initialise la variable i avec la valeur 0
            $i = 0;

            foreach($achats as $achat) {
                // On stocke dans une variable de session la chaîne de caractères et la variable avec l'index id correspondants à l'attribut id de l'élément html button resetAchat pour en récupérer la valeur dans le fichier varback.php (voir fichiers achats-magasin.phtml ligne 153, varback.php ligne 76 et ajax.js ligne 76)
                $_SESSION["loopElementId"][1][$i++] = "resetAchat" . intval($achat["id"]);
            }

            // On stocke le message de confirmation dans une variable de session pour en récupérer la valeur dans le fichier varback.php (dossier backoff, fichiers varback.php ligne 77 et ajax.js ligne 80)
            $_SESSION["loopMsgConfirm"] = "Voulez-vous vraiment supprimer cette commande produit ? Cette action est irréversible.";
        }


        // Si la variable post resetAchat est déclarée et différente de NULL
        if(isset($_POST["resetAchat"])) {
            $achatId = htmlspecialchars($_POST["resetAchat"]);
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification (fichier achats-magasin.phtml ligne 155)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/reassort/achats-magasin?page=" . $page OR $referer == "https://importgames.llemaitre.com/backoff/achatsmag-page-" . $page) { */
                        // On sélectionne toutes les valeurs de la table "achat" où l'Id correspond à celui de la variable achatId (fichier achats-magasin.phtml ligne 153)
                        $query="SELECT * FROM `achat` WHERE `id` = ?";
                        $resultSet = $bdd->query($query, array($achatId));
                        $achatReset = $resultSet->fetch();

                        // On remet à zéro les colonnes contenant les informations de l'achat sélectionné
                        $query = "UPDATE `achat` SET `produit_id` = NULL, `titre` = NULL, `qte` = NULL, `prix` = NULL, `date_a` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($achatReset["id"]));

                        // On sélectionne la colonne "qte" de la table "produit" où la valeur de l'Id correspond à celle de l'achat supprimé pour récupérer le stock actuel du produit
                        $query="SELECT `qte` FROM `produit` WHERE `id` = ?";
                        $resultSet = $bdd->query($query, array($achatReset["produit_id"]));
                        $stockProd = $resultSet->fetch();

                        // Si le stock du produit est inférieur à la quantité de la commande à supprimer,
                        if($stockProd["qte"] < $achatReset["qte"]) {
                            // on affecte à la variable achatReset["qte"] la quantité du produit en stock pour arriver à une balance de 0
                            $achatReset["qte"] = $stockProd["qte"];
                        }

                        // On met à jour la quantité dans la table "produit"
                        $query="UPDATE `produit` SET `qte` = `qte` - ? WHERE `id` = ?";
                        $bdd->insert($query, array($achatReset["qte"], $achatReset["produit_id"]));

                        header("location:/importgames/backoff/achatsmag-page-" . $page);
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page achats-magasin.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans achats-magasin.php
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