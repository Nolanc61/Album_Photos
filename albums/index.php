<div class="titres">
    <?php
        $cnx = mysqli_connect("localhost", "root", "", "albums");
        if(!isset($_GET['idAlb'])){
            $sql = "SELECT * FROM albums LIMIT 1";
            $res = mysqli_query($cnx, $sql);
            $_GET['idAlb']=mysqli_fetch_array($res)["idAlb"];
        }
    ?>
</div>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Album photo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Mes albums</h1><br><br>

    <?php

        if (mysqli_connect_errno()) {
            echo "Échec de la connexion : ".mysqli_connect_error();
            exit();
        }

        $sql = "SELECT * FROM albums";
        $res = mysqli_query($cnx, $sql);
        ?><div class="titres"><?php
        while ($ligne = mysqli_fetch_array($res)) {
            echo '<a href="index.php?idAlb='.$ligne["idAlb"].'">'.$ligne['nomAlb'].'</a>';
        }
        ?>
        <a href="ajouter_album.php">+</a>
        <a class="logo" href="modifier_album.php?idAlb=<?= $_GET["idAlb"] ?>"></a>
        </div>
        <br><br><br>

        <?php
        $sqlImages = "SELECT nomPh FROM photos JOIN comporter ON photos.idPh = comporter.idPh WHERE idAlb=".$_GET["idAlb"];
        $resImages = mysqli_query($cnx, $sqlImages);

        while ($ligne = mysqli_fetch_array($resImages)) {
            echo '<img src="photos/'.$ligne["nomPh"].'">';
        }

        mysqli_free_result($res);

        mysqli_close($cnx);

    ?>

</body>
</html>