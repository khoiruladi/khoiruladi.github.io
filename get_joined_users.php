<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

header('Content-Type: application/json');

$room_code = isset($_SESSION['room_code']) ? $_SESSION['room_code'] : '';

if (empty($room_code)) {
    echo json_encode(['success' => false, 'message' => 'Room code tidak ditemukan']);
    exit;
}

$stmt = $conn->prepare("SELECT username FROM users WHERE room_code = ?");
$stmt->bind_param("s", $room_code);
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
echo json_encode(['success' => true, 'users' => $users]);
?>
