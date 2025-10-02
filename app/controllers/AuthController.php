<?php
if (isset($_POST['ok'])) { // notre bouton a t-il été cliqué
    //si oui on recupere les données suivants
    $nom = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    //on se connecte a la base de donnée
    $servername = "localhost";
    $username = "root";
    $dbpassword = "";
    $dbname = "utilisateurs";

    try{
        $bdd = new PDO("mysql:host=$servername;dbname=$dbname", $username, $dbpassword);
        $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e){
        echo "Connection echouer: " . $e->getMessage();
    }

}
?>
