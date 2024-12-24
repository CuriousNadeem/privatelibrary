<?php
include 'includes/db_connect.php';

// Check if the connection is working
if ($conn) {
    echo "<script>console.log(\"Database connection successful!\");</script>";
}

// Fetch all gallery information from the database
$result = $conn->query("SELECT * FROM galleries ORDER BY created_at DESC");

if (!$result) {
    die("Error fetching galleries: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gallery</title>
    <link rel="stylesheet" href="styles/colorpalette.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <?php include 'header.php';?>
    <div class="grid">
        <?php
        while ($row = $result->fetch_assoc()):
            // Fetch the name and cover image path from the database
            $galleryName = $row['name'];
            $coverImagePath = $row['cover_image_path'];
        ?>
        <div class="grid-card">
            <a href="gallery.php?id=<?= $row['id'] ?>">
                <div class="image-container">
                <img src="<?= $coverImagePath ?>" alt="<?= $galleryName ?>" class="cover-image">
                </div>
                <h3><?= $galleryName ?></h3>
            </a>
        </div>
        <?php endwhile; ?>
    </div>
</body>
</html>
