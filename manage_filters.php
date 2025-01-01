<?php
// File path to filter.js
$filterFilePath = 'js/filter.js';

// Function to parse JavaScript arrays
function parseJSArray($content, $variable) {
    preg_match("/const {$variable} = (\[.*?\]);/s", $content, $matches);
    return isset($matches[1]) ? json_decode($matches[1], true) : [];
}

// Load existing data
$fileContent = file_get_contents($filterFilePath);
$authors = parseJSArray($fileContent, 'authors');
$languages = parseJSArray($fileContent, 'languages');
$mangaTypes = parseJSArray($fileContent, 'mangaTypes');
$tags = parseJSArray($fileContent, 'tags');

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newAuthors = json_encode(array_map('trim', explode(',', $_POST['authors'])));
    $newLanguages = json_encode(array_map('trim', explode(',', $_POST['languages'])));
    $newMangaTypes = json_encode(array_map('trim', explode(',', $_POST['mangaTypes'])));
    $newTags = json_encode(array_map('trim', explode(',', $_POST['tags'])));

    // Generate updated JavaScript content
    $updatedContent = <<<JS
const authors = {$newAuthors};
const languages = {$newLanguages};
const mangaTypes = {$newMangaTypes};
const tags = {$newTags};
JS;

    // Save updated content back to the file
    if (file_put_contents($filterFilePath, $updatedContent)) {
        // Reload updated data to reflect changes
        $fileContent = file_get_contents($filterFilePath);
        $authors = parseJSArray($fileContent, 'authors');
        $languages = parseJSArray($fileContent, 'languages');
        $mangaTypes = parseJSArray($fileContent, 'mangaTypes');
        $tags = parseJSArray($fileContent, 'tags');
        echo '<p style="color: green;">Filters updated successfully!</p>';
    } else {
        echo '<p style="color: red;">Failed to update filters.</p>';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Filters</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        label { font-weight: bold; margin-top: 20px; display: block; }
        textarea { width: 100%; height: 100px; margin-bottom: 20px; }
        button { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    </style>
</head>
<body>
    <h1>Manage Filters</h1>
    <form action="manage_filters.php" method="POST">
        <label for="authors">Authors (comma-separated):</label>
        <textarea name="authors" id="authors"><?= htmlspecialchars(implode(', ', $authors)); ?></textarea>

        <label for="languages">Languages (comma-separated):</label>
        <textarea name="languages" id="languages"><?= htmlspecialchars(implode(', ', $languages)); ?></textarea>

        <label for="mangaTypes">Manga Types (comma-separated):</label>
        <textarea name="mangaTypes" id="mangaTypes"><?= htmlspecialchars(implode(', ', $mangaTypes)); ?></textarea>

        <label for="tags">Tags (comma-separated):</label>
        <textarea name="tags" id="tags"><?= htmlspecialchars(implode(', ', $tags)); ?></textarea>

        <button type="submit">Save Changes</button>
    </form>
</body>
</html>
