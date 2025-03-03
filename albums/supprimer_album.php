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
$albumName = "";

// Vérifier si l'ID de l'album est passé en GET
if (isset($_GET['idAlb'])) {
    $idAlb = mysqli_real_escape_string($cnx, $_GET['idAlb']);

    // Récupérer le nom de l'album avant suppression
    $sqlGetAlbum = "SELECT nomAlb FROM albums WHERE idAlb = '$idAlb'";
    $resAlbum = mysqli_query($cnx, $sqlGetAlbum);
    if ($resAlbum && mysqli_num_rows($resAlbum) > 0) {
        $album = mysqli_fetch_array($resAlbum);
        $albumName = $album['nomAlb'];

        // Récupérer les photos associées à cet album
        $sqlGetPhotos = "SELECT photos.idPh, photos.nomPh 
                         FROM photos
                         JOIN comporter ON photos.idPh = comporter.idPh
                         WHERE comporter.idAlb = '$idAlb'";
        $resPhotos = mysqli_query($cnx, $sqlGetPhotos);

        // Traitement de la suppression si la confirmation a été donnée
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['confirm_delete'])) {
                // Supprimer les photos uniquement associées à cet album
                while ($photo = mysqli_fetch_array($resPhotos)) {
                    $photoId = $photo['idPh'];
                    $photoName = $photo['nomPh'];

                    // Supprimer l'association entre la photo et l'album
                    $sqlDeleteComport = "DELETE FROM comporter WHERE idPh = '$photoId' AND idAlb = '$idAlb'";
                    if (!mysqli_query($cnx, $sqlDeleteComport)) {
                        $message = "Erreur lors de la suppression de l'association photo-album : " . mysqli_error($cnx);
                        break;
                    }

                    // Vérifier si la photo est encore associée à d'autres albums
                    $sqlCheckOtherAlbums = "SELECT COUNT(*) AS count FROM comporter WHERE idPh = '$photoId' AND idAlb != '$idAlb'";
                    $resCheck = mysqli_query($cnx, $sqlCheckOtherAlbums);
                    $count = mysqli_fetch_array($resCheck)['count'];

                    // Si la photo n'est associée qu'à cet album, on peut la supprimer
                    if ($count == 0) {
                        // Supprimer la photo de la table "photos"
                        $sqlDeletePhoto = "DELETE FROM photos WHERE idPh = '$photoId'";
                        if (mysqli_query($cnx, $sqlDeletePhoto)) {
                            // Supprimer le fichier photo du serveur
                            $photoPath = 'photos/' . $photoName;
                            if (file_exists($photoPath)) {
                                unlink($photoPath); // Supprimer le fichier physique
                            }
                        } else {
                            $message = "Erreur lors de la suppression de la photo : " . mysqli_error($cnx);
                            break;
                        }
                    }
                }

                // Supprimer l'album une fois que les photos ont été traitées
                $sqlDeleteAlbum = "DELETE FROM albums WHERE idAlb = '$idAlb'";
                if (mysqli_query($cnx, $sqlDeleteAlbum)) {
                    $message = "L'album \"$albumName\" et toutes ses photos associées ont été supprimés avec succès.";
                } else {
                    $message = "Erreur lors de la suppression de l'album : " . mysqli_error($cnx);
                }
            } elseif (isset($_POST['cancel_delete'])) {
                // Si l'utilisateur annule, rediriger vers la page principale
                header("Location: index.php");
                exit();
            }
        }
    } else {
        $message = "L'album n'existe pas ou a déjà été supprimé.";
    }
} else {
    $message = "Aucun ID d'album spécifié.";
}

mysqli_close($cnx);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Confirmation de suppression d'album</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Confirmation de suppression de l'album</h1><br><br>

    <div class="message">
        <?php
        // Afficher le message (erreur ou succès)
        if (!empty($message)) {
            echo $message;
        }
        ?>
    </div>

    <?php if (empty($message)): ?>
        <div class="confirmation">
            <p>Êtes-vous sûr de vouloir supprimer l'album "<?= $albumName ?>" et toutes ses photos associées ?</p>
            <form method="POST">
                <button type="submit" name="confirm_delete">Oui, supprimer</button>
                <button type="submit" name="cancel_delete">Non, annuler</button>
            </form>
        </div>
    <?php endif; ?>

    <br><br>
    <a href="index.php">Retour à l'index</a>
</body>
</html>
