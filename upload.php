<?php
    include 'includes/db_connect.php';

    // Check if "Upload All" button was clicked
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_all'])) {
        // Ensure files are present and not empty
        if (isset($_FILES['zip_files']) && !empty($_FILES['zip_files']['name'][0])) {
            $tags = $_POST['tags'] ?? [];

            foreach ($_FILES['zip_files']['name'] as $index => $fileName) {
                $fileTmpName = $_FILES['zip_files']['tmp_name'][$index];
                $zipName = pathinfo($fileName, PATHINFO_FILENAME);
                $tag = isset($tags[$index]) ? $tags[$index] : '';

                // Check file size
                if ($_FILES['zip_files']['size'][$index] > 200 * 1024 * 1024) {
                    echo "Error: The file $fileName is too large. Maximum allowed size is 200MB.<br>";
                    continue;
                }

                // Create target directory for extracted files
                $targetDir = __DIR__ . "/assets/galleries/$zipName";
                if (!file_exists($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                // Move and extract the ZIP file
                $zipPath = $targetDir . ".zip";
                move_uploaded_file($fileTmpName, $zipPath);

                $zip = new ZipArchive();
                if ($zip->open($zipPath) === TRUE) {
                    $zip->extractTo($targetDir);
                    $zip->close();
                    unlink($zipPath); // Remove the ZIP file after extraction

                    // Determine cover image path
                    $coverImagePath = "assets/galleries/$zipName/1.jpg";
                    if (!file_exists(__DIR__ . "/$coverImagePath")) {
                        $coverImagePath = "assets/galleries/$zipName/0.jpg";
                    }

                    if (!file_exists(__DIR__ . "/$coverImagePath")) {
                        echo "Error: No valid cover image found in the uploaded ZIP for $fileName.<br>";
                        continue;
                    }

                    // Insert gallery into the database
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
            echo "No files selected for upload.<br>";
        }
    } else {
        echo "<script>console.log(\"Invalid request or button not clicked!\");</script>";
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
        <div id="file-container">
            <!-- File cards will be dynamically added here -->
        </div>

        <br><br>
        <button name="upload_all">Upload All</button>
    </form>
    <div id="alert-box"></div>

    <script src="js/filter.js"></script>
    <script>
        const fileInput = document.getElementById('zip_files');
        const fileContainer = document.getElementById('file-container');

        fileInput.addEventListener('change', function () {
            let cardHTML = ''; // Initialize HTML string

            for (let i = 0; i < fileInput.files.length; i++) {
                const fileName = fileInput.files[i].name;

                // Generate card HTML as a string
                cardHTML += `
                    <div class="file-card">
                        <p>Name:</p>
                        <p class="fileName" contenteditable="true">${fileName}</p>
                        <p>Tags:</p>
                        <div class="tag-editor">
                            <input type="text" placeholder="Enter tags" class="tag-input">
                            <input type="hidden" name="tags[]">
                        </div>
                        <div class="up-extraEditOptions${i} eeo hidden">
                            <div class="dropdown">
                                <input type="text" class="up-author-input${i} dropbtn" placeholder="Add author..." />                      
                                <div class="dropdown-content upAuthorList" data-id="${i}">
                                    <!-- Links will be dynamically added here -->
                                </div>
                            </div>
                            <div class="dropdown">
                                <input type="text" class="up-language-input${i} dropbtn" placeholder="Add Language..." />
                                <div class="dropdown-content upLanguageList" data-id="${i}">
                                    <!-- Links will be dynamically added here -->
                                </div>
                            </div>
                            <div class="dropdown">
                                <input type="text" class="up-manga-type-input${i} dropbtn" placeholder="Add Manga Type..." />
                                <div class="dropdown-content upMangaTypeList" data-id="${i}">
                                    <!-- Links will be dynamically added here -->
                                </div>
                            </div>
                        </div>
                        <button class="editMore" name="other_button_action" data-id="${i}">Edit More</button>
                    </div>
                `;
            }

            // Append all cards to the container
            fileContainer.innerHTML = cardHTML;

            // Re-apply tag management functionality to the dynamically created inputs
            const tagEditors = document.querySelectorAll('.tag-editor');
            tagEditors.forEach(tagEditor => {
                const tagInput = tagEditor.querySelector('.tag-input');
                const hiddenInput = tagEditor.querySelector('input[type="hidden"]');
                addTagFeatures(tagInput, hiddenInput, tagEditor);
            });
            EditMore();
        });

        function addTagFeatures(tagInput, hiddenInput, tagEditor) { //this is line 156
            tagInput.addEventListener('input', function () {
                const value = this.value.toLowerCase();
                const suggestions = tags.filter(tag => tag.toLowerCase().startsWith(value));
                let dropdown = tagEditor.querySelector('.autocomplete-dropdown');

                if (!dropdown) {
                    dropdown = document.createElement('ul');
                    dropdown.classList.add('autocomplete-dropdown');
                    tagEditor.appendChild(dropdown);
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

            tagInput.addEventListener('blur', function () {
                setTimeout(() => {
                    const dropdown = tagEditor.querySelector('.autocomplete-dropdown');
                    if (dropdown) dropdown.remove();
                }, 100); // Delay to allow for click on dropdown items
            });

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

        function addTag(input, hiddenInput, tag) {
            const currentTags = hiddenInput.value ? hiddenInput.value.split(',') : [];
            if (!currentTags.includes(tag)) {
                currentTags.push(tag);
                hiddenInput.value = currentTags.join(',');
            }

            input.value = '';
            const tagDisplay = document.createElement('span');
            tagDisplay.classList.add('tag-item');
            tagDisplay.textContent = tag;
            input.parentNode.insertBefore(tagDisplay, input);

            tagDisplay.addEventListener('click', () => {
                tagDisplay.remove();
                const index = currentTags.indexOf(tag);
                if (index > -1) {
                    currentTags.splice(index, 1);
                    hiddenInput.value = currentTags.join(',');
                }
            });
        }

        
    function EditMore() {
        document.querySelectorAll('.editMore').forEach(btn =>{
            btn.addEventListener('click', ()=>{
                event.preventDefault(); // Prevent form submission
                const editDiv = document.querySelector(`.up-extraEditOptions${btn.dataset.id}`);
                if (editDiv.classList.contains('hidden')) {
                    editDiv.classList.remove('hidden');
                } else {
                    editDiv.classList.add('hidden');
                }
            });
        })
        document.querySelectorAll('.upLanguageList').forEach(element => {
            element.innerHTML = ``;
            languages.forEach(language =>{
                element.innerHTML += `<span class="language" data-id="${element.dataset.id}">${language}</span>`
            })
        })
        document.querySelectorAll('.upMangaTypeList').forEach(element => {
            element.innerHTML = ``;
            mangaTypes.forEach(mangaType =>{
                element.innerHTML += `<span class="mangatype" data-id="${element.dataset.id}">${mangaType}</span>`
            })
        })
        document.querySelectorAll('.upAuthorList').forEach(element => {
            element.innerHTML = ``;
            authors.forEach(author =>{
                element.innerHTML += `<span class="author" data-id="${element.dataset.id}">${author}</span>`
            })
        })
        document.querySelectorAll('.language').forEach(element => {
            element.addEventListener('click', () => {
                const input = document.querySelector(`.up-language-input${element.dataset.id}`);
                input.value = element.innerHTML;
                input.dispatchEvent(new Event('blur')); // Trigger the save logic
            });
        });
        
        document.querySelectorAll('.mangatype').forEach(element => {
            element.addEventListener('click', () => {
                const input = document.querySelector(`.up-manga-type-input${element.dataset.id}`);
                input.value = element.innerHTML;
                input.dispatchEvent(new Event('blur')); // Trigger the save logic
            });
        });
        document.querySelectorAll('.author').forEach(element => {
            element.addEventListener('click', () => {
                const input = document.querySelector(`.up-author-input${element.dataset.id}`);
                input.value = element.innerHTML;
                input.dispatchEvent(new Event('blur')); // Trigger the save logic
            });
        });
        
        document.querySelectorAll('.author, .language, .mangatype').forEach(span => {
            span.addEventListener('click', function () {
                const id = this.getAttribute('data-id');
                const field = this.classList.contains('author') ? 'author' :
                            this.classList.contains('language') ? 'language' :
                            'manga_type';
                const value = this.innerText;
        
                fetch('update_entry.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, field, value })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        log.innerHTML = `${field.charAt(0).toUpperCase() + field.slice(1)} updated successfully!`;
                    } else {
                        log.innerHTML = `Error updating ${field}.`;
                    }
                });
            });
        });
    }
    </script>
    <script src="js/upload.js"></script>
</body>
</html>
