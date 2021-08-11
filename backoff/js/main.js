// JS Document

// Voir fichier facture-client.phtml ligne 130
if(document.getElementById("print")) {
    const print = document.getElementById("print");

    print.addEventListener("click", function(event) {
        window.print();
    });
}