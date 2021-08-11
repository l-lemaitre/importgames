<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page modifadresse.php (ligne 23)
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
                // on charge la session de l'utilisateur retourné après l'authentification du cookie par la bdd,
                $_SESSION["user_id"] = htmlentities($user["id"]);
                $_SESSION["user"] = htmlspecialchars($user["username"]);
                $_SESSION["user_email"] = htmlspecialchars($user["email"]);
            }

            else {
                // sinon l'utilisateur est envoyé à la page connexion
                header("location:connexion");
                exit;
            }
        }

        else {
            // sinon on ne va pas sur cette page
            header("location:connexion");
            exit;
        }
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Modifier mon adresse";
    }


    // On récupère les informations de l'utilisateur connecté
    $query = "SELECT * FROM `user` WHERE `id` = ?";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute([$_SESSION["user_id"]]);
    $user = $resultSet->fetch();


    // Si la variable POST "modifAdresse" est déclarée et différente de NULL
    if(isset($_POST["modifAdresse"])) {
        $nom = htmlspecialchars(trim($_POST["nom"])); // On récupère le nom de l'utilisateur
        $prenom = htmlspecialchars(trim($_POST["prenom"])); // On récupère le prénom
        $adresse = htmlspecialchars(trim($_POST["adresse"])); // On récupère l'adresse
        $codePostal = htmlentities(trim($_POST["codePostal"])); // On récupère le code postal
        $ville = htmlspecialchars(trim($_POST["ville"])); // On récupère la ville
        $pays = htmlspecialchars(trim($_POST["pays"])); // On récupère le pays
        $tel = htmlentities(trim($_POST["tel"])); // On récupère le numéro de téléphone
        $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
        $valid = true;

        // Vérification du nom de l'utilisateur
        if(empty($nom)) {
            $valid = false;
            $emptyNom = true;
        }

        // On vérifie que le nom est dans le bon format
        elseif(!preg_match("/^[A-Za-zàäâçéèëêïîöôùüû\s'-]{3,}$/", $nom)) {
            $valid = false;
            $invalidNom = true;
        }

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

        // Vérification du code postal
        if(empty($codePostal)) {
            $valid = false;
            $emptyCdPost = true;
        }

        // On vérifie que le code postal est dans le bon format
        elseif(!preg_match("/^[0-9]{5,5}$/", $codePostal)) {
            $valid = false;
            $invalidCdPost = true;
        }

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

        // Si toutes les conditions sont remplies alors on passe au traitement
        if($valid) {
            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/modifadresse") { */
                        // On modifie l'adresse de l'utilisateur en mettant à jour nos données dans la table "user"
                        $query = "UPDATE `user` SET `nom` = ?, `prenom` = ?, `adresse` = ?, `code_postal` = ?, `ville` = ?, `pays` = ?, `tel` = ? WHERE `id` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$nom, $prenom, $adresse, $codePostal, $ville, $pays, $tel, $_SESSION["user_id"]]);

                        header("location:profil");
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


    // Si la variable POST "formResetToken" est déclarée et différente de NULL
    if(isset($_POST["formResetToken"])) {
        $formToken = htmlspecialchars(trim($_POST["formResetToken"])); // On récupère le token de vérification (fichier modifadresse.phtml ligne 345)

        //On vérifie que les 2 jetons sont là
        if(!empty($_SESSION["token"]) AND !empty($formToken)) {
            // On vérifie que les deux correspondent
            if($_SESSION["token"] == $formToken) {
                // On enlève la vérification du Referer Header pour tester en localhost
                /* $referer = $_SERVER["HTTP_REFERER"];

                // On vérifie que la requête vient bien du formulaire
                if($referer == "https://importgames.llemaitre.com/modifadresse") { */
                    // On remet à zéro les colonnes contenant l'adresse du client dans la table "user"
                    $query = "UPDATE `user` SET `nom` = NULL, `prenom` = NULL, `adresse` = NULL, `code_postal` = NULL, `ville` = NULL, `pays` = NULL, `tel` = NULL WHERE `id` = ?";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute([$_SESSION["user_id"]]);

                    header("location:profil");
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


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page modifadresse.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans modifadresse.php
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