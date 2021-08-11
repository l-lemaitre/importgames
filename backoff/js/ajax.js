// JS Document

// On utilise l'objet XMLHttpRequest de la méthode AJAX
let xhttp = new XMLHttpRequest();

xhttp.onload = function() {
    /* La variable à récupérer est contenue dans l'objet response et l'attribut responseText 
    dont on construit la valeur JavaScript en utilisant la méthode JSON.parse() */
    let varBack = JSON.parse(this.responseText);

    /* Pour chaque itération du nombre d'id correspondants au choix du nombre de lignes à afficher (voir 
    fichiers varback.php ligne 70 et bdd_connection.php ligne 108), */
    for(let i = 0; i < Object.keys(varBack.idLignes).length + 1; i++) {
        /* si les id correspondants à la propriété idLignes contenues dans la variable varBack sont 
        déclarés et différents de NULL, */
        if(document.getElementById(varBack.idLignes[i])) {
            /* on affecte à la constante affichageLignes l'objet représentant l'élément dont l'id 
            correspond à la chaîne de caractères de la propriété idLignes */
            const affichageLignes = document.getElementById(varBack.idLignes[i]);

            /* L'interface EventListener représente un objet qui peut gérer un évènement distribué par 
            un objet EventTarget. On ajoute un gestionnaire pour l'évènement "change" qui fournit une 
            fonction de rappel */
            affichageLignes.addEventListener("change", function(event) {
                this.form.submit();
            });

            /* Permet d'afficher la valeur de l'option de l'élément html select en utilisant la méthode 
            getElementById() avant ou après l'envoi du formulaire (administrateurs.phtml ligne 24, bdd_connection.php 
            ligne 108 et varback.php ligne 72) */
            document.getElementById(varBack.idLignes[i]).value = varBack.lignes[i];
        }
    }

    if(document.getElementById("lienPage" + varBack.page)) {
        /* On modifie la couleur du texte et du fond pour le lien correspondant au numéro de page du 
        header (voir fichier administrateurs.phtml ligne 161 pour exemple d'utilisation) */
        document.getElementById("lienPage" + varBack.page).style.color = "#fafafa";
        document.getElementById("lienPage" + varBack.page).style.backgroundColor = "#ff8b2b";
    }

    if(document.getElementById("lienPageTop" + varBack.page)) {
        // Voir fichier administrateurs.phtml ligne 58 pour exemple d'utilisation
        document.getElementById("lienPageTop" + varBack.page).style.color = "#fafafa";
        document.getElementById("lienPageTop" + varBack.page).style.backgroundColor = "#ff8b2b";
    }

    // Voir fichiers ficheproduit.php ligne 212 et ficheproduit.phtml ligne 168 pour exemple d'utilisation
    if(document.getElementById(varBack.elementId[1])) {
        const elementId = document.getElementById(varBack.elementId[1]);

        elementId.addEventListener("click", function(event) {
            // Voir fichier ficheproduit.php ligne 215
            let conf = confirm(varBack.msgConfirm);

            if(conf == true) {
                // On valide l'envoi du formulaire
                this.form.submit();
            }
        });
    }

    /* Voir fichiers ajoutimage.phtml ou ajoutproduit.phtml ligne 23, ajoutimage.php ou ajoutproduit.php ligne 53 */
    if(document.getElementById(varBack.elementId[2])) {
        /* Permet d'afficher la description correspondant à la valeur de l'option de l'élément html 
        select en utilisant la méthode getElementById() après l'envoi du formulaire (voir fichiers 
        ajoutimage.php lignes 56 et 80 et ajoutproduit.php lignes 56 et 86) */
        document.getElementById(varBack.elementId[2]).value = varBack.selected[2];
    }

    // Voir fichier administrateurs.php ligne 317 pour exemple d'utilisation
    if(varBack.loopElementId[1]) {
        for(let i = 0; i < Object.keys(varBack.loopElementId[1]).length; i++) {
            // Voir fichier administrateurs.phtml ligne 136
            if(document.getElementById(varBack.loopElementId[1][i])) {
                const loopElementId = document.getElementById(varBack.loopElementId[1][i]);

                loopElementId.addEventListener("click", function(event) {
                    // Voir fichier administrateurs.php ligne 321
                    let conf = confirm(varBack.loopMsgConfirm);

                    if(conf != true) {
                        /* Si l'utilisateur ne confirme pas son choix on utilise la méthode preventDefault() 
                        pour annuler l'événement, ce qui signifie que l'action par défaut qui appartient à 
                        l'événement ne se produira pas */
                        event.preventDefault();
                    }
                });
            }
        }
    }

    if(varBack.loopElementId[2]) {
        for(let i = 0; i < Object.keys(varBack.loopElementId[2]).length; i++) {
            // Voir fichiers produits.php ligne 465 et produits.phtml ligne 178
            if(document.getElementById(varBack.loopElementId[2][i])) {
                const loopElementId = document.getElementById(varBack.loopElementId[2][i]);

                loopElementId.addEventListener("click", function(event) {
                    let conf = confirm(varBack.loopMsgConfirm);

                    if(conf != true) {
                        event.preventDefault();
                    }
                });
            }
        }
    }

    /* Si la propriété verifReset contenue dans la variable varBack est égale à la chaîne de caractères 
    verifResetError (voir fichiers varback.php ligne 78 et administrateurs.php lignes 355 et 361 pour exemple 
    d'utilisation) */
    if(varBack.verifReset == "verifResetError") {
        window.alert("Erreur de vérification.");
    }

    // Voir fichiers varback.php ligne 79 et administrateurs.php ligne 349
    else if(varBack.refReset == "refResetError") {
        window.alert("La requête ne provient pas du formulaire.");
    }

    /* Si l'index 1 de la propriété selected contenue dans la variable varBack vaut true (voir fichiers varback.php ligne 82 
    et administrateurs.php ligne 160 pour exemple d'utilisation) */
    if(varBack.selected[1]) {
        /* On affecte à la variable idTri l'objet représentant la valeur de l'attribut id contraire au 
        choix d'affichage du tri (voir fichiers varback.php lignes 80, 81 et 82, bdd_connection.php ligne 112 et 
        administrateurs.php lignes 160, 169, 184, 191, 201, 207, 217, 223, 233, 239, 249 et 255), */
        let idTri = varBack.idTriList[varBack.selected[1]][varBack.idTriHidden[varBack.selected[1]]];

        /* pour masquer la flèche contraire au choix d'affichage du tri dans la cellule d'en-tête du tableau (voir 
        fichier administrateurs.phtml lignes 70 à 98), */
        document.getElementById(idTri).style.display = "none";


        // Voir fichiers achats-magasin.phtml et ventes.phtml ligne 21
        if(document.getElementById("affichageProd")) {
            const affichageProd = document.getElementById("affichageProd");

            affichageProd.addEventListener("change", function(event) {
                this.form.submit();
            });

            document.getElementById("affichageProd").value = varBack.affichageProd[varBack.selected[1]];
        }
    }
};

// True pour que l'exécution du script continue pendant le chargement, false pour attendre
xhttp.open("GET", "varback.php", true);
xhttp.send();