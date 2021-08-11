<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page modifimage.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier modifimage.php
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

        if(in_array("14", $prv)):
            if(isset($_GET["id"])):
                $imgId = htmlspecialchars($_GET["id"]);

                $query = "SELECT * FROM `image` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($imgId));
                $img = $resultSet->fetch();

                if(!isset($img["id"])) return "Aucun contenu trouvé";

                elseif($img["produit_id"]) return "Modifier image #" . str_pad($img["id"], 4, "0", STR_PAD_LEFT);

                else return "Remplacer image #" . str_pad($img["id"], 4, "0", STR_PAD_LEFT);

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    if(isset($_GET["id"])) {
        $imgId = htmlspecialchars($_GET["id"]);

        $query = "SELECT * FROM `image` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($imgId));
        $img = $resultSet->fetch();


        // On sélectionne toutes les valeurs contenues dans la table "produit" de la base de données où la valeur de la colonne `cat_id` n'est pas NULL (modifimage.phtml ligne 50)
        $query = "SELECT * FROM `produit` WHERE `cat_id` IS NOT NULL";
        $resultSet = $bdd->query($query);
        $prods = $resultSet->fetchAll();


        // Voir fichiers varback.php ligne 74 et ajax.js ligne 68
        $_SESSION["elementId"][2] = "prodId";

        // Si l'id produit de l'image est attribué,
        if(isset($img["produit_id"])) {
            $query = "SELECT * FROM `produit` WHERE `id` = ?";
            $resultSet = $bdd->query($query, array($img["produit_id"]));
            $prod = $resultSet->fetch();

            // si l'id catégorie du produit de l'image est attribué,
            if($prod["cat_id"]) {
                // on affecte à l'index 2 du tableau de la variable de session selected la valeur de $img["produit_id"] pour afficher l'id et le titre du produit actuel de l'image (voir modifimage.phtml lignes 51, varback.php ligne 82 et ajax.js ligne 68)
                $_SESSION["selected"][2] = $img["produit_id"];
            }

            else {
                $_SESSION["selected"][2] = "1";
            }
        }

        else {
            // On affecte à l'index 2 du tableau de la variable de session selected la valeur "1"
            $_SESSION["selected"][2] = "1";
        }


        // Si la variable post mdfImg est déclarée et différente de NULL
        if(isset($_POST["mdfImg"])) {
            $prodId = htmlspecialchars(trim($_POST["prodId"])); // On récupère l'Id produit de l'image
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
            $valid = true;

            // Vérification de l'Id produit de l'image
            if(empty($prodId)) {
                $valid = false;
                $emptyProdId = true;
            }

            // On vérifie que l'Id produit est dans le bon format
            elseif(!preg_match("/^[0-9]+$/", $prodId)) {
                $valid = false;
                $invalidProdId = true;
            }

            // On affecte à l'index 2 du tableau de la variable de session selected la valeur de $prodId pour afficher l'id et le titre du produit de l'image après l'envoi du formulaire
            $_SESSION["selected"][2] = $prodId;

            // Si le nom du fichier image existe
            if($_FILES["imgUrl"]["name"]) {
                $imgUrl = $_FILES["imgUrl"]; // On récupère l'image
                $verifImg = true;

                // Vérification et sécurisation de l'image
                // On récupère le fichier ".tmp" dans le chemin du fichier temporaire
                $source = $imgUrl["tmp_name"];

                // On récupère la taille du fichier
                $taille = $imgUrl["size"];

                // On récupère l'extension du fichier
                $extension = pathinfo($imgUrl["name"], PATHINFO_EXTENSION);

                // On renomme le fichier
                $nouvNom = rand(1000, 9999) . "_" . date("d-m-Y", time());

                // On crée un chemin d'upload pour notre fichier
                $query = "SELECT `cat_id` FROM `produit` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($prodId));
                $prod = $resultSet->fetch();

                if($prod["cat_id"] == 1) {
                    $destination = SITE_DIR . "/../images/image/jeux/";
                }

                elseif($prod["cat_id"] == 2) {
                    $destination = SITE_DIR . "/../images/image/figurines/";
                }

                elseif($prod["cat_id"] == 3) {
                    $destination = SITE_DIR . "/../images/image/papeterie/";
                }

                elseif($prod["cat_id"] > 3) {
                    $destination = SITE_DIR . "/../images/image/nouveau/";
                }

                else {
                    $verifImg = false;
                }

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

                // Si la vérification de l'image échoue on bloque l'enregistrement de l'image dans la bdd et on affiche le message d'erreur dans le fichier modifimage.phtml ligne 71,
                if(!$verifImg) {
                    $valid = false;
                    $errorImg = true;
                }

                else {
                    // sinon on affecte à la variable imgChosen le chemin absolu du dossier de destination concaténé au nouveau nom et à l'extension du fichier
                    $imgChosen = str_replace(SITE_DIR . "/../", "/importgames/", $destination) . $nouvNom . "." . $extension;
                }
            }

            // Ou si on ne charge pas une nouvelle image,
            else {
                // on affecte à la variable imgChosen la valeur actuelle de la colonne "url" de l'image à modifier
                $imgChosen = $img["url"];
            }

            // Si toutes les conditions sont remplies alors on met à jour l'image
            if($valid) {
                //On vérifie que les 2 jetons sont là
                if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                    // On vérifie que les deux correspondent
                    if($_SESSION["token"] == $formToken) {
                        // On enlève la vérification du Referer Header pour tester en localhost
                        /* $referer = $_SERVER["HTTP_REFERER"];

                        // On vérifie que la requête vient bien du formulaire
                        if($referer == "https://importgames.llemaitre.com/backoff/pages/images/modifimage?id=" . $imgId OR $referer == "https://importgames.llemaitre.com/backoff/modifimg-" . $imgId) { */
                            // On modifie les colonnes contenant les informations de l'image
                            $query = "UPDATE `image` SET `produit_id` = ?, `url` = ? WHERE `id` = ?";
                            $bdd->insert($query, array($prodId, $imgChosen, $imgId));

                            if(isset($verifImg)) {
                                // On déplace le fichier téléchargé jusqu'au dossier correspondant à la catégorie du produit en le renommant et en lui ajoutant son extension
                                move_uploaded_file($source, $destination . $nouvNom . "." . $extension);
                            }

                            header("location:/importgames/backoff/imgs-page-1");
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
        $_SESSION["elementId"][1] = "resetImg";

        // Voir fichiers varback.php ligne 75 et ajax.js ligne 54
        $_SESSION["msgConfirm"] = "Voulez-vous vraiment supprimer cette image ? Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";


        // Si la variable post formResetToken est déclarée et différente de NULL
        if(isset($_POST["formResetToken"])) {
            $formToken = htmlspecialchars(trim($_POST["formResetToken"])); // On récupère le token de vérification (fichier modifimage.phtml ligne 88)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/images/modifimage?id=" . $imgId OR $referer == "https://importgames.llemaitre.com/backoff/modifimg-" . $imgId) { */
                        // On remet à zéro les colonnes contenant les informations de l'image
                        $query = "UPDATE `image` SET `produit_id` = NULL, `url` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($imgId));

                        header("location:/importgames/backoff/imgs-page-1");
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page modifimage.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans modifimage.php
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