<?php
include 'includes/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];

// Fetch the folder name before deleting the database entry
$sql = "SELECT name FROM galleries WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $galleryName = $row['name'];
    $folderPath = __DIR__ . "/assets/galleries/" . $galleryName;

    // Delete the folder and its contents
    function deleteFolder($folder) {
        if (!is_dir($folder)) return;
        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $filePath = $folder . DIRECTORY_SEPARATOR . $file;
            is_dir($filePath) ? deleteFolder($filePath) : unlink($filePath);
        }
        rmdir($folder);
    }

    deleteFolder($folderPath);

    // Delete the database entry
    $deleteSql = "DELETE FROM galleries WHERE id = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bind_param('i', $id);

    if ($deleteStmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Entry and folder deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete database entry.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Entry not found.']);
}
?>
