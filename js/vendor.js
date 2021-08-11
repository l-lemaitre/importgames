// JS Document

/* Lorsqu'on active le bouton "changetype" l'input change entre le type password ou text. Voir fonction suivante 
et dans le fichier connexion.phtml ligne 22 pour exemple d'utilisation */
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


// Cookie Consent (voir fichier layout.phtml ligne 149)
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


// FLEXSLIDER (voir fichier index.phtml ligne 3)
// Fonction requise pour s'assurer que le contenu de la page est chargé avant l'initialisation du plugin
$(window).on("load", function() {
    $(".flexslider").flexslider({
        controlNav: true,
        directionNav: true,
        slideshowSpeed: 5000,
        animation: "fade"
    });
});

// Si le media query correspond, masque les flèches et la direction de navigation
function masquerCtrlflexslider() {
  if (window.matchMedia("(max-width: 300px)").matches) {
    $(".flexslider").flexslider({
        controlNav: false,
        directionNav: false,
        slideshowSpeed: 4000,
        animation: "fade"
    });
  }
}

masquerCtrlflexslider();


// OWLCAROUSEL || Slider Produit (voir fichier produit.phtml lignes 37 et 45)
$(document).ready(function () {
    let sync1 = $(".sync1");
    let sync2 = $(".sync2");

    sync1.owlCarousel({
        singleItem: true,
        slideSpeed: 1000,
        navigation: true,
        pagination: false,
        afterAction: syncPosition,
        responsiveRefreshRate: 200,
        navigationText: [
            "<i class='fa fa-chevron-left'></i>",
            "<i class='fa fa-chevron-right'></i>"
        ]
    });

    sync2.owlCarousel({
        items: 4,
        itemsDesktop: [1199, 4],
        itemsDesktopSmall: [979, 3],
        itemsTablet: [768, 3],
        itemsMobile: [479, 2],
        pagination: false,
        responsiveRefreshRate: 100,
        afterInit: function (el) {
            el.find(".owl-item").eq(0).addClass("synced");
        }
    });

    function syncPosition(el) {
        let current = this.currentItem;

        $(".sync2")
                .find(".owl-item")
                .removeClass("synced")
                .eq(current)
                .addClass("synced")
        if($(".sync2").data("owlCarousel") !== undefined) {
            center(current)
        }
    }

    $(".sync2").on("click", ".owl-item", function (e) {
        e.preventDefault();
        let number = $(this).data("owlItem");
        sync1.trigger("owl.goTo", number);
    });

    function center(number) {
        let sync2visible = sync2.data("owlCarousel").owl.visibleItems;
        let num = number;
        let found = false;

        for(let i in sync2visible) {
            if(num === sync2visible[i]) {
                let found = true;
            }
        }

        if(found === false) {
            if(num > sync2visible[sync2visible.length - 1]) {
                sync2.trigger("owl.goTo", num - sync2visible.length + 2)
            } 

            else {
                if(num - 1 === -1) {
                    num = 0;
                }
                sync2.trigger("owl.goTo", num);
            }
        } 

        else if(num === sync2visible[sync2visible.length - 1]) {
            sync2.trigger("owl.goTo", sync2visible[1])
        } 

        else if(num === sync2visible[0]) {
            sync2.trigger("owl.goTo", num - 1)
        }
    }
});


// slick || Carrousel Produits (voir fichier produit.phtml ligne 136)
$(document).ready(function () {
    "use strict";

    $(".carrouselProduits").slick({
        dots: false,
        infinite: true,
        speed: 300,
        slidesToShow: 4,
        slidesToScroll: 1,
        responsive: [{
            breakpoint: 1024,
            settings: {
                slidesToShow: 3,
                slidesToScroll: 1,
                infinite: true,
                dots: false
            }
        }, {
            breakpoint: 600,
            settings: {
                slidesToShow: 1,
                slidesToScroll: 1
            }
        }]
    });
});


// ISOTOPE || Produits Populaires (voir fichier index.phtml ligne 28)
// Initialise Isotope
$(window).on("load", function() {
    let iso = new Isotope(".isotope", {
        itemSelector: ".isotopeArticle",
        layoutMode: "fitRows"
    });

    // Clic sur le bouton de filtre des catégories
    let filtersElem = document.querySelector(".filtresSelectcat");
    filtersElem.addEventListener("click", function(event) {
        // Ne fonctionne qu'avec les boutons
        if(!matchesSelector(event.target, "button") ) {
            return;
        }

        let filterValue = event.target.getAttribute("data-filter");
        // Utilise la fonction de filtre correspondant
        iso.arrange({filter: filterValue});
    });

    // Modifie la classe cochée sur les boutons
    let selectCats = document.querySelectorAll(".selectCat");
    for(let i=0, len = selectCats.length; i < len; i++) {
        let selectCat = selectCats[i];
        radioSelectcat(selectCat);
    }
});

function radioSelectcat(selectCat) {
    selectCat.addEventListener("click", function(event) {
        // Ne fonctionne qu'avec les boutons
        if(!matchesSelector(event.target, "button")) {
            return;
        }
        selectCat.querySelector(".selectionne").classList.remove("selectionne");
        event.target.classList.add("selectionne");
    });
}


// FANCYBOX (voir fichier produit.phtml ligne 41)
$("[data-fancybox='gallery']").fancybox({
    buttons : [ 
        "zoom",
        "slideShow",
        "thumbs",
        "close"
    ],
    animationEffect : "zoom-in-out",
    transitionEffect : "fade",
    transitionDuration: 1000
});