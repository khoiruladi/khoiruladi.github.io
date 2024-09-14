<?php
session_start();
include 'db_connection.php'; // Koneksi ke database

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$username = $data['username'];
$roomCode = $data['roomCode'];

if (empty($username) || empty($roomCode)) {
    echo json_encode(['success' => false, 'message' => 'Data tidak lengkap']);
    exit;
}

// Simpan sinyal ke database
$stmt = $conn->prepare("INSERT INTO signals (username, room_code, timestamp) VALUES (?, ?, NOW())");
$stmt->bind_param("ss", $username, $roomCode);
$success = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $success]);
?>
