<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page categorie.php (ligne 23)
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

        if(isset($_GET["id"]) && isset($_GET["titre"])) {
            $catId = htmlentities($_GET["id"]);
            $catTitre = htmlspecialchars($_GET["titre"]);

            $query = "SELECT `titre` FROM `categorie` WHERE (`id` = ? AND `titre` = ?)";
            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$catId, $catTitre]);
            $cat = $resultSet->fetch();

            if(isset($cat["titre"])) {
                return $cat["titre"];
            }

            else {
                return "Aucun contenu trouvé";
            }
        }

        else {
            return "Erreur adresse HTTP";
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans categorie.phtml ligne 45
    function couperTitre($contenu) {
        $length = 29; // On veut les 29 premiers caractères

        if(strlen($contenu) >= $length) { // Si la longueur de $contenu est plus grande ou égal à $length,
          $titreCoupe = substr($contenu, 0, $length) . "..."; // alors on garde $contenu à partir du début (0) jusqu'à $length (29) et tout ce qui vient ensuite est remplacé par "..."

          return $titreCoupe;
        }

        if(strlen($contenu) < $length) {
            return $contenu; // Affiche le texte en entier si il contient moins de 29 caractères
        }
    }


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans categorie.phtml ligne 46
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


    // Fonction pour masquer le texte trop long dans le titre des produits et le remplacer par "..." dans categorie.phtml ligne 47
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


    if(isset($_GET["id"]) && isset($_GET["titre"])) {
        // On récupère et sécurise le contenu des variables GET "id" et "titre" pour se protéger contre les injections de code HTML ou JavaScript
        $catId = htmlentities($_GET["id"]);
        $catTitre = htmlspecialchars($_GET["titre"]);

        // On sélectionne les valeurs contenues dans la table "categorie" dont l'Id et le titre correspondent à ceux affichés dans le header pour vérifier si la catégorie demandée existe dans la bdd (voir ligne 194 et dans fichier categorie.phtml lignes 4, 12 et 24)
        $query = "SELECT * FROM `categorie` WHERE (`id` = ? AND `titre` = ?)";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$catId, $catTitre]);
        $verifCat = $resultSet->fetch();


        if(isset($verifCat["id"])) {
            // On affecte à la variable selected la valeur "defaut" (voir ligne 198 et dans fichier categorie.phtml ligne 34)
            $selected = "defaut";

            if(isset($_POST["affichageTri"])) {
                // On sécurise la variable POST "affichageTri" avec la fonction htmlspecialchars() pour se protéger contre les injections de code HTML ou JavaScript
                $affichageTri = htmlspecialchars($_POST["affichageTri"]);

                // Si l'expression "affichageTri" est égal à la valeur "nouveautes",
                if($affichageTri == "nouveautes") {
                    // on sélectionne toutes les lignes de la table "produit" dont la colonne "cat_id" correspond à l'id de la categorie, puis on classe le résultat par "id" en inversant l’ordre d'affichage avec le suffixe DESC
                    $query ="SELECT * FROM `produit` WHERE `cat_id` = ? ORDER BY `id` DESC";
                }

                elseif($affichageTri == "qte") {
                    $query = "SELECT * FROM `produit` WHERE `cat_id` = ? AND `qte` > 0 ORDER BY `qte` DESC";
                }

                elseif($affichageTri == "prixCroissant") {
                    $query = "SELECT * FROM `produit` WHERE `cat_id` = ? ORDER BY `prix`";
                }

                elseif($affichageTri == "prixDecroissant") {
                        $query = "SELECT * FROM `produit` WHERE `cat_id` = ? ORDER BY `prix` DESC";   
                }

                elseif($affichageTri == "titreAaZ") {
                    $query ="SELECT * FROM `produit` WHERE `cat_id` = ? ORDER BY `titre`";
                }

                else {
                    $query = "SELECT * FROM `produit` WHERE `cat_id` = ?";
                }

                // La variable selected correspond à la valeur de l'envoi du choix de tri de l'utilisateur (voir ligne 198 et dans fichier categorie.phtml ligne 33)
                $selected = $affichageTri;
            }

            else {
                $query = "SELECT * FROM `produit` WHERE `cat_id` = ?";
            }

            $resultSet = $pdo->prepare($query);
            $resultSet->execute([$verifCat["id"]]);
            $prods = $resultSet->fetchAll();

            // On stocke la variable selected dans une variable de SESSION pour en récupérer la valeur dans le fichier var.php (voir lignes 153/186, fichiers var.php ligne 40 et ajax.js ligne 27)
            $_SESSION["var"] = $selected;
        }
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page categorie.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans categorie.php
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