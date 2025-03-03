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

// Initialisation des variables
$message = "";
$nomAlb = "";

// Vérifier si un album est sélectionné pour la modification
if (isset($_GET['idAlb'])) {
    $album_id = mysqli_real_escape_string($cnx, $_GET['idAlb']);
    
    // Récupérer l'album à modifier
    $sql = "SELECT * FROM albums WHERE idAlb = '$album_id'";
    $res = mysqli_query($cnx, $sql);

    // Si l'album existe, récupérer son nom
    if ($res && mysqli_num_rows($res) > 0) {
        $album = mysqli_fetch_array($res);
        $nomAlb = $album['nomAlb'];
    } else {
        $message = "Aucun album trouvé avec cet ID.";
    }
}

// Traitement du formulaire pour modifier l'album
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['modif_album']) && isset($album_id)) {
    // Récupérer la nouvelle valeur du nom de l'album depuis le formulaire
    $new_album_name = mysqli_real_escape_string($cnx, $_POST['modif_album']);

    // Vérifier si la nouvelle valeur du nom de l'album n'est pas vide
    if (!empty($new_album_name)) {
        // Requête SQL pour mettre à jour le nom de l'album
        $sqlUpdate = "UPDATE albums SET nomAlb = '$new_album_name' WHERE idAlb = '$album_id'";

        // Exécuter la requête et vérifier si elle réussit
        if (mysqli_query($cnx, $sqlUpdate)) {
            $message = "Album modifié avec succès!";
        } else {
            $message = "Erreur lors de la modification de l'album : " . mysqli_error($cnx);
        }
    } else {
        $message = "Le nom de l'album ne peut pas être vide.";
    }
}

// Récupérer tous les albums pour les afficher
$sqlAllAlbums = "SELECT * FROM albums";
$resAllAlbums = mysqli_query($cnx, $sqlAllAlbums);
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
        // Afficher tous les albums
        while ($ligne = mysqli_fetch_array($resAllAlbums)) {
            echo '<a href="index.php?idAlb=' . $ligne["idAlb"] . '">' . $ligne['nomAlb'] . '</a>';
        }
        ?>
        <a href="ajouter_album.php">+</a>
        <a class="logo" href="modifier_album.php"></a>
        <a href="supprimer_album.php?idAlb">🆑</a>
    </div>
    <br><br><br>

    <?php
    // Afficher le formulaire de modification si un album est sélectionné
    if (isset($album)) {
        echo '<form action="modifier_album.php?idAlb=' . $album_id . '" method="post">
                <label>Nom de l\'album :</label>
                <input name="modif_album" id="modif_album" type="text" value="' . $nomAlb . '" />

                <button type="submit">Enregistrer</button>
              </form>';
    } else {
        echo "<div class='message'>Aucun album sélectionné pour la modification.</div>";
    }

    // Afficher le message (erreur ou succès)
    if (!empty($message)) {
        echo "<div class='message'>$message</div>";
    }

    // Libération des résultats de la requête
    mysqli_free_result($resAllAlbums);
    if (isset($res)) {
        mysqli_free_result($res);
    }
    mysqli_close($cnx);
    ?>

</body>
</html>
