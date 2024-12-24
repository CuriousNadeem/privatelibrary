<?php
include 'includes/db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$id = $data['id'];
$field = $data['field'];
$value = $data['value'];

// Update query
$sql = "UPDATE galleries SET $field = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('si', $value, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $stmt->error]);
}
?>
