
<?php
// Connexion à la base de données
$cnx = mysqli_connect("localhost", "root", "", "albums");

if (mysqli_connect_errno()) {
    echo "Échec de la connexion : " . mysqli_connect_error();
    exit();
}

// Initialisation des variables
$message = "";
$photoName = "";
$albums = [];

// Vérifier si l'ID de la photo est passé en GET
if (isset($_GET['idPh'])) {
    $idPh = mysqli_real_escape_string($cnx, $_GET['idPh']);

    // Récupérer les informations de la photo
    $sqlPhoto = "SELECT * FROM photos WHERE idPh = '$idPh'";
    $resPhoto = mysqli_query($cnx, $sqlPhoto);

    if ($resPhoto && mysqli_num_rows($resPhoto) > 0) {
        $photo = mysqli_fetch_array($resPhoto);
        $photoName = $photo['nomPh'];
    } else {
        $message = "Aucune photo trouvée avec cet ID.";
    }

    // Récupérer les albums existants
    $sqlAlbums = "SELECT * FROM albums";
    $resAlbums = mysqli_query($cnx, $sqlAlbums);

    // Récupérer les albums où la photo est déjà présente
    $sqlPhotoAlbums = "SELECT idAlb FROM comporter WHERE idPh = '$idPh'";
    $resPhotoAlbums = mysqli_query($cnx, $sqlPhotoAlbums);
    while ($row = mysqli_fetch_array($resPhotoAlbums)) {
        $albums[] = $row['idAlb'];
}
}

// Traitement du formulaire pour modifier les albums de la photo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['albums'])) {
        $selectedAlbums = $_POST['albums'];
        
        // Supprimer toutes les associations existantes de cette photo
        $sqlDelete = "DELETE FROM comporter WHERE idPh = '$idPh'";
        mysqli_query($cnx, $sqlDelete);

        // Ajouter la photo à chaque album sélectionné
        foreach ($selectedAlbums as $albumId) {
            $sqlInsert = "INSERT INTO comporter (idAlb, idPh) VALUES ('$albumId', '$idPh')";
            mysqli_query($cnx, $sqlInsert);
        }

        $message = "Les albums ont été modifiés avec succès.";
    } else {
        $message = "Veuillez sélectionner au moins un album.";
    }
}

?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Modifier photo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Mes albums</h1><br><br>

    <!-- Afficher les albums existants comme sur les autres pages -->
    <div class="titres">
        <?php
        // Récupérer tous les albums pour les afficher
        $sqlAllAlbums = "SELECT * FROM albums";
        $resAllAlbums = mysqli_query($cnx, $sqlAllAlbums);
        while ($album = mysqli_fetch_array($resAllAlbums)) {
            echo '<a href="index.php?idAlb=' . $album["idAlb"] . '">' . $album['nomAlb'] . '</a>';
        }
        ?>
        <a href="ajouter_album.php">+</a>
        <a class="logo" href="modifier_album.php"></a>
    </div>
    <br><br><br>

    <div class="message">
        <?php
        if (!empty($message)) {
            echo $message;
        }
        ?>
    </div>

    <h2>Photo : <?php echo $photoName; ?></h2>

    <!-- Formulaire pour sélectionner les albums dans lesquels ajouter la photo -->
    <form action="modifier_photo.php?idPh=<?php echo $idPh; ?>" method="post">
        <label for="albums">Choisissez les albums dans lesquels ajouter cette photo :</label><br><br>
        <?php
        // Afficher tous les albums existants avec des cases à cocher
        while ($album = mysqli_fetch_array($resAlbums)) {
            $checked = in_array($album['idAlb'], $albums) ? "checked" : "";
            echo '<input type="checkbox" name="albums[]" value="' . $album['idAlb'] . '" ' . $checked . ' /> ' . $album['nomAlb'] . '<br>';
        }
        ?>
        <button type="submit">Enregistrer</button>
    </form>

    <a href="index.php">Retour à l'index</a>

    <?php
    // Libération des résultats de la requête
    mysqli_free_result($resPhoto);
    mysqli_free_result($resAlbums);
    mysqli_free_result($resPhotoAlbums);
    mysqli_free_result($resAllAlbums);
    mysqli_close($cnx);
    ?>

</body>
</html>
