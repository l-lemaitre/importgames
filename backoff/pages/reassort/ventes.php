<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page ventes.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier ventes.php
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
        return "Liste des ventes";
    }


    // Fonction pour afficher la date de la commande où l'Id correspond à la valeur placée en argument (ventes.phtml ligne 155)
    function getDateCo($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `date_co` FROM `commande` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $com = $resultSet->fetch();

        return $com["date_co"];
    }


    // Fonction pour récupérer la date de création de la fiche produit où l'Id correspond à la valeur placée en argument (ventes.phtml ligne 149)
    function getDatebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `date_creation` FROM `produit` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $prod = $resultSet->fetch();

        return $prod["date_creation"];
    }


    // Fonction pour récupérer la date de création de la fiche produit où l'Id correspond à la valeur placée en argument (ventes.phtml ligne 149)
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
        header("location:/importgames/backoff/ventes-page-1");
    }

    else {
        // On sélectionne toutes les valeurs contenues dans la table "produit" où la valeur de la colonne "cat_id" n'est pas NULL pour afficher l'ensemble des résultats par tri ascendant de la colonne "titre" (ventes.phtml ligne 24)
        $query = "SELECT * FROM `produit` WHERE `cat_id` IS NOT NULL ORDER BY `titre`";
        $resultSet = $bdd->query($query);
        $prods = $resultSet->fetchAll();


        // On exécute un count() sur la table "detail_com" pour extraire le nombre total de lignes où la valeur de la colonne "produit_id" n'est pas NULL
        $query = "SELECT COUNT(*) FROM `detail_com` WHERE `produit_id` IS NOT NULL";
        $resultSet = $bdd->query($query);
        $nbrVentes = $resultSet->fetch();


        // Si la variable post affichageProd est déclarée,
        if(isset($_POST["affichageProd"])) {
            // on affecte à l'index vente du tableau de la variable de session affichageProd la valeur de la variable post affichageProd pour afficher la description correspondant à la valeur de l'option de l'élément htlml select (voir fichiers ventes.phtml ligne 21, varback.php ligne 83 et ajax.js ligne 143)
            $_SESSION["affichageProd"]["vente"] = htmlspecialchars($_POST["affichageProd"]);
        }

        // ou bien si la variable post lignesVente est déclarée,
        elseif(isset($_POST["lignesVente"])) {
            // on affecte à l'index 9 du tableau de la variable de session lignes la valeur de la variable post lignesVente qui correspond à la valeur de l'envoi du nombre de lignes à afficher (voir fichiers varback.php ligne 72 et ajax.js ligne 31)
            $_SESSION["lignes"][9] = htmlspecialchars($_POST["lignesVente"]);
        }

        // ou bien si l'index 9 du tableau de la variable de session lignes est déclaré (pour afficher le nombre de lignes sélectionnées précédemment quand on revient sur la page),
        elseif(isset($_SESSION["lignes"][9])) {
            // on lui affecte sa valeur actuelle
            $_SESSION["lignes"][9] = $_SESSION["lignes"][9];
        }

        // sinon l'index 9 du tableau de la variable de session lignes correspond à la valeur 5, 10 ou 25 selon le nombre total de produits vendus
        else {
            if($nbrVentes[0] <= 100) {
                $_SESSION["lignes"][9] = "5";
            }

            elseif($nbrVentes[0] > 250) {
                $_SESSION["lignes"][9] = "25";
            }

            else {
                $_SESSION["lignes"][9] = "10";
            }
        }


        // La variable limite définit le nombre de lignes affichées par page
        $limite = $_SESSION["lignes"][9];

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page ventes.php il faut ajouter au lien ?page=1
        $page = htmlspecialchars($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers varback.php ligne 73 et ajax.js ligne 35
        $_SESSION["page"] = $page;


        // La variable resultDebut retourne le numéro du résultat d'où commence la sélection de la page. On lui ajoute la valeur 1 pour commencer le compte à partir de 1 et non 0 (ventes.phtml ligne 50 ou 52)
        $resultDebut = (($page - 1) * $limite) + 1;


        // Si l'index vente du tableau de la variable de session affichageProd n'est pas déclaré ou égal à la valeur "defaut"
        if(!isset($_SESSION["affichageProd"]["vente"]) || $_SESSION["affichageProd"]["vente"] == "defaut") {
            // Si le numéro de page fois la selection de lignes est supérieur au nombre de ventes,
            if(($page * $limite) > $nbrVentes[0]) {
                // on affecte à la variable resultFin la valeur du nombre de produits vendus,
                $resultFin = $nbrVentes[0];
            }

            else {
                // sinon on affiche la valeur de la page actuelle multiplié par celle de la limite des lignes à afficher
                $resultFin = $page * $limite;
            }

            // Calcul le nombre de pages
            $nbrPages = ceil($nbrVentes[0] / $limite);
        }

        else {
            // Si l'index vente du tableau de la variable de session affichageProd correspond à "removed",
            if($_SESSION["affichageProd"]["vente"] == "removed") {
                // on exécute un count() sur la table "detail_com" pour extraire le nombre total de lignes où la valeur de la colonne "date_co" de la table "commande" est inférieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la colonne "produit_id" et la colonne "titre" n'est pas égale à la colonne "titre" de la table "detail_com" ou quand la colonne "cat_id" de la table "produit" est NULL,
                $query = "SELECT count(*) FROM `detail_com` WHERE `commande_id` IN (SELECT `id` FROM `commande` WHERE DATE_FORMAT(`date_co`, \"%Y-%m-%d\") <= (SELECT `date_creation` FROM `produit` WHERE `id` = detail_com.produit_id AND `titre` != detail_com.titre)) OR `produit_id` IN (SELECT `id` FROM `produit` WHERE `cat_id` IS NULL)";
                $resultSet = $bdd->query($query);
                $nbrVProd = $resultSet->fetch();
            }

            else {
                // sinon on exécute un count() sur la table "detail_com" pour extraire le nombre total de lignes où la valeur de la colonne "date_co" de la table "commande" est supérieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la variable de session affichageProd et la colonne "titre" est égale à la colonne "titre" de la table "detail_com" et où la colonne "produit_id" correspond à affichageProd (ouf)
                $query = "SELECT count(*) FROM `detail_com` WHERE `commande_id` IN (SELECT `Id` FROM `commande` WHERE DATE_FORMAT(`date_co`, \"%Y-%m-%d\") >= (SELECT `date_creation` FROM `produit` WHERE `id` = ? AND `titre` = detail_com.titre)) AND `produit_id` = ?";
                $resultSet = $bdd->query($query, array($_SESSION["affichageProd"]["vente"], $_SESSION["affichageProd"]["vente"]));
                $nbrVProd = $resultSet->fetch();
            }

            // Si le numéro de page fois la selection de lignes est supérieur au nombre de ventes du produit on affiche ce dernier comme valeur de fin des résultats
            if(($page * $limite) > $nbrVProd[0]) {
                $resultFin = $nbrVProd[0];
            }

            else {
                $resultFin = $page * $limite;
            }

            $nbrPages = ceil($nbrVProd[0] / $limite);
        }


        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:/importgames/backoff/ventes-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir ventes.phtml lignes 59 et 192)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir ventes.phtml lignes 56 et 189)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir ventes.phtml lignes 61 et 194)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }


        // On affecte à la variable total la valeur 0 (voir ventes.phtml ligne 170)
        $total = 0;


        // On affecte à l'index 1 du tableau de la variable de session selected la valeur "vente" pour identifier la page actuelle (voir fichiers varback.php ligne 82 et ajax.js ligne 128)
        $_SESSION["selected"][1] = "vente";


        // Si la clé "vente" de la variable de session choixTri n'est pas déclarée ou sa valeur égale à NULL,
        if(!isset($_SESSION["choixTri"]["vente"])) {
            // on affecte à la clé "vente" de la variable de session choixTri un tableau en attribuant à la clé "id" la valeur "triDesc" et aux clés suivantes la valeur spéciale null (voir ligne 405)
            $_SESSION["choixTri"]["vente"] = array("id" => "triDesc", "com" => null, "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            // on affecte à la clé "vente" de la variable de session idTriHidden la valeur "arrowUpId" (voir fichiers varback.php ligne 81 et ajax.js ligne 128)
            $_SESSION["idTriHidden"]["vente"] = "arrowUpId";
        }


        // Si la variable post triVenteId est déclarée et sa valeur différente de NULL
        if(isset($_POST["triVenteId"])) {
            // On réinitialise l'affichage en attribuant à la clé "id" du tableau affecté à la clé "vente" de la variable de session choixTri sa valeur actuelle et aux clés suivantes la valeur null
            $_SESSION["choixTri"]["vente"] = array("id" => $_SESSION["choixTri"]["vente"]["id"], "com" => null, "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            // Si la clé "id" du tableau affecté à la clé "vente" de la variable de session choixTri correspond à la valeur "triDesc",
            if($_SESSION["choixTri"]["vente"]["id"] == "triDesc") {
                // on lui affecte la valeur "triAsc",
                $_SESSION["choixTri"]["vente"]["id"] = "triAsc";

                // Voir fichiers varback.php ligne 81 et ajax.js ligne 128
                $_SESSION["idTriHidden"]["vente"] = "arrowDownId";
            }

            else {
                // sinon on lui affecte la valeur "triDesc"
                $_SESSION["choixTri"]["vente"]["id"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrowUpId";
            }
        }

        elseif(isset($_POST["triVenteCoId"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => $_SESSION["choixTri"]["vente"]["com"], "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["vente"]["com"] == "triDesc") {
                $_SESSION["choixTri"]["vente"]["com"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnCom";
            }

            else {
                $_SESSION["choixTri"]["vente"]["com"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpCom";
            }
        }

        elseif(isset($_POST["triVenteProdId"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => null, "prod" => $_SESSION["choixTri"]["vente"]["prod"], "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["vente"]["prod"] == "triDesc") {
                $_SESSION["choixTri"]["vente"]["prod"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnProd";
            }

            else {
                $_SESSION["choixTri"]["vente"]["prod"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpProd";
            }
        }

        elseif(isset($_POST["triVenteTitre"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => null, "prod" => null, "titre" => $_SESSION["choixTri"]["vente"]["titre"], "date" => null, "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["vente"]["titre"] == "triAsc") {
                $_SESSION["choixTri"]["vente"]["titre"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpTitre";
            }

            else {
                $_SESSION["choixTri"]["vente"]["titre"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnTitre";
            }
        }

        elseif(isset($_POST["triVenteDate"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => null, "prod" => null, "titre" => null, "date" => $_SESSION["choixTri"]["vente"]["date"], "qte" => null, "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["vente"]["date"] == "triDesc") {
                $_SESSION["choixTri"]["vente"]["date"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnDate";
            }

            else {
                $_SESSION["choixTri"]["vente"]["date"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpDate";
            }
        }

        elseif(isset($_POST["triVenteQte"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => null, "prod" => null, "titre" => null, "date" => null, "qte" => $_SESSION["choixTri"]["vente"]["qte"], "prix" => null, "total" => null);

            if($_SESSION["choixTri"]["vente"]["qte"] == "triDesc") {
                $_SESSION["choixTri"]["vente"]["qte"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnQte";
            }

            else {
                $_SESSION["choixTri"]["vente"]["qte"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpQte";
            }
        }

        elseif(isset($_POST["triVentePrix"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => null, "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => $_SESSION["choixTri"]["vente"]["prix"], "total" => null);

            if($_SESSION["choixTri"]["vente"]["prix"] == "triDesc") {
                $_SESSION["choixTri"]["vente"]["prix"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnPrix";
            }

            else {
                $_SESSION["choixTri"]["vente"]["prix"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpPrix";
            }
        }

        elseif(isset($_POST["triVenteTotal"])) {
            $_SESSION["choixTri"]["vente"] = array("id" => null, "com" => null, "prod" => null, "titre" => null, "date" => null, "qte" => null, "prix" => null, "total" => $_SESSION["choixTri"]["vente"]["total"]);

            if($_SESSION["choixTri"]["vente"]["total"] == "triDesc") {
                $_SESSION["choixTri"]["vente"]["total"] = "triAsc";

                $_SESSION["idTriHidden"]["vente"] = "arrDwnTotal";
            }

            else {
                $_SESSION["choixTri"]["vente"]["total"] = "triDesc";

                $_SESSION["idTriHidden"]["vente"] = "arrUpTotal";
            }
        }


        if(isset($_SESSION["affichageProd"]["vente"])) {
            // Si l'expression $_SESSION["affichageProd"]["vente"] égal "defaut" est true,
            if($_SESSION["affichageProd"]["vente"] == "defaut") {
                // on sélectionne toutes les lignes de la table "detail_com" où la colonne "produit_id" n'est pas NULL
                $query = "SELECT * FROM `detail_com` WHERE `produit_id` IS NOT NULL";
            }

            // ou bien si sa valeur correspond à "removed",
            elseif($_SESSION["affichageProd"]["vente"] == "removed") {
                // on sélectionne toutes les lignes de la table "detail_com" où la valeur de la colonne "date_co" de la table "commande" est inférieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la colonne "produit_id" et la colonne "titre" n'est pas égale à la colonne "titre" de la table "detail_com" ou quand la colonne "cat_id" de la table "produit" est NULL
                $query = "SELECT * FROM `detail_com` WHERE `commande_id` IN (SELECT `id` FROM `commande` WHERE DATE_FORMAT(`date_co`, \"%Y-%m-%d\") <= (SELECT `date_creation` FROM `produit` WHERE `id` = detail_com.produit_id AND `titre` != detail_com.titre)) OR `produit_id` IN (SELECT `id` FROM `produit` WHERE `cat_id` IS NULL)";
            }

            else {
                // sinon on sélectionne toutes les lignes de la table "detail_com" où la valeur de la colonne "date_co" de la table "commande" est supérieure ou égale à celle de la colonne "date_creation" de la table "produit" où la colonne "id" est égale à la variable de session affichageProd et la colonne "titre" est égale à la colonne "titre" de la table "detail_com" et où la colonne "produit_id" correspond à affichageProd
                $query = "SELECT * FROM `detail_com` WHERE `commande_id` IN (SELECT `id` FROM `commande` WHERE DATE_FORMAT(`date_co`, \"%Y-%m-%d\") >= (SELECT `date_creation` FROM `produit` WHERE `id` = ? AND `titre` = detail_com.titre)) AND `produit_id` = ?";
            }
        }

        else {
            $query = "SELECT * FROM `detail_com` WHERE `commande_id` IS NOT NULL";

            // L'index vente du tableau de la variable de session affichageProd correspond à la valeur de l'envoi du choix de produit et la valeur "defaut" à tout les résultats de la table "detail_com" où la valeur de la colonne "commande_id" n'est pas NULL
            $_SESSION["affichageProd"]["vente"] = "defaut";
        }


        // Si la clé "id" du tableau affecté à la clé "vente" de la variable de session choixTri correspond à la valeur "triDesc",
        if($_SESSION["choixTri"]["vente"]["id"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `id` DESC LIMIT $debut, $limite" pour afficher les résultats de la requête par tri descendant de la colonne "id" dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        // Ou bien si la clé "id" du tableau affecté à la clé "vente" de la variable de session choixTri correspond à la valeur "triAsc",
        elseif($_SESSION["choixTri"]["vente"]["id"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `id` ASC LIMIT $debut, $limite" pour afficher les résultats de la requête par tri ascendant de la colonne "id" dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["com"] == "triDesc") {
            $query .= " ORDER BY `commande_id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["com"] == "triAsc") {
            $query .= " ORDER BY `commande_id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["prod"] == "triDesc") {
            $query .= " ORDER BY `produit_id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["prod"] == "triAsc") {
            $query .= " ORDER BY `produit_id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["titre"] == "triDesc") {
            $query .= " ORDER BY `titre` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["titre"] == "triAsc") {
            $query .= " ORDER BY `titre` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["date"] == "triDesc") {
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["date"] == "triAsc") {
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["qte"] == "triDesc") {
            $query .= " ORDER BY `qte` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["qte"] == "triAsc") {
            $query .= " ORDER BY `qte` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["prix"] == "triDesc") {
            $query .= " ORDER BY `prix` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["prix"] == "triAsc") {
            $query .= " ORDER BY `prix` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["total"] == "triDesc") {
            $query .= " ORDER BY `qte` * `prix` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["vente"]["total"] == "triAsc") {
            $query .= " ORDER BY `qte` * `prix` ASC LIMIT $debut, $limite";
        }


        if($_SESSION["affichageProd"]["vente"] != "defaut" && $_SESSION["affichageProd"]["vente"] != "removed") {
            $resultSet = $bdd->query($query, array($_SESSION["affichageProd"]["vente"], $_SESSION["affichageProd"]["vente"]));
            $details = $resultSet->fetchAll();
        }

        else {
            $resultSet = $bdd->query($query);
            $details = $resultSet->fetchAll();
        }


        if($details) {
            // On initialise la variable i avec la valeur 0
            $i = 0;

            foreach($details as $detail) {
                // On stocke dans une variable de session la chaîne de caractères et la variable avec l'index id correspondants à l'attribut id de l'élément html button resetVente pour en récupérer la valeur dans le fichier varback.php (voir fichiers ventes.phtml ligne 163, varback.php ligne 76 et ajax.js ligne 76)
                $_SESSION["loopElementId"][1][$i++] = "resetVente" . intval($detail["id"]);
            }

            // On stocke le message de confirmation dans une variable de session pour en récupérer la valeur dans le fichier varback.php (dossier backoff, fichiers varback.php ligne 77 et ajax.js ligne 80)
            $_SESSION["loopMsgConfirm"] = "Voulez-vous vraiment supprimer cette vente ? Cette action est irréversible.";
        }


        // Si la variable post resetVente est déclarée et différente de NULL
        if(isset($_POST["resetVente"])) {
            $venteId = htmlspecialchars($_POST["resetVente"]);
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification (fichier ventes.phtml ligne 165)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/reassort/ventes?page=" . $page OR $referer == "https://importgames.llemaitre.com/backoff/ventes-page-" . $page) { */
                        // On sélectionne toutes les valeurs de la table "detail_com" où l'Id correspond à celui de la variable venteId (fichier ventes.phtml ligne 163)
                        $query="SELECT * FROM `detail_com` WHERE `id` = ?";
                        $resultSet = $bdd->query($query, array($venteId));
                        $venteReset = $resultSet->fetch();

                        // On remet à zéro les colonnes contenant les informations de la vente sélectionnée
                        $query = "UPDATE `detail_com` SET `commande_id` = NULL, `produit_id` = NULL, `titre` = NULL, `qte` = NULL, `prix` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($venteReset["id"]));

                        // on met à jour la quantité dans la table "produit"
                        $query="UPDATE `produit` SET `qte` = `qte` + ? WHERE `id` = ?";
                        $bdd->insert($query, array($venteReset["qte"], $venteReset["produit_id"]));

                        // On sélectionne toutes les colonnes de la table "detail_com" où la valeur de `commande_id` correspond à celle de la vente supprimée (ligne 515)
                        $query="SELECT * FROM `detail_com` WHERE `commande_id` = ?";
                        $resultSet = $bdd->query($query, array($venteReset["commande_id"]));

                        // On retourne le nombre de lignes affectées par le dernier appel à la fonction execute() sur la table "detail_com" pour récupérer le nombre total de produits de la commande client
                        $nbrProdsCo = $resultSet->rowCount();

                        // Si la commande contient une autre vente,
                        if($nbrProdsCo) {
                            // on met à jour le total dans la table "commande",
                            $query="UPDATE `commande` SET `total` = `total` - (? * ?) WHERE `id` = ?";
                            $bdd->insert($query, array($venteReset["qte"], $venteReset["prix"], $venteReset["commande_id"]));
                        }

                        else {
                            // sinon on remet à zéro les colonnes contenant les informations de la commande client
                            $query = "UPDATE `commande` SET `user_id` = NULL, `numero` = NULL, `adresse` = NULL, `total` = NULL, `date_co` = NULL WHERE `id` = ?";
                            $bdd->insert($query, array($venteReset["commande_id"]));
                        }

                        header("location:/importgames/backoff/ventes-page-" . $page);
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page ventes.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans ventes.php
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