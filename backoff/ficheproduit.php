<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page ficheproduit.php (ligne 23)
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
        // On vérifie que la page est bien sur le "serveur" en utilisant la constante magique __DIR__ qui représente le dossier du fichier ficheproduit.php
        if(file_exists(__DIR__ . "/application/" . $bdd)) {
           include __DIR__ . "/application/" . $bdd;
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

        if(in_array("10", $prv)):
            if(isset($_GET["id"]) || isset($_GET["search"])):
                if(isset($_GET["id"])):
                    $prodId = htmlspecialchars($_GET["id"]);

                    $query = "SELECT * FROM `produit` WHERE `id` = ? AND `cat_id` IS NOT NULL";
                    $resultSet = $bdd->query($query, array($prodId));
                    $prod = $resultSet->fetch();

                elseif(isset($_GET["search"])):
                    $searchResult = strip_tags($_GET["search"]);

                    $query = "SELECT * FROM `produit` WHERE (`titre` LIKE :searchResult) OR (`ean13` LIKE :searchResult)";
                    $resultSet = $bdd->query($query, array(":searchResult" => "%" . $searchResult . "%"));
                    $prod = $resultSet->fetch();
                endif;

                if(!isset($prod["id"])) return "Aucun produit trouvé";

                else return "Fiche produit " . $prod["titre"];

            else:
                return "Erreur adresse HTTP";
            endif;

        else:
            return "Autorisation refusée";
        endif;
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans ficheproduit.phtml ligne 27
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


    // Fonction pour afficher le titre de la catégorie dont l'Id correspond à la valeur placée en argument (ficheproduit.phtml ligne 73)
    function getCatbyId($contenu) {
        $bdd = new ConnexionBdd;

        $query = "SELECT `titre` FROM `categorie` WHERE `id` = ?";
        $resultSet = $bdd->query($query, array($contenu));
        $cat = $resultSet->fetch();

        return $cat["titre"];
    }


    // Si on accède à cette page en passant par le lien "Commander" sur la page produits.phtml,
    if(isset($_GET["id"])) {
        $prodId = htmlspecialchars($_GET["id"]);

        $query = "SELECT * FROM `produit` WHERE `id` = ? AND `cat_id` IS NOT NULL";
        $resultSet = $bdd->query($query, array($prodId));
        $prod = $resultSet->fetch();
    }

    // ou bien si on y accède en faisant une recherche
    elseif(isset($_GET["search"])) {
        $searchResult = strip_tags($_GET["search"]);

        // Sélectionne et va chercher toutes les valeurs contenues dans la table "produit" de la base de données si le titre ou le code barre de l'article correspond à la valeur de la recherche
        $query = "SELECT * FROM `produit` WHERE (`titre` LIKE :searchResult) OR (`ean13` LIKE :searchResult)";
        $resultSet = $bdd->query($query, array(":searchResult" => "%" . $searchResult . "%"));
        $prod = $resultSet->fetch();
    }

    // Dans les deux cas précédents si la variable prod["id"] est déclarée et différente de NULL
    if(isset($prod["id"])) {
        $query = "SELECT * FROM `achat` WHERE `produit_id` IN (SELECT `id` FROM `produit` WHERE `date_creation` <= achat.date_a AND `titre` = achat.titre) AND `produit_id` = ? ORDER BY `id` DESC LIMIT 5";
        $resultSet = $bdd->query($query, array($prod["id"]));
        $achats = $resultSet->fetchAll();


        // On affecte à la variable total la valeur 0 (voir ficheproduit.phtml lignes 124 et 134)
        $total = 0;


        if($achats) {
            // On initialise la variable i avec la valeur 0
            $i = 0;

            foreach($achats as $achat) {
                // On stocke dans une variable de session la chaîne de caractères et la variable avec l'index id correspondants à l'attribut id de l'élément html button resetAchat pour en récupérer la valeur dans le fichier varback.php (voir fichiers ficheproduit.phtml ligne 117, varback.php ligne 76 et ajax.js ligne 76)
                $_SESSION["loopElementId"][1][$i++] = "resetAchat" . intval($achat["id"]);
            }

            // On stocke le message de confirmation dans une variable de session pour en récupérer la valeur dans le fichier varback.php (dossier backoff, fichiers varback.php ligne 77 et ajax.js ligne 80)
            $_SESSION["loopMsgConfirm"] = "Voulez-vous vraiment supprimer cette commande produit ? Cette action est irréversible.";
        }


        // Si la variable post resetAchat est déclarée et différente de NULL
        if(isset($_POST["resetAchat"])) {
            $achatId = htmlspecialchars($_POST["resetAchat"]);
            $formToken = htmlspecialchars(trim($_POST["formToken"])); // On récupère le token de vérification (ficheproduit.phtml ligne 119)

            //On vérifie que les 2 jetons sont là
            if(!empty($_SESSION["token"]) AND !empty($formToken)) {
                // On vérifie que les deux correspondent
                if($_SESSION["token"] == $formToken) {
                    // On enlève la vérification du Referer Header pour tester en localhost
                    /* $referer = $_SERVER["HTTP_REFERER"];

                    // On vérifie que la requête vient bien du formulaire
                    if($referer == "https://importgames.llemaitre.com/backoff/ficheproduit?id=" . $prod["id"] OR $referer == "https://importgames.llemaitre.com/backoff/ficheprod-" . $prod["id"] OR $referer == "https://importgames.llemaitre.com/backoff/ficheproduit?search=" . $searchResult) { */
                        // On sélectionne toutes les valeurs de la table "achat" dont l'Id correspond à celui de la variable achatId (ficheproduit.phtml ligne 117)
                        $query="SELECT * FROM `achat` WHERE `id` = ?";
                        $resultSet = $bdd->query($query, array($achatId));
                        $achatReset = $resultSet->fetch();

                        // On remet à zéro les colonnes contenant les informations de l'achat sélectionné
                        $query = "UPDATE `achat` SET `produit_id` = NULL, `titre` = NULL, `qte` = NULL, `prix` = NULL, `date_a` = NULL WHERE `id` = ?";
                        $bdd->insert($query, array($achatReset["id"]));

                        // On sélectionne la colonne "qte" de la table "produit" où la valeur de l'Id correspond à celle de l'achat supprimé pour récupérer le stock actuel du produit
                        $query="SELECT `qte` FROM `produit` WHERE `id` = ?";
                        $resultSet = $bdd->query($query, array($achatReset["produit_id"]));
                        $stockProd = $resultSet->fetch();

                        // Si le stock du produit est inférieur à la quantité de la commande à supprimer,
                        if($stockProd["qte"] < $achatReset["qte"]) {
                            // on affecte à la variable achatReset["qte"] la quantité du produit en stock pour arriver à une balance de 0
                            $achatReset["qte"] = $stockProd["qte"];
                        }

                        // On met à jour la quantité dans la table "produit"
                        $query="UPDATE `produit` SET `qte` = `qte` - ? WHERE `id` = ?";
                        $bdd->insert($query, array($achatReset["qte"], $achatReset["produit_id"]));

                        header("location:/importgames/backoff/ficheprod-" . $prod["id"]);
                        exit;
                    /* }

                    else {
                        // La requête vient d'autre part donc on bloque (voir fichier varback.php ligne 79 et ajax.js ligne 118)
                        $_SESSION["refReset"] = "refResetError";
                    } */
                }

                else {
                    // Les tokens ne correspondent pas donc on ne modifie pas
                    $_SESSION["verifReset"] = "verifResetError";
                }
            }

            else {
                // Les tokens sont introuvables donc on ne modifie pas (voir fichier varback.php ligne 78 et ajax.js ligne 113)
                $_SESSION["verifReset"] = "verifResetError";
            }
        }


        // Voir fichiers varback.php ligne 74 et ajax.js ligne 50
        $_SESSION["elementId"][1] = "comProd";

        // Voir fichiers varback.php ligne 75 et ajax.js ligne 54
        $_SESSION["msgConfirm"] = "Confirmez-vous l'envoi de cette commande ?";


        // Si la variable post comProdToken est déclarée et différente de NULL
        if(isset($_POST["comProdToken"])) {
            $prixAchat = htmlspecialchars(trim($_POST["prixAchat"])); // On récupère le prix d'achat
            $qte = htmlspecialchars(trim($_POST["qte"])); // On récupère la quantité
            $dateAchat = htmlspecialchars(trim($_POST["dateAchat"])); // On récupère la date d'achat
            $comProdToken = htmlspecialchars(trim($_POST["comProdToken"])); // On récupère le token de vérification
            $valid = true;

            // Vérification du prix d'achat
            if(empty($prixAchat)) {
                $valid = false;
                $emptyPrixA = true;
            }

            // On vérifie que le prix d'achat est dans le bon format
            elseif(!preg_match("/^[0-9]*\.?[0-9]+$/", $prixAchat)) {
                $valid = false;
                $invalidPrixA = true;
            }

            // Vérification de la quantité
            if(empty($qte)) {
                $valid = false;
                $emptyQte = true;
            }

            // On vérifie que la quantité est dans le bon format
            elseif(!preg_match("/^[0-9]+$/", $qte)) {
                $valid = false;
                $invalidQte = true;
            }

            // Vérification de la date d'achat
            if(empty($dateAchat)) {
                $valid = false;
                $emptyDateA = true;
            }

            // On vérifie que la date est dans le bon format
            elseif(!preg_match("/^\d{4}(-)(((0)[0-9])|((1)[0-2]))(-)([0-2][0-9]|(3)[0-1])$/", $dateAchat)) {
                $valid = false;
                $invalidDateA = true;
            }

            // Si toutes les conditions sont remplies alors on met à jour la fiche produit
            if($valid) {
                //On vérifie que les 2 jetons sont là
                if(!empty($_SESSION["token"]) AND !empty($comProdToken)) {
                    // On vérifie que les deux correspondent
                    if($_SESSION["token"] == $comProdToken) {
                        // On enlève la vérification du Referer Header pour tester en localhost
                        /* $referer = $_SERVER["HTTP_REFERER"];

                        // On vérifie que la requête vient bien du formulaire
                        if($referer == "https://importgames.llemaitre.com/backoff/ficheproduit?id=" . $prod["id"] OR $referer == "https://importgames.llemaitre.com/backoff/ficheprod-" . $prod["id"] OR $referer == "https://importgames.llemaitre.com/backoff/ficheproduit?search=" . $searchResult) { */
                            // On insert no données dans la table "achat"
                            $query = "INSERT INTO `achat` (`produit_id`, `titre`, `qte`, `prix`, `date_a`) VALUES (?, ?, ?, ?, ?)";
                            $bdd->insert($query, array($prod["id"], $prod["titre"], $qte, $prixAchat, $dateAchat));

                            // On met à jour la quantité dans la table "produit"
                            $query = "UPDATE `produit` SET `qte` = `qte` + ? WHERE `id` = ?";
                            $bdd->insert($query, array($qte, $prod["id"]));

                            header("location:/importgames/backoff/ficheprod-" . $prod["id"]);
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
    }


    // On affecte à la variable de session FILE_PHTML le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de session template le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page ficheproduit.phtml dans le fichier layout.phtml du dossier "backoff" ligne 188
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans ficheproduit.php
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