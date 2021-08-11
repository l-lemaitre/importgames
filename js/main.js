// JS Document

// Si l'id affichageNavmenu est déclaré et différent de NULL,
if(document.getElementById("affichageNavmenu")) {
    /* On affiche le sous-menu quand l'utilisateur click sur le bouton dont l'id est affichageNavmenu si la taille de la 
    fenêtre est comprise entre 300px minimum et 600px maximum (voir fichier layout.phtml ligne 77) */
    function affichageNavmenu() {
        let affichage = document.getElementById("navMenu");

        if(affichage.className === "navMenu") {
            affichage.className += " responsive";
        }

        else {
            affichage.className = "navMenu";
        }
    }

    let affichageNav = document.getElementById("affichageNavmenu");

    affichageNav.onclick = affichageNavmenu;
}


if(document.getElementById("tablePanier")) {
    // Fonction pour positionner le bouton "Valider ma commande" dans le fichier panier.phtml lignes 70 à 85
    function btnValWidth() {
        // Pour les écrans dont la taille n'est pas comprise entre 300 et 600px de large,
        if(!window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
            /* on affecte à la variable positionTable l'objet Element renvoyé par getElementById(), représentant 
            l'élément dont l'id correspond à tablePanier (panier.phtml ligne 34) */
            let positionTable = document.getElementById("tablePanier");
            /* et on utilise la méthode getElementById() pour définir la largeur de l'élément dont l'id correspond à 
            positionBtnVal selon la largeur totale, y compris le padding et la taille des bordures, de positionTable */
            document.getElementById("positionBtnVal").style.width = positionTable.offsetWidth + "px";
        }
    }

    /* On utilise la méthode addEventListener() pour appeler la fonction btnValWidth à chaque fois que l'événement 
    de type load est envoyé à la cible window */
    window.addEventListener("load", btnValWidth);

    window.addEventListener("resize", btnValWidth);
}


/* Création d'un switch mode sombre/clair avec des variables CSS (fichier stylesheet.scss
 lignes 28 à 42) */
const toggleSwitch = document.querySelector(".themeSwitch input[type='checkbox']");
const currentTheme = localStorage.getItem("theme"); /* Nous utilisons le localStorage du 
navigateur pour enregistrer les préférences de l'utilisateur. Nous vérifions si la 
préférence de thème est enregistrée, si oui, nous nous y conformons en conséquence */

if(document.getElementById("statutTheme")) {
    // On définit la valeur initiale de la position cocher/décocher de l'input de type checkbox
    if(currentTheme) {
        document.documentElement.setAttribute("dataTheme", currentTheme);
      
        if(currentTheme === "dark") {
            toggleSwitch.checked = true;
            document.getElementById("statutTheme").innerHTML = ("Thème sombre activé");

            if(window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
                document.getElementById("statutTheme300").innerHTML = ("Thème sombre");
            }
        }

        else {
            document.getElementById("statutTheme").innerHTML = ("Thème clair activé");

            if(window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
                document.getElementById("statutTheme300").innerHTML = ("Thème clair");
            }
        }
    }

    /* On définit l'affichage de la page en fonction de la position cocher/décocher de l'input de 
    type checkbox */
    function switchTheme(e) {
        if(e.target.checked) {
            document.documentElement.setAttribute("dataTheme", "dark"); /* L'attribut dataTheme 
            du fichier slylesheet.scss ligne 28 est ajouté à notre élément root */
            localStorage.setItem("theme", "dark");
            document.getElementById("statutTheme").innerHTML = ("Thème sombre activé");

            if(window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
                document.getElementById("statutTheme300").innerHTML = ("Thème sombre");
            }
        }

        else {
            document.documentElement.setAttribute("dataTheme", "light");
            localStorage.setItem("theme", "light");
            document.getElementById("statutTheme").innerHTML = ("Thème clair activé");

            if(window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
                document.getElementById("statutTheme300").innerHTML = ("Thème clair");
            }
        }    
    }

    // Ajout du gestionnaire d'événement
    toggleSwitch.addEventListener("change", switchTheme, false);
}

