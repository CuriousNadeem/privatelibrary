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
    <title>Manage Database</title>
    <link rel="stylesheet" href="styles/colorpalette.css">
    <link rel="stylesheet" href="styles/md.css">
    <link rel="stylesheet" href="styles/tags.css">
    <link rel="stylesheet" href="styles/styles.css">
</head>
<body>
    <div id="pageName" data-name="managedb"></div>
    <?php include 'header.php';?>
    <h1>Manage Database</h1>

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

    <div class="log-container">
        <p id="log"></p>
    </div>
    <div class="table-container">
        <?php while ($row = $result->fetch_assoc()): ?>
        <div class="file-card" 
            data-tags="<?= htmlspecialchars($row['tags']) ?>" 
            data-author="<?= htmlspecialchars($row['author']) ?>" 
            data-language="<?= htmlspecialchars($row['language']) ?>" 
            data-manga_type="<?= htmlspecialchars($row['manga_type']) ?>">
            <div class="img-id-container">
                <img class="mdb-img" src="<?= $row['cover_image_path'] ?>" alt="<?= $row['name'] ?>">
                <div class="text-con">
                    <p class="tag-item">ID: <?= $row['id'] ?></p>
                    <p>Name:</p>
                    <p contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="name"><?= $row['name'] ?></p>
                </div>
            </div>
            <p>Path:</p>
            <p contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="cover_image_path"><?= $row['cover_image_path'] ?></p>
            <p>Tags:</p>
            <div class="tag-editor" data-id="<?= $row['id'] ?>" data-field="tags">
            <div class="current-tags">
                <?php foreach (explode(',', $row['tags']) as $tag): ?>
                <span class="tag-item"><?= trim($tag) ?></span>
                <?php endforeach; ?>
            </div>
            <input type="text" class="tag-input" placeholder="Add tags..." />
            <ul class="autocomplete-dropdown"></ul>
            <button class="delete-tags-btn">Delete All Tags</button>
            </div>
            <div class="extraEditOptions<?= $row['id'] ?> eeo hidden">
                <div class="dropdown">
                    <input type="text" class="author-input<?= $row['id'] ?> dropbtn" placeholder="Add author..." value="<?= htmlspecialchars($row['author']) ?>" />                      
                    <div class="dropdown-content authorList" data-id="<?= $row['id'] ?>">
                        <span>Link 1</span>
                        <span>Link 2</span>
                        <span>Link 3</span>
                    </div>
                </div>
                <div class="dropdown">
                    <input type="text" class="language-input<?= $row['id'] ?> dropbtn" placeholder="Add Language..." value="<?= htmlspecialchars($row['language']) ?>" />
                    <div class="dropdown-content languageList" data-id="<?= $row['id'] ?>">
                        <span>Link 1</span>
                        <span>Link 2</span>
                        <span>Link 3</span>
                    </div>
                </div>
                <div class="dropdown">
                    <input type="text" class="manga-type-input<?= $row['id'] ?> dropbtn" placeholder="Add Manga Type..." value="<?= htmlspecialchars($row['manga_type']) ?>" />
                    <div class="dropdown-content mangaTypeList" data-id="<?= $row['id'] ?>">
                        <span>Link 1</span>
                        <span>Link 2</span>
                        <span>Link 3</span>
                    </div>
                </div>
            </div>
            <div class="edit-del-btn-con">
                <button class="editmore" data-id="<?= $row['id'] ?>">Edit</button>
                <button onclick="deleteRow(<?= $row['id'] ?>)">Delete</button>
            </div>
        </div>
        <?php endwhile; ?>

    </div>

    <script type="module" src="js/managedb.js"></script>
    <script src="js/filter.js"></script>
    <script>
        // Handle row deletion
        function deleteRow(id) {
            if (confirm('Are you sure you want to delete this entry?')) {
                fetch('delete_entry.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        log.innerHTML = "Deleted successfully!";
                        location.reload(); // Refresh the page
                    } else {
                        log.innerHTML = "Error deleting entry.";
                    }
                });
            }
        }
    </script>

    <script src="js/index.js"></script>
</body>
</html>
