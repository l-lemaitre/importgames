<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page recherche.php (ligne 23)
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
        }
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return couperTitreFil($_GET["result"]);
    }


    // Fonction pour masquer le texte trop long dans le titre de la recherche et le remplacer par "..." dans recherche.phtml ligne 15
    function couperTitreEntete($contenu) {
        $length = 10; // On veut les 10 premiers caractères

        if(strlen($contenu) >= $length) { // Si la longueur de $contenu est plus grande ou égal à $length,
          $titreCoupe = substr($contenu, 0, $length) . "..."; // alors on garde $contenu à partir du début (0) jusqu'à $length (10) et tout ce qui vient ensuite est remplacé par "..."

          return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu; // Affiche le texte en entier si il contient moins de 10 caractères
        }
    }


    // Fonction pour masquer le texte trop long dans le titre de la recherche du fil d'Ariane et le remplacer par "..." dans recherche.phtml ligne 36
    function couperTitreFil($contenu) {
        $length = 58; // On veut les 58 premiers caractères

        if(strlen($contenu) >= $length) { // Si la longueur de $contenu est plus grande ou égal à $length,
          $titreCoupe = substr($contenu, 0, $length) . "..."; // alors on garde $contenu à partir du début (0) jusqu'à $length (58) et tout ce qui vient ensuite est remplacé par "..."

          return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu; // Affiche le texte en entier si il contient moins de 58 caractères
        }
    }


    // Fonction pour masquer le texte trop long dans le titre de la recherche du fil d'Ariane et le remplacer par "..." dans recherche.phtml ligne 37
    function couperTitreFil300($contenu) {
        $length = 10; // On veut les 10 premiers caractères pour les smartphones

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans recherche.phtml ligne 78
    function couperTitre($contenu) {
        $length = 29; // On veut les 29 premiers caractères

        if(strlen($contenu) >= $length) { // Si la longueur de $contenu est plus grande ou égal à $length,
          $titreCoupe = substr($contenu, 0, $length) . "..."; // alors on garde $contenu à partir du début (0) jusqu'à $length (29) et tout ce qui vient ensuite est remplacé par "..."

          return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu; // Affiche le texte en entier si il contient moins de 29 caractères
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans recherche.phtml ligne 79
    function couperTitre600($contenu) {
        $length = 17; // On veut les 17 premiers caractères pour les appareils mobiles

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans recherche.phtml ligne 80
    function couperTitre300($contenu) {
        $length = 14; // On veut les 14 premiers caractères pour les smartphones

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    if(isset($_GET["result"])) {
        // On récupère et sécurise le contenu de la variable GET "result" avec la fonction strip_tags plutôt que htmlspecialchars pour afficher correctement les caractères spéciaux
        $searchResult = strip_tags($_GET["result"]);

        // Sélectionne et va chercher toutes les valeurs contenues dans la table "produit" de la base de données si le titre ou le code barre des articles correspondent à la valeur de la recherche
        $query = "SELECT * FROM `produit` WHERE (`titre` LIKE :searchResult) OR (`ean13` LIKE :searchResult)";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute(array(":searchResult" => "%" . $searchResult . "%"));
        $prods = $resultSet->fetchAll();

        // On retourne le nombre de lignes affectées par le dernier appel à la fonction execute() sur la table `produit` pour récupérer le nombre total d'articles de la recherche
        $nbrProdsRec = $resultSet->rowCount();

        // La variable limite définit le nombre de lignes à affichées par page
        $limite = 8;
    }


    // Si la variable GET "page" n'est pas déclarée ou ne contient pas un ou plusieurs chiffres
    if(!isset($_GET["page"]) OR !preg_match("/^[0-9]+$/", $_GET["page"])) {
        if(isset($_GET["result"])) {
            // On définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française
            date_default_timezone_set("Europe/Paris");

            // On inscrit dans la base de données le sujet et le nombre de recherches sur le site
            $query = "INSERT INTO `recherche` (`texte`, `user_id`, `date_rec`) VALUES (?, ?, ?)";

            if(!isset($_SESSION["user_id"])) {
                $userId = 0;
            }

            else {
                $userId = $_SESSION["user_id"];
            }

            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$searchResult, $userId, date("Y-m-d H:i:s")]);
        }

        // On utilise la fonction urlencode sur la variable searchResult pour afficher correctement les caractères spéciaux et on ajoute la variable GET "page" à l'adresse de la page recherche
        header("location:recherche?result=" . urlencode($searchResult) . "&page=1");
    }

    else {
        //$_GET["page"] est une variable qui passera par le lien, elle commence à 1 donc pour accéder à la page commande.php il faut ajouter au lien ?page=1
        $page = htmlentities($_GET["page"]);

        // On stocke la variable page dans une variable de SESSION pour en récupérer la valeur dans le fichier var.php (voir fichiers var.php ligne 40 et ajax.js ligne 57)
        $_SESSION["var"] = $page;

        // La variable debut définit à partir de quelle ligne commence la sélection. Par défaut si $page=1 alors $debut=0 / si $page=2 alors $debut=(2-1)*5 = 5
        $debut = ($page - 1) * $limite;

        // On concatène les symboles "%" au début et à la fin de la variable searchResult pour rechercher dans la table "produit" tous les enregistrements qui utilisent les caractères contenus dans searchResult
        $prodSearch = "%" . $searchResult . "%";

        // Requête pour sélectionner les enregistrements par lot on commençant par le début 
        $query = "SELECT * FROM `produit` WHERE (`titre` LIKE :searchResult) OR (`ean13` LIKE :searchResult) ORDER BY `id` ASC LIMIT :debut, :limite";
        $resultSet = $pdo->prepare($query);
        $resultSet->bindParam(":searchResult", $prodSearch);
        $resultSet->bindParam(":debut", $debut, PDO::PARAM_INT);
        $resultSet->bindParam(":limite", $limite, PDO::PARAM_INT);
        $resultSet->execute();
        $prods = $resultSet->fetchAll();


        // Calcul le nombre de pages
        $nbrPages = ceil($nbrProdsRec / $limite);

        // Si le numéro de la page en cours est différent de 1 et plus grand que le nombre de pages total à afficher ou inférieur à 1 on retourne à la page 1
        if(($page <> 1) && ($page > $nbrPages) OR $page < 1) {
            header("location:recherche?result=" . urlencode($searchResult) . "&page=1");
        }

        // Ou bien si on est à la première page et que le nombre de pages pour afficher les lignes est supérieur à 1 on affiche le lien "Suivant >>" (voir recherche.phtml lignes 57 et 96)
        elseif(($page == 1) && ($nbrPages > 1)) {
            $navigation = true;
            $next = $page + 1;
        }

        // Ou bien si la variable page est inférieur au nombre total de pages on affiche "<< Precédent" et "Suivant >>" (voir recherche.phtml lignes 53 et 92)
        elseif($page < $nbrPages) {
            $navigation = true;
            $prev = $page - 1;
            $next = $page + 1;
        }

        // Ou bien si on est à la dernière page et que le nombre total de pages est supérieur à 1 on affiche seulement "<< Precédent" (voir recherche.phtml lignes 60 et 99)
        elseif(($page == $nbrPages) && ($nbrPages > 1)) {
            $navigation = true;
            $prev = $page - 1;
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page recherche.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans recherche.php
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