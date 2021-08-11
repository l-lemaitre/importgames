<?php
	// On vérifie si le panier existe, sinon on le crée
	function creationPanier() {
		if(!isset($_SESSION["panier"])) {
			$_SESSION["panier"] = array();
			$_SESSION["panier"]["idProduit"] = array();
			$_SESSION["panier"]["libelleProduit"] = array();
			$_SESSION["panier"]["qteProduit"] = array();
			$_SESSION["panier"]["prixProduit"] = array();
			$_SESSION["panier"]["imgProduit"] = array();
			$_SESSION["panier"]["verrou"] = false;
		}

		return true;
	}


	// Fonction pour ajouter un article au panier
	function ajouterArticle($idProduit, $libelleProduit, $qteProduit, $prixProduit, $imgProduit) {
		// Si le panier existe,
		if(creationPanier() && !isVerrouille()) {
			// on recherche du produit dans le panier,
			$positionProduit = array_search($idProduit, $_SESSION["panier"]["idProduit"]);

			// si le produit existe déjà on ajoute seulement la quantité,
			if($positionProduit !== FALSE) {
				$_SESSION["panier"]["qteProduit"][$positionProduit] += $qteProduit;
			}

			else {
				// sinon on ajoute le produit
				array_push($_SESSION["panier"]["idProduit"], $idProduit);
				array_push($_SESSION["panier"]["libelleProduit"], $libelleProduit);
				array_push($_SESSION["panier"]["qteProduit"], $qteProduit);
				array_push($_SESSION["panier"]["prixProduit"], $prixProduit);
				array_push($_SESSION["panier"]["imgProduit"], $imgProduit);
			}
		}

		else {
			echo "Un problème est survenu, veuillez contacter l'administrateur du site.";
		}
	}


	// Fonction pour supprimer un article du panier
	function supprimerArticle($idProduit) {
		//Si le panier existe,
		if(creationPanier() && !isVerrouille()) {
			// on passe par un panier temporaire,
			$tmp = array();
			$tmp["idProduit"] = array();
			$tmp["libelleProduit"] = array();
			$tmp["qteProduit"] = array();
			$tmp["prixProduit"] = array();
			$tmp["imgProduit"] = array();
			$tmp["verrou"] = $_SESSION["panier"]["verrou"];

			for($i = 0; $i < count($_SESSION["panier"]["idProduit"]); $i++) {
				if($_SESSION["panier"]["idProduit"][$i] !== $idProduit) {
					array_push($tmp["idProduit"], $_SESSION["panier"]["idProduit"][$i]);
					array_push($tmp["libelleProduit"], $_SESSION["panier"]["libelleProduit"][$i]);
					array_push($tmp["qteProduit"], $_SESSION["panier"]["qteProduit"][$i]);
					array_push($tmp["prixProduit"], $_SESSION["panier"]["prixProduit"][$i]);
					array_push($tmp["imgProduit"], $_SESSION["panier"]["imgProduit"][$i]);
				}
			}

			// on remplace le panier en session par notre panier temporaire à jour,
			$_SESSION["panier"] = $tmp;

			// on efface notre panier temporaire
			unset($tmp);
		}

		else {
			echo "Un problème est survenu, veuillez contacter l'administrateur du site.";
		}
	}

	// Fonction pour modifier la quantité d'un article du panier
	function modifierQTeArticle($idProduit, $qteProduit) {
		// Si le panier existe,
		if(creationPanier() && !isVerrouille()) {
			// si la quantité du produit est positive,
			if($qteProduit > 0) {
				$positionProduit = array_search($idProduit, $_SESSION["panier"]["idProduit"]);

				// si le produit existe déjà on remplace la quantité,
				if($positionProduit !== FALSE) {
					$_SESSION["panier"]["qteProduit"][$positionProduit] = $qteProduit;
				}
			}

			// sinon on supprime l'article
			else {
				supprimerArticle($idProduit);
			}
		}

		else {
			echo "Un problème est survenu veuillez contacter l'administrateur du site.";
		}
	}


	// Montant total du panier
	function montantGlobal() {
		$total=0;
		
		for($i = 0; $i < count($_SESSION["panier"]["idProduit"]); $i++) {
			$total += $_SESSION["panier"]["qteProduit"][$i] * $_SESSION["panier"]["prixProduit"][$i];
		}

		return $total;
	}


	// Fonction de suppression du panier
	function supprimePanier() {
		unset($_SESSION["panier"]);
	}


	// Permet de savoir si le panier est verrouillé
	function isVerrouille() {
		if(isset($_SESSION["panier"]) && $_SESSION["panier"]["verrou"]) {
			return true;
		}

		else {
			return false;
		}
	}


	// On compte le nombre d'articles différents dans le panier
	function compterArticles() {
		$total = 0;

		// Pour chaque article du panier on incrémente de 1,
		for($i = 0; $i < count($_SESSION["panier"]["idProduit"]); $i++) {
			// si la quantité d'un ou plusieurs articles du panier est supérieur à 1,
			if($_SESSION["panier"]["qteProduit"][$i] > 1) {
				for($i = 0; $i < count($_SESSION["panier"]["idProduit"]); $i++) {
					// on ajoute à la variable total le résultat du compte d'articles par leur quantité
					$total += $_SESSION["panier"]["qteProduit"][$i];
				}

				// Termine la fonction et retourne le total des produits
				return $total . " produits";
			}

			if(count($_SESSION["panier"]["idProduit"]) == 1) {
				$total = $_SESSION["panier"]["qteProduit"][$i];

				return $total . " produit";
			}

			if($_SESSION["panier"]["idProduit"][$i] != 1) {
				for($i = 0; $i < count($_SESSION["panier"]["idProduit"]); $i++) {
					$total += $_SESSION["panier"]["qteProduit"][$i];
				}

				return $total . " produits";
			}
		}

		return $total." produit";
	}
?>