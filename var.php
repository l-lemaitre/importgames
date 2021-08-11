<?php
    // Démarre une nouvelle session ou reprend une session existante
    session_start();


	// On désactive les suggestions, les remarques et les alertes PHP du rapport d'erreurs en utilisant la fonction error_reporting() avec les constantes pré-définies E_STRICT, E_NOTICE et E_WARNING
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING));


    // Si la variable de SESSION "var" n'est pas déclarée,
    if(!isset($_SESSION["var"])) {
    	// on lui affecte la valeur null
		$_SESSION["var"] = null;
	}

    if(!isset($_SESSION["var2"])) {
		$_SESSION["var2"] = null;
	}

    if(!isset($_SESSION["var3"])) {
		$_SESSION["var3"] = null;
	}

    if(!isset($_SESSION["var4"])) {
		$_SESSION["var4"] = null;
	}

    if(!isset($_SESSION["var5"])) {
		$_SESSION["var5"] = null;
	}

    if(!isset($_SESSION["var6"])) {
		$_SESSION["var6"] = null;
	}

	// On convertie en un objet la variable var créant ainsi une nouvelle instance de la classe interne stdClass
	$var = (object)array();

	// On utilise l'opérateur objet -> pour définir une propriété dans l'objet $var appelée valeur
	$var->valeur = $_SESSION["var"];

	$var->valeur2 = $_SESSION["var2"];
	$var->valeur3 = $_SESSION["var3"];
	$var->valeur4 = $_SESSION["var4"];
	$var->valeur5 = $_SESSION["var5"];
	$var->valeur6 = $_SESSION["var6"];

	// On affiche la représentation JSON de la variable var retournée en utilisant la fonction json_encode (voir fichier ajax.js lignes 9 et 144)
	echo json_encode($var);
?>