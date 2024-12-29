<?php
include 'includes/db_connect.php';

$tags = [];
$author = [];
$language = [];
$mangatype = [];

// Fetch tags
$result = $conn->query("SELECT DISTINCT tags FROM galleries");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $tags = array_merge($tags, explode(',', $row['tags']));
    }
    $tags = array_unique(array_map('trim', $tags));
}

// Fetch authors
$result = $conn->query("SELECT DISTINCT author FROM galleries");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $author[] = $row['author'];
    }
}

// Fetch languages
$result = $conn->query("SELECT DISTINCT language FROM galleries");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $language[] = $row['language'];
    }
}

// Fetch manga types
$result = $conn->query("SELECT DISTINCT manga_type FROM galleries");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $mangatype[] = $row['manga_type'];
    }
}

// Fetch all gallery data
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
    <link rel="stylesheet" href="styles/tags.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="search-container">
    <!-- Main Search Box -->
    <div class="main-search">
        <input type="text" id="main-search-box" placeholder="Search..." />
        <button id="main-search-btn">
            <img src="assets/img/search2.png" alt="Search" />
        </button>
    </div>

    <!-- Filter Buttons -->
    <div class="filter-buttons">
        <button class="filter-btn" data-filter="tags">Tags</button>
        <button class="filter-btn" data-filter="author">Author</button>
        <button class="filter-btn" data-filter="language">Language</button>
        <button class="filter-btn" data-filter="manga_type">Manga Type</button>
        <button id="gate-toggle-btn" class="gate-toggle-btn">OR Gate</button>
    </div>

    <!-- Dynamic Filter Search -->
    <div class="filter-search-container">
        <div class="filter-search" id="filter-search">
            <!-- Placeholder for dynamic search input -->
        </div>
    </div>

    <!-- Applied Filters -->
    <div class="applied-filters">
        <h4>Applied Filters:</h4>
        <ul id="filters-list" class="filters-list"></ul>
    </div>
    </div>

    <div class="grid">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="grid-card" 
            data-tags="<?= htmlspecialchars($row['tags']) ?>" 
            data-author="<?= htmlspecialchars($row['author']) ?>" 
            data-language="<?= htmlspecialchars($row['language']) ?>" 
            data-manga_type="<?= htmlspecialchars($row['manga_type']) ?>">
            <a href="gallery.php?id=<?= $row['id'] ?>">
                <div class="image-container">
                    <img src="<?= $row['cover_image_path'] ?>" alt="<?= $row['name'] ?>" class="cover-image">
                </div>
                <p class="card-name"><?= $row['name'] ?></p>
            </a>
        </div>
        <?php endwhile; ?>
    </div>


    <script src="js/filter.js"></script>
    <!-- <script>
        const tags = <?php echo json_encode($tags); ?>;
        const authors = <?php echo json_encode($author); ?>;
        const languages = <?php echo json_encode($language); ?>;
        const mangaTypes = <?php echo json_encode($mangatype); ?>;
    </script> -->

    <script src="js/index.js"></script>
</body>
</html>
