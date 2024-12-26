<?php
include 'includes/db_connect.php';

// Fetch all rows from the `galleries` table
$result = $conn->query("SELECT * FROM galleries");
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
</head>
<body>
    <?php include 'header.php';?>
    <h1>Manage Database</h1>
    <div class="log-container">
        <p id="log"></p>
    </div>
    <div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Gallery Name</th>
                <th>Cover Image Path</th>
                <th>Tags</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="name"><?= $row['name'] ?></td>
                <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="cover_image_path"><?= $row['cover_image_path'] ?></td>
                <td>
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
                </td>

                <td>
                    <button onclick="deleteRow(<?= $row['id'] ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <script type="module" src="js/managedb.js"></script>
    <script src="js/tags.js"></script>
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
</body>
</html>
