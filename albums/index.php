<?php
    $cnx = mysqli_connect("localhost", "root", "", "albums");

    if (!isset($_GET['idAlb'])) {
        $sql = "SELECT * FROM albums LIMIT 1";
        $res = mysqli_query($cnx, $sql);

        // Vérifier si une ligne a bien été récupérée
        if ($row = mysqli_fetch_array($res)) {
            $_GET['idAlb'] = $row["idAlb"];
        } else {
            echo "Aucun album trouvé!";
            exit(); // Arrêter l'exécution si aucune donnée n'est récupérée
        }
    }

    // Connexion à la base de données
    $cnx = mysqli_connect("localhost", "root", "", "albums");

    if (mysqli_connect_errno()) {
        echo "Échec de la connexion : " . mysqli_connect_error();
        exit();
    }

    // Si l'ID de l'album est passé en GET, récupérer les photos de cet album
    if (isset($_GET['idAlb'])) {
        $idAlb = mysqli_real_escape_string($cnx, $_GET['idAlb']);

        // Récupérer les photos de l'album
        $sqlImages = "SELECT photos.idPh, photos.nomPh 
                      FROM photos 
                      JOIN comporter ON photos.idPh = comporter.idPh 
                      WHERE comporter.idAlb = '$idAlb'";
        $resImages = mysqli_query($cnx, $sqlImages);
    } else {
        echo "Aucun album sélectionné.";
        exit();
    }
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
        // Récupérer tous les albums pour les afficher en lien
        $sqlAllAlbums = "SELECT * FROM albums";
        $resAllAlbums = mysqli_query($cnx, $sqlAllAlbums);
        while ($album = mysqli_fetch_array($resAllAlbums)) {
            // Afficher le nom de l'album et créer un lien pour chaque album
            echo '<a href="index.php?idAlb=' . $album["idAlb"] . '">' . $album['nomAlb'] . '</a> ';
        }
        ?>
        <a href="ajouter_album.php">+</a>
        <a class="logo" href="modifier_album.php?idAlb=<?= $_GET["idAlb"] ?>"></a>
        <a href="supprimer_album.php?idAlb=<?=$_GET["idAlb"]?>">🆑</a>
    </div>
    <br><br>

    <?php
    // Si des photos sont récupérées pour l'album
    if (mysqli_num_rows($resImages) > 0) {
        // Affichage des photos
        while ($ligne = mysqli_fetch_array($resImages)) {
            // Afficher l'image avec le bouton modifier dans le coin
            echo '<div class="photo">';
            echo '<img src="photos/' . $ligne["nomPh"] . '" alt="' . $ligne["nomPh"] . '" onclick="ouvrirImage(\'photos/' . $ligne["nomPh"] . '\')" />';

            // Ajouter un lien vers la page de modification de la photo avec idPh
            echo '<a class="modifier" href="modifier_photo.php?idPh=' . $ligne["idPh"] . '">✏️</a>';

            // Ajouter un lien vers la page de suppression de la photo avec idPh
            echo '<a class="suppression" href="supprimer_photo.php?idPh=' . $ligne["idPh"] . '">🗑️</a>';
            echo '</div>';
        }
    } else {
        echo "<div class='message'>Aucune photo dans cet album.</div>";
    }

    echo '<a class="ajouter_photo" href="ajouter_photo.php?idAlb=' . $idAlb . '">Ajouter une photo</a>';
    // Libération des résultats de la requête
    mysqli_free_result($resImages);
    mysqli_free_result($resAllAlbums);
    mysqli_close($cnx);
    ?>

    <!-- La fenêtre modale pour afficher l'image en grand -->
    <div id="myModal" class="modal">
        <span class="close" onclick="fermerModal()">&times;</span>
        <img class="modal-content" id="imgModal">
    </div>

    <script>
        // Fonction pour ouvrir l'image en grand dans la modale
        function ouvrirImage(src) {
            var modal = document.getElementById("myModal");
            var modalImg = document.getElementById("imgModal");
            modal.style.display = "block"; // Afficher la modale
            modalImg.src = src; // Mettre l'URL de l'image dans la modale
        }

        // Fonction pour fermer la modale
        function fermerModal() {
            var modal = document.getElementById("myModal");
            modal.style.display = "none"; // Masquer la modale
        }

        // Fermer la modale si l'utilisateur clique en dehors de l'image
        window.onclick = function(event) {
            var modal = document.getElementById("myModal");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
</body>
</html>
