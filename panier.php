<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page panier.php (ligne 23)
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

    else {
        // On récupère les informations de l'utilisateur connecté
        $query = "SELECT * FROM `user` WHERE `id` = ?";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$_SESSION["user_id"]]);
        $user = $resultSet->fetch();
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Panier";
    }


    //On crée un jeton d'authentification pour se protéger contre la faille CSRF (panier.phtml ligne 78, validercommande.php lignes 79, 86 et 161)
    $_SESSION["tokenValidco"] = bin2hex(random_bytes(6));


    // Suppression d'un article du panier par la méthode GET (layout.phtml ligne 102)
    if(isset($_GET["del"])) {
        supprimerArticle($_GET["del"]);

        // On utilise la fonction header pour retourner sur la page qui a conduit à la page courante
        header("location:" . $_SERVER["HTTP_REFERER"]);
    }

    // Traitement des diverses actions du panier via une variable $action
    $erreur = false;

    if(isset($_POST["action"])) {
        $action = $_POST["action"];
    }

    elseif(isset($_GET["action"])) {
        $action = $_GET["action"];
    }

    else {
        $action = false;
    }

    if($action) {
        if(!in_array($action, array("ajout", "suppression", "refresh"))) { 
            $erreur = true;
        }

        /* Récupération des variables en POST ou GET en utilisant l'opérateur ternaire.
        Si la variable $_POST["id"] est déclarée on affecte à la variable $id la valeur de $_POST["id"]. Ou bien si $_GET["id"] est déclarée on affecte à $id la valeur de $_GET["id"]. Sinon on affecte à $id la valeur FALSE */
        $id = (isset($_POST["id"]) ? $_POST["id"] : (isset($_GET["id"]) ? $_GET["id"] : false));

        $libelle = (isset($_POST["libelle"]) ? $_POST["libelle"] : (isset($_GET["libelle"]) ? $_GET["libelle"] : false ));
        $qte = (isset($_POST["qte"]) ? $_POST["qte"] : (isset($_GET["qte"]) ? $_GET["qte"] : false));
        $prix = (isset($_POST["prix"]) ? $_POST["prix"] : (isset($_GET["prix"]) ? $_GET["prix"] : false));
        $img = (isset($_POST["img"]) ? $_POST["img"] : (isset($_GET["img"]) ? $_GET["img"] : false));

        // Suppression des espaces verticaux
        $libelle = preg_replace("#\v#", "", $libelle);
        
        // On vérifie que la variable prix est un float
        $prix = floatval($prix);

        // On traite la variable qte qui peut être un tableau d'entiers ou un entier simple
        if(is_array($qte)) {
            $qteArticle = array();
            $i = 0;

            foreach($qte as $contenu) {
                $qteArticle[$i++] = intval($contenu);
            }
        }

        else {
            $qte = intval($qte);
        }

        // Requête SQL : Sélectionne et va chercher la valeur de la colonne "qte" dans la table "produit" de la base de données où l'id correspond à celui de la variable id
        $query = "SELECT `qte` FROM `produit` WHERE `id` = ?";
        $resultSet = $pdo->prepare($query);
        $resultSet->execute([$id]);
        $prod = $resultSet->fetch();
    }

    if(!$erreur) {
        switch($action){
            Case "ajout" :
                // Recherche du produit dans le panier
                $positProd = array_search($id, $_SESSION["panier"]["idProduit"]);

                // Si la quantité du produit en stock est égale à 0,
                if($prod["qte"] == 0) {
                    header("location:index");
                }

                // ou bien si l'id du produit à ajouter n'est pas déjà présent dans le panier on l'y ajoute
                elseif(!in_array($id, $_SESSION["panier"]["idProduit"])) {
                    ajouterArticle($id, $libelle, $qte, $prix, $img);

                    // On utilise la fonction header pour revenir à l'adresse de la page ayant conduit à la page panier.php
                    header("location:" . $_SERVER["HTTP_REFERER"]);
                }

                // ou bien si la quantité du produit additionnée à celle du panier est inférieur ou égale à celle en stock,
                elseif(($qte + $_SESSION["panier"]["qteProduit"][$positProd]) <= $prod["qte"]) {
                    // si la quantité du produit additionnée à celle du panier est inférieur ou égale à la quantité maximum par produit dans le panier (10),
                    if(($qte + $_SESSION["panier"]["qteProduit"][$positProd]) <= 10) {
                        // on l'ajoute au panier, voir application/fonctions_panier.php ligne 19
                        ajouterArticle($id, $libelle, $qte, $prix, $img);

                        header("location:" . $_SERVER["HTTP_REFERER"]);
                    }

                    else {
                        $qteMax = true;
                    }
                }

                // sinon on affiche le message d'erreur dans le fichier panier.phtml ligne 11
                else {
                    $stockInfCart = true;
                }
                break;

            Case "suppression" :
                // Fonction pour supprimer un article du panier, voir application/fonctions_panier.php ligne 47
                supprimerArticle($id);
                break;

            Case "refresh" :
                // On déclare la variable qteStock
                $qteStock = "";

                // On parcourt le tableau $_SESSION["panier"]["idProduit"] et la valeur de l'élément courant est copié dans $valeur
                foreach($_SESSION["panier"]["idProduit"] as $valeur):
                    $query = "SELECT `qte` FROM `produit` WHERE `id` = ?";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute([$valeur]);
                    $prod = $resultSet->fetch();

                    // On affecte à la variable qteStock sa propre valeur concaténée à la quantité en stock des articles du panier et de la chaîne de caractères ", " comme séparateur
                    $qteStock = $qteStock . $prod[0] . ", ";
                endforeach;

                // On récupère la valeur de la variable qteStock en enlevant la chaîne de caractères ", " à la fin,
                $qteStock = substr($qteStock, 0, -2);

                // puis avec la fonction explode on crée un tableau en utilisant la chaîne de caractères ", " comme séparateur et la variable qteStock comme chaîne initiale
                $qteStock = explode(", ", $qteStock);


                // On crée une boucle qu'on itére avec le count de la variable qteArticle, soit l'équivalent du nombre d'articles dans le panier,
                for($i = 0; $i < count($qteArticle); $i++) {
                    // si les valeurs du tableau qteArticle représentants la quantité par articles dans le panier sont inférieurs ou égales aux valeurs du tableau qteStock représentants la quantité par articles stockée dans la bdd,
                    if($qteArticle[$i] <= $qteStock[$i]) {
                        // on modifie la quantité des articles du panier (voir application/fonctions_panier.php ligne 82)
                        modifierQTeArticle($_SESSION["panier"]["idProduit"][$i], round($qteArticle[$i]));
                    }

                    else {
                        if(isset($_SESSION["panier"]["idProduit"][$i])) {
                            // sinon on remplace la dernière quantité modifiée dans le panier par la quantité en stock
                            modifierQTeArticle($_SESSION["panier"]["idProduit"][$i], $qteStock[$i]);

                            // et on affiche le message d'erreur dans le fichier panier.phtml ligne 53
                            $stockInfQte = true;
                        }
                    }
                }
                break;

            Default:
            break;
        }
    }

    // On affecte à la variable nbrArticles la quantité d'articles du panier (panier.phtml lignes 19 et 46)
    $nbrArticles = count($_SESSION["panier"]["idProduit"]);

    // On stocke la variable nbrArticles dans une variable de SESSION pour en récupérer la valeur dans le fichier var.php (voir fichiers var.php ligne 40 et ajax.js ligne 38)
    $_SESSION["var"] = $nbrArticles;


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page panier.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans panier.php
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