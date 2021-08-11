<?php
    // Inclut le fichier placé en argument bdd_connection.php à la page validercommande.php (ligne 23)
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
        // si le cookie "stayCo" est vide on ne va pas sur cette page
        if(empty($_COOKIE["stayCo"])) {
            header("location:connexion");
            exit;
        }

        else {
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
    }


    // Fonction pour afficher le titre de la page dans la balise html "head" (layout.phtml ligne 13)
    function headTitle() {
        return "Validation commande";
    }


    // On va chercher les infos de l'utilisateur dont l'id correspond à celui de la session en cours
    $query = "SELECT * FROM `user` WHERE `id` = ?";
    $resultSet = $pdo->prepare($query);
    $resultSet->execute([$_SESSION["user_id"]]);
    $user = $resultSet->fetch();


    // Si les variables "val", "tokenValidco" et "token" ne sont pas vides
    if(!empty($_GET["val"]) && !empty($_SESSION["tokenValidco"]) && !empty($_GET["token"])) {
        // On récupère et sécurise le contenu de la variable de SESSION "tokenValidco" et des variables GET "token" et "val"
        $_SESSION["tokenValidco"] = htmlspecialchars($_SESSION["tokenValidco"]);
        $valToken = htmlspecialchars($_GET["token"]);
        $val = htmlspecialchars($_GET["val"]);

        // Si la valeur de la variable val correspond à la chaîne de caractères "verif"
        if($val == "verif") {
            // On vérifie que les deux jetons correspondent
            if($_SESSION["tokenValidco"] == $valToken) {
                // On enlève la vérification du Referer Header pour tester en localhost
                /* $referer = $_SERVER["HTTP_REFERER"];

                // On vérifie que la requête vient bien de l'URL complète qui a conduit le client à la page actuelle
                if($referer == "https://importgames.llemaitre.com/panier") { */
                    // On compte le nombre de commandes que le client a passées sur le site et on incrémente de 1 pour la commande en cours
                    $query = "SELECT COUNT(`user_id`) + 1 FROM `commande` WHERE `user_id` = ?";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute([$_SESSION["user_id"]]);
                    $numCo = $resultSet->fetch();

                    // On définit le décalage horaire par défaut de toutes les fonctions date/heure sur celui de l'heure Française
                    date_default_timezone_set("Europe/Paris");

                    // On affecte à la variable adresse l'adresse de facturation/livraison de l'utilisateur
                    $adresse = $user["nom"] . " " . $user["prenom"] . "\n" . $user["adresse"] . "\n" . $user["code_postal"] . " " . $user["ville"] . "\n" . $user["pays"];

                    // Insertion de la commande
                    $query = "INSERT INTO `commande` (`user_id`, `numero`, `adresse`, `total`, `date_co`) VALUES (?, ?, ?, ?, ?)";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute([$_SESSION["user_id"], $numCo[0], $adresse, montantGlobal(), date("Y-m-d H:i:s")]);

                    // Récupération de la dernière commande de l'utilisateur
                    $query = "SELECT * FROM `commande` WHERE `user_id` = ? ORDER BY `id` DESC LIMIT 1";
                    $resultSet = $pdo->prepare($query);
                    $resultSet->execute([$_SESSION["user_id"]]);
                    $lastCom = $resultSet->fetch();

                    for($i = 0; $i < count($_SESSION["panier"]["idProduit"]); $i++):
                        $commande_id = $lastCom["id"];
                        $produit_id = $_SESSION["panier"]["idProduit"][$i];
                        $titre = $_SESSION["panier"]["libelleProduit"][$i];
                        $qte = $_SESSION["panier"]["qteProduit"][$i];
                        $prix = $_SESSION["panier"]["prixProduit"][$i];

                        // Insertion des détails de la commande
                        $query = "INSERT INTO `detail_com` (`commande_id`, `produit_id`, `titre`, `qte`, `prix`) VALUES (?, ?, ?, ?, ?)";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$commande_id, $produit_id, $titre, $qte, $prix]);

                        // Mise à jour de la quantité dans la table "produit"
                        $query = "UPDATE `produit` SET `qte` = `qte` - ? WHERE `id` = ?";
                        $resultSet = $pdo->prepare($query);
                        $resultSet->execute([$qte, $produit_id]);
                    endfor;

                    // Vide le panier,
                    supprimePanier();

                    // le recrée
                    creationPanier();

                    // Envoie vers la page validco avec la variable valToken et la chaîne de caractères "ok" pour que l'utilisateur n'ajoute pas de lignes de commande par erreur en rechargeant la page
                    header("location:validco-$valToken-ok");
                    exit;
                /* }

                // La requête vient d'autre part donc on bloque
                else {
                    $refError = true;
                } */
            }

            else {
                // Les tokens ne correspondent pas donc on ne valide pas
                $verifError = true;
            }
        }

        // Ou bien si la valeur de la variable val correspond à la chaîne de caractères "ok"
        elseif($val == "ok") {
            // On vérifie que les deux tokens correspondent
            if($_SESSION["tokenValidco"] == $valToken) {
                // On détruit la variable de SESSION "tokenValidco" pour ne pas créer une commande vide avec l'url "validco-$_SESSION["tokenValidco"]-verif"
                unset($_SESSION["tokenValidco"]);

                // On affiche le message de validation de la commande dans le fichier validercommande.phtml ligne 23
                $validCo = true;
            }
            
            else {
                // Les tokens ne correspondent pas donc on ne valide pas
                $verifError = true;
            }
        }

        // Ou alors si la valeur de la variable "val" ne correspond ni à la chaîne de caractères "verif" ou "ok"
        else {
            // On affiche un message d'erreur dans le fichier validercommande.phtml ligne 11
            $verifError = true;
        }
    }

    else {
        // Les tokens sont introuvables donc on ne valide pas
        $verifError = true;
    }


    // On affecte à la variable de SESSION "FILE_PHTML" le chemin complet et le nom du fichier courant
    $_SESSION["FILE_PHTML"] = __FILE__;

    // On affecte à la variable de SESSION "template" le nom et l'extension du fichier courant
    $_SESSION["template"] = substr(strrchr($_SESSION["FILE_PHTML"], "/"), 1);

    // On récupère le nom du fichier courant pour inclure le contenu de la page validercommande.phtml dans le fichier layout.phtml ligne 121
    $_SESSION["template"] = substr($_SESSION["template"], 0, strrpos($_SESSION["template"], "."));


    // Inclut et exécute le fichier layout.phtml qui hérite de la portée des variables présentes dans validercommande.php
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