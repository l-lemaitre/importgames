<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page ajoutpartenaire.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier ajoutpartenaire.php
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
        return "Ajouter un partenaire";
    }


    // Si la variable post ajoutPartn est déclarée et différente de NULL
    if(isset($_POST["ajoutPartn"])) {
        $nom = strip_tags(trim($_POST["nom"])); // On récupère le nom du partenaire
        $img = $_FILES["img"]; // On récupère l'image
        $url = htmlspecialchars(trim($_POST["url"])); // On récupère l'url
        $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
        $verifImg = true;
        $valid = true;

        // Vérification du nom du partenaire
        if(empty($nom)) {
            $valid = false;
            $emptyNom = true;
        }

        // On vérifie que le nom du partenaire est dans le bon format
        elseif(!preg_match("/^[0-9A-Za-zàäâçéèëêïîöôùüû&()\s\/_-]{3,}$/", $nom)) {
            $valid = false;
            $invalidNom = true;
        }

        // Vérification et sécurisation de l'image
        // On récupère le fichier ".tmp" dans le chemin du fichier temporaire 
        $source = $img["tmp_name"];

        // On récupère la taille du fichier
        $taille = $img["size"];

        // On récupère l'extension du fichier
        $extension = pathinfo($img["name"], PATHINFO_EXTENSION);

        // On renomme le fichier
        $nouvNom = rand(1111, 9999) . "_" . time();

        // On crée un chemin d'upload pour notre fichier
        $destination = SITE_DIR . "/../images/partenaire/";

        // On crée un tableau avec les extensions autorisées
        $legalExtensions = array("gif", "jpeg", "jpg", "png", "svg");

        // On crée une variable contenant la taille limite du fichier
        $tailleLimite = "5000000"; // 5000000 Octets = 5 Mo

        // On s'assure que le fichier n'est pas vide
        if(empty($source) || empty($taille)) {
            $verifImg = false;
            $emptyImg = true;
        }

        // On vérifie qu'un fichier portant le même nom n'est pas présent sur le serveur
        elseif(file_exists($destination . $nouvNom . "." . $extension)) {
            $verifImg = false;
        }

        // On vérifie si la taille actuelle du fichier est supérieure à la taille limite
        elseif($taille > $tailleLimite) {
            $verifImg = false;
        }

        // Si l'extension du fichier n'est pas dans notre tableau,
        elseif(!in_array($extension, $legalExtensions)) {
            // la verification de l'image est fausse
            $verifImg = false;
        }

        // Si la vérification de l'image échoue on bloque l'enregistrement du slide dans la bdd et on affiche le message d'erreur dans le fichier ajoutpartenaire.phtml ligne 37
        if(!$verifImg) {
            $valid = false;
            $errorImg = true;
        }

        // Vérification de l'url
        if(empty($url)) {
            $valid = false;
            $emptyUrl = true;
        }

        // On vérifie que l'url est dans le bon format
        elseif(!preg_match("/^(http:\/\/|https:\/\/)?(www\.)?([\w\.-]*)\.(fr|jp|com|net|org|biz|info|mobi|us|cc|bz|tv|ws|name|co|me)(\/[a-z]{2,3})?\z/i", $url)) {
            $valid = false;
            $invalidUrl = true;
        }

        // Si toutes les conditions sont remplies alors on crée le partenaire
        if($valid) {
            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/partenaires/ajoutpartenaire" OR $referer == "https://importgames.llemaitre.com/backoff/ajoutpartn") { */
                        // On insert no données dans la table "partenaire"
                        $query = "INSERT INTO `partenaire` (`nom`, `image`, `url`) VALUES (?, ?, ?)";
                        $bdd->insert($query, array($nom, "/importgames/images/partenaire/" . $nouvNom . "." . $extension, $url));

                        // On déplace le fichier téléchargé jusqu'au dossier correspondant à la constante SITE_DIR et au chemin "/../images/partenaire/" en le renommant et en lui ajoutant son extension (dossier backoff, dir.php)
                        move_uploaded_file($source, $destination . $nouvNom . "." . $extension);

                        header("location:/importgames/backoff/partns-page-1");
                        exit;
                    /* }

                    // La requête vient d'autre part donc on bloque
                    else {
                        $refError = true;
                    } */
                }

                else {
                    // Les tokens ne correspondent pas donc on ne modifie pas
                    $verifError = true;
                }
            }

            else {
                // Les tokens sont introuvables donc on ne modifie pas
                $verifError = true;
            }
        }
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page ajoutpartenaire.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans ajoutpartenaire.php
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