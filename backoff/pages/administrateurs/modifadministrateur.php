<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page modifadministrateur.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier modifadministrateur.php
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

        if(in_array("21", $prv)):
            if(isset($_GET["id"])):
                $adminId = htmlspecialchars($_GET["id"]);

                $query = "SELECT * FROM `admin` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($adminId));
                $admin = $resultSet->fetch();

                if(!isset($admin["id"])) return "Aucun contenu trouvé";

                elseif($admin["name"]) return "Modifier administrateur " . $admin["name"];

                else return "Remplacer administrateur #" . str_pad($admin["id"], 3, "0", STR_PAD_LEFT);

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    if(isset($_GET["id"])) {
        $adminId = htmlspecialchars($_GET["id"]);

        $query = "SELECT * FROM `admin` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($adminId));
        $admin = $resultSet->fetch();


        // Si les privilèges de l'admin ne sont pas vides,
        if(isset($admin["prv"])) {
            // on crée un tableau avec la fonction explode en utilisant la chaîne de caractères ", " comme séparateur et la variable admin["prv"] comme chaîne initiale. On affecte à la variable selected la valeur du tableau pour sélectionner les privilèges de l'admin enregistrés dans la bdd,
            $selected = explode(", ", $admin["prv"]);
        }

        else {
            // sinon on sélectionne les privilèges par défaut (modifadministrateur.phtml lignes 109 à 229)
            $selected = array("selected");   
        }


        // Si la variable post mdfAdmin est déclarée et différente de NULL
        if(isset($_POST["mdfAdmin"])) {
            $name = htmlspecialchars(trim($_POST["name"])); // On récupère le nom de l'admin
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

                if($nameVerif && $nameVerif["name"] <> $admin["name"]) {
                    $valid = false;
                    $usedName = true;
                }
            }

            // Si on modifie le mot de passe
            if(!empty($_POST["password"]) OR $_POST["newPass"] OR $_POST["passConf"]) {
                $newPass = trim($_POST["newPass"]); // On récupère le nouveau mot de passe
                $passConf = trim($_POST["passConf"]); // On récupère la confirmation du mot de passe

                if(!empty($admin["password"])) {
                    $password = trim($_POST["password"]); // On récupère le mot de passe

                    // Vérification du mot de passe
                    if(empty($password)) {
                        $valid = false;
                        $emptyPass = true;
                    }

                    // On vérifie si le mot de passe utilisé correspond bien au hash présent dans la bdd à l'aide de password_verify
                    $correctPassword = password_verify($password, $admin["password"]);

                    if(!$correctPassword) {
                        $valid = false;
                        $passError = true;
                    }
                }

                // Vérification du nouveau mot de passe
                if(empty($newPass)) {
                    $valid = false;
                    $emptyNewPass = true;
                }

                // On vérifie si le mot de passe contient 8 caractères alphanumériques au minimum
                elseif(!preg_match("/^[0-9A-Za-z]{8,}$/", $newPass)) {
                    $valid = false;
                    $invalidNewPass = true;
                }

                // Vérification si le nouveau mot de passe correspond au champ "Confirmer le mot de passe"
                elseif($newPass != $passConf) {
                    $valid = false;
                    $invalidPassConf = true;
                }

                if($valid) {
                    // On utilise la fonction password_hash() pour haché notre nouveau mot de passe avec l'algorithme Argon2id
                    $hash = password_hash($newPass, PASSWORD_ARGON2I);

                    $passChosen = $hash;
                }
            }

            // Ou si on ne change pas le mot de passe
            else {
                // On affecte à la variable "passChosen" la valeur actuelle de la colonne "password" de l'admin à modifier
                $passChosen = $admin["password"];
            }

            // Vérification des privilèges
            if(empty($_POST["prv"])) {
                $valid = false;
                $emptyPrv = true;

                // On affecte à la variable selected un tableau vide
                $selected = array();
            }

            else {
                // On déclare la variable prvUpdate
                $prvUpdate = "";

                // On parcourt le tableau $_POST["prv"] et la valeur de l'élément courant est copié dans $val
                foreach($_POST["prv"] as $val) {
                    // On affecte à la variable prvUpdate sa propre valeur concaténée à celle de l'élément courant et de la chaîne de caractères ", " comme séparateur
                    $prvUpdate = htmlspecialchars($prvUpdate . $val . ", ");
                }

                // On vérifie que les privilèges sont dans le bon format
                if(!preg_match("/^([0-9]{1,2}, )+$/", $prvUpdate)) {
                    $valid = false;
                    $invalidPrv = true;
                }

                // On affecte à la variable selected la valeur de $_POST["prv"] pour garder la sélection des privilèges de l'admin après l'envoi du formulaire (modifadministrateur.phtml lignes 109 à 229)
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
                $invalidDatReg = true;
            }

            // Si toutes les conditions sont remplies alors on met à jour l'administrateur
            if($valid) {
                //On vérifie que les 2 jetons sont là
                if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                    // On vérifie que les deux correspondent
                    if($_SESSION["token"] == $formToken) {
                        // On enlève la vérification du Referer Header pour tester en localhost
                        /* $referer = $_SERVER["HTTP_REFERER"];

                        // On vérifie que la requête vient bien du formulaire
                        if($referer == "https://importgames.llemaitre.com/backoff/pages/administrateurs/modifadministrateur?id=" . $adminId OR $referer == "https://importgames.llemaitre.com/backoff/modifadmin-" . $adminId) { */
                            // On modifie les colonnes contenant les informations de l'administrateur
                            $query = "UPDATE `admin` SET `name` = ?, `password` = ?, `prv` = ?, `date_reg` = ? WHERE `id` = ?";
                            $bdd->insert($query, array($name, $passChosen, substr($prvUpdate, 0, -2), $dateReg, $adminId));

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


        // Voir fichiers varback.php ligne 74 et ajax.js ligne 50
        $_SESSION["elementId"][1] = "resetAdmin";

        // Voir fichiers varback.php ligne 75 et ajax.js ligne 54
        $_SESSION["msgConfirm"] = "Voulez-vous vraiment supprimer cet administrateur ? Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";


        // Si la variable post formResetToken est déclarée et différente de NULL
        if(isset($_POST["formResetToken"])) {
            $formToken = htmlspecialchars(trim($_POST["formResetToken"])); // On récupère le token de vérification (fichier modifadministrateur.phtml ligne 258)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/administrateurs/modifadministrateur?id=" . $adminId OR $referer == "https://importgames.llemaitre.com/backoff/modifadmin-" . $adminId) { */
                        // On remet à zéro les colonnes contenant les informations de l'administrateur
                        $query = "UPDATE `admin` SET `name` = NULL, `password` = NULL, `prv` = NULL, `date_reg` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($adminId));

                        header("location:/importgames/backoff/admins-page-1");
                        exit;
                    /* }

                    // La requête vient d'autre part donc on bloque
                    else {
                        $refResetError = true;
                    } */
                }

                else {
                    // Les tokens ne correspondent pas donc on ne modifie pas
                    $verifResetError = true;
                }
            }

            else {
                // Les tokens sont introuvables donc on ne modifie pas
                $verifResetError = true;
            }
        }
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page modifadministrateur.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans modifadministrateur.php
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