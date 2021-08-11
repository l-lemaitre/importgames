<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page facture-client.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier facture-client.php
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

        if(in_array("27", $prv)):
            if(isset($_GET["id"])):
                $comId = htmlspecialchars($_GET["id"]);

                $query = "SELECT * FROM `commande` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($comId));
                $com = $resultSet->fetch();

                if(!isset($com["user_id"])) return "Aucun contenu trouvé";

                else return "Facture #" . str_pad($com["id"], 4, "0", STR_PAD_LEFT);

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    if(isset($_GET["id"])) {
        // On récupère et sécurise le contenu de la variable get id
        $comId = htmlentities($_GET["id"]);

        // Requête SQL : Sélectionne et va chercher la valeur contenue dans la table "commande" de la base de données où l'id correspond à celui affiché dans le header
        $query = "SELECT * FROM `commande` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($comId));
        $com = $resultSet->fetch();

        if(isset($com["id"])) {
            // Sélectionne et va chercher toutes les valeurs contenues dans la table "detail_com" de la base de données où "commande_id" correspond à l'Id de la commande de l'utilisateur
            $query = "SELECT * FROM `detail_com` WHERE `commande_id` = ? ORDER BY `produit_id`";
            $resultSet = $bdd->query($query, array($com["id"]));
            $details = $resultSet->fetchAll();


            // On récupère le montant total de la facture-client pour l'afficher en Français (fichier facture-client.phtml ligne 122)
            $num = htmlentities($com["total"]);
            $exp = explode(".", $num);
            $nbrForm = new NumberFormatter("fr_FR", NumberFormatter::SPELLOUT);
            $totalFr = ucfirst($nbrForm->format($exp[0])) . " euros et " . ucfirst($nbrForm->format($exp[1])) . " centimes.";
        }
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page facture-client.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans facture-client.php
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