<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page detailcommande-client.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier detailcommande-client.php
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
        // Établi une connexion avec la base de données en créant une instance de la classe ConnexionBdd (fichier bdd_connection.php ligne 53)
        $bdd = new ConnexionBdd;

        // On retourne un tableau de chaînes de caractères à partir de la variable de session prv pour récupérer les privilèges de l'admin (layout.phtml lignes 43 à 181)
        $prv = explode(",", $_SESSION["prv"]);

        if(in_array("26", $prv) || in_array("27", $prv)):
            if(isset($_GET["id"])):
                $comId = htmlspecialchars($_GET["id"]);

                $query = "SELECT * FROM `commande` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($comId));
                $com = $resultSet->fetch();

                if(!isset($com["user_id"])) return "Aucune commande trouvée";

                else return "Commande #" . str_pad($com["id"], 4, "0", STR_PAD_LEFT);

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    // Fonction pour récupérer la date d'inscription de l'utilisateur où l'Id correspond à la valeur placée en argument (detailcommande-client.phtml ligne 47)
    function getDatebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `date_reg` FROM `user` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $user = $resultSet->fetch();

        return $user["date_reg"];
    }


    // Fonction pour afficher le nom d'utilisateur dont l'Id correspond à la valeur placée en argument (detailcommande-client.phtml ligne 51)
    function getNamebyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `username` FROM `user` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $user = $resultSet->fetch();

        return $user["username"];
    }


    if(isset($_GET["id"])) {
        // On récupère et sécurise le contenu de la variable get id
        $comId = htmlentities($_GET["id"]);

        // Requête SQL : Sélectionne et va chercher la valeur contenue dans la table "commande" de la base de données où l'Id correspond à celui affiché dans le header
        $query = "SELECT * FROM `commande` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($comId));
        $com = $resultSet->fetch();

        if(isset($com["id"])) {
            // Sélectionne et va chercher toutes les valeurs contenues dans la table "detail_com" de la base de données où "commande_id" correspond à l'Id de la commande de l'utilisateur
            $query = "SELECT * FROM `detail_com` WHERE `commande_id` = ? ORDER BY `produit_id`";
            $resultSet = $bdd->query($query, array($com["id"]));

            // On initialise 4 tableaux pour la table "detail_com"
            $prodId = array();
            $titre = array();
            $qte = array();
            $prix = array();

            // On effectue une boucle à l'aide d'un while qui va stocker les données issues de la base dans les tableaux pour afficher le détail des produits de la commande
            while($detail = $resultSet->fetch()) {
                $prodId[] = $detail["produit_id"];
                $titre[] = $detail["titre"];
                $qte[] = $detail["qte"];
                $prix[] = $detail["prix"];
            }

            // On va se servir des tableaux pour afficher le nombre de lignes de la commande (fichier detailcommande-client.phtml ligne 76)
            $nbrProdsCo = $resultSet->rowCount();

            // On ajoute dynamiquement une connexion avec la base de données "importgames" en incluant le fichier bdd_co_fonctions.php
            $bddFct = "bdd_co_fonctions";

            $bddFct = trim($bddFct . ".php");

            $bddFct = str_replace("../", "protect", $bddFct);
            $bddFct = str_replace(";", "protect", $bddFct);
            $bddFct = str_replace("%", "protect", $bddFct);

            if(!preg_match("/backoff/", $bddFct) && file_exists(SITE_DIR . "/../application/" . $bddFct)) {
               include SITE_DIR . "/../application/" . $bddFct;
            }

            // Sélectionne et va chercher la valeur contenue dans la table "produit" de la base de données où l'Id correspond à "produit_id" de la table "detail_com"
            $query = "SELECT * FROM `produit` WHERE `id` IN (" . implode(',', array_map([$pdo, "quote"], $prodId)) . ")";
            $resultSet = $bdd->query($query);

            // On initialise 2 tableaux pour la table "produit"
            $apercuImg = array();
            $dateCrea = array();

            while($prod = $resultSet->fetch()) {
                $apercuImg[] = $prod["apercu_img"];
                $dateCrea[] = $prod["date_creation"];
            }
        }
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page detailcommande-client.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans detailcommande-client.php
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