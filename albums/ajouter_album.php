<?php
session_start();
include("fonctions.php");

if(!admin()){
    header("Location: index.php");
    exit();
}

// Connexion à la base de données
$cnx = mysqli_connect("localhost", "root", "", "albums");

// Vérifier si la connexion est bien établie
if (mysqli_connect_errno()) {
    echo "Échec de la connexion : " . mysqli_connect_error();
    exit();
}

// Traitement du formulaire pour ajouter un album
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!empty($_POST['new_album'])) {
        $new_album = mysqli_real_escape_string($cnx, $_POST['new_album']);
        $sqlInsert = "INSERT INTO albums (nomAlb) VALUES ('$new_album')";
        if (mysqli_query($cnx, $sqlInsert)) {
            $message = "Album ajouté avec succès!";
        } else {
            $message = "Erreur lors de l'ajout de l'album : " . mysqli_error($cnx);
        }
    } else {
        $message = "Le nom de l'album ne peut pas être vide.";
    }
}

// Récupération des albums existants
$sql = "SELECT * FROM albums";
$res = mysqli_query($cnx, $sql);
?>

<!doctype html> 
<html lang="fr">
<head>
    <meta charset="utf-8"> 
    <title>Album photo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Mes albums</h1><br><br>

    <div class="titres">
        <?php
        while ($ligne = mysqli_fetch_array($res)) {
            echo '<a href="index.php?idAlb=' . $ligne["idAlb"] . '">' . $ligne['nomAlb'] . '</a>';
        }
        ?>
        <a href="ajouter_album.php">+</a>
        <a class="logo" href="modifier_album.php"></a>
        <a href="supprimer_album.php?idAlb">🆑</a>
    </div>
    <br><br><br>

    <!-- Formulaire pour ajouter un nouvel album -->
    <form action="" method="post">
        <label>Nom du nouvel album :</label>
        <input name="new_album" id="new_album" type="text" />

        <button type="submit">Enregistrer</button>
    </form>

    <?php
    // Afficher le message (erreur ou succès)
    if (!empty($message)) {
        echo "<div class='message'>$message</div>";
    }

    // Libération des résultats de la requête
    mysqli_free_result($res);
    mysqli_close($cnx);
    ?>

</body>
</html>