else {
    if(currentTheme) {
        document.documentElement.setAttribute("dataTheme", currentTheme);
    }
}


/* La fonction scrollHeader() redimensionne les balises du header dont l'Id est sélectionné lorsque 
l'utilisateur fait défiler la page vers le bas */
function scrollHeader() {
    if(document.body.scrollTop > 5 || document.documentElement.scrollTop > 50) {
        document.getElementById("header").style.height = "9.2rem";
        document.getElementById("header").style.padding = "2.7rem 0";
        document.getElementById("panierSousmenu").style.top = "6.3rem";

        if(window.matchMedia("(min-width: 601px) and (max-width: 1024px)").matches) {
            document.getElementById("navMenu").style.marginTop = "-1rem";
            document.getElementById("panierRecherche").style.marginTop = "-0.2rem";
            document.getElementById("panierSousmenu").style.top = "5.1rem";
        }

        else if(window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
            document.getElementById("header").style.height = "5.8rem";
            document.getElementById("imgLogo").style.display = "none";
            document.getElementById("logoTitle").style.display = "initial";
            document.getElementById("recherche").style.display = "none";
            document.getElementById("panier").style.margin = "-1.5rem 6.2rem 0 0";
        }
    }

    else {
        document.getElementById("header").style.height = "14.1rem";
        document.getElementById("header").style.padding = "5.4rem 0";
        document.getElementById("panierSousmenu").style.top = "9rem";

        if(window.matchMedia("(min-width: 601px) and (max-width: 1024px)").matches) {
            document.getElementById("navMenu").style.marginTop = "initial";
            document.getElementById("panierRecherche").style.marginTop = "0.9rem";
        }

        else if(window.matchMedia("(min-width: 300px) and (max-width: 600px)").matches) {
            document.getElementById("header").style.height = "12rem";
            document.getElementById("imgLogo").style.display = "initial";
            document.getElementById("logoTitle").style.display = "none";
            document.getElementById("recherche").style.display = "initial";
            document.getElementById("panier").style.margin = "-0.2rem 1.5rem";
        }
    }
}

// Lorsque la fenêtre défile, la fonction scrollAction() active scrollHeader()
window.onscroll = function scrollAction() {
    scrollHeader();
};


// Si l'id panierBlank est déclaré et différent de NULL
if(document.getElementById("panierBlank")) {
    const panierBlank = document.getElementById("panierBlank");

    /* On crée une nouvelle fenêtre de navigation secondaire avec la fonction window.open et on renvoie le 
    booléen false pour annuler l'action par défaut du lien. Dans le cas où JavaScript est désactivé ou non 
    existant dans le navigateur de l'utilisateur et que l'évènement onclick est ignoré on ajoute un attribut 
    target avec le mot-clé _blank dans le fichier layout.phtml ligne 110 */
    panierBlank.onclick = function() {
        window.open(this.href, "", "toolbar=no, location=no, directories=no, status=yes, scrollbars=yes, resizable=yes, copyhistory=no, width=595, height=350");
        return false;
    };
}


// Voir fichier facture.phtml ligne 96
if(document.getElementById("print")) {
    const print = document.getElementById("print");

    print.addEventListener("click", function(event) {
        window.print();
    });
}


// Voir fichier modifadresse.phtml ligne 343
if(document.getElementById("resetAdress")) {
    const resetAdress = document.getElementById("resetAdress");

    resetAdress.addEventListener("click", function(event) {
        let confReset = confirm("Voulez vous vraiment supprimer votre adresse ?");

        if(confReset == true) {
            this.form.submit();
        }
    });
}


// Voir fichier modifparametres.phtml ligne 95
if(document.getElementById("resetCte")) {
    const resetCte = document.getElementById("resetCte");

    resetCte.addEventListener("click", function(event) {
        let confReset = confirm("Voulez vous vraiment supprimer votre compte ? Cette action est irréversible.");

        if(confReset == true) {
            this.form.submit();
        }
    });
}