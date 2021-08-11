<?php
    // Démarre une nouvelle session ou reprend une session existante
    session_start();


    // Si aucun administrateur n'est connecté alors on ne va pas sur cette page
    if(!isset($_SESSION["admin_id"])) {
        // L'utilisateur est envoyé à la page index/connexion
        header("location:/importgames/backoff/index");
        exit;
    }


	// On désactive les suggestions, les remarques et les alertes PHP du rapport d'erreurs en utilisant la fonction error_reporting() avec les constantes pré-définies E_STRICT, E_NOTICE et E_WARNING
    error_reporting(E_ALL & ~(E_STRICT | E_NOTICE | E_WARNING));


    // Si la variable de session lignes n'est pas déclarée,
    if(!isset($_SESSION["lignes"])) {
    	// on lui affecte un tableau vide
		$_SESSION["lignes"] = array();
	}

    // Si la variable de session page n'est pas déclarée,
    if(!isset($_SESSION["page"])) {
       	// on lui affecte la valeur null
		$_SESSION["page"] = null;
	}

    if(!isset($_SESSION["elementId"])) {
		$_SESSION["elementId"] = array();
	}

    if(!isset($_SESSION["msgConfirm"])) {
		$_SESSION["msgConfirm"] = null;
	}

    if(!isset($_SESSION["loopElementId"])) {
		$_SESSION["loopElementId"] = array();
	}

    if(!isset($_SESSION["loopMsgConfirm"])) {
		$_SESSION["loopMsgConfirm"] = null;
	}

    if(!isset($_SESSION["verifReset"])) {
		$_SESSION["verifReset"] = null;
	}

    if(!isset($_SESSION["refReset"])) {
		$_SESSION["refReset"] = null;
	}

    if(!isset($_SESSION["idTriHidden"])) {
		$_SESSION["idTriHidden"] = array();
	}

    if(!isset($_SESSION["selected"])) {
		$_SESSION["selected"] = array();
	}

    if(!isset($_SESSION["affichageProd"])) {
		$_SESSION["affichageProd"] = array();
	}

	// On convertie en un objet la variable varBack créant ainsi une nouvelle instance de la classe interne stdClass
	$varBack = (object)array();

	// On utilise l'opérateur objet -> pour définir une propriété dans l'objet $varBack appelée idLignes
	$varBack->idLignes = $_SESSION["idLignes"];

	$varBack->lignes = $_SESSION["lignes"];
	$varBack->page = $_SESSION["page"];
	$varBack->elementId = $_SESSION["elementId"];
	$varBack->msgConfirm = $_SESSION["msgConfirm"];
	$varBack->loopElementId = $_SESSION["loopElementId"];
	$varBack->loopMsgConfirm = $_SESSION["loopMsgConfirm"];
	$varBack->verifReset = $_SESSION["verifReset"];
	$varBack->refReset = $_SESSION["refReset"];
	$varBack->idTriList = $_SESSION["idTriList"];
	$varBack->idTriHidden = $_SESSION["idTriHidden"];
	$varBack->selected = $_SESSION["selected"];
	$varBack->affichageProd = $_SESSION["affichageProd"];

	// On affiche la représentation JSON de la variable varBack retournée en utilisant la fonction json_encode (voir fichier ajax.js lignes 9 et 149)
	echo json_encode($varBack, JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
?>