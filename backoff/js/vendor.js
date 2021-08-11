// JS Document

/* Lorsqu'on active le bouton "changetype" l'input change entre le type password ou text. Voir fonction suivante 
et dans le fichier index.phtml ligne 38 pour exemple d'utilisation */
$(".changeType").on("click", function() {
    if($(this).prev("input").attr("type") == "password") {
        changeType($(this).prev("input"), "text");
    }

    else {
        changeType($(this).prev("input"), "password");
    }
  
    return false;
});

/* Fonction pour créer un système de masquage/démasquage d’un champ de mot de passe
(source https://gist.github.com/3559343) */
function changeType(x, type) {
    if(x.prop("type") == type)
        return x;

    try {
        return x.prop("type", type); // IE ne permet pas cela
    } 

    catch(e) {
        /* On essaie de recréer l'élément
        jQuery n'a pas de méthode html() pour l'élément, donc nous devons d'abord le mettre
         dans une div */
        let html = $("<div>").append(x.clone()).html();

        let regex = /type=(\")?([^\"\s]+)(\")?/; // correspond à type=text ou type="text"

        /* Si on ne trouve aucune correspondance, nous ajoutons l'attribut type à la fin;
         sinon, nous le remplaçons */
        let tmp = $(html.match(regex) == null ?
            html.replace(">", ' type="' + type + '">') :
            html.replace(regex, 'type="' + type + '"') );

        // On copie les données de l'ancien élément
        tmp.data("type", x.data("type") );
        let events = x.data("events");
        let cb = function(events) {
            return function() {
                //On lie tous les événements antérieurs
                for(i in events) {
                    let y = events[i];
                    for(j in y)
                    tmp.bind(i, y[j].handler);
                }
            }
        }

        (events);
        x.replaceWith(tmp);
        setTimeout(cb, 10); // On attend un peu pour appeler la fonction
        return tmp;
    }
}


// Cookie Consent (voir fichier layout.phtml ligne 216)
/* Le script qui gère le basculement de la fenêtre contextuelle utilise jQuery et il est défini pour retarder 
l'avertissement de 4 secondes après le chargement de la page */
$(document).ready(function() {
    if(Cookies.get("choixCookies") === undefined) {
        setTimeout(function() {
            $("#cookieConsent").fadeIn(200);
        }, 4000);

        $("#cookieConsentOK").click(function(e) {
            e.preventDefault();
            $("#cookieConsent").fadeOut(200);
            Cookies.set("choixCookies", "oui", { expires: 365 });
        });

        $("#closeCookieConsent").click(function() {
            $("#cookieConsent").fadeOut(200);
        });
    }
});