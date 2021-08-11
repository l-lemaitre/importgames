<?php
	// Démarre une nouvelle session ou reprend une session existante
	session_start();

    // Si un utilisateur est connecté alors on stock sa session dans des cookies (voir fichier index.php ligne 40)
    if(isset($_SESSION["user_id"])) {
    	setcookie("us_i", $_SESSION["user_id"], time() + 60, FALSE, FALSE, FALSE, TRUE);
    	setcookie("us", $_SESSION["user"], time() + 60, FALSE, FALSE, FALSE, TRUE);
    	setcookie("eml", $_SESSION["user_email"], time() + 60, FALSE, FALSE, FALSE, TRUE);
	}

	// Détruit toutes les données associées à la session courante
	session_destroy();

	// Envoie à la page d'accueil
	header("location:/importgames/backoff/index");
?>