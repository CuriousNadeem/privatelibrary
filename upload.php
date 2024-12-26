<?php
include 'includes/db_connect.php';

// Handle multiple file uploads (same as before)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['zip_files']) && !empty($_FILES['zip_files']['name'][0])) {
    $tags = $_POST['tags'] ?? [];

    foreach ($_FILES['zip_files']['name'] as $index => $fileName) {
        $fileTmpName = $_FILES['zip_files']['tmp_name'][$index];
        $zipName = pathinfo($fileName, PATHINFO_FILENAME);
        $tag = isset($tags[$index]) ? $tags[$index] : '';

        if ($_FILES['zip_files']['size'][$index] > 200 * 1024 * 1024) {
            echo "Error: The file $fileName is too large. Maximum allowed size is 200MB.<br>";
            continue;
        }

        $targetDir = __DIR__ . "/assets/galleries/$zipName";

        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $zipPath = $targetDir . ".zip";
        move_uploaded_file($fileTmpName, $zipPath);

        $zip = new ZipArchive();
        if ($zip->open($zipPath) === TRUE) {
            $zip->extractTo($targetDir);
            $zip->close();
            unlink($zipPath);

            $coverImagePath = "assets/galleries/$zipName/1.jpg";
            if (!file_exists(__DIR__ . "/$coverImagePath")) {
                $coverImagePath = "assets/galleries/$zipName/0.jpg";
            }

            if (!file_exists(__DIR__ . "/$coverImagePath")) {
                echo "Error: No valid cover image found in the uploaded ZIP for $fileName.<br>";
                continue;
            }

            $sql = "INSERT INTO galleries (name, cover_image_path, tags, created_at) VALUES (?, ?, ?, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('sss', $zipName, $coverImagePath, $tag);

            if ($stmt->execute()) {
                echo "Gallery $fileName uploaded successfully!";
            } else {
                echo "Error: Failed to save gallery $fileName in the database.<br>";
            }
        } else {
            echo "Error: Failed to open the ZIP file $fileName.<br>";
        }
    }
} else {
    echo "<script>console.log(\"Invalid request!\");</script>";
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
    <link rel="stylesheet" href="styles/tags.css">
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
    <div id="alert-box"></div>

    <script src="js/tags.js"></script>
    <script>
        // Function to add a tag
    function addTag(input, hiddenInput, tag) {
        // Append the tag to the hidden input's value
        const currentTags = hiddenInput.value ? hiddenInput.value.split(',') : [];
        if (!currentTags.includes(tag)) {
            currentTags.push(tag);
            hiddenInput.value = currentTags.join(',');
        }

        // Clear the input and show current tags visually
        input.value = '';
        const tagDisplay = document.createElement('span');
        tagDisplay.classList.add('tag-item');
        tagDisplay.textContent = tag;
        input.parentNode.insertBefore(tagDisplay, input);

        // Allow removal of tags
        tagDisplay.addEventListener('click', () => {
            tagDisplay.remove();
            const index = currentTags.indexOf(tag);
            if (index > -1) {
                currentTags.splice(index, 1);
                hiddenInput.value = currentTags.join(',');
            }
        });
    }

    const fileInput = document.getElementById('zip_files');
    const fileTable = document.getElementById('file-table').getElementsByTagName('tbody')[0];
    
            // Display selected files in a table
            fileInput.addEventListener('change', function () {
            fileTable.innerHTML = ''; // Clear previous table rows

            // Loop through the selected files and add them to the table
            for (let i = 0; i < fileInput.files.length; i++) {
                const row = fileTable.insertRow();
                const cell1 = row.insertCell(0);
                const cell2 = row.insertCell(1);

                const fileName = fileInput.files[i].name;

                // Set the file name in the first column
                cell1.textContent = fileName;

                // Create a container for tag input
                const tagInputContainer = document.createElement('div');
                tagInputContainer.classList.add('tag-editor');

                // Create an input for tags
                const tagInput = document.createElement('input');
                tagInput.type = 'text';
                tagInput.placeholder = 'Enter tags';
                tagInput.classList.add('tag-input');

                // Create a hidden input to store the final tags
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'tags[]';

                tagInputContainer.appendChild(tagInput);
                tagInputContainer.appendChild(hiddenInput);
                cell2.appendChild(tagInputContainer);

                // Handle dropdown and tag addition
                tagInput.addEventListener('input', function () {
                    const value = this.value.toLowerCase();
                    const suggestions = tags.filter(tag => tag.toLowerCase().startsWith(value));
                    let dropdown = tagInputContainer.querySelector('.autocomplete-dropdown');

                    if (!dropdown) {
                        dropdown = document.createElement('ul');
                        dropdown.classList.add('autocomplete-dropdown');
                        tagInputContainer.appendChild(dropdown);
                    }

                    dropdown.innerHTML = ''; // Clear previous suggestions

                    suggestions.forEach(suggestion => {
                        const option = document.createElement('li');
                        option.textContent = suggestion;
                        option.addEventListener('click', () => {
                            addTag(tagInput, hiddenInput, suggestion);
                            dropdown.remove();
                        });
                        dropdown.appendChild(option);
                    });
                });

                // Handle blur to remove the dropdown
                tagInput.addEventListener('blur', function () {
                    setTimeout(() => {
                        const dropdown = tagInputContainer.querySelector('.autocomplete-dropdown');
                        if (dropdown) dropdown.remove();
                    }, 100); // Delay to allow for click on dropdown items
                });

                // Handle Enter key for manual tag addition
                tagInput.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const value = this.value.trim();
                        if (value) {
                            addTag(tagInput, hiddenInput, value);
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
