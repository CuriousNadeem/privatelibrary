<?php
include 'includes/db_connect.php';

// Decode the JSON payload
$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$field = $data['field'];
$value = $data['value'];

// Ensure the field is valid
$validFields = ['name', 'cover_image_path', 'tags', 'language', 'manga_type', 'author'];

if (in_array($field, $validFields)) {
    $stmt = $conn->prepare("UPDATE galleries SET $field = ? WHERE id = ?");
    $stmt->bind_param('si', $value, $id);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Invalid field']);
}
?>
