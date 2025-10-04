<?php
//if (isset($_POST['ok'])) { // notre bouton a t-il été cliqué
//si oui on recupere les données suivants


//on se connecte a la base de donnée
$servername = "mysql-bdelivesae.alwaysdata.net";
$username = "429915";
$dbpassword = "bdelive+6";
$dbname = "bdelivesae_db";

try{
    $bdd = new PDO("mysql:host=$servername;dbname=$dbname", $username, $dbpassword);
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    echo "Connexion échouer: " . $e->getMessage();
}

//}
?>
