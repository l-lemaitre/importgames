<?php
    // On utilise la fonction define pour définir la constante SITE_DIR qui représente le dossier du fichier dir.php. En l'occurence le chemin absolu, racine de l'arborescence pc + site (voir fichiers index.php ligne 174 et bdd_connection.php ligne 72 pour exemples d'utilisation)
    define("SITE_DIR", __DIR__);

    // La fonction dirname renvoie le chemin du dossier parent avec comme paramètre la constante magique __FILE__ qui représente le chemin complet et le nom du fichier courant (rappel)
    // echo dirname(__FILE__) . "/";

    // Équivalents de dirname(__FILE__) . "/" (rappel)
    // echo __DIR__ . "/";
    // echo SITE_DIR . "/";
?>