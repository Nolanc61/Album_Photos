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

// Traitement du formulaire pour ajouter une photo
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['new_photo']) && $_FILES['new_photo']['error'] == 0) {
        // Générer un nouveau nom de fichier pour la photo (ph_ + numéro)
        $photo_name = $_FILES['new_photo']['name'];
        $photo_tmp_name = $_FILES['new_photo']['tmp_name'];
        $photo_size = $_FILES['new_photo']['size'];
        $photo_type = $_FILES['new_photo']['type'];

        // Récupérer le dernier numéro utilisé pour les photos
        $sqlGetLastPhoto = "SELECT MAX(idPh) AS last_id FROM photos";
        $resLastPhoto = mysqli_query($cnx, $sqlGetLastPhoto);
        $rowLastPhoto = mysqli_fetch_assoc($resLastPhoto);
        $lastId = $rowLastPhoto['last_id'];

        // Générer un nom formaté ph_01, ph_02, etc.
        $new_photo_name = 'ph_' . str_pad($lastId + 1, 2, '0', STR_PAD_LEFT) . '.jpg'; // Exemple avec extension .jpg

        // Déplacement du fichier dans le dossier "photos"
        $photo_path = 'photos/' . $new_photo_name;
        if (move_uploaded_file($photo_tmp_name, $photo_path)) {
            // Insérer la photo dans la base de données
            $idAlb = $_GET['idAlb']; // Récupérer l'ID de l'album à partir de l'URL
            $sqlInsert = "INSERT INTO photos (nomPh) VALUES ('$new_photo_name')";
            if (mysqli_query($cnx, $sqlInsert)) {
                // Récupérer l'ID de la photo insérée
                $idPh = mysqli_insert_id($cnx);
                
                // Associer la photo à l'album
                $sqlComport = "INSERT INTO comporter (idAlb, idPh) VALUES ('$idAlb', '$idPh')";
                if (mysqli_query($cnx, $sqlComport)) {
                    $message = "Photo ajoutée avec succès!";
                } else {
                    $message = "Erreur lors de l'association de la photo à l'album : " . mysqli_error($cnx);
                }
            } else {
                $message = "Erreur lors de l'ajout de la photo : " . mysqli_error($cnx);
            }
        } else {
            $message = "Erreur lors du téléchargement de la photo.";
        }
    } else {
        $message = "Veuillez choisir une photo à télécharger.";
    }
}

// Récupérer les albums existants
$sql = "SELECT * FROM albums";
$res = mysqli_query($cnx, $sql);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Ajouter une photo</title>
    <link rel="stylesheet" href="style.css">
    <script>
        // Affichage du nom du fichier sélectionné
        function showFileName(input) {
            var fileName = input.files[0].name;
            document.getElementById('file-name').textContent = 'Nom du fichier : ' + fileName;
        }
    </script>
</head>
<body>
    <h1>Ajouter une photo à l'album</h1><br><br>

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

    <!-- Formulaire pour ajouter une photo -->
    <form action="" method="post" enctype="multipart/form-data">
        <label>Choisissez une photo à ajouter :</label>
        <input type="file" name="new_photo" id="new_photo" accept="image/*" onchange="showFileName(this)" />
        <p id="file-name"></p> <!-- Affichage du nom du fichier -->
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
