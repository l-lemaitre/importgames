<?php
	// Démarre une nouvelle session ou reprend une session existante
	session_start();

    // Si un administrateur est connecté alors on stock sa session dans des cookies (voir fichier index.php ligne 53)
    if(isset($_SESSION["admin_id"])) {
    	setcookie("ad_i", $_SESSION["admin_id"], time() + 60, FALSE, FALSE, FALSE, TRUE);
    	setcookie("ad", $_SESSION["admin"], time() + 60, FALSE, FALSE, FALSE, TRUE);
    	setcookie("pv", $_SESSION["prv"], time() + 60, FALSE, FALSE, FALSE, TRUE);
	}

	// Détruit toutes les données associées à la session courante
	session_destroy();

	// On supprime le cookie "stayCo",
	setcookie("stayCo");
	// on supprime sa valeur présente dans le tableau $_COOKIE
	unset($_COOKIE["stayCo"]);

	// Envoie à la page d'accueil
	header("location:index");
?>