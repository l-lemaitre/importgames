<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page modifcategorie.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier modifcategorie.php
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

        if(in_array("3", $prv)):
            if(isset($_GET["id"])):
                $catId = htmlspecialchars($_GET["id"]);

                $query = "SELECT * FROM `categorie` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($catId));
                $cat = $resultSet->fetch();

                if(!isset($cat["id"])) return "Aucun contenu trouvé";

                elseif($cat["titre"]) return "Modifier catégorie " . $cat["titre"];

                else return "Remplacer catégorie #" . str_pad($cat["id"], 3, "0", STR_PAD_LEFT);

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    if(isset($_GET["id"])) {
        $catId = htmlspecialchars($_GET["id"]);

        $query = "SELECT * FROM `categorie` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($catId));
        $cat = $resultSet->fetch();


        // Si la variable post mdfCat est déclarée et différente de NULL
        if(isset($_POST["mdfCat"])) {
            $titre = htmlspecialchars(trim($_POST["titre"])); // On récupère le titre de la catégorie
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
            $valid = true;

            // Vérification du titre de la catégorie
            if(empty($titre)) {
                $valid = false;
                $emptyTitre = true;
            }

            // On vérifie que le titre de la catégorie est dans le bon format
            elseif(!preg_match("/^[A-Za-zàäâçéèëêïîöôùüû\s-]{3,16}$/", $titre)) {
                $valid = false;
                $invalidTitre = true;
            }

            // Si toutes les conditions sont remplies alors on met à jour la catégorie
            if($valid) {
                //On vérifie que les 2 jetons sont là
                if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                    // On vérifie que les deux correspondent
                    if($_SESSION["token"] == $formToken) {
                        // On enlève la vérification du Referer Header pour tester en localhost
                        /* $referer = $_SERVER["HTTP_REFERER"];

                        // On vérifie que la requête vient bien du formulaire
                        if($referer == "https://importgames.llemaitre.com/backoff/pages/categories/modifcategorie?id=" . $catId OR $referer == "https://importgames.llemaitre.com/backoff/modifcat-" . $catId) { */
                            // On modifie les colonnes contenant les informations de la catégorie
                            $query = "UPDATE `categorie` SET `titre` = ? WHERE `id` = ?";
                            $bdd->insert($query, array($titre, $catId));

                            header("location:/importgames/backoff/cats-page-1");
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
        $_SESSION["elementId"][1] = "resetCat";

        // Voir fichiers varback.php ligne 75 et ajax.js ligne 54
        $_SESSION["msgConfirm"] = "Voulez-vous vraiment supprimer cette catégorie ? Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";


        // Si la variable post formResetToken est déclarée et différente de NULL
        if(isset($_POST["formResetToken"])) {
            $formToken = htmlspecialchars(trim($_POST["formResetToken"])); // On récupère le token de vérification (fichier modifcategorie.phtml ligne 69)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/categories/modifcategorie?id=" . $catId OR $referer == "https://importgames.llemaitre.com/backoff/modifcat-" . $catId) { */
                        // On remet à zéro les colonnes contenant les informations de la catégorie
                        $query = "UPDATE `categorie` SET `titre` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($catId));

                        header("location:/importgames/backoff/cats-page-1");
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page modifcategorie.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans modifcategorie.php
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