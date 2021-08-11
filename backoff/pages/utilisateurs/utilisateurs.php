<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page utilisateurs.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier utilisateurs.php
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
        return "Liste des utilisateurs";
    }


    // Fonction pour masquer le texte trop long du mot de passe hacher et le remplacer par "..." dans utilisateurs.phtml lignes 150, 299 et 309
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
        header("location:/importgames/backoff/users-page-1");
    }

    else {
        // On exécute un count() sur la table "user" pour extraire son nombre total de lignes
        $query = "SELECT COUNT(*) FROM `user`";
        $resultSet = $bdd->query($query);
        $nbrUsers = $resultSet->fetch();


        // Si la variable post lignesUser est déclarée,
        if(isset($_POST["lignesUser"])) {
            // on affecte à l'index 12 du tableau de la variable de session lignes la valeur de la variable post lignesUser qui correspond à la valeur de l'envoi du nombre de lignes à afficher (voir fichiers varback.php ligne 72 et ajax.js ligne 31)
            $_SESSION["lignes"][12] = htmlspecialchars($_POST["lignesUser"]);
        }

        // ou bien si l'index 12 du tableau de la variable de session lignes est déclaré,
        elseif(isset($_SESSION["lignes"][12])) {
            // on lui affecte sa valeur actuelle
            $_SESSION["lignes"][12] = $_SESSION["lignes"][12];
        }

        // sinon l'index 12 du tableau de la variable de session lignes correspond à la valeur 5, 10 ou 25 selon le nombre de lignes à afficher
        else {
            if($nbrUsers[0] <= 100) {
                $_SESSION["lignes"][12] = "5";
            }

            elseif($nbrUsers[0] > 250) {
                $_SESSION["lignes"][12] = "25";
            }

            else {
                $_SESSION["lignes"][12] = "10";
            }
        }


        // La variable limite définit le nombre de lignes à affichées par page
        $limite = $_SESSION["lignes"][12];

        // $_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page utilisateurs.php il faut ajouter au lien ?page=1
        $page = htmlspecialchars($_GET["page"]);

        // La variable debut définit à partir de quelle ligne commence la sélection de la page courante. Par défaut si $page = 1 alors $debut = 0, si $page = 2 alors $debut = (2-1)*5 = 5
        $debut = ($page - 1) * $limite;


        // Voir fichiers varback.php ligne 73 et ajax.js ligne 35
        $_SESSION["page"] = $page;


        // La variable resultDebut retourne le numéro du résultat d'où commence la sélection de la page. On lui ajoute la valeur 1 pour correspondre avec l'Id du premier résultat affiché par tri ascendant (utilisateurs.phtml ligne 41)
        $resultDebut = (($page - 1) * $limite) + 1;

        // Si le numéro de page fois la selection de lignes est supérieur au nombre d'utilisateurs,
        if(($page * $limite) > $nbrUsers[0]) {
            // on affecte à la variable resultFin la valeur du nombre total d'utilisateurs,
            $resultFin = $nbrUsers[0];
        }

        else {
            // sinon on affiche la valeur de la page actuelle multiplié par celle de la limite des lignes à afficher
            $resultFin = $page * $limite;
        }


        // Calcul le nombre de pages
        $nbrPages = ceil($nbrUsers[0] / $limite);

        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:/importgames/backoff/users-page-1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir utilisateurs.phtml lignes 47 et 333)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir utilisateurs.phtml lignes 44 et 330)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir utilisateurs.phtml lignes 49 et 335)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }


        // On affecte à l'index 1 du tableau de la variable de session selected la valeur "user" pour identifier la page actuelle (voir fichiers varback.php ligne 82 et ajax.js ligne 128)
        $_SESSION["selected"][1] = "user";


        // Si la clé "user" de la variable de session choixTri n'est pas déclarée ou sa valeur égale à NULL,
        if(!isset($_SESSION["choixTri"]["user"])) {
            // on affecte à la clé "user" de la variable de session choixTri un tableau en attribuant à la clé "id" la valeur "triDesc" et aux clés suivantes la valeur spéciale null (voir ligne 456)
            $_SESSION["choixTri"]["user"] = array("id" => "triDesc", "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            // on affecte à la clé "user" de la variable de session idTriHidden la valeur "arrowUpId" (voir fichiers varback.php ligne 81 et ajax.js ligne 128)
            $_SESSION["idTriHidden"]["user"] = "arrowUpId";
        }


        // Si la variable post triUserId est déclarée et sa valeur différente de NULL
        if(isset($_POST["triUserId"])) {
            // On réinitialise l'affichage en attribuant à la clé "id" du tableau affecté à la clé "user" de la variable de session choixTri sa valeur actuelle et aux clés suivantes la valeur null
            $_SESSION["choixTri"]["user"] = array("id" => $_SESSION["choixTri"]["user"]["id"], "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            // Si la clé "id" du tableau affecté à la clé "user" de la variable de session choixTri correspond à la valeur "triDesc",
            if($_SESSION["choixTri"]["user"]["id"] == "triDesc") {
                // on lui affecte la valeur "triAsc",
                $_SESSION["choixTri"]["user"]["id"] = "triAsc";

                // Voir fichiers varback.php ligne 81 et ajax.js ligne 128
                $_SESSION["idTriHidden"]["user"] = "arrowDownId";
            }

            else {
                // sinon on lui affecte la valeur "triDesc"
                $_SESSION["choixTri"]["user"]["id"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrowUpId";
            }
        }

        elseif(isset($_POST["triUsern"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => $_SESSION["choixTri"]["user"]["usern"], "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["usern"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["usern"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpUsern";
            }

            else {
                $_SESSION["choixTri"]["user"]["usern"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnUsern";
            }
        }

        elseif(isset($_POST["triUserMail"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => $_SESSION["choixTri"]["user"]["mail"], "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["mail"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["mail"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpMail";
            }

            else {
                $_SESSION["choixTri"]["user"]["mail"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnMail";
            }
        }

        elseif(isset($_POST["triUserPass"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => $_SESSION["choixTri"]["user"]["pass"], "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["pass"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["pass"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpPass";
            }

            else {
                $_SESSION["choixTri"]["user"]["pass"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnPass";
            }
        }

        elseif(isset($_POST["triUserNom"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => $_SESSION["choixTri"]["user"]["nom"], "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["nom"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["nom"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpNom";
            }

            else {
                $_SESSION["choixTri"]["user"]["nom"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnNom";
            }
        }

        elseif(isset($_POST["triUserPrenom"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => $_SESSION["choixTri"]["user"]["prenom"], "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["prenom"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["prenom"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpPrenom";
            }

            else {
                $_SESSION["choixTri"]["user"]["prenom"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnPrenom";
            }
        }

        elseif(isset($_POST["triUserAdresse"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => $_SESSION["choixTri"]["user"]["adress"], "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["adress"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["adress"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpAdress";
            }

            else {
                $_SESSION["choixTri"]["user"]["adress"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnAdress";
            }
        }

        elseif(isset($_POST["triUserZip"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => $_SESSION["choixTri"]["user"]["zip"], "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["zip"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["zip"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpZip";
            }

            else {
                $_SESSION["choixTri"]["user"]["zip"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnZip";
            }
        }

        elseif(isset($_POST["triUserVille"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => $_SESSION["choixTri"]["user"]["ville"], "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["ville"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["ville"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpVille";
            }

            else {
                $_SESSION["choixTri"]["user"]["ville"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnVille";
            }
        }

        elseif(isset($_POST["triUserPays"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => $_SESSION["choixTri"]["user"]["pays"], "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["pays"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["pays"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpPays";
            }

            else {
                $_SESSION["choixTri"]["user"]["pays"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnPays";
            }
        }

        elseif(isset($_POST["triUserTel"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => $_SESSION["choixTri"]["user"]["tel"], "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["tel"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["tel"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpTel";
            }

            else {
                $_SESSION["choixTri"]["user"]["tel"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnTel";
            }
        }

        elseif(isset($_POST["triUserDateReg"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => $_SESSION["choixTri"]["user"]["reg"], "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["reg"] == "triDesc") {
                $_SESSION["choixTri"]["user"]["reg"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnReg";
            }

            else {
                $_SESSION["choixTri"]["user"]["reg"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpReg";
            }
        }

        elseif(isset($_POST["triUserToken"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => $_SESSION["choixTri"]["user"]["tk"], "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["tk"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["tk"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpTk";
            }

            else {
                $_SESSION["choixTri"]["user"]["tk"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnTk";
            }
        }

        elseif(isset($_POST["triUserConfCte"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => $_SESSION["choixTri"]["user"]["conf"], "tkStayc" => null, "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["conf"] == "triDesc") {
                $_SESSION["choixTri"]["user"]["conf"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnConf";
            }

            else {
                $_SESSION["choixTri"]["user"]["conf"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpConf";
            }
        }

        elseif(isset($_POST["triUserTokStayco"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => $_SESSION["choixTri"]["user"]["tkStayc"], "newPass" => null, "unsub" => null);

            if($_SESSION["choixTri"]["user"]["tkStayc"] == "triAsc") {
                $_SESSION["choixTri"]["user"]["tkStayc"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpTkStayc";
            }

            else {
                $_SESSION["choixTri"]["user"]["tkStayc"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnTkStayc";
            }
        }

        elseif(isset($_POST["triUserNewPass"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => $_SESSION["choixTri"]["user"]["newPass"], "unsub" => null);

            if($_SESSION["choixTri"]["user"]["newPass"] == "triDesc") {
                $_SESSION["choixTri"]["user"]["newPass"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnNewPass";
            }

            else {
                $_SESSION["choixTri"]["user"]["newPass"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpNewPass";
            }
        }

        elseif(isset($_POST["triUserDateUnsub"])) {
            $_SESSION["choixTri"]["user"] = array("id" => null, "usern" => null, "mail" => null, "pass" => null, "nom" => null, "prenom" => null, "adress" => null, "zip" => null, "ville" => null, "pays" => null, "tel" => null, "reg" => null, "tk" => null, "conf" => null, "tkStayc" => null, "newPass" => null, "unsub" => $_SESSION["choixTri"]["user"]["unsub"]);

            if($_SESSION["choixTri"]["user"]["unsub"] == "triDesc") {
                $_SESSION["choixTri"]["user"]["unsub"] = "triAsc";

                $_SESSION["idTriHidden"]["user"] = "arrDwnUnsub";
            }

            else {
                $_SESSION["choixTri"]["user"]["unsub"] = "triDesc";

                $_SESSION["idTriHidden"]["user"] = "arrUpUnsub";
            }
        }


        // Requête de base pour afficher et trier les utilisateurs (utilisateurs.phtml lignes 136 et 275)
        $query = "SELECT * FROM `user`";

        // Si la clé "id" du tableau affecté à la clé "user" de la variable de session choixTri correspond à la valeur "triDesc",
        if($_SESSION["choixTri"]["user"]["id"] == "triDesc") {
            // on concatène à la variable query la valeur " ORDER BY `id` DESC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "user" par tri descendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` DESC LIMIT $debut, $limite";
        }

        // Ou bien si la clé "id" du tableau affecté à la clé "user" de la variable de session choixTri correspond à la valeur "triAsc",
        elseif($_SESSION["choixTri"]["user"]["id"] == "triAsc") {
            // on concatène à la variable query la valeur " ORDER BY `id` ASC LIMIT $debut, $limite". On sélectionne toutes les valeurs contenues dans la table "user" par tri ascendant de la colonne "id" et on les affichent dans la limite définie par les variables debut et limite
            $query .= " ORDER BY `id` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["usern"] == "triDesc") {
            $query .= " ORDER BY UPPER(`username`) DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["usern"] == "triAsc") {
            $query .= " ORDER BY UPPER(`username`) ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["mail"] == "triDesc") {
            $query .= " ORDER BY `email` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["mail"] == "triAsc") {
            $query .= " ORDER BY `email` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["pass"] == "triDesc") {
            $query .= " ORDER BY `password` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["pass"] == "triAsc") {
            $query .= " ORDER BY `password` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["nom"] == "triDesc") {
            $query .= " ORDER BY `nom` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["nom"] == "triAsc") {
            $query .= " ORDER BY `nom` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["prenom"] == "triDesc") {
            $query .= " ORDER BY `prenom` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["prenom"] == "triAsc") {
            $query .= " ORDER BY `prenom` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["adress"] == "triDesc") {
            $query .= " ORDER BY `adresse` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["adress"] == "triAsc") {
            $query .= " ORDER BY `adresse` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["zip"] == "triDesc") {
            $query .= " ORDER BY `code_postal` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["zip"] == "triAsc") {
            $query .= " ORDER BY `code_postal` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["ville"] == "triDesc") {
            $query .= " ORDER BY `ville` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["ville"] == "triAsc") {
            $query .= " ORDER BY `ville` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["pays"] == "triDesc") {
            $query .= " ORDER BY `pays` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["pays"] == "triAsc") {
            $query .= " ORDER BY `pays` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["tel"] == "triDesc") {
            $query .= " ORDER BY `tel` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["tel"] == "triAsc") {
            $query .= " ORDER BY `tel` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["reg"] == "triDesc") {
            $query .= " ORDER BY `date_reg` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["reg"] == "triAsc") {
            $query .= " ORDER BY `date_reg` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["tk"] == "triDesc") {
            $query .= " ORDER BY `token` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["tk"] == "triAsc") {
            $query .= " ORDER BY `token` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["conf"] == "triDesc") {
            $query .= " ORDER BY `conf_account` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["conf"] == "triAsc") {
            $query .= " ORDER BY `conf_account` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["tkStayc"] == "triDesc") {
            $query .= " ORDER BY `token_stayco` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["tkStayc"] == "triAsc") {
            $query .= " ORDER BY `token_stayco` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["newPass"] == "triDesc") {
            $query .= " ORDER BY `new_pass` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["newPass"] == "triAsc") {
            $query .= " ORDER BY `new_pass` ASC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["unsub"] == "triDesc") {
            $query .= " ORDER BY `date_unsub` DESC LIMIT $debut, $limite";
        }

        elseif($_SESSION["choixTri"]["user"]["unsub"] == "triAsc") {
            $query .= " ORDER BY `date_unsub` ASC LIMIT $debut, $limite";
        }

        $resultSet = $bdd->query($query);
        $users = $resultSet->fetchAll();


        if($users) {
            // On initialise la variable i avec la valeur 0
            $i = 0;

            foreach($users as $user) {
                // On stocke dans une variable de session la chaîne de caractères et la variable avec l'index id correspondants à l'attribut id de l'élément html button resetUser pour en récupérer la valeur dans le fichier varback.php (voir fichiers produits.phtml ligne 179, varback.php ligne 76 et ajax.js ligne 76)
                $_SESSION["loopElementId"][1][$i++] = "resetUser" . intval($user["id"]);
            }

            // On stocke le message de confirmation dans une variable de session pour en récupérer la valeur dans le fichier varback.php (dossier backoff, fichiers varback.php ligne 77 et ajax.js ligne 80)
            $_SESSION["loopMsgConfirm"] = "Voulez-vous vraiment supprimer ce compte utilisateur ? Certaines informations tels que l'adresse ou la date de supression seront conservées. Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";
        }


        // Si la variable post resetUser est déclarée et différente de NULL
        if(isset($_POST["resetUser"])) {
            $userId = htmlspecialchars($_POST["resetUser"]);
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification (fichier utilisateurs.phtml ligne 181)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/utilisateurs/utilisateurs?page=" . $page OR $referer == "https://importgames.llemaitre.com/backoff/users-page-" . $page) { */
                        // On définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française
                        date_default_timezone_set("Europe/Paris");
                        
                        // On efface les identifiants de connexion et on remet à zéro les paramètres d'activation et de récupération du compte de l'utilisateur
                        $query = "UPDATE `user` SET `username` = ?, `email` = ?, `password` = ?, `token` = NULL, `token_stayco` = NULL, `new_pass` = NULL, `date_unsub` = ? WHERE `id` = ?";
                        $bdd->insert($query, array("", "", "", date("Y-m-d H:i:s"), $userId));

                        header("location:/importgames/backoff/users-page-" . $page);
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page utilisateurs.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans utilisateurs.php
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