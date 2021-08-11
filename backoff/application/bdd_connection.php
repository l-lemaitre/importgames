<?php
    // **PRÉVENTION DU PIRATAGE DE SESSION**
    // Empêche les attaques javascript XSS visant à voler l'ID de session
    ini_set("session.cookie_httponly", 1);

    // **PRÉVENTION DE LA FIXATION DE SESSION**
    // L'ID de session ne peut pas être transmis via les URL
    ini_set("session.use_only_cookies", 1);

    // Utilise une connexion sécurisée (HTTPS) si possible
    // ini_set("session.cookie_secure", 1);


	// Démarre une nouvelle session ou reprend une session existante
	session_start();


    //On crée un jeton d'authentification pour se protéger contre la faille CSRF
    $token = bin2hex(random_bytes(32));


    // Inclut le fichier placé en argument dir.php à la page bdd_connection.php (ligne 43)
    if(empty($dir)) {
        $dir = "dir";

        // On limite l'inclusion aux fichiers .php en ajoutant dynamiquement l'extension. On supprime également d'éventuels espaces
        $dir = trim($dir . ".php");
    }

    // On évite les caractères qui permettent de naviguer dans les répertoires
    $dir = str_replace("../", "protect", $dir);
    $dir = str_replace(";", "protect", $dir);
    $dir = str_replace("%", "protect", $dir);

    // On interdit l'inclusion de dossiers protégés par htaccess
    if(preg_match("/backoff/", $dir)) {
        echo "Vous n'avez pas accès à ce répertoire.";
     }

    else {
        // On vérifie que la page est bien sur le serveur en utilisant la constante magique __DIR__ qui représente le dossier du fichier bdd_connection.php
        if(file_exists(__DIR__ . "/../" . $dir) && $dir != "index.php") {
           include __DIR__ . "/../" . $dir;
        }

        else {
            echo "Page inexistante.";
        }
    }


    // Déclaration de la classe ConnexionBdd
    class ConnexionBdd {
        // On déclare nos attributs et on les initialisent (sauf _connexion) en leur affectant les valeurs suivantes
        private $_host = "localhost"; // Nom de l'hôte
        private $_name = "importgames"; // Nom de la base de données
        private $_user = "À COMPLÉTER"; // Utilisateur
        private $_pass = "À COMPLÉTER"; // Mot de passe
        private $_connexion;

        // On déclare la méthode constructeur pour initialiser les attributs suivants dès la création de l'objet $bdd (ligne 97)
        public function __construct($_host = NULL, $_name = NULL, $_user = NULL, $_pass = NULL) {
            if($_host != NULL){
                $this->_host = $_host;
                $this->_name = $_name;
                $this->_user = $_user;
                $this->_pass = $_pass;
            }

            try {
                $this->_connexion = new PDO("mysql:host=" . $this->_host . ";dbname=" . $this->_name,
                  $this->_user, $this->_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND =>"SET NAMES UTF8", 
                  PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            }

            catch(PDOException $error) {
                echo "Erreur : Impossible de se connecter à la base de données.";
                die();
            }
        }   

        // On déclare la méthode query qui servira d'accesseur pour afficher les données de la bdd
        public function query($sql, $data = array()) {
            $query = $this->_connexion->prepare($sql);
            $query->execute($data);
            return $query;
        }

        // On déclare la méthode insert qui servira de mutateur pour modifier ou insérer des données dans la bdd
        public function insert($sql, $data = array()) {
            $query = $this->_connexion->prepare($sql);
            $query->execute($data);
        }
    }
  
    // On crée l'objet $bdd en instanciant la classe ConnexionBdd
    $bdd = new ConnexionBdd;


    // Si un administrateur est connecté,
    if(isset($_SESSION["admin_id"])) {
        // on retourne un tableau de chaînes de caractères à partir de la variable session prv pour afficher les éléments du menu correspondant aux privilèges de l'admin (layout.phtml lignes 43 à 181)
        $prv = explode(",", $_SESSION["prv"]);
    }


    // On stocke dans un tableau dont les index commencent à 1 les valeurs des attributs id correspondants au choix d'affichage du nombre de lignes qu'on affecte à la variable de session idLignes (voir fichiers administrateurs.phtml ligne 24, varback.php ligne 70 et ajax.js ligne 31 pour exemple d'utilisation)
    $_SESSION["idLignes"] = array(1 => "lignesAdmin", "lignesCat", "lignesCom", "lignesImg", "lignesPartn", "lignesProd", "lignesVisit", "lignesAchat", "lignesVente", "lignesSearch", "lignesSlid", "lignesUser");


    // On stocke dans la variable de session idTriList les valeurs des attributs id correspondants aux flêches représentants le choix d'affichage du tri sur chaque page "liste" (voir fichiers administrateurs.phtml lignes 70, 77, 84, 91 et 98, varback.php ligne 80 et ajax.js ligne 128 pour exemple d'utilisation)
    $_SESSION["idTriList"] = array(
        "admin" => array("arrowDownId" => "arrowDownAdminId", "arrowUpId" => "arrowUpAdminId", "arrDwnName" => "arrDwnAdmName", "arrUpName" => "arrUpAdmName", "arrDwnPass" => "arrDwnAdmPass", "arrUpPass" => "arrUpAdmPass", "arrDwnPrv" => "arrDwnAdmPrv", "arrUpPrv" => "arrUpAdmPrv", "arrDwnReg" => "arrDwnAdmReg", "arrUpReg" => "arrUpAdmReg"),
        "cat" => array("arrowDownId" => "arrowDownCatId", "arrowUpId" => "arrowUpCatId", "arrDwnTitre" => "arrDwnCatTitre", "arrUpTitre" => "arrUpCatTitre"),
        "com" => array("arrowDownId" => "arrowDownComId", "arrowUpId" =>"arrowUpComId", "arrDwnUser" => "arrDwnCoUser", "arrUpUser" => "arrUpCoUser", "arrDwnNum" => "arrDwnCoNum", "arrUpNum" => "arrUpCoNum", "arrDwnAdress" => "arrDwnCoAdress", "arrUpAdress" => "arrUpCoAdress", "arrDwnDate" => "arrDwnCoDate", "arrUpDate" => "arrUpCoDate", "arrDwnTotal" => "arrDwnCoTotal", "arrUpTotal" => "arrUpCoTotal"),
        "img" => array("arrowDownId" => "arrowDownImgId", "arrowUpId" => "arrowUpImgId", "arrDwnProd" => "arrDwnImgProd", "arrUpProd" => "arrUpImgProd", "arrDwnUrl" => "arrDwnImgUrl", "arrUpUrl" => "arrUpImgUrl"),
        "partn" => array("arrowDownId" => "arrowDownPartnId", "arrowUpId" => "arrowUpPartnId", "arrDwnNom" => "arrDwnPrtnNom", "arrUpNom" => "arrUpPrtnNom", "arrDwnImg" => "arrDwnPrtnImg", "arrUpImg" => "arrUpPrtnImg", "arrDwnUrl" => "arrDwnPrtnUrl", "arrUpUrl" => "arrUpPrtnUrl"),
        "prod" => array("arrowDownId" => "arrowDownProdId", "arrowUpId" => "arrowUpProdId", "arrDwnCat" => "arrDwnPrdCat", "arrUpCat" => "arrUpPrdCat", "arrDwnTitre" => "arrDwnPrdTitre", "arrUpTitre" => "arrUpPrdTitre", "arrDwnEan" => "arrDwnPrdEan", "arrUpEan" => "arrUpPrdEan", "arrDwnPrix" => "arrDwnPrdPrix", "arrUpPrix" => "arrUpPrdPrix", "arrDwnQte" => "arrDwnPrdQte", "arrUpQte" => "arrUpPrdQte", "arrDwnSortie" => "arrDwnPrdSortie", "arrUpSortie" => "arrUpPrdSortie", "arrDwnDescr" => "arrDwnPrdDescr", "arrUpDescr" => "arrUpPrdDescr", "arrDwnImg" => "arrDwnPrdImg", "arrUpImg" => "arrUpPrdImg", "arrDwnVid" => "arrDwnPrdVid", "arrUpVid" => "arrUpPrdVid", "arrDwnCrea" => "arrDwnPrdCrea", "arrUpCrea" => "arrUpPrdCrea"),
        "visit" => array("arrowDownId" => "arrowDownVisitId", "arrowUpId" => "arrowUpVisitId", "arrDwnProd" => "arrDwnVstProd", "arrUpProd" => "arrUpVstProd", "arrDwnUser" => "arrDwnVstUser", "arrUpUser" => "arrUpVstUser", "arrDwnDate" => "arrDwnVstDate", "arrUpDate" => "arrUpVstDate"),
        "achat" => array("arrowDownId" => "arrowDownAchatId", "arrowUpId" => "arrowUpAchatId", "arrDwnProd" => "arrDwnAchtProd", "arrUpProd" => "arrUpAchtProd", "arrDwnTitre" => "arrDwnAchtTitre", "arrUpTitre" => "arrUpAchtTitre", "arrDwnDate" => "arrDwnAchtDate", "arrUpDate" => "arrUpAchtDate", "arrDwnQte" => "arrDwnAchtQte", "arrUpQte" => "arrUpAchtQte", "arrDwnPrix" => "arrDwnAchtPrix", "arrUpPrix" => "arrUpAchtPrix", "arrDwnTotal" => "arrDwnAchtTotal", "arrUpTotal" => "arrUpAchtTotal"),
        "vente" => array("arrowDownId" => "arrowDownVenteId", "arrowUpId" => "arrowUpVenteId", "arrDwnCom" => "arrDwnVteCom", "arrUpCom" => "arrUpVteCom", "arrDwnProd" => "arrDwnVteProd", "arrUpProd" => "arrUpVteProd", "arrDwnTitre" => "arrDwnVteTitre", "arrUpTitre" => "arrUpVteTitre", "arrDwnDate" => "arrDwnVteDate", "arrUpDate" => "arrUpVteDate", "arrDwnQte" => "arrDwnVteQte", "arrUpQte" => "arrUpVteQte", "arrDwnPrix" => "arrDwnVtePrix", "arrUpPrix" => "arrUpVtePrix", "arrDwnTotal" => "arrDwnVteTotal", "arrUpTotal" => "arrUpVteTotal"),
        "search" => array("arrowDownId" => "arrowDownRecId", "arrowUpId" => "arrowUpRecId", "arrDwnTxt" => "arrDwnRecTxt", "arrUpTxt" => "arrUpRecTxt", "arrDwnUser" => "arrDwnRecUser", "arrUpUser" => "arrUpRecUser", "arrDwnDate" => "arrDwnRecDate", "arrUpDate" => "arrUpRecDate"),
        "slid" => array("arrowDownId" => "arrowDownSlidId", "arrowUpId" => "arrowUpSlidId", "arrDwnTitre" => "arrDwnSlidTitre", "arrUpTitre" => "arrUpSlidTitre", "arrDwnLeg" => "arrDwnSlidLeg", "arrUpLeg" => "arrUpSlidLeg", "arrDwnImg" => "arrDwnSlidImg", "arrUpImg" => "arrUpSlidImg"),
        "user" => array("arrowDownId" => "arrowDownUserId", "arrowUpId" => "arrowUpUserId", "arrDwnUsern" => "arrDwnUsrUsern", "arrUpUsern" => "arrUpUsrUsern", "arrDwnMail" => "arrDwnUsrMail", "arrUpMail" => "arrUpUsrMail", "arrDwnPass" => "arrDwnUsrPass", "arrUpPass" => "arrUpUsrPass", "arrDwnNom" => "arrDwnUsrNom", "arrUpNom" => "arrUpUsrNom", "arrDwnPrenom" => "arrDwnUsrPrenom", "arrUpPrenom" => "arrUpUsrPrenom", "arrDwnAdress" => "arrDwnUsrAdress", "arrUpAdress" => "arrUpUsrAdress", "arrDwnZip" => "arrDwnUsrZip", "arrUpZip" => "arrUpUsrZip", "arrDwnVille" => "arrDwnUsrVille", "arrUpVille" => "arrUpUsrVille", "arrDwnPays" => "arrDwnUsrPays", "arrUpPays" => "arrUpUsrPays", "arrDwnTel" => "arrDwnUsrTel", "arrUpTel" => "arrUpUsrTel", "arrDwnReg" => "arrDwnUsrReg", "arrUpReg" => "arrUpUsrReg", "arrDwnTk" => "arrDwnUsrTk", "arrUpTk" => "arrUpUsrTk", "arrDwnConf" => "arrDwnUsrConf", "arrUpConf" => "arrUpUsrConf", "arrDwnTkStayc" => "arrDwnUsrTkStayc", "arrUpTkStayc" => "arrUpUsrTkStayc", "arrDwnNewPass" => "arrDwnUsrNewPass", "arrUpNewPass" => "arrUpUsrNewPass", "arrDwnUnsub" => "arrDwnUsrUnsub", "arrUpUnsub" => "arrUpUsrUnsub")
    );


    // Si la valeur de la variable de session verifReset correspond à la chaîne de caractères verifResetError,
    if($_SESSION["verifReset"] = "verifResetError") {
        // on détruit la variable de session verifReset (voir fichier ficheproduit.php lignes 203 et 209)
        unset($_SESSION["verifReset"]);
    }

    elseif($_SESSION["refReset"] = "refResetError") {
        // Voir fichier ficheproduit.php ligne 197
        unset($_SESSION["refReset"]);
    }
?>