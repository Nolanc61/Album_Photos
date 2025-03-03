<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Page de connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<h1>Connexion à l'album photo</h1>
</html>

<?php
session_start();
$_SESSION["auth"] = false;

$cnx = mysqli_connect("localhost", "root", "", "albums");

    if (mysqli_connect_errno()) {
        echo "Échec de la connexion : " . mysqli_connect_error();
        exit();
    }

if(empty($_POST)){
    ?>
    <form action="connexion.php" method="post">
        <label for="login">Login :</label>
        <input type="text" id="login" name="login" />

        <label for="pwd">Mot de passe :</label>
        <input type="password" id="pwd" name="pwd" />

        <input type="submit" value="authentification">
    </form>
    <?php
} else {
    // Récupérer les données du formulaire
    $login = $_POST['login'];
    $pwd = $_POST['pwd'];

    $sql = "SELECT COUNT(*) AS nb FROM users WHERE login='".$login."' AND pwd='".$pwd."'";
    $res= mysqli_query($cnx, $sql);
    $nb=mysqli_fetch_array($res)["nb"];
    echo $nb;
    if($nb==1){
        $_SESSION["auth"] = true;
        header("Location: index.php");
    }else{
        header("Location: connexion.php");
    }

}



?>