<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page ajoututilisateur.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier ajoututilisateur.php
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
        return "Ajouter un utilisateur";
    }


    // Si la variable post ajoutUser est déclarée et différente de NULL
    if(isset($_POST["ajoutUser"])) {
        $username = htmlspecialchars(trim($_POST["username"])); // On récupère le nom d'utilisateur
        $email = htmlspecialchars(strtolower(trim($_POST["email"]))); // On récupère l'adresse e-mail
        $password = trim($_POST["password"]); // On récupère le mot de passe
        $passConf = trim($_POST["passConf"]); // On récupère la confirmation du mot de passe
        $dateReg = htmlentities(trim($_POST["dateReg"])); // On récupère la date d'inscription
        $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
        $valid = true;

        // Vérification du nom d'utilisateur
        if(empty($username)) {
            $valid = false;
            $emptyUsern = true;
        }

        // On vérifie que le nom d'utilisateur est dans le bon format
        elseif(!preg_match("/^[0-9A-Za-zàäâçéèëêïîöôùüû_-]{3,16}$/", $username)) {
            $valid = false;
            $invalidUsern = true;
        }

        else {
            // On vérifie que le nom d'utilisateur est disponible
            $query = "SELECT `username` FROM `user` WHERE `username` = ?";
            $resultSet = $bdd->query($query, array($username));
            $usernVerif = $resultSet->fetch();

            if(isset($usernVerif["username"])) {
                $valid = false;
                $usedUsern = true;
            }
        }

        // Vérification de l'adresse e-mail
        if(empty($email)) {
            $valid = false;
            $emptyMail = true;
        }

        // On vérifie que l'adresse e-mail est dans le bon format
        elseif(!preg_match("/^[0-9a-z\-_.]+@[0-9a-z]+\.[a-z]{2,3}$/i", $email)) {
            $valid = false;
            $invalidMail = true;
        }

        else {
            // On vérifie que l'e-mail est disponible
            $query = "SELECT `email` FROM `user` WHERE `email` = ?";
            $resultSet = $bdd->query($query, array($email));
            $mailVerif = $resultSet->fetch();

            if(isset($mailVerif["email"])) {
                $valid = false;
                $usedMail = true;
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

        // Si on saisie le nom
        if($_POST["nom"]) {
            $nom = htmlspecialchars(trim($_POST["nom"])); // On récupère le nom de l'utilisateur

            // Vérification du nom
            if(empty($nom)) {
                $valid = false;
                $emptyNom = true;
            }

            // On vérifie que le nom est dans le bon format
            elseif(!preg_match("/^[A-Za-zàäâçéèëêïîöôùüû\s-]{3,}$/", $nom)) {
                $valid = false;
                $invalidNom = true;
            }
        }

        // Ou si on n'indique pas le nom
        else {
            // On affecte à la variable nom la valeur NULL
            $nom = NULL;
        }

        // Si on saisie le prénom
        if($_POST["prenom"]) {
            $prenom = htmlspecialchars(trim($_POST["prenom"])); // On récupère le prénom

            // Vérification du prénom
            if(empty($prenom)) {
                $valid = false;
                $emptyPrenom = true;
            }

            // On vérifie que le prénom est dans le bon format
            elseif(!preg_match("/^[A-Za-zàäâçéèëêïîöôùüû\s-]{3,}$/", $prenom)) {
                $valid = false;
                $invalidPrenom = true;
            }
        }

        // Ou si on n'indique pas le prénom
        else {
            // On affecte à la variable prenom la valeur NULL
            $prenom = NULL;
        }

        // Si on saisie l'adresse
        if($_POST["adresse"]) {
            $adresse = htmlspecialchars(trim($_POST["adresse"])); // On récupère l'adresse

            // Vérification de l'adresse
            if(empty($adresse)) {
                $valid = false;
                $emptyAdress = true;
            }

            // On vérifie que l'adresse est dans le bon format
            elseif(!preg_match("/^[0-9A-Za-zàäâçéèëêïîöôùüû\s-]{3,}$/", $adresse)) {
                $valid = false;
                $invalidAdresse = true;
            }
        }

        // Ou si on n'indique pas l'adresse
        else {
            // On affecte à la variable adresse la valeur NULL
            $adresse = NULL;
        }

        // Si on saisie le code postal
        if($_POST["zip"]) {
        $codePostal = htmlentities(trim($_POST["zip"])); // On récupère le code postal

            // Vérification du code postal
            if(empty($codePostal)) {
                $valid = false;
                $emptyZip = true;
            }

            // On vérifie que le code postal est dans le bon format
            elseif(!preg_match("/^[0-9]{5}$/", $codePostal)) {
                $valid = false;
                $invalidZip = true;
            }
        }

        // Ou si on n'indique pas le code postal
        else {
            // On affecte à la variable codePostal la valeur NULL
            $codePostal = NULL;
        }

        // Si on saisie la ville
        if($_POST["ville"]) {
            $ville = htmlspecialchars(trim($_POST["ville"])); // On récupère la ville

            // Vérification de la ville
            if(empty($ville)) {
                $valid = false;
                $emptyVille = true;
            }

            // On vérifie que la ville est dans le bon format
            elseif(!preg_match("/^[A-Za-zÀÄÂÇÉÈËÊÏÎÖÔÙÜÛàäâçéèëêïîöôùüû\s-]{3,}$/", $ville)) {
                $valid = false;
                $invalidVille = true;
            }
        }

        // Ou si on n'indique pas la ville
        else {
            // On affecte à la variable ville la valeur NULL
            $ville = NULL;
        }

        // Si on saisie le pays
        if($_POST["pays"]) {
            $pays = htmlspecialchars(trim($_POST["pays"])); // On récupère le pays

            // Vérification du champ de saisie "pays"
            if(empty($pays)) {
                $valid = false;
                $emptyPays = true;
            }

            // On vérifie que le pays est dans le bon format
            elseif(!preg_match("/^[A-Z]{2}$/", $pays)) {
                $valid = false;
                $invalidPays = true;
            }
        }

        // Ou si on n'indique pas le pays
        else {
            // On affecte à la variable pays la valeur NULL
            $pays = NULL;
        }

        // Si on saisie le numéro de téléphone
        if($_POST["tel"]) {
            $tel = htmlentities(trim($_POST["tel"])); // On récupère le numéro de téléphone

            // Vérification du numéro de téléphone
            if(empty($tel)) {
                $valid = false;
                $emptyTel = true;
            }

            // On vérifie que le numéro de téléphone est dans le bon format
            elseif(!preg_match("/[0][1679][- \.]?([0-9][0-9][- \.]?){4}$/", $tel)) {
                $valid = false;
                $invalidTel = true;
            }
        }

        // Ou si on n'indique pas le numéro de téléphone
        else {
            // On affecte à la variable tel la valeur NULL
            $tel = NULL;
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

        // Si toutes les conditions sont remplies alors on crée le compte utilisateur
        if($valid) {
            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/utilisateurs/ajoututilisateur" OR $referer == "https://importgames.llemaitre.com/backoff/ajoutuser") { */
                        // On utilise la fonction password_hash() pour haché notre mot de passe avec l'algorithme de hachage Argon2id
                        $hash = password_hash($password, PASSWORD_ARGON2I);

                        // On insert no données dans la table "user"
                        $query = "INSERT INTO `user` (`username`, `email`, `password`, `nom`, `prenom`, `adresse`, `code_postal`, `ville`, `pays`, `tel`, `date_reg`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                        $bdd->insert($query, array($username, $email, $hash, $nom, $prenom, $adresse, $codePostal, $ville, $pays, $tel, $dateReg));

                        header("location:/importgames/backoff/users-page-1");
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page ajoututilisateur.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans ajoututilisateur.php
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