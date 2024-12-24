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
</head>
<body>
    <?php include 'header.php';?>
    <h1>Manage Database</h1>
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
                <td contenteditable="true" class="editable" data-id="<?= $row['id'] ?>" data-field="tags"><?= $row['tags'] ?></td>
                <td>
                    <button onclick="deleteRow(<?= $row['id'] ?>)">Delete</button>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    </div>

    <script>
        // Handle inline editing
        document.querySelectorAll('.editable').forEach(cell => {
            cell.addEventListener('blur', function() {
                const id = this.getAttribute('data-id');
                const field = this.getAttribute('data-field');
                const value = this.innerText;

                fetch('update_entry.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, field, value })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Updated successfully!');
                    } else {
                        alert('Error updating entry.');
                    }
                });
            });
        });

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
                        alert('Deleted successfully!');
                        location.reload(); // Refresh the page
                    } else {
                        alert('Error deleting entry.');
                    }
                });
            }
        }
    </script>
</body>
</html>
