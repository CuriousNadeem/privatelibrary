<?php
include 'includes/db_connect.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT * FROM galleries WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$gallery = $result->fetch_assoc();

if ($gallery):
    $galleryPath = 'assets/galleries/' . $gallery['name'];
    // Scan the directory and sort images in ascending order
    $allFiles = array_diff(scandir($galleryPath), ['.', '..']);
    
    // Filter files for only .jpg and .png extensions
    $images = array_filter($allFiles, function ($file) {
        return preg_match('/\.(jpg|png)$/i', $file); // Match .jpg or .png (case insensitive)
    });

    natsort($images); // Sort images naturally (e.g., 1.jpg, 2.jpg, 10.jpg)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $gallery['name'] ?> - Gallery</title>
    <link rel="stylesheet" href="styles/colorpalette.css">
    <link rel="stylesheet" href="styles/gallery.css">
</head>
<body>
    <?php include 'header.php';?>
    <div class="intro">
        <div class="cover-container">
            <img src="<?= $galleryPath . '/' . "1.jpg" ?>" alt="<?= $image ?>">
        </div>
        <div class="details">
            <h2><?= $gallery['name'] ?></h2>
            <p>Tags: <?= $gallery['tags'] ?></p>
            <p>Author: </p>
        </div>
    </div>
    <div class="gallery-container">
    <div class="gallery">
        <?php foreach ($images as $image): ?>
        <img src="<?= $galleryPath . '/' . $image ?>" alt="<?= $image ?>">
        <?php endforeach; ?>
    </div>
    </div>
</body>
</html>

<?php else: ?>
<p>Gallery not found.</p>
<?php endif; ?>
