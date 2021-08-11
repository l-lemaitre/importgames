// JS Document

// On utilise l'objet XMLHttpRequest de la méthode AJAX
let xhttp = new XMLHttpRequest();

xhttp.onload = function() {
    /* La variable à récupérer est contenue dans l'objet response et l'attribut responseText 
    dont on construit la valeur JavaScript en utilisant la méthode JSON.parse() */
    let varPhp = JSON.parse(this.responseText);

    // Si l'id affichageTri est déclaré et différent de NULL,
    if(document.getElementById("affichageTri")) {
        /* On affecte à la constante affichageTri l'objet représentant l'élément dont l'id 
        correspond à la chaîne de caractères affichageTri (voir fichier categorie.phtml ligne 33) */
        const affichageTri = document.getElementById("affichageTri");

        /* L'interface EventListener représente un objet qui peut gérer un évènement distribué par 
        un objet EventTarget. On ajoute un gestionnaire pour l'évènement "change" qui fournit une 
        fonction de rappel */
        affichageTri.addEventListener("change", function(event) {
            this.form.submit();
        });

        /* Permet d'afficher la description correspondant à la valeur de l'option de l'élément htlml 
        select en utilisant la méthode getElementById() avant ou après l'envoi du formulaire 
        (categorie.phtml ligne 33 et var.php ligne 40) */
        document.getElementById("affichageTri").value = varPhp.valeur;
    }

    /* Si la propriété valeur contenue dans la variable varPhp est égale à la chaîne de caractères 
    resetAccount (voir fichiers var.php ligne 40 et modifparametres.php ligne 327) */
    if(varPhp.valeur == "resetAccount") {
        window.alert("Votre compte a bien était supprimé, vous allez être déconnecté.");
        location.href="deconnexion.php";
    }

    // Voir fichier panier.php ligne 230
    for(let i = 0; i < varPhp.valeur; i++) {
        numArticle = parseInt(i, 10) + 1;

        // Voir fichier panier.phtml ligne 51
        if(document.getElementById("qteArticle" + numArticle)) {
            const qteArticle = document.getElementById("qteArticle" + numArticle);

            // Fonction pour limiter la valeur numérique dans une balise input
            function limiter(input) {
               if(input.value <= 0) input.value = 1;
               if(input.value > 10) input.value = 10;
            }

            qteArticle.addEventListener("change", function(event) {
                this.form.submit(limiter(this));
            });
        }
    }

    if(document.getElementById("lienPage" + varPhp.valeur)) {
        /* On modifie la couleur du texte et du fond pour le lien correspondant au numéro de page du 
        header (voir fichiers commandes.phtml ligne 89 ou recherche.phtml ligne 107) */
        document.getElementById("lienPage" + varPhp.valeur).style.color = "#fafafa";
        document.getElementById("lienPage" + varPhp.valeur).style.backgroundColor = "#ff8b2b";
    }

    if(document.getElementById("lienPageTop" + varPhp.valeur)) {
        // Voir fichier recherche.phtml ligne 70
        document.getElementById("lienPageTop" + varPhp.valeur).style.color = "#fafafa";
        document.getElementById("lienPageTop" + varPhp.valeur).style.backgroundColor = "#ff8b2b";
    }

    if(document.getElementById("affichageLignes")) {
        const affichageLignes = document.getElementById("affichageLignes");

        /* Pour la compatibilité, un objet qui n'est pas une fonction avec une propriété `handleEvent` 
        (gestion d'évènement) sera traitée exactement comme la fonction elle-même */
        affichageLignes.addEventListener("change", {
            handleEvent: function(event) {
                affichageLignes.form.submit();
            }
        });

        /* Permet d'afficher la valeur de l'option de l'élément htlml select en utilisant la méthode 
        getElementById() avant ou après l'envoi du formulaire (commandes.phtml ligne 21 et var.php
        ligne 42) */
        document.getElementById("affichageLignes").value = varPhp.valeur2;
    }

    if(document.getElementById("arrowDownNum")) {
        /* Si la propriété valeur3 contenue dans la variable varPhp est différente de la valeur "noSort" 
        (voir fichiers var.php ligne 43 et commandes.php lignes 130, 146, 151, 156 et 169), */
        if(varPhp.valeur3 != "noSort") {
            /* si varPhp.valeur3 correspond à la chaîne de caractères triDesc, */
            if(varPhp.valeur3 == "triDesc") {
                /* on masque la flèche ascendante dans la cellule d'en-tête "Commande" du tableau (fichier 
                commandes.phtml ligne 35), */
                document.getElementById("arrowUpNum").style.display = "none";
            }

            else {
                // sinon on masque la flèche descendante
                document.getElementById("arrowDownNum").style.display = "none";
            }
        }
    }

    if(document.getElementById("arrowDownDate")) {
        if(varPhp.valeur4 != "noSort") {
            if(varPhp.valeur4 == "triDesc") {
                document.getElementById("arrowUpDate").style.display = "none";
            }

            else {
                document.getElementById("arrowDownDate").style.display = "none";
            }
        }
    }

    if(document.getElementById("arrowDownTotal")) {
        if(varPhp.valeur5 != "noSort") {
            if(varPhp.valeur5 == "triDesc") {
                document.getElementById("arrowUpTotal").style.display = "none";
            }

            else {
                document.getElementById("arrowDownTotal").style.display = "none";
            }
        }
    }

    if(document.getElementById("noTriNum")) {
        if(varPhp.valeur6 == "singleOrder") {
            /* On affiche les titres de l'en-tête du tableau et on masque les boutons servant au tri 
            (commandes.phtml lignes 35 à 47) */
            document.getElementById("noTriNum").style.display = "initial";
            document.getElementById("noTriDate").style.display = "initial";
            document.getElementById("noTriTotal").style.display = "initial";
            document.getElementById("triNum").style.display = "none";
            document.getElementById("triDate").style.display = "none";
            document.getElementById("triTotal").style.display = "none";
        }
    }
};

// True pour que l'exécution du script continue pendant le chargement, false pour attendre
xhttp.open("GET", "var.php", true);
xhttp.send();