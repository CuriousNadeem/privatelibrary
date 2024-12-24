<?php
include 'includes/db_connect.php';

// Handle multiple file uploads
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_files']) && !empty($_FILES['zip_files']['name'][0])) {
    $tags = $_POST['tags'] ?? [];

    // Loop through the selected files
    foreach ($_FILES['zip_files']['name'] as $index => $fileName) {
        // Get each file's temporary name and tags
        $fileTmpName = $_FILES['zip_files']['tmp_name'][$index];
        $zipName = pathinfo($fileName, PATHINFO_FILENAME);
        $tag = isset($tags[$index]) ? $tags[$index] : '';

        // Validate file size and move the file
        if ($_FILES['zip_files']['size'][$index] > 200 * 1024 * 1024) { // 200MB
            echo "Error: The file $fileName is too large. Maximum allowed size is 200MB.<br>";
            continue;
        }

        $targetDir = __DIR__ . "/assets/galleries/$zipName";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // Move uploaded ZIP file
        $zipPath = $targetDir . ".zip";
        move_uploaded_file($fileTmpName, $zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($targetDir);
            $zip->close();
            unlink($zipPath); // Remove the ZIP file after extraction

            // Set the cover image path (1.jpg or fallback to 0.jpg)
            $coverImagePath = "assets/galleries/$zipName/1.jpg";
            if (!file_exists(__DIR__ . "/$coverImagePath")) {
                $coverImagePath = "assets/galleries/$zipName/0.jpg";
            }

            // Ensure that at least one image exists
            if (!file_exists(__DIR__ . "/$coverImagePath")) {
                echo "Error: No valid cover image found in the uploaded ZIP for $fileName.<br>";
                continue;
            }

            // Insert gallery information into the database
            $sql = "INSERT INTO galleries (name, cover_image_path, tags, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $zipName, $coverImagePath, $tag);

            if ($stmt->execute()) {
                echo "Gallery $fileName uploaded successfully!<br>";
            } else {
                echo "Error: Failed to save gallery $fileName in the database.<br>";
            }
        } else {
            echo "Error: Failed to open the ZIP file $fileName.<br>";
        }
    }
} else {
    echo "Invalid request.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Gallery</title>
    <link rel="stylesheet" href="styles/colorpalette.css">
    <link rel="stylesheet" href="styles/upload.css">
</head>
<body>
    <?php include 'header.php';?>
    <h1>Upload Gallery</h1>

    <form action="upload.php" method="POST" enctype="multipart/form-data">
        <label for="zip_files">Choose ZIP Files (multiple):</label>
        <input class="choosebtn" type="file" name="zip_files[]" id="zip_files" multiple required>

        <h2>Selected Files:</h2>
        <table class="table" id="file-table">
            <thead>
                <tr>
                    <th>File Name</th>
                    <th>Tags</th>
                </tr>
            </thead>
            <tbody>
                <!-- Dynamic file list will be added here -->
            </tbody>
        </table>

        <br><br>
        <button type="submit">Upload All</button>
    </form>
    <div class="alert-box">
    </div>

    <script>
        const fileInput = document.getElementById('zip_files');
        const fileTable = document.getElementById('file-table').getElementsByTagName('tbody')[0];

        // Display selected files in a table
        fileInput.addEventListener('change', function() {
            fileTable.innerHTML = ''; // Clear previous table rows

            // Loop through the selected files and add them to the table
            for (let i = 0; i < fileInput.files.length; i++) {
                const row = fileTable.insertRow();
                const cell1 = row.insertCell(0);
                const cell2 = row.insertCell(1);

                const fileName = fileInput.files[i].name;

                // Set the file name in the first column
                cell1.textContent = fileName;

                // Create an input for tags in the second column
                const tagInput = document.createElement('input');
                tagInput.type = 'text';
                tagInput.name = 'tags[]';
                tagInput.placeholder = 'Enter tags';
                tagInput.classList.add('tag-input');
                cell2.appendChild(tagInput);
            }
        });
    </script>
</body>
</html>