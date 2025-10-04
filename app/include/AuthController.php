<?php
if (isset($_POST['ok'])) {
    $email = $_POST['email'];
    $mdp = $_POST['pwd'];

    $servername = "mysql-bdelivesae.alwaysdata.net";
    $username = "429915";
    $dbpassword = "bdelive+6";
    $dbname = "bdelivesae_db";

    $link = mysqli_connect($servername, $username, $dbpassword, $dbname) or die('Pb de connexion au serveur: ' . mysqli_connect_error());
    mysqli_select_db($link, $dbname) or die ('Pb de connexion au serveur: ' . mysqli_connect_error());
    $request = 'SELECT * FROM Utilisateurs WHERE email = $email';
    $result = mysqli_query($link, $request);
    if (!$result)
    {
        echo 'Impossible d\'exécuter la requête ', $request, ' : ', mysqli_error($link);
    }
    else
    {
        if (mysqli_num_rows($result) != 0)
        {
            $row = mysqli_fetch_assoc($result);
                if (password_verify($row['pwd'], $mdp)){
                    session_start();
                    $_SESSION['suid'] = session_id();
                    header('Location: homePageView.php');
                }
        }
    }
}
?>
