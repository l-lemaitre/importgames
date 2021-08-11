<?php
    // **PRÉVENTION DU PIRATAGE DE SESSION**
    // Empêche les attaques javascript XSS visant à voler l'ID de session
    ini_set("session.cookie_httponly", 1);

    // **PRÉVENTION DE LA FIXATION DE SESSION**
    // L'ID de session ne peut pas être transmis via les URL
    ini_set("session.use_only_cookies", 1);

    // Utilise une connexion sécurisée (HTTPS) si possible
    // ini_set("session.cookie_secure", 1);


    // Démarre une nouvelle session ou reprend une session existante
    session_start();


    //On crée un jeton d'authentification pour se protéger contre la faille CSRF (modifparametre.phtml lignes 43, 86, 97 et 109, modifadresse.phtml lignes 333, 345 et 358)
    $token = bin2hex(random_bytes(32));


    // Inclut le fichier placé en argument dir.php à la page bdd_connection.php (ligne 43)
    if(empty($dir)) {
        $dir = "dir";

        // On limite l'inclusion aux fichiers .php en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $dir = trim($dir . ".php");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $dir = str_replace("../", "protect", $dir);
    $dir = str_replace(";", "protect", $dir);
    $dir = str_replace("%", "protect", $dir);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $dir)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le serveur en utilisant la constante magique __DIR__ qui représente le dossier du fichier bdd_connection.php
        if(file_exists(__DIR__ . "/../" . $dir) && $dir != "index.php") {
           include __DIR__ . "/../" . $dir;
        }

        else {
            echo "Page inexistante.";
        }
    }


    // Inclut le fichier placé en argument fonctions_panier.php à la page bdd_connection.php (ligne 73)
    if(empty($panier)) {
        $panier = "fonctions_panier";

        // On limite l'inclusion aux fichiers .php en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $panier = trim($panier . ".php");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $panier = str_replace("../", "protect", $panier);
    $panier = str_replace(";", "protect", $panier);
    $panier = str_replace("%", "protect", $panier);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $panier)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le serveur en utilisant la constante SITE_DIR du fichier dir.php inclut ligne 43 plutôt que __DIR__ pour les fichiers à inclure se trouvant dans un dossier différent du fichier appelant (dir.php ligne 3)
        if(file_exists(SITE_DIR . "/application/" . $panier)) {
           include SITE_DIR . "/application/" . $panier;
        }

        else {
            echo "Page inexistante.";
        }
    }


    // Exécute la fonction creationPanier() présente dans le fichier fonctions_panier.php
    creationPanier();


    // Établi une connexion avec la base de données "importgames"
    $pdo = new PDO("mysql:host=localhost;dbname=importgames;charset=UTF8", "UTILISATEUR", "MOT DE PASSE");


    // Requête SQL : Sélectionne et va chercher toutes les valeurs contenues dans la table "categorie" de la base de données où la valeur de la colonne "titre" n'est pas NULL (utilisé notamment pour afficher le titre des catégories dans la barre de navigation, fichier layout.phtml ligne 80)
    $query = "SELECT * FROM `categorie` WHERE `titre` IS NOT NULL";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute();
    $cats = $resultSet->fetchAll();


    // Fonction pour remplacer les caractères accentués par leur équivalent sans accents (layout.phtml ligne 81)
    function replaceAccents($contenu) {
        // On utilise un tableau pour que la fonction strtr ligne 110 ne traite pas les caractères spéciaux comme de l'ISO (source post "allixsenos at gmail dot com" https://www.php.net/manual/fr/function.strtr.php)
        $accentChars = array(
            "Š"=>"S", "š"=>"s", "Đ"=>"Dj", "đ"=>"dj", "Ž"=>"Z", "ž"=>"z", "Č"=>"C", "č"=>"c", "Ć"=>"C", "ć"=>"c",
            "À"=>"A", "Á"=>"A", "Â"=>"A", "Ã"=>"A", "Ä"=>"A", "Å"=>"A", "Æ"=>"A", "Ç"=>"C", "È"=>"E", "É"=>"E",
            "Ê"=>"E", "Ë"=>"E", "Ì"=>"I", "Í"=>"I", "Î"=>"I", "Ï"=>"I", "Ñ"=>"N", "Ò"=>"O", "Ó"=>"O", "Ô"=>"O",
            "Õ"=>"O", "Ö"=>"O", "Ø"=>"O", "Ù"=>"U", "Ú"=>"U", "Û"=>"U", "Ü"=>"U", "Ý"=>"Y", "Þ"=>"B", "ß"=>"Ss",
            "à"=>"a", "á"=>"a", "â"=>"a", "ã"=>"a", "ä"=>"a", "å"=>"a", "æ"=>"a", "ç"=>"c", "è"=>"e", "é"=>"e",
            "ê"=>"e", "ë"=>"e", "ì"=>"i", "í"=>"i", "î"=>"i", "ï"=>"i", "ð"=>"o", "ñ"=>"n", "ò"=>"o", "ó"=>"o",
            "ô"=>"o", "õ"=>"o", "ö"=>"o", "ø"=>"o", "ù"=>"u", "ú"=>"u", "û"=>"u", "ý"=>"y", "ý"=>"y", "þ"=>"b",
            "ÿ"=>"y", "Ŕ"=>"R", "ŕ"=>"r");
       
        return strtr($contenu, $accentChars);
    }


    // On affecte à la variable currentHeader l'url de la page actuelle
    $currentHeader = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

    if(isset($_SESSION["prodHeader"])) {
        // Si la valeur de la variable currentHeader est différente de celle de la variable session prodHeader,
        if($currentHeader != $_SESSION["prodHeader"]) {
            // on détruit les variables de session prodCalled et prodHeader (voir fichier produit.php lignes 227 et 229)
            unset($_SESSION["prodCalled"]);
            unset($_SESSION["prodHeader"]);
        }
    }
?>