<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page produit.php (ligne 23)
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
                // on charge la session de l'utilisateur retourné après l'authentification du cookie par la bdd
                $_SESSION["user_id"] = htmlentities($user["id"]);
                $_SESSION["user"] = htmlspecialchars($user["username"]);
                $_SESSION["user_email"] = htmlspecialchars($user["email"]);
            }
        }
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        // On ajoute dynamiquement une connexion avec la base de données "importgames" en incluant le fichier bdd_co_fonctions.php (ligne 64)
        $bddFct = "bdd_co_fonctions";

        $bddFct = trim($bddFct . ".php");

        $bddFct = str_replace("../", "protect", $bddFct);
        $bddFct = str_replace(";", "protect", $bddFct);
        $bddFct = str_replace("%", "protect", $bddFct);

        if(!preg_match("/backoff/", $bddFct) && file_exists("application/" . $bddFct)) {
           include "application/" . $bddFct;
        }

        if(isset($_GET["id"])) {
            $prodId = htmlentities($_GET["id"]);

            $query = "SELECT `titre` FROM `produit` WHERE `id` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$prodId]);
            $prod = $resultSet->fetch();

            if(isset($prod["titre"])) {
                return $prod["titre"];
            }

            else {
                return "Aucun contenu trouvé";
            }
        }

        else {
            return "Erreur adresse HTTP";
        }
    }


    // Fonction pour afficher le titre de la catégorie où l'id correspond à celui de la colonne "cat_id" du produit (produit.phtml ligne 7)
    function getTitrebyid($contenu) {
        $bddFct = "bdd_co_fonctions";

        $bddFct = trim($bddFct . ".php");

        $bddFct = str_replace("../", "protect", $bddFct);
        $bddFct = str_replace(";", "protect", $bddFct);
        $bddFct = str_replace("%", "protect", $bddFct);

        if(!preg_match("/backoff/", $bddFct) && file_exists("application/" . $bddFct)) {
           include "application/" . $bddFct;
        }
        
        $query = "SELECT `titre` FROM `categorie` WHERE `id` = ?";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$contenu]);
        $cat = $resultSet->fetch();

        return $cat["titre"];
    }


    // Fonction pour afficher le titre de la catégorie sans accents en utilisant son id (produit.phtml lignes 7 et 157)
    function renameTitrebyid($contenu) {
        $bddFct = "bdd_co_fonctions";

        $bddFct = trim($bddFct . ".php");

        $bddFct = str_replace("../", "protect", $bddFct);
        $bddFct = str_replace(";", "protect", $bddFct);
        $bddFct = str_replace("%", "protect", $bddFct);

        if(!preg_match("/backoff/", $bddFct) && file_exists("application/" . $bddFct)) {
           include "application/" . $bddFct;
        }
        
        $query = "SELECT `titre` FROM `categorie` WHERE `id` = ?";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$contenu]);
        $cat = $resultSet->fetch();

        // On utilise un tableau pour que la fonction strtr ligne 143 ne traite pas les caractères spéciaux comme de l'ISO
        $accentChars = array(
            "Š"=>"S", "š"=>"s", "Đ"=>"Dj", "đ"=>"dj", "Ž"=>"Z", "ž"=>"z", "Č"=>"C", "č"=>"c", "Ć"=>"C", "ć"=>"c",
            "À"=>"A", "Á"=>"A", "Â"=>"A", "Ã"=>"A", "Ä"=>"A", "Å"=>"A", "Æ"=>"A", "Ç"=>"C", "È"=>"E", "É"=>"E",
            "Ê"=>"E", "Ë"=>"E", "Ì"=>"I", "Í"=>"I", "Î"=>"I", "Ï"=>"I", "Ñ"=>"N", "Ò"=>"O", "Ó"=>"O", "Ô"=>"O",
            "Õ"=>"O", "Ö"=>"O", "Ø"=>"O", "Ù"=>"U", "Ú"=>"U", "Û"=>"U", "Ü"=>"U", "Ý"=>"Y", "Þ"=>"B", "ß"=>"Ss",
            "à"=>"a", "á"=>"a", "â"=>"a", "ã"=>"a", "ä"=>"a", "å"=>"a", "æ"=>"a", "ç"=>"c", "è"=>"e", "é"=>"e",
            "ê"=>"e", "ë"=>"e", "ì"=>"i", "í"=>"i", "î"=>"i", "ï"=>"i", "ð"=>"o", "ñ"=>"n", "ò"=>"o", "ó"=>"o",
            "ô"=>"o", "õ"=>"o", "ö"=>"o", "ø"=>"o", "ù"=>"u", "ú"=>"u", "û"=>"u", "ý"=>"y", "ý"=>"y", "þ"=>"b",
            "ÿ"=>"y", "Ŕ"=>"R", "ŕ"=>"r");
       
        return strtr($cat["titre"], $accentChars);
    }


    // Fonction pour masquer le texte trop long dans le titre des produits du fil d'Ariane et le remplacer par "..." dans produit.phtml ligne 16
    function couperTitreFil($contenu) {
        $length = 8; // On veut les 8 premiers caractères pour les smartphones

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans produit.phtml ligne 140
    function couperTitre($contenu) {
        $length = 29; // On veut les 29 premiers caractères

        if(strlen($contenu) >= $length) { // Si la longueur de $contenu est plus grande ou égale à $length,
          $titreCoupe = substr($contenu, 0, $length) . "..."; // alors on garde $contenu à partir du début (0) jusqu'à $length (29) et tout ce qui vient ensuite est remplacé par "..."

          return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu; // Affiche le texte en entier si il contient moins de 29 caractères
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans produit.phtml ligne 141
    function couperTitre600($contenu) {
        $length = 17; // On veut les 17 premiers caractères pour les appareils mobiles

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans produit.phtml ligne 142
    function couperTitre300($contenu) {
        $length = 14; // On veut les 14 premiers caractères pour les smartphones

        if(strlen($contenu) >= $length) {
            $titreCoupe = substr($contenu, 0, $length) . "...";

            return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu;
        }
    }


    if(isset($_GET["id"])) {
        // On récupère et sécurise le contenu de la variable GET "id"
        $prodId = htmlentities($_GET["id"]);


        // Requête SQL : Sélectionne et va chercher les valeurs contenues dans la table "produit" de la base de données où l'id correspond à celui affiché dans le header
        $query = "SELECT * FROM `produit` WHERE `id` = ? AND `cat_id` IS NOT NULL";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$prodId]);
        $prod = $resultSet->fetch();


        if(isset($prod["id"])) {
            // Si la variable de SESSION "prodCalled" n'est pas déclarée et la variable prod "titre" différente de FALSE,
            if(!isset($_SESSION["prodCalled"]) && $prod["titre"]) {
                // on lui affecte la valeur true
                $_SESSION["prodCalled"] = true;
                // et à la variable session prodHeader la valeur de la variable currentHeader (voir fichier bdd_connection.php ligne 115)
                $_SESSION["prodHeader"] = $currentHeader;

                // On définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française
                date_default_timezone_set("Europe/Paris");

                // On inscrit dans la base de données le nombre de visites par page produit pour mesurer le trafic sur le site
                $query = "INSERT INTO `visite_prod` (`produit_id`, `user_id`, `date_v`) VALUES (?, ?, ?)";

                if(!isset($_SESSION["user_id"])) {
                    $userId = 0;
                }

                else {
                    $userId = $_SESSION["user_id"];
                }

                $resultSet = $pdo->prepare($query);
                $resultSet->execute([$prodId, $userId, date("Y-m-d H:i:s")]);
            }


            // Sélectionne et va chercher toutes les valeurs contenues dans la table "image" de la base de données où la valeur de "produit_id" correspond à l'id du produit affiché dans le header
            $query = "SELECT * FROM `image` WHERE `produit_id` = ?";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$prodId]);
            $imgs = $resultSet->fetchAll();

            
            // Si la variable POST "ajouterProd" est déclarée et différente de NULL
            if(isset($_POST["ajouterProd"])) {
                // Recherche du produit dans le panier
                $positProd = array_search($prodId, $_SESSION["panier"]["idProduit"]);

                // Si l'id du produit à ajouter n'est pas déjà présent dans le panier on l'y ajoute,
                if(!in_array($prodId, $_SESSION["panier"]["idProduit"])) {
                    ajouterArticle(htmlentities($prod["id"]), strip_tags($prod["titre"]), htmlentities($_POST["qte"]), htmlentities($prod["prix"]), htmlspecialchars($prod["apercu_img"]));
                }

                // ou bien si la quantité du produit additionnée à celle du panier est inférieur ou égale à celle en stock,
                elseif(($_POST["qte"] + $_SESSION["panier"]["qteProduit"][$positProd]) <= $prod["qte"]) {
                    // si la quantité du produit additionnée à celle du panier est inférieur ou égale à la quantité maximum par produit dans le panier (10),
                    if(($_POST["qte"] + $_SESSION["panier"]["qteProduit"][$positProd]) <= 10) {
                        // on l'ajoute au panier, voir application/fonctions_panier.php ligne 19
                        ajouterArticle(htmlentities($prod["id"]), strip_tags($prod["titre"]), htmlentities($_POST["qte"]), htmlentities($prod["prix"]), htmlspecialchars($prod["apercu_img"]));
                    }

                    else {
                        // sinon si la quantité du produit dans le panier est égale à 10,
                        if($_SESSION["panier"]["qteProduit"][$positProd] == 10) {
                            // on affiche le message d'erreur dans le fichier produit.phtml ligne 96
                            $qteMax = true;
                        }

                        else {
                            // sinon on ajoute dans le panier la quantité maximum par produit - la quantité du produit déjà dans le panier pour arriver à 10
                            ajouterArticle(htmlentities($prod["id"]), strip_tags($prod["titre"]), 10 - $_SESSION["panier"]["qteProduit"][$positProd], htmlentities($prod["prix"]), htmlspecialchars($prod["apercu_img"]));

                            $ajoutQteMax = true;
                        }
                    }
                }

                else {
                    // sinon si la quantité du produit dans le panier est égale à celle en stock,
                    if($_SESSION["panier"]["qteProduit"][$positProd] == $prod["qte"]) {
                        // on affiche la message d'erreur dans le fichier produit.phtml ligne 102
                        $stockInfCart = true;
                    }

                    else {
                        // sinon on ajoute dans le panier la quantité du produit en stock - la quantité du produit déjà dans le panier pour arriver à la quantité totale du stock
                        ajouterArticle(htmlentities($prod["id"]), strip_tags($prod["titre"]), htmlentities($prod["qte"] - $_SESSION["panier"]["qteProduit"][$positProd]), htmlentities($prod["prix"]), htmlspecialchars($prod["apercu_img"]));

                        $ajoutStockInf = true;
                    }
                }
            }


            // Si en utilisant la fonction substr() le segment de chaîne défini par la fonction strrchr() sur la variable prod["video"] à partir de "." et commençant au deuxième caractère est égal à "mp4",
            if(substr(strrchr($prod["video"], "."), 1) == "mp4") {
                // la variable videoMp4 se voit affecté la valeur 1 et on affiche la balise html video dans le fichier produit.phtml ligne 112
                $videoMp4 = true;
            }

            // ou bien si la variable prod["video"] est différente de FALSE, 
            elseif($prod["video"]) {
                // on affiche la balise html iframe dans le fichier produit.phtml ligne 117
                $stream = true;
            }


            // setlocale permet de définir les informations de localisation en Français, LC_TIME applique la configuration de localisation pour le format date et heure avec strftime() dans la page produit.phtml ligne 129
            setlocale(LC_TIME, "fr_FR.utf8", "fr_FR.ISO-8859-1", "fr_FR.iso88591", "fr_FR", "fr", "fra", "french", "français");


            // Sélectionne et va chercher toutes les valeurs contenues dans la table "produit" de la base de données où la valeur de "cat_id" correspond et dont l'id est différent de celui du produit affiché dans le header
            $query = "SELECT * FROM `produit` WHERE (`cat_id` = ? AND `id` <> ?)";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$prod["cat_id"], $prod["id"]]);
            $prods = $resultSet->fetchAll();
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page produit.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans produit.php
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