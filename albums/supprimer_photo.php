<?php
// Connexion à la base de données
$cnx = mysqli_connect("localhost", "root", "", "albums");

// Vérifier si la connexion est bien établie
if (mysqli_connect_errno()) {
    echo "Échec de la connexion : " . mysqli_connect_error();
    exit();
}

// Initialisation des variables
$message = "";

// Vérifier si l'ID de la photo est passé en GET
if (isset($_GET['idPh'])) {
    $idPh = mysqli_real_escape_string($cnx, $_GET['idPh']);

    // Récupérer le nom de la photo avant suppression
    $sqlGetPhotoName = "SELECT nomPh FROM photos WHERE idPh = '$idPh'";
    $res = mysqli_query($cnx, $sqlGetPhotoName);
    if ($res && mysqli_num_rows($res) > 0) {
        $photo = mysqli_fetch_array($res);
        $photoName = $photo['nomPh'];

        // Formulaire de confirmation
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (isset($_POST['confirm_delete'])) {
                // Supprimer toutes les associations de la photo dans la table "comporter"
                $sqlDeleteComport = "DELETE FROM comporter WHERE idPh = '$idPh'";
                if (mysqli_query($cnx, $sqlDeleteComport)) {
                    // Suppression de la photo de la table "photos"
                    $sqlDeletePhoto = "DELETE FROM photos WHERE idPh = '$idPh'";
                    if (mysqli_query($cnx, $sqlDeletePhoto)) {
                        // Supprimer le fichier photo du serveur
                        $photoPath = 'photos/' . $photoName;
                        if (file_exists($photoPath)) {
                            unlink($photoPath); // Supprime le fichier physique
                        }

                        $message = "La photo a été supprimée avec succès.";
                    } else {
                        $message = "Erreur lors de la suppression de la photo : " . mysqli_error($cnx);
                    }
                } else {
                    $message = "Erreur lors de la suppression de l'association photo-album : " . mysqli_error($cnx);
                }
            } elseif (isset($_POST['cancel_delete'])) {
                // Rediriger si l'utilisateur annule
                header("Location: index.php");
                exit();
            }
        }
    } else {
        $message = "La photo n'existe pas ou a déjà été supprimée.";
    }
} else {
    $message = "Aucun ID de photo spécifié.";
}

mysqli_close($cnx);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Confirmation de suppression</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Confirmation de suppression</h1><br><br>

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
            <p>Êtes-vous sûr de vouloir supprimer la photo "<?= $photoName ?>" ?</p>
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
