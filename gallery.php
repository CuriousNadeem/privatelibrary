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
            <p class="heading"><?= $gallery['name'] ?></p>
            <p>Tags:</p>
            <div class="tags-container">
                <?php 
                $tagsArray = explode(',', $gallery['tags']); // Split tags by comma
                foreach ($tagsArray as $tag): ?>
                    <button class="filter-btn" onclick="handleFilterClick('<?= trim($tag) ?>', 'tags')"><?= trim($tag) ?></button>
                <?php endforeach; ?>
            </div>
            <p>Author:</p>
            <button class="filter-btn" onclick="handleFilterClick('<?= $gallery['author'] ?>', 'author')"><?= $gallery['author'] ?></button>
            <p>Language:</p>
            <button class="filter-btn" onclick="handleFilterClick('<?= $gallery['language'] ?>', 'language')"><?= $gallery['language'] ?></button>
            <p>Manga Type:</p>
            <button class="filter-btn" onclick="handleFilterClick('<?= $gallery['manga_type'] ?>', 'manga_type')"><?= $gallery['manga_type'] ?></button>
        </div>

    </div>
    <div class="gallery-container">
    <div class="gallery">
        <?php foreach ($images as $image): ?>
        <img src="<?= $galleryPath . '/' . $image ?>" alt="<?= $image ?>">
        <?php endforeach; ?>
    </div>
    </div>

    <script>
        function handleFilterClick(value, type) {
            // Save the filter value and type to sessionStorage
            sessionStorage.setItem('filtervalue', value);
            sessionStorage.setItem('filtertype', type);

            // Redirect to index.php
            window.location.href = 'index.php';
        }
    </script>

</body>
</html>

<?php else: ?>
<p>Gallery not found.</p>
<?php endif; ?>
