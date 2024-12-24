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
    $images = array_diff(scandir($galleryPath), ['.', '..']);
?>

<h1><?= $gallery['name'] ?></h1>
<p>Tags: <?= $gallery['tags'] ?></p>
<div class="gallery">
    <?php foreach ($images as $image): ?>
    <img src="<?= $galleryPath . '/' . $image ?>" alt="<?= $image ?>">
    <?php endforeach; ?>
</div>

<?php else: ?>
<p>Gallery not found.</p>
<?php endif; ?>
