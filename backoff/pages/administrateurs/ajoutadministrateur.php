<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page ajoutadministrateur.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier ajoutadministrateur.php
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
        return "Ajouter un administrateur";
    }


    // On utilise la variable selected pour sélectionner les privilèges par défaut de l'admin (ajoutadministrateur.phtml lignes 53 à 173)
    $selected = array("selected");


    // Si la variable post ajoutAdmin est déclarée et différente de NULL
    if(isset($_POST["ajoutAdmin"])) {
        $name = htmlspecialchars(trim($_POST["name"])); // On récupère le nom de l'admin
        $password = trim($_POST["password"]); // On récupère le mot de passe
        $passConf = trim($_POST["passConf"]); // On récupère la confirmation du mot de passe
        $dateReg = htmlentities(trim($_POST["dateReg"])); // On récupère la date d'inscription
        $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
        $valid = true;

        // Vérification du nom de l'admin
        if(empty($name)) {
            $valid = false;
            $emptyName = true;
        }

        // On vérifie que le nom est dans le bon format
        elseif(!preg_match("/^[0-9A-Za-zàäâçéèëêïîöôùüû_-]{3,16}$/", $name)) {
            $valid = false;
            $invalidName = true;
        }

        else {
            // On vérifie que le nom est disponible
            $query = "SELECT `name` FROM `admin` WHERE `name` = ?";
            $resultSet = $bdd->query($query, array($name));
            $nameVerif = $resultSet->fetch();

            if(isset($nameVerif["name"])) {
                $valid = false;
                $usedName = true;
            }
        }

        // Vérification du mot de passe
        if(empty($password)) {
            $valid = false;
            $emptyPass = true;
        }

        // On vérifie si le mot de passe contient 8 caractères alphanumériques au minimum
        elseif(!preg_match("/^[0-9A-Za-z]{8,}$/", $password)) {
            $valid = false;
            $invalidPass = true;
        }

        // Vérification si le mot de passe correspond au champ "Confirmer le mot de passe"
        elseif($password != $passConf) {
            $valid = false;
            $invalidPassConf = true;
        }

        // Vérification des privilèges
        if(empty($_POST["prv"])) {
            $valid = false;
            $emptyPrv = true;

            // On affecte à la variable selected un tableau vide
            $selected = array();
        }

        else {
            // On déclare la variable insertPrv
            $insertPrv = "";

            // On parcourt le tableau $_POST["prv"] et la valeur de l'élément courant est copié dans $val
            foreach($_POST["prv"] as $val) {
                // On affecte à la variable insertPrv sa propre valeur concaténée à celle de l'élément courant et de la chaîne de caractères ", " comme séparateur
                $insertPrv = htmlspecialchars($insertPrv . $val . ", ");
            }

            // On vérifie que les privilèges sont dans le bon format
            if(!preg_match("/^([0-9]{1,2}, )+$/", $insertPrv)) {
                $valid = false;
                $invalidPrv = true;
            }

            // On affecte à la variable selected la valeur de $_POST["prv"] pour garder la sélection des privilèges de l'admin après l'envoi du formulaire (ajoutadministrateur.phtml lignes 53 à 173)
            $selected = $_POST["prv"];
        }

        // Vérification de la date d'inscription
        if(empty($dateReg)) {
            $valid = false;
            $emptyDateReg = true;
        }

        // On vérifie que la date est dans le bon format
        elseif(!preg_match("/^\d{4}(-)(((0)[0-9])|((1)[0-2]))(-)([0-2][0-9]|(3)[0-1])$/", $dateReg)) {
            $valid = false;
            $invalidDateReg = true;
        }

        // Si toutes les conditions sont remplies alors on crée le compte administrateur
        if($valid) {
            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/administrateurs/ajoutadministrateur" OR $referer == "https://importgames.llemaitre.com/backoff/ajoutadmin") { */
                        // On utilise la fonction password_hash() pour haché notre mot de passe avec l'algorithme de hachage Argon2id
                        $hash = password_hash($password, PASSWORD_ARGON2I);

                        // On insert no données dans la table "admin"
                        $query = "INSERT INTO `admin` (`name`, `password`, `prv`, `date_reg`) VALUES (?, ?, ?, ?)";
                        $bdd->insert($query, array($name, $hash, substr($insertPrv, 0, -2), $dateReg));

                        header("location:/importgames/backoff/admins-page-1");
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page ajoutadministrateur.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans ajoutadministrateur.php
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