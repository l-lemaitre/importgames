<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page modifproduit.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier modifproduit.php
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

        if(in_array("9", $prv)):
            if(isset($_GET["id"])):
                $prodId = htmlspecialchars($_GET["id"]);

                $query = "SELECT * FROM `produit` WHERE `id` = ?";
                $resultSet = $bdd->query($query, array($prodId));
                $prod = $resultSet->fetch();

                if(!isset($prod["id"])) return "Aucun contenu trouvé";

                elseif($prod["cat_id"]) return "Modifier produit " . $prod["titre"];

                else return "Remplacer produit #" . str_pad($prod["id"], 3, "0", STR_PAD_LEFT);

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans modifproduit.phtml ligne 29
    function couperTitre($contenu) {
        $length = 33; // On veut les 33 premiers caractères pour les appareils mobiles

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";
            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    if(isset($_GET["id"])) {
        $prodId = htmlspecialchars($_GET["id"]);

        $query = "SELECT * FROM `produit` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($prodId));
        $prod = $resultSet->fetch();


        // On sélectionne toutes les valeurs contenues dans la table "categorie" de la base de données où la valeur de la colonne `titre` n'est pas NULL (modifproduit.phtml ligne 67)
        $query = "SELECT * FROM `categorie` WHERE `titre` IS NOT NULL";
        $resultSet = $bdd->query($query);
        $cats = $resultSet->fetchAll();


        // Voir fichiers varback.php ligne 74 et ajax.js ligne 68
        $_SESSION["elementId"][2] = "catId";

        // Si l'id catégorie du produit est attribué,
        if(isset($prod["cat_id"])) {
            $query = "SELECT * FROM `categorie` WHERE `id` = ?";
            $resultSet = $bdd->query($query, array($prod["cat_id"]));
            $cat = $resultSet->fetch();

            // si le titre da la catégorie du produit est attribué,
            if($cat["titre"]) {
                // on affecte à l'index 2 du tableau de la variable de session selected la valeur de $prod["cat_id"] pour afficher l'id et le titre de la catégorie actuel du produit (voir modifproduit.phtml lignes 68, varback.php ligne 82 et ajax.js ligne 68)
                $_SESSION["selected"][2] = $prod["cat_id"];
            }

            else {
                $_SESSION["selected"][2] = "1";
            }
        }

        else {
            // On affecte à l'index 2 du tableau de la variable de session selected la valeur "1"
            $_SESSION["selected"][2] = "1";
        }


        // Si la variable post mdfProd est déclarée et différente de NULL
        if(isset($_POST["mdfProd"])) {
            $catId = htmlspecialchars(trim($_POST["catId"])); // On récupère l'id de la catégorie du produit
            $titre = strip_tags(trim($_POST["titre"])); // On récupère le titre
            $ean13 = htmlspecialchars(trim($_POST["ean13"])); // On récupère le code-barres
            $prix = htmlspecialchars(trim($_POST["prix"])); // On récupère le prix
            $qte = htmlspecialchars(trim($_POST["qte"])); // On récupère la quantité
            $descript = strip_tags(trim($_POST["descript"])); //  On récupère la description
            $dateCrea = htmlspecialchars(trim($_POST["dateCrea"])); // On récupère la date de création
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification
            $valid = true;

            // Vérification de l'id catégorie du produit
            if(empty($catId)) {
                $valid = false;
                $emptyCatId = true;
            }

            // On vérifie que l'id de la catégorie est dans le bon format
            elseif(!preg_match("/^[0-9]+$/", $catId)) {
                $valid = false;
                $invalidCatId = true;
            }

            // On affecte à l'index 2 du tableau de la variable de session selected la valeur de $catId pour afficher l'id et le titre de la catégorie du produit après l'envoi du formulaire
            $_SESSION["selected"][2] = $catId;

            // Vérification du titre
            if(empty($titre)) {
                $valid = false;
                $emptyTitre = true;
            }

            // Vérification du code-barres
            if(empty($ean13)) {
                $valid = false;
                $emptyEan = true;
            }

            // On vérifie que le code-barres est dans le bon format
            elseif(!preg_match("/^[0-9]{12,14}$/", $ean13)) {
                $valid = false;
                $invalidEan = true;
            }

            // Vérification du prix
            if(empty($prix)) {
                $valid = false;
                $emptyPrix = true;
            }

            // On vérifie que le prix est dans le bon format
            elseif(!preg_match("/^[0-9]*\.?[0-9]+$/", $prix)) {
                $valid = false;
                $invalidPrix = true;
            }

            // Vérification de la quantité
            if($qte == NULL) {
                $valid = false;
                $emptyQte = true;
            }

            // On vérifie que la quantité est dans le bon format
            elseif(!preg_match("/^[0-9]+$/", $qte)) {
                $valid = false;
                $invalidQte = true;
            }

            // Si on saisi la date de sortie
            if($_POST["dateSortie"]) {
                $dateSortie = htmlspecialchars(trim($_POST["dateSortie"])); // On récupère la date de sortie

                // Vérification de la date de sortie
                if(empty($dateSortie)) {
                    $valid = false;
                    $emptyDateSortie = true;
                }

                // On vérifie que la date est dans le bon format
                elseif(!preg_match("/^\d{4}(-)(((0)[0-9])|((1)[0-2]))(-)([0-2][0-9]|(3)[0-1])$/", $dateSortie)) {
                    $valid = false;
                    $invalidDateS = true;
                }
            }

            // Ou si on n'indique pas la date de sortie
            else {
                // On affecte à la variable dateSortie la valeur NULL
                $dateSortie = NULL;
            }

            // Vérification de la description
            if(empty($descript)){
                $valid = false;
                $emptyDescript = true;
            }

            // Si le nom du fichier image existe
            if($_FILES["prevImg"]["name"]) {
                $prevImg = $_FILES["prevImg"]; // On récupère le fichier de l'aperçu image
                $verifPrevImg = true;

                // Vérification et sécurisation de l'aperçu image
                // On récupère le fichier ".tmp" dans le chemin du fichier temporaire
                $sourceImg = $prevImg["tmp_name"];

                // On récupère la taille du fichier
                $tailleImg = $prevImg["size"];

                // On récupère l'extension du fichier
                $extensionImg = pathinfo($prevImg["name"], PATHINFO_EXTENSION);

                // On renomme le fichier
                $nouvNomImg = rand(1000, 9999) . "_" . date("d-m-Y", time());

                // On crée un chemin d'upload pour notre fichier
                $destinationImg = SITE_DIR . "/../images/produit/";

                // On crée un tableau avec les extensions autorisées
                $legalExtensions = array("gif", "jpeg", "jpg", "png", "svg");

                // On crée une variable contenant la taille limite du fichier
                $tailleLimiteImg = "500000"; // 500000 Octets = 500 Kilooctets

                // On s'assure que le fichier n'est pas vide
                if(empty($sourceImg) || empty($tailleImg)) {
                    $verifPrevImg = false;
                    $emptyPrevImg = true;
                }

                // On vérifie qu'un fichier portant le même nom n'est pas présent sur le serveur
                elseif(file_exists($destinationImg . $nouvNomImg . "." . $extensionImg)) {
                    $verifPrevImg = false;
                }

                // On vérifie si la taille actuelle du fichier est supérieure à la taille limite
                elseif($tailleImg > $tailleLimiteImg) {
                    $verifPrevImg = false;
                }

                // Si l'extension du fichier n'est pas dans notre tableau,
                elseif(!in_array($extensionImg, $legalExtensions)) {
                    // la verification de l'image est fausse
                    $verifPrevImg = false;
                }

                // Si la vérification de l'image échoue on bloque l'enregistrement du produit dans la bdd et on affiche le message d'erreur dans le fichier modifproduit.phtml ligne 138,
                if(!$verifPrevImg) {
                    $valid = false;
                    $errorPrevImg = true;
                }

                else {
                    // sinon on affecte à la variable prevImgChosen le chemin absolu du dossier de destination concaténé au nouveau nom et à l'extension du fichier
                    $prevImgChosen = "/importgames/images/produit/" . $nouvNomImg . "." . $extensionImg;
                }
            }

            // Ou si on ne charge pas une nouvelle image,
            else {
                // on affecte à la variable prevImgChosen la valeur actuelle de la colonne "apercu_img" du produit à modifier
                $prevImgChosen = $prod["apercu_img"];
            }

            // Si les deux champs vidéos sont remplis on bloque l'enregistrement dans la bdd et on affiche le message d'erreur dans le fichier modifproduit.phtml ligne 162
            if($_POST["srcIframe"] AND $_FILES["video"]["name"]) {
                $valid = false;
                $error2Vid = true;
            }

            // Si la variable post srcIframe est différente de false
            elseif($_POST["srcIframe"]) {
                $srcIframe = htmlspecialchars(trim($_POST["srcIframe"])); // On récupère le lien de streaming vidéo

                // Vérification du lien de streaming vidéo
                if(empty($srcIframe)) {
                    $valid = false;
                    $emptySrc = true;
                }

                // On vérifie que le lien est dans le bon format
                elseif(!preg_match("/^(http:\/\/|https:\/\/)?(www\.)?([\w\.-]*)\.(fr|jp|com|net|org|biz|info|mobi|us|cc|bz|tv|ws|name|co|me)([0-9a-z\/?=_-]*)?\z/i", $srcIframe)) {
                    $valid = false;
                    $invalidSrc = true;
                }

                else {
                    // On affecte à la variable videoChosen la valeur de la variable srcIframe
                    $videoChosen = $srcIframe;
                }
            }

            // Ou bien si le nom du fichier video existe
            elseif($_FILES["video"]["name"]) {
                $video = $_FILES["video"]; // On récupère la vidéo
                $verifVideo = true;

                // Vérification et sécurisation de la vidéo
                // On récupère le fichier ".tmp" dans le chemin du fichier temporaire
                $sourceVid = $video["tmp_name"];

                // On récupère la taille du fichier
                $tailleVid = $video["size"];

                // On récupère l'extension du fichier
                $extensionVid = pathinfo($video["name"], PATHINFO_EXTENSION);

                // On renomme le fichier
                $nouvNomVid = rand(1000, 9999) . "_" . date("d-m-Y", time());

                // On crée un chemin d'upload pour notre fichier
                $destinationVid = SITE_DIR . "/../videos/produit/";

                $legalExtension = "mp4";

                // On crée une variable contenant la taille limite du fichier
                $tailleLimiteVid = "100000000"; // 100000000 Octets = 100 Mo

                // On s'assure que le fichier n'est pas vide
                if(empty($sourceVid) || empty($tailleVid)) {
                    $verifVideo = false;
                    $emptyVid = true;
                }

                // On vérifie qu'un fichier portant le même nom n'est pas présent sur le serveur
                elseif(file_exists($destinationVid . $nouvNomVid . "." . $extensionVid)) {
                    $verifVideo = false;
                }

                // On vérifie si la taille actuelle du fichier est supérieure à la taille limite
                elseif($tailleVid > $tailleLimiteVid) {
                    $verifVideo = false;
                }

                // Si l'extension du fichier est différente de la valeur de $legalExtension,
                elseif($extensionVid != $legalExtension) {
                    // la verification de la vidéo est fausse
                    $verifVideo = false;
                }

                // Si la vérification de la vidéo échoue on bloque l'enregistrement dans la bdd et on affiche le message d'erreur dans le fichier modifproduit.phtml ligne 166
                if(!$verifVideo) {
                    $valid = false;
                    $errorVid = true;
                }

                else {
                    // On affecte à la variable videoChosen le chemin absolu du dossier de destination concaténé au nouveau nom et à l'extension du fichier
                    $videoChosen = "/importgames/videos/produit/" . $nouvNomVid . "." . $extensionVid;
                }
            }

            // Ou bien si en utilisant la fonction substr le segment de chaîne défini par la fonction strrchr sur la variable prod["video"] à partir de "." et commençant au deuxième caractère est égal à "mp4"
            elseif(substr(strrchr($prod["video"], "."), 1) == "mp4") {
                // On affecte à la variable "videoChosen" la valeur actuelle de la colonne "video" du produit à modifier
                $videoChosen = $prod["video"];
            }

            // Ou si aucune des conditions lignes 293, 299, 321 ou 381 ne sont remplies
            else {
                // On affecte à la variable "videoChosen" la valeur NULL
                $videoChosen = NULL;
            }

            // Vérification de la date de création
            if(empty($dateCrea)) {
                $valid = false;
                $emptyDateCrea = true;
            }

            // On vérifie que la date est dans le bon format
            elseif(!preg_match("/^\d{4}(-)(((0)[0-9])|((1)[0-2]))(-)([0-2][0-9]|(3)[0-1])$/", $dateCrea)) {
                $valid = false;
                $invalidDateCrea = true;
            }

            // Si toutes les conditions sont remplies alors on met à jour la fiche produit
            if($valid) {
                //On vérifie que les 2 jetons sont là
                if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                    // On vérifie que les deux correspondent
                    if($_SESSION["token"] == $formToken) {
                        // On enlève la vérification du Referer Header pour tester en localhost
                        /* $referer = $_SERVER["HTTP_REFERER"];

                        // On vérifie que la requête vient bien du formulaire
                        if($referer == "https://importgames.llemaitre.com/backoff/pages/produits/modifproduit?id=" . $prodId OR $referer == "https://importgames.llemaitre.com/backoff/modifprod-" . $prodId) { */
                            // On modifie les colonnes contenant les informations de l'utilisateur
                            $query = "UPDATE `produit` SET `cat_id` = ?, `titre` = ?, `ean13` = ?, `prix` = ?, `qte` = ?, `date_sortie` = ?, `description` = ?, `apercu_img` = ?, `video` = ?, `date_creation` = ? WHERE `id` = ?";
                            $bdd->insert($query, array($catId, $titre, $ean13, $prix, $qte, $dateSortie, $descript, $prevImgChosen, $videoChosen, $dateCrea, $prodId));

                            if(isset($verifPrevImg)) {
                                // On déplace le fichier téléchargé jusqu'au dossier correspondant à la constante SITE_DIR et au chemin "/../images/produit/" en le renommant et en lui ajoutant son extension (ligne 244, fichier dir.php)
                                move_uploaded_file($sourceImg, $destinationImg . $nouvNomImg . "." . $extensionImg);
                            }

                            if(isset($verifVideo)) {
                                move_uploaded_file($sourceVid, $destinationVid . $nouvNomVid . "." . $extensionVid);
                            }

                            header("location:/importgames/backoff/prods-page-1");
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
        $_SESSION["elementId"][1] = "resetProd";

        // Voir fichiers varback.php ligne 75 et ajax.js ligne 54
        $_SESSION["msgConfirm"] = "Voulez-vous vraiment supprimer cette fiche produit ? Vous pourrez réutiliser son emplacement avec le bouton REMPLACER.";


        // Si la variable post formResetToken est déclarée et différente de NULL
        if(isset($_POST["formResetToken"])) {
            $formToken = htmlspecialchars(trim($_POST["formResetToken"])); // On récupère le token de vérification (fichier modifproduit.phtml ligne 192)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/pages/produits/modifproduit?id=" . $prodId OR $referer == "https://importgames.llemaitre.com/backoff/modifprod-" . $prodId) { */
                        // On remet à zéro les colonnes contenant les informations du produit
                        $query = "UPDATE `produit` SET `cat_id` = NULL, `titre` = NULL, `ean13` = NULL, `prix` = NULL, `qte` = NULL, `date_sortie` = NULL, `description` = NULL, `apercu_img` = NULL, `video` = NULL, `date_creation` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($prodId));

                        header("location:/importgames/backoff/prods-page-1");
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

    // On récupère le nom du fichier courant pour inclure le contenu de la page modifproduit.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans modifproduit.php
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